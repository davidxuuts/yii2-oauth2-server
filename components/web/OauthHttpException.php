<?php

namespace davidxu\oauth2\components\web;

use League\OAuth2\Server\Exception\OAuthServerException;
use yii\web\HttpException;

/**
 * Class OAuthHttpException constructs {@see HttpException} instance
 * from {@see OAuthServerException}.
 */
class OauthHttpException extends HttpException
{
    /**
     * Constructor.
     * @param OAuthServerException $previous The previous exception used for the exception chaining.
     */
    public function __construct(OAuthServerException $previous)
    {
        $hint = $previous->getHint();

        parent::__construct(
            $previous->getHttpStatusCode(),
            $hint ? $previous->getMessage() . ' ' . $hint . '.' : $previous->getMessage(),
            $previous->getCode(),
            YII_DEBUG === true ? $previous : null
        );
    }
}
