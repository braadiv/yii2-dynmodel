<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 */



namespace braadiv\dynmodel\handlers;

use yii\helpers\ArrayHelper;

use common\modules\FormBase;
/**
 * Class RawValueHandler
 *
 * @package braadiv\dynmodel
 */
class RawValueHandler extends ValueHandler
{
    public $listRulesAvalibal = ['string'=>['min','max'],'integer'=>['min','max','integer_only']];
    // public $listRulesAvalibal = ['min'=>1,'max'=>200,'minlength'=>0,'maxlength'=>200];
    /**
     * @inheritdoc
     */
    public function load()
    {
        $valueModel = $this->getValueModel();

        return $valueModel->value;
    }

    /**
     * @inheritdoc
     */
    public function defaultValue()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function save()
    {
        $EavModel = $this->attributeHandler->owner;
        $valueModel = $this->getValueModel();
        $attribute = $this->attributeHandler->getAttributeName();

        if (isset($EavModel->attributes[$attribute])) {
            $valueModel->value = $EavModel->attributes[$attribute];
            if (!$valueModel->save()) {
                throw new \Exception("Can't save value model");
            }
        }
    }

    public function getTextValue()
    {
        return $this->getValueModel()->value;
    }

    public function addRules()
    {
        $model = &$this->attributeHandler->owner;
        $attribute = &$this->attributeHandler->attributeModel;
        $attribute_name = $this->attributeHandler->getAttributeName();

        if ($attribute->eavType->storeType == ValueHandler::STORE_TYPE_RAW) {
            $model->addRule($attribute_name, 'default', ['value' => $attribute->defaultValue]);
            $type = FormBase::getRuleType($attribute->eavType->name);
            if($type){
                $rules = ArrayHelper::filter(
                    is_null($attribute->attributeRule->rules)
                        ? []
                        : (array) json_decode($attribute->attributeRule->rules),
                    $this->listRulesAvalibal[$type]?? [],
                    );
                $rules = array_filter($rules,function($value)
                {
                    return $value > 0;
                });

                $model->addRule($attribute_name, $type, $rules?? []);
            }
        }
    }
}