<?php
/**
 * Blocks service provider.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2025 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Blocks;

use Pressidium\WP\CookieConsent\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use Pressidium\WP\CookieConsent\Dependencies\Psr\Container\ContainerExceptionInterface;
use Pressidium\WP\CookieConsent\Dependencies\Psr\Container\NotFoundExceptionInterface;

use Pressidium\WP\CookieConsent\Blocks\Cookies_Block\Cookies_Block;

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
        'blocks_manager',
        'cookies_block',
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
             ->add( 'blocks_manager', Blocks_Manager::class )
             ->addArgument( $this->getContainer()->get( 'logger' ) );

        $this->getContainer()
             ->add( 'cookies_block', Cookies_Block::class );
    }

}
