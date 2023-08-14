<?php
namespace davidxu\oauth2\components\authMethods;
use Exception;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\ResourceServer;
use davidxu\oauth2\components\repositories\AccessTokenRepository;
use davidxu\oauth2\components\web\OauthHttpException;
use davidxu\oauth2\Module;
use Yii;
use yii\filters\auth\AuthMethod;
use yii\web\HttpException;
use yii\web\IdentityInterface;
use yii\web\Request;
use yii\web\Response;
use yii\web\User;

class HttpBearerAuth extends AuthMethod
{

    private AccessTokenRepository|AccessTokenRepositoryInterface|string|null  $_accessTokenRepository = null;


    /**
     * Authenticates the current user.
     * @param User $user
     * @param Request $request
     * @param Response $response
     * @return IdentityInterface the authenticated user identity. If authentication information is not provided, null will be returned.
     * @throws HttpException|OauthHttpException  if authentication information is provided but is invalid.
     */
    public function authenticate($user, $request, $response): IdentityInterface
    {

        /** @var Module $module */
        $module = Yii::$app->getModule('oauth2');
//        var_dump($module);exit;

        $accessTokenRepository = $this->getAccessTokenRepository();
        $publicKeyPath = Yii::getAlias($module->publicKey);

        try {
            $server = new ResourceServer(
                $accessTokenRepository,
                $publicKeyPath
            );

            $request = $module->getRequest();

            $request = $server->validateAuthenticatedRequest($request);

            $tokenId = $request->getAttribute('oauth_access_token_id');

            /** See also \common\models\User::findIdentityByAccessToken  */
            $identity = $user->loginByAccessToken($tokenId, get_called_class());

            if ($identity === null) {
                throw OAuthServerException::accessDenied('User not found');
            }
            if ($identity->getId() != $request->getAttribute('oauth_user_id')) {
                throw OAuthServerException::accessDenied('User ID does not match');
            }


            return $identity;

        } catch (OAuthServerException $e) {
            throw new OAuthHttpException($e);
        } catch (Exception $e) {
            throw new HttpException(500, 'Unable to validate the request.', 0, YII_DEBUG ? $e : null);
        }


        // profit

    }

    /**
     * @return string|AccessTokenRepositoryInterface|null|AccessTokenRepository
     */
    public function getAccessTokenRepository(): string|AccessTokenRepositoryInterface|null|AccessTokenRepository
    {
        if (!$this->_accessTokenRepository instanceof AccessTokenRepositoryInterface) {
            $this->_accessTokenRepository = new AccessTokenRepository();
        }
        return $this->_accessTokenRepository;
    }
}
