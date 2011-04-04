<?php
/*
    Plugin Name: vSlider
    Plugin URI: http://www.vibethemes.com/wordpress-plugins/vslider-wordpress-image-slider-plugin/
    Description: Implementing a featured image gallery into your WordPress theme has never been easier! Showcase your portfolio, animate your header or manage your banners with vSlider. vslider by  <a href="http://www.vibethemes.com/" title="premium wordpress themes">VibeThemes</a>.
    Author: VibeThemes.com
    Version: 3.0
    Author URI: http://www.vibethemes.com/

	vSlider is released under GPL:
	http://www.opensource.org/licenses/gpl-license.php
*/

// IF NOT DEFINED, WE DEFINE THE URL PATH TO WP-CONTENT DIRECTORY
if (!defined('WP_CONTENT_URL')) {
	define('WP_CONTENT_URL', get_option('siteurl').'/wp-content');
}

//	DEFINE vslider DEFAULTS (filterable)
$vslider_defaults = apply_filters('vslider_defaults', array(
	'width' => 630,
	'height' => 280,
	'spw' => 7,
	'sph' => 5,
	'delay' => 3000,
	'sDelay' => 30,
	'opacity' => '0.7',
	'titleSpeed' => 1500,
	'effect' => '',
	'navigation' => 'true',
	'links' => 'true',
	'hoverPause' => 'false',
	'fontFamily' => 'Arial, Helvetica, sans-serif',
	'titleFont' => 16,
	'fontSize' => 12,
	'textColor' => 'FFFFFF',
	'bgColor' => '222222',
	'customImg' => 'true',
	'postNr' => 3,
	'chars' => 200,
	'excerpt' => 'true',
	'slide1' => WP_CONTENT_URL.'/plugins/vslider/images/slide1.jpg',
	'slide2' => WP_CONTENT_URL.'/plugins/vslider/images/slide2.jpg',
	'slide3' => WP_CONTENT_URL.'/plugins/vslider/images/slide3.jpg'
));

//	PULL THE SETTINGS FROM THE DB
$vs_settings = get_option('vslider_settings');

//	FALLBACK
$vs_settings = wp_parse_args($vs_settings, $vslider_defaults);

//	REGISTER SETTINGS IN THE DB
add_action('admin_init', 'vslider_register_settings');
function vslider_register_settings() {
	register_setting('vslider_settings', 'vslider_settings', 'vslider_settings_validate');
}
//	ADD OPTIONS PAGE TO APPEARANCE TAB
add_action('admin_menu', 'add_vslider_menu');
function add_vslider_menu() {
    add_menu_page('vSlider', 'vSlider', 'manage_options', 'vslider', 'vslider_admin_page', WP_CONTENT_URL.'/plugins/vslider/images/icon.png');
    add_submenu_page('vslider','WordPress Themes', 'WordPress Themes', 'manage_options', 'vslider-themes', 'vslider_theme_page');
    add_submenu_page('vslider','Tutorials', 'Tutorials', 'manage_options', 'vslider-tutorials', 'vslider_tutorials_page');

}

// GENERATE THE SETTINGS PAGE
function vslider_admin_page() { ?>
	<div class="wrap"><div id="icon-options-general" class="icon32"><br /></div><h2>vSlider 3.0 Settings Page</h2><style type="text/css">.metabox-holder { width: 35%; float: left; margin: 0; padding: 0 10px 0 0; }.metabox-holder .postbox .inside { padding: 0 10px; }</style>
        <?php $url = 'http://www.vibethemes.com/vslidersetup/vslider-msg.php'; $request = new WP_Http; $result = $request->request( $url ); $json = $result['body']; echo $json; ?>
	    <?php echo vslider_settings_admin(); ?>
	</div>
<?php }

// GENERATE THEME SHOWCASE PAGE
function vslider_theme_page() { ?>
	<div class="wrap"><div id="icon-themes" class="icon32"><br /></div><h2>vSlider Themes Page</h2>
        <?php $url = 'http://www.vibethemes.com/vslidersetup/vslider-themes.php'; $request = new WP_Http; $result = $request->request( $url ); $json = $result['body']; echo $json; ?>
	</div>
<?php }

