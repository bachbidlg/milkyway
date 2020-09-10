<?php
/**
 * Created by PhpStorm.
 * User: luken
 * Date: 7/8/2020
 * Time: 15:54
 */

namespace frontend\widgets;


use frontend\models\News;
use yii\base\Widget;

class NewsRelateWidget extends Widget
{
    public $news_id = null;
    public $limit = null;

    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
    }

    public function run()
    {
        $news_relate = News::getRelateNews($this->news_id, $this->limit, false);
        if (count($news_relate) <= 0) return null;
        return $this->render('news-relate', [
            'news_relate' => $news_relate
        ]);
    }
}