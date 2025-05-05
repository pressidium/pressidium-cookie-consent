<?php
/**
 * AI service provider.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2025 Pressidium
 */

namespace Pressidium\WP\CookieConsent\AI;

use Pressidium\WP\CookieConsent\Dependencies\Psr\Container\ContainerExceptionInterface;
use Pressidium\WP\CookieConsent\Dependencies\Psr\Container\NotFoundExceptionInterface;

use Pressidium\WP\CookieConsent\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service_Provider class.
 *
 * @since 1.8.0
 */
final class Service_Provider extends AbstractServiceProvider {

    /**
     * The provided array is a way to let the container
     * know that a service is provided by this service
     * provider. Every service that is registered via
     * this service provider must have an alias added
     * to this array, or it will be ignored.
     *
     * @var array
     */
    protected $provides = array(
        'ai_api',
    );

    /**
     * Access the container and register or retrieve anything that you need to.
     *
     * Remember, every alias registered within this method
     * must be declared in the `$provides` array.
     *
     * @throws NotFoundExceptionInterface  No entry was found in the container.
     * @throws ContainerExceptionInterface Something went wrong with the container.
     *
     * @return void
     */
    public function register(): void {
        $this->getContainer()
             ->add( 'ai_api', AI_API::class )
             ->addArgument( $this->getContainer()->get( 'logger' ) )
             ->addArgument( $this->getContainer()->get( 'encrypted_options' ) )
             ->addArgument( $this->getContainer()->get( 'settings' ) );
    }

}