// GENERATE THE TUTORIAL PAGE
function vslider_tutorials_page() { ?>
	<div class="wrap"><div id="icon-users" class="icon32"><br /></div><h2>vSlider Tutorials</h2>
        <?php $url = 'http://www.vibethemes.com/vslidersetup/vslider-tutorials.php'; $request = new WP_Http; $result = $request->request( $url ); $json = $result['body']; echo $json; ?>
	</div>
<?php }

//	DISPLAY "UPDATED" MESSAGE IF SETTINGS ARE UPDATED
function vslider_settings_update_check() {
	global $vs_settings;
	if(isset($vs_settings['update'])) {
		echo '<div class="updated fade" id="message"><p>vslider Settings <strong>'.$vs_settings['update'].'</strong></p></div>';
		unset($vs_settings['update']);
		update_option('vslider_settings', $vs_settings);
	}
}

//	SANITIZE SETTINGS FOR STORAGE
function vslider_settings_validate($input) {
	$input['width'] = intval($input['width']);
	$input['height'] = intval($input['height']);
	return $input;
}

//	DISPLAY THE SETTINGS PAGE
function vslider_settings_admin() { ?>
	<?php vslider_settings_update_check(); ?>
	<form method="post" action="options.php">
	<?php settings_fields('vslider_settings'); ?>
	<?php global $vs_settings; $options = $vs_settings; ?>

	<!-- SETTINGS PAGE FIRST COLUMN -->
	<div class="metabox-holder">

		<div class="postbox">
		<h3><?php _e("General Setings", 'vslider'); ?></h3>
			<div class="inside">
                <p><?php _e("Image width", 'vslider'); ?>:<input type="text" name="vslider_settings[width]" value="<?php echo $options['width'] ?>" size="3" />px&nbsp;&nbsp;<?php _e("height", 'vslider'); ?>:<input type="text" name="vslider_settings[height]" value="<?php echo $options['height'] ?>" size="3" />px</p>
                <p><?php _e("Squares per width", 'vslider'); ?>:<input type="text" name="vslider_settings[spw]" value="<?php echo $options['spw'] ?>" size="3" />&nbsp;&nbsp;<?php _e("per height", 'vslider'); ?>:<input type="text" name="vslider_settings[sph]" value="<?php echo $options['sph'] ?>" size="3" /></p>
                <p><?php _e("Delay between images", 'vslider'); ?>:<input type="text" name="vslider_settings[delay]" value="<?php echo $options['delay'] ?>" size="3" />&nbsp;in ms</p>
                <p><?php _e("Delay beetwen squares", 'vslider'); ?>:<input type="text" name="vslider_settings[sDelay]" value="<?php echo $options['sDelay'] ?>" size="3" />&nbsp;in ms</p>
                <p><?php _e("Opacity of title and navigation", 'vslider'); ?>:<input type="text" name="vslider_settings[opacity]" value="<?php echo $options['opacity'] ?>" size="3" /></p>
                <p><?php _e("Speed of title appereance", 'vslider'); ?>:<input type="text" name="vslider_settings[titleSpeed]" value="<?php echo $options['titleSpeed'] ?>" size="3" />&nbsp;in ms</p>
                <p><?php _e("Effect", 'vslider'); ?>:<select name="vslider_settings[effect]"><option value="" <?php selected('', $options['effect']); ?>>all combined</option><option value="random" <?php selected('random', $options['effect']); ?>>random</option><option value="swirl" <?php selected('swirl', $options['effect']); ?>>swirl</option><option value="rain" <?php selected('rain', $options['effect']); ?>>rain</option><option value="straight" <?php selected('straight', $options['effect']); ?>>straight</option></select></p>
                <p><?php _e("Show navigation buttons", 'vslider'); ?>:<select name="vslider_settings[navigation]"><option value="true" <?php selected('true', $options['navigation']); ?>>Yes</option><option value="false" <?php selected('false', $options['navigation']); ?>>No</option></select></p>
                <p><?php _e("Show images as links ", 'vslider'); ?>:<select name="vslider_settings[links]"><option value="true" <?php selected('true', $options['links']); ?>>Yes</option><option value="false" <?php selected('false', $options['links']); ?>>No</option></select></p>
                <p><?php _e("Pause on mouse hover", 'vslider'); ?>:<select name="vslider_settings[hoverPause]"><option value="true" <?php selected('true', $options['hoverPause']); ?>>Yes</option><option value="false" <?php selected('false', $options['hoverPause']); ?>>No</option></select></p>
                <p><?php _e("Font family", 'vslider'); ?>:<select name="vslider_settings[fontFamily]"><option value="'Trebuchet MS', Helvetica, sans-serif" <?php selected("'Trebuchet MS', Helvetica, sans-serif", $options['fontFamily']); ?>>'Trebuchet MS', Helvetica, sans-serif</option><option value="Arial, Helvetica, sans-serif" <?php selected('Arial, Helvetica, sans-serif', $options['fontFamily']); ?>>Arial, Helvetica, sans-serif</option><option value="Tahoma, Geneva, sans-serif" <?php selected('Tahoma, Geneva, sans-serif', $options['fontFamily']); ?>>Tahoma, Geneva, sans-serif</option><option value="Verdana, Geneva, sans-serif" <?php selected('Verdana, Geneva, sans-serif', $options['fontFamily']); ?>>Verdana, Geneva, sans-serif</option><option value="Georgia, serif" <?php selected('Georgia, serif', $options['fontFamily']); ?>>Georgia, serif</option><option value="'Arial Black', Gadget, sans-serif" <?php selected("'Arial Black', Gadget, sans-serif", $options['fontFamily']); ?>>'Arial Black', Gadget, sans-serif</option><option value="'Bookman Old Style', serif" <?php selected("'Bookman Old Style', serif", $options['fontFamily']); ?>>'Bookman Old Style', serif</option><option value="'Comic Sans MS', cursive" <?php selected("'Comic Sans MS', cursive", $options['fontFamily']); ?>>'Comic Sans MS', cursive</option><option value="'Courier New', Courier, monospace" <?php selected("'Courier New', Courier, monospace", $options['fontFamily']); ?>>'Courier New', Courier, monospace</option><option value="Garamond, serif" <?php selected("Garamond, serif", $options['fontFamily']); ?>>Garamond, serif</option><option value="'Times New Roman', Times, serif" <?php selected("'Times New Roman', Times, serif", $options['fontFamily']); ?>>'Times New Roman', Times, serif</option><option value="Impact, Charcoal, sans-serif" <?php selected("Impact, Charcoal, sans-serif", $options['fontFamily']); ?>>Impact, Charcoal, sans-serif</option><option value="'Lucida Console', Monaco, monospace" <?php selected("'Lucida Console', Monaco, monospace", $options['fontFamily']); ?>>'Lucida Console', Monaco, monospace</option><option value="'MS Sans Serif', Geneva, sans-serif" <?php selected("'MS Sans Serif', Geneva, sans-serif", $options['fontFamily']); ?>>'MS Sans Serif', Geneva, sans-serif</option></select></p>
                <p><?php _e("Title font size", 'vslider'); ?>:<input type="text" name="vslider_settings[titleFont]" value="<?php echo $options['titleFont'] ?>" size="3" />px</p>
                <p><?php _e("Text font size", 'vslider'); ?>:<input type="text" name="vslider_settings[fontSize]" value="<?php echo $options['fontSize'] ?>" size="3" />px</p>
                <p><?php _e("Text color", 'vslider'); ?>:<input id="textColor" type="text" name="vslider_settings[textColor]" value="<?php echo $options['textColor'] ?>" size="8" />&nbsp;HEX</p>
                <p><?php _e("Background color", 'vslider'); ?>:<input id="bgColor" type="text" name="vslider_settings[bgColor]" value="<?php echo $options['bgColor'] ?>" size="8" />&nbsp;HEX</p>
                <small><?php _e("Click on the text box to pick a color", 'vslider'); ?></small>
                <input type="hidden" name="vslider_settings[update]" value="UPDATED" />
                <p><input type="submit" class="button" value="<?php _e('Save Settings') ?>" /></p>
			</div>
		</div>

	</div>
	<!-- END FIRST COLUMN -->


	<!-- SETTINGS PAGE SECONT COLUMN -->
	<div class="metabox-holder">

		<div class="postbox">
		<h3><?php _e("Images Source", 'vslider'); ?></h3>
			<div class="inside">
                <p><?php _e("Use custom images", 'vslider'); ?>?&nbsp;<select name="vslider_settings[customImg]"><option value="true" <?php selected('true', $options['customImg']); ?>>Yes, Custom</option><option value="false" <?php selected('false', $options['customImg']); ?>>No, a Category</option></select></p>
				<p><?php _e("Select a Category:", 'vslider'); ?><br /><?php wp_dropdown_categories(array('selected' => $options['imgCat'], 'name' => 'vslider_settings[imgCat]', 'orderby' => 'Name' , 'hierarchical' => 1, 'show_option_all' => __("All Categories", 'vslider'), 'hide_empty' => '0' )); ?></p>
                <p><?php _e("No. of posts/images", 'vslider'); ?>:<input type="text" name="vslider_settings[postNr]" value="<?php echo $options['postNr'] ?>" size="3" /></p>
                <p><?php _e("Display post excerpt", 'vslider'); ?>?&nbsp;<select name="vslider_settings[excerpt]"><option value="true" <?php selected('true', $options['excerpt']); ?>>Yes</option><option value="false" <?php selected('false', $options['excerpt']); ?>>No</option></select>&nbsp;<?php _e("No. of chars", 'vslider'); ?>:<input type="text" name="vslider_settings[chars]" value="<?php echo $options['chars'] ?>" size="3" /></p>
                <input type="hidden" name="vslider_settings[update]" value="UPDATED" />
                <p><input type="submit" class="button" value="<?php _e('Save Settings') ?>" /></p>
			</div>
		</div>

		<div class="postbox">
		<h3><?php _e("Custom Image 1", 'vslider'); ?><div id="click1" style="float:right;cursor:pointer;">click to expand</div></h3>
			<div class="inside" id="slide1" style="display:none;">
				<p><strong><?php _e("Image #1", 'vslider'); ?></strong> <?php _e("URL link", 'vslider'); ?>:<br /><input type="text" name="vslider_settings[slide1]" value="<?php echo $options['slide1'] ?>" size="45" /><br />
				<?php _e("Image links to", 'vslider'); ?>:<br /><input type="text" name="vslider_settings[link1]" value="<?php echo $options['link1'] ?>" size="45" /><br />
				<?php _e("Heading text", 'vslider'); ?>:<br /><input type="text" name="vslider_settings[heading1]" value="<?php echo $options['heading1'] ?>" size="45" /><br />
				<?php _e("Description text", 'vslider'); ?>:<br /><textarea name="vslider_settings[desc1]" cols=37 rows=3><?php echo $options['desc1'] ?></textarea>
                <input type="hidden" name="vslider_settings[update]" value="UPDATED" />
                <p><input type="submit" class="button" value="<?php _e('Save Settings') ?>" /></p>
                </p>
			</div>
		</div>

		<div class="postbox">
		<h3><?php _e("Custom Image 2", 'vslider'); ?><div id="click2" style="float:right;cursor:pointer;">click to expand</div></h3>
			<div class="inside" id="slide2" style="display:none;">
				<p><strong><?php _e("Image #2", 'vslider'); ?></strong> <?php _e("URL link", 'vslider'); ?>:<br /><input type="text" name="vslider_settings[slide2]" value="<?php echo $options['slide2'] ?>" size="45" /><br />
				<?php _e("Image links to", 'vslider'); ?>:<br /><input type="text" name="vslider_settings[link2]" value="<?php echo $options['link2'] ?>" size="45" /><br />
				<?php _e("Heading text", 'vslider'); ?>:<br /><input type="text" name="vslider_settings[heading2]" value="<?php echo $options['heading2'] ?>" size="45" /><br />
				<?php _e("Description text", 'vslider'); ?>:<br /><textarea name="vslider_settings[desc2]" cols=37 rows=3><?php echo $options['desc2'] ?></textarea>
                <input type="hidden" name="vslider_settings[update]" value="UPDATED" />
                <p><input type="submit" class="button" value="<?php _e('Save Settings') ?>" /></p>
                </p>
			</div>
		</div>

		<div class="postbox">
		<h3><?php _e("Custom Image 3", 'vslider'); ?><div id="click3" style="float:right;cursor:pointer;">click to expand</div></h3>
			<div class="inside" id="slide3" style="display:none;">
				<p><strong><?php _e("Image #3", 'vslider'); ?></strong> <?php _e("URL link", 'vslider'); ?>:<br /><input type="text" name="vslider_settings[slide3]" value="<?php echo $options['slide3'] ?>" size="45" /><br />
				<?php _e("Image links to", 'vslider'); ?>:<br /><input type="text" name="vslider_settings[link3]" value="<?php echo $options['link3'] ?>" size="45" /><br />
				<?php _e("Heading text", 'vslider'); ?>:<br /><input type="text" name="vslider_settings[heading3]" value="<?php echo $options['heading3'] ?>" size="45" /><br />
				<?php _e("Description text", 'vslider'); ?>:<br /><textarea name="vslider_settings[desc3]" cols=37 rows=3><?php echo $options['desc3'] ?></textarea>
                <input type="hidden" name="vslider_settings[update]" value="UPDATED" />
                <p><input type="submit" class="button" value="<?php _e('Save Settings') ?>" /></p>
                </p>
			</div>
		</div>

		<div class="postbox">
		<h3><?php _e("Custom Image 4", 'vslider'); ?><div id="click4" style="float:right;cursor:pointer;">click to expand</div></h3>
			<div class="inside" id="slide4" style="display:none;">
				<p><strong><?php _e("Image #4", 'vslider'); ?></strong> <?php _e("URL link", 'vslider'); ?>:<br /><input type="text" name="vslider_settings[slide4]" value="<?php echo $options['slide4'] ?>" size="45" /><br />
				<?php _e("Image links to", 'vslider'); ?>:<br /><input type="text" name="vslider_settings[link4]" value="<?php echo $options['link4'] ?>" size="45" /><br />
				<?php _e("Heading text", 'vslider'); ?>:<br /><input type="text" name="vslider_settings[heading4]" value="<?php echo $options['heading4'] ?>" size="45" /><br />
				<?php _e("Description text", 'vslider'); ?>:<br /><textarea name="vslider_settings[desc4]" cols=37 rows=3><?php echo $options['desc4'] ?></textarea>
                <input type="hidden" name="vslider_settings[update]" value="UPDATED" />
                <p><input type="submit" class="button" value="<?php _e('Save Settings') ?>" /></p>
                </p>
			</div>
		</div>

		<div class="postbox">
		<h3><?php _e("Custom Image 5", 'vslider'); ?><div id="click5" style="float:right;cursor:pointer;">click to expand</div></h3>
			<div class="inside" id="slide5" style="display:none;">
				<p><strong><?php _e("Image #5", 'vslider'); ?></strong> <?php _e("URL link", 'vslider'); ?>:<br /><input type="text" name="vslider_settings[slide5]" value="<?php echo $options['slide5'] ?>" size="45" /><br />
				<?php _e("Image links to", 'vslider'); ?>:<br /><input type="text" name="vslider_settings[link5]" value="<?php echo $options['link5'] ?>" size="45" /><br />
				<?php _e("Heading text", 'vslider'); ?>:<br /><input type="text" name="vslider_settings[heading5]" value="<?php echo $options['heading5'] ?>" size="45" /><br />
				<?php _e("Description text", 'vslider'); ?>:<br /><textarea name="vslider_settings[desc5]" cols=37 rows=3><?php echo $options['desc5'] ?></textarea>
                <input type="hidden" name="vslider_settings[update]" value="UPDATED" />
                <p><input type="submit" class="button" value="<?php _e('Save Settings') ?>" /></p>
                </p>
			</div>
		</div>

        </form>
        <p><a href="<?php echo bloginfo( 'url' ) ?>/wp-admin/admin.php?page=vslider-themes" class="button-primary"><?php _e("Compatible WordPress Themes", 'vslider'); ?></a>&nbsp;&nbsp;&nbsp;<a href="<?php echo bloginfo( 'url' ) ?>/wp-admin/admin.php?page=vslider-tutorials" class="button"><?php _e("Tutorials", 'vslider'); ?></a></p><p><img style="vertical-align:middle" src="<?php echo WP_CONTENT_URL ?>/plugins/vslider/images/twitter.gif" alt="follow me on twitter" />&nbsp;<a style="text-decoration: none;" href="http://twitter.com/VibeThemes" target="_blank"><?php _e("Follow me on Twitter", 'vslider'); ?></a></p><form action="https://www.paypal.com/cgi-bin/webscr" method="post"><input type="hidden" name="cmd" value="_xclick" /><input type="hidden" name="business" value="iftimie.s@gmail.com" /><input type="hidden" name="item_name" value="A nice Coffee for vSlider creator!" /><input type="hidden" name="currency_code" value="USD" /><p><?php _e('Like this plugin? Buy me a Coffee!') ?></p><select id="amount" name="amount" class=""><option value="3">Cappuccino - $3</option><option value="6">Frappuccino - $6</option><option value="10">Hot Chocolate - $10</option><option value="20">Expensive Coffee - $20</option><option value="50">Alien Coffee - $50</option></select><input type="hidden" name="no_shipping" value="2" /><input type="hidden" name="no_note" value="1" /><input type="hidden" name="mrb" value="3FWGC6LFTMTUG" /><input type="hidden" name="bn" value="IC_Sample" /><input type="hidden" name="return" value="http://www.vibethemes.com/" /><input type="submit" name="submit" class="button" value="<?php _e('Buy Now!') ?>" /></form>

	</div>

	<!-- END SECONT COLUMN -->
    <div style="clear:both;"></div>
    <form method="post" action="options.php"><?php settings_fields('vslider_settings'); ?><?php global $vslider_defaults; // use the defaults ?><?php foreach((array)$vslider_defaults as $key => $value) : ?><input type="hidden" name="vslider_settings[<?php echo $key; ?>]" value="<?php echo $value; ?>" /><?php endforeach; ?><input type="hidden" name="vslider_settings[update]" value="RESET" /><input type="submit" value="<?php _e('Reset Settings') ?>" /></form>
    <p style="font-family:Georgia;font-style:italic;font-size: 14px;">This plugin is based on a&nbsp;&nbsp;<a href="http://jquery.com/" target="_blank">jQuery</a>&nbsp;&nbsp;image slider called&nbsp;&nbsp;<a href="http://workshop.rs/projects/coin-slider/" target="_blank">coin slider</a>&nbsp;&nbsp;</p>
<?php }

