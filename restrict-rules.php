<?php

function restrict_rule_post_type() {
	$labels = array (
			'name'               => _x( 'Restrict Rules', 'Restrict Rules', 'restrict-partial-content' ),
			'singular_name'      => _x( 'Restrict Rule', 'post type singular name', 'restrict-partial-content' ),
			'add_new'            => _x( 'Add New', 'restrict_rule', 'restrict-partial-content' ),
			'add_new_item'       => __( 'Add New Restrict Rule', 'restrict-partial-content' ),
			'edit_item'          => __( 'Edit Restrict Rules', 'restrict-partial-content' ),
			'menu_name'          => _x( 'Restrict Rules', 'admin menu', 'restrict-partial-content' ),
	);


	$restrict_rule_args = 		array(
			'labels' => $labels,
			'description' => 'Easily manage rules for restricting partial content of any page/post',
			'public' => false,
			'show_ui' => true,
			'capability_type' => 'page',
			'show_in_menu' => true,
			'menu_position' => 5,
			'supports' => array('title')	
	);
  	register_post_type( 'restrict_rule', $restrict_rule_args);
}
add_action( 'init', 'restrict_rule_post_type' );

function restrict_rule_columns($columns) {
	unset(
		$columns['date']
		);
	$new_columns = array(
		'shortcode' => "Shortcode"
	);
	return array_merge($columns, $new_columns);
}
add_filter('manage_restrict_rule_posts_columns' , 'restrict_rule_columns');

function restrict_rule_shortcode_column($column, $post_id) {
	switch ( $column ) {
		case 'shortcode' :
			echo '[restrict rule="'.$post_id.'"]';
			break;
	}
}
add_action( 'manage_restrict_rule_posts_custom_column' , 'restrict_rule_shortcode_column', 10, 2 );

function add_restrict_rule_metaboxes() {
	add_meta_box ( 'restrict_rule', 'Select Options', 'get_restrict_rule', 'restrict_rule', 'normal', 'default' );
}
add_action( 'add_meta_boxes', 'add_restrict_rule_metaboxes' );

function get_restrict_rule () {
	// Allow role section for the rules
	global $wp_roles;
	$post_id = get_the_ID();
	$current_allow_role = get_post_meta($post_id, "allow_role", true);
	echo '<style>
			.restrict-rule-meta-box {min-height: 400px; }
			.restrict-rule-meta-box label {font-weight: bold;}
			.restrict-rule-meta-box .small-text {font-size: 10px;}
			.restrict-rule-meta-box .left-side {width: 240px; float: left;}
			.restrict-rule-meta-box .right-side {width: 400px; float: left;}
		</style>';
	echo '<div class="restrict-rule-meta-box">
			<div class="left-side">';
			
	//Create the none field for security
	wp_nonce_field('restrict_partial_rule_action', 'restrict_partial_rule');
	
	//Allow user id/name section
	$current_allow_user = get_post_meta($post_id, "allow_user", true);
	$site_users = get_users('orderby=login');
	$current_allowed_users = array();
	if (isset($current_allow_user)) {
		$current_allowed_users = explode(",", $current_allow_user);
	}
	
	echo '<p><label>Allow Users</label></p><p style="overflow-y:scroll; height: 350px; width: 200px;">';
	foreach ($site_users as $site_user) {
		echo '<input type="checkbox" name="allow_user[]" value="'.$site_user->user_login.'" '.(in_array($site_user->user_login, $current_allowed_users)? 'checked':'').' /> '.$site_user->user_login.' <br />';
	}
	echo '</p>';
	echo '</div> <!-- left side end --> <div class="right-side">';
	echo '<label>Allow Role </label><select name="allow_role" id="allow_role">';
	echo '<option value=""></option>';
	foreach ($wp_roles->roles as $value=>$current_wp_role) {
		echo '<option value="'.$value.'" '.($value == $current_allow_role ? 'selected':' ').'>'.$current_wp_role['name'].'</option>';
	}
	echo '</select>';
	
	
	//Time restriction section
	$current_open_time = get_post_meta($post_id, "open_time", true);
	echo '<p>
		<label>Open Time</label> <br />
		<span class="small-text">Put in the following format: YYYY-MM-DD HH::MM:SS - example 2016-03-31 16:00:09</span> <br />
		<input name="open_time" id="open_time" type="text" '.(isset($current_open_time) ? 'value="'.$current_open_time.'"' : '').'>';
	
	//Condition section
	$current_condition = get_post_meta($post_id, "open_condition", true);
	echo '<p>
		<label>Condition</label>
		<input name="open_condition" id="open_condition" type="radio" value="any" '.(isset($current_condition) && $current_condition=="any" ? 'checked':'').'> Any
		&nbsp; &nbsp; &nbsp;<input name="open_condition" id="open_condition" type="radio" value="all" '.(isset($current_condition) && $current_condition=="all" ? 'checked':'').'> All';

	//Restriction Message
	$current_message = get_post_meta($post_id, "restrict_message", true);
	wp_editor( $current_message, 'restrict_message2', array('teeny'=>true, 'media_buttons'=>false, 'textarea_rows'=>3, 'textarea_name' => 'restrict_message') );
	
	echo '</div> <!-- right side end -->
		</div>';
}

function save_restrict_rule($post_id, $post) {
	//Only go ahead if it is a restrict rule post type and the nonce matches
	if ( 'restrict_rule' != $post->post_type) {
		return;
		//wp_nonce_ays('restrict_partial_rule_action');
    }
	
	if ( !isset( $_POST['restrict_partial_rule'] ) || !wp_verify_nonce( $_POST['restrict_partial_rule'], 'restrict_partial_rule_action') ) {
		wp_nonce_ays('restrict_partial_rule_action');
	}
	
	//Save allow role
	if(get_post_meta($post->ID, 'allow_role', FALSE)) {
		update_post_meta($post->ID, 'allow_role', $_POST['allow_role']);
	}
	else {
		add_post_meta($post->ID, 'allow_role', $_POST['allow_role']);
	}
	
	//Save allow user
	if (isset($_POST['allow_user'])) {
		$allow_user_string = implode(",", $_POST['allow_user']);
	}
	if(get_post_meta($post->ID, 'allow_user', FALSE)) {
		update_post_meta($post->ID, 'allow_user', $allow_user_string);
	}
	else {
		add_post_meta($post->ID, 'allow_user', $allow_user_string);
	}
	
	//Save open time
	if(get_post_meta($post->ID, 'open_time', FALSE)) {
		update_post_meta($post->ID, 'open_time', $_POST['open_time']);
	}
	else {
		add_post_meta($post->ID, 'open_time', $_POST['open_time']);
	}
	
	//Save condition
	if(get_post_meta($post->ID, 'open_condition', FALSE)) {
		update_post_meta($post->ID, 'open_condition', $_POST['open_condition']);
	}
	else {
		add_post_meta($post->ID, 'open_condition', $_POST['open_condition']);
	}
	
	//Save Message
	if(get_post_meta($post->ID, 'restrict_message', FALSE)) {
		update_post_meta($post->ID, 'restrict_message', $_POST['restrict_message']);
	}
	else {
		add_post_meta($post->ID, 'restrict_message', $_POST['restrict_message']);
	}
}
add_action('save_post', 'save_restrict_rule', 1, 2);

