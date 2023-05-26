<?php
/**
 * Hooks Manager.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Hooks;

// Prevent direct access to files
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Hooks_Manager {

    /**
     * Register an object.
     *
     * @param object $object
     *
     * @return void
     */
    public function register( $object ): void {
        if ( $object instanceof Actions ) {
            $this->register_actions( $object );
        }

        if ( $object instanceof Filters ) {
            $this->register_filters( $object );
        }
    }

    /**
     * Register the actions of the given object.
     *
     * @param object $object
     *
     * @return void
     */
    private function register_actions( $object ): void {
        $actions = $object->get_actions();

        foreach ( $actions as $action_name => $action_details ) {
            $method        = $action_details[0];
            $priority      = $action_details[1] ?? 10;
            $accepted_args = $action_details[2] ?? 1;

            add_action(
                $action_name,
                array( $object, $method ),
                $priority,
                $accepted_args
            );
        }
    }

    /**
     * Register the filters of the given object.
     *
     * @param object $object
     *
     * @return void
     */
    private function register_filters( $object ): void {
        $filters = $object->get_filters();

        foreach ( $filters as $filter_name => $filter_details ) {
            $method        = $filter_details[0];
            $priority      = $filter_details[1] ?? 10;
            $accepted_args = $filter_details[2] ?? 1;

            add_filter(
                $filter_name,
                array( $object, $method ),
                $priority,
                $accepted_args
            );
        }
    }

}
