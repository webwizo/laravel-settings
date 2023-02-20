<?php

namespace QCod\Settings;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // Load migration
        $this->loadMigrationsFrom(__DIR__.'/migrations');

        // Publish migration
        $this->publishes([
            __DIR__.'/migrations/' => database_path('/migrations/'),
        ], 'migrations');

        /**
         * Extending collection
         * 
         * @link https://laravel.com/docs/9.x/collections#creating-collections
         */
        Collection::macro(
            'dynamic',
            function ($fresh = false) {
                return $this->map(function ($value) use ($fresh) {

                    preg_match_all('/\{[\w]+\}/', $value, $output);

                    if (!$output[0]) {
                        return $value;
                    }

                    $replacement = [];
                    foreach ($output[0] as $out) {
                        $dynamic_key = str_replace(["{", "}"], "", $out);

                        $replacement[] = settings()->get(
                                key: $dynamic_key,
                                fresh: $fresh
                            );
                    }

                    return str_replace($output[0], $replacement, $value);
                });
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        // bind setting storage
        $this->app->bind(
            'QCod\Settings\Setting\SettingStorage',
            'QCod\Settings\Setting\SettingEloquentStorage'
        );
    }
}
