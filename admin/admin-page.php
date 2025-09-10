<?php
// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

// Get settings with defaults
$watso_instance = WatsoWhatsAppChat::get_instance();
$settings = $watso_instance->get_settings();

// Get icon URL for preview
$preview_icon_url = WATSO_PLUGIN_URL . 'assets/images/whatsapp-default.png';
if (!empty($settings['custom_icon'])) {
	$preview_icon_url = $settings['custom_icon'];
}

// Get button title for preview - properly check for empty
$preview_title = isset($settings['button_title']) ? trim($settings['button_title']) : '';
$has_title = !empty($preview_title);
?>

<div class="wrap watso-admin-wrap">
	<div class="watso-header">
		<div class="watso-header-content">
			<div class="watso-logo">
				<span class="dashicons dashicons-format-chat" style="font-size: 40px; width: 40px; height: 40px; color: #25D366;"></span>
			</div>
			<div class="watso-header-text">
				<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
				<p><?php esc_html_e('WhatsApp chat button for instant customer support', 'watso-basic-chat'); ?></p>
			</div>
		</div>
	</div>

	<div class="watso-tabs-container">


		<?php
		// Dinamik banner sistemi
		$hosteva_banners = array(
			'domain' => array(
				'image' => 'hosteva-domain-728x90.png',
				'url' => 'https://www.hosteva.com/domain?utm_campaign=watso_basic_chat'
			)
		);

		// Rastgele banner seç
		$banner_keys = array_keys($hosteva_banners);
		$random_banner_key = $banner_keys[array_rand($banner_keys)];
		$selected_banner = $hosteva_banners[$random_banner_key];

		// Debug banner using our debug function
		if (isset($settings['debug_mode']) && $settings['debug_mode']) {
			$watso_instance = WatsoWhatsAppChat::get_instance();
			$watso_instance->debug_log('Banner displayed', array(
				'banner_key' => $random_banner_key,
				'banner_url' => $selected_banner['url']
			));
		}
		?>

		<div class="watso-hosteva-banner-container">
			<div class="watso-banner-size">
				<a href="<?php echo esc_url($selected_banner['url']); ?>"
				   target="_blank" rel="noopener noreferrer"
				   class="watso-banner-link">
					<img src="<?php echo esc_url(WATSO_PLUGIN_URL . 'assets/images/' . $selected_banner['image']); ?>" class="watso-banner-img">
				</a>
			</div>

		</div>


		<h2 class="nav-tab-wrapper">
			<a href="#general" class="nav-tab nav-tab-active watso-tab" data-tab="general">
				🛠️ <?php esc_html_e('General Settings', 'watso-basic-chat'); ?>
			</a>
			<a href="#appearance" class="nav-tab watso-tab" data-tab="appearance">
				🎨 <?php esc_html_e('Appearance & Colors', 'watso-basic-chat'); ?>
			</a>
			<a href="#numbers" class="nav-tab watso-tab" data-tab="numbers">
				📞 <?php esc_html_e('Numbers', 'watso-basic-chat'); ?>
			</a>
			<a href="#tracking" class="nav-tab watso-tab" data-tab="tracking">
				📈 <?php esc_html_e('Tracking', 'watso-basic-chat'); ?>
			</a>
			<a href="#schedule" class="nav-tab watso-tab" data-tab="schedule">
				⏱️ <?php esc_html_e('Schedule', 'watso-basic-chat'); ?>
			</a>
			<a href="#advanced" class="nav-tab watso-tab" data-tab="advanced">
				🧪 <?php esc_html_e('Advanced', 'watso-basic-chat'); ?>
			</a>
		</h2>

		<form method="post" action="options.php" id="watso-settings-form">
			<?php settings_fields('watso_settings_group'); ?>

			<div id="watso-tab-content" class="watso-tab-content">
				<!-- General Tab -->
				<div id="tab-general" class="watso-tab-panel active">
					<?php watso_render_general_tab($settings); ?>
				</div>

				<!-- Appearance Tab -->
				<div id="tab-appearance" class="watso-tab-panel">
					<?php watso_render_appearance_tab($settings); ?>
				</div>

				<!-- Numbers Tab -->
				<div id="tab-numbers" class="watso-tab-panel">
					<?php watso_render_numbers_tab($settings); ?>
				</div>

				<!-- Tracking Tab -->
				<div id="tab-tracking" class="watso-tab-panel">
					<?php watso_render_tracking_tab($settings); ?>
				</div>

				<!-- Schedule Tab -->
				<div id="tab-schedule" class="watso-tab-panel">
					<?php watso_render_schedule_tab($settings); ?>
				</div>

				<!-- Advanced Tab -->
				<div id="tab-advanced" class="watso-tab-panel">
					<?php watso_render_advanced_tab($settings); ?>
				</div>
			</div>

			<div class="watso-form-footer">
				<?php submit_button(__('Save Settings', 'watso-basic-chat'), 'primary', 'submit', false, array('class' => 'watso-btn watso-btn-primary watso-btn-large')); ?>
			</div>
		</form>
	</div>

	<!-- Live Preview - Using Unified Renderer -->
	<?php
	// Use unified renderer - admin preview mode
	WatsoButtonRenderer::render_button($settings, true);
	?>
</div>
