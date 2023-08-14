<?php
namespace davidxu\oauth2\models;

use DateTimeImmutable;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;
use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "oauth_access_token".
 *
 * @property int $id
 * @property int $client_id
 * @property int $user_id
 * @property string $identifier
 * @property int $type
 * @property int $created_at
 * @property int $updated_at
 * @property int $expired_at
 * @property int $status
 *
 * @property Scope[] relatedScopes
 * @property Client relatedClient
 */
class AccessToken extends ActiveRecord implements AccessTokenEntityInterface
{

    use AccessTokenTrait,TokenEntityTrait;

    const STATUS_ACTIVE = 1;
    const STATUS_REVOKED = -10;

    protected $scopes = [];

    protected static ?string $accessTokenTable = '{{%oauth_access_token}}';
    protected static ?string $accessTokenScopeTable = '{{%oauth_access_token_scope}}';
    protected static ?string $clientTable = '{{%oauth_client}}';

    public function init(): void
    {
        parent::init();
        if (isset(Yii::$app->params['davidxu.oauth2.table'])) {
            self::$clientTable = Yii::$app->params['davidxu.oauth2.table']['authClientTable']
                ?? self::$clientTable;
            self::$accessTokenTable = Yii::$app->params['davidxu.oauth2.table']['authAccessTokenTable']
                ?? self::$accessTokenTable;
            self::$accessTokenScopeTable = Yii::$app->params['davidxu.oauth2.table']['authAccessTokenScopeTable']
                ?? self::$accessTokenScopeTable;
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName(): ?string
    {
        return self::$accessTokenTable;
    }

    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(),[
            TimestampBehavior::class,
        ]);
    }

    public function afterFind(): void
    {
        foreach($this->relatedScopes as $scope) {
            $this->addScope($scope);
        }
    }

    /**
     * @inheritDoc
     */
    public function getExpiryDateTime(): DateTimeImmutable
    {
        return (new DateTimeImmutable())->setTimestamp($this->expired_at);
    }

    /**
     * @inheritDoc
     */
    public function setExpiryDateTime(DateTimeImmutable $dateTime): void
    {
        $this->expired_at = $dateTime->getTimestamp();
    }

    /**
     * Set the identifier of the user associated with the token.
     *
     * @param string|int|null $identifier The identifier of the user
     */
    public function setUserIdentifier($identifier): void
    {
        $this->user_id = $identifier;
    }

    /**
     * Get the token user's identifier.
     *
     * @return int|string
     */
    public function getUserIdentifier(): int|string
    {
        return $this->user_id;
    }

    /**
     * Set the client that the token was issued to.
     *
     * @param ClientEntityInterface $client
     */
    public function setClient(ClientEntityInterface $client): void
    {
        $this->client_id = $client->id;
    }

//    /**
//     * Associate a scope with the token.
//     *
//     * @param ScopeEntityInterface $scope
//     */
//    public function addScope(ScopeEntityInterface $scope) {
//        // TODO: Implement addScope() method.
//        var_dump($scope);exit;
//    }


    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['client_id'], 'required'], // identifier
            [['user_id'], 'default'],
            ['status', 'default', 'value' => static::STATUS_ACTIVE],
            ['status', 'in', 'range' => [static::STATUS_REVOKED, static::STATUS_ACTIVE]],
        ];
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->relatedClient;
    }

    /**
     * Gets query of [[Client]]
     * @return ActiveQuery
     */
    public function getRelatedClient(): ActiveQuery
    {
        return $this->hasOne(Client::class, ['id' => 'client_id']);
    }


    /**
     * Associate a scope with the token.
     *
     * @param ScopeEntityInterface $scope
     */
    public function addScope(ScopeEntityInterface $scope): void
    {
        $this->scopes[$scope->getIdentifier()] = $scope;
    }

    /**
     * @throws InvalidConfigException
     */
    public function getRelatedScopes(): ActiveQuery
    {
        return $this->hasMany(Scope::class, ['id' => 'scope_id'])
            ->viaTable(self::$accessTokenScopeTable, ['access_token_id' => 'id']);
    }

    /**
     * @return array
     */
    public function getScopes(): array
    {
        return array_keys($this->scopes);
    }

    /**
     * Get the token's identifier.
     *
     * @return string|int
     */
    public function getIdentifier(): string|int
    {
        return $this->identifier;
    }

    /**
     * Set the token's identifier.
     *
     * @param mixed $identifier
     */
    public function setIdentifier($identifier): void
    {
        $this->identifier = $identifier;
    }
}
