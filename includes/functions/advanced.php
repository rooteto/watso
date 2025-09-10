<?php
/**
 * Advanced Settings Functions
 */

if (!defined('ABSPATH')) {
	exit;
}

function watso_init_advanced_settings() {
	// WordPress settings API initialization for advanced
	// Currently using custom handling, may implement WordPress Settings API in future versions
}

function watso_render_advanced_tab($settings) {
	?>
	<div class="watso-card">
		<h3><?php esc_html_e('Advanced Settings', 'watso-basic-chat'); ?></h3>
		<div class="watso-form-grid">
			<div class="watso-form-field">
				<label><?php esc_html_e('Debug Mode', 'watso-basic-chat'); ?></label>
				<label class="watso-toggle">
					<input type="checkbox" name="watso_settings[debug_mode]" value="1" <?php checked($settings['debug_mode']); ?>>
					<span class="watso-toggle-slider"></span>
				</label>
				<p class="watso-description"><?php esc_html_e('Enable debug logs in browser console and PHP error log.', 'watso-basic-chat'); ?></p>
			</div>

			<div class="watso-form-field">
				<label><?php esc_html_e('Database Cleanup', 'watso-basic-chat'); ?></label>
				<label class="watso-toggle">
					<input type="checkbox" name="watso_settings[clean_on_uninstall]" value="1" <?php checked($settings['clean_on_uninstall']); ?>>
					<span class="watso-toggle-slider"></span>
				</label>
				<p class="watso-description"><?php esc_html_e('When this option is active, all settings and uploaded files are deleted when the plugin is uninstalled. Useful for testing or starting fresh.', 'watso-basic-chat'); ?></p>
			</div>

			<div class="watso-form-field">
				<label><?php esc_html_e('Version Information', 'watso-basic-chat'); ?></label>
				<input type="text" value="<?php echo esc_attr(WATSO_VERSION); ?>" readonly class="watso-input">
				<p class="watso-description"><?php esc_html_e('Current plugin version.', 'watso-basic-chat'); ?></p>
			</div>
		</div>
	</div>

	<div class="watso-card">
		<h3><?php esc_html_e('Support & Information', 'watso-basic-chat'); ?></h3>
		<div class="watso-info-box">
			<h4><?php esc_html_e('About Plugin', 'watso-basic-chat'); ?></h4>
			<p><?php esc_html_e('Watso Chat plugin provides professional customer support through instant messaging capabilities.', 'watso-basic-chat'); ?></p>
			<p>
				<strong><?php esc_html_e('Support:', 'watso-basic-chat'); ?></strong>
				<a href="https://www.hosteva.com/?utm_source=wp_plugin&utm_medium=admin_panel&utm_campaign=basic_chat&utm_content=support_link" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e('Visit Hosteva.com', 'watso-basic-chat'); ?>
				</a>
			</p>
			<p>
				<strong><?php esc_html_e('Version:', 'watso-basic-chat'); ?></strong>
				<?php echo esc_html(WATSO_VERSION); ?>
			</p>
			<p>
				<strong><?php esc_html_e('Documentation:', 'watso-basic-chat'); ?></strong>
				<a href="https://www.hosteva.com/plugins/watso-basic-chat/?utm_source=wp_plugin&utm_medium=admin_panel&utm_campaign=basic_chat&utm_content=documentation_link" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e('Watso Documentation', 'watso-basic-chat'); ?>
				</a>
			</p>
		</div>
	</div>

	<?php if (defined('WP_DEBUG') && WP_DEBUG && isset($settings['debug_mode']) && $settings['debug_mode']): ?>
	<div class="watso-card">
		<h3><?php esc_html_e('Debug Information', 'watso-basic-chat'); ?></h3>
		<div class="watso-info-box">
			<h4><?php esc_html_e('System Info', 'watso-basic-chat'); ?></h4>
			<p><strong><?php esc_html_e('WordPress Version:', 'watso-basic-chat'); ?></strong> <?php echo esc_html(get_bloginfo('version')); ?></p>
			<p><strong><?php esc_html_e('PHP Version:', 'watso-basic-chat'); ?></strong> <?php echo esc_html(PHP_VERSION); ?></p>
			<p><strong><?php esc_html_e('Plugin Version:', 'watso-basic-chat'); ?></strong> <?php echo esc_html(WATSO_VERSION); ?></p>
			<p><strong><?php esc_html_e('Active Theme:', 'watso-basic-chat'); ?></strong> <?php echo esc_html(wp_get_theme()->get('Name')); ?></p>

			<?php
			$upload_dir = wp_upload_dir();
			$debug_log = $upload_dir['basedir'] . '/watso-debug.log';
			if (file_exists($debug_log)):
				?>
				<p><strong><?php esc_html_e('Debug Log:', 'watso-basic-chat'); ?></strong>
					<?php
					/* translators: %s is the file size of the debug log */
					printf(esc_html__('Log file exists (%s)', 'watso-basic-chat'), esc_html(size_format(filesize($debug_log))));
					?>
				</p>
			<?php endif; ?>
		</div>
	</div>
	<?php endif; ?>
	<?php
}
