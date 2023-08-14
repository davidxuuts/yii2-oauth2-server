<?php

use yii\db\Migration;
use yii\db\Schema;

/**
 * Class m180517_114315_oauth2
 */
class m180517_114315_oauth2 extends Migration
{    
    private ?string $_tableOptions = null;

    private static ?string $clientTable = '{{%oauth_client}}';
    private static ?string $accessTokenTable = '{{%oauth_access_token}}';
    private static ?string $scopeTable = '{{%oauth_scope}}';
    private static ?string $clientScopeTable = '{{%oauth_client_scope}}';
    private static ?string $accessTokenScopeTable = '{{%oauth_access_token_scope}}';
    private static ?string $refreshTokenTable = '{{%oauth_refresh_token}}';
    private static ?string $authCodeTable = '{{%oauth_auth_code}}';
    private static ?string $authCodeScopeTable = '{{%oauth_auth_code_scope}}';

    public function init(): void
    {
        parent::init();
        
        if (isset(Yii::$app->params['davidxu.oauth2.table'])) {
            self::$clientTable = Yii::$app->params['davidxu.oauth2.table']['authClientTable']
                ?? self::$clientTable;
            self::$accessTokenTable = Yii::$app->params['davidxu.oauth2.table']['authAccessTokenTable']
                ?? self::$accessTokenTable;
            self::$scopeTable = Yii::$app->params['davidxu.oauth2.table']['authScopeTable']
                ?? self::$scopeTable;
            self::$clientScopeTable = Yii::$app->params['davidxu.oauth2.table']['authClientScopeTable']
                ?? self::$clientScopeTable;
            self::$accessTokenScopeTable = Yii::$app->params['davidxu.oauth2.table']['authAccessTokenScopeTable']
                ?? self::$accessTokenScopeTable;
            self::$refreshTokenTable = Yii::$app->params['davidxu.oauth2.table']['authRefreshTokenTable']
                ?? self::$refreshTokenTable;
            self::$authCodeTable = Yii::$app->params['davidxu.oauth2.table']['authAuthCodeTable']
                ?? self::$authCodeTable;
            self::$authCodeScopeTable = Yii::$app->params['davidxu.oauth2.table']['authAuthCodeScopeTable']
                ?? self::$authCodeScopeTable;
        }
    }

    private static function _tables(): array
    {
        return [
            self::$clientTable => [
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
            self::$accessTokenTable => [
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
            self::$scopeTable => [
                'id' => Schema::TYPE_PK,
                'identifier' => Schema::TYPE_STRING . ' NOT NULL',
                'name' => Schema::TYPE_STRING,
                'KEY (identifier)',
            ],
            self::$clientScopeTable => [
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
            self::$accessTokenScopeTable => [
                'access_token_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'scope_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'PRIMARY KEY (access_token_id, scope_id)',
                'FOREIGN KEY (access_token_id) REFERENCES {{%oauth_access_token}} (id) ON DELETE CASCADE ON UPDATE CASCADE',
                'FOREIGN KEY (scope_id) REFERENCES {{%oauth_scope}} (id) ON DELETE CASCADE ON UPDATE CASCADE',
            ],
            self::$refreshTokenTable => [
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
            self::$authCodeTable => [
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
            self::$authCodeScopeTable => [
                'auth_code_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'scope_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'PRIMARY KEY (auth_code_id, scope_id)',
                'FOREIGN KEY (auth_code_id) REFERENCES {{%oauth_auth_code}} (id) ON DELETE CASCADE ON UPDATE CASCADE',
                'FOREIGN KEY (scope_id) REFERENCES {{%oauth_scope}} (id) ON DELETE CASCADE ON UPDATE CASCADE',
            ],
        ];
    }

    public function safeUp(): bool
    {
        if ($this->db->driverName === 'mysql' || $this->db->driverName === 'mariadb') {
            $this->_tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        foreach (static::_tables() as $name => $attributes) {
            try {
                $this->execute('SET foreign_key_checks = 0');
                $this->createTable($name, $attributes, $this->_tableOptions);
                $this->execute('SET foreign_key_checks = 1');
            } catch (Exception $e) {
                echo $e->getMessage(), "\n";
                return false;
            }
        }

        return true;
    }

    public function safeDown(): bool
    {
        foreach (array_reverse(static::_tables()) as $name => $attributes) {
            try {
                $this->execute('SET foreign_key_checks = 0');
                $this->dropTable($name);
                $this->execute('SET foreign_key_checks = 1');
            } catch (Exception $e) {
                echo "m180517_114315_oauth2 cannot be reverted.\n";
                echo $e->getMessage(), "\n";
                return false;
            }
        }

        return true;
    }
}