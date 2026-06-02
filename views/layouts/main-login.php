<?php

/* @var $this \yii\web\View */
/* @var $content string */

\hail812\adminlte3\assets\AdminLteAsset::register($this);
$this->registerCssFile('https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700');
$this->registerCssFile('https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css');
\hail812\adminlte3\assets\PluginAsset::register($this)->add(['fontawesome', 'icheck-bootstrap']);
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Korporate-ANVIZ | Log in</title>
        <!-- Tell the browser to be responsive to screen width -->
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php $this->registerCsrfMetaTags() ?>
        <?php $this->head() ?>
    </head>
    <body class="hold-transition login-page">
    <?php  $this->beginBody() ?>
    <div class="login-box">
        <div class="login-logo">
            <img href="<?=Yii::$app->homeUrl?>" src="../web/img/korporateLogo.png" alt="Korporate Logo" class="login-box">
        </div>
        <!-- /.login-logo -->

        <?= $content ?>
    </div>
    <!-- /.login-box -->

    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>

<style>
    body {
        height: 100vh;
        margin: 0;
        background: linear-gradient(135deg, #2521ff, #030175);
    }
</style>
