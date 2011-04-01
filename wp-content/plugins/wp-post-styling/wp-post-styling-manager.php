<?php
// Set Default Options
	if( get_option( 'post-styling-initial') != '1' ) {
		update_option( 'jd-post-styling-screen', '1' );
		update_option( 'post-styling-initial', '1' );
		update_option( 'jd-posts-styling-default', 'enable' );
		update_option( 'jd-post-styling-boxsize', '6' );
	}
		
	if ( $_POST['submit-type'] == 'options' ) {
		//UPDATE OPTIONS
		update_option( 'jd-post-styling-screen', $_POST['jd-post-styling-screen'] );
		update_option( 'jd-post-styling-mobile', $_POST['jd-post-styling-mobile'] );
		update_option( 'jd-post-styling-print', $_POST['jd-post-styling-print'] );
		update_option( 'jd-post-styling-default', $_POST['jd-post-styling-default'] );
		update_option( 'jd-post-styling-boxsize', $_POST['jd-post-styling-boxsize'] );
		$message = __("WP Post Styling Options Updated",'wp-post-styling');

	} 
	if ( $_POST['submit-type'] == 'library' ) {
		if ( (($_POST[ 'jd_style_library_name' ] == "") || ($_POST[ 'jd_style_library_css' ] == "") || ($_POST[ 'jd_style_library_type' ] == "")) && !isset($_POST['delete_style']) ) {
		$message = "<ul>";
			if ( $_POST[ 'jd_style_library_name' ] == "" ) {
			$message .= "<li>" . __("Please enter a name for this Style Library record.",'wp-post-styling') . "</li>";
			}
			if ( $_POST[ 'jd_style_library_css' ] == "" ) {
			$message .= "<li>" . __("Please enter styling instructions for this Style Library record.",'wp-post-styling') . "</li>";		
			}
			if ( $_POST[ 'jd_style_library_type' ] == "" ) {
			$message .= "<li>" . __("Please select a type for this Style Library record.",'wp-post-styling') . "</li>";		
			}	
        $message .= "</ul>";
		} else {
			if (isset($_POST['edit_style'])) {
			$results = update_library_style( $_POST['edit_style'], $_POST[ 'jd_style_library_name' ], $_POST[ 'jd_style_library_css' ], $_POST[ 'jd_style_library_type' ]);			
			$type = "update";
			} elseif (isset($_POST['delete_style'])) {
            $results = delete_library_style( $_POST['delete_style'] );
			$type = "delete";
			} else {
			$type = "insert";
			$results = insert_new_library_style( $_POST[ 'jd_style_library_name' ], $_POST[ 'jd_style_library_css' ], $_POST[ 'jd_style_library_type' ]);
			}
			if ( $results == TRUE ) {
				if ( $type == "update" ) {
				$message = __("WP Post Styling Library Updated",'wp-post-styling');
				} elseif ( $type == "delete" ) {
				$message = __("Record Deleted from WP Post Styling Library",'wp-post-styling');				
				} elseif ( $type == "insert" ) {
				$message = __("Recorded Added to WP Post Styling Library",'wp-post-styling');				
				}
			} else {
			$message = __("WP Post Styling Library Update Failed",'wp-post-styling');
			}
		}
	}
	if (isset($_GET['delete_style'])) {
		$delete_style = (int) $_GET['delete_style'];
		$message = __("Are you sure you want to delete this record?",'wp-post-styling');
		$message .= "<form method=\"post\" action=\"?page=wp-post-styling/wp-post-styling.php\">
		<div>
		<input type=\"hidden\" name=\"delete_style\" value=\"$delete_style\" />
		<input type=\"hidden\" name=\"submit-type\" value=\"library\" />
		<input type=\"submit\" name=\"submit\" class=\"button-primary\" value=\"".__('Yes, delete it!',"wp-post-styling")."\" />
		</div>
		</form>";
	}
	// FUNCTION to see if checkboxes should be checked
	if ( !function_exists('jc_checkCheckbox') ) {
		function jd_checkCheckbox( $theFieldname ){
			if( get_option( $theFieldname ) == '1'){
				echo 'checked="checked"';
			}
		}
	}
?>
<?php if ($message) : ?>
<div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
<?php endif; ?>
<div id="dropmessage" class="updated" style="display:none;"></div>

	<?php if ( version_compare( $wp_version,"2.7",">" )) {
	echo "<div class=\"wrap\">";
	} ?>

<div id="wp-post-styling">
<h2><?php _e("WP Post Styling Settings", 'wp-post-styling'); ?></h2>

<div class="resources">
<ul>
<li><a href="http://www.joedolson.com/articles/wp-post-styling/"><?php _e("Get Support",'wp-post-styling'); ?></a></li>
<li><form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<div>
<input type="hidden" name="cmd" value="_s-xclick" />
<input type="hidden" name="hosted_button_id" value="5C4T2NCL4GEBE" />
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif" name="submit" alt="Donate!" />
<img alt="" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
</div>
</form>
</li>
</ul>

</div>

