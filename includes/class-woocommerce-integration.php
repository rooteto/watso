<?php
/**
 * WooCommerce Integration Logic
 */

if (!defined('ABSPATH')) {
	exit;
}

class WatsoWooCommerce {

	private static $instance = null;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		if (!class_exists('WooCommerce')) {
			return;
		}

		add_action('wp', array($this, 'init_hooks'));
	}

	public function init_hooks() {
		$settings = WatsoWhatsAppChat::get_instance()->get_settings();

		// Log hook initialization
		$this->log_to_console('WooCommerce: Initializing hooks', array(
			'woo_enabled' => isset($settings['woo_enabled']) ? $settings['woo_enabled'] : false,
			'cart_button_enabled' => isset($settings['woo_cart_button_enabled']) ? $settings['woo_cart_button_enabled'] : false,
			'order_button_enabled' => isset($settings['woo_order_button_enabled']) ? $settings['woo_order_button_enabled'] : false,
			'is_cart' => is_cart(),
			'is_checkout' => is_checkout(),
			'is_product' => is_product()
		));

		if (!isset($settings['woo_enabled']) || !$settings['woo_enabled']) {
			return;
		}

		// Product page integration
		add_filter('watso_whatsapp_message', array($this, 'customize_product_message'), 20, 2);

		// Cart page integration - Use more universal hooks
		if (is_cart() && isset($settings['woo_cart_button_enabled']) && $settings['woo_cart_button_enabled']) {
			$this->log_to_console('WooCommerce: Cart page detected');
			add_action('woocommerce_before_cart', array($this, 'render_cart_support_button'), 5);
			add_action('woocommerce_after_cart_totals', array($this, 'render_cart_support_button'), 15);
			add_action('woocommerce_cart_collaterals', array($this, 'render_cart_support_button'), 5);
		}

		// Checkout page integration
		if (is_checkout() && !is_order_received_page() && isset($settings['woo_cart_button_enabled']) && $settings['woo_cart_button_enabled']) {
			$this->log_to_console('WooCommerce: Checkout hooks added');
			add_action('woocommerce_before_checkout_form', array($this, 'render_cart_support_button'), 5);
			add_action('woocommerce_after_checkout_form', array($this, 'render_cart_support_button'), 15);
		}

		// Order confirmation page integration (Thank you page)
		if (is_order_received_page() && isset($settings['woo_order_button_enabled']) && $settings['woo_order_button_enabled']) {
			$this->log_to_console('WooCommerce: Thank you page hooks added');
			add_action('woocommerce_thankyou', array($this, 'render_order_status_button'), 20);
		}
	}

	/**
	 * Customize WhatsApp message for various WooCommerce pages
	 */
	public function customize_product_message($message, $settings) {
		// 1. Product Page
		if (is_product()) {
			global $product;
			if (!$product) {
				return $message;
			}

			$template = $settings['woo_product_message'] ?? __('Hello, I want to get information about [product_name]: [product_url]', 'watso-basic-chat');
			$template = watso_get_translated_string($template, 'Woo Product Message');

			$tags = array(
				'[product_name]'  => $product->get_name(),
				'[product_url]'   => get_permalink($product->get_id()),
				'[product_sku]'   => $product->get_sku(),
				'[product_price]' => $product->get_price()
			);

			$final_message = str_replace(array_keys($tags), array_values($tags), $template);
			
			$this->log_to_console('WooCommerce: Product message applied', array('message' => $final_message));
			return $final_message;
		}

		// 2. Cart Page (Apply to main widget too if enabled)
		if (is_cart() && isset($settings['woo_cart_button_enabled']) && $settings['woo_cart_button_enabled']) {
			$template = $settings['woo_cart_message'] ?? __('Hello, I need support with my cart before checkout.', 'watso-basic-chat');
			$final_message = watso_get_translated_string($template, 'Woo Cart Message');
			
			$this->log_to_console('WooCommerce: Cart message applied to widget', array('message' => $final_message));
			return $final_message;
		}

		// 3. Checkout Page (During payment)
		if (is_checkout() && !is_order_received_page() && isset($settings['woo_cart_button_enabled']) && $settings['woo_cart_button_enabled']) {
			$template = $settings['woo_cart_message'] ?? __('Hello, I need support with my cart before checkout.', 'watso-basic-chat');
			$final_message = watso_get_translated_string($template, 'Woo Cart Message');
			
			$this->log_to_console('WooCommerce: Checkout message applied to widget', array('message' => $final_message));
			return $final_message;
		}

		// 4. Thank You Page (Order Status)
		if (is_order_received_page() && isset($settings['woo_order_button_enabled']) && $settings['woo_order_button_enabled']) {
			$order_id = get_query_var('order-received');
			$order = wc_get_order($order_id);
			
			$template = $settings['woo_order_message'] ?? __('Hello, I want to ask about my order #[order_id].', 'watso-basic-chat');
			$template = watso_get_translated_string($template, 'Woo Order Message');

			$tags = array(
				'[order_id]'    => $order_id,
				'[order_total]' => $order ? $order->get_total() : ''
			);

			$final_message = str_replace(array_keys($tags), array_values($tags), $template);
			
			$this->log_to_console('WooCommerce: Order status message applied to widget', array('message' => $final_message));
			return $final_message;
		}

		return $message;
	}

	/**
	 * Render support button on cart page
	 */
	public function render_cart_support_button() {
		$settings = WatsoWhatsAppChat::get_instance()->get_settings();
		$button_text = $settings['woo_cart_button_text'] ?? __('Support before checkout', 'watso-basic-chat');
		$button_text = watso_get_translated_string($button_text, 'Woo Cart Button Text');

		$cart_message = $settings['woo_cart_message'] ?? __('Hello, I need support with my cart before checkout.', 'watso-basic-chat');
		$cart_message = watso_get_translated_string($cart_message, 'Woo Cart Message');
		
		// Temporarily override source_message_text for URL generation
		$settings['source_message_text'] = $cart_message;
		
		// Use first active number for this direct button
		$active_numbers = $this->get_active_numbers($settings);
		if (empty($active_numbers)) {
			WatsoWhatsAppChat::get_instance()->debug_log('WooCommerce: No active numbers found for cart button');
			return;
		}

		$number = $active_numbers[0]['number'];
		$whatsapp_url = WatsoWhatsAppChat::get_instance()->generate_whatsapp_url($number, $settings, 0);

		$this->log_to_console('WooCommerce: Rendering cart support button', array(
			'message' => $cart_message,
			'url' => $whatsapp_url
		));

		echo '<div class="watso-woo-cart-support" style="margin-top: 15px; margin-bottom: 15px;">';
		echo '<a href="' . esc_url($whatsapp_url) . '" class="watso-woo-button" target="_blank" rel="noopener noreferrer" style="background-color: #25D366; color: #fff; display: block; text-align: center; padding: 12px; border-radius: 8px; text-decoration: none; font-weight: 600;">';
		echo '<span class="dashicons dashicons-whatsapp" style="vertical-align: middle; margin-right: 5px;"></span>';
		echo esc_html($button_text);
		echo '</a>';
		echo '</div>';
	}

	/**
	 * Render order status button on thank you page
	 */
	public function render_order_status_button($order_id) {
		if (!$order_id) {
			return;
		}

		$order = wc_get_order($order_id);
		if (!$order) {
			return;
		}

		$settings = WatsoWhatsAppChat::get_instance()->get_settings();
		$template = $settings['woo_order_message'] ?? __('Hello, I want to ask about my order #[order_id].', 'watso-basic-chat');
		$template = watso_get_translated_string($template, 'Woo Order Message');

		$tags = array(
			'[order_id]'    => $order_id,
			'[order_total]' => $order->get_total()
		);

		$custom_message = str_replace(array_keys($tags), array_values($tags), $template);
		
		// Temporarily override source_message_text for URL generation
		$original_message = $settings['source_message_text'];
		$settings['source_message_text'] = $custom_message;

		$active_numbers = $this->get_active_numbers($settings);
		if (empty($active_numbers)) {
			return;
		}

		$number = $active_numbers[0]['number'];
		$whatsapp_url = WatsoWhatsAppChat::get_instance()->generate_whatsapp_url($number, $settings, 0);

		$this->log_to_console('WooCommerce: Rendering order status button', array(
			'order_id' => $order_id,
			'message' => $custom_message,
			'url' => $whatsapp_url
		));

		$box_title = $settings['woo_order_title'] ?? __('Need help with your order?', 'watso-basic-chat');
		$box_title = watso_get_translated_string($box_title, 'Woo Order Title');

		$button_text = $settings['woo_order_button_text'] ?? __('Ask Order Status via WhatsApp', 'watso-basic-chat');
		$button_text = watso_get_translated_string($button_text, 'Woo Order Button Text');

		echo '<div class="watso-woo-order-status">';
		echo '<h4>' . esc_html($box_title) . '</h4>';
		echo '<a href="' . esc_url($whatsapp_url) . '" class="watso-woo-button" target="_blank" rel="noopener noreferrer" style="background-color: #25D366; color: #fff;">';
		echo '<span class="dashicons dashicons-whatsapp"></span>';
		echo esc_html($button_text);
		echo '</a>';
		echo '</div>';
	}

	private function get_active_numbers($settings) {
		$active_numbers = array();
		
		// 1. Check for multiple numbers
		if (isset($settings['numbers']) && is_array($settings['numbers']) && !empty($settings['numbers'])) {
			foreach ($settings['numbers'] as $number) {
				if (!empty($number['number']) && (!isset($number['active']) || $number['active'])) {
					$active_numbers[] = $number;
				}
			}
		}
		
		// 2. Fallback to single primary number if no multiple numbers are active
		if (empty($active_numbers) && !empty($settings['phone_number'])) {
			$active_numbers[] = array(
				'number' => $settings['phone_number'],
				'title' => $settings['button_title'] ?? 'WhatsApp'
			);
		}
		
		return $active_numbers;
	}

	/**
	 * Helper to log messages to browser console if debug mode is active
	 */
	private function log_to_console($message, $data = null) {
		$settings = WatsoWhatsAppChat::get_instance()->get_settings();
		if (!isset($settings['debug_mode']) || !$settings['debug_mode']) {
			return;
		}

		$log_data = array(
			'message' => $message,
			'data' => $data,
			'timestamp' => current_time('c')
		);

		// Use footer for logging to ensure it appears in console
		add_action('wp_footer', function() use ($log_data) {
			echo '<script type="text/javascript">console.log("[Watso Server Debug]", ' . wp_json_encode($log_data) . ');</script>';
		}, 99);
	}
}
