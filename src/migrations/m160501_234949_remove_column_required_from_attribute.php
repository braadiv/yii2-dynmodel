<?php

use yii\db\Migration;

class m160501_234949_remove_column_required_from_attribute extends Migration
{
    const TABLE_NAME = '{{%eav_attribute}}';

    public function safeUp()
    {
        $this->dropColumn(self::TABLE_NAME, 'required');
    }

    public function safeDown()
    {
        $this->addColumn(self::TABLE_NAME, 'required', $this->smallInteger(1)->defaultValue(0));
    }
}
