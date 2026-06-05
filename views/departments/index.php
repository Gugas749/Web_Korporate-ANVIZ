<?php

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Url;
use yii\widgets\Pjax;

$this->title = 'Departamentos';
$this->registerCssFile('@web/css/views-index.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerJsFile('@web/js/dept-index.js', ['depends' => [\yii\web\JqueryAsset::class]]);
?>

<div class="content">
    <div class="container-fluid py-4" style="background:#f9fafb; min-height:100vh;">

        <!-- ── Header ─────────────────────────────────────── -->
        <div class="d-flex justify-content-between align-items-center mb-4 px-3 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold text-dark mb-0">Departamentos</h4>
                <small class="text-muted"><?= count($departments) ?> departamento<?= count($departments) !== 1 ? 's' : '' ?> registado<?= count($departments) !== 1 ? 's' : '' ?></small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <!-- Search -->
                <div class="input-group" style="width:220px; position:relative;">
                    <?php $form = ActiveForm::begin([
                        'method'  => 'get',
                        'action'  => ['departments/index'],
                        'options' => ['class' => 'd-flex align-items-center w-100'],
                    ]); ?>
                    <input type="text" name="q"
                        class="form-control form-control-sm ps-3 pe-5"
                        placeholder="Pesquisar departamento..."
                        value="<?= Html::encode($search) ?>"
                        style="border:1px solid #e5e7eb; border-radius:8px;">
                    <button type="submit"
                            class="input-group-text bg-transparent border-0 text-muted"
                            style="position:absolute; right:10px; top:50%; transform:translateY(-50%); z-index:5;">
                        <i class="fas fa-search"></i>
                    </button>
                    <?php ActiveForm::end(); ?>
                </div>
                <!-- New department button -->
                <button type="button"
                        class="btn btn-primary btn-sm fw-semibold"
                        data-bs-toggle="modal"
                        data-bs-target="#createModal"
                        style="white-space:nowrap;">
                    <i class="fas fa-plus me-1"></i>Novo
                </button>
            </div>
        </div>

        <!-- ── Department cards ───────────────────────────── -->
        <div class="row g-3 px-3" id="deptRow">
            <?php if (!empty($departments)): ?>
                <?php
                // Cycle through accent colours for variety
                $accents = [
                    ['#ede9fe','#7c3aed'],
                    ['#e0f2fe','#0284c7'],
                    ['#dcfce7','#16a34a'],
                    ['#fef9c3','#ca8a04'],
                    ['#fee2e2','#dc2626'],
                    ['#f0fdf4','#15803d'],
                    ['#fdf2f8','#a21caf'],
                    ['#fff7ed','#c2410c'],
                ];
                foreach ($departments as $i => $dept):
                    [$accentBg, $accentColor] = $accents[$i % count($accents)];
                    $initial = strtoupper(mb_substr($dept->DeptName, 0, 1));
                    $memberCount = count($dept->userinfo);
                ?>
                <div class="col-xl-3 col-md-4 col-sm-6 dept-wrapper" data-dept-id="<?= $dept->Deptid ?>">
                    <div class="card border-0 shadow-sm dept-card h-100" style="border-radius:16px; overflow:hidden; cursor:default;">

                        <!-- Accent strip -->
                        <div style="height:4px; background:<?= $accentColor ?>;"></div>

                        <div class="card-body p-4">

                            <!-- Header -->
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0 fw-bold text-white"
                                        style="width:44px;height:44px;background:<?= $accentColor ?>;font-size:1.1rem;">
                                        <?= $initial ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size:.95rem; line-height:1.2;">
                                            <?= htmlspecialchars($dept->DeptName) ?>
                                        </div>
                                        <div class="text-muted" style="font-size:.75rem;">ID #<?= $dept->Deptid ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Member count badge -->
                            <div class="mb-3">
                                <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3"
                                    style="background:<?= $accentBg ?>;">
                                    <i class="fas fa-users" style="color:<?= $accentColor ?>;font-size:.85rem;"></i>
                                    <span style="font-size:.82rem;color:<?= $accentColor ?>;font-weight:600;">
                                        <?= $memberCount ?> colaborador<?= $memberCount !== 1 ? 'es' : '' ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Action buttons -->
                            <div class="d-flex align-items-center justify-content-between mt-auto pt-2"
                                style="border-top:1px solid #f3f4f6;">

                                <!-- Expand toggle -->
                                <button type="button"
                                        class="btn btn-sm btn-light expand-btn fw-semibold"
                                        style="font-size:.78rem;"
                                        onclick="toggleExpand(this, <?= $dept->Deptid ?>)">
                                    <i class="fas fa-users me-1"></i>Ver membros
                                </button>

                                <div class="d-flex gap-2">
                                    <!-- Edit -->
                                    <button type="button"
                                            class="btn btn-sm btn-light action-btn"
                                            title="Editar"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editModal"
                                            data-id="<?= $dept->Deptid ?>"
                                            data-name="<?= htmlspecialchars($dept->DeptName) ?>">
                                        <i class="fas fa-pen" style="color:#6366f1;"></i>
                                    </button>
                                    <!-- Delete -->
                                    <a href="<?= Url::to(['departments/delete', 'id' => $dept->Deptid]) ?>"
                                    class="btn btn-sm btn-light action-btn"
                                    title="Eliminar"
                                    data-method="post"
                                    data-confirm="Tem a certeza que quer eliminar este departamento?">
                                        <i class="fas fa-trash" style="color:#dc2626;"></i>
                                    </a>
                                </div>
                            </div>

                            <!-- Expanded members area (hidden by default) -->
                            <div class="aff-users-container mt-3" style="display:none;"></div>

                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-sitemap fa-2x mb-3 d-block" style="opacity:.3;"></i>
                        Nenhum departamento encontrado.
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<!-- ══════════════════════════════════════════════════
     EDIT MODAL
══════════════════════════════════════════════════ -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold text-dark">Editar Departamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <input type="hidden" id="editDeptId">
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-muted">Nome do Departamento</label>
                    <input id="editDeptName" class="form-control" placeholder="Nome...">
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 px-4">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary px-4" id="editSaveBtn"
                        style="background:#6366f1;border:none;">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════
     CREATE MODAL
══════════════════════════════════════════════════ -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold text-dark">Novo Departamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-muted">Nome do Departamento</label>
                    <input id="createDeptName" class="form-control" placeholder="Nome...">
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 px-4">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary px-4" id="createSaveBtn"
                        style="background:#6366f1;border:none;">Criar</button>
            </div>
        </div>
    </div>
</div>

<style>
    .dept-card { transition: transform .2s ease, box-shadow .2s ease; }
    .dept-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.1) !important; }
    .action-btn { transition: transform .15s ease; }
    .action-btn:hover { transform: scale(1.12); }
    .expand-btn.active { background:#6366f1 !important; color:#fff !important; }
</style>

<!-- AJAX URLs -->
<script>
    const getUsersAffiliatedUrl = "<?= Url::to(['/departments/get-users-affiliated']) ?>";
    const getDeptDetailUrl      = "<?= Url::to(['/departments/get-dept-detail']) ?>";
    const updateDeptUrl         = "<?= Url::to(['/departments/update']) ?>";
    const createDeptUrl         = "<?= Url::to(['/departments/create']) ?>";
</script>
