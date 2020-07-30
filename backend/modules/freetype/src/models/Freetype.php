<?php

namespace milkyway\freetype\models;

use common\helpers\MyHelper;
use common\models\User;
use milkyway\freetype\FreetypeModule;
use milkyway\freetype\models\table\FreetypeTable;
use yii\behaviors\BlameableBehavior;
use yii\db\ActiveRecord;
use Yii;
use yii\db\Transaction;
use yii\web\UploadedFile;

/**
 * This is the model class for table "freetype".
 *
 * @property int $id
 * @property string $image
 * @property int $status
 * @property int $sort Thứ tự
 * @property int $created_at
 * @property int $created_by
 * @property int $updated_at
 * @property int $updated_by
 *
 * @property User $createdBy
 * @property User $updatedBy
 */
class Freetype extends FreetypeTable
{
    const SCENARIO_UPDATE = 'update';
    public $toastr_key = 'freetype';
    public $freetype_language;
    public $iptImage;
    private $oldImage;

    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
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
            [['status', 'sort', 'type'], 'integer'],
            [['image'], 'string', 'max' => 255],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['updated_by' => 'id']],
            [['freetype_language'], 'validateFreetypeLanguage'],
            [['iptImage'], 'file', 'extensions' => ['jpg', 'jpeg', 'png'], 'maxSize' => 2 * 1024 * 1024, 'wrongExtension' => 'Chỉ chấp nhận định dạng file: {extensions}'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => FreetypeModule::t('freetype', 'ID'),
            'image' => FreetypeModule::t('freetype', 'Image'),
            'status' => FreetypeModule::t('freetype', 'Status'),
            'sort' => FreetypeModule::t('freetype', 'Sort'),
            'type' => FreetypeModule::t('freetype', 'Type'),
            'created_at' => FreetypeModule::t('freetype', 'Created At'),
            'created_by' => FreetypeModule::t('freetype', 'Created By'),
            'updated_at' => FreetypeModule::t('freetype', 'Updated At'),
            'updated_by' => FreetypeModule::t('freetype', 'Updated By'),
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
        parent::afterSave($insert, $changedAttributes); // TODO: Change the autogenerated stub
    }

    public function afterDelete()
    {
        if ($this->image != null && file_exists($this->pathImage . '/' . $this->image)) {
            @unlink($this->pathImage . '/' . $this->image);
        }
        return parent::afterDelete(); // TODO: Change the autogenerated stub
    }

    public function setFreetypeLanguage()
    {
        foreach ($this->freetypeLanguage as $language_id => $freetype_language) {
            $this->freetype_language[$language_id] = $freetype_language->getAttributes();
        }
    }

    public function saveFreetypeLanguage()
    {
        if (!$this->hasErrors()) {
            $transaction = Yii::$app->db->beginTransaction(Transaction::SERIALIZABLE);
            foreach ($this->freetype_language as $freetype_language) {
                $freetype_language_model = null;
                if (isset($freetype_language['freetype_id']) && isset($freetype_language['language_id'])) {
                    $freetype_language_model = FreetypeLanguage::find()->where(['freetype_id' => $freetype_language['freetype_id'], 'language_id' => $freetype_language['language_id']])->one();
                }
                if ($freetype_language_model == null) $freetype_language_model = new FreetypeLanguage();
                $freetype_language_model->scenario = FreetypeLanguage::SCENARIO_UPDATE;
                $freetype_language_model->setAttributes(array_merge($freetype_language, [
                    'freetype_id' => $this->primaryKey
                ]));
//                echo '<pre>';
//                var_dump($freetype_language_model);die;
                if (!$freetype_language_model->save()) {
                    $transaction->rollBack();
                    return false;
                }
            }
            $transaction->commit();
            return true;
        }
    }

    public function validateFreetypeLanguage()
    {
        if (!$this->hasErrors()) {
            foreach ($this->freetype_language as $i => $freetype_language) {
                $freetype_language_model = null;
                if (isset($freetype_language['freetype_id']) && isset($freetype_language['language_id'])) {
                    $freetype_language_model = FreetypeLanguage::find()->where(['freetype_id' => $freetype_language['freetype_id'], 'language_id' => $freetype_language['language_id']])->one();
                }
                if ($freetype_language_model == null) $freetype_language_model = new FreetypeLanguage();
                if ($this->scenario === self::SCENARIO_UPDATE) $freetype_language_model->scenario = FreetypeLanguage::SCENARIO_UPDATE;
                $freetype_language_model->setAttributes($freetype_language);
                if (!$freetype_language_model->validate()) {
                    foreach ($freetype_language_model->getErrors() as $k => $error) {
                        $this->addError("freetype_language[$i][$k]", $error);
                    }
                }
            }
        }
    }
}
