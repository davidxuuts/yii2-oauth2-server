<?php
/**
 *
 * A PSR-7 compatible version of a request. Required for the phpleague oauth2 library.
 *
 * Based on original by chervand (Source: https://github.com/chervand/yii2-oauth2-server)
 */

namespace davidxu\oauth2\components\web;

use yii\web\Request;

class ServerRequest extends \GuzzleHttp\Psr7\ServerRequest
{
    /**
     * ServerRequest constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        parent::__construct(
            $request->method,
            $request->url,
            $request->headers->toArray(),
            $request->rawBody
        );
    }

}