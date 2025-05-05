<?php
/**
 * Cookies shortcode.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2025 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Shortcodes\Cookies_Shortcode;

use Pressidium\WP\CookieConsent\Shortcodes\Shortcode as Abstract_Shortcode;
use Pressidium\WP\CookieConsent\HTML\Table;
use Pressidium\WP\CookieConsent\HTML\Row;
use Pressidium\WP\CookieConsent\HTML\Cell;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Shortcode class.
 *
 * @since 1.8.0
 */
class Shortcode extends Abstract_Shortcode {

    /**
     * Return the shortcode tag (without square brackets).
     *
     * @return string
     */
    public function get_tag(): string {
        return 'pressidium_cookie_consent_cookies';
    }

    /**
     * Return the default values for the shortcode attributes.
     *
     * Overridden method from the parent class to provide
     * the default values for the shortcode attributes.
     *
     * @return array<string, mixed>
     */
    public function get_default_attributes(): array {
        return array(
            'category'     => '',
            'show_columns' => 'name,domain,expiration,path,description',
        );
    }

    /**
     * Return a mapping between column names and their i18n headers.
     *
     * @return array<string, string>
     */
    private function get_i18n_headers(): array {
        return array(
            'name'        => __( 'Name', 'pressidium-cookie-consent' ),
            'domain'      => __( 'Domain', 'pressidium-cookie-consent' ),
            'expiration'  => __( 'Expiration', 'pressidium-cookie-consent' ),
            'path'        => __( 'Path', 'pressidium-cookie-consent' ),
            'description' => __( 'Description', 'pressidium-cookie-consent' ),
        );
    }

    /**
     * Return the headers of the columns to show.
     *
     * @param string[] $columns_to_show An array of column names for the columns to show.
     *
     * @return string[]
     */
    private function get_column_headers( array $columns_to_show ): array {
        return array_values(
            array_intersect_key(
                $this->get_i18n_headers(),
                array_flip( $columns_to_show )
            )
        );
    }

    /**
     * Parse the shortcode attributes and return a `Shortcode_Attributes` object.
     *
     * @param array<string, mixed> $atts Shortcode attributes.
     *
     * @return Shortcode_Attributes
     */
    private function parse_attributes( array $atts = array() ): Shortcode_Attributes {
        $attributes = new Shortcode_Attributes();
        $attributes
            ->set_category( $atts['category'] )
            ->set_show_columns( $atts['show_columns'] );

        return $attributes;
    }

    /**
     * Return a `Table` object for the given cookies, columns to show and cookie category.
     *
     * @param array<string, array<string, string>> $cookies         An array of cookies for this category.
     * @param string[]                             $columns_to_show An array of column names for the columns to show.
     * @param string                               $cookie_category Cookie category.
     *
     * @return Table
     */
    private function get_table( array $cookies, array $columns_to_show, string $cookie_category ): Table {
        $table = new Table();
        $table->set_headers( $this->get_column_headers( $columns_to_show ) )
              ->add_attribute( 'data-cookie-category', $cookie_category );

        foreach ( $cookies as $cookie ) {
            $row = new Row();

            foreach ( $columns_to_show as $column_to_show ) {
                if ( isset( $cookie[ $column_to_show ] ) ) {
                    $cell = new Cell();
                    $cell->set_content( wpautop( $cookie[ $column_to_show ] ) )
                         ->add_attribute( 'data-column', $column_to_show );

                    $row->add_cell( $cell );
                }
            }

            $table->add_row( $row );
        }

        return $table;
    }

    /**
     * Return the shortcode output.
     *
     * This method should return the output of the shortcode.
     * It should *never* produce an output of any kind.
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     *
     * @see https://developer.wordpress.org/reference/functions/add_shortcode/
     *
     * @param array<string, mixed> $atts    Shortcode attributes (with default values).
     * @param string               $content Shortcode content.
     *
     * @return string
     */
    public function get_output( array $atts = array(), string $content = '' ): string {
        $attributes = $this->parse_attributes( $atts );

        $all_cookies = pressidium_cookie_consent_get_cookies();
        $cookies     = $all_cookies[ $attributes->get_category() ] ?? array();

        if ( empty( $cookies ) ) {
            return sprintf(
                /* translators: Cookie category name. */
                __( 'No cookies listed under the "%s" category.', 'pressidium-cookie-consent' ),
                esc_html( $attributes->get_category() )
            );
        }

        $columns_to_show = $attributes->get_columns_to_show();

        $table = $this->get_table( $cookies, $columns_to_show, $attributes->get_category() );

        return sprintf(
            '<div class="pressidium-cookie-consent-cookies">%s</div>',
            wp_kses_post( $table->get_html() )
        );
    }

}