// ADD COLORPICKER JS TO SETTINGS PAGE
add_action('admin_print_scripts', 'vslider_admin_scripts');
function vslider_admin_scripts(){
	wp_enqueue_script ('jquery');
    wp_enqueue_script('color_picker', $src = WP_CONTENT_URL.'/plugins/vslider/picker/colorpicker.js', $deps = array('jquery'));
}

// ADD COLORPICKER CSS AND JQUERY SCRIPTS TO SETTINGS PAGE
add_action('admin_head', 'vslider_admin_head');
function vslider_admin_head () {?>
<link rel="stylesheet" media="screen" type="text/css" href="<?php echo bloginfo( 'url' ) . '/wp-content/plugins/vslider/picker/colorpicker.css'; ?>" />
<script type="text/javascript">
    var $jq = jQuery.noConflict();
    $jq(document).ready(function() { $jq(".fade").fadeIn(1000).fadeTo(1000, 1).fadeOut(1000); });
	$jq(document).ready(function() { $jq('#click1').click(function() { $jq('#slide1').slideToggle('slow', function() { });}); $jq('#click2').click(function() { $jq('#slide2').slideToggle('slow', function() { });}); $jq('#click3').click(function() { $jq('#slide3').slideToggle('slow', function() { });}); $jq('#click4').click(function() { $jq('#slide4').slideToggle('slow', function() { });}); $jq('#click5').click(function() { $jq('#slide5').slideToggle('slow', function() { });}); });
	$jq(document).ready(function() { $jq('#textColor, #bgColor').ColorPicker({ onShow: function (colpkr) { $jq(colpkr).fadeIn(500); return false; }, onHide: function (colpkr) { $jq(colpkr).fadeOut(500); return false; }, onSubmit: function(hsb, hex, rgb, el) { $jq(el).val(hex); $jq(el).ColorPickerHide(); }, onBeforeShow: function () { $jq(this).ColorPickerSetColor(this.value); } })	.bind('keyup', function(){ $jq(this).ColorPickerSetColor(this.value); }); });
</script>
<?php }

