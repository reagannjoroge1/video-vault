<?php

namespace app\models;

use Yii;
use yii\helpers\Inflector;

/**
 * This is the model class for table "category".
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int|null $created_at
 *
 * @property Video[] $videos
 */
class Category extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description', 'created_at'], 'default', 'value' => null],
            [['name', 'slug'], 'required'],
            [['description'], 'string'],
            [['created_at'], 'integer'],
            [['name'], 'string', 'max' => 100],
            [['slug'], 'string', 'max' => 120],
            [['slug'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'slug' => 'Slug',
            'description' => 'Description',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Gets query for [[Videos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVideos()
    {
        return $this->hasMany(Video::class, ['category_id' => 'id']);
    }
        public function beforeValidate()
    {
        if (parent::beforeValidate()) {

            if (empty($this->slug) && !empty($this->name)) {
                $this->slug = Inflector::slug($this->name);
            }

            return true;
        }

        return false;
    }

    public function beforeSave($insert)
    {
        if ($insert && empty($this->created_at)) {
            $this->created_at = time();
        }

        return parent::beforeSave($insert);
    }

}
