<?php
namespace davidxu\oauth2\components\repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use davidxu\oauth2\models\Client;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

class ClientRepository implements ClientRepositoryInterface
{

    /**
     * Validate a client's secret.
     *
     * @param string $clientIdentifier The client's identifier
     * @param null|string $clientSecret The client's secret (if sent)
     * @param null|string $grantType The type of grant the client is using (if sent)
     *
     * @return bool
     */
    public function validateClient($clientIdentifier, $clientSecret, $grantType): bool
    {
        /** @var Client $client */
        $client = Client::find()->where(['status'=>Client::STATUS_ACTIVE, 'identifier'=>$clientIdentifier])->one();

        if ($client instanceof Client) {
            $isValidUser = $client->validateSecret($clientSecret);
            /* @TODO do something with $grantType ?? */
            return $isValidUser;
        }

        return false;
    }

    /**
     * Get a client.
     *
     * @param string $clientIdentifier The client's identifier
     *
     * @return Client|ClientEntityInterface|null
     */
    public function getClientEntity($clientIdentifier): Client|ClientEntityInterface|null
    {
        /** @var Client $client */
        $client = Client::find()
            ->where(['status' => Client::STATUS_ACTIVE, 'identifier' => $clientIdentifier])
            ->one();
        return $client;
    }
}
