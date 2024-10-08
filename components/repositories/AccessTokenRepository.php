<?php
namespace davidxu\oauth2\components\repositories;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use davidxu\oauth2\models\AccessToken;
use davidxu\oauth2\models\Scope;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use yii\helpers\Json;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{

    /**
     * Create a new access token
     *
     * @param ClientEntityInterface $clientEntity
     * @param ScopeEntityInterface[] $scopes
     * @param mixed $userIdentifier
     *
     * @return AccessToken|AccessTokenEntityInterface
     * @throws OAuthServerException
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null): AccessToken|AccessTokenEntityInterface
    {
        $token = new AccessToken();
        $token->setClient($clientEntity);
        $token->setUserIdentifier($userIdentifier);
        foreach ($scopes as $scope) {
            $token->addScope($scope);
        }
        if (!$token->validate()) {
            throw OAuthServerException::serverError('Could not get new token: '.Json::encode($token->getErrors()));
        }

        return $token;
    }

    /**
     * @inheritDoc
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void
    {
        if ($accessTokenEntity instanceof  AccessToken) {
            $accessTokenEntity->expired_at = $accessTokenEntity->getExpiryDateTime()->getTimestamp();
            if ($accessTokenEntity->save()) {
                $scopeIdentifiers = $accessTokenEntity->getScopes();
                $scopes = Scope::findAll(['identifier' => $scopeIdentifiers]);
                foreach ($scopes as $scope) {
                    if ($scope instanceof Scope) {
                        $accessTokenEntity->link('relatedScopes', $scope);
                    }
                }
            }
        }
    }

    /**
     * Revoke an access token.
     *
     * @param string $tokenId
     */
    public function revokeAccessToken($tokenId): void
    {
        $token = AccessToken::find()->where(['identifier'=>$tokenId])->one();
        if ($token instanceof AccessToken) {
            $token->updateAttributes(['status' => AccessToken::STATUS_REVOKED]);
        }
    }

    /**
     * Check if the access token has been revoked.
     *
     * @param string $tokenId
     *
     * @return bool Return true if this token has been revoked
     */
    public function isAccessTokenRevoked($tokenId): bool
    {
        $token = AccessToken::find()->where(['identifier'=>$tokenId])->one();
        return $token === null || (int)$token->status === AccessToken::STATUS_REVOKED;
    }
}
