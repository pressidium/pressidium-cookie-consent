<?php
/**
 * API service provider.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Feedback;

use Pressidium\WP\CookieConsent\Dependencies\Psr\Container\ContainerExceptionInterface;
use Pressidium\WP\CookieConsent\Dependencies\Psr\Container\NotFoundExceptionInterface;

use Pressidium\WP\CookieConsent\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Class Service_Provider.
 *
 * @since 1.1.0
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
        'feedback_api',
        'feedback',
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
             ->add( 'feedback_api', Feedback_API::class )
             ->addArgument( $this->getContainer()->get( 'logger' ) );

        $this->getContainer()
             ->add( 'feedback', Feedback::class )
             ->addArgument( $this->getContainer()->get( 'feedback_api' ) );
    }

}
