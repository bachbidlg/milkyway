<?php

namespace milkyway\team\models\table;

use cheatsheet\Time;
use milkyway\team\models\query\TeamCategoryQuery;
use modava\auth\models\User;
use Yii;
use yii\db\ActiveRecord;

class TeamCategoryTable extends \yii\db\ActiveRecord
{
    const STATUS_DISABLED = 0;
    const STATUS_PUBLISHED = 1;

    public static function tableName()
    {
        return 'team_category';
    }

    public static function find()
    {
        return new TeamCategoryQuery(get_called_class());
    }

    public function afterDelete()
    {
        $cache = Yii::$app->cache;
        $keys = [];
        foreach ($keys as $key) {
            $cache->delete($key);
        }
        return parent::beforeDelete(); // TODO: Change the autogenerated stub
    }

    public function afterSave($insert, $changedAttributes)
    {
        $cache = Yii::$app->cache;
        $keys = [];
        foreach ($keys as $key) {
            $cache->delete($key);
        }
        parent::afterSave($insert, $changedAttributes); // TODO: Change the autogenerated stub
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserCreated()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserUpdated()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }

    public static function getAll($published = false, $data_cache = YII2_CACHE)
    {
        $cache = Yii::$app->cache;
        $key = 'redis-team-category-get-all';
        $data = $cache->get($key);
        if ($data == false || $data_cache === false) {
            $query = self::find()->sort();
            if ($published === true) $query->published();
            $data = $query->all();
            $cache->set($key, $data_cache);
        }
        return $data;
    }
}
