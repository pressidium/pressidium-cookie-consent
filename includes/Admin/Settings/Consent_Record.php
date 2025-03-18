<?php
/**
 * Consent record.
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2023 Pressidium
 */

namespace Pressidium\WP\CookieConsent\Admin\Settings;

use DateTime;

use Pressidium\WP\CookieConsent\Utils\String_Utils;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Forbidden' );
}

/**
 * Consent_Record class.
 *
 * @since 1.2.0
 */
class Consent_Record {

    /**
     * @var string Timestamp of consent.
     */
    private string $date;

    /**
     * @var string Unique ID of the consent.
     */
    private string $id;

    /**
     * @var string URL of the page where the consent was given.
     */
    private string $url;

    /**
     * @var string|null Geographic location based on IP address.
     */
    private ?string $geo_location;

    /**
     * @var string Anonymized IP address.
     */
    private string $ip_address;

    /**
     * @var string User agent.
     */
    private string $user_agent;

    /**
     * @var bool Whether the consent was given for the necessary cookie category.
     */
    private bool $necessary_consent;

    /**
     * @var bool Whether the consent was given for the analytics cookie category.
     */
    private bool $analytics_consent;

    /**
     * @var bool Whether the consent was given for the targeting cookie category.
     */
    private bool $targeting_consent;

    /**
     * @var bool Whether the consent was given for the preferences cookie category.
     */
    private bool $preferences_consent;

    /**
     * Consent_Record constructor.
     */
    public function __construct() {

    }

    /**
     * Return the timestamp of the consent.
     *
     * @return string
     */
    public function get_date(): string {
        return $this->date;
    }

    /**
     * Set the timestamp of the consent.
     *
     * @param DateTime|string $date
     *
     * @return Consent_Record
     */
    public function set_date( $date ): Consent_Record {
        $this->date = $date instanceof DateTime
            ? $date->format( 'Y-m-d\TH:i:s.u\Z' )
            : $date;

        return $this; // chainable
    }

    /**
     * Return the unique ID of the consent.
     *
     * @return string
     */
    public function get_id(): string {
        return $this->id;
    }

    /**
     * Set the unique ID of the consent.
     *
     * @param string $id
     *
     * @return Consent_Record
     */
    public function set_id( string $id ): Consent_Record {
        $this->id = String_Utils::truncate( $id, 40 );

        return $this; // chainable
    }

    /**
     * Return the URL of the page where the consent was given.
     *
     * @return string
     */
    public function get_url(): string {
        return $this->url;
    }

    /**
     * Set the URL of the page where the consent was given.
     *
     * @param string $url
     *
     * @return Consent_Record
     */
    public function set_url( string $url ): Consent_Record {
        $this->url = String_Utils::truncate( $url, 255 );

        return $this; // chainable
    }

    /**
     * Return the geographic location based on the IP address.
     *
     * @return string
     */
    public function get_geo_location(): ?string {
        return $this->geo_location;
    }

    /**
     * Set the geographic location based on the IP address.
     *
     * @param string|null $geo_location
     *
     * @return Consent_Record
     */
    public function set_geo_location( ?string $geo_location ): Consent_Record {
        $this->geo_location = String_Utils::truncate( $geo_location, 8 );

        return $this; // chainable
    }

    /**
     * Return the anonymized IP address.
     *
     * @return string
     */
    public function get_ip_address(): string {
        return $this->ip_address;
    }

    /**
     * Anonymize and set the IP address.
     *
     * @param string $ip_address
     *
     * @return Consent_Record
     */
    public function set_ip_address( string $ip_address ): Consent_Record {
        $this->ip_address = String_Utils::truncate( wp_privacy_anonymize_ip( $ip_address ), 40 );

        return $this; // chainable
    }

    /**
     * Return the User Agent.
     *
     * @return string
     */
    public function get_user_agent(): string {
        return $this->user_agent;
    }

    /**
     * Set the User Agent.
     *
     * @param string $user_agent
     *
     * @return Consent_Record
     */
    public function set_user_agent( string $user_agent ): Consent_Record {
        $this->user_agent = String_Utils::truncate( $user_agent, 255 );

        return $this; // chainable
    }

    /**
     * Return whether the consent was given for the necessary cookie category.
     *
     * @return bool
     */
    public function has_necessary_consent(): bool {
        return $this->necessary_consent;
    }

    /**
     * Set whether the consent was given for the necessary cookie category.
     *
     * @param bool $necessary_consent
     *
     * @return Consent_Record
     */
    public function set_necessary_consent( bool $necessary_consent ): Consent_Record {
        $this->necessary_consent = $necessary_consent;

        return $this; // chainable
    }

    /**
     * Return whether the consent was given for the analytics cookie category.
     *
     * @return bool
     */
    public function has_analytics_consent(): bool {
        return $this->analytics_consent;
    }

    /**
     * Set whether the consent was given for the analytics cookie category.
     *
     * @param bool $analytics_consent
     *
     * @return Consent_Record
     */
    public function set_analytics_consent( bool $analytics_consent ): Consent_Record {
        $this->analytics_consent = $analytics_consent;

        return $this; // chainable
    }

    /**
     * Return whether the consent was given for the targeting cookie category.
     *
     * @return bool
     */
    public function has_targeting_consent(): bool {
        return $this->targeting_consent;
    }

    /**
     * Set whether the consent was given for the targeting cookie category.
     *
     * @param bool $targeting_consent
     *
     * @return Consent_Record
     */
    public function set_targeting_consent( bool $targeting_consent ): Consent_Record {
        $this->targeting_consent = $targeting_consent;

        return $this; // chainable
    }

    /**
     * Return whether the consent was given for the preferences cookie category.
     *
     * @return bool
     */
    public function has_preferences_consent(): bool {
        return $this->preferences_consent;
    }

    /**
     * Set whether the consent was given for the preferences cookie category.
     *
     * @param bool $preferences_consent
     *
     * @return Consent_Record
     */
    public function set_preferences_consent( bool $preferences_consent ): Consent_Record {
        $this->preferences_consent = $preferences_consent;

        return $this; // chainable
    }

}
