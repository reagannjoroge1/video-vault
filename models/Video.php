<?php

namespace app\models;

use Yii;
use yii\helpers\Inflector;
use yii\behaviors\TimestampBehavior;
use yii\web\UploadedFile;

/**
 * This is the model class for table "video".
 */
class Video extends \yii\db\ActiveRecord
{
    /**
     * Uploaded video file.
     */
    public $videoFile;

    /**
     * Uploaded thumbnail image.
     */
    public $thumbnailFile;


    public static function tableName()
    {
        return 'video';
    }


    public function rules()
    {
        return [
            [
                [
                    'description',
                    'thumbnail_path',
                    'recording_date',
                    'year',
                    'duration_seconds',
                    'category_id',
                    'created_at',
                    'updated_at'
                ],
                'default',
                'value' => null
            ],

            [['is_published'], 'default', 'value' => 0],

            [['title', 'slug', 'video_path'], 'required'],

            [['description'], 'string'],

            [['recording_date'], 'safe'],

            [
                [
                    'year',
                    'duration_seconds',
                    'category_id',
                    'is_published',
                    'created_at',
                    'updated_at'
                ],
                'integer'
            ],

            [['title', 'slug'], 'string', 'max' => 255],

            [['video_path', 'thumbnail_path'], 'string', 'max' => 1024],

            [['slug'], 'unique'],

            [
                ['category_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Category::class,
                'targetAttribute' => ['category_id' => 'id']
            ],

            // VIDEO FILE VALIDATION
            [
                ['videoFile'],
                'file',
                'extensions' => ['mp4', 'mov', 'webm'],
                'maxSize' => 2 * 1024 * 1024 * 1024,
                'tooBig' => 'The video file must not exceed 2 GB.'
            ],

            // THUMBNAIL VALIDATION
            [
                ['thumbnailFile'],
                'file',
                'extensions' => ['jpg', 'jpeg', 'png', 'webp'],
                'maxSize' => 10 * 1024 * 1024,
                'tooBig' => 'The thumbnail must not exceed 10 MB.'
            ],
        ];
    }


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'slug' => 'Slug',
            'description' => 'Description',
            'video_path' => 'Video Path',
            'thumbnail_path' => 'Thumbnail Path',
            'videoFile' => 'Upload Video',
            'thumbnailFile' => 'Upload Thumbnail',
            'recording_date' => 'Recording Date',
            'year' => 'Year',
            'duration_seconds' => 'Duration (Seconds)',
            'category_id' => 'Category',
            'is_published' => 'Is Published',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }


    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }


    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => [
                        'created_at',
                        'updated_at'
                    ],
                    \yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => [
                        'updated_at'
                    ],
                ],
            ],
        ];
    }


    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }

        if (empty($this->slug) && !empty($this->title)) {
            $this->slug = Inflector::slug($this->title);
        }

        if (!empty($this->recording_date) && empty($this->year)) {
            $this->year = (int) date(
                'Y',
                strtotime($this->recording_date)
            );
        }

        return true;
    }
}