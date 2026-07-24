<?php

namespace App\Support;

use GuzzleHttp\RequestOptions;
use SocialiteProviders\Keycloak\Provider;

class KeycloakSocialiteProvider extends Provider
{
    public static function additionalConfigKeys()
    {
        return array_merge(parent::additionalConfigKeys(), ['internal_base_url']);
    }

    // URL yang dipakai server-side (token exchange & userinfo). Di Docker,
    // container tidak bisa mengakses Keycloak lewat localhost host machine.
    protected function getInternalBaseUrl(): string
    {
        $base = $this->getConfig('internal_base_url') ?: $this->getConfig('base_url');

        return rtrim(rtrim($base, '/').'/realms/'.$this->getConfig('realms', 'master'), '/');
    }

    protected function getTokenUrl()
    {
        return $this->getInternalBaseUrl().'/protocol/openid-connect/token';
    }

    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get($this->getInternalBaseUrl().'/protocol/openid-connect/userinfo', [
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer '.$token,
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }
}
