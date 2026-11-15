<?php

/** @var yii\web\View $this */

use yii\helpers\Html;
use app\models\Video;

$this->title = 'Video Vault | A Collection of Memories';
$this->params['meta_description'] = 'Explore Video Vault, a personal archive of videos and memories.';

// Fetch the latest published videos
$videos = Video::find()
    ->where(['is_published' => 1])
    ->orderBy(['created_at' => SORT_DESC])
    ->limit(3)
    ->all();

$videoCount = Video::find()
    ->where(['is_published' => 1])
    ->count();

$categoryCount = \app\models\Category::find()->count();

?>

<div class="video-vault-home">

    <!-- HERO SECTION -->
    <section class="vault-hero rounded-4 mb-5">

        <div class="vault-hero-content">

            <span class="vault-label">A PERSONAL VIDEO ARCHIVE</span>

            <h1>
                Every moment<br>
                deserves to be <span>remembered.</span>
            </h1>

            <p>
                Welcome to Video Vault — a collection of moments,
                experiences and memories preserved through video.
            </p>

            <?= Html::a(
                'Explore the Archive →',
                ['/video/index'],
                ['class' => 'btn btn-warning btn-lg mt-3']
            ) ?>

        </div>

        <div class="vault-hero-decoration">
            <span>▶</span>
        </div>

    </section>


    <!-- STATISTICS -->
    <section class="row g-3 mb-5">

        <div class="col-md-6">
            <div class="vault-stat">
                <span class="stat-number"><?= (int) $videoCount ?></span>
                <span class="stat-label">Published Videos</span>
            </div>
        </div>

        <div class="col-md-6">
            <div class="vault-stat">
                <span class="stat-number"><?= (int) $categoryCount ?></span>
                <span class="stat-label">Archive Categories</span>
            </div>
        </div>

    </section>


    <!-- LATEST VIDEOS -->
    <section class="mb-5">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>
                <span class="vault-label">DISCOVER</span>
                <h2 class="fw-bold mt-2">Recently Added</h2>
            </div>

            <?= Html::a(
                'View All Videos →',
                ['/video/index'],
                ['class' => 'vault-link']
            ) ?>

        </div>


        <?php if (!empty($videos)): ?>

            <div class="row g-4">

                <?php foreach ($videos as $video): ?>

                    <div class="col-md-6 col-lg-4">

                        <div class="card vault-video-card h-100">

                            <?php if ($video->thumbnail_path): ?>

                                <?= Html::img(
                                    $video->thumbnail_path,
                                    [
                                        'class' => 'card-img-top vault-thumbnail',
                                        'alt' => Html::encode($video->title),
                                    ]
                                ) ?>

                            <?php else: ?>

                                <div class="vault-placeholder">
                                    <span>▶</span>
                                </div>

                            <?php endif; ?>


                            <div class="card-body">

                                <span class="vault-label">
                                    <?= Html::encode($video->year ?: 'VIDEO ARCHIVE') ?>
                                </span>

                                <h3 class="h5 fw-bold mt-2">
                                    <?= Html::encode($video->title) ?>
                                </h3>

                                <p class="text-secondary small">
                                    <?= Html::encode(
                                        \yii\helpers\StringHelper::truncate(
                                            $video->description ?? '',
                                            100
                                        )
                                    ) ?>
                                </p>

                                <?= Html::a(
                                    'Watch Video →',
                                    ['/video/view', 'id' => $video->id],
                                    ['class' => 'vault-link']
                                ) ?>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="vault-empty">
                <h4>Your archive starts here.</h4>
                <p>Published videos will appear here once they are added.</p>
            </div>

        <?php endif; ?>

    </section>


    <!-- CLOSING SECTION -->
    <section class="vault-bottom text-center rounded-4">

        <h2 class="fw-bold">Memories worth keeping.</h2>

        <p class="text-secondary">
            Explore the collection and revisit the moments that matter.
        </p>

        <?= Html::a(
            'Browse Video Collection',
            ['/video/index'],
            ['class' => 'btn btn-warning mt-2']
        ) ?>

    </section>

</div>