<?php

namespace milkyway\contact\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class ContactAsset extends AssetBundle
{
    public $sourcePath = '@contactweb';
    public $css = [
        'vendors/bootstrap/dist/css/bootstrap.min.css',
        'vendors/jquery-toggles/css/toggles.css',
        'vendors/jquery-toggles/css/themes/toggles-light.css',
        'css/customContact.css',
    ];
    public $js = [
        "vendors/popper.js/dist/umd/popper.min.js",
        "vendors/bootstrap/dist/js/bootstrap.min.js",
        "vendors/jasny-bootstrap/dist/js/jasny-bootstrap.min.js",
        'js/customContact.js'
    ];
    public $jsOptions = array(
        'position' => \yii\web\View::POS_END
    );
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
