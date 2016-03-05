<?php
/**
 * @link http://phe.me
 * @copyright Copyright (c) 2014 Pheme
 * @license MIT http://opensource.org/licenses/MIT
 */

use yii\helpers\Html;
use pheme\settings\Module;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var array $config
 * @var \pheme\settings\models\Dynamic $model
 */

$this->title = Module::t(
    'settings',
    'Customer'
);
$this->params['breadcrumbs'][] = ['label' => Module::t('settings', 'Settings'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="setting-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="setting-form">

        <?php $form = ActiveForm::begin(); ?>

        <?php foreach ($config as $field): ?>
            <?= $form->field($model, $field['name'])->textInput(['maxlength' => 255]) ?>
        <?php endforeach ?>

        <div class="form-group">
            <?=
            Html::submitButton(
                Module::t('settings', 'Save'),
                [
                    'class' => 'btn btn-success'
                ]
            ) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

</div>
