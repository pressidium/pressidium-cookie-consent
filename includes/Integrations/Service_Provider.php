<?php
/**
 * Integrations service provider.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2025 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Integrations;

use Pressidium\WP\CookieConsent\Dependencies\Psr\Container\ContainerExceptionInterface;
use Pressidium\WP\CookieConsent\Dependencies\Psr\Container\NotFoundExceptionInterface;

use Pressidium\WP\CookieConsent\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Service_Provider class.
 *
 * @since 1.8.0
 */
class Service_Provider extends AbstractServiceProvider {

    /**
     * The provided array is a way to let the container
     * know that a service is provided by this service
     * provider. Every service that is registered via
     * this service provider must have an alias added
     * to this array, or it will be ignored.
     *
     * @var string[]
     */
    protected $provides = array(
        'wp_consent_api',
    );

    /**
     * Access the container and register or retrieve anything that you need to.
     *
     * Remember, every alias registered within this method
     * must be declared in the `$provides` array.
     *
     * @throws NotFoundExceptionInterface  No entry was found in the container.
     * @throws ContainerExceptionInterface Something went wrong with the container.
     */
    public function register(): void {
        $this->getContainer()
             ->add( 'wp_consent_api', WP_Consent_API::class );
    }

}
