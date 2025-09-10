<?php
/**
 * Unified Chat Button Renderer - Improved Version
 * New fields: Department, Individual status text, Short description
 * Separate colors for each field
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

class WatsoButtonRenderer {

	public static function render_button($settings, $is_preview = false) {
		$active_numbers = self::get_active_numbers($settings);

		// Debug
		if (isset($settings['debug_mode']) && $settings['debug_mode'] && !$is_preview) {
			echo '<!-- Watso: Found ' . count($active_numbers) . ' active numbers -->';
		}

		// If no active numbers, show nothing
		if (empty($active_numbers)) {
			if (isset($settings['debug_mode']) && $settings['debug_mode'] && !$is_preview) {
				echo '<!-- Watso: No active numbers found -->';
			}
			return;
		}

		// Button settings
		$button_title = isset($settings['button_title']) ? trim($settings['button_title']) : '';
		$has_title = !empty($button_title);

		// Dropdown header text
		$dropdown_header_text = isset($settings['dropdown_header_text']) ? trim($settings['dropdown_header_text']) : esc_attr__('Select a contact', 'watso-basic-chat');

		// Colors
		$button_radius = isset($settings['button_radius']) && is_numeric($settings['button_radius']) ? (int)$settings['button_radius'] : 15;
		$button_color = isset($settings['button_color']) ? $settings['button_color'] : '#119849';
		$status_color = isset($settings['status_text_color']) ? $settings['status_text_color'] : '#4CAF50';
		$department_color = isset($settings['department_color']) ? $settings['department_color'] : '#666666';
		$description_color = isset($settings['short_description_color']) ? $settings['short_description_color'] : '#888888';

		// Icon URL
		$icon_url = WATSO_PLUGIN_URL . 'assets/images/whatsapp-default.png';
		if (!empty($settings['custom_icon'])) {
			$icon_url = $settings['custom_icon'];
		}

		// Container class and ID
		$container_id = $is_preview ? 'watso-preview-button-container' : 'watso-chat-widget';
		$container_class = $is_preview ? 'watso-preview-container' : 'watso-chat-widget watso-' . $settings['position'];

		// Create inline style
		$button_inline_style = sprintf(
			'border-radius: %dpx !important; background-color: %s !important;',
			$button_radius,
			esc_attr($button_color)
		);

		// Avatar background style
		$avatar_style = sprintf('background: %s !important;', esc_attr($button_color));
		?>

		<div id="<?php echo esc_attr($container_id); ?>" class="<?php echo esc_attr($container_class); ?>"
			<?php if (!$is_preview): ?>
				data-position="<?php echo esc_attr($settings['position']); ?>"
				data-color="<?php echo esc_attr($button_color); ?>"
				data-radius="<?php echo esc_attr($button_radius); ?>"
			<?php endif; ?>>

			<?php if (count($active_numbers) === 1): ?>
				<!-- SINGLE NUMBER - DIRECT LINK -->
				<?php
				$number = $active_numbers[0];
				$whatsapp_url = $is_preview ? '#' : WatsoWhatsAppChat::get_instance()->generate_whatsapp_url($number['number'], $settings, 0);
				?>
				<a href="<?php echo esc_url($whatsapp_url); ?>"
				   class="watso-chat-button watso-single-number"
				   <?php if (!$is_preview): ?>target="_blank" rel="noopener noreferrer" data-number="<?php echo esc_attr($number['number']); ?>"<?php endif; ?>
				   <?php if ($has_title): ?>title="<?php echo esc_attr($button_title); ?>"<?php endif; ?>
				   style="<?php echo esc_attr($button_inline_style); ?>">

					<img src="<?php echo esc_url($icon_url); ?>" alt="WhatsApp" class="watso-icon">

					<?php if ($has_title): ?>
						<span class="watso-button-text"><?php echo esc_html($button_title); ?></span>
					<?php endif; ?>
				</a>

			<?php else: ?>
				<!-- MULTIPLE NUMBERS - DROPDOWN -->
				<div class="watso-chat-button watso-multiple-numbers"
				     <?php if (!$is_preview): ?>role="button" tabindex="0" aria-label="<?php echo $has_title ? esc_attr($button_title) : esc_attr__('WhatsApp Contact', 'watso-basic-chat'); ?>" aria-expanded="false" aria-haspopup="true"<?php endif; ?>
				     <?php if ($has_title): ?>title="<?php echo esc_attr($button_title); ?>"<?php endif; ?>
					 style="<?php echo esc_attr($button_inline_style); ?>">

					<img src="<?php echo esc_url($icon_url); ?>" alt="WhatsApp" class="watso-icon">

					<?php if ($has_title): ?>
						<span class="watso-button-text"><?php echo esc_html($button_title); ?></span>
					<?php endif; ?>
				</div>

				<!-- DROPDOWN MENU -->
				<div class="watso-dropdown-menu<?php echo $is_preview ? ' watso-preview-dropdown' : ''; ?>" role="menu" aria-label="<?php esc_html_e('WhatsApp contact options', 'watso-basic-chat'); ?>">
					<div class="watso-dropdown-header">
						<span><?php echo esc_html($dropdown_header_text); ?></span>
						<button class="watso-close-dropdown" type="button" aria-label="<?php esc_html_e('Close', 'watso-basic-chat'); ?>">&times;</button>
					</div>
					<div class="watso-dropdown-content">
						<?php foreach ($active_numbers as $index => $number): ?>
							<?php
							$whatsapp_url = $is_preview ? '#' : WatsoWhatsAppChat::get_instance()->generate_whatsapp_url($number['number'], $settings, $index);

							// Contact information
							$person_name = !empty($number['title']) ? $number['title'] : esc_attr__('Unnamed', 'watso-basic-chat');
							$department = !empty($number['department']) ? $number['department'] : '';
							$status_text = !empty($number['status_text']) ? $number['status_text'] : '';
							$short_description = !empty($number['short_description']) ? $number['short_description'] : '';
							?>
							<a href="<?php echo esc_url($whatsapp_url); ?>"
							   class="watso-dropdown-item"
							   role="menuitem"
							   <?php if (!$is_preview): ?>target="_blank" rel="noopener noreferrer" data-number="<?php echo esc_attr($number['number']); ?>"<?php endif; ?>
							   title="<?php echo esc_attr($person_name . ($department ? ' - ' . $department : '')); ?>"
							   <?php if ($is_preview): ?>onclick="return false;"<?php endif; ?>>

								<div class="watso-contact-info">
									<div class="watso-contact-avatar" style="<?php echo esc_attr($avatar_style); ?>">
										<img src="<?php echo esc_url($icon_url); ?>" alt="WhatsApp">
									</div>
									<div class="watso-contact-details">
										<!-- Contact Name (Main Title) -->
										<div class="watso-contact-name"><?php echo esc_html($person_name); ?></div>

										<!-- Department (Subtitle) -->
										<?php if (!empty($department)): ?>
											<div class="watso-contact-department" style="color: <?php echo esc_attr($department_color); ?>;">
												<?php echo esc_html($department); ?>
											</div>
										<?php endif; ?>

										<!-- Short Description (Under Department) -->
										<?php if (!empty($short_description)): ?>
											<div class="watso-contact-description" style="color: <?php echo esc_attr($description_color); ?>;">
												<?php echo esc_html($short_description); ?>
											</div>
										<?php endif; ?>
									</div>
								</div>

								<!-- Right Side: Status -->
								<?php if (!empty($status_text)): ?>
									<div class="watso-contact-status">
										<span class="watso-online-indicator" style="background: <?php echo esc_attr($status_color); ?>;" aria-hidden="true"></span>
										<span class="watso-status-text" style="color: <?php echo esc_attr($status_color); ?>;">
											<?php echo esc_html($status_text); ?>
										</span>
									</div>
								<?php endif; ?>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>

		<?php
		// Inject additional CSS for preview
		if ($is_preview) {
			?>
			<style id="watso-preview-dynamic-css">
				/* Special CSS for preview - force with !important */
				#<?php echo esc_attr($container_id); ?> .watso-chat-button {
					border-radius: <?php echo esc_attr($button_radius); ?>px !important;
					background-color: <?php echo esc_attr($button_color); ?> !important;
				}

				#<?php echo esc_attr($container_id); ?> .watso-contact-avatar {
					                                        background: <?php echo esc_attr($button_color); ?> !important;
				                                        }

				/* For hover effect */
				#<?php echo esc_attr($container_id); ?> .watso-chat-button:hover {
					                                        background-color: <?php echo esc_attr(self::adjustBrightness($button_color, -20)); ?> !important;
				                                        }

				/* Colors for new fields */
				#<?php echo esc_attr($container_id); ?> .watso-contact-department {
					                                        color: <?php echo esc_attr($department_color); ?> !important;
				                                        }

				#<?php echo esc_attr($container_id); ?> .watso-contact-description {
					                                        color: <?php echo esc_attr($description_color); ?> !important;
				                                        }

				#<?php echo esc_attr($container_id); ?> .watso-status-text {
					                                        color: <?php echo esc_attr($status_color); ?> !important;
				                                        }

				#<?php echo esc_attr($container_id); ?> .watso-online-indicator {
					                                        background: <?php echo esc_attr($status_color); ?> !important;
				                                        }
			</style>
			<?php
		}
	}

	private static function get_active_numbers($settings) {
		$active_numbers = array();

		if (isset($settings['numbers']) && is_array($settings['numbers'])) {
			foreach ($settings['numbers'] as $number) {
				if (!empty($number['number']) && (!isset($number['active']) || $number['active'])) {
					$active_numbers[] = $number;
				}
			}
		}

		return $active_numbers;
	}

	// Color darkening function
	private static function adjustBrightness($hex, $percent) {
		// Remove # if present
		$hex = str_replace('#', '', $hex);

		// Parse RGB
		$num = hexdec($hex);
		$amt = round(2.55 * $percent);
		$R = ($num >> 16) + $amt;
		$G = ($num >> 8 & 0x00FF) + $amt;
		$B = ($num & 0x0000FF) + $amt;

		// Ensure values are within 0-255 range
		$newR = max(0, min(255, $R));
		$newG = max(0, min(255, $G));
		$newB = max(0, min(255, $B));

		return '#' . sprintf('%02x%02x%02x', $newR, $newG, $newB);
	}
}
