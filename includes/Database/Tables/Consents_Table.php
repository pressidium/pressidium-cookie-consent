<?php
/**
 * Consents table.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Database\Tables;

use Pressidium\WP\CookieConsent\Admin\Settings\Consent_Record;
use Pressidium\WP\CookieConsent\Database\Blueprint;
use Pressidium\WP\CookieConsent\Database\Table;
use Pressidium\WP\CookieConsent\Database\Raw;

use Exception;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Consents_Table abstract class.
 *
 * @since 1.2.0
 */
final class Consents_Table extends Table {

    /**
     * Return the table name without the prefix.
     *
     * @return string
     */
    public function get_table_name(): string {
        return 'pressidium_cookie_consents';
    }

    /**
     * Build the table schema.
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * Schema:
     *
     * | Column name         | Type         |
     * | ------------------- | ------------ |
     * | id                  | VARCHAR(255) |
     * | consent_date        | DATETIME     |
     * | url                 | VARCHAR(255) |
     * | geo_location        | VARCHAR(255) |
     * | ip_address          | VARCHAR(255) |
     * | user_agent          | VARCHAR(255) |
     * | necessary_consent   | TINYINT(1)   |
     * | analytics_consent   | TINYINT(1)   |
     * | targeting_consent   | TINYINT(1)   |
     * | preferences_consent | TINYINT(1)   |
     * | created_at          | TIMESTAMP    |
     * | updated_at          | TIMESTAMP    |
     *
     * @param Blueprint $table Table's blueprint.
     *
     * @return void
     */
    protected function get_table_schema( Blueprint $table ): void {
        $zero_int = new Raw( 0 );

        $table->string( 'id', 40 )
              ->not_nullable()
              ->primary();

        $table->datetime( 'consent_date' )
              ->not_nullable();

        $table->string( 'url', 255 )
              ->default_to( '' )
              ->not_nullable();

        $table->string( 'geo_location', 8 )
              ->nullable();

        $table->string( 'ip_address', 40 )
              ->not_nullable();

        $table->string( 'user_agent', 255 )
              ->not_nullable();

        $table->boolean( 'necessary_consent' )
              ->not_nullable()
              ->default_to( $zero_int );

        $table->boolean( 'analytics_consent' )
              ->not_nullable()
              ->default_to( $zero_int );

        $table->boolean( 'targeting_consent' )
              ->not_nullable()
              ->default_to( $zero_int );

        $table->boolean( 'preferences_consent' )
              ->not_nullable()
              ->default_to( $zero_int );

        $table->timestamps();
    }

    /**
     * Whether a consent record exists for the given ID.
     *
     * @param string $consent_id Consent ID.
     *
     * @return bool
     */
    public function has_consent_record( string $consent_id ): bool {
        global $wpdb;

        $table_name = $this->get_prefix() . $this->get_table_name();

        return (bool) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_name} WHERE id = %s",
                $consent_id
            )
        );
    }

    /**
     * Insert the given consent record into the database.
     *
     * @throws Exception If the table name is empty.
     *
     * @param Consent_Record $consent_record Consent record to insert.
     *
     * @return bool Whether the record was inserted successfully.
     */
    public function insert_consent_record( Consent_Record $consent_record ): bool {
        $record_data = array(
            'id'                  => $consent_record->get_id(),
            'consent_date'        => $consent_record->get_date(),
            'url'                 => $consent_record->get_url(),
            'geo_location'        => $consent_record->get_geo_location(),
            'ip_address'          => $consent_record->get_ip_address(),
            'user_agent'          => $consent_record->get_user_agent(),
            'necessary_consent'   => $consent_record->has_necessary_consent(),
            'analytics_consent'   => $consent_record->has_analytics_consent(),
            'targeting_consent'   => $consent_record->has_targeting_consent(),
            'preferences_consent' => $consent_record->has_preferences_consent(),
        );

        $record_data_types = array(
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%d',
            '%d',
            '%d',
            '%d',
        );

        return (bool) $this->insert( $record_data, $record_data_types );
    }

    /**
     * Update the given consent record in the database.
     *
     * @throws Exception If the table name is empty.
     *
     * @param Consent_Record $consent_record Consent record to update.
     *
     * @return bool Whether the record was updated successfully.
     */
    public function update_consent_record( Consent_Record $consent_record ): bool {
        $record_data = array(
            'consent_date'        => $consent_record->get_date(),
            'url'                 => $consent_record->get_url(),
            'geo_location'        => $consent_record->get_geo_location(),
            'ip_address'          => $consent_record->get_ip_address(),
            'user_agent'          => $consent_record->get_user_agent(),
            'necessary_consent'   => $consent_record->has_necessary_consent(),
            'analytics_consent'   => $consent_record->has_analytics_consent(),
            'targeting_consent'   => $consent_record->has_targeting_consent(),
            'preferences_consent' => $consent_record->has_preferences_consent(),
        );

        $record_data_types = array(
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%d',
            '%d',
            '%d',
            '%d',
        );

        $where = array( 'id' => $consent_record->get_id() );

        return (bool) $this->update( $record_data, $where, $record_data_types );
    }

    /**
     * Either insert the given consent record if it doesn't exist, or update it if it does.
     *
     * @throws Exception If the table name is empty.
     *
     * @param Consent_Record $consent_record Consent record to insert or update.
     *
     * @return bool Whether the record was inserted or updated successfully.
     */
    public function set_consent_record( Consent_Record $consent_record ): bool {
        if ( ! $this->has_consent_record( $consent_record->get_id() ) ) {
            return $this->insert_consent_record( $consent_record );
        }

        return $this->update_consent_record( $consent_record );
    }

    /**
     * Return the schema version of the table.
     *
     * Bump this up if the table schema changes in a future release.
     *
     * @return string
     */
    public function get_version(): string {
        return '1.2';
    }

}
