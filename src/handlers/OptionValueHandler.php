<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 */

namespace braadiv\dynmodel\handlers;

/**
 * Class OptionValueHandler
 *
 * @package braadiv\dynmodel
 */
class OptionValueHandler extends ValueHandler
{
    /**
     * @inheritdoc
     */
    public function load()
    {
        $valueModel = $this->getValueModel();

        return $valueModel->optionId;
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
            $valueModel->optionId = $EavModel->attributes[$attribute];

            // Save empty value
            if (!$valueModel->optionId) {
                $valueModel->optionId = null;
            }

            if (!$valueModel->save()) {
                throw new \Exception("Can't save value model");
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function defaultValue()
    {
        $defaultOptions = [];

        foreach ($this->attributeHandler->attributeModel->eavOptions as $option) {
            if ($option->defaultOptionId === 1) {
                $defaultOptions[] = $option->id;
            }
        }

        return $defaultOptions;
    }

    public function getTextValue()
    {
        if(!isset($this->getValueModel()->option->_value)){
            return '';
        }
        return $this->getValueModel()->option->_value;
    }

    public function addRules()
    {
        $model = &$this->attributeHandler->owner;
        $attribute = &$this->attributeHandler->attributeModel;
        $attribute_name = $this->attributeHandler->getAttributeName();

        if ($attribute->eavType->storeType == ValueHandler::STORE_TYPE_OPTION) {
            $model->addRule($attribute_name, 'default', ['value' => $attribute->defaultOptionId]);
        }
    }
}