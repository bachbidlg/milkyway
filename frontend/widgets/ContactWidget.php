<?php
/**
 * Created by PhpStorm.
 * User: luken
 * Date: 7/8/2020
 * Time: 13:57
 */

namespace frontend\widgets;


use frontend\models\form\ContactForm;
use yii\base\ViewNotFoundException;
use yii\base\Widget;

class ContactWidget extends Widget
{
    public $type = 'contact';
    private $view = 'contact';

    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
    }

    public function run()
    {
        $model = new ContactForm();

        if ($this->type == 'contact_wg') {
            $this->view = 'contact_wg';
        }

        try {
            return $this->render($this->view, [
                'model' => $model
            ]);
        } catch (ViewNotFoundException $exception) {
            return null;
        }


    }
}