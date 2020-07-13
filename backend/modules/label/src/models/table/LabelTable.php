<?php

namespace milkyway\label\models\table;

use cheatsheet\Time;
use Yii;
use yii\db\ActiveRecord;

class LabelTable extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'label';
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


}
