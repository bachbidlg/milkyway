<?php

namespace milkyway\news\models\table;

use common\models\User;
use milkyway\language\models\table\LanguageTable;
use milkyway\news\models\query\NewsCategoryQuery;
use Yii;

class NewsCategoryTable extends \yii\db\ActiveRecord
{
    const STATUS_DISABLED = 0;
    const STATUS_PUBLISHED = 1;
    const TYPE_NEWS = 0;
    const TYPE_PROJECT = 1;
    const TYPE_SUPPORT = 2;
    const TYPE = [
        self::TYPE_NEWS => 'Tin tức',
        self::TYPE_PROJECT => 'Dự án',
        self::TYPE_SUPPORT => 'Hỗ trợ khách hàng',
    ];

    const TYPE_DU_AN_THIET_KE = 0;
    const TYPE_DU_AN_THI_CONG = 1;
    const TYPE_DU_AN = [
        self::TYPE_DU_AN_THIET_KE => 'Dự án thiết kế',
        self::TYPE_DU_AN_THI_CONG => 'Dự án thi công'
    ];
    public $pathImage;
    public $urlImage;

    public function init()
    {
        $this->pathImage = Yii::getAlias('@frontend/web/uploads/news-category');
        $this->urlImage = Yii::getAlias('@frontendUrl/uploads/news-category');
        parent::init(); // TODO: Change the autogenerated stub
    }

    public static function tableName()
    {
        return 'news_category';
    }

    public static function find()
    {
        return new NewsCategoryQuery(get_called_class());
    }

    public function afterDelete()
    {
        $cache = Yii::$app->cache;
        $keys = [
            'redis-news-category-get-by-id-' . $this->id,
            'redis-news-category-get-all',
            'redis-news-category-get-by-slug-' . $this->slug,
            'redis-news-category-get-by-type-' . $this->type,
            'redis-news-category-get-by-category-' . $this->category,
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
            'redis-news-category-get-by-id-' . $this->id,
            'redis-news-category-get-all',
            'redis-news-category-get-by-slug-' . $this->slug,
            'redis-news-category-get-by-type-' . $this->type,
            'redis-news-category-get-by-category-' . $this->category,
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
        return $this->hasOne(self::class, ['id' => 'category']);
    }

    public function getCategoryHasMany()
    {
        return $this->hasMany(self::class, ['category' => 'id']);
    }

    public function getNewsCategoryLanguage()
    {
        return $this->hasMany(NewsCategoryLanguageTable::class, ['news_category_id' => 'id'])->indexBy('language_id');
    }

    public function getImage()
    {
        if (!is_dir($this->pathImage . '/' . $this->image) && file_exists($this->pathImage . '/' . $this->image)) {
            return Yii::$app->assetManager->publish($this->pathImage . '/' . $this->image)[1];
        }
        $noImage = Yii::getAlias('@frontend/web/default/no-image-770x450.png');
        if (file_exists($noImage)) {
            return Yii::$app->assetManager->publish($noImage)[1];
        }
        return null;
    }

    public static function getById($id)
    {
        $cache = Yii::$app->cache;
        $key = 'redis-news-category-get-by-id-' . $id;
        $data = $cache->get($key);
        if ($data == false) {
            $query = self::find()->where([self::tableName() . '.id' => $id]);
            $data = $query->one();
            $cache->set($key, $data);
        }
        return $data;
    }

    public static function getByIds(array $ids = [], $sort = false)
    {
        if (count($ids) <= 0) return [];
        $query = self::find()->where(['IN', self::tableName() . '.id', $ids])->indexBy('id');
        if ($sort === true) $query->orderBy([new \yii\db\Expression('FIELD (id, ' . implode($ids, '-') . ')')]);
        return $query->all();
    }

    public static function getBySlug($slug, $data_cache = YII2_CACHE)
    {
        $cache = Yii::$app->cache;
        $key = 'redis-news-category-get-by-slug-' . $slug;
        $data = $cache->get($key);
        if ($data == false || $data_cache == false) {
            $query = self::find()->where([self::tableName() . '.slug' => $slug]);
            $data = $query->one();
            $cache->set($key, $data);
        }
        return $data;
    }

    public static function getByType($type, $published = false, $data_cache = YII2_CACHE)
    {
        $cache = Yii::$app->cache;
        $key = 'redis-news-category-get-by-type-';
        if (is_array($type)) $key .= implode('-', $type);
        else $key .= $type;
        $data = $cache->get($key);
        if ($data == false || $data_cache === false) {
            $query = self::find();
            if (is_array($type)) $query->where(['IN', self::tableName() . '.type', $type]);
            else $query->where([self::tableName() . '.type' => $type]);
            if ($published === true) $query->published();
            $data = $query->all();
            $cache->set($key, $data);
        }
        return $data;
    }

    public static function getByCategory($category)
    {
        $cache = Yii::$app->cache;
        $key = 'redis-news-category-get-by-category-' . $category;
        $data = $cache->get($key);
        if ($data == false) {
            $query = self::find()->where([self::tableName() . '.category' => $category]);
            $data = $query->all();
            $cache->set($key, $data);
        }
        return $data;
    }

    public static function getAll()
    {
        $cache = Yii::$app->cache;
        $key = 'redis-news-category-get-all';
        $data = $cache->get($key);
        if ($data == false) {
            $query = self::find()->sort();
            $data = $query->all();
            $cache->set($key, $data);
        }
        return $data;
    }

    public static function getMenu($type = null, $current = null, $category = null, $get_published = true, $data = null, $prefix = '|--')
    {
        if ($data == null) $data = [];
        $default_language = LanguageTable::getDefaultLanguage();
        $query = self::find()->joinWith(['newsCategoryLanguage'])->where([self::tableName() . '.category' => $category])->sort()->groupBy([self::tableName() . '.id']);
        if ($type !== null) $query->andWhere([self::tableName() . '.type' => $type]);
        if ($get_published === true) $query->published();
        $rows = $query->all();
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                if ($current === $row->primaryKey) continue;
                $language = $default_language !== null ? $default_language->primaryKey : array_keys($row->newsCategoryLanguage)[0];
                $data[$row->primaryKey] = [
                    'id' => $row->primaryKey,
                    'name' => $prefix . $row->newsCategoryLanguage[$language]->name
                ];
                $data = self::getMenu($type, $current, $row->primaryKey, $get_published, $data, $prefix . '|--');
            }
        }
        return $data;
    }
}
