<?php

namespace braadiv\dynmodel\models;

use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "eav_attribute_rules".
 *
 * @property integer $id
 * @property integer $attributeId
 * @property string $rules
 */
class EavAttributeRule extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%eav_attribute_rules}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['attributeId'], 'integer'],
            [['required', 'visible', 'locked'], 'integer', 'max' => 1],
            [['rules'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'attributeId' => Yii::t('eav', 'Attribute ID'),
            'rules' => Yii::t('eav', 'Rules'),
            'required' => Yii::t('eav', 'Required'),
            'locked' => Yii::t('eav', 'Locked'),
            'visible' => Yii::t('eav', 'Visible'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEavAttribute()
    {
        return $this->hasOne(EavAttribute::className(), ['id' => 'attributeId']);
    }

    private function getDefaultFields()
    {
        return [
            'id',
            'attributeId',
            'rules',
            'required',
            'locked',
            'visible',
            'eavAttribute',
        ];
    }

    private function getSkipFields()
    {
        return [
            'cid',
            'label',
            'type',
            'field_type',
            'description',
        ];
    }

    public function __get($name)
    {
        if (in_array($name, $this->getDefaultFields())) {
            return parent::__get($name);
        }

        if (in_array($name, $this->getSkipFields())) {
            $rules = Json::decode($this->rules);

            if (isset($rules[$name])) {
                return $rules[$name];
            }
        }

        return [];
    }

    public function __set($name, $value)
    {
        if (in_array($name, $this->getDefaultFields())) {
            return parent::__set($name, $value);
        }

        if (in_array($name, $this->getSkipFields())) {
            return [];
        }

        $rules = Json::decode($this->rules);

        if (!$rules) {
            $rules = [];
        }

        $rules[$name] = $value;

        $this->rules = Json::encode($rules);
    }
}
