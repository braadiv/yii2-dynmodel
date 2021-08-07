<?php

use braadiv\dynmodel\handlers\ValueHandler;
use yii\db\Migration;

class m150821_133232_init extends Migration
{
    public $tables;
    public $attributeTypes = [];

    public function init()
    {
        $this->tables = [
            'entity' => "{{%eav_entity}}",
            'attribute' => "{{%eav_attribute}}",
            'attribute_type' => "{{%eav_attribute_type}}",
            'value' => "{{%eav_attribute_value}}",
            'option' => "{{%eav_attribute_option}}",
        ];

        $this->attributeTypes = [
            [
                'name' => 'text',
                'storeType' => ValueHandler::STORE_TYPE_RAW,
                'handlerClass' => '\braadiv\dynmodel\widgets\TextInput',
            ],
            [
                'name' => 'option',
                'storeType' => ValueHandler::STORE_TYPE_OPTION,
                'handlerClass' => '\braadiv\dynmodel\widgets\DropDownList',
            ],
            [
                'name' => 'checkbox',
                'storeType' => ValueHandler::STORE_TYPE_MULTIPLE_OPTIONS,
                'handlerClass' => '\braadiv\dynmodel\widgets\CheckBoxList',
            ],
            [
                'name' => 'array',
                'storeType' => ValueHandler::STORE_TYPE_ARRAY,
                'handlerClass' => '\braadiv\dynmodel\widgets\EncodedTextInput',
            ],
            [
                'name' => 'radio',
                'storeType' => ValueHandler::STORE_TYPE_OPTION,
                'handlerClass' => '\braadiv\dynmodel\widgets\RadioList',
            ],
            [
                'name' => 'area',
                'storeType' => ValueHandler::STORE_TYPE_RAW,
                'handlerClass' => '\braadiv\dynmodel\widgets\Textarea',
            ],
        ];
    }

    public function safeUp()
    {
        $options = $this->db->driverName == 'mysql'
            ? 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB'
            : null;

        $this->createTable(
            $this->tables['entity'],
            [
                'id' => $this->primaryKey(11),
                'entityName' => $this->string(50),
                'entityModel' => $this->string(100),
                'categoryId' => $this->integer(11),
            ],
            $options
        );

        $this->createTable(
            $this->tables['attribute'],
            [
                'id' => $this->primaryKey(11),
                'entityId' => $this->integer(11)->defaultValue(null),
                'typeId' => $this->integer(11)->defaultValue(null),
                'type' => $this->string(50)->defaultValue(''),
                'name' => $this->string(255)->defaultValue('NULL'),
                'label' => $this->string(255)->defaultValue('NULL'),
                'label_en' => $this->string(255)->defaultValue('NULL'),
                'defaultValue' => $this->string(255)->defaultValue('NULL'),
                'defaultOptionId' => $this->integer(11)->defaultValue(0),
                'description' => $this->string(255)->defaultValue('NULL'),
                'description_en' => $this->string(255)->defaultValue('NULL'),
                'required' => $this->smallInteger(1)->defaultValue(0),
                'order' => $this->integer(11)->defaultValue(0),
            ],
            $options
        );

        $this->createTable(
            $this->tables['attribute_type'],
            [
                'id' => $this->primaryKey(11),
                'name' => $this->string(255)->defaultValue('NULL'),
                'handlerClass' => $this->string(255)->defaultValue('NULL'),
                'storeType' => $this->smallInteger(6)->defaultValue(0),
            ],
            $options
        );

        $this->createTable(
            $this->tables['value'],
            [
                'id' => $this->primaryKey(11),
                'entityId' => $this->integer(11)->defaultValue(0),
                'attributeId' => $this->integer(11)->defaultValue(0),
                'value' => $this->string(255)->defaultValue('NULL'),
                'optionId' => $this->integer(11)->defaultValue(0),
                'plan_app_id' => $this->integer(11)->defaultValue(0),
                'order' => $this->integer(11)->defaultValue('NULL'),
            ],
            $options
        );

        $this->createTable(
            $this->tables['option'],
            [
                'id' => $this->primaryKey(11),
                'attributeId' => $this->integer(11)->defaultValue(0),
                'value' => $this->string(255)->defaultValue('NULL'),
                'value_en' => $this->string(255)->defaultValue('NULL'),
                'index_value' => $this->string(255)->defaultValue('NULL'),
                'defaultOptionId' => $this->smallInteger(1)->defaultValue(0),
            ],
            $options
        );

        if ($this->db->driverName != "sqlite") {
            $this->addForeignKey(
                'FK_Attribute_typeId',
                $this->tables['attribute'],
                'typeId',
                $this->tables['attribute_type'],
                'id',
                "CASCADE",
                "NO ACTION"
            );

            $this->addForeignKey(
                'FK_EntityId',
                $this->tables['attribute'],
                'entityId',
                $this->tables['entity'],
                'id',
                "CASCADE",
                "NO ACTION"
            );

            $this->addForeignKey(
                'FK_Value_entityId',
                $this->tables['value'],
                'entityId',
                $this->tables['entity'],
                'id',
                "CASCADE",
                "NO ACTION"
            );

            $this->addForeignKey(
                'FK_Value_attributeId',
                $this->tables['value'],
                'attributeId',
                $this->tables['attribute'],
                'id',
                "CASCADE",
                "NO ACTION"
            );

            $this->addForeignKey(
                'FK_Value_optionId',
                $this->tables['value'],
                'optionId',
                $this->tables['option'],
                'id',
                "CASCADE",
                "NO ACTION"
            );

            $this->addForeignKey(
                'FK_Option_attributeId',
                $this->tables['option'],
                'attributeId',
                $this->tables['attribute'],
                'id',
                "CASCADE",
                "NO ACTION"
            );
        }

        foreach ($this->attributeTypes as $columns) {
            $this->insert($this->tables['attribute_type'], $columns);
        }
    }

    public function safeDown()
    {
        if ($this->db->driverName != "sqlite") {
            $this->dropForeignKey('FK_Attribute_typeId', $this->tables['attribute']);
            $this->dropForeignKey('FK_EntityId', $this->tables['attribute']);
            $this->dropForeignKey('FK_Value_entityId', $this->tables['value']);
            $this->dropForeignKey('FK_Value_attributeId', $this->tables['value']);
            $this->dropForeignKey('FK_Value_optionId', $this->tables['value']);
            $this->dropForeignKey('FK_Option_attributeId', $this->tables['option']);
        }

        $this->dropTable($this->tables['attribute']);
        $this->dropTable($this->tables['attribute_type']);
        $this->dropTable($this->tables['value']);
        $this->dropTable($this->tables['option']);
        $this->dropTable($this->tables['entity']);
    }

}
