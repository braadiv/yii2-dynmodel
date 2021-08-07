<?php

use yii\db\Migration;

class m160807_162635_change_default_value_in_eav_attribute_value extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%eav_attribute_value}}', 'value', $this->text()->null());
    }

    public function safeDown()
    {
        $this->alterColumn('{{%eav_attribute_value}}', 'value', $this->string(255)->defaultValue('NULL'));
    }
}
