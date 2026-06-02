<?php
/* @var $this       yii\web\View */
/* @var $users      app\models\Userinfo[] */
/* @var $search     string|null */
/* @var $detailUser app\models\Userinfo|null */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Url;
use yii\widgets\Pjax;

$this->title = 'Colaboradores';
$this->registerCssFile('@web/css/views-index.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
?>

<div class="content">
    <div class="container-fluid py-4" style="background:#f9fafb; min-height:100vh;">

        <?php Pjax::begin(['id' => 'collaboratorsTable', 'timeout' => 5000, 'enablePushState' => false]); ?>

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 px-3">
            <h4 class="fw-bold text-dark">Colaboradores</h4>
            <div class="d-flex align-items-center gap-3">
                <div class="input-group mx-5" style="width:240px; position:relative;">
                    <?php $form = ActiveForm::begin([
                        'method'  => 'get',
                        'action'  => ['collaborators/index'],
                        'options' => ['data' => ['pjax' => true], 'class' => 'd-flex align-items-center w-100'],
                    ]); ?>
                    <input type="text" name="q"
                           class="form-control form-control-sm ps-3 pe-5"
                           placeholder="Pesquisar colaborador..."
                           value="<?= Html::encode($search) ?>"
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

        <!-- Table card -->
        <div class="card shadow-sm border-0 mx-3" style="border-radius:16px;">
            <div class="card-body">
                <h6 class="fw-bold text-secondary mb-3">
                    Total: <?= count($users) ?> colaborador<?= count($users) !== 1 ? 'es' : '' ?>
                </h6>
                <div class="table-responsive">
                    <table class="table align-middle table-hover">
                        <thead class="text-muted small">
                            <tr>
                                <th>Referência</th>
                                <th>Nome</th>
                                <th>Cargo</th>
                                <th>Departamento</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td class="text-muted small"><?= htmlspecialchars($user->Userid) ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                                 style="width:34px;height:34px;background:#6366f1;font-size:.8rem;flex-shrink:0;">
                                                <?= strtoupper(mb_substr($user->Name ?? '?', 0, 1)) ?>
                                            </div>
                                            <span class="fw-semibold"><?= htmlspecialchars($user->Name ?? '—') ?></span>
                                        </div>
                                    </td>
                                    <td class="text-muted small"><?= htmlspecialchars($user->Duty ?? '—') ?></td>
                                    <td class="text-muted small"><?= htmlspecialchars($user->dept->DeptName ?? 'N/A') ?></td>
                                    <td>
                                        <?= Html::a('Ver Detalhes', ['collaborators/index', 'id' => $user->Userid],
                                            ['class' => 'btn btn-outline-primary btn-sm fw-semibold shadow-sm']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    <i class="fas fa-users-slash fa-2x mb-2 d-block" style="opacity:.3;"></i>
                                    Nenhum colaborador encontrado.
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php Pjax::end(); ?>

        <!-- ══════════════════════════════════════════
             DETAIL PANEL
        ══════════════════════════════════════════ -->
        <?php if ($detailUser): ?>
        <div id="detailPanel"
             style="position:fixed; top:0; right:0; width:460px; height:100vh;
                    background:#fff; z-index:1050; overflow-y:auto;
                    box-shadow:-4px 0 24px rgba(0,0,0,.12);
                    transform:translateX(100%); transition:transform .3s ease;">
            <div class="p-4">

                <!-- Panel header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="fw-bold text-dark mb-0">Detalhes do Colaborador</h5>
                        <small class="text-muted">#<?= htmlspecialchars($detailUser->Userid) ?></small>
                    </div>
                    <a href="<?= Url::to(['collaborators/index', 'q' => $search]) ?>"
                       class="btn btn-sm btn-light">
                        <i class="fas fa-times"></i>
                    </a>
                </div>

                <!-- Avatar + name -->
                <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded-3"
                     style="background:#f8f7ff;">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                         style="width:52px;height:52px;background:#6366f1;font-size:1.3rem;flex-shrink:0;">
                        <?= strtoupper(mb_substr($detailUser->Name ?? '?', 0, 1)) ?>
                    </div>
                    <div>
                        <div class="fw-bold text-dark"><?= htmlspecialchars($detailUser->Name ?? '—') ?></div>
                        <div class="text-muted small"><?= htmlspecialchars($detailUser->Duty ?? 'Sem cargo') ?></div>
                        <div class="text-muted small"><?= htmlspecialchars($detailUser->dept->DeptName ?? '—') ?></div>
                    </div>
                </div>

                <!-- Info rows -->
                <?php
                $fields = [
                    ['label' => 'Código',          'icon' => 'id-badge',      'value' => $detailUser->UserCode],
                    ['label' => 'Sexo',             'icon' => 'venus-mars',    'value' => $detailUser->Sex],
                    ['label' => 'Data Nascimento',  'icon' => 'birthday-cake', 'value' => $detailUser->Birthday
                        ? date('d/m/Y', strtotime($detailUser->Birthday)) : null],
                    ['label' => 'Data Admissão',    'icon' => 'calendar-plus', 'value' => $detailUser->EmployDate
                        ? date('d/m/Y', strtotime($detailUser->EmployDate)) : null],
                    ['label' => 'Telemóvel',        'icon' => 'mobile-alt',    'value' => $detailUser->Mobile],
                    ['label' => 'Telefone',         'icon' => 'phone',         'value' => $detailUser->Telephone],
                    ['label' => 'Morada',           'icon' => 'map-marker-alt','value' => $detailUser->Address],
                    ['label' => 'Habilitações',     'icon' => 'graduation-cap','value' => $detailUser->Educated],
                    ['label' => 'Especialidade',    'icon' => 'star',          'value' => $detailUser->Specialty],
                    ['label' => 'Nº Cartão',        'icon' => 'credit-card',   'value' => $detailUser->CardNum],
                    ['label' => 'Observações',      'icon' => 'sticky-note',   'value' => $detailUser->Remark],
                ];
                ?>
                <div class="row g-2">
                    <?php foreach ($fields as $f):
                        if (empty($f['value'])) continue;
                    ?>
                        <div class="col-12">
                            <div class="d-flex align-items-start gap-2 p-2 rounded-2"
                                 style="background:#fafafa; border:1px solid #f3f4f6;">
                                <i class="fas fa-<?= $f['icon'] ?> mt-1 text-muted" style="width:14px;"></i>
                                <div>
                                    <div class="text-muted" style="font-size:.72rem;"><?= $f['label'] ?></div>
                                    <div class="fw-semibold small text-dark"><?= htmlspecialchars($f['value']) ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Attendance flags -->
                <div class="mt-3 d-flex gap-2 flex-wrap">
                    <?php if ($detailUser->IsAtt): ?>
                        <span class="badge rounded-pill bg-success">Regista Presença</span>
                    <?php endif; ?>
                    <?php if ($detailUser->Isovertime): ?>
                        <span class="badge rounded-pill bg-warning text-dark">Horas Extra</span>
                    <?php endif; ?>
                    <?php if ($detailUser->Isrest): ?>
                        <span class="badge rounded-pill bg-secondary">Descanso</span>
                    <?php endif; ?>
                </div>

                <!-- Actions -->
                <div class="mt-4 d-flex gap-2">
                    <a href="<?= Url::to(['reports/diario', 'date' => date('Y-m-d')]) ?>"
                       class="btn btn-outline-primary btn-sm flex-grow-1">
                        <i class="fas fa-calendar-day me-1"></i> Relatório Hoje
                    </a>
                    <a href="<?= Url::to(['collaborators/index', 'q' => $search]) ?>"
                       class="btn btn-light btn-sm">Fechar</a>
                </div>

            </div>
        </div>

        <!-- Overlay -->
        <div id="overlay"
             style="position:fixed;top:0;left:0;width:100%;height:100%;
                    background:rgba(0,0,0,.4);z-index:1049;display:none;"
             onclick="window.location.href='<?= Url::to(['collaborators/index', 'q' => $search]) ?>'">
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const panel   = document.getElementById('detailPanel');
            const overlay = document.getElementById('overlay');
            overlay.style.display = 'block';
            document.body.style.overflow = 'hidden';
            requestAnimationFrame(() => {
                panel.style.transform = 'translateX(0)';
            });
        });
        </script>
        <?php else: ?>
        <div id="overlay" style="display:none;"></div>
        <?php endif; ?>

    </div>
</div>
