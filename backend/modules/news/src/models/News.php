<?php

namespace milkyway\news\models;

use common\helpers\MyHelper;
use common\models\User;
use milkyway\language\models\Language;
use milkyway\language\models\table\LanguageTable;
use milkyway\news\models\table\NewsImagesTable;
use milkyway\news\NewsModule;
use milkyway\news\models\table\NewsTable;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\SluggableBehavior;
use yii\db\ActiveRecord;
use Yii;
use yii\db\Transaction;
use yii\web\UploadedFile;

/**
 * This is the model class for table "news".
 *
 * @property int $id
 * @property string $slug
 * @property int $category
 * @property string $image
 * @property int $status
 * @property int $sort Thứ tự
 * @property int $created_at
 * @property int $created_by
 * @property int $updated_at
 * @property int $updated_by
 * @property string $alias
 *
 * @property NewsCategory $category0
 * @property User $createdBy
 * @property User $updatedBy
 * @property NewsLanguage[] $newsLanguages
 * @property Language[] $languages
 */
class News extends NewsTable
{
    const SCENARIO_UPDATE = 'update';
    public $toastr_key = 'news';
    public $news_language;
    public $iptImage;
    private $oldImage;
    public $news_images;

    public function behaviors()
    {
        $default_language = LanguageTable::getDefaultLanguage();
        $list_language = LanguageTable::getAll();
        return array_merge(
            parent::behaviors(),
            [
                'slug' => [
                    'class' => SluggableBehavior::class,
//                    'immutable' => false,
                    'ensureUnique' => true,
                    'attributes' => [
                        ActiveRecord::EVENT_BEFORE_INSERT => ['slug'],
                        ActiveRecord::EVENT_BEFORE_UPDATE => ['slug'],
                    ],
                    'value' => function () use ($default_language, $list_language) {
                        if ($this->slug != null) return $this->slug;
                        $language = $default_language !== null ? $default_language->primaryKey : $list_language[0]->primaryKey;
                        return MyHelper::createAlias($this->news_language[$language]['name']);
                    }
                ],
                [
                    'class' => BlameableBehavior::class,
                    'createdByAttribute' => 'created_by',
                    'updatedByAttribute' => 'updated_by',
                ],
                'timestamp' => [
                    'class' => 'yii\behaviors\TimestampBehavior',
                    'preserveNonEmptyValues' => true,
                    'attributes' => [
                        ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                        ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                    ],
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['category'], 'required'],
            [['category', 'sort'], 'integer'],
            [['slug', 'image'], 'string', 'max' => 255],
            [['category'], 'exist', 'skipOnError' => true, 'targetClass' => NewsCategory::class, 'targetAttribute' => ['category' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['updated_by' => 'id']],
            [['news_language'], 'validateNewsLanguage'],
            [['news_images'], 'validateNewsImage'],
            [['iptImage'], 'file', 'extensions' => ['jpg', 'jpeg', 'png'], 'maxSize' => 2 * 1024 * 1024, 'wrongExtension' => 'Chỉ chấp nhận định dạng file: {extensions}'],
        ];
    }

    public function beforeSave($insert)
    {
        $this->oldImage = $this->getOldAttribute('image');
        return parent::beforeSave($insert); // TODO: Change the autogenerated stub
    }

    public function afterSave($insert, $changedAttributes)
    {
        $iptImage = UploadedFile::getInstance($this, 'iptImage');
        if ($iptImage != null) {
            $imageName = $iptImage->baseName . '-' . time() . '.' . $iptImage->extension;
            if ($iptImage->saveAs($this->pathImage . '/' . $imageName)) {
                $this->updateAttributes([
                    'image' => $imageName
                ]);
                if (file_exists($this->pathImage . '/' . $this->oldImage)) {
                    @unlink($this->pathImage . '/' . $this->oldImage);
                }
            }
        }
        $alias = '';
        if ($this->categoryHasOne != null) $alias = $this->categoryHasOne->alias;
        $alias .= '/' . $this->primaryKey;
        $this->updateAttributes([
            'alias' => $alias
        ]);
        parent::afterSave($insert, $changedAttributes); // TODO: Change the autogenerated stub
    }

    public function afterDelete()
    {
        if ($this->image != null && file_exists($this->pathImage . '/' . $this->image)) {
            @unlink($this->pathImage . '/' . $this->image);
        }
        return parent::afterDelete(); // TODO: Change the autogenerated stub
    }

    public function setNewsLanguage()
    {
        foreach ($this->newsLanguage as $language_id => $news_language) {
            $this->news_language[$language_id] = $news_language->getAttributes();
        }
    }

    public function saveNewsLanguage()
    {
        if (!$this->hasErrors()) {
            $transaction = Yii::$app->db->beginTransaction(Transaction::SERIALIZABLE);
            foreach ($this->news_language as $news_language) {
                $news_language_model = null;
                if (isset($news_language['news_id']) && isset($news_language['language_id'])) {
                    $news_language_model = NewsLanguage::find()->where(['news_id' => $news_language['news_id'], 'language_id' => $news_language['language_id']])->one();
                }
                if ($news_language_model == null) $news_language_model = new NewsLanguage();
                $news_language_model->scenario = NewsLanguage::SCENARIO_UPDATE;
                $news_language_model->setAttributes(array_merge($news_language, [
                    'news_id' => $this->primaryKey
                ]));
                if (!$news_language_model->save()) {
                    $transaction->rollBack();
                    return false;
                }
            }
            $transaction->commit();
            return true;
        }
    }

    public function validateNewsLanguage()
    {
        if (!$this->hasErrors()) {
            foreach ($this->news_language as $i => $news_language) {
                $news_language_model = null;
                if (isset($news_language['news_id']) && isset($news_language['language_id'])) {
                    $news_language_model = NewsLanguage::find()->where(['news_id' => $news_language['news_id'], 'language_id' => $news_language['language_id']])->one();
                }
                if ($news_language_model == null) $news_language_model = new NewsLanguage();
                if ($this->scenario === self::SCENARIO_UPDATE) $news_language_model->scenario = NewsLanguage::SCENARIO_UPDATE;
                $news_language_model->setAttributes($news_language);
                if (!$news_language_model->validate()) {
                    foreach ($news_language_model->getErrors() as $k => $error) {
                        $this->addError("news_language[$i][$k]", $error);
                    }
                }
            }
        }
    }

    public function validateNewsImage()
    {
        if (!$this->hasErrors()) {
            foreach ($this->news_images as $i => $news_images) {
                $news_images_model = null;
                if (isset($news_images['id'])) {
                    $news_images_model = NewsImagesTable::getById($news_images['id']);
                }
                if ($news_images_model == null) $news_images_model = new NewsImages();
                if ($this->scenario === self::SCENARIO_UPDATE) $news_images_model->scenario = NewsImages::SCENARIO_UPDATE;
                $news_images_model->setAttributes($news_images);
                if (!$news_images_model->validate()) {
                    foreach ($news_images_model->getErrors() as $k => $error) {
                        $this->addError("news_images[$i][$k]", $error);
                    }
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => NewsModule::t('news', 'ID'),
            'slug' => NewsModule::t('news', 'Slug'),
            'category' => NewsModule::t('news', 'Category'),
            'image' => NewsModule::t('news', 'Image'),
            'status' => NewsModule::t('news', 'Status'),
            'hot' => NewsModule::t('news', 'Nổi bật'),
            'sort' => NewsModule::t('news', 'Sort'),
            'created_at' => NewsModule::t('news', 'Created At'),
            'created_by' => NewsModule::t('news', 'Created By'),
            'updated_at' => NewsModule::t('news', 'Updated At'),
            'updated_by' => NewsModule::t('news', 'Updated By'),
            'alias' => NewsModule::t('news', 'Alias'),
        ];
    }
}
