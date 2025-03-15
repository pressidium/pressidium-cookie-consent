<?php
$all_cookies     = pressidium_cookie_consent_get_cookies();
$cookie_category = $attributes['cookieCategory'] ?? '';
$cookies         = $all_cookies[ $cookie_category ] ?? array();

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

$fallback_color    = $attributes['textColor'] ?? '#000000';
$border_color      = \Pressidium\WP\CookieConsent\Utils::get_color_hex_by_slug( $attributes['borderColor'] );
$border_color      = ! empty( $border_color ) ? $border_color : $fallback_color;
$border_color_attr = 'border-color: ' . $border_color;

$border_styles = array(
    'border-width' => $attributes['style']['border']['width'] ?? '0',
    'border-style' => $attributes['style']['border']['style'] ?? 'solid',
);

$border_css = array();
foreach ( $border_styles as $property => $value ) {
    $border_css[] = esc_attr( $property ) . ':' . esc_attr( $value );
}
?>

<div <?php echo wp_kses_data( get_block_wrapper_attributes() ); ?>>
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
