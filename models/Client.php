<?php
namespace davidxu\oauth2\models;

use Exception;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "oauth_client".
 *
 * @property int $id
 * @property string $identifier
 * @property string $secret
 * @property string $name
 * @property string $redirect_uri
 * @property int $token_type
 * @property int $is_confidential
 * @property int $grant_type
 * @property int $created_at
 * @property int $updated_at
 * @property int $status
 *
 * @property Scope[] $scopes
 */
class Client extends ActiveRecord implements ClientEntityInterface {


    const STATUS_ACTIVE = 1;
    const STATUS_DISABLED = -1;

    const GRANT_TYPE_CLIENT_CREDENTIALS = 1;
    const GRANT_TYPE_PASSWORD = 2;

    const GRANT_TYPE_AUTHORIZATION_CODE = 3;
    const GRANT_TYPE_IMPLICIT = 4;
    const GRANT_TYPE_REFRESH_TOKEN = 5;

    protected static ?string $clientTable = '{{%oauth_client}}';
    protected static ?string $clientScopeTable = '{{%oauth_client_scope}}';

    public function init(): void
    {
        parent::init();
        if (isset(Yii::$app->params['davidxu.oauth2.table'])) {
            self::$clientTable = Yii::$app->params['davidxu.oauth2.table']['authClientTable']
                ?? self::$clientTable;
            self::$clientScopeTable = Yii::$app->params['davidxu.oauth2.table']['authClientScopeTable']
                ?? self::$clientScopeTable;
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName(): ?string
    {
        return self::$clientTable;
    }

    public static function getGrantTypeOptions(): array
    {
        return [
            static::GRANT_TYPE_AUTHORIZATION_CODE => 'authorization_code',
            static::GRANT_TYPE_IMPLICIT => 'implicit',
            static::GRANT_TYPE_PASSWORD => 'password',
            static::GRANT_TYPE_CLIENT_CREDENTIALS => 'client_credentials',
            static::GRANT_TYPE_REFRESH_TOKEN => 'refresh_token',
        ];
    }

    /**
     * @throws Exception
     */
    public static function getGrantTypeId($grantType, $default = null)
    {
        return ArrayHelper::getValue(array_flip(static::getGrantTypeOptions()), $grantType, $default);
    }

    /**
    * @inheritdoc
    */
    public function behaviors(): array
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * Get the client's identifier.
     *
     * @return string|int|object
     */
    public function getIdentifier(): string|int|object
    {
        return $this->identifier;
    }

    /**
     * Get the client's name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the registered redirect URI (as a string).
     *
     * Alternatively return an indexed array of redirect URIs.
     *
     * @return string
     */
    public function getRedirectUri(): string
    {
        return $this->redirect_uri;
    }

    /**
     * @param $secret
     * @return bool
     */
    public function validateSecret($secret): bool
    {
        return password_verify($secret,$this->secret);
    }


    public function hashSecret($secret): string
    {
        return password_hash($secret,PASSWORD_DEFAULT);
    }

    public function attributeLabels(): array
    {
        return [
            'identifier' => Yii::t('oauth2','Client ID'),
            'secret' => Yii::t('oauth2','Client secret'),
            'is_confidential' => Yii::t('oauth2','Can the client store secrets? (Confidential client)'),
        ];
    }



    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['identifier','secret','name','redirect_uri'], 'required'],
            [['is_confidential'],'boolean']
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert): bool
    {
        if ($this->isNewRecord) {
            $this->secret = $this->hashSecret($this->secret);
        }
        return parent::beforeSave($insert);
    }


    /**
     * @throws InvalidConfigException
     */
    public function getScopes(callable $filter): ActiveQuery
    {
        return $this->hasMany(Scope::class, ['id' => 'scope_id'])
            ->viaTable(self::$clientScopeTable, ['client_id' => 'id'], $filter);
    }


    /**
     * @inheritdoc
     */
    public function isConfidential(): bool
    {
        return (bool)$this->is_confidential;
    }
}