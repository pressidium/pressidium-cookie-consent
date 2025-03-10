<?php
/**
 * Filters interface.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Hooks;

// Prevent direct access to files
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

interface Filters {

    /**
     * Return the filters to register.
     *
     * @return array<string, array{0: string, 1?: int, 2?: int}>
     */
    public function get_filters(): array;

}
