<?php

namespace davidxu\oauth2\models;

use DateTimeImmutable;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "oauth_refresh_token".
 *
 * @property int $id
 * @property int $access_token_id
 * @property string $identifier
 * @property int $created_at
 * @property int $updated_at
 * @property int $expired_at
 * @property int $status
 *
 * @property AccessToken $relatedAccessToken
 */

class RefreshToken extends ActiveRecord implements RefreshTokenEntityInterface
{

    protected static ?string $refreshTokenTable = '{{%oauth_refresh_token}}';

    public function init(): void
    {
        parent::init();
        if (isset(Yii::$app->params['davidxu.oauth2.table'])) {
            self::$refreshTokenTable = Yii::$app->params['davidxu.oauth2.table']['authRefreshTokenTable']
                ?? self::$refreshTokenTable;
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName(): ?string
    {
        return self::$refreshTokenTable;
    }

    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(),[
            TimestampBehavior::class,
        ]);
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
     * {@inheritdoc}
     *
     * @param AccessTokenEntityInterface|ActiveRecord $accessToken
     */
    public function setAccessToken(AccessTokenEntityInterface $accessToken): void
    {
       $this->access_token_id = $accessToken->getPrimaryKey();
    }


    /**
     * Get the access token that the refresh token was originally associated with.
     *
     * @return AccessTokenEntityInterface
     */
    public function getAccessToken(): AccessTokenEntityInterface
    {
        return $this->relatedAccessToken;
    }

    /**
     * Gets query of [[RelatedAccessToken]]
     * @return ActiveQuery
     */
    public function getRelatedAccessToken(): ActiveQuery
    {
        return $this->hasOne(AccessToken::class, ['id' => 'access_token_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['access_token_id', 'identifier'], 'required'],
            ['access_token_id', 'exist', 'targetClass' => AccessToken::class, 'targetAttribute' => 'id'],
            ['identifier', 'unique'],
            [['created_at', 'updated_at'], 'default', 'value' => time()],
        ];
    }
}