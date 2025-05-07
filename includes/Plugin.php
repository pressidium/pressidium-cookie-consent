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
use Pressidium\WP\CookieConsent\Blocks\Service_Provider as Blocks_Service_Provider;
use Pressidium\WP\CookieConsent\Shortcodes\Service_Provider as Shortcodes_Service_Provider;
use Pressidium\WP\CookieConsent\Integrations\Service_Provider as Integrations_Service_Provider;
use Pressidium\WP\CookieConsent\AI\Service_Provider as AI_Service_Provider;

use Pressidium\WP\CookieConsent\Hooks\Hooks_Manager;
use Pressidium\WP\CookieConsent\Logging\File_Logger;
use Pressidium\WP\CookieConsent\Logging\Logger;
use Pressidium\WP\CookieConsent\Options\WP_Options;
use Pressidium\WP\CookieConsent\Options\Data_Encryption;
use Pressidium\WP\CookieConsent\Options\Encrypted_Options;

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
     * @var Container Dependency injection container.
     */
    private Container $container;

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
     * Add WordPress hooks.
     *
     * @return void
     */
    private function add_hooks(): void {
        add_action( 'init', array( $this, 'register_blocks' ) );
    }

    /**
     * Add service providers to the container.
     *
     * @return void
     */
    private function add_service_providers(): void {
        try {
            $this->container->addServiceProvider( Feedback_Service_Provider::class );
            $this->container->addServiceProvider( Settings_Service_Provider::class );
            $this->container->addServiceProvider( Client_Service_Provider::class );
            $this->container->addServiceProvider( Blocks_Service_Provider::class );
            $this->container->addServiceProvider( Shortcodes_Service_Provider::class );
            $this->container->addServiceProvider( Integrations_Service_Provider::class );
            $this->container->addServiceProvider( AI_Service_Provider::class );
        } catch ( ContainerExceptionInterface | NotFoundExceptionInterface $exception ) {
            $this->logger->log_exception( $exception );
        }
    }

    /**
     * Register hooks with the `Hooks_Manager`.
     *
     * @param Hooks_Manager $hooks_manager Hooks manager.
     *
     * @return void
     */
    private function register_hooks( Hooks_Manager $hooks_manager ): void {
        try {
            $hooks_manager->register( $this->container->get( 'settings_api' ) );
            $hooks_manager->register( $this->container->get( 'ai_api' ) );
            $hooks_manager->register( $this->container->get( 'settings_page' ) );
            $hooks_manager->register( $this->container->get( 'cookie_consent' ) );
            $hooks_manager->register( $this->container->get( 'consent_mode' ) );
            $hooks_manager->register( $this->container->get( 'feedback' ) );
            $hooks_manager->register( $this->container->get( 'cookies_block' ) );
            $hooks_manager->register( $this->container->get( 'wp_consent_api' ) );
        } catch ( ContainerExceptionInterface | NotFoundExceptionInterface $exception ) {
            $this->logger->log_exception( $exception );
        }
    }

    /**
     * Register shortcodes with the `Shortcodes_Manager`.
     *
     * @return void
     */
    private function register_shortcodes(): void {
        try {
            $this->container->get( 'shortcodes_manager' )->register( $this->container->get( 'cookies_shortcode' ) );
        } catch ( ContainerExceptionInterface | NotFoundExceptionInterface $exception ) {
            $this->logger->log_exception( $exception );
        }
    }

    /**
     * Register blocks with the `Blocks_Manager`.
     *
     * @return void
     */
    public function register_blocks(): void {
        try {
            $this->container->get( 'blocks_manager' )->register( $this->container->get( 'cookies_block' ) );
        } catch ( ContainerExceptionInterface | NotFoundExceptionInterface $exception ) {
            $this->logger->log_exception( $exception );
        }
    }

    /**
     * Register database tables with the `Database_Manager`.
     *
     * @param Database_Manager $database_manager Database manager.
     *
     * @return void
     */
    private function register_tables( Database_Manager $database_manager ): void {
        $database_manager->register_table( $this->container->get( 'consents_table' ) );

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

        $this->container = new Container();

        $hooks_manager = new Hooks_Manager();
        $this->container->add( 'hooks_manager', $hooks_manager );

        $this->logger = new File_Logger();
        $this->container->add( 'logger', $this->logger );

        $logs = new Logs( $this->logger );
        $this->container->add( 'logs', $logs );

        $geo_locator = new Geo_Locator();
        $this->container->add( 'geo_locator', $geo_locator );

        $options = new WP_Options();
        $this->container->add( 'options', $options );

        $encrypted_options = new Encrypted_Options( new Data_Encryption(), $options );
        $this->container->add( 'encrypted_options', $encrypted_options );

        $database_manager = new Database_Manager( $options, $this->logger );
        $this->container->add( 'database_manager', $database_manager );

        $consents_table = new Consents_Table();
        $this->container->add( 'consents_table', $consents_table );

        $exporter = new CSV_Exporter( $this->logger );
        $this->container->add( 'db_table_exporter', $exporter );

        $settings = new Settings( $options );
        $this->container->add( 'settings', $settings );

        $upgrader = new Upgrader( $this->logger, $settings );
        $this->container->add( 'upgrader', $upgrader );

        $this->add_service_providers();
        $this->register_tables( $database_manager );
        $this->register_hooks( $hooks_manager );
        $this->register_shortcodes();

        add_filter(
            'pressidium_cookie_consent_container',
            function () {
                return $this->container;
            }
        );

        /*
         * Run the upgrader on every request to check if the plugin was upgraded
         * and if so, migrate the settings to the latest version and resave them.
         */
        $upgrader->maybe_upgrade();
    }

}
