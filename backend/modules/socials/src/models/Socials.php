<?php

namespace milkyway\socials\models;

use common\helpers\MyHelper;
use common\models\User;
use milkyway\socials\SocialsModule;
use milkyway\socials\models\table\SocialsTable;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\ActiveRecord;
use Yii;
use yii\web\UploadedFile;

/**
 * This is the model class for table "socials".
 *
 * @property int $id
 * @property string $name
 * @property int $type
 * @property string $image
 * @property string $url
 * @property int $status
 * @property int $created_at
 * @property int $created_by
 * @property int $updated_at
 * @property int $updated_by
 *
 * @property User $createdBy
 * @property User $updatedBy
 */
class Socials extends SocialsTable
{
    public $toastr_key = 'socials';
    public $iptImage;
    public $iptIcon;

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
                [
                    'class' => AttributeBehavior::class,
                    'attributes' => [
                        ActiveRecord::EVENT_BEFORE_INSERT => ['image'],
                        ActiveRecord::EVENT_BEFORE_UPDATE => ['image'],
                    ],
                    'value' => function () {
                        if ($this->type == self::TYPE_IMAGE) {
                            $image = null;
                            $iptImage = UploadedFile::getInstance($this, 'iptImage');
                            if ($iptImage != null) {
                                $imageName = $iptImage->baseName . '-' . time() . '.' . $iptImage->extension;
                                if ($iptImage->saveAs($this->pathImage . '/' . $imageName)) return $imageName;
                            }
                        } else if ($this->type == self::TYPE_ICON) return $this->iptIcon;
                        return null;
                    }
                ]
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
            [['type', 'sort'], 'integer'],
            [['name', 'url', 'image'], 'string', 'max' => 255],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['updated_by' => 'id']],
            [['iptImage'], 'file', 'maxSize' => 2 * 1024 * 1024, 'extensions' => ['png', 'jpg', 'jpeg'], 'wrongExtension' => 'Chỉ chấp nhận định dạng: {extensions}', 'when' => function () {
                return $this->type == self::TYPE_IMAGE;
            }, 'whenClient' => "function(){
                return $('#selectType').val() == '" . self::TYPE_IMAGE . "';
            }"],
            [['iptIcon'], 'string', 'max' => 255, 'when' => function () {
                return $this->type == self::TYPE_ICON;
            }, 'whenClient' => "function(){
                return $('#selectType').val() == '" . self::TYPE_ICON . "';
            }"]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => SocialsModule::t('socials', 'ID'),
            'name' => SocialsModule::t('socials', 'Name'),
            'type' => SocialsModule::t('socials', 'Type'),
            'image' => SocialsModule::t('socials', 'Image'),
            'iptImage' => SocialsModule::t('socials', 'Image'),
            'iptIcon' => SocialsModule::t('socials', 'Icon'),
            'url' => SocialsModule::t('socials', 'Url'),
            'sort' => SocialsModule::t('socials', 'Sort'),
            'status' => SocialsModule::t('socials', 'Status'),
            'created_at' => SocialsModule::t('socials', 'Created At'),
            'created_by' => SocialsModule::t('socials', 'Created By'),
            'updated_at' => SocialsModule::t('socials', 'Updated At'),
            'updated_by' => SocialsModule::t('socials', 'Updated By'),
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        $oldType = $this->getOldAttribute('type');
        if ($oldType == self::TYPE_IMAGE && $this->getAttribute('type') == self::TYPE_ICON) {
            $oldImage = $this->getOldAttribute('image');
            if ($oldImage != null && !is_dir($this->pathImage . '/' . $oldImage) && file_exists($this->pathImage . '/' . $oldImage)) {
                @unlink($this->pathImage . '/' . $oldImage);
            }
        }
        parent::afterSave($insert, $changedAttributes); // TODO: Change the autogenerated stub
    }

    public function afterDelete()
    {
        $type = $this->type;
        if ($type == self::TYPE_IMAGE) {
            $image = $this->image;
            if ($image != null && !is_dir($this->pathImage . '/' . $image) && file_exists($this->pathImage . '/' . $image)) {
                @unlink($this->pathImage . '/' . $image);
            }
        }
        return parent::afterDelete(); // TODO: Change the autogenerated stub
    }
}
