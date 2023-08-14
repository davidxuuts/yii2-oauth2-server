<?php
/**
 * A PSR-7 compatible version of a response. Required for the php league oauth2 library.
 *
 */

namespace davidxu\oauth2\components\web;

use GuzzleHttp\Psr7\Response;
use Yii;
use yii\web\HeaderCollection;

class ServerResponse extends Response
{

    /**
     * Send this request as a standard Yii2 response
     */
    public function send(): void
    {
        /** @var HeaderCollection $headers */
        $headers = Yii::$app->response->headers;
        $headers->removeAll();
        foreach ($this->getHeaders() as $_header => $lines) {
            $headers->add($_header, $this->getHeaderLine($_header));
        }
        Yii::$app->response->version = $this->getProtocolVersion();
        Yii::$app->response->statusCode = $this->getStatusCode();
        Yii::$app->response->statusText = $this->getReasonPhrase();
        Yii::$app->response->content = $this->getBody();

        Yii::$app->response->send();
    }
}
