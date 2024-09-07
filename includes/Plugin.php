<?php
/**
 * Plugin.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent;

use Pressidium\WP\CookieConsent\Dependencies\League\Container\Container;

use Pressidium\WP\CookieConsent\Database\CSV_Exporter;
use Pressidium\WP\CookieConsent\Dependencies\Psr\Container\ContainerExceptionInterface;
use Pressidium\WP\CookieConsent\Dependencies\Psr\Container\NotFoundExceptionInterface;

use Pressidium\WP\CookieConsent\Admin\Settings\Service_Provider as Settings_Service_Provider;
use Pressidium\WP\CookieConsent\Client\Service_Provider as Client_Service_Provider;
use Pressidium\WP\CookieConsent\Feedback\Service_Provider as Feedback_Service_Provider;

use Pressidium\WP\CookieConsent\Hooks\Hooks_Manager;
use Pressidium\WP\CookieConsent\Logging\File_Logger;
use Pressidium\WP\CookieConsent\Logging\Logger;
use Pressidium\WP\CookieConsent\Options\WP_Options;

use Pressidium\WP\CookieConsent\Database\Tables\Consents_Table;
use Pressidium\WP\CookieConsent\Database\Database_Manager;

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
     * @var bool Whether the plugin was just activated.
     */
    private bool $just_activated = false;

    /**
     * Mark the plugin as activated.
     *
     * @return void
     */
    public function mark_as_activated(): void {
        $this->just_activated = true;
    }

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
            $container->addServiceProvider( Feedback_Service_Provider::class );
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
            $hooks_manager->register( $container->get( 'consent_mode' ) );
            $hooks_manager->register( $container->get( 'feedback' ) );
        } catch ( ContainerExceptionInterface | NotFoundExceptionInterface $exception ) {
            $this->logger->log_exception( $exception );
        }
    }

    /**
     * Register database tables with the `Database_Manager`.
     *
     * @param Database_Manager $database_manager Database manager.
     * @param Container        $container        Dependency injection container.
     *
     * @return void
     */
    private function register_tables( Database_Manager $database_manager, Container $container ): void {
        $database_manager->register_table( $container->get( 'consents_table' ) );

        if ( $this->just_activated ) {
            $database_manager->create_tables();
            return;
        }

        $database_manager->maybe_upgrade_tables();
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

        $logs = new Logs( $this->logger );
        $container->add( 'logs', $logs );

        $geo_locator = new Geo_Locator();
        $container->add( 'geo_locator', $geo_locator );

        $options = new WP_Options();
        $container->add( 'options', $options );

        $database_manager = new Database_Manager( $options, $this->logger );
        $container->add( 'database_manager', $database_manager );

        $consents_table = new Consents_Table();
        $container->add( 'consents_table', $consents_table );

        $exporter = new CSV_Exporter( $this->logger );
        $container->add( 'db_table_exporter', $exporter );

        $settings = new Settings( $options );
        $container->add( 'settings', $settings );

        $upgrader = new Upgrader( $this->logger, $settings );
        $container->add( 'upgrader', $upgrader );

        $this->add_service_providers( $container );
        $this->register_tables( $database_manager, $container );
        $this->register_hooks( $hooks_manager, $container );

        /*
         * Run the upgrader on every request to check if the plugin was upgraded
         * and if so, migrate the settings to the latest version and resave them.
         */
        $upgrader->maybe_upgrade();
    }

}
