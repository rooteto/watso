<?php
/**
 * Tracking Settings Functions
 */

if (!defined('ABSPATH')) {
	exit;
}

function watso_init_tracking_settings() {
	// WordPress settings API initialization for tracking
}

function watso_render_tracking_tab($settings) {
	?>
	<div class="watso-card">
		<h3><?php esc_html_e('Tracking & UTM Settings', 'watso-basic-chat'); ?></h3>
		<div class="watso-form-grid">
			<div class="watso-form-field">
				<label><?php esc_html_e('UTM Parameters', 'watso-basic-chat'); ?></label>
				<label class="watso-toggle">
					<input type="checkbox" name="watso_settings[utm_enabled]" value="1" <?php checked($settings['utm_enabled']); ?>>
					<span class="watso-toggle-slider"></span>
				</label>
				<p class="watso-description">
					<?php esc_html_e('Add UTM parameters for Google Analytics tracking.', 'watso-basic-chat'); ?>
					<br><a href="https://support.google.com/analytics/answer/10917952" target="_blank"><?php esc_html_e('How to view UTM data in Google Analytics', 'watso-basic-chat'); ?></a>
				</p>

				<!-- UTM Fields - Same column -->
				<div class="watso-utm-fields watso-sub-fields" style="<?php echo !$settings['utm_enabled'] ? 'display:none;' : ''; ?>">
					<div class="watso-sub-container">
						<h5 class="watso-sub-title"><?php esc_html_e('UTM Parameters', 'watso-basic-chat'); ?></h5>

						<div class="watso-sub-field">
							<label class="watso-sub-label"><?php esc_html_e('UTM Source', 'watso-basic-chat'); ?></label>
							<input type="text" name="watso_settings[utm_source]" value="<?php echo esc_attr($settings['utm_source'] ?? 'website'); ?>" class="watso-input watso-sub-input" placeholder="website">
						</div>

						<div class="watso-sub-field">
							<label class="watso-sub-label"><?php esc_html_e('UTM Medium', 'watso-basic-chat'); ?></label>
							<input type="text" name="watso_settings[utm_medium]" value="<?php echo esc_attr($settings['utm_medium'] ?? 'whatsapp'); ?>" class="watso-input watso-sub-input" placeholder="whatsapp">
						</div>

						<div class="watso-sub-field">
							<label class="watso-sub-label"><?php esc_html_e('UTM Campaign', 'watso-basic-chat'); ?></label>
							<input type="text" name="watso_settings[utm_campaign]" value="<?php echo esc_attr($settings['utm_campaign'] ?? 'support'); ?>" class="watso-input watso-sub-input" placeholder="support">
							<p class="watso-sub-description"><?php esc_html_e('Automatic "-2", "-3" suffixes are added for multiple numbers', 'watso-basic-chat'); ?></p>
						</div>
					</div>
				</div>
			</div>

			<div class="watso-form-field">
				<label><?php esc_html_e('Facebook/Meta Pixel Tracking', 'watso-basic-chat'); ?></label>
				<label class="watso-toggle">
					<input type="checkbox" name="watso_settings[meta_tracking]" value="1" <?php checked($settings['meta_tracking']); ?>>
					<span class="watso-toggle-slider"></span>
				</label>
				<p class="watso-description">
					<?php esc_html_e('Track the performance of your Facebook ads. Sends "Watso Contact" event for single number, "Watso Contact 2", "Watso Contact 3" for multiple numbers.', 'watso-basic-chat'); ?>
					<br><a href="https://www.facebook.com/business/help/952192354843755" target="_blank"><?php esc_html_e('Meta Pixel setup guide', 'watso-basic-chat'); ?></a>
				</p>
			</div>

			<div class="watso-form-field">
				<label><?php esc_html_e('Show Click Source', 'watso-basic-chat'); ?></label>
				<label class="watso-toggle">
					<input type="checkbox" id="watso_show_source_url" name="watso_settings[show_source_url]" value="1" <?php checked($settings['show_source_url']); ?>>
					<span class="watso-toggle-slider"></span>
				</label>
				<p class="watso-description"><?php esc_html_e('Include the page URL where user clicked the button in WhatsApp message.', 'watso-basic-chat'); ?></p>

				<!-- Source Message Field - Same column -->
				<div class="watso-source-message-field watso-sub-fields" style="<?php echo !$settings['show_source_url'] ? 'display:none;' : ''; ?>">
					<div class="watso-sub-container">
						<h5 class="watso-sub-title"><?php esc_html_e('Source Message Settings', 'watso-basic-chat'); ?></h5>
						<div class="watso-sub-field">
							<label class="watso-sub-label"><?php esc_html_e('Source Message Text', 'watso-basic-chat'); ?></label>
							<input type="text" name="watso_settings[source_message_text]" value="<?php echo esc_attr($settings['source_message_text'] ?? 'Hello! I am visiting this page:'); ?>" class="watso-input watso-sub-input" placeholder="<?php esc_html_e('Hello! I am visiting this page:', 'watso-basic-chat'); ?>">
							<p class="watso-sub-description"><?php esc_html_e('This message is sent to WhatsApp along with the page URL.', 'watso-basic-chat'); ?></p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
}
