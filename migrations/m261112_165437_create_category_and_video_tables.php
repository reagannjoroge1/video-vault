<?php

use yii\db\Migration;

class m261112_165437_create_category_and_video_tables extends Migration
{
    public function safeUp()
    {
        // Create category table
        $this->createTable('{{%category}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(100)->notNull(),
            'slug' => $this->string(120)->notNull()->unique(),
            'description' => $this->text(),
            'created_at' => $this->integer(),
        ]);

        // Create video table
        $this->createTable('{{%video}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->notNull(),
            'slug' => $this->string(255)->notNull()->unique(),
            'description' => $this->text(),

            'video_path' => $this->string(1024)->notNull(),
            'thumbnail_path' => $this->string(1024),

            'recording_date' => $this->date(),
            'year' => $this->integer(),

            'duration_seconds' => $this->integer(),

            'category_id' => $this->integer(),

            'is_published' => $this->boolean()
                ->notNull()
                ->defaultValue(0),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        // Category relationship
        $this->addForeignKey(
            'fk-video-category',
            '{{%video}}',
            'category_id',
            '{{%category}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        // Indexes
        $this->createIndex(
            'idx-video-year',
            '{{%video}}',
            'year'
        );

        $this->createIndex(
            'idx-video-published',
            '{{%video}}',
            'is_published'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-video-category',
            '{{%video}}'
        );

        $this->dropTable('{{%video}}');
        $this->dropTable('{{%category}}');
    }
}