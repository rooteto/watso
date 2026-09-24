<?php
/**
 * Analytics Dashboard Functions
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * AJAX Handler for filtering analytics data without page reload
 */
add_action('wp_ajax_watso_filter_analytics', 'watso_ajax_filter_analytics');
function watso_ajax_filter_analytics() {
	check_ajax_referer('watso_nonce', 'nonce');

	if (!current_user_can('manage_options')) {
		wp_send_json_error(array('message' => __('Unauthorized access.', 'watso-basic-chat')));
	}

	$period = isset($_POST['period']) ? sanitize_key($_POST['period']) : '30days';
	
	// Get plugin settings
	$settings = get_option('watso_settings', array());

	ob_start();
	watso_render_analytics_content($settings, $period);
	$html = ob_get_clean();

	wp_send_json_success(array('html' => $html));
}

/**
 * Main Analytics Tab Renderer
 */
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

	// Total all-time clicks check
	$total_clicks = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
	
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

	$current_period = isset($_GET['period']) ? sanitize_key($_GET['period']) : '30days';
	$allowed_periods = array('30days', 'this_month', 'last_month', '7days', 'today', 'all');
	if (!in_array($current_period, $allowed_periods, true)) {
		$current_period = '30days';
	}

	// Fallback URL with correct menu slug 'watso-settings'
	$base_url = admin_url('admin.php?page=watso-settings&tab=analytics');
	?>
	<div class="watso-card">
		<!-- Header & Period Filter Toolbar -->
		<div class="watso-analytics-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 25px;">
			<h3 style="margin: 0; font-size: 20px; font-weight: 700; color: #1e293b;"><?php esc_html_e('Analytics Overview', 'watso-basic-chat'); ?></h3>
			
			<!-- Period Filter Buttons (Handled via Instant AJAX) -->
			<div class="watso-period-filters" style="display: inline-flex; background: #f1f5f9; padding: 4px; border-radius: 8px; gap: 4px; flex-wrap: wrap;">
				<a href="<?php echo esc_url(add_query_arg('period', '30days', $base_url)); ?>" data-period="30days" class="watso-period-btn <?php echo ($current_period === '30days') ? 'active' : ''; ?>">
					<?php esc_html_e('Last 30 Days', 'watso-basic-chat'); ?>
				</a>
				<a href="<?php echo esc_url(add_query_arg('period', 'this_month', $base_url)); ?>" data-period="this_month" class="watso-period-btn <?php echo ($current_period === 'this_month') ? 'active' : ''; ?>">
					<?php esc_html_e('This Month', 'watso-basic-chat'); ?>
				</a>
				<a href="<?php echo esc_url(add_query_arg('period', 'last_month', $base_url)); ?>" data-period="last_month" class="watso-period-btn <?php echo ($current_period === 'last_month') ? 'active' : ''; ?>">
					<?php esc_html_e('Last Month', 'watso-basic-chat'); ?>
				</a>
				<a href="<?php echo esc_url(add_query_arg('period', '7days', $base_url)); ?>" data-period="7days" class="watso-period-btn <?php echo ($current_period === '7days') ? 'active' : ''; ?>">
					<?php esc_html_e('Last 7 Days', 'watso-basic-chat'); ?>
				</a>
				<a href="<?php echo esc_url(add_query_arg('period', 'today', $base_url)); ?>" data-period="today" class="watso-period-btn <?php echo ($current_period === 'today') ? 'active' : ''; ?>">
					<?php esc_html_e('Today', 'watso-basic-chat'); ?>
				</a>
				<a href="<?php echo esc_url(add_query_arg('period', 'all', $base_url)); ?>" data-period="all" class="watso-period-btn <?php echo ($current_period === 'all') ? 'active' : ''; ?>">
					<?php esc_html_e('All Time', 'watso-basic-chat'); ?>
				</a>
			</div>
		</div>

		<!-- Container that gets refreshed instantly via AJAX -->
		<div id="watso-analytics-content" class="watso-analytics-content">
			<?php watso_render_analytics_content($settings, $current_period); ?>
		</div>
	</div>
	<?php
}

