<?php
/**
 * Created by PhpStorm.
 * User: luken
 * Date: 7/8/2020
 * Time: 13:57
 */

namespace frontend\widgets;


use yii\base\Widget;

class ContactWidget extends Widget
{
    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
    }

    public function run()
    {
        return $this->render('contact', []);
    }
}