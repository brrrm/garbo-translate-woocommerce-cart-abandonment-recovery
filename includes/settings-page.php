<?php
/**
 * Cartflows view for cart abandonment tabs.
 *
 * @package Woocommerce-Cart-Abandonment-Recovery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div class="wrap">
	
	<h1 id="wcf_cart_abandonment_tracking_table"><?php echo esc_html__( 'Translate WooCommerce Cart Abandonment Recovery', 'woo-cart-abandonment-recovery' ); ?></h1>

	<form action="<?php echo esc_html(admin_url('admin-post.php')); ?>" method="POST" class="garbo-translate-wcar-form">
		<input type="hidden" name="action" value="garbo_translate_wcar_save">
		<?php wp_nonce_field('garbo_translate_wcar_save', 'garbo_translate_wcar'); ?>
		
		<?php foreach($templates as $key => $template){ ?>
			<?php foreach($languages as $slug => $lang){ ?>

				<div class="template_<?php echo $template['id']; ?>">
					<h2> Translate <?php echo $template['template_name']; ?> to <?php echo $lang['name']; ?></h2>
					<div class="subject field">
						<label for="subject">Email subject</label>
						<input type="textfield" name="translations[<?php echo $template['id']; ?>][<?php echo $slug; ?>][email_subject]" value="<?php echo $lang['translations'][$template['id']]['email_subject']; ?>">
					</div>
					<div class="email_body field">
						<label for="email_body">Email body</label>
						<!--<textarea name="translations[<?php echo $template['id']; ?>][<?php echo $slug; ?>][email_body]"><?php echo $lang['translations'][$template['id']]['email_body']; ?></textarea> -->
						<?php
						wp_editor(
							stripslashes($lang['translations'][$template['id']]['email_body']),
							sprintf('translations_%d__%s__email_body', $template['id'], $slug),
							array(
								'media_buttons' => true,
								'textarea_rows' => 15,
								'tabindex' => 4,
								'tinymce'  => array(
									'theme_advanced_buttons1' => 'bold,italic,underline,|,bullist,numlist,blockquote,|,link,unlink,|,spellchecker,fullscreen,|,formatselect,styleselect',
								),
								'textarea_name' 	=> sprintf('translations[%d][%s][email_body]', $template['id'], $slug)
							)
						);
						?>
					</div>
				</div>


			<?php } ?>
		<?php } ?>

		<input type="submit" name="submit" value="save">
	</form>
</div>