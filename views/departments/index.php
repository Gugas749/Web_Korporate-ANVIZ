<?php

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\Pjax;

$this->registerCssFile('@web/css/views-index.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerJsFile('@web/js/main-index.js', ['depends' => [\yii\bootstrap5\BootstrapPluginAsset::class]]);
$this->registerJsFile('@web/js/user-index-form.js', ['depends' => [\yii\web\JqueryAsset::class]]);

?>

<div class="content">
    <div class="container-fluid py-4" style="background-color:#f9fafb; min-height:100vh;">
        <?php Pjax::begin([
                'id' => 'metersTable',
                'timeout' => 5000,
                'enablePushState' => false, // important
        ]); ?>
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
                    <div class="col-md-4 col-sm-6 mb-3">
                        <div class="card card-outline card-primary h-100 shadow-sm">

                            <div class="card-header">
                                <h3 class="card-title fw-bold">
                                    <?= htmlspecialchars($dept->DeptName) ?>
                                </h3>
                            </div>

                            <div class="card-body">
                                <p class="mb-1">
                                    <strong>Referência:</strong>
                                    <?= htmlspecialchars($dept->Deptid) ?>
                                </p>

                                <p class="mb-1">
                                    <strong>Total de afiliados:</strong>
                                    <?= htmlspecialchars(sizeof($dept->userinfo) ?? 'N/A') ?>
                                </p>
                            </div>

                            <div class="card-footer text-end">
                                <?= Html::a('Ver Detalhes',
                                        ['user/index', 'id' => $dept->Deptid],
                                        [
                                                'class' => 'btn btn-outline-primary btn-sm fw-semibold'
                                        ]
                                ) ?>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center text-muted">
                    Nenhum utilizador encontrado.
                </div>
            <?php endif; ?>
        </div>
        <?php Pjax::end(); ?>

        <!-- DETAIL PANEL -->
        <?php if ($detailDept): ?>
            <div id="detailPanel" class="detail-panel bg-white shadow show">
                <div class="modal-content border-0 shadow-lg rounded-4 p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold text-dark mb-0">Detalhes do Utilizador</h5>
                        <button type="button" class="closeDetailPanel btn btn-sm btn-light">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <?php $form = \yii\widgets\ActiveForm::begin([
                            'id' => 'update-user-form',
                            'action' => ['update', 'id' => $detailDept->id],
                            'method' => 'post',
                    ]); ?>

                    <!-- STATUS BADGE -->
                    <div class="mb-4">
                        <?php
                        $statusClass = $statusClasses[$detailUser->status ?? 10] ?? 'bg-secondary';
                        $statusText = $statusOptions[$detailUser->status ?? 10] ?? 'DESCONHECIDO';
                        ?>
                        <span id="user-status-badge" class="badge <?= $statusClass ?> px-3 py-2"><?= $statusText ?></span>
                    </div>

                    <?php
                    $profile = $detailUser->userprofile ?? new Userprofile();
                    $techInfo = null;
                    $isTechnician = false;
                    if($detailUser->technicianinfos !== null){
                        $techInfo = $detailUser->technicianinfos;
                        $isTechnician = true;
                    }
                    $enterpriseList = ArrayHelper::map(Enterprise::find()->all(), 'id', 'name');
                    $selectedValue = $isTechnician ? '1' : '0';
                    ?>

                    <!-- TECNICO STUFF -->
                    <div class="row g-3 mb-3 align-items-end">
                        <div class="col-md-4">
                            <?= $form->field($detailUser, 'technicianinfos')->dropDownList(
                                    ['0' => 'Morador', '1' => 'Técnico'],
                                    ['options' => [$selectedValue => ['Selected' => true]], 'id' => 'user-type-dropdown']
                            )->label('Tipo de Utilizador') ?>
                        </div>
                        <?php
                        $techInfoModel = new \common\models\Technicianinfo();
                        if($isTechnician){
                            $techInfoModel = $techInfo;
                        }
                        ?>

                        <div class="col-md-4 professional-field <?= $isTechnician ? '' : 'hidden' ?>">
                            <?= $form->field($techInfoModel, 'enterpriseID')->dropDownList(
                                    $enterpriseList,
                                    ['prompt' => 'Selecione a empresa']
                            )->label('Empresa Associada') ?>
                        </div>

                        <div class="col-md-4 professional-field <?= $isTechnician ? '' : 'hidden' ?>">
                            <?= $form->field($techInfoModel, 'profissionalCertificateNumber')->textInput()->label('Nº Certificado Profissional') ?>
                        </div>

                    </div>

                    <!-- RESTO DOS CAMPOS -->
                    <div class="row g-1">
                        <div class="col-md-2"><?= $form->field($detailUser, 'id')->textInput(['readonly' => true])->label('Referência') ?></div>
                        <div class="col-md-4"><?= $form->field($profile, 'name')->textInput(['value' => $profile->name ?? 'N/A'])->label('Nome') ?></div>
                        <div class="col-md-3"><?= $form->field($detailUser, 'username')->textInput()->label('Username') ?></div>
                        <div class="col-md-4"><?= $form->field($profile, 'birthDate')->input('date', ['value' => $profile->birthDate ? date('Y-m-d', strtotime($profile->birthDate)) : null])->label('Data de Nascimento') ?></div>
                        <div class="col-md-6"><?= $form->field($detailUser, 'email')->textInput()->label('Email') ?></div>
                        <div class="col-md-6"><?= $form->field($profile, 'address')->textInput(['value' => $profile->address ?? 'N/A'])->label('Morada') ?></div>
                        <div class="col-md-4"><?= $form->field($detailUser, 'status')->dropDownList($statusOptions, ['id' => 'user-status-dropdown'])->label('Estado') ?></div>
                        <div class="col-md-5"><?= $form->field($detailUser, 'created_at')->textInput(['value' => Yii::$app->formatter->asDate($detailUser->created_at), 'readonly' => true])->label('Data de Registo') ?></div>
                        <div class="col-md-5"><?= $form->field($detailUser, 'updated_at')->textInput(['value' => Yii::$app->formatter->asDate($detailUser->updated_at), 'readonly' => true])->label('Última Atualização') ?></div>
                    </div>

                    <div class="d-flex justify-content-end mt-4 gap-2">
                        <button type="button" class="closeDetailPanel btn btn-light px-4">Fechar</button>
                        <?= Html::submitButton('Editar', ['class' => 'btn btn-primary px-4 py-2', 'style' => 'background-color:#4f46e5; border:none;']) ?>
                        <?php \yii\widgets\ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <!--ATIVAR O DETAIL PANEL -->
        <?php if ($detailDept): ?>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const detailPanel = document.getElementById('detailPanel');
                    const overlay = document.getElementById('overlay');

                    overlay.style.display = 'block';
                    detailPanel.style.display = 'block';
                    document.body.style.overflow = 'hidden';

                    requestAnimationFrame(() => {
                        detailPanel.classList.add('show');
                    });
                });
            </script>
        <?php endif; ?>
        <!-- OVERLAY -->
        <div id="overlay"></div>
    </div>
</div>

<script>
    document.addEventListener('click', function(event) {
        const target = event.target;

        // Abrir Right Panel
        if (target.closest('[data-toggle="right-panel"]')) {
            const panel = document.getElementById('rightPanel');
            if (!panel) return;

            let overlay = document.getElementById('overlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.id = 'overlay';
                overlay.style.cssText = `
                position:fixed;
                top:0;
                left:0;
                width:100%;
                height:100%;
                background:rgba(0,0,0,0.5);
                z-index:1049;
                display:none;
            `;
                document.body.appendChild(overlay);
            }

            panel.style.display = 'block';
            overlay.style.display = 'block';
            document.body.style.overflow = 'hidden';
            return;
        }

        // Fechar Right Panel ao clicar no botão ou no overlay
        if (target.closest('#closeRightPanel') || target.closest('#overlay')) {
            const panel = document.getElementById('rightPanel');
            const overlay = document.getElementById('overlay');

            if (panel) panel.style.display = 'none';
            if (overlay) overlay.style.display = 'none';
            document.body.style.overflow = 'auto';
            return;
        }
    });
</script>
