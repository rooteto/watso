<?php
/**
 * Numbers Settings Functions - Improved Version
 * For each number: Phone, Title, Department, Status, Short Text
 */

if (!defined('ABSPATH')) {
	exit;
}

function watso_init_numbers_settings() {
	// WordPress settings API initialization for numbers
}

function watso_render_numbers_tab($settings) {
	?>
	<div class="watso-card">
		<h3><?php esc_html_e('Numbers & Contact Information', 'watso-basic-chat'); ?></h3>
		<div class="watso-numbers-container">
			<?php
			$numbers = isset($settings['numbers']) ? $settings['numbers'] : array(array(
				  'number' => '',
				  'title' => '',
				  'department' => '',
				  'status_text' => '',
				  'short_description' => '',
				  'active' => true
			  ));
			foreach ($numbers as $index => $number):
				?>
				<div class="watso-number-card">
					<div class="watso-number-header">
						<h4><?php esc_html_e('Contact', 'watso-basic-chat'); ?> #<?php echo esc_html($index + 1); ?></h4>
						<label class="watso-toggle watso-toggle-small">
							<input type="checkbox" name="watso_settings[numbers][<?php echo esc_attr($index); ?>][active]" value="1" <?php checked(isset($number['active']) ? $number['active'] : true); ?>>
							<span class="watso-toggle-slider"></span>
						</label>
					</div>
					<div class="watso-number-fields">
						<!-- Phone Number -->
						<div class="watso-form-field">
							<label><?php esc_html_e('Phone Number', 'watso-basic-chat'); ?></label>
							<input type="text" name="watso_settings[numbers][<?php echo esc_attr($index); ?>][number]" value="<?php echo esc_attr($number['number'] ?? ''); ?>" placeholder="<?php esc_html_e('e.g: +905551234567', 'watso-basic-chat'); ?>" class="watso-input">
						</div>

						<!-- Title -->
						<div class="watso-form-field">
							<label><?php esc_html_e('Title', 'watso-basic-chat'); ?></label>
							<input type="text" name="watso_settings[numbers][<?php echo esc_attr($index); ?>][title]" value="<?php echo esc_attr($number['title'] ?? ''); ?>" placeholder="<?php esc_html_e('e.g: John Smith', 'watso-basic-chat'); ?>" class="watso-input">
						</div>

						<!-- Department -->
						<div class="watso-form-field">
							<label><?php esc_html_e('Department', 'watso-basic-chat'); ?></label>
							<input type="text" name="watso_settings[numbers][<?php echo esc_attr($index); ?>][department]" value="<?php echo esc_attr($number['department'] ?? ''); ?>" placeholder="<?php esc_html_e('e.g: Technical Support', 'watso-basic-chat'); ?>" class="watso-input">
						</div>

						<!-- Status Text -->
						<div class="watso-form-field">
							<label><?php esc_html_e('Status Text', 'watso-basic-chat'); ?></label>
							<input type="text" name="watso_settings[numbers][<?php echo esc_attr($index); ?>][status_text]" value="<?php echo esc_attr($number['status_text'] ?? ''); ?>" placeholder="<?php esc_html_e('e.g: Online, Busy, In Meeting', 'watso-basic-chat'); ?>" class="watso-input">
							<p class="watso-field-description"><?php esc_html_e('If left blank, status will not be displayed', 'watso-basic-chat'); ?></p>
						</div>

						<!-- Short Description -->
						<div class="watso-form-field watso-field-full">
							<label><?php esc_html_e('Short Description', 'watso-basic-chat'); ?></label>
							<input type="text" name="watso_settings[numbers][<?php echo esc_attr($index); ?>][short_description]" value="<?php echo esc_attr($number['short_description'] ?? ''); ?>" placeholder="<?php esc_html_e('e.g: Available Mon-Fri 9AM-6PM', 'watso-basic-chat'); ?>" class="watso-input">
							<p class="watso-field-description"><?php esc_html_e('Displayed in small font. You can write working hours, availability, or any brief note.', 'watso-basic-chat'); ?></p>
						</div>
					</div>
					<div class="watso-number-actions">
						<button type="button" class="watso-btn watso-btn-danger watso-btn-small watso-remove-number" <?php echo count($numbers) <= 1 ? 'disabled' : ''; ?>><?php esc_html_e('Delete', 'watso-basic-chat'); ?></button>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="watso-form-actions">
			<button type="button" class="watso-btn watso-btn-primary watso-add-number"><?php esc_html_e('Add Number', 'watso-basic-chat'); ?></button>
		</div>
		<div class="watso-info-box">
			<p><?php esc_html_e('If there is one number, users are redirected directly. If there are multiple numbers, a dropdown menu is shown.', 'watso-basic-chat'); ?></p>
			<p><strong><?php esc_html_e('Display Order:', 'watso-basic-chat'); ?></strong></p>
			<ul>
				<li><?php esc_html_e('• Title (main name/person)', 'watso-basic-chat'); ?></li>
				<li><?php esc_html_e('• Department (subtitle)', 'watso-basic-chat'); ?></li>
				<li><?php esc_html_e('• Status + Short Description (on the right side)', 'watso-basic-chat'); ?></li>
			</ul>
		</div>
	</div>
	<?php
}
