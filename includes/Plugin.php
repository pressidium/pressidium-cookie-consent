<?php
/**
 * Plugin.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent;

use League\Container\Container;

use Pressidium\WP\CookieConsent\Admin\Settings\Service_Provider as Settings_Service_Provider;
use Pressidium\WP\CookieConsent\Client\Service_Provider as Client_Service_Provider;

use Pressidium\WP\CookieConsent\Hooks\Hooks_Manager;
use Pressidium\WP\CookieConsent\Options\WP_Options;
use Pressidium\WP\CookieConsent\Logging\File_Logger;
use Pressidium\WP\CookieConsent\Logging\Logger;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Plugin class.
 *
 * @since 1.0.0
 */
class Plugin {

    /**
     * @var Logger Logger instance.
     */
    private Logger $logger;

    /**
     * Load plugin text domain.
     *
     * @return void
     */
    public function load_textdomain(): void {
        load_plugin_textdomain(
            'pressidium-cookie-consent',
            false, // this parameter is deprecated
            dirname( plugin_basename( __FILE__ ) ) . '/languages'
        );
    }

    /**
     * Add WordPress hooks.
     *
     * @return void
     */
    private function add_hooks(): void {
        add_action( 'init', array( $this, 'load_textdomain' ) );
    }

    /**
     * Add service providers to the container.
     *
     * @param Container $container Dependency injection container.
     *
     * @return void
     */
    private function add_service_providers( Container $container ): void {
        try {
            $container->addServiceProvider( Settings_Service_Provider::class );
            $container->addServiceProvider( Client_Service_Provider::class );
        } catch ( ContainerExceptionInterface | NotFoundExceptionInterface $exception ) {
            $this->logger->log_exception( $exception );
        }
    }

    /**
     * Register hooks with the `Hooks_Manager`.
     *
     * @param Hooks_Manager $hooks_manager Hooks manager.
     * @param Container     $container     Dependency injection container.
     *
     * @return void
     */
    private function register_hooks( Hooks_Manager $hooks_manager, Container $container ): void {
        try {
            $hooks_manager->register( $container->get( 'settings_api' ) );
            $hooks_manager->register( $container->get( 'settings_page' ) );
            $hooks_manager->register( $container->get( 'cookie_consent' ) );
        } catch ( ContainerExceptionInterface | NotFoundExceptionInterface $exception ) {
            $this->logger->log_exception( $exception );
        }
    }

    /**
     * Initialize the plugin.
     *
     * @return void
     */
    public function init(): void {
        define( __NAMESPACE__ . '\NS', __NAMESPACE__ . '\\' );

        $this->add_hooks();

        $container = new Container();

        $hooks_manager = new Hooks_Manager();
        $container->add( 'hooks_manager', $hooks_manager );

        $this->logger = new File_Logger();
        $container->add( 'logger', $this->logger );

        $options = new WP_Options();
        $container->add( 'options', $options );

        $settings = new Settings( $options );
        $container->add( 'settings', $settings );

        $this->add_service_providers( $container );
        $this->register_hooks( $hooks_manager, $container );
    }

}
