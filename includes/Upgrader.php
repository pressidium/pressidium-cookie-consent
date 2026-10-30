<?php
/**
 * Upgrader.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2024 Pressidium
 */

namespace Pressidium\WP\CookieConsent;

use const Pressidium\WP\CookieConsent\VERSION;

use Pressidium\WP\CookieConsent\Dependencies\Psr\SimpleCache\CacheInterface;
use Pressidium\WP\CookieConsent\Logging\Logger;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Upgrader class.
 *
 * @since 1.5.2
 */
class Upgrader {

    /**
     * @var string Key of the transient holding the version a failed upgrade was targeting.
     */
    const FAILED_UPGRADE_KEY = 'pressidium_cookie_consent_failed_upgrade';

    /**
     * @var int Number of seconds to wait before retrying a failed upgrade.
     */
    const RETRY_INTERVAL = 15 * MINUTE_IN_SECONDS;

    /**
     * @var Logger Logger object.
     */
    private Logger $logger;

    /**
     * @var Settings Settings object.
     */
    private Settings $settings;

    /**
     * @var CacheInterface Cache instance.
     */
    private CacheInterface $cache;

    /**
     * Upgrader constructor.
     *
     * @param Logger         $logger   Logger object.
     * @param Settings       $settings Settings object.
     * @param CacheInterface $cache    Cache instance.
     */
    public function __construct( Logger $logger, Settings $settings, CacheInterface $cache ) {
        $this->logger   = $logger;
        $this->settings = $settings;
        $this->cache    = $cache;
    }

    /**
     * Whether the plugin was just upgraded.
     *
     * @param array $settings Settings to check against.
     *
     * @return bool
     */
    public function did_just_upgrade( array $settings ): bool {
        if ( ! isset( $settings['version'] ) ) {
            return false;
        }

        if ( ! defined( 'Pressidium\WP\CookieConsent\VERSION' ) ) {
            return false;
        }

        return version_compare( $settings['version'], VERSION, '<' );
    }

    /**
     * Migrate the given settings to the latest version.
     *
     * @param array $settings Settings to migrate.
     *
     * @return array
     */
    private function maybe_migrate( array $settings ): array {
        $migrator          = new Migrator( $settings );
        $migrated_settings = $migrator->maybe_migrate();

        return $migrated_settings;
    }

    /**
     * Maybe upgrade.
     *
     * @return void
     */
    public function maybe_upgrade(): void {
        if ( ! is_admin() ) {
            // We only run the upgrader in the admin area, bail early
            return;
        }

        if ( $this->cache->get( self::FAILED_UPGRADE_KEY ) === VERSION ) {
            // Upgrade to this version failed recently, wait before retrying
            return;
        }

        $settings = Emoji::decode_array( $this->settings->get() );

        if ( ! $this->did_just_upgrade( $settings ) ) {
            // Plugin was not just upgraded, bail early
            return;
        }

        $old_version = $settings['version'];

        $settings            = $this->maybe_migrate( $settings );
        $settings['version'] = VERSION;

        $set_successfully = $this->settings->set( $settings );

        if ( ! $set_successfully ) {
            $this->cache->set( self::FAILED_UPGRADE_KEY, VERSION, self::RETRY_INTERVAL );

            $this->logger->error(
                sprintf(
                    'Could not save settings after upgrade from version %s to %s. Will retry in %d minutes.',
                    $old_version,
                    VERSION,
                    self::RETRY_INTERVAL / MINUTE_IN_SECONDS
                )
            );
            return;
        }

        $this->logger->info(
            sprintf(
                'Successfully upgraded from version %s to %s.',
                $old_version,
                VERSION
            )
        );
    }

}
