<?php

namespace davidxu\oauth2;

use DateInterval;
use Exception;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\ImplicitGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use davidxu\oauth2\components\repositories\AuthCodeRepository;
use davidxu\oauth2\components\repositories\RefreshTokenRepository;
use davidxu\oauth2\components\web\ServerRequest;
use davidxu\oauth2\components\web\ServerResponse;
use davidxu\oauth2\components\repositories\AccessTokenRepository;
use davidxu\oauth2\components\repositories\ClientRepository;
use davidxu\oauth2\components\repositories\ScopeRepository;
use davidxu\oauth2\controllers\AuthorizeController;
use davidxu\oauth2\controllers\ClientsController;
use davidxu\oauth2\controllers\TokenController;
use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\InvalidConfigException;
use yii\filters\Cors;
use yii\rest\UrlRule;

class Module extends \yii\base\Module implements BootstrapInterface {

    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'davidxu\oauth2\controllers';


    /**
     * @var string Class to use as UserRepository
     */
    public $userRepository;

    /**
     * @var string Alias to the private key file
     */
    public string $privateKey;

    /**
     * @var string Alias to the public key file
     */
    public string $publicKey;

    /**
     * @var string A random encryption key. For example, you could create one with base64_encode(random_bytes(32))
     */
    public string $encryptionKey;

    /**
     * @var string The period an accessToken should be valid for. Defaults to PT1H (1 hour). See DateInterval.
     */
    public string $accessTokenTTL = 'PT1H';

    /**
     * @var string The period an accessToken should be valid for. Defaults to PT1H (1 hour). See DateInterval.
     */
    public string $refreshTokenTTL = 'P1M';

    /**
     * @var bool Enable the Client Credentials Grant (https://oauth2.thephpleague.com/authorization-server/client-credentials-grant/)
     */
    public bool $enableClientCredentialsGrant = true;

    /**
     * @var bool Enable the Password Grant (https://oauth2.thephpleague.com/authorization-server/resource-owner-password-credentials-grant/)
     */
    public bool $enablePasswordGrant = true;

    /**
     * @var bool Enable the Authorization Code Grant (https://oauth2.thephpleague.com/authorization-server/auth-code-grant/)
     */
    public bool $enableAuthorizationCodeGrant = true;

    /**
     * @var bool Enable the Implicit Grant (https://oauth2.thephpleague.com/authorization-server/implicit-grant/
     */
    public bool $enableImplicitGrant = false;

    public array $urlManagerRules = [];

    public bool $enableClientsController = true;

    public $controllerMap = [
        'authorize' => [
            'class' => AuthorizeController::class,
            'as corsFilter' => Cors::class,
        ],
        'token' => [
            'class' => TokenController::class,
            'as corsFilter' => Cors::class,
        ],
        'clients' => [
            'class' => ClientsController::class,
        ]
    ];


    /**
     * Sets module's URL manager rules on application's bootstrap.
     * @param Application $app
     */
    public function bootstrap($app): void
    {
        $app->getUrlManager()
            ->addRules([
                [
                    'class' => UrlRule::class,
                    'pluralize' => false,
                    'only' => ['create', 'options'],
                    'extraPatterns' => [
                        'OPTIONS <action:\w+>' => 'options'
                    ],
                    'controller' => [$this->uniqueId . '/token']
                ]
            ],false);
    }

    /**
     * @return null|AuthorizationServer
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function getAuthorizationServer(): ?AuthorizationServer
    {
        if (!$this->has('server')) {

            $clientRepository = new ClientRepository();
            $accessTokenRepository = new AccessTokenRepository();
            $authCodeRepository = new AuthCodeRepository();
            $refreshTokenRepository = new RefreshTokenRepository();
            $userRepository = new $this->userRepository;
            $scopeRepository = new ScopeRepository();

            $server = new AuthorizationServer(
                $clientRepository,
                $accessTokenRepository,
                $scopeRepository,
                Yii::getAlias($this->privateKey),
                $this->encryptionKey
            );

            $enableRefreshGrant = false;

            /* Client Credentials Grant */
            if ($this->enableClientCredentialsGrant) {
                $server->enableGrantType(
                    new ClientCredentialsGrant(),
                    new DateInterval($this->accessTokenTTL)
                );
            }

            /* Client Credentials Grant */
            if ($this->enableImplicitGrant) {
                $server->enableGrantType(
                    new ImplicitGrant(new DateInterval($this->accessTokenTTL)),
                    new DateInterval($this->accessTokenTTL)
                );
            }

            /* Password Grant */
            if ($this->enablePasswordGrant) {
                $server->enableGrantType(new PasswordGrant(
                    $userRepository,
                    $refreshTokenRepository
                ));
                $enableRefreshGrant = true;
            }

            /* Authorization Code Flow Grant */
            if ($this->enableAuthorizationCodeGrant) {
                $grant = new AuthCodeGrant(
                    $authCodeRepository,
                    $refreshTokenRepository,
                    new DateInterval($this->refreshTokenTTL)
                );
                $grant->setRefreshTokenTTL(new DateInterval($this->refreshTokenTTL));
                $server->enableGrantType($grant);
                $enableRefreshGrant = true;
            }

            /* Refresh Token Grant */
            if ($enableRefreshGrant) {
                $grant = new RefreshTokenGrant(
                    $refreshTokenRepository
                );
                $grant->setRefreshTokenTTL(new DateInterval($this->refreshTokenTTL));
                $server->enableGrantType($grant);
            }

            $this->set('server',$server);
        }
        return $this->get('server');
    }


    /**
     * @var ServerRequest
     */
    private $_psrRequest;

    /**
     * Create a PSR-7 compatible request from the Yii2 request object
     * @return ServerRequest|static
     */
    public function getRequest() {
        if ($this->_psrRequest === null) {
            $request = Yii::$app->request;
            $this->_psrRequest = (new ServerRequest($request))->withParsedBody($request->bodyParams)->withQueryParams($request->queryParams);
        }
        return $this->_psrRequest;
    }


    /**
     * @var ServerResponse
     */
    private $_psrResponse;

    /**
     * @return ServerResponse|static
     */
    public function getResponse() {
        if ($this->_psrResponse === null) {
            $this->_psrResponse = new ServerResponse();
        }
        return $this->_psrResponse;
    }

}