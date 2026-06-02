<?php
/* @var $this        yii\web\View */
/* @var $devices     array|null   from /devices/stats  */
/* @var $search      string|null */
/* @var $detailDevice mixed */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap5\ActiveForm;

$this->title = 'Dispositivos';

// Map BiometricType int → readable label
$biometricLabels = [
    0 => 'Desconhecido',
    1 => 'Impressão Digital',
    2 => 'Cartão',
    3 => 'Facial',
    4 => 'Digital + Cartão',
    5 => 'Facial + Cartão',
];
?>

<div class="content">
    <div class="container-fluid py-4" style="background:#f9fafb; min-height:100vh;">

        <!-- ── Header ─────────────────────────────────────── -->
        <div class="d-flex justify-content-between align-items-center mb-4 px-3">
            <div>
                <h4 class="fw-bold text-dark mb-0">Dispositivos</h4>
                <small class="text-muted">Estado dos leitores biométricos Anviz</small>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="input-group" style="width:220px; position:relative;">
                    <?php $form = ActiveForm::begin([
                        'method'  => 'get',
                        'action'  => ['devices/index'],
                        'options' => ['class' => 'd-flex align-items-center w-100'],
                    ]); ?>
                    <input type="text" name="q"
                        class="form-control form-control-sm ps-3 pe-5"
                        placeholder="Pesquisar..."
                        value="<?= Html::encode($search ?? '') ?>"
                        style="border:1px solid #e5e7eb; border-radius:8px;">
                    <button type="submit"
                            class="input-group-text bg-transparent border-0 text-muted"
                            style="position:absolute; right:10px; top:50%; transform:translateY(-50%); z-index:5;">
                        <i class="fas fa-search"></i>
                    </button>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>

        <!-- ── Summary bar ────────────────────────────────── -->
        <?php if (!empty($devices)): ?>
        <?php
        $totalUsers   = array_sum(array_column($devices, 'userAmount'));
        $totalRecords = array_sum(array_column($devices, 'allRecordAmount'));
        $totalNew     = array_sum(array_column($devices, 'newRecordAmount'));
        $summaryCards = [
            ['label' => 'Dispositivos',       'value' => count($devices),  'icon' => 'fas fa-microchip',     'bg' => '#ede9fe', 'color' => '#7c3aed'],
            ['label' => 'Utilizadores totais','value' => $totalUsers,      'icon' => 'fas fa-users',          'bg' => '#e0f2fe', 'color' => '#0284c7'],
            ['label' => 'Registos totais',    'value' => $totalRecords,    'icon' => 'fas fa-database',       'bg' => '#dcfce7', 'color' => '#16a34a'],
            ['label' => 'Novos registos',     'value' => $totalNew,        'icon' => 'fas fa-bolt',           'bg' => '#fef9c3', 'color' => '#ca8a04'],
        ];
        ?>
        <div class="row g-3 mb-4 px-3">
            <?php foreach ($summaryCards as $c): ?>
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm" style="border-radius:14px;">
                    <div class="card-body d-flex align-items-center py-3" style="gap:1rem;">
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                            style="width:48px;height:48px;background:<?= $c['bg'] ?>;">
                            <i class="<?= $c['icon'] ?>" style="color:<?= $c['color'] ?>;font-size:1.2rem;"></i>
                        </div>
                        <div style="padding-left:.25rem;">
                            <div class="text-muted small"><?= $c['label'] ?></div>
                            <div class="fw-bold fs-4 lh-1 mt-1"><?= number_format($c['value']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- ── Device cards ───────────────────────────────── -->
        <div class="row g-3 px-3">
            <?php if (!empty($devices)): ?>
                <?php foreach ($devices as $i => $dev):
                    // Apply search filter client-side fallback (controller can also filter)
                    $ip           = $dev['ipAddress']            ?? '—';
                    $bioType      = (int)($dev['deviceBiometricType'] ?? 0);
                    $bioLabel     = $biometricLabels[$bioType]   ?? 'Desconhecido';
                    $userAmt      = (int)($dev['userAmount']          ?? 0);
                    $fpAmt        = (int)($dev['fingerPrintAmount']   ?? 0);
                    $cardAmt      = (int)($dev['cardAmount']          ?? 0);
                    $pwdAmt       = (int)($dev['passwordAmount']      ?? 0);
                    $allRec       = (int)($dev['allRecordAmount']     ?? 0);
                    $newRec       = (int)($dev['newRecordAmount']     ?? 0);

                    // Colour accent based on biometric type
                    $accents = [
                        0 => ['#f3f4f6','#6b7280'],
                        1 => ['#ede9fe','#7c3aed'],
                        2 => ['#e0f2fe','#0284c7'],
                        3 => ['#dcfce7','#16a34a'],
                        4 => ['#fef9c3','#ca8a04'],
                        5 => ['#fee2e2','#dc2626'],
                    ];
                    [$accentBg, $accentColor] = $accents[$bioType] ?? $accents[0];

                    // Credential breakdown percentages for mini bar
                    $total = max(1, $fpAmt + $cardAmt + $pwdAmt);
                ?>
                <div class="col-xl-4 col-md-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius:16px; overflow:hidden;">

                        <!-- Card top accent strip -->
                        <div style="height:4px; background:<?= $accentColor ?>;"></div>

                        <div class="card-body p-4">

                            <!-- Header row -->
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                                        style="width:44px;height:44px;background:<?= $accentBg ?>; margin-right:4px;">
                                        <i class="fas fa-microchip" style="color:<?= $accentColor ?>;font-size:1.1rem;"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">Dispositivo #<?= htmlspecialchars((string)$dev['id']) ?></div>
                                        <div class="text-muted small"><?= htmlspecialchars($ip) ?></div>
                                    </div>
                                </div>
                                <!-- Online badge -->
                                <span class="badge rounded-pill"
                                    style="background:#dcfce7;color:#15803d;font-size:.72rem;font-weight:600;">
                                    <i class="fas fa-circle" style="font-size:.5rem; margin-right:4px;"></i>Online
                                </span>
                            </div>

                            <!-- Biometric type badge -->
                            <div class="mb-3">
                                <span class="badge rounded-pill px-3 py-1"
                                    style="background:<?= $accentBg ?>;color:<?= $accentColor ?>;font-size:.78rem;">
                                    <i class="fas fa-fingerprint" style="margin-right:5px;"></i><?= htmlspecialchars($bioLabel) ?>
                                </span>
                            </div>

                            <!-- Stats grid -->
                            <div class="row g-2 mb-3">
                                <?php
                                $stats = [
                                    ['label' => 'Utilizadores', 'value' => $userAmt,  'icon' => 'fas fa-users'],
                                    ['label' => 'Registos',     'value' => $allRec,   'icon' => 'fas fa-database'],
                                    ['label' => 'Novos',        'value' => $newRec,   'icon' => 'fas fa-bolt'],
                                    ['label' => 'Impressões',   'value' => $fpAmt,    'icon' => 'fas fa-fingerprint'],
                                    ['label' => 'Cartões',      'value' => $cardAmt,  'icon' => 'fas fa-credit-card'],
                                    ['label' => 'Passwords',    'value' => $pwdAmt,   'icon' => 'fas fa-key'],
                                ];
                                foreach ($stats as $s): ?>
                                <div class="col-4">
                                    <div class="p-2 text-center rounded-3" style="background:#f9fafb;">
                                        <div class="text-muted mb-1"
                                            style="font-size:.7rem; display:flex; align-items:center; justify-content:center; gap:4px;">
                                            <i class="<?= $s['icon'] ?>"></i>
                                            <span><?= $s['label'] ?></span>
                                        </div>
                                        <div class="fw-bold text-dark" style="font-size:1.05rem;line-height:1;">
                                            <?= number_format($s['value']) ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Credential breakdown bar -->
                            <div class="mb-1 mt-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted" style="font-size:.72rem;">Distribuição de credenciais</span>
                                </div>
                                <div class="d-flex rounded-pill overflow-hidden" style="height:7px;background:#f3f4f6;gap:2px;">
                                    <?php if ($fpAmt > 0): ?>
                                    <div style="width:<?= round($fpAmt/$total*100) ?>%;background:#7c3aed;" title="Impressões digitais"></div>
                                    <?php endif; ?>
                                    <?php if ($cardAmt > 0): ?>
                                    <div style="width:<?= round($cardAmt/$total*100) ?>%;background:#0284c7;" title="Cartões"></div>
                                    <?php endif; ?>
                                    <?php if ($pwdAmt > 0): ?>
                                    <div style="width:<?= round($pwdAmt/$total*100) ?>%;background:#ca8a04;" title="Passwords"></div>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex gap-3 mt-1">
                                    <span style="font-size:.68rem;color:#7c3aed;"><i class="fas fa-circle" style="font-size:.5rem; margin-right:3px;"></i>Digital</span>
                                    <span style="font-size:.68rem;color:#0284c7;margin-left:1rem;"><i class="fas fa-circle" style="font-size:.5rem; margin-right:3px;"></i>Cartão</span>
                                    <span style="font-size:.68rem;color:#ca8a04;margin-left:1rem;"><i class="fas fa-circle" style="font-size:.5rem; margin-right:3px;"></i>Password</span>
                                </div>
                            </div>

                            <!-- New records indicator -->
                            <?php if ($newRec > 0): ?>
                            <div class="mt-3 p-2 rounded-3 d-flex align-items-center gap-2"
                                style="background:#fef9c3;">
                                <i class="fas fa-bolt" style="color:#ca8a04;font-size:.85rem; margin-right:2px;"></i>
                                <span style="font-size:.78rem;color:#854d0e;font-weight:600;">
                                    <?= number_format($newRec) ?> novo<?= $newRec !== 1 ? 's' : '' ?> registo<?= $newRec !== 1 ? 's' : '' ?> por sincronizar
                                </span>
                            </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-microchip fa-2x mb-3 d-block" style="opacity:.3;"></i>
                        <?php if ($devices === null): ?>
                            Não foi possível ligar ao AnvizWebSDK. Verifique se o serviço está em execução.
                        <?php else: ?>
                            Nenhum dispositivo encontrado.
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>