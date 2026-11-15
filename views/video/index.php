<?php

use app\models\Category;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\widgets\LinkPager;

/** @var yii\web\View $this */
/** @var app\models\VideoSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Explore the Archive';
$this->params['breadcrumbs'][] = $this->title;

// Get categories for the filter
$categories = Category::find()
    ->orderBy(['name' => SORT_ASC])
    ->all();

// Check administrator status
$isAdmin = !Yii::$app->user->isGuest
    && Yii::$app->user->identity->role === 'admin';

$videos = $dataProvider->getModels();

?>

<div class="video-gallery">

    <!-- GALLERY HEADER -->
    <div class="gallery-header mb-5">

        <span class="vault-label">THE COLLECTION</span>

        <h1 class="display-5 fw-bold mt-2">
            <?= Html::encode($this->title) ?>
        </h1>

        <p class="text-secondary">
            Discover and revisit the moments preserved in Video Vault.
        </p>

        <?php if ($isAdmin): ?>
            <?= Html::a(
                '+ Add New Video',
                ['create'],
                ['class' => 'btn btn-warning mt-3']
            ) ?>
        <?php endif; ?>

    </div>


    <!-- SEARCH AND CATEGORY FILTER -->
    <div class="gallery-filters mb-5">

        <form method="get" action="<?= Html::encode(\yii\helpers\Url::to(['/video/index'])) ?>">

            <input type="hidden" name="r" value="video/index">

            <div class="row g-3">

                <div class="col-md-7">

                    <input
                        type="text"
                        name="q"
                        class="form-control"
                        placeholder="Search videos by title..."
                        value="<?= Html::encode(Yii::$app->request->get('q', '')) ?>"
                    >

                </div>

                <div class="col-md-3">

                    <select name="category_id" class="form-select">

                        <option value="">All Categories</option>

                        <?php foreach ($categories as $category): ?>

                            <option
                                value="<?= $category->id ?>"
                                <?= Yii::$app->request->get('category_id') == $category->id ? 'selected' : '' ?>
                            >
                                <?= Html::encode($category->name) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="col-md-2">

                    <button type="submit" class="btn btn-warning w-100">
                        Search
                    </button>

                </div>

            </div>

        </form>

    </div>


    <!-- VIDEO COLLECTION -->
    <?php if (!empty($videos)): ?>

        <div class="row g-4">

            <?php foreach ($videos as $video): ?>

                <div class="col-md-6 col-lg-4">

                    <div class="card vault-video-card h-100">

                        <!-- THUMBNAIL -->

                        <?php if (!empty($video->thumbnail_path)): ?>

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


                        <!-- VIDEO INFORMATION -->

                        <div class="card-body d-flex flex-column">

                            <div class="d-flex justify-content-between align-items-center mb-2">

                                <span class="vault-label">
                                    <?= Html::encode($video->year ?: 'VIDEO') ?>
                                </span>

                                <?php if ($video->category): ?>

                                    <span class="badge bg-secondary">
                                        <?= Html::encode($video->category->name) ?>
                                    </span>

                                <?php endif; ?>

                            </div>


                            <h3 class="h5 fw-bold">
                                <?= Html::encode($video->title) ?>
                            </h3>


                            <p class="text-secondary small">
                                <?= Html::encode(
                                    StringHelper::truncate(
                                        $video->description ?? '',
                                        110
                                    )
                                ) ?>
                            </p>


                            <div class="mt-auto">

                                <?= Html::a(
                                    '▶ Watch Video',
                                    ['view', 'id' => $video->id],
                                    ['class' => 'btn btn-outline-warning btn-sm']
                                ) ?>

                                <?php if ($isAdmin): ?>

                                            <?= Html::a(
                                                'Edit',
                                                ['update', 'id' => $video->id],
                                                ['class' => 'btn btn-outline-secondary btn-sm']
                                            ) ?>

                                            <?= Html::a(
                                                'Delete',
                                                ['delete', 'id' => $video->id],
                                                [
                                                    'class' => 'btn btn-outline-danger btn-sm',
                                                    'data' => [
                                                        'method' => 'post',
                                                        'confirm' => 'Are you sure you want to delete this video?',
                                                    ],
                                                ]
                                            ) ?>

                                        <?php endif; ?>
                            </div>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>


        <!-- PAGINATION -->

        <div class="mt-5 d-flex justify-content-center">

            <?= LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'options' => ['class' => 'pagination'],
                'linkOptions' => ['class' => 'page-link'],
                'pageCssClass' => 'page-item',
                'activePageCssClass' => 'active',
                'disabledPageCssClass' => 'disabled',
            ]) ?>

        </div>


    <?php else: ?>

        <div class="vault-empty">

            <h4>No videos found.</h4>

            <p class="text-secondary">
                Try another search or choose a different category.
            </p>

            <?= Html::a(
                'Clear Filters',
                ['index'],
                ['class' => 'btn btn-outline-warning']
            ) ?>

        </div>

    <?php endif; ?>

</div>