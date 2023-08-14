<?php
namespace davidxu\oauth2\models;

use DateTimeImmutable;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
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
 *
 */
class AuthCode extends ActiveRecord implements AuthCodeEntityInterface
{
    const STATUS_ACTIVE = 1;
    const STATUS_REVOKED = -10;

    protected array $scopes = [];

    protected ?string $redirectUri;
    
    protected static ?string $authCodeTable = '{{%oauth_auth_code}}';
    protected static ?string $authCodeScopeTable = '{{%oauth_auth_code_scope}}';

    public function init(): void
    {
        parent::init();
        if (isset(Yii::$app->params['davidxu.oauth2.table'])) {
            self::$authCodeTable = Yii::$app->params['davidxu.oauth2.table']['authAuthCodeTable']
                ?? self::$authCodeTable;
            self::$authCodeScopeTable = Yii::$app->params['davidxu.oauth2.table']['authCodeScopeTable']
                ?? self::$authCodeScopeTable;
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName(): ?string
    {
        return self::$authCodeTable;
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
     * Associate a scope with the token.
     *
     * @param ScopeEntityInterface $scope
     */
    public function addScope(ScopeEntityInterface $scope): void
    {
        $this->scopes[$scope->getIdentifier()] = $scope;
    }

    /**
     * @return ActiveQuery
     * @throws InvalidConfigException
     */
    public function getRelatedScopes(): ActiveQuery
    {
        return $this->hasMany(Scope::class, ['id' => 'scope_id'])
            ->viaTable(self::$authCodeScopeTable, ['auth_code_id' => 'id']);
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

    /**
     * @return string|null
     */
    public function getRedirectUri(): ?string
    {
        return $this->redirectUri;
    }

    /**
     * @param string $uri
     */
    public function setRedirectUri($uri): void
    {
        $this->redirectUri = $uri;
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
     * @return string|int|null
     */
    public function getUserIdentifier(): int|string|null
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

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->relatedClient;
    }

    /**
     * @return ActiveQuery
     */
    public function getRelatedClient(): ActiveQuery
    {
        return $this->hasOne(Client::class, ['id' => 'client_id']);
    }

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
}