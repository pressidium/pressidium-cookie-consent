<?php
/**
 * Abstract class for admin options pages.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Admin;

use Pressidium\WP\CookieConsent\Hooks\Actions;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Abstract class for admin options pages.
 *
 * @since 1.0.0
 */
abstract class Page implements Actions {

    /**
     * Return the menu slug.
     *
     * @return string
     */
    abstract protected function get_menu_slug(): string;

    /**
     * Return the option group.
     *
     * @return string
     */
    abstract protected function get_option_group(): string;

    /**
     * Return the option name.
     *
     * @return string
     */
    abstract protected function get_option_name(): string;

    /**
     * Return the page title.
     *
     * @return string
     */
    abstract protected function get_page_title(): string;

    /**
     * Return the menu title.
     *
     * @return string
     */
    abstract protected function get_menu_title(): string;

    /**
     * Return the capability required for this menu to be displayed to the user.
     *
     * Override this method if you want to change the required capability.
     *
     * @return string
     */
    protected function get_capability(): string {
        return 'manage_options';
    }

    /**
     * Return the description of this options page.
     *
     * @return string
     */
    protected function get_description(): string {
        return '';
    }

    /**
     * Return the URL to the icon to be used for this menu.
     *
     * @return string
     */
    protected function get_icon(): string {
        return 'dashicons-admin-generic';
    }

    /**
     * Return the position in the menu order this item should appear.
     *
     * @return int|null
     */
    protected function get_position() {
        return null;
    }

    /**
     * Render the settings page.
     *
     * @return void
     */
    public function render(): void {
        ?>

        <div class="wrap">
            <form  method="post">
                <h1>
                    <?php echo esc_html( $this->get_page_title() ); ?>
                </h1>

                <?php if ( ! empty ( $this->get_description() ) ) : ?>

                    <p>
                        <?php
                        echo wp_kses( $this->get_description(), array(
                            'a'      => array(
                                'href'  => array(),
                                'title' => array(),
                                'ref'   => array(),
                            ),
                            'br'     => array(),
                            'em'     => array(),
                            'strong' => array(),
                        ) );
                        ?>
                    </p>

                <?php endif; ?>

                <!-- This is where React will be rendered -->
                <div id="pressidium-cookie-consent-root"></div>
            </form>
        </div>

        <?php
    }

    /**
     * Sanitize options.
     *
     * @param array $options Options to sanitize.
     *
     * @return array
     */
    public function sanitize( array $options ): array {
        foreach ( $options as $key => $option ) {
            $options[ $key ] = sanitize_text_field( $option );
        }

        return $options;
    }

    /**
     * Add the options page for the admin menu.
     *
     * @link https://developer.wordpress.org/reference/hooks/admin_menu/
     *
     * @return void
     */
    public function add_page(): void {
        add_menu_page(
            $this->get_page_title(),
            $this->get_menu_title(),
            $this->get_capability(),
            $this->get_menu_slug(),
            array( $this, 'render' ),
            $this->get_icon(),
            $this->get_position()
        );
    }

    /**
     * Initialize admin options page.
     *
     * @link https://developer.wordpress.org/reference/hooks/admin_init/
     *
     * @return void
     */
    public function init(): void {
        register_setting(
            $this->get_option_group(),
            $this->get_option_name(),
            array(
                'type'              => 'array',
                'sanitize_callback' => array( $this, 'sanitize' ),
            )
        );
    }

    /**
     * Return the actions to register.
     *
     * @return array<string, array{0: string, 1?: int, 2?: int}>
     */
    public function get_actions(): array {
        return array(
            'admin_menu' => array( 'add_page' ),
            'admin_init' => array( 'init' ),
        );
    }

}
