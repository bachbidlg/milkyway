<?php
/**
 * Created by PhpStorm.
 * User: luken
 * Date: 7/8/2020
 * Time: 14:09
 */

namespace frontend\widgets;


use yii\base\Widget;

class NewsRecommendWidget extends Widget
{
    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
    }

    public function run()
    {
        return $this->render('news-recommend', []);
    }
}