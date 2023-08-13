<?php

use yii\db\Migration;
use yii\db\Schema;

/**
 * Class m190716_104500_v8_updates
 */
class m190716_104500_v8_updates extends Migration
{

    private ?string $clientTable = '{{%oauth_client}}';

    public function init()
    {
        parent::init();
        
        if (Yii::$app->params['davidxu.oauth2.table']) {
            $this->clientTable = Yii::$app->params['davidxu.oauth2.table']['authClientTable'] ?? $this->clientTable;
        }
    }

    public function safeUp()
    {
        $this->addColumn($this->clientTable, 'is_confidential',$this->boolean()->notNull()->defaultValue(1).' AFTER `token_type`');
    }

    public function safeDown()
    {
        $this->dropColumn($this->clientTable, 'is_confidential');
    }

}