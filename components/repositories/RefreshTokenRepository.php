<?php
namespace davidxu\oauth2\components\repositories;

use Exception;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use davidxu\oauth2\models\RefreshToken;
use Throwable;
use yii\db\StaleObjectException;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{

    /**
     * Creates a new refresh token
     *
     * @return RefreshToken|RefreshTokenEntityInterface
     */
    public function getNewRefreshToken(): RefreshToken|RefreshTokenEntityInterface
    {
        return new RefreshToken();
    }

    /**
     * Create a new refresh token_name.
     *
     * @param ?RefreshTokenEntityInterface $refreshTokenEntity
     *
     * @return RefreshTokenEntityInterface
     * @throws OAuthServerException|Exception
     */
    public function persistNewRefreshToken(?RefreshTokenEntityInterface $refreshTokenEntity): RefreshTokenEntityInterface
    {
        if ($refreshTokenEntity instanceof  RefreshToken) {
            $refreshTokenEntity->expired_at = $refreshTokenEntity->getExpiryDateTime()->getTimestamp();
            $refreshTokenEntity->save();

            if ($refreshTokenEntity->save()) {
               return $refreshTokenEntity;
            } else {
                throw new Exception(print_r($refreshTokenEntity->getErrors(),true));
            }
        }
        throw OAuthServerException::serverError('Refresh token failure');
    }

    /**
     * Revoke an access token.
     *
     * @param string|int $tokenId
     * @throws Throwable|StaleObjectException
     */
    public function revokeRefreshToken($tokenId): void
    {
        // TODO: Implement revokeAccessToken() method.
        $token = RefreshToken::find()->where(['identifier'=>$tokenId])->one();
        if ($token instanceof RefreshToken) {
           $token->delete();
        }
    }

    /**
     * Check if the access token has been revoked.
     *
     * @param string|int $tokenId
     *
     * @return bool Return true if this token has been revoked
     */
    public function isRefreshTokenRevoked($tokenId): bool
    {
        $token = RefreshToken::find()->where(['identifier'=>$tokenId])->one();
        return $token === null;
    }
}
