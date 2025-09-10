<?php
/**
 * Appearance Settings Functions - Improved Version
 * Short Description Color added
 */

if (!defined('ABSPATH')) {
	exit;
}

function watso_init_appearance_settings() {
	// WordPress settings API initialization for appearance
}

function watso_render_appearance_tab($settings) {
	?>
	<div class="watso-card">
		<h3><?php esc_html_e('Appearance & Colors', 'watso-basic-chat'); ?></h3>
		<div class="watso-form-grid">
			<div class="watso-form-field watso-field-full">
				<label><?php esc_html_e('Default Icon', 'watso-basic-chat'); ?></label>
				<div class="watso-icon-preview-container">
					<div class="watso-icon-preview">
						<img src="<?php echo esc_url(WATSO_PLUGIN_URL . 'assets/images/whatsapp-default.png'); ?>" alt="Default WhatsApp Icon">
					</div>
					<div class="watso-icon-actions">
						<button type="button" class="watso-btn watso-btn-secondary watso-reset-icon"><?php esc_html_e('Reset to Default', 'watso-basic-chat'); ?></button>
					</div>
				</div>
				<p class="watso-description"><?php esc_html_e('Default WhatsApp icon is displayed, you can change it below.', 'watso-basic-chat'); ?></p>
			</div>

			<div class="watso-form-field watso-field-full">
				<label><?php esc_html_e('Upload Custom Icon', 'watso-basic-chat'); ?></label>
				<div class="watso-upload-container">
					<input type="hidden" name="watso_settings[custom_icon]" value="<?php echo esc_url($settings['custom_icon'] ?? ''); ?>" class="watso-custom-icon-url">
					<div class="watso-upload-actions">
						<button type="button" class="watso-btn watso-btn-primary watso-upload-icon"><?php esc_html_e('Upload Icon', 'watso-basic-chat'); ?></button>
						<button type="button" class="watso-btn watso-btn-danger watso-remove-icon" style="<?php echo empty($settings['custom_icon']) ? 'display:none;' : ''; ?>"><?php esc_html_e('Remove', 'watso-basic-chat'); ?></button>
					</div>
					<div class="watso-custom-icon-preview" style="<?php echo empty($settings['custom_icon']) ? 'display:none;' : ''; ?>">
						<img src="<?php echo esc_url($settings['custom_icon'] ?? ''); ?>">
					</div>
				</div>
				<p class="watso-description"><?php esc_html_e('Upload your own icon (recommended size: 60x60px, PNG format).', 'watso-basic-chat'); ?></p>
			</div>

			<div class="watso-form-field">
				<label><?php esc_html_e('Button Color', 'watso-basic-chat'); ?></label>
				<div class="watso-color-container">
					<input type="text" name="watso_settings[button_color]" value="<?php echo esc_attr($settings['button_color'] ?? '#119849'); ?>" class="watso-color-picker">
					<button type="button" class="watso-btn watso-btn-secondary watso-reset-color"><?php esc_html_e('Reset', 'watso-basic-chat'); ?></button>
				</div>
				<p class="watso-description"><?php esc_html_e('WhatsApp green is default but you can change it.', 'watso-basic-chat'); ?></p>
			</div>

			<div class="watso-form-field">
				<label><?php esc_html_e('Short Description Color', 'watso-basic-chat'); ?></label>
				<div class="watso-color-container">
					<input type="text" name="watso_settings[short_description_color]" value="<?php echo esc_attr($settings['short_description_color'] ?? '#888888'); ?>" class="watso-color-picker">
					<button type="button" class="watso-btn watso-btn-secondary watso-reset-description-color"><?php esc_html_e('Reset', 'watso-basic-chat'); ?></button>
				</div>
				<p class="watso-description"><?php esc_html_e('Color of short description text. Gray tones are recommended.', 'watso-basic-chat'); ?></p>
			</div>

			<div class="watso-form-field">
				<label><?php esc_html_e('Status Text Color', 'watso-basic-chat'); ?></label>
				<div class="watso-color-container">
					<input type="text" name="watso_settings[status_text_color]" value="<?php echo esc_attr($settings['status_text_color'] ?? '#4CAF50'); ?>" class="watso-color-picker">
					<button type="button" class="watso-btn watso-btn-secondary watso-reset-status-color"><?php esc_html_e('Reset', 'watso-basic-chat'); ?></button>
				</div>
				<p class="watso-description"><?php esc_html_e('Color of status text (Online, Busy, etc.). Default green.', 'watso-basic-chat'); ?></p>
			</div>

			<div class="watso-form-field">
				<label><?php esc_html_e('Department Text Color', 'watso-basic-chat'); ?></label>
				<div class="watso-color-container">
					<input type="text" name="watso_settings[department_color]" value="<?php echo esc_attr($settings['department_color'] ?? '#666666'); ?>" class="watso-color-picker">
					<button type="button" class="watso-btn watso-btn-secondary watso-reset-department-color"><?php esc_html_e('Reset', 'watso-basic-chat'); ?></button>
				</div>
				<p class="watso-description"><?php esc_html_e('Color of department name. Appears as subtitle.', 'watso-basic-chat'); ?></p>
			</div>
		</div>

		<div class="watso-info-box">
			<h4><?php esc_html_e('Color Descriptions', 'watso-basic-chat'); ?></h4>
			<ul style="margin: 10px 0; padding-left: 20px;">
				<li><strong><?php esc_html_e('Button Color:', 'watso-basic-chat'); ?></strong> <?php esc_html_e('Main button and avatar background', 'watso-basic-chat'); ?></li>
				<li><strong><?php esc_html_e('Status Text:', 'watso-basic-chat'); ?></strong> <?php esc_html_e('Online/Busy status and online indicator', 'watso-basic-chat'); ?></li>
				<li><strong><?php esc_html_e('Department:', 'watso-basic-chat'); ?></strong> <?php esc_html_e('Department information under person\'s name', 'watso-basic-chat'); ?></li>
				<li><strong><?php esc_html_e('Short Description:', 'watso-basic-chat'); ?></strong> <?php esc_html_e('Small description text at the bottom', 'watso-basic-chat'); ?></li>
			</ul>
		</div>
	</div>
	<?php
}
?>
