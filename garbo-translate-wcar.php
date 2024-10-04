<?php

/*
Plugin Name:  Garbo translate wcar
Plugin URI:   https://www.garbo.nl
Description:  Translate email templates from Cart Abandoment plugin
Version:      1.0
Author:       Garbo
Author URI:   https://www.garbo.nl
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  garbo_translate_wcar
Domain Path:  /languages
*/

// create admin page

// for each template
 // for each language
  // text field



add_action('admin_menu', 'garbo_translate_wcar_setup_menu');
function garbo_translate_wcar_setup_menu(){
	// main page
	add_menu_page( 
		__('Translate Cart Abandonment', 'garbo_translate_wcar'),
		__('Translate Cart Abandonmentss', 'garbo_translate_wcar'),
		'manage_woocommerce',
		'garbo_translate_wcar',
		'garbo_translate_wcar_translate_page',
		'',
		58
	);
}

add_action( 'admin_enqueue_scripts', 'garbo_translate_wcar_enqueue_scripts' );
function garbo_translate_wcar_enqueue_scripts($hook){
	if($hook != 'toplevel_page_garbo_translate_wcar'){
		return;
	}

	wp_enqueue_style('garbo_translate_wcar', plugins_url('assets/css/garbo-translate-wcar.css', __FILE__), [], '0.0.1');
}

function garbo_translate_wcar_translate_page(){
	$templates = garbo_translate_wcar_get_templates();
	$lang_slugs = pll_languages_list(['fields' => 'slug', 'hide_default' => true]);
	$lang_names = pll_languages_list(['fields' => 'name', 'hide_default' => true]);
	$languages = [];
	foreach($lang_slugs as $key => $slug){
		$languages[$slug] = [
			'name'			=> $lang_names[$key]
		];
		foreach($templates as $template){
			$option_name = 'wcar_translation_' . $template['id'] . '_' . $slug;
			$default = [
				'email_subject'	=> $template['email_subject'],
				'email_body'	=> $template['email_body']
			];
			$languages[$slug]['translations'][$template['id']] = get_option($option_name, $default);
		}
	}
	include_once 'includes/settings-page.php';
}

function garbo_translate_wcar_get_templates(){
	global $wpdb;
	$cart_abandonment_template_table_name = $wpdb->prefix . CARTFLOWS_CA_EMAIL_TEMPLATE_TABLE;
	$templates = $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM {$cart_abandonment_template_table_name} WHERE is_activated = 1" ), //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		ARRAY_A
	); // db call ok; no-cache ok.
	return $templates;

}

add_action('admin_post_garbo_translate_wcar_save', 'garbo_translate_wcar_save');
function garbo_translate_wcar_save(){
	$nonce = sanitize_text_field($_POST['garbo_translate_wcar']);
    $action = sanitize_text_field($_POST['action']);
    if (!isset($nonce) || !wp_verify_nonce($nonce, $action)) {
        print 'Sorry, your nonce did not verify.';
        exit;
    }
    if (!current_user_can('manage_options')) {
        print 'You can\'t manage options';
        exit;
    }

    if(isset($_POST['translations']) && is_array($_POST['translations'])){
    	$settings = $_POST['translations'];
    	foreach($settings as $template_id => $translations){
    		foreach($translations as $slug => $translation){
    			update_option('wcar_translation_' . $template_id . '_' . $slug, $translation);
    		}
    	}
    }

    // redirect
    $redirect_to = admin_url('admin.php?page=garbo_translate_wcar');
    wp_safe_redirect($redirect_to);
    exit;
}


add_filter( 'woo_ca_recovery_email_data', 'garbo_translate_wcar_woo_ca_recovery_email_data', 10, 2); 
function garbo_translate_wcar_woo_ca_recovery_email_data($email_data, $preview_email ){
	$lang = garbo_translate_wcar_get_language($email_data);
	global $garbo_translate_wcar_lang;
	$garbo_translate_wcar_lang = $lang;

	$translation = get_option('wcar_translation_' . $email_data->email_template_id . '_' . $lang);
	if($translation){
		$email_data->email_subject = $translation['email_subject'];
		$email_data->email_body = $translation['email_body'];
	}

	return $email_data;
}

function garbo_translate_wcar_get_language($email_data){
	return pll_get_post_language($email_data->checkout_id, 'slug');
}

add_filter('option_wcf_ca_from_email', 'garbo_translate_wcar__option_wcf_ca_from_email', 10, 2);
add_filter('option_wcf_ca_reply_email', 'garbo_translate_wcar__option_wcf_ca_from_email', 10, 2);
function garbo_translate_wcar__option_wcf_ca_from_email($value, $option){
	global $garbo_translate_wcar_lang;
	if(!isset($garbo_translate_wcar_lang)){
		return $value;
	}

	switch($garbo_translate_wcar_lang){
		case 'sv':
			return 'info@norhage.se';
		case 'fi':
			return 'info@norhage.fi';
		case 'de':
			return 'info@norhage.de';
		case 'da':
			return 'info@norhage.dk';
		case 'nb':
		default:
			return 'info@norhage.no';
	}
}

add_filter('option_wcf_ca_from_name', 'garbo_translate_wcar__option_wcf_ca_from_name', 10, 2);
function garbo_translate_wcar__option_wcf_ca_from_name($value, $option){
	global $garbo_translate_wcar_lang;
	if(!isset($garbo_translate_wcar_lang)){
		return $value;
	}

	switch($garbo_translate_wcar_lang){
		case 'sv':
			return 'Norhage.se';
		case 'fi':
			return 'Norhage.fi';
		case 'de':
			return 'Norhage.de';
		case 'da':
			return 'Norhage.dk';
		case 'nb':
		default:
			return 'Norhage.no';
	}
	return $value;
}

