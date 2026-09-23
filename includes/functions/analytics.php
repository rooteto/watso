<?php
/**
 * Analytics Dashboard Functions
 */

if (!defined('ABSPATH')) {
	exit;
}

function watso_render_analytics_tab($settings) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'watso_clicks';
	
	// Check if table exists
	$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
	
	if (!$table_exists) {
		?>
		<div class="watso-card">
			<h3><?php esc_html_e('Analytics Dashboard', 'watso-basic-chat'); ?></h3>
			<div class="watso-no-data" style="text-align: center; padding: 50px 20px;">
				<span class="dashicons dashicons-chart-bar" style="font-size: 64px; width: 64px; height: 64px; color: #ccc;"></span>
				<h4><?php esc_html_e('Analytics system is initializing...', 'watso-basic-chat'); ?></h4>
				<p><?php esc_html_e('Please refresh the page to view click statistics.', 'watso-basic-chat'); ?></p>
			</div>
		</div>
		<?php
		return;
	}

	// Fetch Stats
	$total_clicks = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
	$last_30_clicks = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE click_time >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
	$today_clicks = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE click_time >= CURDATE()");
	
	// Check if we have any clicks
	if ($total_clicks === 0) {
		?>
		<div class="watso-card">
			<h3><?php esc_html_e('Analytics Dashboard', 'watso-basic-chat'); ?></h3>
			<div class="watso-no-data" style="text-align: center; padding: 50px 20px;">
				<span class="dashicons dashicons-chart-bar" style="font-size: 64px; width: 64px; height: 64px; color: #cbd5e1; margin-bottom: 20px; display: inline-block;"></span>
				<h4 style="font-size: 18px; color: #334155; margin: 0 0 10px 0; font-weight: 600;"><?php esc_html_e('No Click Data Available Yet', 'watso-basic-chat'); ?></h4>
				<p style="color: #64748b; font-size: 14px; max-width: 450px; margin: 0 auto; line-height: 1.5;"><?php esc_html_e('Once your visitors click the WhatsApp button, statistics about clicked numbers, pages, and devices will start displaying here in real time.', 'watso-basic-chat'); ?></p>
			</div>
		</div>
		<?php
		return;
	}

	// 30 Days daily trend
	$days = array();
	for ($i = 29; $i >= 0; $i--) {
		$date = date('Y-m-d', strtotime("-$i days"));
		$days[$date] = 0;
	}
	
	$trend_results = $wpdb->get_results("
		SELECT DATE(click_time) as click_date, COUNT(*) as click_count 
		FROM $table_name 
		WHERE click_time >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
		GROUP BY click_date
	");
	
	foreach ($trend_results as $row) {
		if (isset($days[$row->click_date])) {
			$days[$row->click_date] = (int)$row->click_count;
		}
	}
	
	$max_clicks = max($days);
	if ($max_clicks == 0) {
		$max_clicks = 10;
	}

	$width = 800;
	$height = 200;
	$padding = 20;
	$chart_w = $width - ($padding * 2);
	$chart_h = $height - ($padding * 2);
	$x_step = $chart_w / (count($days) - 1);
	
	$points = array();
	$index = 0;
	foreach ($days as $date => $count) {
		$x = $padding + ($index * $x_step);
		$y = $padding + $chart_h - (($count / $max_clicks) * $chart_h);
		$points[] = "$x,$y";
		$index++;
	}
	$points_str = implode(' ', $points);
	$fill_points = "$padding," . ($padding + $chart_h) . " " . $points_str . " " . ($padding + $chart_w) . "," . ($padding + $chart_h);

	// Clicks by agent
	$agent_results = $wpdb->get_results("
		SELECT number, COUNT(*) as click_count 
		FROM $table_name 
		GROUP BY number 
		ORDER BY click_count DESC
	");
	
	// Map numbers to titles from settings
	$number_map = array();
	if (!empty($settings['numbers']) && is_array($settings['numbers'])) {
		foreach ($settings['numbers'] as $num_item) {
			$number_map[$num_item['number']] = $num_item['title'] . (!empty($num_item['department']) ? ' (' . $num_item['department'] . ')' : '');
		}
	}

	// Clicks by Page
	$page_results = $wpdb->get_results("
		SELECT page_url, COUNT(*) as click_count 
		FROM $table_name 
		GROUP BY page_url 
		ORDER BY click_count DESC 
		LIMIT 5
	");

	// Clicks by Device
	$device_results = $wpdb->get_results("
		SELECT device, COUNT(*) as click_count 
		FROM $table_name 
		GROUP BY device
	");
	$devices = array('desktop' => 0, 'mobile' => 0);
	foreach ($device_results as $row) {
		$devices[$row->device] = (int) $row->click_count;
	}
	$device_total = array_sum($devices);
	
	// Clicks by Hour (Busy hours)
	$hour_results = $wpdb->get_results("
		SELECT HOUR(click_time) as click_hour, COUNT(*) as click_count 
		FROM $table_name 
		GROUP BY click_hour 
		ORDER BY click_count DESC
		LIMIT 5
	");
	?>
	
	<div class="watso-card">
		<h3><?php esc_html_e('Analytics Overview', 'watso-basic-chat'); ?></h3>
		
		<!-- Stats Summary Cards -->
		<div class="watso-stats-cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px;">
			<div class="watso-stat-card" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; display: flex; align-items: center; gap: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
				<div class="watso-stat-icon" style="background: rgba(17, 152, 73, 0.1); color: #119849; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px;">
					<i class="fas fa-mouse-pointer"></i>
				</div>
				<div class="watso-stat-info">
					<span class="watso-stat-label" style="display: block; font-size: 12px; color: #64748b; font-weight: 500;"><?php esc_html_e('Total Clicks', 'watso-basic-chat'); ?></span>
					<span class="watso-stat-value" style="display: block; font-size: 24px; color: #1e293b; font-weight: 700;"><?php echo number_format_i18n($total_clicks); ?></span>
				</div>
			</div>
			
			<div class="watso-stat-card" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; display: flex; align-items: center; gap: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
				<div class="watso-stat-icon" style="background: rgba(0, 115, 170, 0.1); color: #0073aa; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px;">
					<i class="fas fa-calendar-alt"></i>
				</div>
				<div class="watso-stat-info">
					<span class="watso-stat-label" style="display: block; font-size: 12px; color: #64748b; font-weight: 500;"><?php esc_html_e('Last 30 Days', 'watso-basic-chat'); ?></span>
					<span class="watso-stat-value" style="display: block; font-size: 24px; color: #1e293b; font-weight: 700;"><?php echo number_format_i18n($last_30_clicks); ?></span>
				</div>
			</div>
			
			<div class="watso-stat-card" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; display: flex; align-items: center; gap: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
				<div class="watso-stat-icon" style="background: rgba(233, 30, 99, 0.1); color: #e91e63; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px;">
					<i class="fas fa-bolt"></i>
				</div>
				<div class="watso-stat-info">
					<span class="watso-stat-label" style="display: block; font-size: 12px; color: #64748b; font-weight: 500;"><?php esc_html_e('Today', 'watso-basic-chat'); ?></span>
					<span class="watso-stat-value" style="display: block; font-size: 24px; color: #1e293b; font-weight: 700;"><?php echo number_format_i18n($today_clicks); ?></span>
				</div>
			</div>
		</div>

		<!-- Main Trend Chart -->
		<div class="watso-chart-box" style="margin-top: 30px; background: #fafafa; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px;">
			<h4 style="margin: 0 0 15px 0; font-size: 14px; font-weight: 600; color: #475569;"><?php esc_html_e('Click Trend (Last 30 Days)', 'watso-basic-chat'); ?></h4>
			<div class="watso-svg-chart-container" style="position: relative;">
				<svg viewBox="0 0 800 200" width="100%" height="200" class="watso-svg-chart" style="overflow: visible;">
					<defs>
						<linearGradient id="watso-gradient" x1="0" y1="0" x2="0" y2="1">
							<stop offset="0%" stop-color="#119849" stop-opacity="0.3"/>
							<stop offset="100%" stop-color="#119849" stop-opacity="0.0"/>
						</linearGradient>
					</defs>
					
					<!-- Grid lines -->
					<line x1="20" y1="20" x2="780" y2="20" stroke="#f1f5f9" stroke-width="1" />
					<line x1="20" y1="60" x2="780" y2="60" stroke="#f1f5f9" stroke-width="1" />
					<line x1="20" y1="100" x2="780" y2="100" stroke="#f1f5f9" stroke-width="1" />
					<line x1="20" y1="140" x2="780" y2="140" stroke="#f1f5f9" stroke-width="1" />
					<line x1="20" y1="180" x2="780" y2="180" stroke="#cbd5e1" stroke-width="1" />
					
					<!-- Area fill -->
					<polygon points="<?php echo esc_attr($fill_points); ?>" fill="url(#watso-gradient)" />
					
					<!-- Line -->
					<polyline points="<?php echo esc_attr($points_str); ?>" fill="none" stroke="#119849" stroke-width="2" />
					
					<!-- Interactive dots -->
					<?php
					$index = 0;
					foreach ($days as $date => $count):
						$x = $padding + ($index * $x_step);
						$y = $padding + $chart_h - (($count / $max_clicks) * $chart_h);
					?>
						<circle cx="<?php echo $x; ?>" cy="<?php echo $y; ?>" r="4" fill="#fff" stroke="#119849" stroke-width="2" class="watso-chart-dot" style="cursor: pointer;">
							<title><?php echo esc_html(date_i18n('d M Y', strtotime($date))) . ': ' . esc_html($count) . ' ' . esc_html__('clicks', 'watso-basic-chat'); ?></title>
						</circle>
					<?php
						$index++;
					endforeach;
					?>
				</svg>
			</div>
			<?php
			$day_keys = array_keys($days);
			$first_day = isset($day_keys[0]) ? $day_keys[0] : '';
			$mid_day = isset($day_keys[15]) ? $day_keys[15] : '';
			$last_day = !empty($day_keys) ? end($day_keys) : '';
			?>
			<!-- Date Labels -->
			<div class="watso-chart-labels" style="display: flex; justify-content: space-between; padding: 8px 10px 0 10px; font-size: 10px; color: #64748b;">
				<span><?php echo !empty($first_day) ? esc_html(date_i18n('d M', strtotime($first_day))) : ''; ?></span>
				<span><?php echo !empty($mid_day) ? esc_html(date_i18n('d M', strtotime($mid_day))) : ''; ?></span>
				<span><?php echo !empty($last_day) ? esc_html(date_i18n('d M', strtotime($last_day))) : ''; ?></span>
			</div>
		</div>
		
		<!-- Secondary Stats Grid -->
		<div class="watso-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-top: 30px;">
			
			<!-- Clicks by Agent -->
			<div class="watso-card-panel" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px;">
				<h4 style="margin: 0 0 15px 0; font-size: 14px; font-weight: 600; color: #475569; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
					<i class="fas fa-users-cog" style="color: #4caf50;"></i><?php esc_html_e('Clicks by Agent / Number', 'watso-basic-chat'); ?>
				</h4>
				<div class="watso-panel-list">
					<?php foreach ($agent_results as $row): 
						$display_name = isset($number_map[$row->number]) ? $number_map[$row->number] : $row->number;
						$pct = $total_clicks > 0 ? round(($row->click_count / $total_clicks) * 100) : 0;
					?>
						<div class="watso-panel-item" style="margin-bottom: 12px;">
							<div class="watso-item-desc" style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px; color: #334155;">
								<span style="font-weight: 600;"><?php echo esc_html($display_name); ?></span>
								<span style="color: #64748b;"><?php echo esc_html($row->click_count) . ' ' . esc_html__('clicks', 'watso-basic-chat') . ' (' . $pct . '%)'; ?></span>
							</div>
							<div class="watso-progress-bar" style="height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
								<div class="watso-progress-fill" style="width: <?php echo $pct; ?>%; height: 100%; background: #119849; border-radius: 3px;"></div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			
			<!-- Clicks by Page -->
			<div class="watso-card-panel" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px;">
				<h4 style="margin: 0 0 15px 0; font-size: 14px; font-weight: 600; color: #475569; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
					<i class="fas fa-link" style="color: #0073aa;"></i><?php esc_html_e('Top Pages (Clicks)', 'watso-basic-chat'); ?>
				</h4>
				<table class="watso-table-simple" style="width: 100%; border-collapse: collapse; font-size: 12px;">
					<tbody>
						<?php foreach ($page_results as $row): 
							$clean_url = $row->page_url;
							$path = wp_parse_url($clean_url, PHP_URL_PATH);
							if (empty($path)) {
								$path = '/';
							}
						?>
							<tr style="border-bottom: 1px solid #f1f5f9;">
								<td style="padding: 8px 0; color: #334155; font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 220px;" title="<?php echo esc_url($clean_url); ?>">
									<?php echo esc_html($path); ?>
								</td>
								<td style="padding: 8px 0; text-align: right; color: #64748b; font-weight: 600;">
									<?php echo esc_html($row->click_count); ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<!-- Device & Hour breakdown -->
			<div class="watso-card-panel" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px;">
				<h4 style="margin: 0 0 15px 0; font-size: 14px; font-weight: 600; color: #475569; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
					<i class="fas fa-mobile-alt" style="color: #e91e63;"></i><?php esc_html_e('Device & Time Breakdown', 'watso-basic-chat'); ?>
				</h4>
				<div class="watso-device-breakdown" style="margin-bottom: 15px;">
					<?php 
					$mobile_pct = $device_total > 0 ? round(($devices['mobile'] / $device_total) * 100) : 0;
					$desktop_pct = $device_total > 0 ? 100 - $mobile_pct : 0;
					?>
					<div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px; color: #334155;">
						<span><i class="fas fa-mobile-alt" style="margin-right: 4px; color: #e91e63;"></i><?php esc_html_e('Mobile', 'watso-basic-chat'); ?> (<?php echo $mobile_pct; ?>%)</span>
						<span><i class="fas fa-desktop" style="margin-right: 4px; color: #0073aa;"></i><?php esc_html_e('Desktop', 'watso-basic-chat'); ?> (<?php echo $desktop_pct; ?>%)</span>
					</div>
					<div style="height: 10px; background: #0073aa; border-radius: 5px; overflow: hidden; display: flex;">
						<div style="width: <?php echo $mobile_pct; ?>%; height: 100%; background: #e91e63;"></div>
					</div>
				</div>

				<div class="watso-busy-hours">
					<h5 style="margin: 15px 0 8px 0; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 600;"><?php esc_html_e('Peak Hours', 'watso-basic-chat'); ?></h5>
					<div style="display: flex; gap: 6px; flex-wrap: wrap;">
						<?php foreach ($hour_results as $row): ?>
							<span style="background: #f1f5f9; color: #334155; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 500;">
								<?php printf('%02d:00 (%d)', $row->click_hour, $row->click_count); ?>
							</span>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
			
		</div>
	</div>
	<?php
}
