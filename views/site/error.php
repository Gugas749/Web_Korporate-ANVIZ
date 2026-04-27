<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Url;
use yii\helpers\Html;

$this->title = $name;
$this->params['breadcrumbs'] = [['label' => $this->title]];
?>
<div class="error-page">
    <div class="error-content" style="margin-left: auto;">
        <h3><i class="fas fa-exclamation-triangle text-danger"></i> <?= Html::encode($name) ?></h3>

        <p>
            <?= nl2br(Html::encode($message)) ?>
        </>

        <p>
            <br>Contacte um administrador, se pensar ser um erro. Obrigado.
            <br>Clique para retomar para <?= Html::a('dashboard', Url::to(['dashboard/index'])); ?>
        </p>
    </div>
</div>

