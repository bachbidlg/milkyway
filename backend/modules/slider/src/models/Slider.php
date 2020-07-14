<?php

namespace milkyway\slider\models;

use common\helpers\MyHelper;
use common\models\User;
use milkyway\slider\SliderModule;
use milkyway\slider\models\table\SliderTable;
use yii\behaviors\BlameableBehavior;
use yii\db\ActiveRecord;
use Yii;
use yii\web\UploadedFile;

/**
 * This is the model class for table "slider".
 *
 * @property int $id
 * @property string $name
 * @property string $image
 * @property string $url
 * @property int $type Slider chính, Slider phụ, Partner...
 * @property int $status
 * @property int $sort
 * @property int $created_at
 * @property int $created_by
 * @property int $updated_at
 * @property int $updated_by
 *
 * @property User $createdBy
 * @property User $updatedBy
 */
class Slider extends SliderTable
{
    public $toastr_key = 'slider';
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
            [['name'], 'required'],
            [['type', 'status', 'sort', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['name', 'image', 'url'], 'string', 'max' => 255],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['updated_by' => 'id']],
            [['iptImage'], 'file', 'extensions' => ['jpg', 'jpeg', 'png'], 'maxSize' => 2 * 1024 * 1024, 'wrongExtension' => 'Chỉ chấp nhận định dạng file: {extensions}'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => SliderModule::t('slider', 'ID'),
            'name' => SliderModule::t('slider', 'Name'),
            'image' => SliderModule::t('slider', 'Image'),
            'url' => SliderModule::t('slider', 'Url'),
            'type' => SliderModule::t('slider', 'Type'),
            'status' => SliderModule::t('slider', 'Status'),
            'sort' => SliderModule::t('slider', 'Sort'),
            'created_at' => SliderModule::t('slider', 'Created At'),
            'created_by' => SliderModule::t('slider', 'Created By'),
            'updated_at' => SliderModule::t('slider', 'Updated At'),
            'updated_by' => SliderModule::t('slider', 'Updated By'),
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
}
