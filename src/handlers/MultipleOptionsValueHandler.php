<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 */

namespace braadiv\dynmodel\handlers;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Class MultipleOptionsValueHandler
 *
 * @package braadiv\dynmodel
 */
class MultipleOptionsValueHandler extends ValueHandler
{
    /** @var AttributeHandler */
    public $attributeHandler;

    public function load()
    {
        $EavModel = $this->attributeHandler->owner;

        /** @var ActiveRecord $valueClass */
        $valueClass = $EavModel->valueClass;

        $models = $valueClass::findAll(
            [
                'entityId' => $EavModel->entityModel->getPrimaryKey(),
                'attributeId' => $this->attributeHandler->attributeModel->getPrimaryKey(),
                'plan_app_id' => $EavModel->plan_app_id,
                'order' => $EavModel->row_no,
            ]
        );

        $values = ArrayHelper::getColumn(
            $models,
            function ($element) {
                return $element->optionId;
            }
        );

        return $values;
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

    public function save()
    {
        $EavModel = $this->attributeHandler->owner;
        $attribute = $this->attributeHandler->getAttributeName();
        /** @var ActiveRecord $valueClass */
        $valueClass = $EavModel->valueClass;

        $baseQuery = $valueClass::find()->where(
            [
                'entityId' => $EavModel->entityModel->getPrimaryKey(),
                'attributeId' => $this->attributeHandler->attributeModel->getPrimaryKey(),
                'plan_app_id' => $EavModel->plan_app_id,
                'order' => $EavModel->row_no,
            ]
        );

        $allOptions = ArrayHelper::getColumn(
            $this->attributeHandler->attributeModel->eavOptions,
            function ($element) {
                return $element->getPrimaryKey();
            }
        );

        $query = clone $baseQuery;
        $query->andWhere(['NOT IN', 'optionId', $allOptions]);
        $valueClass::deleteAll($query->where);

        // then we delete unselected options
        $selectedOptions = ArrayHelper::getValue($EavModel->attributes, $attribute);
        if (!is_array($selectedOptions)) {
            $selectedOptions = [];
        }
        $deleteOptions = array_diff($allOptions, $selectedOptions);

        $query = clone $baseQuery;
        $query->andWhere(['IN', 'optionId', $deleteOptions]);

        $valueClass::deleteAll($query->where);

        // third we insert missing options
        foreach ($selectedOptions as $id) {
            $query = clone $baseQuery;
            $query->andWhere(['optionId' => $id]);

            $valueModel = $query->one();

            if (!$valueModel instanceof ActiveRecord) {
                /** @var ActiveRecord $valueModel */
                $valueModel = new $valueClass;
                $valueModel->entityId = $EavModel->entityModel->getPrimaryKey();
                $valueModel->attributeId = $this->attributeHandler->attributeModel->getPrimaryKey();
                $valueModel->optionId = $id;
                 $valueModel->plan_app_id = $EavModel->plan_app_id;
                $valueModel->order = $EavModel->row_no;
                if (!$valueModel->save()) {
                    throw new \Exception("Can't save value model");
                }
            }
        }
    }

    public function getTextValue()
    {
        $EavModel = $this->attributeHandler->owner;

        /** @var ActiveRecord $valueClass */
        $valueClass = $EavModel->valueClass;

        $models = $valueClass::findAll(
            [
                'entityId' => $EavModel->entityModel->getPrimaryKey(),
                'attributeId' => $this->attributeHandler->attributeModel->getPrimaryKey(),
            ]
        );

        $values = [];
        foreach ($models as $model) {
            $values[] = $model->option->value;
        }

        return implode(', ', $values);
    }

    public function addRules()
    {
        $model = &$this->attributeHandler->owner;
        $attribute = &$this->attributeHandler->attributeModel;
        $attribute_name = $this->attributeHandler->getAttributeName();
    }
}