<?php

use yii\db\Migration;

class m160711_132535_delete_foregin_key_from_values extends Migration
{
    const TABLE_NAME = '{{%eav_attribute_value}}';

    public function safeUp()
    {
        $this->dropForeignKey('FK_Value_optionId', self::TABLE_NAME);

        $this->dropForeignKey('FK_Value_entityId', self::TABLE_NAME);
    }

    public function safeDown()
    {
        $this->addForeignKey(
            'FK_Value_optionId',
            self::TABLE_NAME,
            'optionId',
            '{{%eav_attribute_option}}',
            'id',
            'CASCADE',
            'NO ACTION'
        );

        $this->addForeignKey(
            'FK_Value_entityId',
            self::TABLE_NAME,
            'entityId',
            '{{%eav_entity}}',
            'id',
            'CASCADE',
            'NO ACTION'
        );
    }
}