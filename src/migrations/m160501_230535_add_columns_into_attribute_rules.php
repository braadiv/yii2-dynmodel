<?php

use yii\db\Migration;

class m160501_230535_add_columns_into_attribute_rules extends Migration
{
    const TABLE_NAME = '{{%eav_attribute_rules}}';

    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, 'required', $this->smallInteger(1)->defaultValue(0));
        $this->addColumn(self::TABLE_NAME, 'visible', $this->smallInteger(1)->defaultValue(0));
        $this->addColumn(self::TABLE_NAME, 'locked', $this->smallInteger(1)->defaultValue(0));
    }

    public function safeDown()
    {
        $this->dropColumn(self::TABLE_NAME, 'required');
        $this->dropColumn(self::TABLE_NAME, 'visible');
        $this->dropColumn(self::TABLE_NAME, 'locked');
    }
}
