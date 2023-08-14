<?php
namespace davidxu\oauth2\controllers;

use yii\web\User;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use davidxu\oauth2\components\web\OauthHttpException;
use davidxu\oauth2\components\web\ServerResponse;
use davidxu\oauth2\Module;
use Yii;
use yii\web\Controller;
use yii\web\HttpException;
use Throwable;

/**
 * Class TestController
 *
 * @package davidxu\oauth2\controllers
 *
 * @property Module module
 */
class AuthorizeController extends Controller {

    /**
     * @throws HttpException
     * @throws OauthHttpException
     * @throws Throwable
     */
    public function actionIndex(): void
    {
        $module = $this->module;
        try {
            $request =  $module->getRequest();
            $response = $module->getResponse();

            // Validate the HTTP request and return an AuthorizationRequest object.
            $authRequest = $module->getAuthorizationServer()->validateAuthorizationRequest($request);

            // The auth request object can be serialized and saved into a user's session.
            // You will probably want to redirect the user at this point to a login endpoint.

            if (Yii::$app->user->isGuest) {
                Yii::$app->user->setReturnUrl(Yii::$app->request->url);
//                return $this->redirect(Yii::$app->user->loginUrl)->send();
                $this->redirect(Yii::$app->user->loginUrl)->send();
            }


            // Once the user has logged in set the user on the AuthorizationRequest
            /** @var UserEntityInterface|User $user */
            $user = Yii::$app->user->getIdentity();
            $authRequest->setUser($user); // an instance of UserEntityInterface

            // At this point you should redirect the user to an authorization page.
            // This form will ask the user to approve the client and the scopes requested.


            // Once the user has approved or denied the client update the status
            // (true = approved, false = denied)
            $authRequest->setAuthorizationApproved(true);

            /** @var ServerResponse $authResponse */
            $authResponse = $module->getAuthorizationServer()->completeAuthorizationRequest($authRequest, $response);

            $authResponse->send();

        } catch (OAuthServerException $e) {
            throw new OAuthHttpException($e);
        } catch (\Exception $e) {
            throw new HttpException(500, 'Unable to respond to access token request.', 0, YII_DEBUG ? $e : null);
        }
    }
}
