<?php
/*
Plugin Name: WP Post Styling
Plugin URI: http://www.joedolson.com/articles/wp-post-styling/
Description: Allows you to define custom styles for any specific post or page on your WordPress site. Helps reduce clutter in your stylesheet.
Version: 1.2.3
Author: Joseph Dolson
Author URI: http://www.joedolson.com/
*/
/*  Copyright 2008-2011  Joseph C Dolson  (email : wp-post-styling@joedolson.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
$jd_plugin_url = "http://www.joedolson.com/articles/wp-post-styling/";

global $wp_version, $wp_plugin_url, $wp_plugin_dir, $wp_content_url, $wp_content_dir, $wpmu_plugin_url, $wpmu_plugin_dir;


if ( version_compare( get_bloginfo( 'version' ) , '3.0' , '<' ) && is_ssl() ) {
	$wp_content_url = str_replace( 'http://' , 'https://' , get_option( 'siteurl' ) );
} else {
	$wp_content_url = get_option( 'siteurl' );
}
$wp_content_url .= '/wp-content';
$wp_content_dir = ABSPATH . 'wp-content';

if ( defined('WP_CONTENT_URL') ) {
	$wp_content_url = constant('WP_CONTENT_URL');
}
if ( defined('WP_CONTENT_DIR') ) {
	$wp_content_dir = constant('WP_CONTENT_DIR');
}
$wp_plugin_url = $wp_content_url . '/plugins';
$wp_plugin_dir = $wp_content_dir . '/plugins';
$wpmu_plugin_url = $wp_content_url . '/mu-plugins';
$wpmu_plugin_dir = $wp_content_dir . '/mu-plugins';

// Upgrade post meta
if ( get_option( 'wp_post_styling_version') ) {
$version = get_option( 'wp_post_styling_version' ); 
} else {
$version = '1.2.2'; // could be anything less, but this is the first version with an upgrade routine.
}
if ( version_compare( $version, '1.2.3',"<" )) {
	// update all post meta to match new format
	jd_fix_post_style_meta();
}
$version = '1.2.3';
update_option( 'wp_post_styling_version',$version );

// Exit if below version requirements

$exit_msg=__('WP Post Styling requires WordPress 2.5 or more recent. <a href="http://codex.wordpress.org/Upgrading_WordPress">Please update your WordPress version!</a>');

if ( version_compare( $wp_version,"2.5","<" )) {
	exit ($exit_msg);
}

function insert_new_library_style( $name, $css, $type) {
	global $wpdb;
	$name = $wpdb->escape($name);
	$css = $wpdb->escape($css);
	$type = $wpdb->escape($type);
	$table_name = $wpdb->prefix . "post_styling_library";
	$query = "INSERT INTO `$table_name` (`name`,`css`,`type`)
		VALUES ('$name','$css','$type')";
		$results = $wpdb->query($query);
	if ($results) {
	return TRUE;
	} else {
	return FALSE;
	}
}	
	
function update_library_style( $id, $name, $css, $type) {
	global $wpdb;
	$name = $wpdb->escape($name);
	$css = $wpdb->escape($css);
	$type = $wpdb->escape($type);
	$table_name = $wpdb->prefix . "post_styling_library";
	$query = "UPDATE `$table_name` SET `name`='$name',`css`='$css',`type`='$type' WHERE `id`=$id";
		$results = $wpdb->query($query);
	if ($results) {
	return TRUE;
	} else {
	return FALSE;
	}
}	

function delete_library_style( $id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . "post_styling_library";
	$query = "DELETE FROM `$table_name` WHERE `id`=$id";
		$results = $wpdb->query($query);
	if ($results) {
	return TRUE;
	} else {
	return FALSE;
	}
}		
	
function jd_create_post_styling_library_table() {
	global $wpdb;
	$post_styling_db_version = "1.0";	
	$table_name = $wpdb->prefix . "post_styling_library";
    if ( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name ) {
	$sql = "CREATE TABLE " . $table_name . " (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  name tinytext NOT NULL,
	  css text NOT NULL,
	  type VARCHAR(32) NOT NULL,
	  UNIQUE KEY id (id)
	);";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
	}	
	add_option( "post_styling_db_version", $post_styling_db_version );	
    $installed_ver = get_option( "post_styling_db_version" );
    if ( $installed_ver != $post_styling_db_version ) {
	$sql = "CREATE TABLE " . $table_name . " (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  name tinytext NOT NULL,
	  css text NOT NULL,
	  type VARCHAR(32) NOT NULL,
	  UNIQUE KEY id (id)
	);";
      //require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php'); 
    dbDelta($sql);
    update_option( "post_styling_db_version", $post_styling_db_version );
	}
}	

function jd_post_style_library_selector($current="screen") {
	// select library items from database where library is print
	global $wpdb;
	$prefix = $wpdb->prefix;
	$dbtable = $prefix . 'post_styling_library';
	$results = $wpdb->get_results(
		"SELECT `name`, `css`
		FROM `$dbtable`
		WHERE `type` = '$current'
		ORDER BY name ASC
		");
		
	if (count($results)) {
		foreach ($results as $result) {
			echo '<option value="'. htmlspecialchars($result->css).'">'. ($result->name) .'</option>'."\n";
		}
	} else {
		echo '<option value="none">'.__('Library is empty.','wp-post-styling').'</option>';
	}
}
	
function jd_post_style_data($id,$datatype) {
	// select library items from database where library is print
	global $wpdb;
	$prefix = $wpdb->prefix;
	$dbtable = $prefix . 'post_styling_library';
	$results = $wpdb->get_results(
		"SELECT `$datatype`
		FROM `$dbtable`
		WHERE `id` = '$id'
		");
	if (count($results)) {
		foreach ($results as $result) {
			return $result->$datatype;
		}
	} 		
}	
	
function jd_post_style_library_listing() {
	// select all library items from database 
	global $wpdb;
	$table = "<table id=\"wp-style-library\" summary=\"".__('Listing of CSS patterns in the Style Library.','wp-post-styling')."\">
<thead>\n<tr>\n	<th scope=\"col\">".__('Name','wp-post-styling')."</th>\n	<th scope=\"col\">".__('Styles','wp-post-styling')."</th>\n	<th scope=\"col\">".__('Type','wp-post-styling')."</th>\n	<th>".__('Delete','wp-post-styling')."</th>\n</tr>\n</thead>
<tbody>\n";
	$table_end = "</tbody>\n</table>";
	$prefix = $wpdb->prefix;
	$dbtable = $prefix . 'post_styling_library';
	$results = $wpdb->get_results(
		"SELECT `id`, `name`, `css`, `type`
		FROM `$dbtable`
		ORDER BY name ASC
		");
		
	if (count($results)) {
		foreach ($results as $result) {
			if ($odd_or_even == "odd") { $odd_or_even = "even"; } else { $odd_or_even = "odd"; }
			$table .= "<tr class=\"$odd_or_even\">\n	<td><a href=\"?page=wp-post-styling/wp-post-styling.php&amp;edit_style=".($result->id)."\">". ($result->name) ."</a></td>\n	<td>". htmlspecialchars($result->css)."</td>\n	<td>". ($result->type) ."</td>\n	<td>".'<a href="?page=wp-post-styling/wp-post-styling.php&amp;delete_style='.($result->id).'" class="delete">Delete</a></td>'."\n".'</tr>'."\n";
		}
		$write_table = TRUE;
	} else {
		$table_values = '<p>'.__('Library is empty.','wp-post-styling').'</p>';
		$write_table = FALSE;
	}
	if ($write_table == TRUE) {
		echo $table;
		echo $table_end;
	} else {
		echo $table_values;
	}
return;
}	

add_action('admin_menu','jd_add_post_styling_outer_box');
add_action('admin_menu','jd_addpost_stylingAdminPages');


function jd_add_post_styling_old_box() {
?>

<div class="dbx-b-ox-wrapper">
<fieldset id="wppoststylingdiv" class="dbx-box">
<div class="dbx-h-andle-wrapper">
<h3 class="dbx-handle"><?php _e('WP Post Styling', 'wp-post-styling', 'wp-post-styling') ?></h3>
</div>
<div class="dbx-c-ontent-wrapper">
<div class="dbx-content">
<?php
jd_add_post_styling_inner_box();
?>
</div>
</fieldset>
</div>
<?php
}
	
// Add custom Tweet field on Post & Page write/edit forms
function jd_add_post_styling_inner_box() {
	global $post;
	$post_id = $post;
	if (is_object($post_id)) {
		$post_id = $post_id->ID;
	} else {
		$post_id = $post_id;
	}
	$jd_post_styling_print = htmlspecialchars(stripcslashes(get_post_meta($post_id, '_jd_post_styling_print', true)));
	$jd_post_styling_mobile = htmlspecialchars(stripcslashes(get_post_meta($post_id, '_jd_post_styling_mobile', true)));
	$jd_post_styling_screen = htmlspecialchars(stripcslashes(get_post_meta($post_id, '_jd_post_styling_screen', true)));
	
	$jd_style_this = get_post_meta($post_id, '_jd_style_this', true);
		if ( $jd_style_this == 'disable' ) {
		$jd_selected = array(' checked="checked"','');
		} else {
		$jd_selected = array('',' checked="checked"');
		}
	$jd_box_size = get_option('jd-post-styling-boxsize');
	if ($jd_box_size == "") {
	$jd_box_size = 6;
	}
	?>
	<?php if ( get_option( 'jd-post-styling-screen' ) == '1' ) { ?>
	<p>
	<label for="jd_post_styling_screen"><?php _e('Custom Screen Styles For This Post', 'wp-post-styling'); ?></label><br /><textarea name="jd_post_styling_screen" id="jd_post_styling_screen" rows="<?php echo $jd_box_size; ?>" cols="70"><?php echo $jd_post_styling_screen; ?></textarea>
	</p>
	<p>
	<label for="jd_post_styling_screen_library"><?php _e('Custom Screen Style Library','wp-post-styling'); ?></label><br /><select id="jd_post_styling_screen_library" name="jd_post_styling_screen_library">
	<option value="none">Select library style</option>
	<?php
	jd_post_style_library_selector("screen");
	?>
	</select>
	</p>
	<?php } ?>
	
	<?php if ( get_option( 'jd-post-styling-mobile' ) == '1' ) { ?>
	<p>
	<label for="jd_post_styling_mobile"><?php _e('Custom Mobile Styles For This Post', 'wp-post-styling') ?></label><br /><textarea name="jd_post_styling_mobile" id="jd_post_styling_mobile" rows="<?php echo $jd_box_size; ?>" cols="70"><?php echo $jd_post_styling_mobile ?></textarea>
	</p>
		<p>
	<label for="jd_post_styling_mobile_library"><?php _e('Custom Mobile Style Library','wp-post-styling'); ?></label><br /><select id="jd_post_styling_mobile_library" name="jd_post_styling_mobile_library">
	<option value="none">Select library style</option>
	<?php
	jd_post_style_library_selector("mobile");
	?>	
	</select>
	</p>
	<?php } ?>
	
	<?php if ( get_option( 'jd-post-styling-print' ) == '1' ) { ?>
	<p>
	<label for="jd_post_styling_print"><?php _e('Custom Print Styles For This Post', 'wp-post-styling') ?></label><br /><textarea name="jd_post_styling_print" id="jd_post_styling_print" rows="<?php echo $jd_box_size; ?>" cols="70"><?php echo $jd_post_styling_print ?></textarea>
	</p>
	<p>
	<label for="jd_post_styling_print_library"><?php _e('Custom Print Style Library','wp-post-styling'); ?></label><br /><select id="jd_post_styling_print_library" name="jd_post_styling_print_library">
	<option value="none">Select library style</option>
	<?php
	jd_post_style_library_selector("print");
	?>
	</select>
	</p>	
	<?php } ?>
	
	<p><a target="__blank" href="http://www.joedolson.com/articles/wp-post-styling/"><?php _e('Get Support', 'wp-post-styling') ?></a> &raquo;
</p>
<p>
	<input type="radio" name="jd_style_this" value="disable"<?php echo $jd_selected[0]; ?> id="jd_style_this" /> <label for="jd_style_this">Disable custom styles on this post.</label>
	<input type="radio" name="jd_style_this" value="enable"<?php echo $jd_selected[1]; ?> id="jd_style_this_enable" /> <label for="jd_style_this_enable">Enable custom styles on this post.</label>	
</p>
<?php
}

function jd_add_post_styling_outer_box() {
	if ( function_exists( 'add_meta_box' )) {
    add_meta_box( 'poststyling_div','WP Post Styling', 'jd_add_post_styling_inner_box', 'post', 'advanced' );
	add_meta_box( 'poststyling_div','WP Post Styling', 'jd_add_post_styling_inner_box', 'page', 'advanced' );	
   } else {
    add_action('dbx_post_advanced', 'jd_add_post_styling_old_box' );
	add_action('dbx_page_advanced', 'jd_add_post_styling_old_box' );
  }
}

// Post the custom styles into the post meta table
function set_jd_post_styling( $id ) {
	if (isset($_POST['jd_post_styling_screen'])) {
		$jd_post_styling_screen = $_POST[ 'jd_post_styling_screen' ];
		$jd_post_styling_screen_library = $_POST[ 'jd_post_styling_screen_library' ];
			if ($jd_post_styling_screen_library == "none") {
				if (isset($jd_post_styling_screen) && !empty($jd_post_styling_screen)) {
				update_post_meta( $id, '_jd_post_styling_screen', $jd_post_styling_screen );
				}
			} else {
				update_post_meta( $id, '_jd_post_styling_screen', $jd_post_styling_screen_library );
			}
	}
	if (isset($_POST['jd_post_styling_print'])) {
		$jd_post_styling_print = $_POST[ 'jd_post_styling_print' ];
		$jd_post_styling_print_library = $_POST[ 'jd_post_styling_print_library' ];
			if ($jd_post_styling_print_library == "none") {	
				if (isset($jd_post_styling_print) && !empty($jd_post_styling_print)) {
				update_post_meta( $id, '_jd_post_styling_print', $jd_post_styling_print );
				}
			} else {
				update_post_meta( $id, '_jd_post_styling_print', $jd_post_styling_print_library );
			}
	}
	if (isset($_POST['jd_post_styling_mobile'])) {
		$jd_post_styling_mobile = $_POST[ 'jd_post_styling_mobile' ];
		$jd_post_styling_mobile_library = $_POST[ 'jd_post_styling_mobile_library' ];
			if ($jd_post_styling_mobile_library == "none") {	
				if (isset($jd_post_styling_mobile) && !empty($jd_post_styling_mobile)) {
				update_post_meta( $id, '_jd_post_styling_mobile', $jd_post_styling_mobile );
				}		
			} else {
				update_post_meta( $id, '_jd_post_styling_mobile', $jd_post_styling_mobile_library );
			}
	}
	if (isset($_POST['jd_style_this'])) {
		$jd_style_this = $_POST[ 'jd_style_this' ];
		if (isset($jd_style_this) && !empty($jd_style_this)) {		
			if ($jd_style_this == 'disable') {
				update_post_meta( $id, '_jd_style_this', 'disable');
			} else if ($jd_style_this == 'enable') {
				update_post_meta( $id, '_jd_style_this', 'enable');
			}
		}
	}
}

function post_jd_post_styling() {
	global $wp_query;
	$this_post = $wp_query->get_queried_object();
	if (is_object($this_post)) {
	$id = $this_post->ID;
	if ( get_post_meta( $id, '_jd_style_this', TRUE ) == 'enable' ) {
echo "<!-- Styles Added by WP Post Styling (http://www.joedolson.com/articles/wp-post-styling/) -->\n";
			if ( get_post_meta( $id, '_jd_post_styling_screen', TRUE) != '') {
			$this_post_styles = stripcslashes( get_post_meta( $id, '_jd_post_styling_screen', TRUE ) );
			echo "
<style type='text/css' media='screen'>\n
$this_post_styles\n
</style>\n";
			}
			if ( get_post_meta( $id, 'jd_post_styling_mobile', TRUE) != '' ) {
			$this_post_styles = stripcslashes( get_post_meta( $id, '_jd_post_styling_mobile', TRUE ) );
			echo "
<style type='text/css' media='handheld'>\n
$this_post_styles\n
</style>\n";
			}
			if ( get_post_meta( $id, 'jd_post_styling_print', TRUE) != '' ) {
			$this_post_styles = stripcslashes( get_post_meta( $id, '_jd_post_styling_print', TRUE ) );
			echo "
<style type='text/css' media='print'>\n
$this_post_styles\n
</style>\n";
			}	
echo "<!-- End WP Post Styling -->\n";	
	}
	}
}


// Add the administrative settings to the "Settings" menu.

function jd_addpost_stylingAdminPages() {
    if ( function_exists( 'add_submenu_page' ) ) {
		 $plugin_page = add_options_page( 'WP Post Styling', 'WP Post Styling', 'edit_pages', __FILE__, 'jd_wp_post_styling_manage_page' );
		 add_action( 'admin_head-'. $plugin_page, 'jd_addPostStylingAdminStyles' );		 
    }
 }
// Include the Manager page
function jd_wp_post_styling_manage_page() {
    include(dirname(__FILE__).'/wp-post-styling-manager.php' );
}
function jd_post_styling_plugin_action($links, $file) {
	if ($file == plugin_basename(dirname(__FILE__).'/wp-post-styling.php'))
		$links[] = "<a href='options-general.php?page=wp-post-styling/wp-post-styling.php'>" . __('Settings', 'wp-post-styling') . "</a>";
	return $links;
}

function jd_fix_post_style_meta() {
	$args = array( 'numberposts' => -1 );
	$posts = get_posts( $args );
	if ($posts) {
		foreach ( $posts as $post ) {
			$post_id = $post->ID; 
			$oldmeta = array('jd_post_styling_mobile','jd_post_styling_print','jd_post_styling_screen','jd_style_this');
			foreach ($oldmeta as $value) {
				$old_value = get_post_meta($post_id,$value,true);
				update_post_meta( $post_id, "_$value", $old_value );
				delete_post_meta( $post_id, $value );
			}
		}
	}
}

function jd_addPostStylingAdminStyles() {
global $wp_plugin_url;
	if ( $_GET['page'] == "wp-post-styling/wp-post-styling.php" ) {
		echo '<link type="text/css" rel="stylesheet" href="'.$wp_plugin_url.'/wp-post-styling/styles.css" />';
	}
}

//Add Plugin Actions and Filters to WordPress
add_filter('plugin_action_links', 'jd_post_styling_plugin_action', -10, 2);
add_action( 'save_post', 'set_jd_post_styling' );
add_action( 'wp_head','post_jd_post_styling' );
register_activation_hook(__FILE__,'jd_create_post_styling_library_table');

?>