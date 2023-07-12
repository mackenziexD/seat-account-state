<?php

namespace Helious\SeatAccountStatus;

use Seat\Services\AbstractSeatPlugin;

/**
 * Class YourPackageServiceProvider.
 *
 * @package Author\Seat\YourPackage
 */
class AccountStatusServiceProvider extends AbstractSeatPlugin
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/Config/seat-account-status.php', 'seat-account-status');
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->addCommands();

        $this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'seat-account-status');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        
    }

    
    private function addCommands() 
    {
        //
    }

    /**
     * Get the package's routes.
     *
     * @return string
     */
    protected function getRouteFile()
    {
        return __DIR__.'/routes.php';
    }

    

    /**
     * Return the plugin public name as it should be displayed into settings.
     *
     * @return string
     * @example SeAT Web
     *
     */
    public function getName(): string
    {
        return 'SeAT Account Status';
    }

    /**
     * Return the plugin repository address.
     *
     * @example https://github.com/eveseat/web
     *
     * @return string
     */
    public function getPackageRepositoryUrl(): string
    {
        return 'https://github.com/mackenziexD/seat-account-status';
    }

    /**
     * Return the plugin technical name as published on package manager.
     *
     * @return string
     * @example web
     *
     */
    public function getPackagistPackageName(): string
    {
        return 'seat-account-status';
    }

    /**
     * Return the plugin vendor tag as published on package manager.
     *
     * @return string
     * @example eveseat
     *
     */
    public function getPackagistVendorName(): string
    {
        return 'helious';
    }
}