// ADD VSLIDER JS TO THEME HEAD SECTION
add_action('wp_print_scripts', 'vslider_head_scripts');
function vslider_head_scripts() {
    wp_enqueue_script ('jquery');
	wp_enqueue_script('vslider', WP_CONTENT_URL.'/plugins/vslider/js/vslider.js', $deps = array('jquery'));
}
add_action('wp_head', 'vslider_head');
function vslider_head() { global $vs_settings; ?>
<!-- Start vSlider Settings -->
<script type="text/javascript">var $jq = jQuery.noConflict(); $jq(document).ready(function() { $jq('#vslider').coinslider({ width: <?php echo $vs_settings['width']; ?>, height: <?php echo $vs_settings['height']; ?>, spw: <?php echo $vs_settings['spw']; ?>, sph: <?php echo $vs_settings['sph']; ?>, delay: <?php echo $vs_settings['delay']; ?>, sDelay: <?php echo $vs_settings['sDelay']; ?>, opacity: <?php echo $vs_settings['opacity']; ?>, titleSpeed: <?php echo $vs_settings['titleSpeed']; ?>, effect: '<?php echo $vs_settings["effect"]; ?>', navigation: <?php echo $vs_settings['navigation']; ?>, links : <?php echo $vs_settings['links']; ?>, hoverPause: <?php echo $vs_settings['hoverPause']; ?> }); });</script>
<style type="text/css" media="screen">#vslider { width: <?php echo $vs_settings['width']; ?>px; height: <?php echo $vs_settings['height']; ?>px;overflow: hidden; position: relative; }#vslider a, #vslider a img {border: none !important; text-decoration: none !important; outline: none !important; }#vslider h4 {color: #<?php echo $vs_settings['textColor']; ?> !important;margin: 0px !important;padding: 0px !important;font-family: <?php echo $vs_settings['fontFamily']; ?> !important;font-size: <?php echo $vs_settings['titleFont']; ?>px !important;}#vslider .cs-title {width: 100%;padding: 10px;background: #<?php echo $vs_settings['bgColor']; ?>;color: #<?php echo $vs_settings['textColor']; ?>  !important;font-family: <?php echo $vs_settings['fontFamily']; ?> !important;font-size: <?php echo $vs_settings['fontSize']; ?>px !important;letter-spacing: normal !important;line-height: normal !important;}.cs-prev, .cs-next {font-weight: bold;background: #<?php echo $vs_settings['bgColor']; ?> !important;font-size: 28px !important;font-family: "Courier New", Courier, monospace;color: #<?php echo $vs_settings['textColor']; ?> !important;padding: 0px 10px !important;-moz-border-radius: 5px;-khtml-border-radius: 5px;-webkit-border-radius: 5px;}</style>
<!-- End vSlider Settings -->
<?php }

