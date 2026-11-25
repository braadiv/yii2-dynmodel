<?php

namespace braadiv\dynmodel\models;

use braadiv\dynmodel\models\Eav;
use braadiv\dynmodel\models\EavEntity;
use Yii;

/**
 * This is the model class for table "{{%eav_attribute_value}}".
 *
 * @property integer $id
 * @property integer $entityId
 * @property integer $attributeId
 * @property string $value
 * @property integer $optionId
 * @property EavAttribute $attribute
 * @property Eav $entity
 * @property EavAttributeOption $option
 */
class EavAttributeValue extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%eav_attribute_value}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['entityId', 'attributeId'], 'required'],
            [['entityId', 'attributeId', 'optionId','order','plan_app_id'], 'integer'],
            // [['value'], 'string', 'max' => 1032],
            [['value'], 'string', 'max' => 65000],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'entityId' => Yii::t('eav', 'Entity ID'),
            'attributeId' => Yii::t('eav', 'Attribute ID'),
            'value' => Yii::t('eav', 'Value'),
            'optionId' => Yii::t('eav', 'Option ID'),
            'plan_app_id' => Yii::t('eav', 'Plan App'),
        ];
    }


    public function getPlanApp()
    {
        if (class_exists('common\models\PlanApp')) {
            return $this->hasOne('common\models\PlanApp', ['id' => 'plan_app_id']);
        }
        return null;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEavAttribute()
    {
        return $this->hasOne(EavAttribute::class, ['id' => 'attributeId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEntity()
    {
        return $this->hasOne(EavEntity::class, ['id' => 'entityId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOption()
    {
        return $this->hasOne(EavAttributeOption::class, ['id' => 'optionId']);
    }

    public function getValue()
    {
        return 'XXX';
    }
}
