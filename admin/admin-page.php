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
		<div class="watso-header-content" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
			<div style="display: flex; align-items: center; gap: 15px;">
				<div class="watso-logo">
					<span class="dashicons dashicons-format-chat" style="font-size: 40px; width: 40px; height: 40px; color: #25D366;"></span>
				</div>
				<div class="watso-header-text">
					<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
					<p><?php esc_html_e('WhatsApp chat button for instant customer support', 'watso-basic-chat'); ?></p>
				</div>
			</div>
			<div class="watso-header-utilities" style="display: flex; align-items: center; gap: 15px;">
				<span class="watso-version-badge" style="background: rgba(37, 211, 102, 0.1); color: #128C7E; font-weight: 600; padding: 4px 10px; border-radius: 20px; font-size: 12px; cursor: pointer;" title="<?php esc_attr_e('Show What\'s New', 'watso-basic-chat'); ?>">
					v<?php echo esc_html(WATSO_VERSION); ?>
				</span>
				<div class="watso-notification-trigger" style="position: relative; cursor: pointer; font-size: 20px; color: #64748b; transition: color 0.2s;" title="<?php esc_attr_e('What\'s New & Support', 'watso-basic-chat'); ?>">
					<i class="fas fa-bell"></i>
					<span class="watso-notification-badge" style="position: absolute; top: -5px; right: -5px; width: 8px; height: 8px; background: #ef4444; border-radius: 50%;"></span>
				</div>
			</div>
		</div>
	</div>

	<div class="watso-tabs-container">


		<?php
		// Dinamik asset sistemi
		$hosteva_assets = array(
			'domain' => array(
				'image' => 'asset-v1.png',
				'url' => 'https://www.hosteva.com/domain?utm_campaign=watso_basic_chat'
			)
		);

		// Rastgele asset seç
		$asset_keys = array_keys($hosteva_assets);
		$random_asset_key = $asset_keys[array_rand($asset_keys)];
		$selected_asset = $hosteva_assets[$random_asset_key];

		// Debug asset using our debug function
		if (isset($settings['debug_mode']) && $settings['debug_mode']) {
			$watso_instance = WatsoWhatsAppChat::get_instance();
			$watso_instance->debug_log('Asset displayed', array(
				'asset_key' => $random_asset_key,
				'asset_url' => $selected_asset['url']
			));
		}
		?>

		<div class="watso-hero-asset-wrap">
			<div class="watso-asset-box">
				<a href="<?php echo esc_url($selected_asset['url']); ?>"
				   target="_blank" rel="noopener noreferrer"
				   class="watso-asset-link">
					<img src="<?php echo esc_url(WATSO_PLUGIN_URL . 'assets/images/' . $selected_asset['image']); ?>" class="watso-asset-media">
				</a>
			</div>

		</div>


		<h2 class="nav-tab-wrapper">
			<a href="#analytics" class="nav-tab nav-tab-active watso-tab" data-tab="analytics">
				<i class="fas fa-chart-bar watso-icon-analytics" style="color: #4CAF50;"></i> <?php esc_html_e('Analytics', 'watso-basic-chat'); ?>
			</a>
			<a href="#general" class="nav-tab watso-tab" data-tab="general">
				<i class="fas fa-cog watso-icon-general"></i> <?php esc_html_e('Settings', 'watso-basic-chat'); ?>
			</a>
			<a href="#appearance" class="nav-tab watso-tab" data-tab="appearance">
				<i class="fas fa-palette watso-icon-appearance"></i> <?php esc_html_e('Appearance', 'watso-basic-chat'); ?>
			</a>
			<a href="#numbers" class="nav-tab watso-tab" data-tab="numbers">
				<i class="fas fa-phone-alt watso-icon-numbers"></i> <?php esc_html_e('Numbers', 'watso-basic-chat'); ?>
			</a>
			<a href="#tracking" class="nav-tab watso-tab" data-tab="tracking">
				<i class="fas fa-chart-line watso-icon-tracking"></i> <?php esc_html_e('Tracking', 'watso-basic-chat'); ?>
			</a>
			<a href="#schedule" class="nav-tab watso-tab" data-tab="schedule">
				<i class="fas fa-clock watso-icon-schedule"></i> <?php esc_html_e('Schedule', 'watso-basic-chat'); ?>
			</a>
			<a href="#woocommerce" class="nav-tab watso-tab" data-tab="woocommerce">
				<i class="fas fa-shopping-cart watso-icon-woo"></i> <?php esc_html_e('Woo', 'watso-basic-chat'); ?>
			</a>
			<a href="#advanced" class="nav-tab watso-tab" data-tab="advanced">
				<i class="fas fa-flask watso-icon-advanced"></i> <?php esc_html_e('Advanced', 'watso-basic-chat'); ?>
			</a>
		</h2>

		<form method="post" action="options.php" id="watso-settings-form">
			<?php settings_fields('watso_settings_group'); ?>

			<div id="watso-tab-content" class="watso-tab-content">
				<!-- Analytics Tab -->
				<div id="tab-analytics" class="watso-tab-panel active">
					<?php watso_render_analytics_tab($settings); ?>
				</div>

				<!-- General Tab -->
				<div id="tab-general" class="watso-tab-panel">
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

				<!-- WooCommerce Tab -->
				<div id="tab-woocommerce" class="watso-tab-panel">
					<?php watso_render_woocommerce_tab($settings); ?>
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

	<!-- Slide-out Sidebar Panel -->
	<div class="watso-overlay" id="watso-sidebar-overlay"></div>
	<div class="watso-sidebar-panel" id="watso-sidebar-panel">
		<div class="watso-sidebar-header">
			<h3><i class="fas fa-bullhorn" style="color: #25D366; margin-right: 8px;"></i><?php esc_html_e('What\'s New & Support', 'watso-basic-chat'); ?></h3>
			<button type="button" class="watso-sidebar-close" id="watso-sidebar-close">&times;</button>
		</div>
		<div class="watso-sidebar-body">
			
			<!-- Changelog Timeline Section -->
			<div class="watso-sidebar-section">
				<h4 class="watso-sidebar-section-title"><?php esc_html_e('Recent Updates', 'watso-basic-chat'); ?></h4>
				<div class="watso-timeline">
					<div class="watso-timeline-item">
						<span class="watso-timeline-badge"><?php esc_html_e('v1.3.0', 'watso-basic-chat'); ?></span>
						<span class="watso-timeline-date"><?php esc_html_e('September 2026', 'watso-basic-chat'); ?></span>
						<ul class="watso-timeline-list">
							<li><strong><?php esc_html_e('Added:', 'watso-basic-chat'); ?></strong> <?php esc_html_e('Instant AJAX date filtering for analytics dashboard (no page reload).', 'watso-basic-chat'); ?></li>
							<li><strong><?php esc_html_e('Added:', 'watso-basic-chat'); ?></strong> <?php esc_html_e('Smart period comparisons with real-time growth rate badges.', 'watso-basic-chat'); ?></li>
							<li><strong><?php esc_html_e('Added:', 'watso-basic-chat'); ?></strong> <?php esc_html_e('Historical monthly timeline aggregation in All-Time chart view.', 'watso-basic-chat'); ?></li>
							<li><strong><?php esc_html_e('Updated:', 'watso-basic-chat'); ?></strong> <?php esc_html_e('WordPress 7.1 core compatibility and verification.', 'watso-basic-chat'); ?></li>
						</ul>
					</div>
					<div class="watso-timeline-item">
						<span class="watso-timeline-badge watso-badge-secondary"><?php esc_html_e('v1.2.0', 'watso-basic-chat'); ?></span>
						<span class="watso-timeline-date"><?php esc_html_e('May 2026', 'watso-basic-chat'); ?></span>
						<ul class="watso-timeline-list">
							<li><strong><?php esc_html_e('Added:', 'watso-basic-chat'); ?></strong> <?php esc_html_e('Built-in Analytics Dashboard (SVG charts, agent clicks and device stats).', 'watso-basic-chat'); ?></li>
							<li><strong>Code Quality:</strong> <?php esc_html_e('Performance-friendly, lightweight database logging infrastructure with zero extra external libraries.', 'watso-basic-chat'); ?></li>
							<li><strong><?php esc_html_e('Added:', 'watso-basic-chat'); ?></strong> <?php esc_html_e('Feature Request Submission Panel.', 'watso-basic-chat'); ?></li>
							<li><strong><?php esc_html_e('Updated:', 'watso-basic-chat'); ?></strong> <?php esc_html_e('WordPress 7.0 compatibility updates and plugin testing.', 'watso-basic-chat'); ?></li>
						</ul>
					</div>
					<div class="watso-timeline-item">
						<span class="watso-timeline-badge watso-badge-secondary"><?php esc_html_e('v1.1.0', 'watso-basic-chat'); ?></span>
						<ul class="watso-timeline-list">
							<li><strong><?php esc_html_e('Added:', 'watso-basic-chat'); ?></strong> <?php esc_html_e('WooCommerce Integration (Cart, product and order pages).', 'watso-basic-chat'); ?></li>
							<li><strong><?php esc_html_e('Added:', 'watso-basic-chat'); ?></strong> <?php esc_html_e('Arabic language support and RTL layout compatibility.', 'watso-basic-chat'); ?></li>
						</ul>
					</div>
					<div class="watso-timeline-item">
						<span class="watso-timeline-badge watso-badge-secondary"><?php esc_html_e('v1.0.6', 'watso-basic-chat'); ?></span>
						<ul class="watso-timeline-list">
							<li><strong><?php esc_html_e('Added:', 'watso-basic-chat'); ?></strong> <?php esc_html_e('German language support and admin settings shortcuts.', 'watso-basic-chat'); ?></li>
						</ul>
					</div>
				</div>
			</div>

			<!-- Request Feature Section -->
			<div class="watso-sidebar-section feedback-section">
				<h4 class="watso-sidebar-section-title"><?php esc_html_e('Submit Feature Request', 'watso-basic-chat'); ?></h4>
				<p class="watso-sidebar-section-desc"><?php esc_html_e('Do you want a new feature for Watso Basic Chat? Send us your request via email, and we\'ll add it for you.', 'watso-basic-chat'); ?></p>
				
				<a href="mailto:watso@hosteva.com?subject=<?php echo rawurlencode('Watso Chat - Feature Request'); ?>" class="watso-btn watso-btn-primary watso-btn-full" style="text-decoration: none; text-align: center; display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
					<i class="fas fa-envelope"></i> <?php esc_html_e('Submit Request via Email', 'watso-basic-chat'); ?>
				</a>
			</div>

		</div>
	</div>

	<!-- Live Preview - Using Unified Renderer -->
	<?php
	// Use unified renderer - admin preview mode
	WatsoButtonRenderer::render_button($settings, true);
	?>
</div>
