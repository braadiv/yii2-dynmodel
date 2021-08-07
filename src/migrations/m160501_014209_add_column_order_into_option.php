<?php

use yii\db\Migration;

class m160501_014209_add_column_order_into_option extends Migration
{
    const TABLE_NAME = '{{%eav_attribute_option}}';

    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, 'order', $this->integer(11)->defaultValue(0));
    }

    public function safeDown()
    {
        $this->dropColumn(self::TABLE_NAME, 'order');
    }
}
