<?php

namespace milkyway\news\models\table;

use common\models\User;
use milkyway\news\models\query\NewsQuery;
use Yii;

class NewsTable extends \yii\db\ActiveRecord
{
    const STATUS_DISABLED = 0;
    const STATUS_PUBLISHED = 1;
    const TYPE_NEWS = 0;
    const TYPE_SERVICE = 1;
    const TYPE = [
        self::TYPE_NEWS => 'Tin tức',
        self::TYPE_SERVICE => 'Dịch vụ'
    ];
    public $pathImage;
    public $urlImage;

    public function init()
    {
        $this->pathImage = Yii::getAlias('@frontend/web/uploads/news');
        $this->urlImage = Yii::getAlias('@frontendUrl/uploads/news');
        parent::init(); // TODO: Change the autogenerated stub
    }

    public static function tableName()
    {
        return 'news';
    }

    public static function find()
    {
        return new NewsQuery(get_called_class());
    }

    public function afterDelete()
    {
        $cache = Yii::$app->cache;
        $keys = [
            'redis-news-get-by-id-' . $this->id,
            'redis-news-get-all'
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
            'redis-news-get-by-id-' . $this->id,
            'redis-news-get-all'
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

    public function getCategoryHasOne()
    {
        return $this->hasOne(NewsCategoryTable::class, ['id' => 'category']);
    }

    public function getNewsLanguage()
    {
        return $this->hasMany(NewsLanguageTable::class, ['news_id' => 'id'])->indexBy('language_id');
    }

    public static function getById($id)
    {
        $cache = Yii::$app->cache;
        $key = 'redis-news-get-by-id-' . $id;
        $data = $cache->get($key);
        if ($data == false) {
            $query = self::find()->where([self::tableName() . '.id' => $id]);
            $data = $query->one();
            $cache->set($key, $data);
        }
        return $data;
    }

    public static function getAll()
    {
        $cache = Yii::$app->cache;
        $key = 'redis-news-get-all';
        $data = $cache->get($key);
        if ($data == false) {
            $query = self::find()->sort();
            $data = $query->all();
            $cache->set($key, $data);
        }
        return $data;
    }
}
