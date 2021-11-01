<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 */

namespace braadiv\dynmodel;

use braadiv\dynmodel\widgets\AttributeHandler;
use Yii;
use yii\base\DynamicModel as BaseEavModel;
use yii\db\ActiveRecord;
use yii\widgets\ActiveForm;
use braadiv\dynmodel\models\EavAttribute;
use NXP\MathExecutor;
/**
 * Class EavModel
 *
 * @package braadiv\dynmodel
 */
class EavModel extends BaseEavModel
{
    public $ConditionClass = "\common\\modules\\models\\Condition";
    public $CompareClass = "\common\\modules\\models\\Compare";

    /** @var string Class to use for storing data */
    public $valueClass;

    /** @var ActiveRecord */
    public $entityModel;

    /** @var AttributeHandler[] */
    public $handlers;

    /** @var string */
    public $attribute = '';

    /** @var ActiveForm */
    public $activeForm;

    /** @var string[] */
    private $attributeLabels = [];


    public $condition;

    public $plan_app_id;
    
    public $row_no;



    /**
     * Constructor for creating form model from entity object
     *
     * @param array $params
     * @return static
     */
    public static function create($params)
    {
        $params['class'] = static::className();

        /** @var static $model */
        $model = Yii::createObject($params);
        // if(isset($params['condition'])){
        // // print_r($params);
        // //     die();
        //     $model->entityModel->condition = $params['condition'];
        // }
        $params = [];

        /**
         * Event EavBehavior::afterSave
         * Rise after form submit
         */
        if ($model->attribute <> 'eav') {
            $params = [EavAttribute::tableName() . '.name' => $model->attribute];
        }

        $attributes = $model
            ->entityModel
            // Load data from owner model
            ->getEavAttributes()
            ->joinWith('entity')
            ->andWhere($params)
            ->all();

        foreach ($attributes as $attribute) {
            $handler = AttributeHandler::load($model, $attribute);
            $attribute_name = $handler->getAttributeName();

            //
            // Add rules
            //

            if ($attribute->required) {
                $model->addRule($attribute_name, 'required');
            } else {
                $model->addRule($attribute_name, 'safe');
            }
            $model->setAttributeLabel($attribute_name,$handler->getAttributeLabel());

            $handler->valueHandler->addRules();

            //
            // Load attribute value
            //

            $value = $handler->valueHandler->load();
            if (!$value) {
                // Set default attribute
                $value = $handler->valueHandler->defaultValue();
            }

            $model->defineAttribute($attribute_name, $value);

            //
            // Add widget handler
            //

            $model->handlers[$attribute_name] = $handler;
        }

        return $model;
    }

    /**
     * @inheritdoc
     */
    public function getAttributeLabels()
    {
        return $this->attributeLabels;
    }

    public function setLabel($name, $label)
    {
        $this->attributeLabels[$name] = $label;
    }

    public function save($runValidation = true, $attributes = null)
    {
        if (!$this->handlers) {
            Yii::info(Yii::t('eav', 'Dynamic model data were no attributes.'), __METHOD__);

            return false;
        }

        if ($runValidation && !$this->validate($attributes)) {
            Yii::info(Yii::t('eav', 'Dynamic model data were not save due to validation error.'), __METHOD__);

            return false;
        }

        $db = $this->entityModel->getDb();

        $transaction = $db->beginTransaction();
        try {
            foreach ($this->handlers as $handler) {
                // echo "<pre>";
                // $Condition =$this->ConditionClass::find()->where(['attributeId'=>$handler->attributeModel->id])->one();
                $Condition =$handler->attributeModel->planCondition;
                if($Condition){
                // print_r($handler->attributeModel->planCondition); die();
                    $field1 = $this->handlers['c'.$Condition->field_1];
                    $value1 = $field1->valueHandler->getValueModel()->option->index_value;
                    $field2 = $this->handlers['c'.$Condition->field_2];
                    $value2 = $field2->valueHandler->getValueModel()->option->index_value;
                    
                    $executor = new MathExecutor();
                    $value = $executor->execute("$value1 $Condition->operator $value2 ");
                    $Compares = $Condition->compareModels;
                    foreach ($Compares as $row ) {
                        if($executor->execute("$value $row->operator $row->value ")){
                                $this->__set($handler->attributeModel->name,$row->optionId);
                            break;
                        }
                    }
                }
                
                $handler->valueHandler->save(!$runValidation);
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public function __set($name, $value)
    {
        $this->defineAttribute($name, $value);
    }

    public function getValue()
    {
        if (isset($this->attributes[$this->attribute])) {
            return $this->attributes[$this->attribute];
        } else {
            return '';
        }
    }

    public function __toString()
    {
        if (isset($this->attributes[$this->attribute])) {
            if (is_string($this->attributes[$this->attribute])) {
                return (string)$this->attributes[$this->attribute];
            } else {
                return (string)json_encode($this->attributes[$this->attribute]);
            }
        } else {
            return '';
        }
    }

    public function formName()
    {
        // if(isset($this->entityModel->name)){
        //     $reflector =  \yii\helpers\BaseInflector::camelize($this->entityModel->name);
        //     return $reflector. '[EavModel]';

        // }
        // echo "<pre>ss";
        // print_r($this->entityModel); 
        return self::getModelShortName($this->entityModel) . '[EavModel]';
    }

    public static function getModelShortName($model)
    {
        // echo "<pre>ss";
        if(isset($model->name)){

            $reflector =  \yii\helpers\BaseInflector::camelize($model->name);
            return $reflector;
        }
        // print_r($this); die();
        $reflector = new \ReflectionClass($model::className());
        return $reflector->getShortName();
    }
}
