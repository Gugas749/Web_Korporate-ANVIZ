<?php
/* @var $this               yii\web\View */
/* @var $date               string   Y-m-d */
/* @var $records            array    grouped by userid */
/* @var $totalCollaborators int */
/* @var $present            int */
/* @var $absent             int */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Relatório Diário';
$prevDate = date('Y-m-d', strtotime($date . ' -1 day'));
$nextDate = date('Y-m-d', strtotime($date . ' +1 day'));
$isToday  = ($date === date('Y-m-d'));
$ptDays   = ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'];
?>

<div class="container-fluid py-4" style="background:#f9fafb; min-height:100vh;">

    <!-- ── Header ──────────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-4 px-2 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold text-dark mb-0">Relatório Diário</h4>
            <small class="text-muted">Registo de presenças por dia</small>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <!-- Date navigator -->
            <a href="<?= Url::to(['reports/diario', 'date' => $prevDate]) ?>"
               class="btn btn-sm btn-outline-secondary"><i class="fas fa-chevron-left"></i></a>

            <form method="get" action="index.php" class="d-flex">
                <input type="hidden" name="r" value="reports/diario">
                <input type="date"
                    name="date"
                    value="<?= Html::encode($date) ?>"
                    max="<?= date('Y-m-d') ?>"
                    class="form-control form-control-sm"
                    style="width:160px;"
                    onchange="this.form.submit()">
            </form>

            <a href="<?= $isToday ? '#' : Url::to(['reports/diario', 'date' => $nextDate]) ?>"
               class="btn btn-sm btn-outline-secondary <?= $isToday ? 'disabled' : '' ?>" style="margin-right:0.5rem;">
                <i class="fas fa-chevron-right"></i></a>

            <a href="<?= Url::to(['reports/diario', 'date' => date('Y-m-d')]) ?>"
               class="btn btn-sm btn-primary" style="margin-right:0.5rem;">Hoje</a>

            <!-- Export PDF -->
            <?php if (!empty($records)): ?>
            <a href="<?= Url::to(['reports/export-pdf', 'type' => 'diario', 'date' => $date]) ?>"
               class="btn btn-sm btn-danger ms-1">
                <i class="fas fa-file-pdf me-1" style="margin-right:0.5rem;"></i>Exportar PDF
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── Cards ──────────────────────────── -->
    <div class="row g-3 mb-4 px-2">
        <?php
        $cards = [
            ['label' => 'Total Colaboradores', 'value' => $totalCollaborators, 'icon' => 'fas fa-users',       'bg' => '#e0f2fe', 'color' => '#0284c7'],
            ['label' => 'Presentes',            'value' => $present,            'icon' => 'fas fa-user-check', 'bg' => '#dcfce7', 'color' => '#16a34a'],
            ['label' => 'Ausentes',             'value' => $absent,             'icon' => 'fas fa-user-times', 'bg' => '#fee2e2', 'color' => '#dc2626'],
        ];
        foreach ($cards as $card): ?>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="border-radius:14px;">
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

    <!-- ── Table ──────────────────────────────────── -->
    <div class="card border-0 shadow-sm mx-2" style="border-radius:16px;">
        <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0">
                <?= $ptDays[date('w', strtotime($date))] ?>, <?= date('d/m/Y', strtotime($date)) ?>
            </h6>
            <?php if (!empty($records)): ?>
                <span class="badge bg-success rounded-pill"><?= $present ?> presente(s)</span>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php if (empty($records)): ?>
                <div class="text-center text-muted py-5">
                    <i class="fas fa-calendar-times fa-2x mb-3 d-block" style="opacity:.35;"></i>
                    Sem registos de presença para <?= date('d/m/Y', strtotime($date)) ?>.
                </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle table-hover">
                    <thead class="text-muted small bg-light">
                        <tr>
                            <th class="ps-3">Colaborador</th>
                            <th>Departamento</th>
                            <th class="text-center">Entrada</th>
                            <th class="text-center">Saída</th>
                            <th class="text-center">Horas</th>
                            <th class="text-center">Registos</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($records as $r): ?>
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                         style="width:34px;height:34px;background:#6366f1;font-size:.8rem;flex-shrink:0;">
                                        <?= strtoupper(mb_substr($r['name'], 0, 1)) ?>
                                    </div>
                                    <div style="padding-left:0.5rem;">
                                        <div class="fw-semibold text-dark small"><?= Html::encode($r['name']) ?></div>
                                        <div class="text-muted" style="font-size:.7rem;">#<?= Html::encode($r['userid']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-muted small"><?= Html::encode($r['dept']) ?></td>
                            <td class="text-center">
                                <?php if ($r['in']): ?>
                                    <span class="badge rounded-pill" style="background:#dcfce7;color:#15803d;font-weight:600;">
                                        <i class="fas fa-sign-in-alt me-1"></i><?= Html::encode($r['in']) ?>
                                    </span>
                                <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($r['out']): ?>
                                    <span class="badge rounded-pill" style="background:#fee2e2;color:#b91c1c;font-weight:600;">
                                        <i class="fas fa-sign-out-alt me-1"></i><?= Html::encode($r['out']) ?>
                                    </span>
                                <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                            </td>
                            <td class="text-center fw-semibold small">
                                <?= $r['hours'] > 0 ? number_format($r['hours'], 2) . 'h' : '—' ?>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill bg-secondary"><?= $r['punch_count'] ?>x</span>
                            </td>
                            <td class="text-center">
                                <?php
                                if ($r['in'] && $r['out']) echo '<span class="badge bg-success rounded-pill">Completo</span>';
                                elseif ($r['in'])          echo '<span class="badge rounded-pill" style="background:#fef9c3;color:#854d0e;">Em curso</span>';
                                else                       echo '<span class="badge bg-secondary rounded-pill">Só saída</span>';
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>
