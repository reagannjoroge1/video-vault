<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use app\models\Category;

/** @var yii\web\View $this */
/** @var app\models\Video $model */
/** @var yii\widgets\ActiveForm $form */

$categories = ArrayHelper::map(
    Category::find()->orderBy(['name' => SORT_ASC])->all(),
    'id',
    'name'
);
?>

<div class="video-form">

    <?php $form = ActiveForm::begin([
        'options' => [
            'enctype' => 'multipart/form-data'
        ]
    ]); ?>

    <!-- VIDEO TITLE -->

    <?= $form->field($model, 'title')->textInput([
        'maxlength' => true,
        'placeholder' => 'Enter video title'
    ]) ?>


    <!-- VIDEO SLUG -->

    <?= $form->field($model, 'slug')->textInput([
        'maxlength' => true,
        'placeholder' => 'Leave blank to generate automatically'
    ]) ?>


    <!-- DESCRIPTION -->

    <?= $form->field($model, 'description')->textarea([
        'rows' => 5,
        'placeholder' => 'Describe this video...'
    ]) ?>


    <!-- CATEGORY -->

    <?= $form->field($model, 'category_id')->dropDownList(
        $categories,
        ['prompt' => 'Select a category']
    ) ?>


    <!-- VIDEO FILE UPLOAD -->

    <?= $form->field($model, 'videoFile')->fileInput([
        'accept' => '.mp4,.mov,.webm'
    ])->hint('Accepted formats: MP4, MOV and WebM. Maximum size: 2 GB.') ?>

    <?php if (!$model->isNewRecord && !empty($model->video_path)): ?>

        <p class="text-secondary">
            Current video:
            <?= Html::a(
                'View existing video',
                $model->video_path,
                ['target' => '_blank', 'rel' => 'noopener']
            ) ?>
        </p>

    <?php endif; ?>


    <!-- THUMBNAIL UPLOAD -->

    <?= $form->field($model, 'thumbnailFile')->fileInput([
        'accept' => '.jpg,.jpeg,.png,.webp'
    ])->hint('Accepted formats: JPG, JPEG, PNG and WebP. Maximum size: 10 MB.') ?>

    <?php if (!$model->isNewRecord && !empty($model->thumbnail_path)): ?>

        <p class="text-secondary">
            Current thumbnail:
            <?= Html::img($model->thumbnail_path, [
                'style' => 'max-width:180px; display:block; margin-top:10px;'
            ]) ?>
        </p>

    <?php endif; ?>


    <!-- RECORDING DATE -->

    <?= $form->field($model, 'recording_date')->input('date') ?>


    <!-- YEAR -->

    <?= $form->field($model, 'year')->input('number', [
        'min' => 1900,
        'max' => 2100
    ]) ?>


    <!-- DURATION -->

    <?= $form->field($model, 'duration_seconds')->input('number', [
        'min' => 0
    ]) ?>


    <!-- PUBLICATION STATUS -->

    <?= $form->field($model, 'is_published')->checkbox([
        'label' => 'Publish this video (make it publicly visible)'
    ]) ?>


    <!-- SUBMIT BUTTON -->

    <div class="form-group mt-4">

        <?= Html::submitButton('Save Video', [
            'class' => 'btn btn-success'
        ]) ?>

    </div>

    <?php ActiveForm::end(); ?>

</div>