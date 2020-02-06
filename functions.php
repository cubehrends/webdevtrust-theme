<?php
////////////////////////////////////////////////////////////////////
// Theme Information
////////////////////////////////////////////////////////////////////

    $themename = "webdevtrust";
    $developer_uri = "https://webdevtrust.com";
    $shortname = "wdt";
    define( 'WEBDEVTRUST_THEME_VERSION', '1.2.0' );
    load_theme_textdomain( 'webdevtrust', get_template_directory() . '/languages' );

////////////////////////////////////////////////////////////////////
// scripts
////////////////////////////////////////////////////////////////////
function theme_enqueue() {
    wp_enqueue_style( 'wdt-css', get_stylesheet_directory_uri() . '/style.css', array('hello-elementor-theme-style'), WEBDEVTRUST_THEME_VERSION, 'all' );
}
add_action( 'wp_enqueue_scripts', 'theme_enqueue' );
////////////////////////////////////////////////////////////////////
// dashboard
////////////////////////////////////////////////////////////////////
function wdt_dashboard_widget_content() {
	echo '<h3>Images</h3>
	<ul>
		<li>Featured 1200×675px</li>
    <li>Person 600×600px</li>
		<li>Sponsor Logo 400×200px</li>
	</ul>';
	echo '<h3>Colors</h3>
	<ul>
		<li>Primary (blue) #0274be</li>
		<li>Secondary (violet) #be0274</li>
    <li>Tertiary (bluegray) #6e7b8b</li>
    <li>Background blue #0a183c</li>
    <li>Background grey #2c3043</li>
	</ul>';
  echo '<hr><p>
    Contact <a href="https://webdevtrust.com">webdevtrust</a> for support
  <p>';
}
function add_dashboard_widgets() {
	wp_add_dashboard_widget(
		'wdt_dashboard_widget', // slug.
		'<img src="' . get_stylesheet_directory_uri() . '/img/logo-dashboard.png">', // title.
		'wdt_dashboard_widget_content' // function.
	);
}
add_action( 'wp_dashboard_setup', 'add_dashboard_widgets' );

////////////////////////////////////////////////////////////////////
// shortcodes
////////////////////////////////////////////////////////////////////

// returns the content without processing any shortcodes inside of it
function showshortcode($atts, $content = ''){
        return $content;
}
add_shortcode( 'showshortcode', 'showshortcode' );

////////////////////////////////////////////////////////////////////
// Comments
////////////////////////////////////////////////////////////////////
function register_pll_strings() {

  if( !function_exists( 'pll_register_string' ) ) return; // go away if polylang not active

	pll_register_string('Comment Consent', 'I accept your', 'Comments');
	pll_register_string('Privacy Policy', 'Privacy Policy', 'Comments');
	pll_register_string('Policy Page', 'privacy', 'Comments');
	pll_register_string('Error', 'ERROR', 'Comments');
	pll_register_string('Error Text', 'You must accept our Privacy Policy.', 'Comments');

}
add_action( 'init', 'register_pll_strings' );

function custom_comment_consent_fields( $fields ) {

  if( !function_exists( 'pll__' ) ) return; // go away if polylang not active

	$site_url = get_site_url();
	$locale_simple = substr( get_locale(), 0, 2 );
	$locale_url = $site_url .'/'. $locale_simple .'/';

	$privacy_policy_consent_label = pll__( 'I accept your' ) .' <a href="'. $locale_url . pll__( 'privacy' ) .'">'. pll__( 'Privacy Policy' ) .'</a>.';

	$fields['policy-consent'] =
		'<p class="comment-form-policy-consent">
			<input id="wp-comment-policy-consent" name="wp-comment-policy-consent" value="yes" type="checkbox"'. ( isset( $_COOKIE['comment_author_privacy_' . COOKIEHASH] )  ? ' checked="checked"' : '' ) .' aria-required="true">
			<label for="wp-comment-policy-consent">'. $privacy_policy_consent_label .'</label>
			<span class="comment-form-policy__required required">*</span>
		</p>';

    return $fields;
}

add_filter('comment_form_default_fields', 'custom_comment_consent_fields');

function verify_policy_consented( $commentdata ) {

  if( !function_exists( 'pll__' ) ) return; // go away if polylang not active

	if ( !isset( $_POST['wp-comment-policy-consent'] ) && !is_user_logged_in() ) {
    $privacy_policy_consent_error = '<strong>'. pll__( 'ERROR' ) .':</strong>&nbsp;'. pll__( 'You must accept our Privacy Policy.' );
		wp_die( $privacy_policy_consent_error . '<p><a href="javascript:history.back()">' . __('&laquo; Back') . '</a></p>');
	} else {
		$hashed_cookie = 'comment_author_privacy_' . COOKIEHASH;
		if ( isset( $_POST['wp-comment-cookies-consent'] ) ) {
			setcookie( $hashed_cookie, 1, time() + 3600 * 24 * 100, COOKIEPATH, COOKIE_DOMAIN, false );
		} else {
			setcookie( $hashed_cookie, '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN );
		}
	}

	return $commentdata;

}
add_filter('preprocess_comment', 'verify_policy_consented');
