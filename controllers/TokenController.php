<?php
namespace davidxu\oauth2\controllers;

use League\OAuth2\Server\Exception\OAuthServerException;
use davidxu\oauth2\components\web\OauthHttpException;
use davidxu\oauth2\models\AccessToken;
use davidxu\oauth2\Module;
use yii\filters\auth\CompositeAuth;
use yii\filters\Cors;
use yii\helpers\Json;
use yii\rest\ActiveController;
use yii\web\HttpException;

/**
 * Class TestController
 *
 * @package davidxu\oauth2\controllers
 *
 * @property Module module
 */
class TokenController extends ActiveController {

    /**
     * @var string
     */
    public $modelClass = AccessToken::class;

    /**
     * {@inheritdoc}
     */
    public function actions() {
        return [
            'options' => [
                'class' => 'yii\rest\OptionsAction',
            ],
        ];
    }

    /**
     * @return mixed
     * @throws HttpException
     * @throws OauthHttpException
     */
    public function actionCreate() {
        /** @var Module $module */
        $module = $this->module;

        try {
            $request =  $module->getRequest();
            $response = $module->getResponse();

            $response = $module->getAuthorizationServer()->respondToAccessTokenRequest($request,$response);
            return Json::decode($response->getBody()->__toString());
        } catch (OAuthServerException $e) {
            throw new OAuthHttpException($e);
        } catch (\Exception $e) {
            throw new HttpException(500, 'Unable to respond to access token request.', 0,YII_DEBUG ? $e : null);
        }
    }


}