<?php

use yii\db\Migration;

/**
 * Class m200826_093155_create_table_team
 */
class m200826_093155_create_table_team extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        /* check table exists */
        $check_table = Yii::$app->db->getTableSchema('team');
        if ($check_table === null) {
            $tableOptions = null;
            if ($this->db->driverName === 'mysql') {
                // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
                $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
            }
            $this->createTable('team', [
                'id' => $this->primaryKey(),
                'name' => $this->string(255)->notNull(),
                'slug' => $this->string(255)->notNull(),
                'category' => $this->integer(11)->notNull(),
                'image' => $this->string(255)->null(),
                'position' => $this->string(255)->null(),
                'status' => $this->tinyInteger(1)->null()->defaultValue(1)->comment('0: disabled, 1: published'),
                'sort' => $this->integer(11)->null()->defaultValue(0),
                'created_at' => $this->integer(11)->null(),
                'created_by' => $this->integer(11)->null()->defaultValue(1),
                'updated_at' => $this->integer(11)->null(),
                'updated_by' => $this->integer(11)->null()->defaultValue(1),
            ], $tableOptions);
            $this->createIndex('index-team_slug', 'team', 'slug');
            $this->addForeignKey('foreign_key-team-category-team_category-id', 'team', 'category', 'team_category', 'id', 'RESTRICT', 'CASCADE');
            $this->addForeignKey('foreign_key-team-created_by-user-id', 'team', 'created_by', 'user', 'id', 'RESTRICT', 'CASCADE');
            $this->addForeignKey('foreign_key-team-updated_by-user-id', 'team', 'updated_by', 'user', 'id', 'RESTRICT', 'CASCADE');
        }
        if (!is_dir(Yii::getAlias('@frontend/web/uploads/team'))) {
            @mkdir(Yii::getAlias('@frontend/web/uploads/team'), 0775, true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200826_093155_create_table_team cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200826_093155_create_table_team cannot be reverted.\n";

        return false;
    }
    */
}
