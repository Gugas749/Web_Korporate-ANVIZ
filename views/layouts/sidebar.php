<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index3.html" class="brand-link">
        <img src="<?=$assetDir?>/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">AdminLTE 3</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="<?=$assetDir?>/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">Alexander Pierce</a>
            </div>
        </div>

        <!-- Menu -->
        <nav class="mt-2">
            <?php
            echo \hail812\adminlte\widgets\Menu::widget([
                    'options' => [
                            'class' => 'nav nav-pills nav-sidebar flex-column nav-legacy',
                            'data-widget' => 'treeview',
                            'role' => 'menu'
                    ],
                    'items' => [
                            ['label' => 'Dashboard', 'icon' => 'tachometer-alt', 'url' => ['dashboard/index']],
                            [
                                    'label' => 'Colaboradores',
                                    'icon' => 'user',
                                    'items' => [
                                            ['label' => 'Colaboradores', 'url' => ['collaborators/index'], 'icon' => 'user', 'iconStyle' => 'far'],
                                            ['label' => 'Departamentos', 'url' => ['departments/index'], 'icon' => 'code-branch'],
                                    ]
                            ],
                            ['label' => 'Gii',  'icon' => 'file-code', 'url' => ['/gii'], 'target' => '_blank'],
                    ],
            ]);
            ?>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>