<?php
/* @var $this      yii\web\View */
/* @var $weekStart string  Y-m-d (Monday) */
/* @var $weekEnd   string  Y-m-d (Sunday or today) */
/* @var $days      array   ['Y-m-d' => [userid => record|null]] */
/* @var $summary   array   [userid => ['name','dept','days','present','absent']] */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Relatório Semanal';
$ptDayNames  = ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'];
$prevWeek    = date('Y-m-d', strtotime($weekStart . ' -7 days'));
$nextWeek    = date('Y-m-d', strtotime($weekStart . ' +7 days'));
$isCurrent   = ($weekStart === date('Y-m-d', strtotime('monday this week')));
$dayKeys     = array_keys($days);
$totalCollaborators = count($summary);
$totalPresencas     = array_sum(array_column($summary, 'present'));
$totalAusencias     = array_sum(array_column($summary, 'absent'));
$workDays           = count($dayKeys);
?>

<div class="container-fluid py-4" style="background:#f9fafb; min-height:100vh;">

    <!-- ── Header ──────────────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-4 px-2 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold text-dark mb-0">Relatório Semanal</h4>
            <small class="text-muted">
                <?= date('d/m/Y', strtotime($weekStart)) ?> — <?= date('d/m/Y', strtotime($weekEnd)) ?>
            </small>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="<?= Url::to(['reports/semanal', 'week_start' => $prevWeek]) ?>"
               class="btn btn-sm btn-outline-secondary"><i class="fas fa-chevron-left"></i></a>

            <form method="get" action="index.php" class="d-flex">
                <input type="hidden" name="r" value="reports/semanal">
                <input type="week"
                    name="week_input"
                    value="<?= date('Y-\WW', strtotime($weekStart)) ?>"
                    max="<?= date('Y-\WW') ?>"
                    class="form-control form-control-sm"
                    style="width:160px;"
                    onchange="
                        var parts = this.value.split('-W');
                        var d = new Date(parts[0], 0, 1 + (parseInt(parts[1]) - 1) * 7);
                        d.setDate(d.getDate() - d.getDay() + 1);
                        var iso = d.toISOString().substr(0, 10);
                        window.location.href = 'index.php?r=reports%2Fsemanal&week_start=' + iso;
                    ">
            </form>

            <a href="<?= $isCurrent ? '#' : Url::to(['reports/semanal', 'week_start' => $nextWeek]) ?>"
               class="btn btn-sm btn-outline-secondary <?= $isCurrent ? 'disabled' : '' ?>" style="margin-right:0.5rem;">
                <i class="fas fa-chevron-right"></i></a>

            <a href="<?= Url::to(['reports/semanal']) ?>" class="btn btn-sm btn-primary" style="margin-right:0.5rem;">Esta semana</a>

            <!-- Export PDF -->
            <?php if (!empty($summary)): ?>
            <a href="<?= Url::to(['reports/export-pdf', 'type' => 'semanal', 'week_start' => $weekStart]) ?>"
               class="btn btn-sm btn-danger ms-1">
                <i class="fas fa-file-pdf me-1" style="margin-right:0.5rem;"></i>Exportar PDF
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── Cards ────────────────────────────────── -->
    <div class="row g-3 mb-4 px-2">
        <?php
        $cards = [
            ['label' => 'Colaboradores',    'value' => $totalCollaborators, 'icon' => 'fas fa-users',          'bg' => '#e0f2fe', 'color' => '#0284c7'],
            ['label' => 'Dias analisados',  'value' => $workDays,           'icon' => 'fas fa-calendar-check', 'bg' => '#f3e8ff', 'color' => '#7c3aed'],
            ['label' => 'Total Presenças',  'value' => $totalPresencas,     'icon' => 'fas fa-check-circle',   'bg' => '#dcfce7', 'color' => '#16a34a'],
            ['label' => 'Total Ausências',  'value' => $totalAusencias,     'icon' => 'fas fa-times-circle',   'bg' => '#fee2e2', 'color' => '#dc2626'],
        ];
        foreach ($cards as $card): ?>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-body d-flex align-items-center py-3" style="gap:1rem;">
                    <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0"
                         style="width:48px;height:48px;background:<?= $card['bg'] ?>;">
                        <i class="<?= $card['icon'] ?>" style="color:<?= $card['color'] ?>;font-size:1.2rem;"></i>
                    </div>
                    <div style="padding-left:0.25rem;">
                        <div class="text-muted small"><?= $card['label'] ?></div>
                        <div class="fw-bold fs-4 lh-1 mt-1"><?= $card['value'] ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ── Grid table ────────────────────────────── -->
    <div class="card border-0 shadow-sm mx-2" style="border-radius:16px; overflow:hidden;">
        <div class="card-header bg-white border-0 pt-4 pb-2">
            <h6 class="fw-bold text-dark mb-0">Presenças por Colaborador</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0" style="min-width:700px;">
                    <thead class="text-muted small" style="background:#f8f9fa;">
                        <tr>
                            <th class="ps-4 py-3" style="min-width:200px;">Colaborador</th>
                            <th style="min-width:110px;">Departamento</th>
                            <?php foreach ($dayKeys as $day): ?>
                            <th class="text-center" style="min-width:90px;">
                                <div><?= $ptDayNames[date('w', strtotime($day))] ?></div>
                                <div class="fw-bold text-dark" style="font-size:.9rem;"><?= date('d/m', strtotime($day)) ?></div>
                            </th>
                            <?php endforeach; ?>
                            <th class="text-center" style="min-width:80px;">Presenças</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($summary)): ?>
                        <tr>
                            <td colspan="<?= 3 + count($dayKeys) ?>" class="text-center text-muted py-5">
                                <i class="fas fa-calendar-times fa-2x mb-3 d-block" style="opacity:.35;"></i>
                                Sem dados para esta semana.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($summary as $s): ?>
                        <tr class="border-top">
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                         style="width:32px;height:32px;background:#6366f1;font-size:.75rem;flex-shrink:0;">
                                        <?= strtoupper(mb_substr($s['name'], 0, 1)) ?>
                                    </div>
                                    <div class="fw-semibold text-dark small" style="padding-left:0.75rem;"><?= Html::encode($s['name']) ?></div>
                                </div>
                            </td>
                            <td class="text-muted small"><?= Html::encode($s['dept']) ?></td>

                            <?php foreach ($dayKeys as $day): ?>
                            <?php $rec = $s['days'][$day] ?? null; ?>
                            <td class="text-center py-2">
                                <?php if ($rec && $rec['hours'] > 0): ?>
                                    <div class="d-flex flex-column align-items-center gap-1">
                                        <span class="badge rounded-pill"
                                              style="background:#dcfce7;color:#15803d;font-size:.7rem;">
                                            <?= Html::encode($rec['in'] ?? '?') ?>
                                            <i class="fas fa-arrow-right mx-1" style="font-size:.6rem;"></i>
                                            <?= Html::encode($rec['out'] ?? '?') ?>
                                        </span>
                                        <span class="text-muted" style="font-size:.68rem;"><?= $rec['duration'] ?></span>
                                    </div>
                                <?php else: ?>
                                    <span style="color:#e5e7eb;font-size:1rem;">●</span>
                                <?php endif; ?>
                            </td>
                            <?php endforeach; ?>

                            <td class="text-center">
                                <?php
                                $pct   = $workDays > 0 ? round($s['present'] / $workDays * 100) : 0;
                                $color = $pct >= 80 ? '#16a34a' : ($pct >= 50 ? '#d97706' : '#dc2626');
                                ?>
                                <span class="fw-bold" style="color:<?= $color ?>;">
                                    <?= $s['present'] ?>/<?= $workDays ?>
                                </span>
                                <div class="progress mt-1 mx-2" style="height:4px;border-radius:99px;">
                                    <div class="progress-bar" role="progressbar"
                                         style="width:<?= $pct ?>%;background:<?= $color ?>;"></div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>