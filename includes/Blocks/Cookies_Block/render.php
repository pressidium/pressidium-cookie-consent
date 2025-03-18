<?php
/**
 * Cookies Block.
 *
 * We use this file to render the Cookies Block at the front end.
 * We have to render the block in PHP because it is a dynamic block.
 * The block is used to display the cookies of a specific category
 * and has to retrieve the listed cookies dynamically.
 *
 * Learn more about dynamic blocks in WordPress:
 *
 * @link https://developer.wordpress.org/block-editor/how-to-guides/block-tutorial/creating-dynamic-blocks/
 *
 * @author Konstantinos Pappas <konpap@pressidium.com>
 * @copyright 2025 Pressidium
 */

// Let phpstan know that the $attributes variable is defined.
/** @var array<string, mixed> $attributes */

use Pressidium\WP\CookieConsent\Utils\Color_Utils;

$all_cookies     = pressidium_cookie_consent_get_cookies();
$cookie_category = $attributes['cookieCategory'] ?? '';
$cookies         = $all_cookies[ $cookie_category ] ?? array();

// Columns
$column_names = array(
    'name',
    'domain',
    'expiration',
    'path',
    'description',
);

$column_headers = array(
    'name'        => __( 'Name', 'pressidium-cookie-consent' ),
    'domain'      => __( 'Domain', 'pressidium-cookie-consent' ),
    'expiration'  => __( 'Expiration', 'pressidium-cookie-consent' ),
    'path'        => __( 'Path', 'pressidium-cookie-consent' ),
    'description' => __( 'Description', 'pressidium-cookie-consent' ),
);

// Border
$fallback_border_color = $attributes['textColor'] ?? '#000000';

if ( ! isset( $attributes['borderColor'] ) ) {
    $border_color = $fallback_border_color;
} else {
    $border_color = Color_Utils::get_color_hex_by_slug( $attributes['borderColor'] );
    $border_color = ! empty( $border_color ) ? $border_color : $fallback_border_color;
}

$border_color_attr = 'border-color: ' . $border_color;

$border_styles = array();

if ( isset( $attributes['style']['border']['width'] ) ) {
    $border_styles['border-width'] = $attributes['style']['border']['width'];
}

if ( isset( $attributes['style']['border']['style'] ) ) {
    $border_styles['border-style'] = $attributes['style']['border']['style'];
}

$border_css = array();
foreach ( $border_styles as $property => $value ) {
    $border_css[] = esc_attr( $property ) . ':' . esc_attr( $value );
}

// Stripes
if ( isset( $attributes['stripeColor'] ) ) {
    $stripe_color = Color_Utils::get_color_preset_var_by_slug( $attributes['stripeColor'] );
} elseif ( isset( $attributes['customStripeColor'] ) ) {
    $stripe_color = $attributes['customStripeColor'];
} else {
    $stripe_color = 'transparent';
}

$block_wrapper_attributes = get_block_wrapper_attributes(
    array(
        'style' => sprintf( '--cc-block-stripe-color: %s;', esc_attr( $stripe_color ) ),
    )
);
?>

<div <?php echo wp_kses_data( $block_wrapper_attributes ); ?>>
    <table
        data-cookie-category="<?php echo esc_attr( $cookie_category ); ?>"
        style="<?php echo esc_attr( implode( ';', $border_css ) ); ?>"
    >
        <thead>
            <tr>
                <?php
                foreach ( $column_names as $column_name ) :
                    if ( ! $attributes['columnsVisibility'][ $column_name ] ) {
                        continue;
                    }

                    $alignment = $attributes['headerAlignment'] ?? 'center';
                    ?>

                    <th
                        style="<?php echo esc_attr( $border_color_attr ); ?>"
                        class="<?php echo esc_attr( 'align-' . $alignment ); ?>"
                    >
                        <?php echo esc_html( $column_headers[ $column_name ] ); ?>
                    </th>

                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $cookies as $cookie ) : ?>
                <tr>
                    <?php
                    foreach ( $column_names as $column_name ) :
                        if ( ! $attributes['columnsVisibility'][ $column_name ] ) {
                            continue;
                        }

                        $classes = array();

                        $should_wrap = ( $column_name === 'name' && $attributes['wrapName'] )
                            || ( $column_name === 'domain' && $attributes['wrapDomain'] );

                        if ( $should_wrap ) {
                            $classes[] = 'wrap';
                        }

                        $alignment = $attributes['columnsAlignment'][ $column_name ] ?? null;

                        $classes[] = $alignment ? 'align-' . $attributes['columnsAlignment'][ $column_name ] : '';
                        ?>

                        <td
                            data-column="<?php echo esc_attr( $column_name ); ?>"
                            class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
                            style="<?php echo esc_attr( $border_color_attr ); ?>"
                        >
                            <?php echo esc_html( $cookie[ $column_name ] ); ?>
                        </td>

                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
