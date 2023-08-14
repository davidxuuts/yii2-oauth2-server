<?php
namespace davidxu\oauth2\components\repositories;


use frontend\models\Auth;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use davidxu\oauth2\models\AuthCode;
use davidxu\oauth2\models\Scope;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

class AuthCodeRepository implements AuthCodeRepositoryInterface
{

    /**
     * Creates a new AuthCode
     *
     * @return AuthCodeEntityInterface|AuthCode
     */
    public function getNewAuthCode(): AuthCodeEntityInterface|AuthCode
    {
        return new AuthCode();
    }

    /**
     * Persists a new auth code to permanent storage.
     *
     * @param ?AuthCodeEntityInterface $authCodeEntity
     *
     */
    public function persistNewAuthCode(?AuthCodeEntityInterface $authCodeEntity): void
    {
        if ($authCodeEntity instanceof AuthCode) {
            $authCodeEntity->expired_at = $authCodeEntity->getExpiryDateTime()->getTimestamp();
            if ($authCodeEntity->save()) {
                $scopeIdentifiers = $authCodeEntity->getScopes();
                $scopes = Scope::findAll(['identifier' => $scopeIdentifiers]);
                foreach ($scopes as $scope) {
                    if ($scope instanceof Scope) {
                        $authCodeEntity->link('relatedScopes', $scope);
                    }
                }
            }
        }
    }

    /**
     * Revoke an auth code.
     *
     * @param string|int $codeId
     */
    public function revokeAuthCode($codeId): void
    {
        $code = AuthCode::find()->where(['identifier'=>$codeId])->one();
        if ($code instanceof AuthCode) {
            $code->updateAttributes(['status' => AuthCode::STATUS_REVOKED]);
        }
    }

    /**
     * Check if the auth code has been revoked.
     *
     * @param string|int $codeId
     *
     * @return bool Return true if this code has been revoked
     */
    public function isAuthCodeRevoked($codeId): bool
    {
        $code = AuthCode::find()->where(['identifier'=>$codeId])->one();
        return $code === null || $code->status == AuthCode::STATUS_REVOKED;
    }
}
