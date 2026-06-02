<?php
use yii\bootstrap5\Html;

$this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css');
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4"
       style="position:fixed; top:0; left:0; height:100vh;
              display:flex; flex-direction:column; z-index:1038;">

    <!-- Brand Logo -->
    <a class="brand-link" style="flex-shrink:0;">
        <img src="<?= Yii::$app->request->baseUrl ?>/img/korpLogo.png"
             alt="Korporate Logo"
             class="brand-image img-circle elevation-3"
             style="opacity:.8">
        <span class="brand-text font-weight-light">Korporate-ANVIZ</span>
    </a>

    <!-- Sidebar wrapper -->
    <div class="sidebar" style="display:flex; flex-direction:column; flex:1; overflow:hidden; min-height:0;">

        <!-- Scrollable nav -->
        <div style="flex:1; overflow-y:auto; overflow-x:hidden;">
            <nav class="mt-2">
                <?php
                echo \hail812\adminlte\widgets\Menu::widget([
                    'options' => [
                        'class' => 'nav nav-pills nav-sidebar flex-column nav-legacy',
                        'data-widget' => 'treeview',
                        'role' => 'menu',
                    ],
                    'items' => [
                        ['label' => 'Dashboard',  'icon' => 'tachometer-alt', 'url' => ['dashboard/index']],
                        [
                            'label' => 'Colaboradores',
                            'icon'  => 'user',
                            'items' => [
                                ['label' => 'Colaboradores', 'url' => ['collaborators/index'], 'icon' => 'user',        'iconStyle' => 'far'],
                                ['label' => 'Departamentos', 'url' => ['departments/index'],   'icon' => 'code-branch'],
                            ],
                        ],
                        [
                            'label' => 'Relatórios',
                            'icon'  => 'clipboard',
                            'items' => [
                                ['label' => 'Diário',  'url' => ['reports/diario'],  'icon' => 'calendar-day'],
                                ['label' => 'Semanal', 'url' => ['reports/semanal'], 'icon' => 'calendar-week'],
                            ],
                        ],
                        ['label' => 'Dispositivos', 'icon' => 'microchip',  'url' => ['devices/index']],
                        ['label' => 'Gii',           'icon' => 'file-code', 'url' => ['/gii'], 'target' => '_blank'],
                    ],
                ]);
                ?>
            </nav>
        </div>

        <!-- User card pinned to bottom -->
        <div class="user-panel mt-auto pb-3 mb-0 mx-3 d-flex align-items-center border-top pt-3"
             style="flex-shrink:0;">
            <div class="image me-2 d-flex align-items-center justify-content-center">
                <i class="bi bi-person-circle text-secondary" style="font-size:1.4rem;"></i>
            </div>
            <div class="info" style="overflow:hidden;">
                <a href="#" class="d-block text-truncate" style="max-width:140px;">
                    <?= Html::encode(Yii::$app->user->identity->username ?? '') ?>
                </a>
                <small class="text-muted" style="font-size:.7rem;">
                    <?php
                    $level = Yii::$app->user->identity->AccessLevel ?? 0;
                    echo $level >= 1 ? 'Administrador' : 'Utilizador';
                    ?>
                </small>
            </div>
        </div>

    </div>
</aside>
