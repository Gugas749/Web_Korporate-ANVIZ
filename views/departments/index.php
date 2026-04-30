<?php

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\Pjax;

$this->registerCssFile('@web/css/views-index.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerJsFile('@web/js/main-index.js', ['depends' => [\yii\bootstrap5\BootstrapPluginAsset::class]]);
$this->registerJsFile('@web/js/dept-index.js', ['depends' => [\yii\web\JqueryAsset::class]]);

?>

<div class="content">
    <div class="container-fluid py-4" style="background-color:#f9fafb; min-height:100vh;">
        <!-- NAVIGATION? -->
        <div class="d-flex justify-content-between align-items-center mb-4 px-3">
            <h4 class="fw-bold text-dark">Departamentos</h4>
            <div class="d-flex align-items-center gap-3">
                <!-- Search -->
                <div class="input-group mx-5" style="width:220px;">
                    <?php $form = ActiveForm::begin([
                            'method' => 'get',
                            'action' => ['departments/index'],
                            'options' => ['data' => ['pjax' => true], 'class' => 'd-flex align-items-center w-100'],
                    ]); ?>
                    <input type="text" name="q"
                           class="form-control form-control-sm ps-3 pe-5"
                           placeholder="Search"
                           value="<?= Html::encode($search) ?>"
                           style="border:1px solid #e5e7eb;">
                    <button type="submit" class="input-group-text bg-transparent border-0 text-muted"
                            style="position:absolute; right:10px; top:50%; transform:translateY(-50%);">
                        <i class="fas fa-search"></i>
                    </button>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
        <!-- USER LIST -->
        <div class="row">
            <?php if (!empty($departments)): ?>
                <?php foreach ($departments as $dept): ?>
                    <div class="col-md-4 col-sm-6 mb-3 dept-wrapper">
                        <div class="card bg-dark text-white shadow-sm dept-card h-100">
                            <div class="card-body d-flex flex-column">
                                <!-- TOP: icon + name -->
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-sitemap me-2"></i>
                                    <span class="fw-semibold fs-5">
                                    <?= htmlspecialchars($dept->DeptName) ?>
                                </span>
                                </div>

                                <!-- MIDDLE: info -->
                                <div class="mb-3">
                                    <div><strong>Total de afiliados:</strong> <?= sizeof($dept->userinfo) ?></div>
                                </div>

                                <!-- BOTTOM: actions -->
                                <div class="mt-auto d-flex justify-content-end gap-3">

                                    <button type="button"
                                            class="text-white action-icon btn btn-link p-0"
                                            data-bs-toggle="modal"
                                            data-bs-target="#detailsModal"
                                            data-id="<?= $dept->Deptid ?>">
                                        <i class="fas fa-pen"></i>
                                    </button>

                                    <a href="<?= \yii\helpers\Url::to(['departments/delete', 'id' => $dept->Deptid]) ?>"
                                       class="text-white action-icon"
                                       data-method="post"
                                       data-confirm="Tem certeza?">
                                        <i class="fas fa-trash"></i>
                                    </a>

                                    <a onclick="toggleExpand(this, <?= $dept->Deptid ?>)"
                                       class="text-white action-icon">
                                        <i class="fas fa-arrow-down"></i>
                                    </a>

                                </div>

                                <div class="row aff-users-container"></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center text-muted">
                    Nenhum departamento encontrado.
                </div>
            <?php endif; ?>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="detailsModalLabel">Administração</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-2">
                            <label>Nome do departamento</label>
                            <input id="detailsDeptName" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-bs-target="#detailsModal" href="#detailsModal">Close</button>
                        <button type="button" class="btn btn-primary">Save changes</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    .aff-users-card {
        transition: all 0.2s ease;
        transform: scale(1.01);
        box-shadow: 0 15px 40px rgba(0,0,0,0.1);
    }

    .aff-users-card:hover {
        border-color: #0d6efd;
        transform: translateY(-2px);
    }

    .dept-card{
        transition: all 0.2s ease;
    }

    .dept-card:hover {
        border-color: #0d6efd;
        transform: translateY(-2px);
    }

    /* EXPANDED STATE */
    .dept-wrapper.expanded {
        flex: 0 0 100% !important;
        max-width: 100% !important;
    }

    .dept-wrapper.expanded .dept-card{
        transform: scale(1.01);
        box-shadow: 0 15px 40px rgba(0,0,0,0.4);
    }

    /* hide non-selected cards when one is expanded */
    .row.expanded-mode .dept-wrapper {
        display: none;
    }

    .row.expanded-mode .dept-wrapper.expanded {
        display: block;
        flex: 0 0 100% !important;
        max-width: 100% !important;
    }

    .action-icon {
        opacity: 0.8;
        transition: 0.2s;
    }

    .action-icon:hover {
        opacity: 1;
        color: #0d6efd !important;
        transform: scale(1.15);
    }
</style>

<!-- AJAX URLS -->
<script>
    const getUsersAffiliatedUrl = "<?= \yii\helpers\Url::to(['/departments/get-users-affiliated']) ?>";
    const getDeptDetailUrl = "<?= \yii\helpers\Url::to(['/departments/get-dept-detail']) ?>";
</script>
