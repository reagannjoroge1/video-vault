<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Video $model */

$this->title = $model->title;
$this->params['breadcrumbs'][] = [
    'label' => 'Videos',
    'url' => ['index']
];
$this->params['breadcrumbs'][] = $this->title;

$isAdmin = !Yii::$app->user->isGuest
    && Yii::$app->user->identity->role === 'admin';

?>

<div class="video-watch-page">

    <!-- VIDEO PLAYER -->

    <div class="watch-player-container">

        <?php if (!empty($model->video_path)): ?>

            <video
                class="vault-player"
                controls
                preload="metadata"
                poster="<?= Html::encode($model->thumbnail_path ?? '') ?>"
            >
                <source
                    src="<?= Html::encode($model->video_path) ?>"
                    type="video/mp4"
                >

                Your browser does not support HTML5 video.
            </video>

        <?php else: ?>

            <div class="watch-placeholder">
                <span>▶</span>
                <p>Video file not available.</p>
            </div>

        <?php endif; ?>

    </div>


    <!-- VIDEO INFORMATION -->

    <div class="watch-information">

        <div class="watch-heading">

            <div>

                <span class="vault-label">
                    VIDEO VAULT COLLECTION
                </span>

                <h1 class="mt-2">
                    <?= Html::encode($model->title) ?>
                </h1>

                <div class="watch-meta">

                    <?php if ($model->year): ?>
                        <span><?= Html::encode($model->year) ?></span>
                    <?php endif; ?>

                    <?php if ($model->category): ?>
                        <span>
                            • <?= Html::encode($model->category->name) ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($model->recording_date): ?>
                        <span>
                            • <?= Html::encode($model->recording_date) ?>
                        </span>
                    <?php endif; ?>

                </div>

            </div>

            <?php if ($isAdmin): ?>

                <div class="watch-admin-actions">

                    <?= Html::a(
                        'Edit Video',
                        ['update', 'id' => $model->id],
                        ['class' => 'btn btn-outline-warning']
                    ) ?>

                    <?= Html::a(
                        'Delete',
                        ['delete', 'id' => $model->id],
                        [
                            'class' => 'btn btn-outline-danger',
                            'data' => [
                                'confirm' => 'Are you sure you want to delete this video?',
                                'method' => 'post',
                            ],
                        ]
                    ) ?>

                </div>

            <?php endif; ?>

        </div>


        <!-- DESCRIPTION -->

        <div class="watch-description">

            <h4>About this video</h4>

            <p>
                <?= nl2br(Html::encode($model->description ?: 'No description available.')) ?>
            </p>

        </div>


        <!-- BACK TO GALLERY -->

        <div class="watch-footer">

            <?= Html::a(
                '← Back to Video Gallery',
                ['index'],
                ['class' => 'btn btn-outline-warning']
            ) ?>

        </div>

    </div>

</div>