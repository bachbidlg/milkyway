<?php

namespace milkyway\comments\models\table;

use milkyway\comments\models\query\CommentsQuery;
use modava\auth\models\User;
use Yii;

/**
 * This is the model class for table "comments".
 *
 * @property int $id
 * @property string $comment
 * @property string $comment_table
 * @property int $comment_id
 * @property int $status 0: Disabled, 1: Published
 * @property int $created_at
 * @property int $created_by
 * @property int $updated_at
 * @property int $updated_by
 * @property array $metadata Other info, ex: name, phone, address,...
 *
 * @property User $createdBy
 * @property User $updatedBy
 */
class CommentsTable extends \yii\db\ActiveRecord
{
    const STATUS_DISABLED = 0;
    const STATUS_PUBLISHED = 1;

    public static function tableName()
    {
        return 'comments';
    }

    public static function find()
    {
        return new CommentsQuery(get_called_class());
    }

    public function afterDelete()
    {
        $cache = Yii::$app->cache;
        $keys = [
            'redis-comments-get-all',
            'redis-comments-get-by-id-' . $this->id
        ];
        foreach ($keys as $key) {
            $cache->delete($key);
        }
        return parent::beforeDelete(); // TODO: Change the autogenerated stub
    }

    public function afterSave($insert, $changedAttributes)
    {
        $cache = Yii::$app->cache;
        $keys = [
            'redis-comments-get-all',
            'redis-comments-get-by-id-' . $this->id
        ];
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

    public static function getById($id, $data_cache = YII2_CACHE)
    {
        $cache = Yii::$app->cache;
        $key = 'redis-comments-get-by-id-' . $id;
        $data = $cache->get($key);
        if ($data == false || $data_cache === false) {
            $query = self::find()->where(['id' => $id]);
            $data = $query->one();
            if($data_cache === true) $cache->set($key, $data);
        }
        return $data;
    }

    public static function getAll($published = false, $data_cache = YII2_CACHE)
    {
        $cache = Yii::$app->cache;
        $key = 'redis-comments-get-all';
        $data = $cache->get($key);
        if ($data == false || $data_cache === false) {
            $query = self::find();
            if ($published === true) $query->published();
            $data = $query->all();
            if($data_cache === true) $cache->set($key, $data);
        }
        return $data;
    }

    public function dataMetadataByKey($key)
    {
        if (!is_array($this->metadata) || !array_key_exists($key, $this->metadata)) return null;
        return $this->metadata[$key];
    }
}
