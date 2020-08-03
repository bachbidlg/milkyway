<?php
/**
 * Created by PhpStorm.
 * User: luken
 * Date: 7/2/2020
 * Time: 19:33
 */

/* @var $footerInfo frontend\models\Freetype */

/* @var $menu_footer array */

/* @var $menu frontend\models\NewsCategory */

/* @var $sub_menu frontend\models\NewsCategory */

/* @var $shop frontend\models\Shop */

use yii\helpers\Url;

$default_language = $this->params['default_language'];
$shop = $this->params['shop'];
?>
<footer id="footer">
    <div class="footer-top">
        <div class="container">
            <div class="row">
                <?php if (!empty($footer_info)) { ?>
                    <div class="col-lg-4">
                        <div class="ft-column about">
                            <div class="ft-col-title"><?= $footer_info->freetypeLanguage[$default_language]->name ?></div>
                            <div class="ft-col-content">
                                <?= $footer_info->freetypeLanguage[$default_language]->content ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <?php if ($shop != null) { ?>
                    <div class="col-lg-5">
                        <div class="ft-column info">
                            <div class="ft-col-title">Thông tin</div>
                            <div class="ft-col-content">
                                <?php if ($shop->shopLanguage[$default_language]->getMetadata('name') != null) { ?>
                                    <p>
                                        <strong><?= $shop->shopLanguage[$default_language]->getMetadata('name') ?></strong>
                                    </p>
                                <?php } ?>
                                <?php if ($shop->shopLanguage[$default_language]->getMetadata('address') != null) { ?>
                                    <p>
                                        <strong>Đ/c:</strong> <?= $shop->shopLanguage[$default_language]->getMetadata('address') ?>
                                    </p>
                                <?php } ?>
                                <?php if ($shop->dataMetadata('hotline') != null) { ?>
                                    <p>
                                        <strong>Hotline:</strong> <?= $shop->dataMetadata('hotline') ?>
                                    </p>
                                <?php } ?>
                                <?php if ($shop->dataMetadata('mst') != null) { ?>
                                    <p>
                                        <strong>MST:</strong> <?= $shop->dataMetadata('mst') ?>
                                    </p>
                                <?php } ?>
                                <?php if ($shop->dataMetadata('created') != null) { ?>
                                    <p>
                                        <strong>Ngày cấp giấy phép:</strong> <?= $shop->dataMetadata('created') ?>
                                    </p>
                                <?php } ?>
                                <?php if ($shop->dataMetadata('started') != null) { ?>
                                    <p>
                                        <strong>Ngày hoạt động:</strong> <?= $shop->dataMetadata('started') ?>
                                    </p>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <?php
                if (count($menu_footer) > 0) {
                    ?>
                    <div class="col-lg-3">
                        <div class="ft-column ft-nav">
                            <div class="ft-col-title"><?= Yii::t('frontend', 'Hỗ trợ khách hàng') ?></div>
                            <div class="ft-col-content">
                                <ul>
                                    <?php foreach ($menu_footer as $menu) { ?>
                                        <li>
                                            <a href="<?= Url::toRoute(['/news/index', 'slug' => $menu->slug]) ?>"
                                               title="<?= $menu->newsCategoryLanguage[$default_language]->name ?>"><?= $menu->newsCategoryLanguage[$default_language]->name ?></a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <div class="row">
                <div class="copyright">
                    &copy;
                    2020 <?= $shop != null ? $shop->shopLanguage[$default_language]->getMetadata('slogan') : '' ?>
                </div>
                <div class="social">
                    <a href="#" title="" target="_blank"><i class="fa fa-facebook-f"></i></a>
                    <a href="#" title="" target="_blank"><i class="fa fa-instagram"></i></a>
                    <a href="#" title="" target="_blank"><i class="fa fa-youtube"></i></a>
                    <a href="#" title="" target="_blank"><i class="fa fa-linkedin"></i></a>
                </div>
            </div>
        </div>
    </div>
</footer>
