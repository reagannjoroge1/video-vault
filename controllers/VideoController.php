<?php

namespace app\controllers;

use app\models\Video;
use app\models\VideoSearch;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\FileHelper;
use Symfony\Component\Process\Process;

class VideoController extends Controller
{
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        [
                            'allow' => true,
                            'actions' => ['index', 'view'],
                            'roles' => ['?', '@'],
                        ],
                        [
                            'allow' => true,
                            'actions' => ['create', 'update', 'delete'],
                            'matchCallback' => function ($rule, $action) {
                                return $this->isAdmin();
                            },
                        ],
                    ],
                ],

                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }


    /**
     * Display videos.
     */
    public function actionIndex()
    {
        $searchModel = new VideoSearch();

        $dataProvider = $searchModel->search(
            $this->request->queryParams
        );

        $query = $dataProvider->query;

        if (!$this->isAdmin()) {
            $query->andWhere(['is_published' => 1]);
        }

        $searchTerm = trim(Yii::$app->request->get('q', ''));

        if ($searchTerm !== '') {
            $query->andWhere(['like', 'title', $searchTerm]);
        }

        $categoryId = Yii::$app->request->get('category_id');

        if ($categoryId !== null && $categoryId !== '') {
            $query->andWhere(['category_id' => (int) $categoryId]);
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * Display an individual video.
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        if (!$this->isAdmin() && !$model->is_published) {
            throw new NotFoundHttpException(
                'The requested page does not exist.'
            );
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }


    /**
     * Upload and create a new video.
     */
    public function actionCreate()
    {
        $model = new Video();

        if ($model->load(Yii::$app->request->post())) {

            if ($this->saveUploadedFiles($model)) {
                Yii::$app->session->setFlash(
                    'success',
                    'Video uploaded successfully!'
                );

                return $this->redirect([
                    'view',
                    'id' => $model->id
                ]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }


    /**
     * Update an existing video.
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (
            $model->load(Yii::$app->request->post())
            && $this->saveUploadedFiles($model)
        ) {
            Yii::$app->session->setFlash(
                'success',
                'Video updated successfully!'
            );

            return $this->redirect([
                'view',
                'id' => $model->id
            ]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }


    /**
     * Save uploaded video and thumbnail files.
     */
    protected function saveUploadedFiles($model)
    {
        // Get uploaded files
        $model->videoFile = UploadedFile::getInstance(
            $model,
            'videoFile'
        );

        $model->thumbnailFile = UploadedFile::getInstance(
            $model,
            'thumbnailFile'
        );
               

        // A video file is required when creating a new record
        if ($model->isNewRecord && !$model->videoFile) {
            $model->addError(
                'videoFile',
                'Please select a video file to upload.'
            );

            return false;
        }

        // Prepare upload directories
        $videoDirectory = Yii::getAlias(
            '@webroot/uploads/videos'
        );

        $thumbnailDirectory = Yii::getAlias(
            '@webroot/uploads/thumbnails'
        );

        FileHelper::createDirectory($videoDirectory);
        FileHelper::createDirectory($thumbnailDirectory);

        // Keep track of newly uploaded files for cleanup
        $savedFiles = [];

        // Generate a unique video filename
        if ($model->videoFile) {

            $videoName = Yii::$app->security
                ->generateRandomString(24)
                . '.'
                . strtolower($model->videoFile->extension);

            $model->video_path = 'uploads/videos/' . $videoName;
        }

        // Generate a unique thumbnail filename
        if ($model->thumbnailFile) {

            $thumbnailName = Yii::$app->security
                ->generateRandomString(24)
                . '.'
                . strtolower($model->thumbnailFile->extension);

            $model->thumbnail_path = 'uploads/thumbnails/' . $thumbnailName;
        }

        // Validate all model fields and uploaded files
        if (!$model->validate()) {
            return false;
        }

        // Save the video file
        if ($model->videoFile) {

            $videoFullPath = $videoDirectory . '/' . $videoName;

            if (!$model->videoFile->saveAs($videoFullPath)) {
                $model->addError(
                    'videoFile',
                    'Could not save the video file.'
                );

                return false;
            }

           $savedFiles[] = $videoFullPath;

            // Generate thumbnail automatically if no custom thumbnail was uploaded
            if (!$model->thumbnailFile) {

                $generatedThumbnail = $this->generateThumbnail(
                    $model->video_path
                );

                if ($generatedThumbnail !== null) {
                    $model->thumbnail_path = $generatedThumbnail;

                    $generatedThumbnailFullPath = Yii::getAlias(
                        '@webroot/' . $generatedThumbnail
                    );

                    $savedFiles[] = $generatedThumbnailFullPath;
                }
            }
        }

        // Save the thumbnail
        if ($model->thumbnailFile) {

            $thumbnailFullPath = $thumbnailDirectory
                . '/'
                . $thumbnailName;

            if (!$model->thumbnailFile->saveAs($thumbnailFullPath)) {

                foreach ($savedFiles as $file) {
                    @unlink($file);
                }

                $model->addError(
                    'thumbnailFile',
                    'Could not save the thumbnail image.'
                );

                return false;
            }

            $savedFiles[] = $thumbnailFullPath;
        }

        // Save database record
        if (!$model->save(false)) {

            foreach ($savedFiles as $file) {
                @unlink($file);
            }

            Yii::error(
                'Could not save video record: ' . json_encode($model->errors),
                __METHOD__
            );

            $model->addError(
                'title',
                'The video could not be saved. Please try again.'
            );

            return false;
        }

        return true;
    }


    /**
     * Delete a video record.
     */
    /**
 * Delete a video record and its associated uploaded files.
 */
        public function actionDelete($id)
        {
            $model = $this->findModel($id);

            // Remember file paths before deleting the database record.
            $videoPath = $model->video_path;
            $thumbnailPath = $model->thumbnail_path;

            if ($model->delete() !== false) {

                // Files that are allowed to be deleted.
                $files = [
                    [
                        'path' => $videoPath,
                        'directory' => 'uploads/videos',
                    ],
                    [
                        'path' => $thumbnailPath,
                        'directory' => 'uploads/thumbnails',
                    ],
                ];

                foreach ($files as $file) {

                    if (empty($file['path'])) {
                        continue;
                    }

                    $relativePath = str_replace('\\', '/', $file['path']);
                    $allowedPrefix = $file['directory'] . '/';

                    // Prevent deletion outside the intended upload directory.
                    if (strpos($relativePath, $allowedPrefix) !== 0) {
                        continue;
                    }

                    $directory = realpath(
                        Yii::getAlias('@webroot/' . $file['directory'])
                    );

                    $fullPath = realpath(
                        Yii::getAlias('@webroot/' . $relativePath)
                    );

                    if (
                        $directory !== false &&
                        $fullPath !== false &&
                        strpos(
                            $fullPath,
                            $directory . DIRECTORY_SEPARATOR
                        ) === 0 &&
                        is_file($fullPath)
                    ) {
                        if (!unlink($fullPath)) {
                            Yii::warning(
                                'Could not delete uploaded file: ' . $fullPath,
                                __METHOD__
                            );
                        }
                    }
                }

                Yii::$app->session->setFlash(
                    'success',
                    'Video and associated files deleted successfully.'
                );

            } else {

                Yii::$app->session->setFlash(
                    'error',
                    'Could not delete the video. Please try again.'
                );
            }

            return $this->redirect(['index']);
        }


    /**
     * Find video by ID.
     */
    protected function findModel($id)
    {
        if (($model = Video::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(
            'The requested page does not exist.'
        );
    }
    /**
 * Generate a thumbnail from an uploaded video using FFmpeg.
        */
       
    /**
 * Generate a thumbnail from an uploaded video using FFmpeg.
 */
        /**
 * Generate a thumbnail from an uploaded video using FFmpeg.
 */
        protected function generateThumbnail($videoPath)
        {
            $videoFullPath = Yii::getAlias(
                '@webroot/' . ltrim($videoPath, '/')
            );

            if (!is_file($videoFullPath)) {
                Yii::error(
                    'Video file not found: ' . $videoFullPath,
                    __METHOD__
                );

                return null;
            }

            $thumbnailDirectory = Yii::getAlias(
                '@webroot/uploads/thumbnails'
            );

            FileHelper::createDirectory($thumbnailDirectory);

            $thumbnailName = Yii::$app->security
                ->generateRandomString(24) . '.jpg';

            $thumbnailFullPath = $thumbnailDirectory . '/' . $thumbnailName;

            // Try extracting at 2 seconds, then at the beginning.
            foreach ([2, 0] as $seekTime) {

                $process = new Process([
                    'ffmpeg',
                    '-hide_banner',
                    '-loglevel', 'error',
                    '-y',
                    '-ss', (string) $seekTime,
                    '-i', $videoFullPath,
                    '-frames:v', '1',
                    '-q:v', '2',
                    $thumbnailFullPath
                ]);

                $process->setTimeout(120);

                try {
                    $process->run();
                } catch (\Throwable $e) {
                    Yii::error(
                        'FFmpeg error: ' . $e->getMessage(),
                        __METHOD__
                    );

                    continue;
                }

                if (
                    $process->isSuccessful()
                    && is_file($thumbnailFullPath)
                    && filesize($thumbnailFullPath) > 0
                ) {
                    return 'uploads/thumbnails/' . $thumbnailName;
                }

                if (file_exists($thumbnailFullPath)) {
                    @unlink($thumbnailFullPath);
                }
            }

            Yii::error(
                'Could not generate thumbnail for: ' . $videoFullPath,
                __METHOD__
            );

            return null;
        }

    /**
     * Check administrator privileges.
     */
    protected function isAdmin()
    {
        return !Yii::$app->user->isGuest
            && Yii::$app->user->identity->role === 'admin';
    }
}