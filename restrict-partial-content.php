<?php
/**
 * Plugin Name: Restrict Partial Content
 * Plugin URI: http://wordpress.org/plugins/restrict-partial-content/
 * Description: This plugin helps to protect specific portion of the content
 * Version: 1.3
 * Author: Waqas Ahmed
 * Author URI: http://speedsoftsol.com
 * License: GPL2
 */


include 'restrict-rules.php';

/** Rendering the output when the shortcode is placed **/
function render_restrict( $atts, $content = null ) {
	$parameter = extract( shortcode_atts( array(
		'condition' => 'any',
		'allow_role' => 'all',
		'allow_user' => 'all',
		'message' => ' [This content is restricted. Either login with the correct access or wait till the content is available for everyone.] ',
		'open_time' => 'No Time',
		'rule' => 'None'
	), $atts ) );

	//Restrict flag 1=restricted
	$final_restrict = 1;
	$time_restrict = 1;
	$user_role_restrict = 1;
	$user_id_restrict = 1;

	//If rule is supplied then get the data from that rule
	if ($rule != 'None') {
		$rule_data = get_metadata ('post', $rule);
		
		$allow_role = $rule_data['allow_role'][0];
		$allow_user = $rule_data['allow_user'][0];
		$open_time = $rule_data['open_time'][0];
		$condition = $rule_data['open_condition'][0];
		$message = $rule_data['restrict_message'][0];
	}
	
	//Find the server date
	$server_date = strtotime(current_time('Y-m-d H:i:s')); // use current_time
	//Calculate diff
	$interval = 0;
	$open_time = trim($open_time);
	if ($open_time != 'No Time' && $open_time!="") {
		$content_opening_date = strtotime($open_time);
		$interval = $content_opening_date - $server_date;
		if ($interval <0 ) {
			$time_restrict = 0;
		}
	}



	// Find current user role and ID - only when a user is logged in
	if (is_user_logged_in()) {
		$user_info = wp_get_current_user();
		$user_role = $user_info->roles[0];
		$user_id = $user_info->ID;
		$user_name = $user_info->user_login;
	
		//Check for ids/names
		$user_list = explode (",", $allow_user);
		$user_list_trimmed = array_map('trim', $user_list);
		if ($user_id !== 0 && (in_array($user_id, $user_list_trimmed) || in_array($user_name, $user_list_trimmed) )) {
			$user_id_restrict = 0;
		}

		//Check for roles
		$allow_role = strtolower ($allow_role);
		$role_list = explode (",", $allow_role);
		$role_list_trimmed = array_map('trim', $role_list);
		if ( $user_id !== 0) {
			foreach ( $user_info->roles as $user_role ) {
				if ( in_array ($user_role, $role_list_trimmed) ) {
					$user_role_restrict = 0;
				}
			}
		}
	}
	
	$condition = strtolower ($condition);
	$condition = trim ($condition);

	//Just in case someone puts in wrong condition - default to any
	if ($condition != "any" && $condition!= "all") {
		$condition="any";
	}

	if ($condition == "any") {
		if (($time_restrict == 0 || $open_time == "No Time") || ($user_id_restrict == 0 || $allow_user=="all") || ($user_role_restrict == 0 || $allow_role=="all")) {
			$final_restrict = 0;
		}
	}

	if ($condition == "all") {
		if (($time_restrict == 0 || $open_time == "No Time") && ($user_id_restrict == 0 || $allow_user=="all") && ($user_role_restrict == 0 || $allow_role=="all")) {
			$final_restrict = 0;
		}
	}

	if ($final_restrict == 1) {
		wp_enqueue_style( 'restrict-content', plugins_url().'/restrict-partial-content/restrict-partial.css');
		$output = '<div class="restricted-content">' .  wp_kses_data ( $message ) . '</div>';
		if ($interval >0) {
			$output .= '<div id="timer">
			<div id="timer-days"></div><div id="timer-hours"></div><div id="timer-minutes"></div><div id="timer-seconds"></div>
		</div>
			<div id="timer-message"></div>
		';
		$output .= "<script>

	function counter(total_time) {
		var d = Math.floor(total_time/86400);
		var remaining_time = total_time - (d*86400);

		var h = Math.floor(remaining_time/3600);
		var remaining_time = remaining_time - (h*3600);

		var m = Math.floor(remaining_time/60);
		var remaining_time = remaining_time - (m*60);
		var s = remaining_time;

		document.getElementById('timer-days').innerHTML = d + ' days';
		document.getElementById('timer-hours').innerHTML = h + ' hours';
		document.getElementById('timer-minutes').innerHTML = m + ' minutes';
		document.getElementById('timer-seconds').innerHTML = s + ' seconds';

		total_time--;
		if (total_time <0 ) {
			document.getElementById('timer-message').innerHTML = 'Refresh the page to view the content';
			document.getElementById('timer').style.display='none';
			document.getElementById('timer-message').style.display='inline-block';
			return;
		}
		setTimeout(function () {counter (total_time)}, 1000);

	}

	counter(".$interval.");
	</script>";
		}
	}
	else {
		$output = do_shortcode( $content );
	}
	return $output;
}
add_shortcode( 'restrict', 'render_restrict' );
