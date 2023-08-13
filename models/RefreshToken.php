<?php

namespace davidxu\oauth2\models;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Client = andere applicatie die connectie maakt met ons als oauth2 server
 * This is the model class for table "oauth_refresh_token".
 *
 * @property int $id
 * @property int $access_token_id
 * @property string $identifier
 * @property int $created_at
 * @property int $updated_at
 * @property int $expired_at
 * @property int $status
 */

class RefreshToken extends ActiveRecord implements RefreshTokenEntityInterface {


    protected ?string $refreshTokenTable = '{{%oauth_refresh_token}}';

    public function init()
    {
        parent::init();
        if (Yii::$app->params['davidxu.oauth2.table']) {
            $this->refreshTokenTable = Yii::$app->params['davidxu.oauth2.table']['authRefreshTokenTable'] ?? $this->refreshTokenTable;
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return $this->refreshTokenTable;
    }

    public function behaviors() {
        return ArrayHelper::merge(parent::behaviors(),[
            TimestampBehavior::class,
        ]);
    }

    /**
     * Get the token's identifier.
     *
     * @return string
     */
    public function getIdentifier() {
        return $this->identifier;
    }

    /**
     * Set the token's identifier.
     *
     * @param mixed $identifier
     */
    public function setIdentifier($identifier) {
        $this->identifier = $identifier;
    }

    /**
     * @inheritDoc
     */
    public function getExpiryDateTime() {
        return (new \DateTimeImmutable())->setTimestamp($this->expired_at);
    }

    /**
     * @inheritDoc
     */
    public function setExpiryDateTime(\DateTimeImmutable $dateTime) {
        $this->expired_at = $dateTime->getTimestamp();
    }

    /**
     * {@inheritdoc}
     *
     * @param AccessTokenEntityInterface|ActiveRecord $accessToken
     */
    public function setAccessToken(AccessTokenEntityInterface $accessToken)
    {
       $this->access_token_id = $accessToken->getPrimaryKey();
    }


    /**
     * Get the access token that the refresh token was originally associated with.
     *
     * @return AccessTokenEntityInterface
     */
    public function getAccessToken() {
        return $this->relatedAccessToken;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRelatedAccessToken()
    {
        return $this->hasOne(AccessToken::class, ['id' => 'access_token_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['access_token_id', 'identifier'], 'required'],
            ['access_token_id', 'exist', 'targetClass' => AccessToken::class, 'targetAttribute' => 'id'],
            ['identifier', 'unique'],
            [['created_at', 'updated_at'], 'default', 'value' => time()],
        ];
    }
}