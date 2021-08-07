<?php

use yii\db\Migration;

class m160731_142546_alter_table_eav_attribute extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%eav_attribute}}', 'categoryId', $this->integer(11)->null());
    }

    public function safeDown()
    {
        // Just pass. It's was already nullable before this migration
    }
}