/**
 * Render dynamic analytics cards, SVG chart, and breakdown lists
 */
function watso_render_analytics_content($settings, $period = '30days') {
	global $wpdb;
	$table_name = $wpdb->prefix . 'watso_clicks';

	$allowed_periods = array('30days', 'this_month', 'last_month', '7days', 'today', 'all');
	if (!in_array($period, $allowed_periods, true)) {
		$period = '30days';
	}

	$total_clicks = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
	$where_sql = '1=1';
	$chart_days = array();
	$chart_title = __('Click Trend', 'watso-basic-chat');
	$is_monthly = false;

	switch ($period) {
		case 'this_month':
			$where_sql = "click_time >= DATE_FORMAT(NOW(), '%Y-%m-01 00:00:00')";
			$chart_title = __('Click Trend (This Month)', 'watso-basic-chat');
			$current_day = (int) date('j');
			$year_month = date('Y-m');
			for ($d = 1; $d <= $current_day; $d++) {
				$date_str = sprintf('%s-%02d', $year_month, $d);
				$chart_days[$date_str] = 0;
			}
			break;

		case 'last_month':
			$where_sql = "click_time >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH), '%Y-%m-01 00:00:00') AND click_time < DATE_FORMAT(NOW(), '%Y-%m-01 00:00:00')";
			$chart_title = __('Click Trend (Last Month)', 'watso-basic-chat');
			$last_month_timestamp = strtotime('first day of previous month');
			$days_in_last_month = (int) date('t', $last_month_timestamp);
			$last_year_month = date('Y-m', $last_month_timestamp);
			for ($d = 1; $d <= $days_in_last_month; $d++) {
				$date_str = sprintf('%s-%02d', $last_year_month, $d);
				$chart_days[$date_str] = 0;
			}
			break;

		case '7days':
			$where_sql = "click_time >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)";
			$chart_title = __('Click Trend (Last 7 Days)', 'watso-basic-chat');
			for ($i = 6; $i >= 0; $i--) {
				$date_str = date('Y-m-d', strtotime("-$i days"));
				$chart_days[$date_str] = 0;
			}
			break;

		case 'today':
			$where_sql = "click_time >= CURDATE()";
			$chart_title = __('Click Trend (Today)', 'watso-basic-chat');
			for ($h = 0; $h <= 23; $h++) {
				$h_str = sprintf('%02d:00', $h);
				$chart_days[$h_str] = 0;
			}
			break;

		case 'all':
			$where_sql = '1=1';
			$chart_title = __('Click Trend (All Time)', 'watso-basic-chat');
			$is_monthly = true;
			
			// Detect the earliest click date to show a proper historical timeline
			$first_click_time = $wpdb->get_var("SELECT MIN(click_time) FROM $table_name");
			$start_month = !empty($first_click_time) ? strtotime('first day of this month', strtotime($first_click_time)) : strtotime('-5 months');
			$current_month = strtotime('first day of this month');
			
			// Ensure at least 6 months are displayed
			$min_start = strtotime('-5 months', $current_month);
			if ($start_month > $min_start) {
				$start_month = $min_start;
			}
			
			$curr = $start_month;
			while ($curr <= $current_month) {
				$ym = date('Y-m', $curr);
				$chart_days[$ym] = 0;
				$curr = strtotime('+1 month', $curr);
			}
			break;

		case '30days':
		default:
			$where_sql = "click_time >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)";
			$chart_title = __('Click Trend (Last 30 Days)', 'watso-basic-chat');
			for ($i = 29; $i >= 0; $i--) {
				$date_str = date('Y-m-d', strtotime("-$i days"));
				$chart_days[$date_str] = 0;
			}
			break;
	}

	// Filtered clicks count
	$filtered_clicks = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE $where_sql");

	// Monthly comparison calculations (This Month vs Last Month)
	$this_month_clicks = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE click_time >= DATE_FORMAT(NOW(), '%Y-%m-01 00:00:00')");
	$last_month_clicks = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE click_time >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH), '%Y-%m-01 00:00:00') AND click_time < DATE_FORMAT(NOW(), '%Y-%m-01 00:00:00')");
	
	$month_diff = $this_month_clicks - $last_month_clicks;
	if ($last_month_clicks > 0) {
		$month_growth = round(($month_diff / $last_month_clicks) * 100, 1);
	} elseif ($this_month_clicks > 0) {
		$month_growth = 100;
	} else {
		$month_growth = 0;
	}

	// Daily comparison calculations (Today vs Yesterday)
	$today_clicks = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE click_time >= CURDATE()");
	$yesterday_clicks = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE click_time >= DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND click_time < CURDATE()");
	
	$day_diff = $today_clicks - $yesterday_clicks;
	if ($yesterday_clicks > 0) {
		$day_growth = round(($day_diff / $yesterday_clicks) * 100, 1);
	} elseif ($today_clicks > 0) {
		$day_growth = 100;
	} else {
		$day_growth = 0;
	}

	// Populate chart points based on period
	if ($period === 'today') {
		$hourly_results = $wpdb->get_results("
			SELECT HOUR(click_time) as h, COUNT(*) as c 
			FROM $table_name 
			WHERE click_time >= CURDATE()
			GROUP BY h
		");
		foreach ($hourly_results as $row) {
			$h_key = sprintf('%02d:00', (int)$row->h);
			if (isset($chart_days[$h_key])) {
				$chart_days[$h_key] = (int)$row->c;
			}
		}
	} elseif ($is_monthly) {
		$monthly_results = $wpdb->get_results("
			SELECT DATE_FORMAT(click_time, '%Y-%m') as click_month, COUNT(*) as click_count 
			FROM $table_name 
			GROUP BY click_month
		");
		foreach ($monthly_results as $row) {
			if (isset($chart_days[$row->click_month])) {
				$chart_days[$row->click_month] = (int)$row->click_count;
			}
		}
	} else {
		$trend_results = $wpdb->get_results("
			SELECT DATE(click_time) as click_date, COUNT(*) as click_count 
			FROM $table_name 
			WHERE $where_sql
			GROUP BY click_date
		");
		foreach ($trend_results as $row) {
			if (isset($chart_days[$row->click_date])) {
				$chart_days[$row->click_date] = (int)$row->click_count;
			}
		}
	}

	// SVG chart metrics
	$max_clicks = max($chart_days);
	if ($max_clicks <= 0) {
		$max_clicks = 10;
	}

	$width = 800;
	$height = 200;
	$padding = 20;
	$chart_w = $width - ($padding * 2);
	$chart_h = $height - ($padding * 2);
	$point_count = count($chart_days);
	$x_step = $point_count > 1 ? ($chart_w / ($point_count - 1)) : $chart_w;

	$points = array();
	$index = 0;
	foreach ($chart_days as $label => $count) {
		$x = $padding + ($index * $x_step);
		$y = $padding + $chart_h - (($count / $max_clicks) * $chart_h);
		$points[] = "$x,$y";
		$index++;
	}
	$points_str = implode(' ', $points);
	$fill_points = "$padding," . ($padding + $chart_h) . " " . $points_str . " " . ($padding + $chart_w) . "," . ($padding + $chart_h);

	// Agent mappings
	$number_map = array();
	if (!empty($settings['numbers']) && is_array($settings['numbers'])) {
		foreach ($settings['numbers'] as $num_item) {
			$number_map[$num_item['number']] = $num_item['title'] . (!empty($num_item['department']) ? ' (' . $num_item['department'] . ')' : '');
		}
	}

	// Filtered Breakdown: Clicks by Agent
	$agent_results = $wpdb->get_results("
		SELECT number, COUNT(*) as click_count 
		FROM $table_name 
		WHERE $where_sql
		GROUP BY number 
		ORDER BY click_count DESC
	");

	// Filtered Breakdown: Clicks by Page
	$page_results = $wpdb->get_results("
		SELECT page_url, COUNT(*) as click_count 
		FROM $table_name 
		WHERE $where_sql
		GROUP BY page_url 
		ORDER BY click_count DESC 
		LIMIT 5
	");

	// Filtered Breakdown: Clicks by Device
	$device_results = $wpdb->get_results("
		SELECT device, COUNT(*) as click_count 
		FROM $table_name 
		WHERE $where_sql
		GROUP BY device
	");
	$devices = array('desktop' => 0, 'mobile' => 0);
	foreach ($device_results as $row) {
		$devices[$row->device] = (int) $row->click_count;
	}
	$device_total = array_sum($devices);

	// Filtered Breakdown: Peak Hours
	$hour_results = $wpdb->get_results("
		SELECT HOUR(click_time) as click_hour, COUNT(*) as click_count 
		FROM $table_name 
		WHERE $where_sql
		GROUP BY click_hour 
		ORDER BY click_count DESC
		LIMIT 5
	");

	// Best performing month (for All Time card)
	$best_month_row = $wpdb->get_row("
		SELECT DATE_FORMAT(click_time, '%Y-%m') as m, COUNT(*) as c 
		FROM $table_name 
		GROUP BY m 
		ORDER BY c DESC 
		LIMIT 1
	");
	$best_month_display = $best_month_row ? date_i18n('F Y', strtotime($best_month_row->m . '-01')) : '-';
	$best_month_count = $best_month_row ? (int)$best_month_row->c : 0;
	?>
	<!-- Stats & Comparison Cards -->
	<div class="watso-stats-cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; margin-bottom: 30px;">
		
		<!-- Card 1: Selected Period Total -->
		<div class="watso-stat-card" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 22px; display: flex; align-items: center; gap: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
			<div class="watso-stat-icon" style="background: rgba(17, 152, 73, 0.1); color: #119849; width: 52px; height: 52px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0;">
				<i class="fas fa-mouse-pointer"></i>
			</div>
			<div class="watso-stat-info" style="flex-grow: 1;">
				<span class="watso-stat-label" style="display: block; font-size: 13px; color: #64748b; font-weight: 500;">
					<?php 
					if ($period === 'this_month') {
						esc_html_e('This Month Clicks', 'watso-basic-chat');
					} elseif ($period === 'last_month') {
						esc_html_e('Last Month Clicks', 'watso-basic-chat');
					} elseif ($period === '7days') {
						esc_html_e('Last 7 Days Clicks', 'watso-basic-chat');
					} elseif ($period === 'today') {
						esc_html_e('Today Clicks', 'watso-basic-chat');
					} elseif ($period === 'all') {
						esc_html_e('All Time Total Clicks', 'watso-basic-chat');
					} else {
						esc_html_e('Selected Period Clicks', 'watso-basic-chat');
					}
					?>
				</span>
				<span class="watso-stat-value" style="display: block; font-size: 26px; color: #1e293b; font-weight: 700; line-height: 1.2; margin-top: 4px;">
					<?php echo number_format_i18n($filtered_clicks); ?>
				</span>
				<span style="font-size: 11px; color: #94a3b8; display: block; margin-top: 2px;">
					<?php echo esc_html__('All-time Total:', 'watso-basic-chat') . ' ' . number_format_i18n($total_clicks); ?>
				</span>
			</div>
		</div>
		
		<?php if ($period === 'all'): ?>
			<!-- Card 2 (All Time Context): Best Performing Month -->
			<div class="watso-stat-card" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 22px; display: flex; align-items: center; gap: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
				<div class="watso-stat-icon" style="background: rgba(0, 115, 170, 0.1); color: #0073aa; width: 52px; height: 52px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0;">
					<i class="fas fa-trophy"></i>
				</div>
				<div class="watso-stat-info" style="flex-grow: 1;">
					<div style="display: flex; justify-content: space-between; align-items: center;">
						<span class="watso-stat-label" style="display: block; font-size: 13px; color: #64748b; font-weight: 500;"><?php esc_html_e('Best Month', 'watso-basic-chat'); ?></span>
						<span class="watso-growth-badge watso-growth-up">
							<?php echo number_format_i18n($best_month_count); ?> <?php esc_html_e('clicks', 'watso-basic-chat'); ?>
						</span>
					</div>
					<div style="margin-top: 4px;">
						<span class="watso-stat-value" style="font-size: 20px; color: #1e293b; font-weight: 700; line-height: 1.2;">
							<?php echo esc_html($best_month_display); ?>
						</span>
					</div>
					<span style="font-size: 11px; color: #94a3b8; display: block; margin-top: 2px;">
						<?php esc_html_e('Highest engagement in history', 'watso-basic-chat'); ?>
					</span>
				</div>
			</div>

			<!-- Card 3 (All Time Context): Top Agent -->
			<?php 
			$top_agent = !empty($agent_results[0]) ? $agent_results[0] : null;
			$top_agent_name = $top_agent ? (isset($number_map[$top_agent->number]) ? $number_map[$top_agent->number] : $top_agent->number) : '-';
			$top_agent_clicks = $top_agent ? (int)$top_agent->click_count : 0;
			?>
			<div class="watso-stat-card" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 22px; display: flex; align-items: center; gap: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
				<div class="watso-stat-icon" style="background: rgba(233, 30, 99, 0.1); color: #e91e63; width: 52px; height: 52px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0;">
					<i class="fas fa-user-check"></i>
				</div>
				<div class="watso-stat-info" style="flex-grow: 1;">
					<div style="display: flex; justify-content: space-between; align-items: center;">
						<span class="watso-stat-label" style="display: block; font-size: 13px; color: #64748b; font-weight: 500;"><?php esc_html_e('Top Agent', 'watso-basic-chat'); ?></span>
						<span class="watso-growth-badge watso-growth-up">
							<?php echo number_format_i18n($top_agent_clicks); ?> <?php esc_html_e('clicks', 'watso-basic-chat'); ?>
						</span>
					</div>
					<div style="margin-top: 4px;">
						<span class="watso-stat-value" style="font-size: 20px; color: #1e293b; font-weight: 700; line-height: 1.2; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 200px;">
							<?php echo esc_html($top_agent_name); ?>
						</span>
					</div>
					<span style="font-size: 11px; color: #94a3b8; display: block; margin-top: 2px;">
						<?php esc_html_e('Most clicked support number', 'watso-basic-chat'); ?>
					</span>
				</div>
			</div>

		<?php else: ?>
			<!-- Card 2: Monthly Comparison (This Month vs Last Month) -->
			<div class="watso-stat-card" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 22px; display: flex; align-items: center; gap: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
				<div class="watso-stat-icon" style="background: rgba(0, 115, 170, 0.1); color: #0073aa; width: 52px; height: 52px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0;">
					<i class="fas fa-calendar-alt"></i>
				</div>
				<div class="watso-stat-info" style="flex-grow: 1;">
					<div style="display: flex; justify-content: space-between; align-items: center;">
						<span class="watso-stat-label" style="display: block; font-size: 13px; color: #64748b; font-weight: 500;"><?php esc_html_e('Monthly Comparison', 'watso-basic-chat'); ?></span>
						<?php if ($month_diff > 0): ?>
							<span class="watso-growth-badge watso-growth-up" title="<?php esc_attr_e('Increase compared to last month', 'watso-basic-chat'); ?>">
								<i class="fas fa-arrow-up"></i> +<?php echo esc_html($month_growth); ?>%
							</span>
						<?php elseif ($month_diff < 0): ?>
							<span class="watso-growth-badge watso-growth-down" title="<?php esc_attr_e('Decrease compared to last month', 'watso-basic-chat'); ?>">
								<i class="fas fa-arrow-down"></i> <?php echo esc_html($month_growth); ?>%
							</span>
						<?php else: ?>
							<span class="watso-growth-badge watso-growth-neutral">
								0%
							</span>
						<?php endif; ?>
					</div>
					<div style="display: flex; align-items: baseline; gap: 12px; margin-top: 4px;">
						<span class="watso-stat-value" style="font-size: 26px; color: #1e293b; font-weight: 700; line-height: 1.2;">
							<?php echo number_format_i18n($this_month_clicks); ?>
						</span>
						<span style="font-size: 12px; color: #64748b;">
							<?php echo esc_html__('vs.', 'watso-basic-chat') . ' ' . number_format_i18n($last_month_clicks) . ' ' . esc_html__('(Last Mo.)', 'watso-basic-chat'); ?>
						</span>
					</div>
					<span style="font-size: 11px; color: #94a3b8; display: block; margin-top: 2px;">
						<?php esc_html_e('Current calendar month vs previous', 'watso-basic-chat'); ?>
					</span>
				</div>
			</div>
			
			<!-- Card 3: Daily Comparison (Today vs Yesterday) -->
			<div class="watso-stat-card" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 22px; display: flex; align-items: center; gap: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
				<div class="watso-stat-icon" style="background: rgba(233, 30, 99, 0.1); color: #e91e63; width: 52px; height: 52px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0;">
					<i class="fas fa-bolt"></i>
				</div>
				<div class="watso-stat-info" style="flex-grow: 1;">
					<div style="display: flex; justify-content: space-between; align-items: center;">
						<span class="watso-stat-label" style="display: block; font-size: 13px; color: #64748b; font-weight: 500;"><?php esc_html_e('Today vs Yesterday', 'watso-basic-chat'); ?></span>
						<?php if ($day_diff > 0): ?>
							<span class="watso-growth-badge watso-growth-up" title="<?php esc_attr_e('Increase compared to yesterday', 'watso-basic-chat'); ?>">
								<i class="fas fa-arrow-up"></i> +<?php echo esc_html($day_growth); ?>%
							</span>
						<?php elseif ($day_diff < 0): ?>
							<span class="watso-growth-badge watso-growth-down" title="<?php esc_attr_e('Decrease compared to yesterday', 'watso-basic-chat'); ?>">
								<i class="fas fa-arrow-down"></i> <?php echo esc_html($day_growth); ?>%
							</span>
						<?php else: ?>
							<span class="watso-growth-badge watso-growth-neutral">
								0%
							</span>
						<?php endif; ?>
					</div>
					<div style="display: flex; align-items: baseline; gap: 12px; margin-top: 4px;">
						<span class="watso-stat-value" style="font-size: 26px; color: #1e293b; font-weight: 700; line-height: 1.2;">
							<?php echo number_format_i18n($today_clicks); ?>
						</span>
						<span style="font-size: 12px; color: #64748b;">
							<?php echo esc_html__('vs.', 'watso-basic-chat') . ' ' . number_format_i18n($yesterday_clicks) . ' ' . esc_html__('(Yesterday)', 'watso-basic-chat'); ?>
						</span>
					</div>
					<span style="font-size: 11px; color: #94a3b8; display: block; margin-top: 2px;">
						<?php esc_html_e('Real-time daily pace comparison', 'watso-basic-chat'); ?>
					</span>
				</div>
			</div>
		<?php endif; ?>
	</div>

	<!-- Main Trend Chart Box -->
	<div class="watso-chart-box" style="margin-top: 30px; background: #fafafa; border: 1px solid #e2e8f0; border-radius: 10px; padding: 22px;">
		<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
			<h4 style="margin: 0; font-size: 15px; font-weight: 600; color: #334155; display: flex; align-items: center; gap: 8px;">
				<i class="fas fa-chart-area" style="color: #119849;"></i><?php echo esc_html($chart_title); ?>
			</h4>
			<span style="font-size: 12px; color: #64748b; font-weight: 500;">
				<?php echo esc_html__('Peak in view:', 'watso-basic-chat') . ' ' . esc_html(max($chart_days)) . ' ' . esc_html__('clicks', 'watso-basic-chat'); ?>
			</span>
		</div>
		
		<div class="watso-svg-chart-container" style="position: relative;">
			<svg viewBox="0 0 800 200" width="100%" height="200" class="watso-svg-chart" style="overflow: visible;">
				<defs>
					<linearGradient id="watso-gradient" x1="0" y1="0" x2="0" y2="1">
						<stop offset="0%" stop-color="#119849" stop-opacity="0.25"/>
						<stop offset="100%" stop-color="#119849" stop-opacity="0.0"/>
					</linearGradient>
				</defs>
				
				<!-- Horizontal grid lines -->
				<line x1="20" y1="20" x2="780" y2="20" stroke="#f1f5f9" stroke-width="1" />
				<line x1="20" y1="60" x2="780" y2="60" stroke="#f1f5f9" stroke-width="1" />
				<line x1="20" y1="100" x2="780" y2="100" stroke="#f1f5f9" stroke-width="1" />
				<line x1="20" y1="140" x2="780" y2="140" stroke="#f1f5f9" stroke-width="1" />
				<line x1="20" y1="180" x2="780" y2="180" stroke="#cbd5e1" stroke-width="1" />
				
				<!-- Area fill -->
				<polygon points="<?php echo esc_attr($fill_points); ?>" fill="url(#watso-gradient)" />
				
				<!-- Line -->
				<polyline points="<?php echo esc_attr($points_str); ?>" fill="none" stroke="#119849" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
				
				<!-- Interactive dots -->
				<?php
				$index = 0;
				foreach ($chart_days as $label => $count):
					$x = $padding + ($index * $x_step);
					$y = $padding + $chart_h - (($count / $max_clicks) * $chart_h);

					if ($is_monthly) {
						$display_label = date_i18n('F Y', strtotime($label . '-01'));
					} elseif ($period === 'today') {
						$display_label = $label;
					} else {
						$display_label = date_i18n('d M Y', strtotime($label));
					}
				?>
					<circle cx="<?php echo $x; ?>" cy="<?php echo $y; ?>" r="5" fill="#fff" stroke="#119849" stroke-width="2.5" class="watso-chart-dot" style="cursor: pointer;">
						<title><?php echo esc_html($display_label) . ': ' . esc_html($count) . ' ' . esc_html__('clicks', 'watso-basic-chat'); ?></title>
					</circle>
					<?php if ($count > 0): ?>
						<!-- Value label above dot for active points -->
						<text x="<?php echo $x; ?>" y="<?php echo max(15, $y - 10); ?>" text-anchor="middle" font-size="11" font-weight="700" fill="#119849"><?php echo esc_html($count); ?></text>
					<?php endif; ?>
				<?php
					$index++;
				endforeach;
				?>
			</svg>
		</div>
		
		<?php
		$chart_keys = array_keys($chart_days);
		$total_points = count($chart_keys);

		// Determine step size to display up to 6 evenly distributed labels
		if ($total_points <= 7) {
			$label_indices = range(0, $total_points - 1);
		} else {
			$step = ($total_points - 1) / 5;
			$label_indices = array();
			for ($k = 0; $k <= 5; $k++) {
				$label_indices[] = (int) round($k * $step);
			}
			$label_indices = array_unique($label_indices);
		}

		$format_label = function($val) use ($period, $is_monthly) {
			if (empty($val)) return '';
			if ($is_monthly) {
				return date_i18n('M Y', strtotime($val . '-01'));
			}
			if ($period === 'today') return $val;
			return date_i18n('d M', strtotime($val));
		};
		?>
		<!-- Date Labels (Multi-point distribution) -->
		<div class="watso-chart-labels" style="display: flex; justify-content: space-between; padding: 12px 10px 0 10px; font-size: 11px; color: #64748b; font-weight: 500;">
			<?php foreach ($label_indices as $idx): 
				if (isset($chart_keys[$idx])):
			?>
				<span><?php echo esc_html($format_label($chart_keys[$idx])); ?></span>
			<?php 
				endif;
			endforeach; 
			?>
		</div>
	</div>
	
	<!-- Secondary Stats Grid (Filtered Breakdown) -->
	<div class="watso-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-top: 30px;">
		
		<!-- Clicks by Agent -->
		<div class="watso-card-panel" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px;">
			<h4 style="margin: 0 0 15px 0; font-size: 14px; font-weight: 600; color: #475569; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
				<i class="fas fa-users-cog" style="color: #4caf50;"></i><?php esc_html_e('Clicks by Agent / Number', 'watso-basic-chat'); ?>
			</h4>
			<div class="watso-panel-list">
				<?php if (empty($agent_results)): ?>
					<p style="color: #94a3b8; font-size: 13px; text-align: center; padding: 20px 0;"><?php esc_html_e('No clicks in this period.', 'watso-basic-chat'); ?></p>
				<?php else: ?>
					<?php foreach ($agent_results as $row): 
						$display_name = isset($number_map[$row->number]) ? $number_map[$row->number] : $row->number;
						$pct = $filtered_clicks > 0 ? round(($row->click_count / $filtered_clicks) * 100) : 0;
					?>
						<div class="watso-panel-item" style="margin-bottom: 14px;">
							<div class="watso-item-desc" style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 5px; color: #334155;">
								<span style="font-weight: 600;"><?php echo esc_html($display_name); ?></span>
								<span style="color: #64748b;"><?php echo esc_html($row->click_count) . ' ' . esc_html__('clicks', 'watso-basic-chat') . ' (' . $pct . '%)'; ?></span>
							</div>
							<div class="watso-progress-bar" style="height: 6px; background: #f1f5f9; border-radius: 3px; overflow: hidden;">
								<div class="watso-progress-fill" style="width: <?php echo $pct; ?>%; height: 100%; background: #119849; border-radius: 3px;"></div>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>
		
		<!-- Clicks by Page -->
		<div class="watso-card-panel" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px;">
			<h4 style="margin: 0 0 15px 0; font-size: 14px; font-weight: 600; color: #475569; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
				<i class="fas fa-link" style="color: #0073aa;"></i><?php esc_html_e('Top Pages (Clicks)', 'watso-basic-chat'); ?>
			</h4>
			<?php if (empty($page_results)): ?>
				<p style="color: #94a3b8; font-size: 13px; text-align: center; padding: 20px 0;"><?php esc_html_e('No clicks in this period.', 'watso-basic-chat'); ?></p>
			<?php else: ?>
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
								<td style="padding: 10px 0; color: #334155; font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 220px;" title="<?php echo esc_url($clean_url); ?>">
									<?php echo esc_html($path); ?>
								</td>
								<td style="padding: 10px 0; text-align: right; color: #64748b; font-weight: 600;">
									<?php echo esc_html($row->click_count); ?> <?php esc_html_e('clicks', 'watso-basic-chat'); ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>

		<!-- Device & Hour breakdown -->
		<div class="watso-card-panel" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px;">
			<h4 style="margin: 0 0 15px 0; font-size: 14px; font-weight: 600; color: #475569; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
				<i class="fas fa-mobile-alt" style="color: #e91e63;"></i><?php esc_html_e('Device & Time Breakdown', 'watso-basic-chat'); ?>
			</h4>
			<div class="watso-device-breakdown" style="margin-bottom: 18px;">
				<?php 
				$mobile_pct = $device_total > 0 ? round(($devices['mobile'] / $device_total) * 100) : 0;
				$desktop_pct = $device_total > 0 ? 100 - $mobile_pct : 0;
				?>
				<div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 6px; color: #334155;">
					<span><i class="fas fa-mobile-alt" style="margin-right: 4px; color: #e91e63;"></i><?php esc_html_e('Mobile', 'watso-basic-chat'); ?> (<?php echo $mobile_pct; ?>%)</span>
					<span><i class="fas fa-desktop" style="margin-right: 4px; color: #0073aa;"></i><?php esc_html_e('Desktop', 'watso-basic-chat'); ?> (<?php echo $desktop_pct; ?>%)</span>
				</div>
				<div style="height: 10px; background: #0073aa; border-radius: 5px; overflow: hidden; display: flex;">
					<div style="width: <?php echo $mobile_pct; ?>%; height: 100%; background: #e91e63;"></div>
				</div>
			</div>

			<div class="watso-busy-hours">
				<h5 style="margin: 15px 0 8px 0; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 600;"><?php esc_html_e('Peak Hours', 'watso-basic-chat'); ?></h5>
				<?php if (empty($hour_results)): ?>
					<p style="color: #94a3b8; font-size: 12px; margin: 0;"><?php esc_html_e('No activity recorded.', 'watso-basic-chat'); ?></p>
				<?php else: ?>
					<div style="display: flex; gap: 6px; flex-wrap: wrap;">
						<?php foreach ($hour_results as $row): ?>
							<span style="background: #f1f5f9; color: #334155; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 500;">
								<?php printf('%02d:00 (%d)', $row->click_hour, $row->click_count); ?>
							</span>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		
	</div>
	<?php
}
