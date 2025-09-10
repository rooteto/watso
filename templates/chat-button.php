<?php
/**
 * Chat Button Template
 *
 * @package Watso WhatsApp Chat
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

// Get settings using the class method
$watso_instance = WatsoWhatsAppChat::get_instance();
$settings = $watso_instance->get_settings();
$active_numbers = array();

// Filter active numbers
if ($settings['debug_mode']) {
	echo '<!-- Watso Template: Loading -->';
}

// Filter active numbers
if (isset($settings['numbers']) && is_array($settings['numbers'])) {
	foreach ($settings['numbers'] as $number) {
		if (!empty($number['number']) && (!isset($number['active']) || $number['active'])) {
			$active_numbers[] = $number;
		}
	}
}

// Debug
if ($settings['debug_mode']) {
	echo '<!-- Watso Template: Found ' . count($active_numbers) . ' active numbers -->';
}

// If no active numbers, don't show anything
if (empty($active_numbers)) {
	if ($settings['debug_mode']) {
		echo '<!-- Watso Template: No active numbers found -->';
	}
	return;
}

// Get button settings
$position = $settings['position'];
$button_title = isset($settings['button_title']) ? trim($settings['button_title']) : '';
$has_title = !empty($button_title);

// NEW: Online status text and dropdown header text settings
$online_status_text = isset($settings['online_status_text']) ? trim($settings['online_status_text']) : __('Online', 'watso-basic-chat');
$dropdown_header_text = isset($settings['dropdown_header_text']) ? trim($settings['dropdown_header_text']) : __('Select a contact', 'watso-basic-chat');

$button_radius = $settings['button_radius'];
$button_color = $settings['button_color'];

// Get icon URL
$icon_url = WATSO_PLUGIN_URL . 'assets/images/whatsapp-default.png';
if (!empty($settings['custom_icon'])) {
	$icon_url = $settings['custom_icon'];
}

// Position class
$position_class = 'watso-' . $position;
?>

<div id="watso-chat-widget"
	 class="watso-chat-widget <?php echo esc_attr($position_class); ?>"
	 data-position="<?php echo esc_attr($position); ?>"
	 data-color="<?php echo esc_attr($button_color); ?>"
	 data-radius="<?php echo esc_attr($button_radius); ?>">

	<?php if (count($active_numbers) === 1): ?>
		<!-- Single number - direct link -->
		<?php
		$number = $active_numbers[0];
		$whatsapp_url = $watso_instance->generate_whatsapp_url($number['number'], $settings);
		?>
		<a href="<?php echo esc_url($whatsapp_url); ?>"
		   class="watso-chat-button watso-single-number"
		   target="_blank"
		   rel="noopener noreferrer"
		   data-number="<?php echo esc_attr($number['number']); ?>"
		   <?php if ($has_title): ?>title="<?php echo esc_attr($button_title); ?>"<?php endif; ?>
		   style="border-radius: <?php echo esc_attr($button_radius); ?>px; background-color: <?php echo esc_attr($button_color); ?>;">
			<img src="<?php echo esc_url($icon_url); ?>"
				 alt="WhatsApp"
				 class="watso-icon">
			<?php if ($has_title): ?>
				<span class="watso-button-text"><?php echo esc_html($button_title); ?></span>
			<?php endif; ?>
		</a>

	<?php else: ?>
		<!-- Multiple numbers - dropdown -->
		<div class="watso-chat-button watso-multiple-numbers"
			 role="button"
			 tabindex="0"
		     <?php if ($has_title): ?>aria-label="<?php echo esc_attr($button_title); ?>"<?php else: ?>aria-label="<?php esc_html_e('WhatsApp Contact', 'watso-basic-chat'); ?>"<?php endif; ?>
			 aria-expanded="false"
			 aria-haspopup="true"
		     <?php if ($has_title): ?>title="<?php echo esc_attr($button_title); ?>"<?php endif; ?>
			 style="border-radius: <?php echo esc_attr($button_radius); ?>px; background-color: <?php echo esc_attr($button_color); ?>;">
			<img src="<?php echo esc_url($icon_url); ?>"
				 alt="WhatsApp"
				 class="watso-icon">
			<?php if ($has_title): ?>
				<span class="watso-button-text"><?php echo esc_html($button_title); ?></span>
			<?php endif; ?>
		</div>

		<div class="watso-dropdown-menu"
			 role="menu"
			 aria-label="<?php esc_html_e('WhatsApp contact options', 'watso-basic-chat'); ?>">
			<div class="watso-dropdown-header">
				<span><?php echo esc_html($dropdown_header_text); ?></span>
				<button class="watso-close-dropdown"
						type="button"
						aria-label="<?php esc_html_e('Close', 'watso-basic-chat'); ?>">&times;</button>
			</div>
			<div class="watso-dropdown-content">
				<?php foreach ($active_numbers as $index => $number): ?>
					<?php $whatsapp_url = $watso_instance->generate_whatsapp_url($number['number'], $settings, $index); ?>
					<a href="<?php echo esc_url($whatsapp_url); ?>"
					   class="watso-dropdown-item"
					   role="menuitem"
					   target="_blank"
					   rel="noopener noreferrer"
					   data-number="<?php echo esc_attr($number['number']); ?>"
					   title="<?php echo esc_attr($number['title'] . (!empty($number['department']) ? ' - ' . $number['department'] : '')); ?>">
						<div class="watso-contact-info">
							<div class="watso-contact-avatar" style="background: <?php echo esc_attr($button_color); ?>;">
								<img src="<?php echo esc_url($icon_url); ?>" alt="WhatsApp">
							</div>
							<div class="watso-contact-details">
								<div class="watso-contact-name"><?php echo esc_html($number['title']); ?></div>
								<?php if (!empty($number['department'])): ?>
									<div class="watso-contact-department"><?php echo esc_html($number['department']); ?></div>
								<?php endif; ?>
								<?php if (!empty($number['short_description'])): ?>
									<div class="watso-contact-description"><?php echo esc_html($number['short_description']); ?></div>
								<?php endif; ?>
							</div>
						</div>
						<div class="watso-contact-status">
							<span class="watso-online-indicator" aria-hidden="true"></span>
							<span class="watso-status-text"><?php echo esc_html($online_status_text); ?></span>
						</div>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>
</div>
