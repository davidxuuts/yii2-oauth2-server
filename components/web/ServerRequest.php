<?php
/**
 *
 * A PSR-7 compatible version of a request. Required for the php league oauth2 library.
 *
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
