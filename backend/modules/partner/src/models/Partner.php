<?php

namespace milkyway\partner\models;

use common\helpers\MyHelper;
use common\models\User;
use milkyway\partner\PartnerModule;
use milkyway\partner\models\table\PartnerTable;
use yii\behaviors\BlameableBehavior;
use yii\db\ActiveRecord;
use Yii;
use yii\web\UploadedFile;

/**
 * This is the model class for table "partner".
 *
 * @property int $id
 * @property string $name
 * @property string $image
 * @property string $url
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
class Partner extends PartnerTable
{
    public $toastr_key = 'partner';
    public $iptImage;
    public $oldImage;
    public $pathTemp;

    public function __construct($config = [])
    {
        $this->pathTemp = Yii::getAlias('@backend/web/tmp');
        parent::__construct($config);
    }

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
            [['status', 'sort'], 'integer'],
            [['name', 'url'], 'string', 'max' => 255],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['updated_by' => 'id']],
            [['image'], 'file', 'extensions' => ['png', 'jpg', 'jpeg'], 'wrongExtension' => 'Chỉ chấp nhận định dạng: {extensions}', 'maxSize' => 2 * 1024 * 1024]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => PartnerModule::t('partner', 'ID'),
            'name' => PartnerModule::t('partner', 'Name'),
            'image' => PartnerModule::t('partner', 'Image'),
            'url' => PartnerModule::t('partner', 'Url'),
            'status' => PartnerModule::t('partner', 'Status'),
            'sort' => PartnerModule::t('partner', 'Sort'),
            'created_at' => PartnerModule::t('partner', 'Created At'),
            'created_by' => PartnerModule::t('partner', 'Created By'),
            'updated_at' => PartnerModule::t('partner', 'Updated At'),
            'updated_by' => PartnerModule::t('partner', 'Updated By'),
            'iptImage' => PartnerModule::t('partner', 'Image'),
        ];
    }

    public function beforeSave($insert)
    {
        $this->oldImage = $this->getOldAttribute('image');
        return parent::beforeSave($insert); // TODO: Change the autogenerated stub
    }

    public function afterSave($insert, $changedAttributes)
    {
        $image = UploadedFile::getInstance($this, 'iptImage');
        if ($image != null) {
            $fileName = MyHelper::createAlias($image->baseName) . '-' . time() . '.' . $image->extension;
            if ($image->saveAs($this->pathImage . '/' . $fileName)) {
                $this->updateAttributes([
                    'image' => $fileName
                ]);
                if ($this->oldImage != null && file_exists($this->pathImage . '/' . $this->oldImage)) {
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
