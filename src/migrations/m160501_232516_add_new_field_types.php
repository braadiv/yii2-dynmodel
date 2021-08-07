<?php

use braadiv\dynmodel\handlers\ValueHandler;
use yii\db\Migration;

class m160501_232516_add_new_field_types extends Migration
{
    const TABLE_NAME = '{{%eav_attribute_type}}';

    public function safeUp()
    {
        $this->insert(
            self::TABLE_NAME,
            [
                'name' => 'numiric',
                'storeType' => ValueHandler::STORE_TYPE_RAW,
                'handlerClass' => '\braadiv\dynmodel\widgets\NumericInput',
            ]
        );
    }

    public function safeDown()
    {
        $this->delete(
            self::TABLE_NAME,
            [
                'name' => 'numiric',
                'storeType' => ValueHandler::STORE_TYPE_RAW,
                'handlerClass' => '\braadiv\dynmodel\widgets\NumericInput',
            ]
        );
    }
}
