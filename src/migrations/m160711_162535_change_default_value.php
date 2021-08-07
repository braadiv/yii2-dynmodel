<?php

use yii\db\Migration;

class m160711_162535_change_default_value extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%eav_attribute}}', 'description', $this->string(255)->defaultValue(''));
    }

    public function safeDown()
    {
        $this->alterColumn('{{%eav_attribute}}', 'description', $this->string(255)->defaultValue('NULL'));
    }
}
