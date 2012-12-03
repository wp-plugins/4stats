<?php
/*
Plugin Name: 4stats
Plugin URI: http://www.4stats.de/wordpress/
Description: This plugins adds the 4stats.de website statistics to your wp-admin interface and the tracking code to your blog.
Author: 4stats
Version: 2.0.2
Author URI: http://www.4stats.de/
*/


if(!class_exists('Fourstats'))
{
	class Fourstats
	{
		
		static private $classobj = null;
		static private $tab = null;
		
		/**
		 * construct
		 *
		 * @uses
		 * @access public
		 * @since 0.0.1
		 * @return void
		 */
		public function __construct()
		{
			if(is_admin())
			{
				add_action( 'admin_menu', array( $this, 'add_page_to_navi' ) );
				
				if ( empty($GLOBALS['pagenow']) or ( !empty($GLOBALS['pagenow']) && $GLOBALS['pagenow'] == 'index.php' ) )
				{
					add_action(
						'wp_dashboard_setup',
						array(
							__CLASS__,
							'add_dashboard_stats'
						)
					);
				}
			}
			else
			{
				
			}
		}
		
		
		/**
		 * points the class
		 *
		 * @access public
		 * @since 0.0.1
		 * @return object
		 */
		public function get_object() {

			if ( NULL === self :: $classobj )
				self :: $classobj = new self;

			return self :: $classobj;
		}
		
		/**
		 * Installation hook, will be called on plugin-installation
		 *
		 */
		function install()
		{
			global $wpdb;
			//
		}

		/**
		 * Installation hook, will be called on plugin-uninstall
		 *
		 */
		function uninstall()
		{
			global $wpdb;
			//
		}

		/**
		 * Ads navigation item
		 *
		 */
		function add_page_to_navi()
		{
			global $wpdb;
			if ( function_exists('add_options_page') )
			{
				add_options_page('4stats', '4stats', 'manage_options', '4stats/4stats.php&tab=settings', array('Fourstats', 'config_page'));
			}
			
			if ( function_exists('add_menu_page') )
			{
				add_menu_page('4stats', '4stats', 'read', __FILE__, array( 'Fourstats', 'stats_page' ), plugins_url('4stats/img/icon.png'));
			}
			
			
		}

		function add_config_page_to_plugins($links, $file)
		{
			if($file ==  plugin_basename(__FILE__))
			{
				$settings_link = '<a href="options-general.php?page=4stats/4stats.php&tab=settings">' . __('Settings') . '</a>';
				array_push( $links, $settings_link );
			}
			return $links;
		}
		
		function the_tabs()
		{
			?>
			<h2 class="nav-tab-wrapper">
				<a class="nav-tab<?= (!$_GET['tab'] || $_GET['tab'] == "stats") ? " nav-tab-active" : ""; ?>" href="admin.php?page=4stats/4stats.php&amp;tab=stats">Stats</a>
				<a class="nav-tab<?= ($_GET['tab'] == "settings") ? " nav-tab-active" : ""; ?>" href="options-general.php?page=4stats/4stats.php&amp;tab=settings"><?= __('Settings'); ?></a>
			</h2>
			<?
		}
		
		function config_page()
		{
			self::$tab = 'settings';
			self::stats_page();
		}
		
		function stats_page()
		{
			if(isset($_POST['submit']))
			{
				$options['4stats_project_id'] = $_POST['4stats_project_id'];
				$options['4stats_api_key'] = $_POST['4stats_api_key'];
				$options['4stats_hide_counter'] = $_POST['4stats_hide_counter'];
				$options['4stats_disable_tracking'] = $_POST['4stats_disable_tracking'];
				update_option('4stats', $options);
			}
			
			$options  = get_option('4stats');
			
			if($_GET['tab'] == 'settings' || self::$tab == 'settings' || !$options['4stats_project_id'] || !$options['4stats_api_key'])
			{
				?>
				<div class="wrap">
					<h2>4stats Configuration</h2>
					<? self::the_tabs(); ?>
					<form action="" method="post" id="4stats-conf">
						<table class="form-table">
							<tr>
								<td colspan="2">
									<label for="4stats_project_id">4stats Project ID:</label><br/>
									<input size="50" type="text" id="4stats_project_id" name="4stats_project_id" <?php echo 'value="'.$options['4stats_project_id'].'" '; ?>/><br/>
									<small>Please enter your 4stats project id.</small>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<label for="4stats_api_key">4stats API-Key:</label><br/>
									<input size="50" type="text" id="4stats_api_key" name="4stats_api_key" <?php echo 'value="'.$options['4stats_api_key'].'" '; ?>/><br/>
									<small>Please enter your 4stats API-Key. It can found in the <a href="https://www.4stats.de/conf/project-settings/" target="_blank">project settings page</a>.</small>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<label for="4stats_hide_counter">Counter Visibility:</label><br/>
									<input size="50" type="checkbox" id="4stats_hide_counter" name="4stats_hide_counter" value="1" <?php echo ($options['4stats_hide_counter'] == "1") ? 'checked="checked"' : ""; ?>/> Hide Counter<br/>
									<small>Please only choose this option if you have a visible counter and don't want to show it in this blog.</small>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<label for="4stats_disable_tracking">Tracking Code:</label><br/>
									<input type="checkbox" id="4stats_disable_tracking" name="4stats_disable_tracking" value="1" <?php echo ($options['4stats_disable_tracking'] == "1") ? 'checked="checked"' : ""; ?>/> Disable Tracking<br/>
									<small>Please only choose this option if you allready have the tracking code installed manually.</small>
								</td>
							</tr>
						</table>
						<br/>
						<span class="submit" style="border: 0;"><input type="submit" name="submit" value="Save Settings" /></span>
					</form>
					<br/><br/>
					</div>
				<?
			}
			else
			{
				/* Get plugin info */
				$plugin_info = get_plugin_data(__FILE__);

				wp_register_script(
					'google_jsapi',
					'https://www.google.com/jsapi',
					false
				);
				wp_enqueue_script('google_jsapi');
				
				wp_register_script(
					'4stats',
					plugins_url('js/4stats.min.js', __FILE__),
					array('jquery'),
					$plugin_info['Version']
				);
				wp_enqueue_script('4stats');
				
				wp_enqueue_style('4stats-css',
				                plugins_url('css/4stats.css', __FILE__),
				                false,
				                $plugin_info['Version'],
				                false);
				?>
				<div class="wrap">
					<h2>4stats</h2>
					<? self::the_tabs(); ?>
					<div id="content4stats">
						<h3><?php _e('Day Performance'); ?></h3>
						<div id="chart_visitor_div" style="height: 180px; width: 100%;"></div>
						<br>
				
						<table class="widefat" id="fs_overview_stats">
							<thead>
								<tr>
								</tr>
							</thead>
							<tbody>
							</tbody>
						</table>
					
						<br>
						<h3><?php _e('Performance last 31 days'); ?></h3>
						<div id="chart_visitor_div_last31" style="height: 180px; width: 100%;"></div>
					
						<br>
					
						<div id="fs-box-row">
						</div>

						<h3><?php _e('Performance this year'); ?></h3>
						<div id="chart_visitor_div_year" style="height: 180px; width: 100%;"></div>

						<br>
					
						<div>
							<table class="widefat" id="fs-visitor-log">
								<thead><tr><th colspan="3"><?php _e('Last 25 Visitors'); ?></th></tr></thead>
								<tbody></tbody>
							</table>
						</div>
					</div>
				</div>
				<script type="text/javascript">
				jQuery(document).ready(function() { 
					fs = new fourStats(<?= $options['4stats_project_id']; ?>, '<?= $options['4stats_api_key']; ?>', '', '<?= date("D, d M Y H:i:s"); ?>');
					fs.statsPage();
				});				
				</script>
				<?
			}
		}
		
		/**
		* Initialisierung des Dashboard-Chart
		*
		* @since   2.0
		* @change  2.0
		*/
		public static function add_dashboard_stats()
		{
			if ( !current_user_can('level_8') ) {
				return;
			}

			/* Widget hinzufügen */
			wp_add_dashboard_widget(
				'fs_widget',
				'4stats',
				array(
					__CLASS__,
					'show_dashboard_stats'
				)
			);

			/* Get plugin info */
			$plugin_info = get_plugin_data(__FILE__);

			wp_register_script(
				'google_jsapi',
				'https://www.google.com/jsapi',
				false
			);
			wp_enqueue_script('google_jsapi');
			
			wp_register_script(
				'4stats',
				plugins_url('js/4stats.min.js', __FILE__),
				array('jquery'),
				$plugin_info['Version']
			);
			wp_enqueue_script('4stats');
			
			wp_enqueue_style('4stats-css',
			                plugins_url('css/dashboard.css', __FILE__),
			                false,
			                $plugin_info['Version'],
			                false);
			
		}
		

		/**
		* Ausgabe des Dashboard-Stats
		*
		* @since   2.0
		* @change  2.0
		*/
		public static function show_dashboard_stats()
		{
			$options  = get_option('4stats');
			?>
			<h3><?php _e('Day Performance'); ?></h3>
			<div id="chart_visitor_div" style="height: 120px; width: 100%;"></div>
			<br>
			<div id="fs_dashboard_stats">
			</div>
			<p class="textright">
				<a class="button" href="index.php?page=4stats/4stats.php">View All</a>
			</p>
			<script type="text/javascript">
			jQuery(document).ready(function() { 
				fs = new fourStats(<?= $options['4stats_project_id']; ?>, '<?= $options['4stats_api_key']; ?>', '', '<?= date("D, d M Y H:i:s"); ?>');
				fs.dashBoard();
			});				
			</script>
			<?
		}
		
		function tracking_code() {
			$options  = get_option('4stats');
			if(isset($options['4stats_project_id']) && $options['4stats_project_id'] && $options['4stats_disable_tracking'] != 1)
			{
				echo "<script type=\"text/javascript\">document.write(unescape('%3Cscr' + 'ipt src=\"http'+(document.location.protocol=='https:'?'s':'')+'://4stats.de/de/counter?id={$options['4stats_project_id']}";
				echo ($options['4stats_hide_counter'] == "1") ? '&amp;cntr=hide' : "";
				echo "\" type=\"text/javascript\"%3E%3C/script%3E'));</script>\n";
				echo "<noscript><div><img src=\"http://4stats.de/de/stats?id={$options['4stats_project_id']}";
				echo ($options['4stats_hide_counter'] == "1") ? '&amp;cntr=hide' : "";
				echo "\" style=\"border: none;\" alt=\"4stats\" /></div></noscript>\n";
			}
		}
	}
}

if(is_admin()) {
	register_activation_hook( __FILE__, array('Fourstats', 'install'));
	register_deactivation_hook( __FILE__, array('Fourstats', 'uninstall'));
	add_action( 'plugins_loaded', array('Fourstats', 'get_object') );
	add_filter( 'plugin_action_links', array( 'Fourstats', 'add_config_page_to_plugins'), 11, 2 );
} else {
	add_action('wp_footer', array('Fourstats', 'tracking_code'));
}
?>