<p>
<?php _e("This plugin offers the possibility of adding up to three additional fields to your posting interface for adding styles. Usually, you'll probably only need to add custom screen styles, but you can also choose to add mobile or print media styles for each post, if your default style sheets don't cover this."); ?>
</p>
<p>
<?php _e("Note that the styles you assign a given post using this plugin will only apply to that post's individual post page, and will <em>not</em> be applied on any archive pages."); ?>
</p>
<div class="post-styling-options">
<h2><?php _e('General Settings','wp-post-styling'); ?></h2>
	<form method="post" action="">
		<fieldset>
			<legend><?php _e('WordPress Post Styling Options','wp-post-styling'); ?></legend>
			<p>
				<input type="checkbox" name="jd-post-styling-screen" id="jd-post-styling-screen" value="1" <?php jd_checkCheckbox('jd-post-styling-screen')?> />
				<label for="jd-post-styling-screen"><strong><?php _e('Add Custom Screen Styles','wp-post-styling'); ?></strong></label>
			</p>
			<p>
				<input type="checkbox" name="jd-post-styling-mobile" id="jd-post-styling-mobile" value="1" <?php jd_checkCheckbox('jd-post-styling-mobile')?> />
				<label for="jd-post-styling-mobile"><strong><?php _e('Add Custom Mobile Styles','wp-post-styling'); ?></strong></label>
			</p>
			<p>				
				<input type="checkbox" name="jd-post-styling-print" id="jd-post-styling-print" value="1" <?php jd_checkCheckbox('jd-post-styling-print')?> />
				<label for="jd-post-styling-print"><strong><?php _e('Add Custom Print Styles','wp-post-styling'); ?></strong></label>
			</p>
			<p>				
				<input type="checkbox" name="jd-post-styling-default" id="jd-post-styling-default" value="disable" <?php jd_checkCheckbox('jd-post-styling-default')?> />
				<label for="jd-post-styling-default"><strong><?php _e('Disable Custom Styles as default condition','wp-post-styling'); ?></strong></label>				
			</p>
			<p>				
				<input type="text" name="jd-post-styling-boxsize" id="jd-post-styling-boxsize" value="<?php echo get_option('jd-post-styling-boxsize'); ?>" size="3" />
				<label for="jd-post-styling-boxsize"><strong><?php _e('Size of custom style text box (in lines.)','wp-post-styling'); ?></strong></label>				
			</p>
		</fieldset>
		<div>
		<input type="hidden" name="submit-type" value="options" />
		</div>
		<p>
		<input type="submit" name="submit" class="button-primary"  value="<?php _e('Save WP Post Styling Options','wp-post-styling'); ?>" />
		</p>
	</form>
</div>
<div class="post-styling-library">
<h2><?php _e('Custom Style Library','wp-post-styling'); ?></h2>
	<form method="post" action="">
	<?php if (isset($_GET['edit_style']))  { ?>
	<div><input type="hidden" name="edit_style" value="<?php echo (int) $_GET['edit_style']; ?>" /></div>
	<?php } ?>
		<fieldset>
		<legend><?php if (!isset($_GET['edit_style'])) { _e('Add Custom Style to Library','wp-post-styling'); } else { _e('Edit Custom Styles','wp-post-styling'); } ?></legend>
		<p>
		<label for="jd_style_library_name"><?php _e('Style Name','wp-post-styling'); ?></label><br /><input type="text" name="jd_style_library_name" id="jd_style_library_name" value="<?php if (isset($_GET['edit_style'])) { $id = (int) $_GET['edit_style']; echo jd_post_style_data($id,"name"); } ?>" size="40" />
		</p>
		<p>
		<label for="jd_style_library_css"><?php _e('CSS','wp-post-styling'); ?></label><br /><textarea name="jd_style_library_css" id="jd_style_library_css" rows="6" cols="50"><?php if (isset($_GET['edit_style'])) { $id = (int) $_GET['edit_style']; echo stripcslashes( jd_post_style_data( $id,"css" ) ); } ?></textarea>
		</p>
		<p>
		<label for="jd_style_library_type"><?php _e('Library Type','wp-post-styling'); ?></label> <select name="jd_style_library_type" id="jd_style_library_type"><option value="screen"<?php if (isset($_GET['edit_style'])) { $id = (int) $_GET['edit_style']; $type = jd_post_style_data($id,"type");  if($type == "screen") { echo " selected=\"selected\""; } } ?>><?php _e('Screen'); ?></option><option value="mobile"<?php if (isset($_GET['edit_style'])) { $id = (int) $_GET['edit_style']; $type = jd_post_style_data($id,"type");  if($type == "mobile") { echo " selected=\"selected\""; } } ?>><?php _e('Mobile'); ?></option><option value="print"<?php if (isset($_GET['edit_style'])) { $id = (int) $_GET['edit_style']; $type = jd_post_style_data($id,"type");  if($type == "print") { echo " selected=\"selected\""; } } ?>><?php _e('Print'); ?></option></select>
		</p>
		</fieldset>
		<div>
		<input type="hidden" name="submit-type" value="library" />
		</div>	
	<p>
	<input type="submit" name="submit" class="button-primary" value="<?php if (!isset($_GET['edit_style'])) {  _e('Add to WP Post Styling Library','wp-post-styling'); } else { _e('Update WP Post Styling Library','wp-post-styling'); }?>" />	
	</p>
	</form>
<?php if (isset($_GET['edit_style'])) { echo "<p><a href=\"?page=wp-post-styling/wp-post-styling.php\">"; _e('Add New Style','wp-post-styling'); echo "</a></p>"; } ?>
</div>

<div class="post-styling-entries">
<h2><?php _e('Your Style Library','wp-post-styling'); ?></h2>

<?php jd_post_style_library_listing(); ?>

<p>
<?php _e('Note: editing the styles in your style library will not effect any previously published posts using those styles.','wp-post-styling'); ?>
</p>
</div>
<?php 
global $wp_version; 
if ( version_compare( $wp_version,"2.7",">" )) {
echo "</div>";
} 
?>