// ENABLE SUPPORT FOR POST THUMBNAILS
if ( function_exists('add_theme_support') ) {
	add_theme_support('post-thumbnails');
}

// LIMIT CONTENT FUNCTION
function vslider_limitpost ($max_char, $more_link_text = '(more...)', $stripteaser = 0, $more_file = '') {
    $content = get_the_content($more_link_text, $stripteaser, $more_file);
    $content = apply_filters('the_content', $content);
    $content = str_replace(']]>', ']]&gt;', $content);
    $content = strip_tags($content);

   if (strlen($_GET['p']) > 0) {
      echo $content;
      echo "&nbsp;<a rel='nofollow' href='";
      the_permalink();
      echo "'>".__('Read More', 'vibe')." &rarr;</a>";
   }
   else if ((strlen($content)>$max_char) && ($espacio = strpos($content, " ", $max_char ))) {
        $content = substr($content, 0, $espacio);
        $content = $content;
        echo $content;
        echo "...";
        echo "&nbsp;<a rel='nofollow' href='";
        the_permalink();
        echo "'>".$more_link_text."</a>";
   }
   else {
      echo $content;
      echo "&nbsp;<a rel='nofollow' href='";
      the_permalink();
      echo "'>".__('Read More', 'vibe')." &rarr;</a>";
   }
}

