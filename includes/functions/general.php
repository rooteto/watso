<?php
/**
 * General Settings Functions - Improved Version
 * Online Status Text removed (now separate for each number)
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

function watso_init_general_settings() {
	add_settings_section(
		'watso_general_section',
		__('General Settings', 'watso-basic-chat'),
		'watso_general_section_callback',
		'watso_general'
	);

	add_settings_field(
		'watso_active',
		__('Plugin Status', 'watso-basic-chat'),
		'watso_active_field_callback',
		'watso_general',
		'watso_general_section'
	);

	add_settings_field(
		'watso_position',
		__('Position', 'watso-basic-chat'),
		'watso_position_field_callback',
		'watso_general',
		'watso_general_section'
	);

	add_settings_field(
		'watso_show_mobile',
		__('Show on Mobile', 'watso-basic-chat'),
		'watso_show_mobile_field_callback',
		'watso_general',
		'watso_general_section'
	);

	add_settings_field(
		'watso_button_title',
		__('Title', 'watso-basic-chat'),
		'watso_button_title_field_callback',
		'watso_general',
		'watso_general_section'
	);

	add_settings_field(
		'watso_dropdown_header_text',
		__('Dropdown Header Text', 'watso-basic-chat'),
		'watso_dropdown_header_text_field_callback',
		'watso_general',
		'watso_general_section'
	);

	add_settings_field(
		'watso_button_radius',
		__('Corner Radius', 'watso-basic-chat'),
		'watso_button_radius_field_callback',
		'watso_general',
		'watso_general_section'
	);
}

function watso_general_section_callback() {
	echo '<p>' . esc_html__('You can configure the basic settings of the plugin here.', 'watso-basic-chat') . '</p>';
}

function watso_active_field_callback() {
	$settings = WatsoWhatsAppChat::get_instance()->get_settings();
	?>
	<label class="watso-toggle">
		<input type="checkbox" name="watso_settings[active]" value="1" <?php checked($settings['active']); ?>>
		<span class="watso-toggle-slider"></span>
	</label>
	<p class="description"><?php esc_html_e('Turn the plugin on or off. When off, nobody can see the button.', 'watso-basic-chat'); ?></p>
	<?php
}

function watso_position_field_callback() {
	$settings = WatsoWhatsAppChat::get_instance()->get_settings();
	$positions = array(
		'bottom-right' => __('Bottom Right', 'watso-basic-chat'),
		'bottom-left' => __('Bottom Left', 'watso-basic-chat'),
		'middle-right' => __('Middle Right', 'watso-basic-chat'),
		'middle-left' => __('Middle Left', 'watso-basic-chat')
	);
	?>
	<select name="watso_settings[position]" class="watso-select">
		<?php foreach ($positions as $value => $label): ?>
			<option value="<?php echo esc_attr($value); ?>" <?php selected($settings['position'], $value); ?>>
				<?php echo esc_html($label); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<p class="description"><?php esc_html_e('Select the position where the button will appear on the screen.', 'watso-basic-chat'); ?></p>
	<?php
}

function watso_show_mobile_field_callback() {
	$settings = WatsoWhatsAppChat::get_instance()->get_settings();
	?>
	<label class="watso-toggle">
		<input type="checkbox" name="watso_settings[show_mobile]" value="1" <?php checked($settings['show_mobile']); ?>>
		<span class="watso-toggle-slider"></span>
	</label>
	<p class="description"><?php esc_html_e('Should the button also be visible on mobile devices?', 'watso-basic-chat'); ?></p>
	<?php
}

function watso_button_title_field_callback() {
	$settings = WatsoWhatsAppChat::get_instance()->get_settings();
	?>
	<input type="text" name="watso_settings[button_title]" value="<?php echo esc_attr($settings['button_title']); ?>" class="watso-input regular-text" placeholder="<?php esc_html_e('WhatsApp Support', 'watso-basic-chat'); ?>">
	<p class="description"><?php esc_html_e('The text that appears on the button.', 'watso-basic-chat'); ?></p>
	<?php
}

function watso_dropdown_header_text_field_callback() {
	$settings = WatsoWhatsAppChat::get_instance()->get_settings();
	?>
	<input type="text" name="watso_settings[dropdown_header_text]" value="<?php echo esc_attr($settings['dropdown_header_text']); ?>" class="watso-input regular-text" placeholder="<?php esc_html_e('Choose a contact', 'watso-basic-chat'); ?>">
	<p class="description"><?php esc_html_e('The header text displayed in the multi-number menu.', 'watso-basic-chat'); ?></p>
	<?php
}

function watso_button_radius_field_callback() {
	$settings = WatsoWhatsAppChat::get_instance()->get_settings();
	?>
	<div class="watso-range-container">
		<input type="range" name="watso_settings[button_radius]" value="<?php echo esc_attr($settings['button_radius']); ?>" min="0" max="30" class="watso-range-slider">
		<span class="watso-range-value"><?php echo esc_html($settings['button_radius']); ?>px</span>
	</div>
	<p class="description"><?php esc_html_e('Button corner radius (0px = square, 30px = fully rounded).', 'watso-basic-chat'); ?></p>
	<?php
}

function watso_render_general_tab($settings) {
	?>
	<div class="watso-card">
		<h3><?php esc_html_e('General Settings', 'watso-basic-chat'); ?></h3>
		<div class="watso-form-grid">
			<div class="watso-form-field">
				<label><?php esc_html_e('Plugin Status', 'watso-basic-chat'); ?></label>
				<?php watso_active_field_callback(); ?>
			</div>

			<div class="watso-form-field">
				<label><?php esc_html_e('Show on Mobile', 'watso-basic-chat'); ?></label>
				<?php watso_show_mobile_field_callback(); ?>
			</div>

			<div class="watso-form-field">
				<label><?php esc_html_e('Position', 'watso-basic-chat'); ?></label>
				<?php watso_position_field_callback(); ?>
			</div>

			<div class="watso-form-field">
				<label><?php esc_html_e('Title', 'watso-basic-chat'); ?></label>
				<?php watso_button_title_field_callback(); ?>
			</div>

			<div class="watso-form-field">
				<label><?php esc_html_e('Dropdown Header Text', 'watso-basic-chat'); ?></label>
				<?php watso_dropdown_header_text_field_callback(); ?>
			</div>

			<div class="watso-form-field watso-field-full">
				<label><?php esc_html_e('Corner Radius', 'watso-basic-chat'); ?></label>
				<?php watso_button_radius_field_callback(); ?>
			</div>
		</div>
	</div>
	<?php
}
