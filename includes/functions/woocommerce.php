<?php
/**
 * WooCommerce Settings Functions
 */

if (!defined('ABSPATH')) {
	exit;
}

function watso_init_woocommerce_settings() {
	// Initialization logic if needed
}

function watso_render_woocommerce_tab($settings) {
	$is_woo_active = class_exists('WooCommerce');
	?>
	<?php if (!$is_woo_active) : ?>
		<div class="watso-alert watso-alert-warning" style="margin-bottom: 20px; border-left: 4px solid #ffba00; background: #fff; padding: 15px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
			<p style="margin: 0; font-weight: 500; color: #856404;">
				<span class="dashicons dashicons-warning" style="color: #ffba00; margin-right: 5px;"></span>
				<?php _e('WooCommerce plugin is not active. Please install and activate WooCommerce to use these features.', 'watso-basic-chat'); ?>
			</p>
		</div>
	<?php endif; ?>

	<div class="watso-card <?php echo !$is_woo_active ? 'watso-disabled-card' : ''; ?>" style="<?php echo !$is_woo_active ? 'opacity: 0.6; pointer-events: none;' : ''; ?>">
		<h3><?php esc_html_e('WooCommerce Integration', 'watso-basic-chat'); ?></h3>
		<p class="watso-description">
			<?php esc_html_e('Enable special WhatsApp features for your WooCommerce store.', 'watso-basic-chat'); ?>
		</p>

		<div class="watso-form-grid">
			<div class="watso-form-field">
				<label><?php esc_html_e('Enable Integration', 'watso-basic-chat'); ?></label>
				<label class="watso-toggle">
					<input type="checkbox" name="watso_settings[woo_enabled]" value="1" <?php checked($settings['woo_enabled'] ?? false); ?>>
					<span class="watso-toggle-slider"></span>
				</label>
				<p class="watso-description"><?php esc_html_e('Activate WooCommerce specific features.', 'watso-basic-chat'); ?></p>
			</div>
		</div>
	</div>

	<div class="watso-card <?php echo !$is_woo_active ? 'watso-disabled-card' : ''; ?>" style="<?php echo !$is_woo_active ? 'opacity: 0.6; pointer-events: none;' : ''; ?>">
		<h3><?php esc_html_e('Product Page Settings', 'watso-basic-chat'); ?></h3>
		<div class="watso-form-grid">
			<div class="watso-form-field">
				<label><?php esc_html_e('Product Message Template', 'watso-basic-chat'); ?></label>
				<textarea name="watso_settings[woo_product_message]" class="watso-input" rows="3"><?php echo esc_textarea($settings['woo_product_message'] ?? __('Hello, I want to get information about [product_name]: [product_url]', 'watso-basic-chat')); ?></textarea>
				<p class="watso-description">
					<?php esc_html_e('Message sent when customer clicks WhatsApp button on product page.', 'watso-basic-chat'); ?><br>
					<strong><?php esc_html_e('Available tags:', 'watso-basic-chat'); ?></strong> <code>[product_name]</code>, <code>[product_url]</code>, <code>[product_sku]</code>, <code>[product_price]</code>
				</p>
			</div>
		</div>
	</div>

	<div class="watso-card <?php echo !$is_woo_active ? 'watso-disabled-card' : ''; ?>" style="<?php echo !$is_woo_active ? 'opacity: 0.6; pointer-events: none;' : ''; ?>">
		<h3><?php esc_html_e('Cart Page Settings', 'watso-basic-chat'); ?></h3>
		<div class="watso-form-grid">
			<div class="watso-form-field">
				<label><?php esc_html_e('Show Support Button in Cart', 'watso-basic-chat'); ?></label>
				<label class="watso-toggle">
					<input type="checkbox" name="watso_settings[woo_cart_button_enabled]" value="1" <?php checked($settings['woo_cart_button_enabled'] ?? false); ?>>
					<span class="watso-toggle-slider"></span>
				</label>
			</div>

			<div class="watso-form-field">
				<label><?php esc_html_e('Cart Button Text', 'watso-basic-chat'); ?></label>
				<input type="text" name="watso_settings[woo_cart_button_text]" value="<?php echo esc_attr($settings['woo_cart_button_text'] ?? __('Support before checkout', 'watso-basic-chat')); ?>" class="watso-input">
			</div>

			<div class="watso-form-field">
				<label><?php esc_html_e('Cart Message Template', 'watso-basic-chat'); ?></label>
				<textarea name="watso_settings[woo_cart_message]" class="watso-input" rows="3"><?php echo esc_textarea($settings['woo_cart_message'] ?? __('Hello, I need support with my cart before checkout.', 'watso-basic-chat')); ?></textarea>
				<p class="watso-description">
					<?php esc_html_e('Message sent when customer clicks WhatsApp button on cart page.', 'watso-basic-chat'); ?>
				</p>
			</div>
		</div>
	</div>

	<div class="watso-card <?php echo !$is_woo_active ? 'watso-disabled-card' : ''; ?>" style="<?php echo !$is_woo_active ? 'opacity: 0.6; pointer-events: none;' : ''; ?>">
		<h3><?php esc_html_e('Order Confirmation Settings', 'watso-basic-chat'); ?></h3>
		<div class="watso-form-grid">
			<div class="watso-form-field">
				<label><?php esc_html_e('Show Order Status Button', 'watso-basic-chat'); ?></label>
				<label class="watso-toggle">
					<input type="checkbox" name="watso_settings[woo_order_button_enabled]" value="1" <?php checked($settings['woo_order_button_enabled'] ?? false); ?>>
					<span class="watso-toggle-slider"></span>
				</label>
			</div>

			<div class="watso-form-field">
				<label><?php esc_html_e('Order Box Title', 'watso-basic-chat'); ?></label>
				<input type="text" name="watso_settings[woo_order_title]" value="<?php echo esc_attr($settings['woo_order_title'] ?? __('Need help with your order?', 'watso-basic-chat')); ?>" class="watso-input">
			</div>

			<div class="watso-form-field">
				<label><?php esc_html_e('Order Status Button Text', 'watso-basic-chat'); ?></label>
				<input type="text" name="watso_settings[woo_order_button_text]" value="<?php echo esc_attr($settings['woo_order_button_text'] ?? __('Ask Order Status via WhatsApp', 'watso-basic-chat')); ?>" class="watso-input">
			</div>

			<div class="watso-form-field">
				<label><?php esc_html_e('Order Status Message Template', 'watso-basic-chat'); ?></label>
				<textarea name="watso_settings[woo_order_message]" class="watso-input" rows="3"><?php echo esc_textarea($settings['woo_order_message'] ?? __('Hello, I want to ask about my order #[order_id].', 'watso-basic-chat')); ?></textarea>
				<p class="watso-description">
					<?php esc_html_e('Message sent when customer clicks WhatsApp button on order confirmation page.', 'watso-basic-chat'); ?><br>
					<strong><?php esc_html_e('Available tags:', 'watso-basic-chat'); ?></strong> <code>[order_id]</code>, <code>[order_total]</code>
				</p>
			</div>
		</div>
	</div>
	<?php
}
