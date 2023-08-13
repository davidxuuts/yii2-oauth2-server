<?php

use yii\db\Migration;
use yii\db\Schema;
use Yii;

/**
 * Class m180517_114315_oauth2
 */
class m180517_114315_oauth2 extends Migration
{    
    private string $tableName = '{{%branch}}';
    private ?string $_tableOptions = null;

    private ?string $clientTable = '{{%oauth_client}}';
    private ?string $accessTokenTable = '{{%oauth_access_token}}';
    private ?string $scopeTable = '{{%oauth_scope}}';
    private ?string $clientScopeTable = '{{%oauth_client_scope}}';
    private ?string $accessTokenScopeTable = '{{%oauth_access_token_scope}}';
    private ?string $refreshTokenTable = '{{%oauth_refresh_token}}';
    private ?string $authCodeTable = '{{%oauth_auth_code}}';
    private ?string $authCodeScopeTable = '{{%oauth_auth_code_scope}}';

    public function init()
    {
        parent::init();
        
        if (Yii::$app->params['davidxu.oauth2.table']) {
            $this->clientTable = Yii::$app->params['davidxu.oauth2.table']['authClientTable'] ?? $this->clientTable;
            $this->accessTokenTable = Yii::$app->params['davidxu.oauth2.table']['authAccessTokenTable'] ?? $this->accessTokenTable;
            $this->scopeTable = Yii::$app->params['davidxu.oauth2.table']['authScopeTable'] ?? $this->scopeTable;
            $this->clientScopeTable = Yii::$app->params['davidxu.oauth2.table']['authClientScopeTable'] ?? $this->clientScopeTable;
            $this->accessTokenScopeTable = Yii::$app->params['davidxu.oauth2.table']['authAccessTokenScopeTable'] ?? $this->accessTokenScopeTable;
            $this->refreshTokenTable = Yii::$app->params['davidxu.oauth2.table']['authRefreshTokenTable'] ?? $this->refreshTokenTable;
            $this->authCodeTable = Yii::$app->params['davidxu.oauth2.table']['authAuthCodeTable'] ?? $this->authCodeTable;
            $this->authCodeScopeTable = Yii::$app->params['davidxu.oauth2.table']['authAuthCodeScopeTable'] ?? $this->authCodeScopeTable;
        }
    }

    private static function _tables()
    {
        return [
            $this->clientTable => [
                'id' => Schema::TYPE_PK,
                'identifier' => Schema::TYPE_STRING . ' NOT NULL',
                'secret' => Schema::TYPE_STRING, // not confidential if null
                'name' => Schema::TYPE_STRING . ' NOT NULL',
                'redirect_uri' => Schema::TYPE_STRING,
                'token_type' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 1', // Bearer
                'grant_type' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 1', // Authorization Code
                'created_at' => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL',
                'updated_at' => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL',
                'status' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 1', // Active,
                'KEY (token_type)',
                'KEY (grant_type)',
                'KEY (status)',
                'KEY (identifier)',
            ],
            $this->accessTokenTable => [
                'id' => Schema::TYPE_PK,
                'client_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'user_id' => Schema::TYPE_INTEGER,
                'identifier' => Schema::TYPE_STRING . ' NOT NULL',
                'created_at' => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL',
                'updated_at' => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL',
                'expired_at' => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL',
                'status' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 1', // Active,
                'FOREIGN KEY (client_id) REFERENCES {{%oauth_client}} (id) ON DELETE CASCADE ON UPDATE CASCADE',
                'KEY (status)',
                'KEY (identifier)',
            ],
            $this->scopeTable => [
                'id' => Schema::TYPE_PK,
                'identifier' => Schema::TYPE_STRING . ' NOT NULL',
                'name' => Schema::TYPE_STRING,
                'KEY (identifier)',
            ],
            $this->clientScopeTable => [
                'id' => Schema::TYPE_PK,
                'client_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'scope_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'user_id' => Schema::TYPE_INTEGER . ' DEFAULT NULL', // common if null
                'grant_type' => Schema::TYPE_SMALLINT . ' DEFAULT NULL', // all grants if null
                'is_default' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 0',
                'UNIQUE KEY (client_id, scope_id, user_id, grant_type)',
                'FOREIGN KEY (client_id) REFERENCES {{%oauth_client}} (id) ON DELETE CASCADE ON UPDATE CASCADE',
                'FOREIGN KEY (scope_id) REFERENCES {{%oauth_scope}} (id) ON DELETE CASCADE ON UPDATE CASCADE',
                'KEY (grant_type)',
                'KEY (is_default)',
            ],
            $this->accessTokenScopeTable => [
                'access_token_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'scope_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'PRIMARY KEY (access_token_id, scope_id)',
                'FOREIGN KEY (access_token_id) REFERENCES {{%oauth_access_token}} (id) ON DELETE CASCADE ON UPDATE CASCADE',
                'FOREIGN KEY (scope_id) REFERENCES {{%oauth_scope}} (id) ON DELETE CASCADE ON UPDATE CASCADE',
            ],
            $this->refreshTokenTable => [
                'id' => Schema::TYPE_PK,
                'access_token_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'identifier' => Schema::TYPE_STRING . ' NOT NULL',
                'created_at' => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL',
                'updated_at' => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL',
                'expired_at' => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL',
                'status' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 1', // Active,
                'FOREIGN KEY (access_token_id) REFERENCES {{%oauth_access_token}} (id) ON DELETE CASCADE ON UPDATE CASCADE',
                'KEY (status)',
                'KEY (identifier)',
            ],
            $this->authCodeTable => [
                'id' => Schema::TYPE_PK,
                'identifier' => Schema::TYPE_STRING . ' NOT NULL',
                'client_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'user_id' => Schema::TYPE_INTEGER,
                'created_at' => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL',
                'updated_at' => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL',
                'expired_at' => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL',
                'status' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 1', // Active,
                'FOREIGN KEY (client_id) REFERENCES {{%oauth_client}} (id) ON DELETE CASCADE ON UPDATE CASCADE',
                'KEY (status)',
                'KEY (identifier)',
            ],
            $this->authCodeScopeTable => [
                'auth_code_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'scope_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'PRIMARY KEY (auth_code_id, scope_id)',
                'FOREIGN KEY (auth_code_id) REFERENCES {{%oauth_auth_code}} (id) ON DELETE CASCADE ON UPDATE CASCADE',
                'FOREIGN KEY (scope_id) REFERENCES {{%oauth_scope}} (id) ON DELETE CASCADE ON UPDATE CASCADE',
            ],
        ];
    }

    public function safeUp()
    {
        if ($this->db->driverName === 'mysql' || $this->db->driverName === 'mariadb') {
            $this->_tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        foreach (static::_tables() as $name => $attributes) {
            try {
                $this->execute('SET foreign_key_checks = 0');
                $this->createTable($name, $attributes, $this->_tableOptions);
                $this->execute('SET foreign_key_checks = 1');
            } catch (\Exception $e) {
                echo $e->getMessage(), "\n";
                return false;
            }
        }

        return true;
    }

    public function safeDown()
    {
        foreach (array_reverse(static::_tables()) as $name => $attributes) {
            try {
                $this->execute('SET foreign_key_checks = 0');
                $this->dropTable($name);
                $this->execute('SET foreign_key_checks = 1');
            } catch (\Exception $e) {
                echo "m160920_072449_oauth cannot be reverted.\n";
                echo $e->getMessage(), "\n";
                return false;
            }
        }

        return true;
    }
}