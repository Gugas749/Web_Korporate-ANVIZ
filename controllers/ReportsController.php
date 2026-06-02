<?php

namespace app\controllers;

use app\models\Userinfo;
use app\models\VRecord;
use Yii;
use yii\web\Controller;
use yii\web\Response;
use Mpdf\Mpdf;

class ReportsController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::class,
                'except' => ['error'],
                'rules' => [['allow' => true, 'roles' => ['@']]],
                'denyCallback' => function () {
                    return Yii::$app->response->redirect(['site/login']);
                },
            ],
        ];
    }

    // ══════════════════════════════════════════════════════════════
    //  API HELPERS
    // ══════════════════════════════════════════════════════════════

    private function apiGet(string $path): ?array
    {
        $base = Yii::$app->params['anvizApiUrl'] ?? 'https://localhost:7236';
        $ch   = curl_init($base . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => false,    // TODO: remove in production
            CURLOPT_SSL_VERIFYHOST => false,    // TODO: remove in production
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        ]);
        $res  = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (!$res || $code !== 200) return null;
        return json_decode($res, true);
    }

    private function fetchAllRecords(?string $from = null, ?string $to = null): array
    {
        $query = VRecord::find()
            ->where(['!=', 'Deptid', -1])   // exclude soft-deleted users (same rule as Userinfo)
            ->orderBy(['CheckTime' => SORT_ASC]);

        if ($from) {
            $query->andWhere(['>=', 'CheckTime', $from . ' 00:00:00']);
        }
        if ($to) {
            $query->andWhere(['<=', 'CheckTime', $to   . ' 23:59:59']);
        }

        $rows    = $query->all();
        $merged  = [];
        $seen    = [];

        foreach ($rows as $r) {
            $uid = (string)$r->Userid;
            $ts  = strtotime($r->CheckTime);
            if (!$uid || $ts === false) continue;

            // Deduplicate identical punches (same user + same second)
            $key = $uid . '|' . $ts;
            if (isset($seen[$key])) continue;
            $seen[$key] = true;

            $merged[] = [
                'userid'  => $uid,
                'name'    => $r->Name     ?? $uid,
                'dept'    => $r->DeptName ?? '—',
                'ts'      => $ts,
                'date'    => date('Y-m-d', $ts),
                // CheckType: 0 = Check-In, 1 = Check-Out  (same as Anviz SDK RecordType)
                'isIn'    => ((int)$r->CheckType === 0),
            ];
        }

        return $merged;
    }

    private function groupByUser(array $allRecords, string $from, string $to): array
    {
        $fromTs = strtotime($from . ' 00:00:00');
        $toTs   = strtotime($to   . ' 23:59:59');

        $grouped = [];

        foreach ($allRecords as $r) {
            if ($r['ts'] < $fromTs || $r['ts'] > $toTs) continue;

            $uid = $r['userid'];

            if (!isset($grouped[$uid])) {
                $grouped[$uid] = [
                    'userid'   => $uid,
                    'name'     => $r['name'],   // comes from V_Record.Name
                    'dept'     => $r['dept'],   // comes from V_Record.DeptName
                    'in_ts'    => null,
                    'out_ts'   => null,
                    'in'       => null,
                    'out'      => null,
                    'duration' => '—',
                    'hours'    => 0.0,
                    'all_ts'   => [],
                ];
            }

            $grouped[$uid]['all_ts'][] = ['ts' => $r['ts'], 'isIn' => $r['isIn']];

            if ($r['isIn']) {
                // Keep earliest check-in
                if ($grouped[$uid]['in_ts'] === null || $r['ts'] < $grouped[$uid]['in_ts']) {
                    $grouped[$uid]['in_ts'] = $r['ts'];
                    $grouped[$uid]['in']    = date('H:i:s', $r['ts']);
                }
            } else {
                // Keep latest check-out
                if ($grouped[$uid]['out_ts'] === null || $r['ts'] > $grouped[$uid]['out_ts']) {
                    $grouped[$uid]['out_ts'] = $r['ts'];
                    $grouped[$uid]['out']    = date('H:i:s', $r['ts']);
                }
            }
        }

        foreach ($grouped as &$e) {
            // Duration + decimal hours
            if ($e['in_ts'] && $e['out_ts'] && $e['out_ts'] > $e['in_ts']) {
                $diff       = $e['out_ts'] - $e['in_ts'];
                $e['duration'] = sprintf('%dh%02dm', floor($diff / 3600), floor(($diff % 3600) / 60));
                $e['hours']    = round($diff / 3600, 2);
            }

            // All punches in chronological order (for PDF "Hora" column)
            usort($e['all_ts'], fn($a, $b) => $a['ts'] - $b['ts']);
            $e['times_str']    = implode(' - ', array_map(fn($p) => date('H:i:s', $p['ts']), $e['all_ts']));
            $e['punch_count']  = count($e['all_ts']);

            unset($e['in_ts'], $e['out_ts'], $e['all_ts']);
        }
        unset($e);

        uasort($grouped, fn($a, $b) => strcmp($a['name'], $b['name']));
        return $grouped;
    }

    // ══════════════════════════════════════════════════════════════
    //  DIÁRIO
    // ══════════════════════════════════════════════════════════════

    public function actionDiario()
    {
        $date = Yii::$app->request->get('date', date('Y-m-d'));
        if ($date > date('Y-m-d')) $date = date('Y-m-d');

        // Pass the date so the query filters at DB level — no full-table load
        $allRecords = $this->fetchAllRecords($date, $date);
        $records    = $this->groupByUser($allRecords, $date, $date);

        // Total collaborators still comes from Userinfo (the authoritative list)
        $totalCollaborators = (int) \app\models\Userinfo::find()
            ->where(['!=', 'Deptid', -1])
            ->count();

        $present = count($records);
        $absent  = max(0, $totalCollaborators - $present);

        $this->view->title = 'Relatório Diário';
        return $this->render('diario', compact(
            'date', 'records', 'totalCollaborators', 'present', 'absent'
        ));
    }

    // ══════════════════════════════════════════════════════════════
    //  SEMANAL
    // ══════════════════════════════════════════════════════════════

    public function actionSemanal()
    {
        $weekStart = Yii::$app->request->get('week_start')
                  ?? date('Y-m-d', strtotime('monday this week'));
        $weekEnd   = date('Y-m-d', strtotime($weekStart . ' +6 days'));
        if ($weekEnd > date('Y-m-d')) $weekEnd = date('Y-m-d');

        // Single DB query for the whole week
        $allRecords = $this->fetchAllRecords($weekStart, $weekEnd);

        // Build per-day breakdown
        $days = [];
        $cur  = strtotime($weekStart);
        $end  = strtotime($weekEnd);
        while ($cur <= $end) {
            $d        = date('Y-m-d', $cur);
            $days[$d] = $this->groupByUser($allRecords, $d, $d);
            $cur      = strtotime('+1 day', $cur);
        }

        // Summary per collaborator — still need the master list for "absent" rows
        $userinfos = \app\models\Userinfo::find()
            ->where(['!=', 'Deptid', -1])
            ->orderBy(['Name' => SORT_ASC])
            ->all();

        $summary = [];
        foreach ($userinfos as $u) {
            $uid     = $u->Userid;
            $dayData = [];
            $present = 0;
            foreach ($days as $day => $dayRecs) {
                $dayData[$day] = $dayRecs[$uid] ?? null;
                if (isset($dayRecs[$uid])) $present++;
            }
            $summary[$uid] = [
                'userid'  => $uid,
                'name'    => $u->Name,
                'dept'    => $u->dept->DeptName ?? '—',
                'days'    => $dayData,
                'present' => $present,
                'absent'  => count($days) - $present,
            ];
        }

        $this->view->title = 'Relatório Semanal';
        return $this->render('semanal', compact('weekStart', 'weekEnd', 'days', 'summary'));
    }

    // ══════════════════════════════════════════════════════════════
    //  EXPORT PDF
    // ══════════════════════════════════════════════════════════════

    public function actionExportPdf()
    {
        $type    = Yii::$app->request->get('type', 'diario');
        $ptDays  = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
        $employees = [];

        if ($type === 'diario') {
            $date = Yii::$app->request->get('date', date('Y-m-d'));
            if ($date > date('Y-m-d')) $date = date('Y-m-d');

            $dateFrom = date('d-m-Y', strtotime($date));
            $dateTo   = $dateFrom;

            $allRecords = $this->fetchAllRecords($date, $date);
            $records    = $this->groupByUser($allRecords, $date, $date);

            foreach ($records as $uid => $r) {
                $employees[] = [
                    'id'          => $uid,
                    'name'        => $r['name'],
                    'dept'        => $r['dept'],
                    'total_hours' => $r['hours'],
                    'records'     => [[
                        'date'    => $date,
                        'weekday' => $ptDays[date('w', strtotime($date))],
                        'times'   => $r['times_str'],
                        'count'   => $r['punch_count'],
                        'hours'   => $r['hours'],
                    ]],
                ];
            }

            $filename = 'relatorio_diario_' . $date . '.pdf';

        } else {
            // semanal
            $weekStart = Yii::$app->request->get('week_start')
                      ?? date('Y-m-d', strtotime('monday this week'));
            $weekEnd   = date('Y-m-d', strtotime($weekStart . ' +6 days'));
            if ($weekEnd > date('Y-m-d')) $weekEnd = date('Y-m-d');

            $dateFrom = date('d-m-Y', strtotime($weekStart));
            $dateTo   = date('d-m-Y', strtotime($weekEnd));

            // One DB query for the full week
            $allRecords = $this->fetchAllRecords($weekStart, $weekEnd);

            $userinfos = \app\models\Userinfo::find()
                ->where(['!=', 'Deptid', -1])
                ->orderBy(['Name' => SORT_ASC])
                ->all();

            foreach ($userinfos as $u) {
                $uid        = $u->Userid;
                $empRecords = [];
                $totalHours = 0.0;
                $cur = strtotime($weekStart);
                $end = strtotime($weekEnd);

                while ($cur <= $end) {
                    $d    = date('Y-m-d', $cur);
                    $dayR = $this->groupByUser($allRecords, $d, $d);
                    if (isset($dayR[$uid]) && $dayR[$uid]['hours'] > 0) {
                        $rec = $dayR[$uid];
                        $empRecords[] = [
                            'date'    => $d,
                            'weekday' => $ptDays[date('w', $cur)],
                            'times'   => $rec['times_str'],
                            'count'   => $rec['punch_count'],
                            'hours'   => $rec['hours'],
                        ];
                        $totalHours += $rec['hours'];
                    }
                    $cur = strtotime('+1 day', $cur);
                }

                if (!empty($empRecords)) {
                    $employees[] = [
                        'id'          => $uid,
                        'name'        => $u->Name,
                        'dept'        => $u->dept->DeptName ?? '',
                        'total_hours' => round($totalHours, 2),
                        'records'     => $empRecords,
                    ];
                }
            }

            $filename = 'relatorio_semanal_' . $weekStart . '.pdf';
        }

        if (empty($employees)) {
            Yii::$app->session->setFlash('warning', 'Sem registos para exportar.');
            return $this->redirect(Yii::$app->request->referrer ?: ['reports/diario']);
        }

        // ── mPDF render ───────────────────────────────────────────
        $html = $this->buildReportHtml($employees, $dateFrom, $dateTo);

        try {
            $mpdf = new \Mpdf\Mpdf([
                'mode'              => 'utf-8',
                'format'            => 'A4',
                'margin_top'        => 15,
                'margin_bottom'     => 15,
                'margin_left'       => 18,
                'margin_right'      => 18,
                'default_font_size' => 9,
                'default_font'      => 'dejavusans',
                'tempDir'           => Yii::getAlias('@runtime') . '/mpdf',
            ]);
            $mpdf->WriteHTML($html);
            $pdfContent = $mpdf->Output('', 'S');
        } catch (\Exception $e) {
            Yii::error('mPDF error: ' . $e->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'Erro ao gerar PDF: ' . $e->getMessage());
            return $this->redirect(Yii::$app->request->referrer ?: ['reports/diario']);
        }

        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'application/pdf');
        Yii::$app->response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        Yii::$app->response->headers->set('Content-Length', strlen($pdfContent));
        return $pdfContent;
    }
 
    private function buildReportHtml(array $employees, string $dateFrom, string $dateTo): string
    {
        $css = '
            body { font-family: dejavusans, sans-serif; font-size: 9pt; color: #1a1a1a; }
            .page-title  { font-size: 13pt; font-weight: bold; margin-bottom: 2px; }
            .page-dates  { font-size: 10pt; color: #555555; margin-bottom: 8px; }
            hr { border: none; border-top: 0.5px solid #cccccc; margin: 6px 0; }
            .person-name { font-size: 11pt; font-weight: bold; color: #1a1a2e;
                           margin-top: 10px; margin-bottom: 5px; }
            .person-dept { font-size: 9pt; color: #666666; font-weight: normal; }
            table { width: 100%; border-collapse: collapse; }
            table.header-row th {
                font-size: 8.5pt; font-weight: bold; color: #555555;
                background: #f0f0f5; padding: 4px 4px;
                border-bottom: 0.5px solid #dddddd;
                text-align: left;
            }
            table.header-row th.right { text-align: right; }
            table.records td {
                font-size: 9pt; padding: 3px 4px;
                border-bottom: 0.5px solid #eeeeee;
                text-align: left;
            }
            table.records td.right  { text-align: right; }
            tr.even td { background: #f7f7f7; }
            tr.odd  td { background: #ffffff; }
            .total-line {
                text-align: right; font-size: 9pt; font-weight: bold;
                color: #1a1a2e; margin-top: 6px;
            }
        ';
 
        $parts = [];
 
        foreach ($employees as $idx => $emp) {
            $rows = '';
            foreach ($emp['records'] as $i => $rec) {
                $rowClass = ($i % 2 === 0) ? 'even' : 'odd';
                $hours    = number_format((float)$rec['hours'], 2);
                // Escape special chars
                $times    = htmlspecialchars($rec['times'],   ENT_QUOTES, 'UTF-8');
                $weekday  = htmlspecialchars($rec['weekday'], ENT_QUOTES, 'UTF-8');
                $date     = htmlspecialchars($rec['date'],    ENT_QUOTES, 'UTF-8');
                $count    = (int)$rec['count'];
 
                $rows .= "
                <tr class=\"{$rowClass}\">
                    <td>{$date}</td>
                    <td>{$weekday}</td>
                    <td>{$times}</td>
                    <td class=\"right\">{$count}</td>
                    <td class=\"right\">{$hours}</td>
                </tr>";
            }
 
            $name       = htmlspecialchars($emp['name'], ENT_QUOTES, 'UTF-8');
            $dept       = htmlspecialchars($emp['dept'], ENT_QUOTES, 'UTF-8');
            $id         = htmlspecialchars((string)$emp['id'], ENT_QUOTES, 'UTF-8');
            $totalHours = number_format((float)$emp['total_hours'], 2);
 
            $block = "
            <div class=\"page-title\">Relat&oacute;rio di&aacute;rio de registros</div>
            <div class=\"page-dates\">{$dateFrom} Para {$dateTo}</div>
            <hr>
 
            <table class=\"header-row\">
                <tr>
                    <th style=\"width:16%\">Data</th>
                    <th style=\"width:9%\">Semana</th>
                    <th style=\"width:52%\">Hora</th>
                    <th class=\"right\" style=\"width:9%\">Vezes</th>
                    <th class=\"right\" style=\"width:14%\">Working Time</th>
                </tr>
            </table>
 
            <p class=\"person-name\">{$id} - {$name} &nbsp;<span class=\"person-dept\">{$dept}</span></p>
 
            <table class=\"records\">
                {$rows}
            </table>
 
            <p class=\"total-line\">Total de horas: {$totalHours}</p>
            ";
 
            $parts[] = $block;
        }
 
        // Join pages with mPDF page break tag
        $body = implode('<pagebreak />', $parts);
 
        return "<html><head><style>{$css}</style></head><body>{$body}</body></html>";
    }
}