// VSLIDER
function vslider() { global $vs_settings;
    if($vs_settings['slide1']): $slide[1]="1"; endif;
    if($vs_settings['slide2']): $slide[2]="2"; endif;
    if($vs_settings['slide3']): $slide[3]="3"; endif;
    if($vs_settings['slide4']): $slide[4]="4"; endif;
    if($vs_settings['slide5']): $slide[5]="5"; endif;
    $imgURL = array();
    $imgLink = array();
    $imgTitle = array();
    $imgText = array();
    $slideNr = count($slide);
    $totalSlides = range(1,$slideNr);
    $count = 0;

echo '<div id="vslider">';
if($vs_settings['customImg'] == 'false') {
    $recent = new WP_Query("cat=".$vs_settings['imgCat']."&showposts=".$vs_settings['postNr']); while($recent->have_posts()) : $recent->the_post(); ?>
        <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail ( array($vs_settings['width'], $vs_settings['height']) ); ?>
        <?php if($vs_settings['excerpt'] == 'true') { ?>
            <span><h4><?php the_title(); ?></h4><?php vslider_limitpost($vs_settings['chars'], "" ); ?></span>
        <?php } ?>
        </a>
    <?php endwhile;
} else {
foreach ($totalSlides as $slideNr) {
    $count++;
    $imgURL[$count]         = $vs_settings['slide'.$slideNr];
    $imgLink[$count]        = $vs_settings['link'.$slideNr];
    $imgTitle[$count]       = $vs_settings['heading'.$slideNr];
    $imgText[$count]        = $vs_settings['desc'.$slideNr];
    ?>
    <a href="<?php echo $imgLink[$count]; ?>"><img src="<?php echo $imgURL[$count]; ?>" style="width:<?php echo $vs_settings['width']; ?>px;height:<?php echo $vs_settings['height']; ?>px;" alt="<?php echo $imgTitle[$count]; ?>" />
    <?php if($vs_settings['heading'.$slideNr]) { ?>
        <span><h4><?php echo "$imgTitle[$count]"; ?></h4><?php echo "$imgText[$count]"; ?></span>
    <?php } ?>
    </a>
<?php }
}
echo '</div>';
}

// REGISTER VSLIDER AS WIDGET
add_action('widgets_init', create_function('', "register_widget('vslider_widget');"));
class vslider_widget extends WP_Widget {

	function vslider_widget() { global $vs_settings;
		$widget_ops = array( 'classname' => 'vslider-widget', 'description' => 'jQuery Image Slider' );
		$control_ops = array( 'width' => 200, 'height' => 250, 'id_base' => 'vslider-widget' );
		$this->WP_Widget( 'vslider-widget', 'vSlider Widget', $widget_ops, $control_ops );
	}

	function widget($args, $instance) {
		extract($args);

		echo $before_widget;

			if (!empty($instance['title']))
				echo $before_title . $instance['title'] . $after_title;

    vslider ();

	echo $after_widget;
	}

	function update($new_instance, $old_instance) {
		return $new_instance;
	}

	function form($instance) { ?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e("Title"); ?>:</label>
			<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" style="width:95%;" /></p>

	<?php
	}
}

?>