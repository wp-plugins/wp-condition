<?php
/*
Plugin Name: WP Condition
Plugin URI: http://wpfixit.net
Description: Display WP-Condition in Chart for Database Performance, Memory Performance, Site Performance, and Social Performance. Requires PHP 5.2.0+
Version: 1.0.0
Author: zinger252
Author URI: http://wpfixit.net
*/

class WP_Page_Condition_Stats {

	private $average_option;

	/**
	 * Gets things started
	 */
	function __construct() {
		// Init
		add_action( 'init', array( &$this, 'init' ) );

		// Frontend
		add_action( 'wp_head', array( &$this, 'wp_head' ) );
		add_action( 'wp_footer', array( &$this, 'wp_footer' ) );

		// Backend
		add_action( 'admin_head', array( &$this, 'wp_head' ) );
		add_action( 'admin_footer', array( &$this, 'wp_footer' ) );
		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );

		// Enqueue
		add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue' ) );
		add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue' ) );

		// Where to store averages
		$this->average_option = is_admin() ? 'wpfixit_con_admin_load_times' : 'wpfixit_con_load_times';
	}

	/**
	 * init function.
	 *
	 * @access public
	 */
	function init() {
		load_plugin_textdomain( 'wpfixit_con', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		if ( isset( $_GET['reset_wpfixit_con_stats'] ) && $_GET['reset_wpfixit_con_stats'] == 1 ) {
			update_option( $this->average_option, array() );
			wp_safe_redirect(  wp_get_referer() );
			exit;
		}    	

	}
	function admin_menu() {
//		$this->display();
		add_menu_page( 'WPFIXIT', 'WP Condition', 'manage_options', 'wp-conditions', array( &$this, 'display' ), '', 99 );
	}
	/**
	 * wp_head function.
	 *
	 * @access public
	 */
	function wp_head() {
/*		echo "<script type='text/javascript'>
			function wpfixit_con_hide(){
			   var wpplsDiv = document.getElementById('wpfixit_con');
			   wpplsDiv.style.display = 'none';
			}
		</script>"; */
	} 

	/**
	 * wp_footer function.
	 *
	 * @access public
	 */
	function wp_footer() {
	//	$this->display();

	}

	/**
	 * enqueue function.
	 *
	 * @access public
	 */
	function enqueue() {
        wp_enqueue_style( 'wpfixit_con-style', plugins_url('style.css', __FILE__) );
		wp_enqueue_script( 'wpfixit_con-script', plugins_url('Chart.js', __FILE__) );
	}

	/**
	 * display function.
	 *
	 * @access public
	 */
	function display() {
		// Get values we're displaying
		include( plugin_dir_path( __FILE__ ) . 'lib/social.php');         
		$obj=new shareCount(site_url()); 
		$timer_stop 		= timer_stop(0);
		$query_count 		= get_num_queries();
		$memory_usage 		= round( $this->convert_bytes_to_hr( memory_get_usage() ), 2 );
		$memory_peak_usage 	= round( $this->convert_bytes_to_hr( memory_get_peak_usage() ), 2 );
		$memory_limit 		= round( $this->convert_bytes_to_hr( $this->let_to_num( WP_MEMORY_LIMIT ) ), 2 );
		$load_times			= array_filter( (array) get_option( $this->average_option ) );

		$load_times[]		= $timer_stop;

		// Update load times
		update_option( $this->average_option, $load_times );

		// Get average
		if ( sizeof( $load_times ) > 0 )
			$average_load_time = round( array_sum( $load_times ) / sizeof( $load_times ), 4 );

		// Display the info
		?>
        <h1>WP Condition - <small>WPFIXIT.net</small></h1>
        <div id="wpfixit_container" style="width:100%">
		<div id="wpfixit_conditions">
        <table>
        <tbody cellspacing="20">
        
        <tr>
        <td>
        
			
				<h2>Database Perfomance:</h2><p><?php printf( __( '%s queries in %s seconds.', 'wpfixit_con' ), $query_count, $timer_stop ); ?>
                <?php if (empty( $load_times ))
						echo 'Reload this Page to see Chart';
						function wpo_fs_info($filesize)
				{
					$bytes = array(
							'B',
							'K',
							'M',
							'G',
							'T'
					);
					if ($filesize < 1024)
							$filesize = 1;
					for ($i = 0; $filesize > 1024; $i++)
							$filesize /= 1024;
					$wpo_fs_info['size'] = round($filesize, 3);
					$wpo_fs_info['type'] = $bytes[$i];
					return $wpo_fs_info;
				}
					$rows   = mysql_query("SHOW table STATUS");
					$dbsize = 0;
					while ($row = mysql_fetch_array($rows))
						{
							$dbsize += $row['Data_length'] + $row['Index_length'];
						}
					$dbsize = wpo_fs_info($dbsize);
					echo 'Database Size '.$dbsize['size'].$dbsize['type'];
					
					
						?>
                </p>
                <canvas id="svperform" width="200" height="200"></canvas>
                <script>
		var pieData = [
				{
					value: <?php echo $query_count ?>,
					color:"lightblue"
				},
				{
					value : <?php echo $timer_stop ?>,
					color : "red"
				}
			
			];

	var myPie = new Chart(document.getElementById("svperform").getContext("2d")).Pie(pieData);


                </script>
                </td>
        <td>
				<h2>Site Performance:</h2><p><?php printf( __( 'Average load time of %s (%s runs).', 'wpfixit_con' ), $average_load_time, sizeof( $load_times ) ); ?></p>
                <canvas id="siperform" height="200" width="200"></canvas>


	<script>
	var chartData = [
			<?php 
			$count = 0;
			if($load_times)
			foreach ($load_times as $loadtime){
				$count++;
				?>
			{
				value : <?php echo $loadtime?>,
				color: "#D<?php echo $count?>7041"
			},
			<?php }
			?>
		];
	var myPolarArea = new Chart(document.getElementById("siperform").getContext("2d")).PolarArea(chartData);
	</script>
                
                
                
                
        </td>
        <td>
                <div id="wpfixit_right" style="float:left">
        
		<?php        
		$current_user = wp_get_current_user();?>
    <h1>Submit Your Issue:</h1>
    <form action="http://www.wpfixit.net/?page_id=168" method="post" target="_blank">
    <label>Subject: </label><br /><input type="text" name="subject" /><br />
    <label>Issue: </label><br /><textarea name="issue" cols="40" rows="10" ></textarea>
    <input name="username" type="hidden" value="<?php echo urlencode(bloginfo('name')).'-'.$current_user->user_login ?>" />
    <input name="performance" type="hidden" value="<?php echo 'MemoryPeak:'.$memory_peak_usage.'MemoryUsage:'.$memory_usage.'AvgLoadtime:'.$average_load_time.'TotalQueries:'.$query_count.'TimeforQueries:'.$timer_stop ?>" />
    <input name="email" type="hidden" value="<?php echo $current_user->user_email ?>" />
    <?php submit_button('Submit Ticket $38'); ?>
    </form>
        
        </div>

        </td>
        </tr>
        
        <tr>
        <td>
                
				<h2>Memory Usage:</h2><p><?php printf( __( '%s out of %s MB (%s) memory used.', 'wpfixit_con' ), $memory_usage, $memory_limit, round( ( $memory_usage / $memory_limit ), 2 ) * 100 . '%' ); ?></p>
                
                <canvas id="dbperform" width="200" height="200"></canvas>
                <script>
		var pieData = [
				{
					value: <?php echo $memory_limit ?>,
					color:"lightblue"
				},
				{
					value : <?php echo $memory_usage ?>,
					color : "red"
				}
			
			];

	var myPie = new Chart(document.getElementById("dbperform").getContext("2d")).Pie(pieData);


                </script>

        </td>
        <td>

				<h2>Peak Memory Usage:</h2><p><?php printf( __( 'Peak memory usage %s MB.', 'wpfixit_con' ), $memory_peak_usage ); ?></p>
                
                <canvas id="peakmemory" height="200" width="200"></canvas>


	<script>
	var pieData = [
			{
				value : <?php echo $memory_peak_usage?>,
				color: "red"
			},
			{
				value : <?php echo $memory_limit ?>,
				color: "lightblue"
			}
		];
	var myPie = new Chart(document.getElementById("peakmemory").getContext("2d")).Pie(pieData);
	</script>
                
			
			
            
                    </td>
                    <td>
                    <div class="actions">
				<a class="reset" href="<?php echo add_query_arg( 'reset_wpfixit_con_stats', 1 ); ?>">Reset</a>
			</div>
                    </td>
        </tr>
        
        <tr>
        <th colspan="2" rowspan="2">
                
				<h2>Social Performance:</h2>
                <canvas id="socialperform" height="300" width="600"></canvas>


<script>
		var barChartData = {
			labels : ["Twitter","Facebook","LinkedIn","Google+","Delicious","Pinterest","Stumble"],
			datasets : [
				{
					fillColor : "rgba(220,220,220,0.5)",
					strokeColor : "rgba(220,220,220,1)",
					data : [<?php echo $obj->get_tweets().','.$obj->get_fb().','.$obj->get_linkedin().','.$obj->get_plusones().','.$obj->get_delicious().','.$obj->get_pinterest().','.$obj->get_stumble();?>]
				},
				{
					fillColor : "rgba(151,187,205,0.5)",
					strokeColor : "rgba(151,187,205,1)",
					data : [<?php echo $obj->get_tweets().','.$obj->get_fb().','.$obj->get_linkedin().','.$obj->get_plusones().','.$obj->get_delicious().','.$obj->get_pinterest().','.$obj->get_stumble();?>]
				}
			]
			
		}

	var myLine = new Chart(document.getElementById("socialperform").getContext("2d")).Bar(barChartData);
	</script>                
                
                
                
        </th>
<td></td>
<td></td>

        
        </tr>
        
        </tbody>
        </table>

		</div>
        </div>
		<?php
	}

	/**
	 * let_to_num function.
	 *
	 * This function transforms the php.ini notation for numbers (like '2M') to an integer
	 *
	 * @access public
	 * @param $size
	 * @return int
	 */
	function let_to_num( $size ) {
	    $l 		= substr( $size, -1 );
	    $ret 	= substr( $size, 0, -1 );
	    switch( strtoupper( $l ) ) {
		    case 'P':
		        $ret *= 1024;
		    case 'T':
		        $ret *= 1024;
		    case 'G':
		        $ret *= 1024;
		    case 'M':
		        $ret *= 1024;
		    case 'K':
		        $ret *= 1024;
	    }
	    return $ret;
	}

	/**
	 * convert_bytes_to_hr function.
	 *
	 * @access public
	 * @param mixed $bytes
	 */
	function convert_bytes_to_hr( $bytes ) {
		$units = array( 0 => 'B', 1 => 'kB', 2 => 'MB', 3 => 'GB' );
		$log = log( $bytes, 1024 );
		$power = (int) $log;
		$size = pow(1024, $log - $power);
		return $size . $units[$power];
	}

}

$WP_Page_Condition_Stats = new WP_Page_Condition_Stats();

add_action( 'admin_menu', 'wpfixit_conditions' );
function wpfixit_conditions(){
//    add_menu_page( 'WPFIXIT', 'WP Condition', 'manage_options', 'wp-conditions', 'display', '', 99 );
}