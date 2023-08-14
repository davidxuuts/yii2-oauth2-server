<?php
namespace davidxu\oauth2\components\repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use davidxu\oauth2\models\Client;
use davidxu\oauth2\models\Scope;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

class ScopeRepository implements ScopeRepositoryInterface {


    /**
     * Return information about a scope.
     *
     * @param string|int|null $identifier The scope identifier
     *
     * @return array|ActiveRecord|null
     */
    public function getScopeEntityByIdentifier($identifier): array|ActiveRecord|null
    {
        return Scope::find()->where(['identifier' => $identifier])->one();
    }

    /**
     * Given a client, grant type and optional user identifier validate the set of scopes requested are valid and optionally
     * append additional scopes or remove requested scopes.
     *
     * @param ScopeEntityInterface[] $scopes
     * @param string $grantType
     * @param Client $clientEntity
     * @param null|string $userIdentifier
     *
     * @return ScopeEntityInterface[]
     * @throws InvalidConfigException
     */
    public function finalizeScopes(array $scopes, $grantType, ClientEntityInterface $clientEntity, $userIdentifier = null): array
    {
        $allowedScopes = $clientEntity->getScopes(
            function (ActiveQuery $query) use ($scopes, $grantType, $userIdentifier) {
                if (empty($scopes)) {
                    $query->andWhere(['is_default' => true]);
                }
                // common and assigned to user
                $query->andWhere(['or', ['user_id' => null], ['user_id' => $userIdentifier]]);
//                // common and grant-specific
                $query->andWhere([
                    'or',
                    ['grant_type' => null],
                    ['grant_type' => Client::getGrantTypeId($grantType)]
                ]);
            }
        );

        if (!empty($scopes)) {
            $allowedScopes->andWhere(['in', 'identifier', $scopes]);
        }

        return $allowedScopes->all();
    }
}
