<?php

use yii\db\Migration;

/**
 * Class m190716_104500_v8_updates
 */
class m190716_104500_v8_updates extends Migration
{

    private static ?string $clientTable = '{{%oauth_client}}';

    public function init(): void
    {
        parent::init();
        
        if (isset(Yii::$app->params['davidxu.oauth2.table'])) {
            self::$clientTable = Yii::$app->params['davidxu.oauth2.table']['authClientTable']
                ?? self::$clientTable;
        }
    }

    public function safeUp(): void
    {
        $this->addColumn(self::$clientTable, 'is_confidential',
            $this->boolean()->notNull()->defaultValue(1).' AFTER `token_type`'
        );
    }

    public function safeDown(): void
    {
        $this->dropColumn(self::$clientTable, 'is_confidential');
    }

}