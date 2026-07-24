<?php

namespace App\Providers;

use App\Support\KeycloakSocialiteProvider;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Manager\Config as SocialiteConfig;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Socialite::extend('keycloak', function ($app) {
            $config = $app['config']['services.keycloak'];

            $provider = Socialite::buildProvider(KeycloakSocialiteProvider::class, $config);
            $provider->setConfig(new SocialiteConfig(
                $config['client_id'],
                $config['client_secret'],
                $config['redirect'],
                [
                    'base_url'          => $config['base_url'],
                    'internal_base_url' => $config['internal_base_url'] ?? null,
                    'realms'            => $config['realms'],
                ]
            ));

            return $provider;
        });
    }
}
