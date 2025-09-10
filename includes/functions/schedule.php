<?php
/**
 * Schedule Settings Functions
 */

if (!defined('ABSPATH')) {
	exit;
}

function watso_init_schedule_settings() {
	// WordPress settings API initialization for schedule
}

function watso_render_schedule_tab($settings) {
	?>
	<div class="watso-card">
		<h3><?php esc_html_e('Scheduling & Visibility', 'watso-basic-chat'); ?></h3>
		<div class="watso-form-grid">
			<div class="watso-form-field watso-field-full">
				<label><?php esc_html_e('Enable Time Range', 'watso-basic-chat'); ?></label>
				<label class="watso-toggle">
					<input type="checkbox" id="watso_schedule_enabled" name="watso_settings[schedule_enabled]" value="1" <?php checked($settings['schedule_enabled']); ?>>
					<span class="watso-toggle-slider"></span>
				</label>
				<p class="watso-description"><?php esc_html_e('Show button only during specific hours. Note: Controlled via JavaScript for cache compatibility.', 'watso-basic-chat'); ?></p>
			</div>
		</div>

		<div class="watso-schedule-settings" style="<?php echo !$settings['schedule_enabled'] ? 'display:none;' : ''; ?>">
			<h4><?php esc_html_e('Working Hours', 'watso-basic-chat'); ?></h4>
			<div class="watso-schedule-grid">
				<?php
				$days = array(
					'monday' => __('Monday', 'watso-basic-chat'),
					'tuesday' => __('Tuesday', 'watso-basic-chat'),
					'wednesday' => __('Wednesday', 'watso-basic-chat'),
					'thursday' => __('Thursday', 'watso-basic-chat'),
					'friday' => __('Friday', 'watso-basic-chat'),
					'saturday' => __('Saturday', 'watso-basic-chat'),
					'sunday' => __('Sunday', 'watso-basic-chat')
				);

				foreach ($days as $day_key => $day_name):
					$day_schedule = isset($settings['schedule_hours'][$day_key]) ? $settings['schedule_hours'][$day_key] : array('start' => '09:00', 'end' => '18:00', 'enabled' => true);
					?>
					<div class="watso-schedule-day">
						<div class="watso-day-header">
							<span class="watso-day-name"><?php echo esc_html($day_name); ?></span>
							<label class="watso-toggle watso-toggle-small">
								<input type="checkbox" name="watso_settings[schedule_hours][<?php echo esc_attr($day_key); ?>][enabled]" value="1" <?php checked($day_schedule['enabled']); ?>>
								<span class="watso-toggle-slider"></span>
							</label>
						</div>
						<div class="watso-time-inputs">
							<input type="time" name="watso_settings[schedule_hours][<?php echo esc_attr($day_key); ?>][start]" value="<?php echo esc_attr($day_schedule['start']); ?>" class="watso-input">
							<span class="watso-time-separator">-</span>
							<input type="time" name="watso_settings[schedule_hours][<?php echo esc_attr($day_key); ?>][end]" value="<?php echo esc_attr($day_schedule['end']); ?>" class="watso-input">
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<h4><?php esc_html_e('Holidays', 'watso-basic-chat'); ?></h4>
			<div class="watso-holidays-container">
				<?php
				$holidays = isset($settings['holidays']) ? $settings['holidays'] : array();
				if (empty($holidays)) {
					$holidays = array(array('date' => '', 'title' => ''));
				}
				foreach ($holidays as $index => $holiday):
					?>
					<div class="watso-holiday-card">
						<div class="watso-holiday-fields">
							<input type="date" name="watso_settings[holidays][<?php echo esc_attr($index); ?>][date]" value="<?php echo esc_attr($holiday['date'] ?? ''); ?>" class="watso-input">
							<input type="text" name="watso_settings[holidays][<?php echo esc_attr($index); ?>][title]" value="<?php echo esc_attr($holiday['title'] ?? ''); ?>" placeholder="<?php esc_html_e('Holiday Name', 'watso-basic-chat'); ?>" class="watso-input">
						</div>
						<button type="button" class="watso-btn watso-btn-danger watso-btn-small watso-remove-holiday"><?php esc_html_e('Delete', 'watso-basic-chat'); ?></button>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="watso-form-actions">
				<button type="button" class="watso-btn watso-btn-secondary watso-add-holiday"><?php esc_html_e('Add Holiday', 'watso-basic-chat'); ?></button>
			</div>
		</div>
	</div>
	<?php
}
