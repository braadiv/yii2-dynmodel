<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 */

namespace braadiv\dynmodel\handlers;

use braadiv\dynmodel\widgets\AttributeHandler;
use yii\db\ActiveRecord;

/**
 * Class ValueHandler
 *
 * @package braadiv\dynmodel
 * @property ActiveRecord $valueModel
 * @property string $textValue
 */
abstract class ValueHandler
{
    const STORE_TYPE_RAW = 0;
    const STORE_TYPE_OPTION = 1;
    const STORE_TYPE_MULTIPLE_OPTIONS = 2;
    const STORE_TYPE_ARRAY = 3; // Json encoded

    /** @var AttributeHandler */
    public $attributeHandler;

    /**
     * @return ActiveRecord
     * @throws \Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function getValueModel()
    {
        $EavModel = $this->attributeHandler->owner;

        /** @var ActiveRecord $valueClass */
        $valueClass = $EavModel->valueClass;
 // echo "<pre>";
        // print_r($EavModel->plan_app_id); die();
        $valueModel = $valueClass::findOne(
            [
                'entityId' => $EavModel->entityModel->getPrimaryKey(),
                'attributeId' => $this->attributeHandler->attributeModel->getPrimaryKey(),
                'plan_app_id' => $EavModel->plan_app_id,
                'order' => $EavModel->row_no,
            ]
        );

        if (!$valueModel instanceof ActiveRecord) {
            /** @var ActiveRecord $valueModel */
            $valueModel = new $valueClass;
            $valueModel->entityId = $EavModel->entityModel->getPrimaryKey();
            $valueModel->attributeId = $this->attributeHandler->attributeModel->getPrimaryKey();
            $valueModel->plan_app_id = $EavModel->plan_app_id;
            $valueModel->order = $EavModel->row_no;
        // print_r($valueModel->plan_app_id); die();
            
            // die();
        }

        return $valueModel;
    }

    abstract public function defaultValue();

    abstract public function load();

    abstract public function save();

    abstract public function getTextValue();

    // TODO 7: Add rules from $attributeModel->getEavOptions()
    abstract public function addRules();
}