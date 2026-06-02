<?php
/* @var $this               yii\web\View */
/* @var $totalCollaborators int */
/* @var $totalDevices       int */
/* @var $presentToday       int */
/* @var $recentActivity     array  last ~10 attendance records */
/* @var $weeklyData         array  ['labels'=>[], 'present'=>[]] for chart */

use yii\helpers\Url;

$this->title = 'Dashboard';
?>

<div class="container-fluid py-4" style="background:#f9fafb; min-height:100vh;">

    <!-- ── Page title ───────────────────────────────── -->
    <div class="mb-4 px-2">
        <h4 class="fw-bold text-dark mb-0">Dashboard</h4>
        <small class="text-muted">Bem-vindo ao painel de controlo · <?= date('l, d \d\e F \d\e Y') ?></small>
    </div>

    <!-- ── KPI cards ────────────────────────────────── -->
    <div class="row g-3 mb-4 px-2">

        <!-- Total Colaboradores -->
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
                <div class="card-body d-flex align-items-center py-4" style="gap:1rem;">
                    <div class="d-flex align-items-center justify-content-center rounded-3"
                         style="width:56px;height:56px;background:#ede9fe;flex-shrink:0;">
                        <i class="fas fa-users" style="color:#7c3aed;font-size:1.4rem;"></i>
                    </div>
                    <div style="padding-left:0.25rem;">
                        <div class="text-muted small mb-1">Total Colaboradores</div>
                        <div class="fw-bold" style="font-size:2rem;line-height:1;"><?= $totalCollaborators ?></div>
                        <a href="<?= Url::to(['collaborators/index']) ?>"
                           class="text-decoration-none small" style="color:#7c3aed;">
                            Ver lista <i class="fas fa-arrow-right ms-1" style="font-size:.7rem;"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Presentes Hoje -->
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
                <div class="card-body d-flex align-items-center py-4" style="gap:1rem;">
                    <div class="d-flex align-items-center justify-content-center rounded-3"
                         style="width:56px;height:56px;background:#dcfce7;flex-shrink:0;">
                        <i class="fas fa-user-check" style="color:#16a34a;font-size:1.4rem;"></i>
                    </div>
                    <div style="padding-left:0.25rem;">
                        <div class="text-muted small mb-1">Presentes Hoje</div>
                        <div class="fw-bold" style="font-size:2rem;line-height:1;"><?= $presentToday ?></div>
                        <a href="<?= Url::to(['reports/diario']) ?>"
                           class="text-decoration-none small" style="color:#16a34a;">
                            Relatório diário <i class="fas fa-arrow-right ms-1" style="font-size:.7rem;"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dispositivos -->
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
                <div class="card-body d-flex align-items-center py-4" style="gap:1rem;">
                    <div class="d-flex align-items-center justify-content-center rounded-3"
                         style="width:56px;height:56px;background:#fef9c3;flex-shrink:0;">
                        <i class="fas fa-microchip" style="color:#ca8a04;font-size:1.4rem;"></i>
                    </div>
                    <div style="padding-left:0.25rem;">
                        <div class="text-muted small mb-1">Dispositivos</div>
                        <div class="fw-bold" style="font-size:2rem;line-height:1;"><?= $totalDevices ?></div>
                        <a href="<?= Url::to(['devices/index']) ?>"
                           class="text-decoration-none small" style="color:#ca8a04;">
                            Ver dispositivos <i class="fas fa-arrow-right ms-1" style="font-size:.7rem;"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ── Chart + Recent activity ──────────────────── -->
    <div class="row g-3 px-2">

        <!-- Weekly presence chart -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
                <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0">Presenças — últimos 7 dias</h6>
                    <a href="<?= Url::to(['reports/semanal']) ?>"
                       class="btn btn-sm btn-outline-secondary">Ver relatório</a>
                </div>
                <div class="card-body">
                    <canvas id="weeklyChart" style="max-height:260px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent activity feed -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
                <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0">Actividade Recente</h6>
                    <a href="<?= Url::to(['reports/diario']) ?>"
                       class="btn btn-sm btn-outline-secondary">Hoje</a>
                </div>
                <div class="card-body p-0" style="overflow-y:auto;max-height:300px;">
                    <?php if (empty($recentActivity)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-inbox fa-2x mb-2 d-block" style="opacity:.3;"></i>
                            Sem actividade recente.
                        </div>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($recentActivity as $rec): ?>
                                <?php
                                $isIn    = in_array(strtolower((string)($rec['checktype'] ?? '')), ['0','in','i','check-in'], true);
                                $color   = $isIn ? '#16a34a' : '#dc2626';
                                $icon    = $isIn ? 'sign-in-alt' : 'sign-out-alt';
                                $label   = $isIn ? 'Entrada' : 'Saída';
                                $time    = isset($rec['checktime']) ? date('H:i', strtotime($rec['checktime'])) : '—';
                                $name    = $rec['name'] ?? ($rec['userid'] ?? '—');
                                ?>
                                <li class="list-group-item border-0 px-4 py-2">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                                             style="width:32px;height:32px;background:<?= $isIn ? '#dcfce7' : '#fee2e2' ?>;flex-shrink:0;">
                                            <i class="fas fa-<?= $icon ?>" style="color:<?= $color ?>;font-size:.8rem;"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold text-dark small"><?= htmlspecialchars($name) ?></div>
                                            <div class="text-muted" style="font-size:.72rem;"><?= $label ?></div>
                                        </div>
                                        <span class="text-muted small"><?= $time ?></span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('weeklyChart').getContext('2d');

    const labels  = <?= json_encode($weeklyData['labels']  ?? []) ?>;
    const present = <?= json_encode($weeklyData['present'] ?? []) ?>;
    const total   = <?= (int)$totalCollaborators ?>;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Presentes',
                    data: present,
                    backgroundColor: '#6366f1',
                    borderRadius: 8,
                    borderSkipped: false,
                },
                {
                    label: 'Ausentes',
                    data: present.map(v => Math.max(0, total - v)),
                    backgroundColor: '#e5e7eb',
                    borderRadius: 8,
                    borderSkipped: false,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16 } },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y}`
                    }
                }
            },
            scales: {
                x: { stacked: true, grid: { display: false } },
                y: { stacked: true, beginAtZero: true, max: total || 10,
                     ticks: { stepSize: 1 }, grid: { color: '#f3f4f6' } }
            }
        }
    });
});
</script>
