<?php

namespace app\controllers;

use app\models\Userinfo;
use Yii;
use yii\web\Controller;

class DashboardController extends Controller
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

    private function apiGet(string $path): ?array
    {
        $base = Yii::$app->params['anvizApiUrl'] ?? 'https://localhost:7236';
        $ch   = curl_init($base . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        ]);
        $res  = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!$res || $code !== 200) return null;
        return json_decode($res, true);
    }

    public function actionIndex()
    {
        // ── Collaborators ────────────────────────────
        $totalCollaborators = (int) Userinfo::find()
            ->where(['!=', 'Deptid', -1])
            ->count();

        // ── Devices ──────────────────────────────────
        $devices      = $this->apiGet('/devices') ?? [];
        $totalDevices = count($devices);

        // ── Records from all devices (for today + weekly chart) ──
        $today   = date('Y-m-d');
        $todayTs = strtotime($today . ' 00:00:00');
        $weekAgoTs = strtotime('-6 days 00:00:00');

        $allRecords     = [];
        $recentActivity = [];

        foreach ($devices as $device) {
            $id   = $device['id'] ?? null;
            if (!$id) continue;
            $recs = $this->apiGet('/devices/' . $id . '/records') ?? [];
            foreach ($recs as $r) {
                $uid  = (string)($r['id']   ?? $r['Id']   ?? null);
                $time = $r['time']           ?? $r['Time'] ?? null;
                $type = $r['type']           ?? $r['Type'] ?? null;
                if (!$uid || !$time) continue;
                $ts = strtotime($time);
                if ($ts === false || $ts < $weekAgoTs) continue;
                $allRecords[] = [
                    'userid' => $uid,
                    'ts'     => $ts,
                    'date'   => date('Y-m-d', $ts),
                    'isIn'   => ((int)$type === 0),
                    'time'   => date('H:i', $ts),
                ];
            }
        }

        // ── Present today (unique users with a record today) ─────
        $todayUsers = [];
        foreach ($allRecords as $r) {
            if ($r['date'] === $today) {
                $todayUsers[$r['userid']] = true;
            }
        }
        $presentToday = count($todayUsers);

        // ── Recent activity feed (last 15, newest first, today only) ─
        $userinfos = Userinfo::find()
            ->where(['!=', 'Deptid', -1])
            ->indexBy('Userid')
            ->all();

        $todayRecs = array_filter($allRecords, fn($r) => $r['date'] === $today);
        usort($todayRecs, fn($a, $b) => $b['ts'] - $a['ts']);

        foreach (array_slice(array_values($todayRecs), 0, 15) as $r) {
            $recentActivity[] = [
                'name'      => $userinfos[$r['userid']]->Name ?? $r['userid'],
                'checktime' => date('Y-m-d H:i:s', $r['ts']),
                'checktype' => $r['isIn'] ? '0' : '1',
            ];
        }

        // ── Weekly chart data (last 7 days) ──────────────────────
        $ptDays    = ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'];
        $weeklyData = ['labels' => [], 'present' => []];

        for ($i = 6; $i >= 0; $i--) {
            $d    = date('Y-m-d', strtotime("-$i days"));
            $seen = [];
            foreach ($allRecords as $r) {
                if ($r['date'] === $d) $seen[$r['userid']] = true;
            }
            $weeklyData['labels'][]  = $ptDays[date('w', strtotime($d))] . ' ' . date('d/m', strtotime($d));
            $weeklyData['present'][] = count($seen);
        }

        $this->view->title = 'Dashboard';

        return $this->render('index', compact(
            'totalCollaborators', 'totalDevices',
            'presentToday', 'recentActivity', 'weeklyData'
        ));
    }
}
