<?php


if(!function_exists('cesis_fonts_url'))
{
function cesis_fonts_url() {
$fonts_url = '';

/* Translators: If there are characters in your language that are not
* supported by Lora, translate this to 'off'. Do not translate
* into your own language.
*/
$poppins = _x( 'on', 'Poppins font: on or off', 'cesis' );

/* Translators: If there are characters in your language that are not
* supported by Open Sans, translate this to 'off'. Do not translate
* into your own language.
*/
$open_sans = _x( 'on', 'Poppins font: on or off', 'cesis' );

/* Translators: If there are characters in your language that are not
* supported by Open Sans, translate this to 'off'. Do not translate
* into your own language.
*/
$roboto = _x( 'on', 'Roboto font: on or off', 'cesis' );

if ( 'off' !== $poppins || 'off' !== $open_sans || 'off' !== $roboto ) {
$font_families = array();

if ( 'off' !== $poppins ) {
$font_families[] = 'Poppins:400,500,600,70';
}

if ( 'off' !== $open_sans ) {
$font_families[] = 'Open Sans:300,400,600,700';
}
if ( 'off' !== $roboto ) {
$font_families[] = 'Roboto:400,500,700,900';
}

$query_args = array(
'family' => urlencode( implode( '|', $font_families ) ),
'subset' => urlencode( 'latin,latin-ext' ),
);

$fonts_url = add_query_arg( $query_args, 'https://fonts.googleapis.com/css' );
}

return esc_url_raw( $fonts_url );
}
}
