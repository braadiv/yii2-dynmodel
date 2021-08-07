<?php

namespace braadiv\dynmodel\models;

use Yii;

/**
 * This is the model class for table "{{%eav_attribute_type}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $handlerClass
 * @property integer $storeType
 * @property EavAttribute[] $eavAttributes
 */
class EavAttributeType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%eav_attribute_type}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['storeType'], 'integer'],
            [['name', 'handlerClass'], 'string', 'max' => 255],
            [
                'name',
                'match',
                'pattern' => '/(^|.*\])([\w\.]+)(\[.*|$)/',
                'message' => Yii::t('eav', 'Type name must contain latin word characters only.'),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => Yii::t('eav', 'Name'),
            'handlerClass' => Yii::t('eav', 'Handler Class'),
            'storeType' => Yii::t('eav', 'Store Type'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEavAttributes()
    {
        return $this->hasMany(EavAttribute::className(), ['typeId' => 'id'])
            ->orderBy(['order' => SORT_DESC]);
    }

    public function getFormBuilder()
    {
        $class = $this->handlerClass;

        return [
            'order' => isset($class::$order) ? $class::$order : 0,
            'view' => isset($class::$fieldView) ? $class::$fieldView : Yii::t('eav', 'Template view'),
            'edit' => isset($class::$fieldSettings) ? $class::$fieldSettings : Yii::t('eav', 'Template settings'),
            'addButton' => isset($class::$fieldButton) ? $class::$fieldButton : Yii::t('eav', 'Template field button'),
            'defaultAttributes' => isset($class::$defaultAttributes)
                ? $class::$defaultAttributes
                : Yii::t(
                    'eav',
                    'Template default attributes'
                ),
        ];
    }

}