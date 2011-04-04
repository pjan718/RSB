<?php
/* 
Plugin Name: Secure and Accessible PHP Contact Form
Plugin URI: http://green-beast.com/blog/?page_id=136
Version: v.2.1WP B20110125
Author: <a href="http://green-beast.com/">Mike Cherim</a> and <a href="http://blue-anvil.com/">Mike Jolley</a>
Description: This powerful yet easy-to-install contact form features exceptional accessibility and usability while still providing extensive anti-spam and anti-exploit security features. A marriage of communication and peace-of-mind. 
*/

/* 
Secure and Accessible PHP Contact Form v.2.0WP (c) Copyright 2006-current. All rights reserved.
Mike Cherim (http://green-beast.com/) and Mike Jolley  (http://www.blue-anvil.com/)
You are free to use this application but may not redistribute it without written permission.
Use of this application will be at your own risk. No guarantees or warranties are made, direct or implied.
The creators cannot and will not be liable or held accountable for damages, direct or consequential.
By using this application it implies agreement to these conditions. 
*/

################################################################################
// Vars and version
################################################################################

global $wp_db_version, $wpdb, $gb_root, $form_version, $build;

$form_version = "v.2.1WP";
$build = "B20110125";

if ($wp_db_version < 8201) {
	if ( ! defined( 'WP_CONTENT_URL' ) ) {
		if ( defined( 'WP_SITEURL' ) ) define( 'WP_CONTENT_URL', WP_SITEURL . '/wp-content' );
		else define( 'WP_CONTENT_URL', get_bloginfo( 'wpurl' ) . '/wp-content' );
	}
	if ( ! defined( 'WP_CONTENT_DIR' ) ) define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
	if ( ! defined( 'WP_PLUGIN_URL' ) ) define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
	if ( ! defined( 'WP_PLUGIN_DIR' ) ) define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
}

$gb_root = WP_PLUGIN_URL."/wp-gbcf/";

############################################################################
// Include files
############################################################################

include_once('wp-gbcf_functions.php');

if ( is_admin() ) :
	include_once('wp-gbcf_admin/wp-gbcf_overview.php');
	include_once('wp-gbcf_admin/wp-gbcf_config.php');
	include_once('wp-gbcf_admin/wp-gbcf_styling.php');
	include_once('wp-gbcf_admin/wp-gbcf_help.php');
	gb_set_options();
endif;

################################################################################
// Menus
################################################################################

function wp_gb_contact_form() { 
	global $gb_root;	
	add_menu_page('Contact Form', 'Contact Form', 'manage_options', __FILE__ , 'gb_contact_form_admin_welcome', $gb_root.'wp-gbcf_themes/wp-gbcf_images/menu_icon.png');
	add_submenu_page(__FILE__, 'Overview', 'Overview', 'manage_options', __FILE__, 'gb_contact_form_admin_welcome');
	add_submenu_page(__FILE__, 'Configuration', 'Configuration', 'manage_options', 'configuration', 'gb_contact_form_admin');
	add_submenu_page(__FILE__, 'Styling', 'Styling', 'manage_options', 'styling', 'gb_contact_form_admin_style');
	add_submenu_page(__FILE__, 'Documentation', 'Documentation', 'manage_options', 'documentation', 'gb_contact_form_admin_docs');
}
add_action('admin_menu', 'wp_gb_contact_form');

############################################################################
// CSS/JS Head
############################################################################

function gb_contact_form_head() {
	global $gb_root;
    $sheet = get_option('gb_style');
	if($sheet=='theme') :
    	echo '<link rel="stylesheet" type="text/css" media="screen" href="'.$gb_root.'wp-gbcf_themes/'.get_option('gb_theme').'" />';
	endif;
    echo '<!--[if IE]><script src="'.$gb_root.'wp-gbcf_focus.js" type="text/javascript"></script><![endif]-->';    
}

function gb_admin_form_head() {	
	global $gb_root, $wp_db_version;	
	?>
	<style type="text/css" media="screen">
		<?php
		if ($wp_db_version > 11547) {
			?>
			#secureformsadminicon {
				background:transparent url(<?php echo $gb_root; ?>wp-gbcf_themes/wp-gbcf_images/heading-icon.gif) no-repeat scroll -8px -5px;
			}
			<?php
		}
		?>
		.gbcf p, .gbcf ul, .gbcf ol, .gbcf dl {
			margin: 1em 0;
			line-height: 140%;
			font-size: 12px;
		}
		.gbcf li, .gbcf dt, .gbcf dd {
			line-height: 140%;
			font-size: 12px;
			margin-bottom: 0.5em;
		}
		.gbcf ol {
			list-style: decimal outside;
			margin-left: 2em;
		}
		.gbcf dt { font-weight : bold; }
		.gbcf abbr, .gbcf .abbr { border-bottom:1px dotted #999; cursor:help; }
		.gbcf input.btn { cursor:pointer; padding:5px 30px 5px 30px; }
		.gbcf input.reset { cursor:pointer; border:2px outset #999; padding:1px; }
		.gbcf .alert { font-weight:bold; color:#cd0000; font-size:1.1em; }
		.gbcf .reg_alert { color:#cd0000; }
		.gbcf .updated p {
			margin: 0.5em 0;
		}
		.gbcf fieldset.options { 
			border: 1px dotted #c6c6c6; 
			padding: 1em; 
			margin: 1em 0;
			position: relative;
		}
		.gbcf fieldset.options legend {
			background: #dddddd;
			padding: 0.5em;
		}
		.gbcf fieldset.options p { 
			padding: 0 10px; 
		}
		.gbcf p.jump {
			font-size: 10px;
			position: absolute;
			bottom: 0;
			right: 0;
			margin: 0 !important;
			padding: 0.25em !important;
			background: #dddddd;
		}
		.gbcf .jump a {			
			color: #606060;
			text-decoration: none;
			line-height: 1;			
		}
		.gbcf hr { background-color:#ccc; border:1px solid #ccc; }
		.gbcf textarea#themebox { width:99%; font-family:'courier new', monospace; font-size:1.1em; }
	</style>
	<?php
}

add_action('wp_head', 'gb_contact_form_head');
add_action('admin_head', 'gb_admin_form_head');

############################################################################
// Template tag
############################################################################

function gbcf_show() {
    echo gb_show_contact_form();	
}

############################################################################
// Shortcode
############################################################################

if (function_exists('add_shortcode')) : 
	function gb_contact_form_shortcode( $atts ) {
		return gb_show_contact_form();	
	}
	add_shortcode('gb_contact_form', 'gb_contact_form_shortcode');
endif;

############################################################################
// HTML Comment
############################################################################
	
function gb_contact_form_tag( $content = '' ) {
	if(strstr ($content, "<!--gb_contact_form-->" )) {
		$form = gb_show_contact_form();
		$content = str_replace("<p><!--gb_contact_form--></p>", "<!--gb_contact_form-->", $content);
		$content = str_replace("<!--gb_contact_form-->", $form, $content);
	}
	return $content;
}
add_filter('the_content', 'gb_contact_form_tag');

############################################################################
// Form Display
############################################################################

function gb_show_contact_form() {

	global $form_version, $build;
	
	$options = gb_get_options();

	$forms = "";
	
	/* Security key gen */
	$pid_key  = md5(get_option('wp_gb_pid_key'));
	$vers_id  = md5($form_version);
	$bldn_id  = md5($build);
	$date_id  = md5(date('TOZ'));
	$seml_id  = md5($options['gb_email_address']);
	$form_id  = "$pid_key$bldn_id$vers_id$seml_id$date_id"; 
	$form_id  = strtoupper(md5($form_id));
	$form_id  = "ID".$form_id."MC";	
	$send_value = trim(strtolower("submit_".md5($form_id)));
	$trap_1_value = md5($form_id);
	/* End security key gen */

	$forms .= "\n".'<div id="gb_form_div"><!-- BEGIN: Secure and Accessible PHP Contact Form '.$form_version.' by Mike Cherim (http://green-beast.com/) and Mike Jolley (http://blue-anvil.com/) -->'."\n";

 	if ($_POST[$send_value]) {
 	
 		$posted = array();

		// Posted variables
		$posted['gbname'] 		= $_POST['gbname'];           
		$posted['email'] 		= $_POST['email'];         
		$posted['phone'] 		= $_POST['phone'];     
		$posted['url'] 			= $_POST['url'];
		$posted['reason'] 		= $_POST['reason'];       
		$posted['message'] 		= $_POST['message'];     
		$posted['form_id'] 		= $_POST['form_id'];
		$posted['trap1'] 		= $_POST['GB'.$trap_1_value.''];
		$posted['trap2'] 		= $_POST['p-mail'];
		$posted['spamq'] 		= $_POST['spamq']; 
		
		if (isset($_POST['gbcc'])) 
			$posted['gbcc'] 	= $_POST['gbcc']; 
		else 
			$posted['gbcc'] 	= '';
			
		$ltd 					= trim(strip_tags(stripslashes(current_time("mysql"))));		
		$ip						= trim(strip_tags(stripslashes(getenv("REMOTE_ADDR"))));
		$hr			 			= trim(strip_tags(stripslashes(getenv("HTTP_REFERER"))));
		$hst		 			= trim(strip_tags(stripslashes(gethostbyaddr( $_SERVER['REMOTE_ADDR'] ))));
		$ua			 			= trim(strip_tags(stripslashes($_SERVER['HTTP_USER_AGENT'])));
		
		// Strip slashes, html, php, binary, and scrub posted vars
		$posted = array_map('trim', $posted);
		$posted = array_map('strip_tags', $posted);
		$posted = array_map('stripslashes', $posted);
		
		// Email header
		$gb_email_header = stripslashes(strip_tags(trim("From: ".$options['gb_email_address']."\n"."Reply-To: ".$posted['email']."\n"."MIME-Version: 1.0\n"."Content-type: text/plain; charset=\"" . get_bloginfo('charset') . "\"\n"."Content-transfer-encoding: quoted-printable\n\n"))); 
		
		// Identify exploits
		$head_expl = "/(bcc:|cc:|document.cookie|document.write|onclick|onload)/i";
		$inpt_expl = "/(content-type|to:|bcc:|cc:|document.cookie|document.write|onclick|onload)/i";

		// Carbon Copy request negotiation
		if($posted['gbcc'] == "gbcc") {
		     $gb_cc = ", ".$posted['email']."";
		     $cc_notify1 = "<br /><small>(A carbon copy has also been sent to this address.)</small>";
		     $cc_notify2 = "(Copy sent)";
		     $cc_notify3 = "";
		} else {
		     $gb_cc = "";
		     $cc_notify1 = ""; 
		     $cc_notify2 = ""; 
		     $cc_notify3 = "";
		} 

		// Required fields need stuffing or get an error showing fields needed
		if(!isset($posted['gbname'], $posted['email'], $posted['reason'], $posted['message'], $posted['spamq']) || empty($posted['gbname']) || empty($posted['email']) || empty($posted['reason']) || empty($posted['message']) || empty($posted['spamq'])) {
    		
    		$forms .= '<h'.$options['gb_heading'].' class="formhead" id="results">Results: <span class="error">'.$options['error_heading'].'</span></h'.$options['gb_heading'].'><p><span class="error">Required Field(s) Missed:</span> The following &#8220;Required&#8221; fields were not filled in. Using your &#8220;Back&#8221; button, please go back and fill in all required fields.</p>'."\n";
    		$forms .= '<dl>'."\n";
     		$forms .= '<dt>Empty Field(s):</dt>'."\n";
     		
			if(empty($posted['gbname'])) $forms .= '<dd>&#8220;Full name&#8221;</dd>'."\n"; 
			if(empty($posted['email'])) $forms .= '<dd>&#8220;Email address&#8221;</dd>'."\n"; 
			if(empty($posted['reason'])) $forms .= '<dd>&#8220;Subject&#8221;</dd>'."\n"; 
			if(empty($posted['message'])) $forms .= '<dd>&#8220;Message&#8221;</dd>'."\n"; 
			if(empty($posted['spamq'])) $forms .= '<dd>&#8220;'.$options['gb_randomq'].'&#8221;</dd>'."\n"; 
			
			$forms .= '</dl>'."\n";
		
		} else {

			// Or the email doesn't seem to be properly formed or has illegal email characters
			if(!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,6})", $posted['email'])) {
				$forms.=('   <h'.$options['gb_heading'].' class="formhead" id="results">Results: <span class="error">'.$options['error_heading'].'</span></h'.$options['gb_heading'].'>
			     <p><span class="error">Invalid Email Address:</span> The email address you have submitted seems to be invalid. Using your &#8220;Back&#8221; button, please go back and check the address you entered. Please try not to worry, '.$options['i_or_we'].' do respect your privacy.</p>'."\n");
				inc_spam_count();
			// Anti-spam trap 1
			} else if($posted['trap1'] !== "") {
				$forms.=('   <h'.$options['gb_heading'].' class="formhead" id="results">Results: <span class="error">'.$options['error_heading'].'</span></h'.$options['gb_heading'].'>
			     <p><span class="error">Anti-Spam Trap 1 Field Populated:</span> You populated a spam trap anti-spam input so you must be a spambot. Go away!</p>'."\n");
			    inc_spam_count();
			// Anti-spam trap 2
			} else if($posted['trap2'] !== "") {
				$forms.=('   <h'.$options['gb_heading'].' class="formhead" id="results">Results: <span class="error">'.$options['error_heading'].'</span></h'.$options['gb_heading'].'>
			     <p><span class="error">Anti-Spam Trap 2 Field Populated:</span> You populated a spam trap anti-spam input that is meant to confuse automated spam-sending machines. If you accidently entered data in this field, using your &#8220;Back&#8221; button, please go back and remove it before submitting this form. Sorry for the confusion.</p>'."\n");
				inc_spam_count();
			// Input length error tripping
			} else if(strlen($posted['gbname']) > 40 || strlen($posted['email']) > 40 || strlen($posted['phone']) > 30 || strlen($posted['url']) > 60 || strlen($posted['gbcc']) > 4) {
				$forms.=('   <h'.$options['gb_heading'].' class="formhead" id="results">Results: <span class="error">'.$options['error_heading'].'</span></h'.$options['gb_heading'].'>
				<p><span class="error">Input Maxlength Violation:</span> Certain inputs have been populated beyond that which is allowed by the form. Therefore you must be trying to post remotely and are probably a spambot. Go away!</p>'."\n");
				inc_spam_count();
			// Contact reason validation
			} else if(!in_array($posted['reason'], $options['gb_options'])) { 
				$forms.=('   <h'.$options['gb_heading'].' class="formhead" id="results">Results: <span class="error">'.$options['error_heading'].'</span></h'.$options['gb_heading'].'>
				<p><span class="error">Contact Reason Violation:</span> You have tried to post a &#8220;Contact Reason&#8221; which doesn&#8217;t exist in '.$options['my_or_our'].' menu. Therefore you must be trying to post remotely and are probably a spambot. Go away!</p>'."\n");
				inc_spam_count();
			// Check the IP black list
			} else if(in_array($ip, $options['ip_blacklist'])) { 
				$forms.=('   <h'.$options['gb_heading'].' class="formhead" id="results">Results: <span class="error">'.$options['error_heading'].'</span></h'.$options['gb_heading'].'>
				<p><span class="error">Blacklisted IP Address:</span> Sorry, but your IP address has been blocked. Perhaps you have abused your form submission privileges in the past. If you&#8217;ve sent spam to '.$options['me_or_us'].' in the past, this could be the reason.</p>'."\n");
				inc_spam_count();
			// Form value confirmation
			} else if(md5($form_id) !== $posted['form_id']) {
				$forms.=('   <h'.$options['gb_heading'].' class="formhead" id="results">Results: <span class="error">'.$options['error_heading'].'</span></h'.$options['gb_heading'].'>
				<p><span class="error">Form ID Value Mismatch:</span> The submitted ID does not match registered ID of this form which means you&#8217;re trying to post remotely so this mean you must be a spambot. Go away!</p>'."\n");
				inc_spam_count();
			// My long version of Jem's exploit killer
			} else if(preg_match($head_expl, $gb_email_header) || preg_match($inpt_expl, $posted['gbname']) || preg_match($inpt_expl, $posted['email']) || preg_match($inpt_expl, $posted['phone']) || preg_match($inpt_expl, $posted['url']) || preg_match($inpt_expl, $posted['message'])) {
				$forms.=('   <h'.$options['gb_heading'].' class="formhead" id="results">Results: <span class="error">'.$options['error_heading'].'</span></h'.$options['gb_heading'].'>
				<p><span class="error">Injection Exploit Detected:</span> It seems that you&#8217;re possibly trying to apply a header or input injection exploit in '.$options['my_or_our'].' form. If you are, please stop at once! If not, using your &#8220;Back&#8221; button, please go back and check to make sure you haven&#8217;t entered <strong>content-type</strong>, <strong>to:</strong>, <strong>bcc:</strong>, <strong>cc:</strong>, <strong>document.cookie</strong>, <strong>document.write</strong>, <strong>onclick</strong>, or <strong>onload</strong> in any of the form inputs. If you have and you&#8217;re trying to send a legitimate message, for security reasons, please find another way of communicating these terms.</p>'."\n");
				inc_spam_count();
			// Anti-spam verification
			} else if(strtolower($posted['spamq']) !== strtolower($options['gb_randoma'])) {
				$forms.=('   <h'.$options['gb_heading'].' class="formhead" id="results">Results: <span class="error">'.$options['error_heading'].'</span></h'.$options['gb_heading'].'>
				<p><span class="error">Anti-Spam Question/Answer Mismatch:</span> The answer you supplied to the anti-spam question is incorrect. Using your &#8220;Back&#8221; button, please go back and try again or use '.$options['my_or_our'].' regular email, <a href="mailto:'.$options['gb_email_address'].'?subject='.$options['gb_website_name'].'%20Backup%20Email%20[Anti-Spam Question/Answer Mismatch]">'.$options['gb_email_address'].'</a>, if having Anti-Spam question difficulty.</p>'."\n");
				inc_spam_count();
			// And now let's see if the variable for submit matches what's required
			} else if(!(isset($_POST[$send_value]))) {
				$forms.=('   <h'.$options['gb_heading'].' class="formhead" id="results">Results: <span class="error">'.$options['error_heading'].'</span></h'.$options['gb_heading'].'>
				<p><span class="error">Submit Variable Mismatch:</span> It looks like you&#8217;re trying to post remotely as the submit variable is unmatched. Using your &#8220;Back&#8221; button, please go back and try again  or try '.$options['my_or_our'].' regular email, <a href="mailto:'.$options['gb_email_address'].'?subject='.$options['gb_website_name'].'%20Backup%20Email%20[Submit Variable Mismatch]">'.$options['gb_email_address'].'</a>, to circumvent Variable Mismatch.</p>'."\n");
				inc_spam_count();
			// Holy smokes, looks like all's cool and we can send the message
			} else {
				$gb_content = "Hello ".$options['gb_contact_name'].",\n\nYou are being contacted via ".$options['gb_website_name']." by ".$posted['gbname'].". ".$posted['gbname']." has provided the following information so you may contact them:\n\n   Email: ".$posted['email']." $cc_notify2\n   Phone: ".$posted['phone']."\n   Website: ".$posted['url']."\n   Subject: ".$posted['reason']."\n\nMessage:\n   ".$posted['message']."\n\n\n--------------------------\nOther Data and Information:\n   IP Address: $ip\n   Time Stamp: $ltd\n   Referrer: $hr\n   Host: $hst\n   User Agent: $ua\n   Resolve IP Whois: http://ws.arin.net/cgi-bin/whois.pl?queryinput=3D$ip\n\n";
				$gb_ccmail = "Hello ".$posted['gbname'].",\n\nThis is a copy of the email you sent to ".$options['gb_website_name'].". If appropriate to your message, you should receive a response quickly. You successfully sent the following information:\n\n   Email: ".$posted['email']." $cc_notify3\n   Phone: ".$posted['phone']."\n   Website: ".$posted['url']."\n   Subject: ".$posted['reason']."\n\nMessage:\n   ".$posted['message']."\n\n\n--------------------------\nOther Data and Information:\n   Time Stamp: $ltd\n\n";

				// Remove tags and slashes from content-including header then trim it again
				$gb_content = stripslashes(strip_tags(trim($gb_content)));
				$gb_ccmail = stripslashes(strip_tags(trim($gb_ccmail)));

				// The mail function helps, let's send this stuff
				wp_mail($options['gb_email_address'], "[".$options['gb_website_name']."] Contact from ".$posted['gbname'], $gb_content, $gb_email_header);

				if($gb_cc !== "") {
				     wp_mail("$gb_cc", "[Copy] Email sent to ".$options['gb_website_name']."", $gb_ccmail, $gb_email_header);
				}

				// And let's inform the user and show them what they sent
				$forms.=('   <h'.$options['gb_heading'].' class="formhead" id="results">Results: <span class="success">'.$options['success_heading'].'</span> <small>[ <a href="'.$hr.'">Reset Form</a> ]</small></h'.$options['gb_heading'].'>
				<p><span class="success">Message Sent:</span> You have successfully sent a message to '.$options['me_or_us'].', '.$posted['gbname'].'. If appropriate to your message, '.$$options['i_or_we'].' will get back to you shortly. You submitted the following information:</p> 
				<ul>
				<li><span class="items">Name:</span> '.$posted['gbname'].'</li>
				<li><span class="items">Email:</span> <a href="mailto:'.$posted['email'].'">'.$posted['email'].'</a> '.$cc_notify1.'</li>
				<li><span class="items">Phone:</span> '.$posted['phone'].'</li>
				<li><span class="items">Website:</span> <a href="'.$posted['url'].'">'.$posted['url'].'</a></li>
				<li><span class="items">Reason:</span> '.$posted['reason'].'</li>
				</ul>
				<dl id="result_dl_blockq">
				<dt>Message:</dt>
				<dd>
				<blockquote cite="'.$hr.'">
				'.wptexturize(wpautop($posted['message'])).'
				<p><cite>&#8212;'.$posted['gbname'].'</cite></p>
				</blockquote>
				</dd>
				</dl>
				<dl>
				<dt><small>Time Stamp:</small></dt>
				<dd><small>Form Submitted: '.$ltd.'</small></dd>
				</dl>'."\n");
			}
		}
		
	} else { 
	
		// No errors so far? No successes so far? No confirmation? Hmm. Maybe the user needs a contact form
		if ($options['showformhead']=='yes'){
			$forms .= '<h'.$options['gb_heading'].' class="main_formhead">'.$options['gb_website_name'].' Contact Form</h'.$options['gb_heading'].'>'."\n"; 
		}
 		
 		$forms .= '<form id="gb_form" method="post" action="'.htmlentities($_SERVER['REQUEST_URI']).'#results">
			<!-- Form Intro -->
			<fieldset id="formwrap">
				<legend id="mainlegend" style="cursor:help;" title="Note: Code and markup will be removed from all fields!"><span>Contact '.$options['me_or_us'].'';

		if($options['showprivacy'] == "yes") {
		    $forms .= ' <small class="privacy">[&nbsp;<a tabindex="'.$options['tab_privacy'].'" href="'.$options['privacyurl'].'" title="Review '.$options['my_or_our'].' privacy policy">Privacy</a>&nbsp;]</small></span></legend>'; 
		} else {
		    $forms .= '</span></legend>';
		}
		
		if ($options['showphone']=='yes' || $options['showwebsite']=='yes') :
			$forms .= '
				<!-- Required Info -->
				<fieldset>
					<legend><span>Required:</span></legend>';
		endif;

		$forms .= '
				<label for="name">Full name</label><br /><input tabindex="'.$options['tab_name'].'" class="med" type="text" name="gbname" id="name" size="35" maxlength="40" value="" /><br /> 
				<label for="email">Email address</label><br /><input tabindex="'.$options['tab_email'].'" class="med" type="text" name="email" id="email" size="35" maxlength="40" value="" /><br />';
				
		if ($options['showphone']=='yes' || $options['showwebsite']=='yes') :
			
			$forms .= '
				</fieldset>
				<!-- Optional Info -->
				<fieldset>
					<legend><span>Optional:</span></legend>';
			
			if ($options['showphone']=='yes') :
				$forms .= '<label for="phone">Phone number</label><br /><input tabindex="'.$options['tab_phone'].'" class="med" type="text" name="phone" id="phone" size="35" maxlength="30" value="" /><br />';
			endif;
			
			if ($options['showwebsite']=='yes') :
				$forms .= '<label for="url">Web address</label><br /><input tabindex="'.$options['tab_url'].'" class="med" type="text" name="url" id="url" size="35" maxlength="60" value="http://" />';
			endif;
			
			$forms .= '</fieldset>
			<!-- Required Reasons -->
			<fieldset>
				<legend><span>Required:</span></legend>';
				
		endif;
			
		$forms .= '<label for="reason">Subject</label><br /> 
				<select tabindex="'.$options['tab_reason'].'" class="med" style="cursor:pointer;" name="reason" id="reason">
					<option value="" selected="selected">Please make a selection</option>'."\n"; 
		
		while (list(, $gb_opts) = each($options['gb_options'])) {	
			$gb_opts = str_replace('"', "", "$gb_opts");
		    $forms .= '<option value="'.$gb_opts.'">'.$gb_opts.'</option>'."\n"; 
		} 
		
		$forms .= '
				</select><br />
				<!-- Required Form Comments Area -->
				<label for="message">Message</label><br /><textarea tabindex="'.$options['tab_message'].'" class="textbox" rows="12" cols="60" name="message" id="message"></textarea><br />
				<!-- Required anti spam confirmation -->
				<label title="No worries, the text entered here is case-insensitive" for="spamq">'.$options['gb_randomq'].'</label><br /><input tabindex="'.$options['tab_spam'].'" class="short" type="text" name="spamq" id="spamq" size="15" maxlength="30" value="" /> <small class="whythis" title="This confirms you\'re a human user!">- <a tabindex="'.$options['tab_why'].'" href="#spamq" style="cursor:help;">Why ask? <span>To confirm you&#8217;re a person</span></a></small><br />';
				
		if ($options['showphone']=='yes' || $options['showwebsite']=='yes') :
			$forms .= '</fieldset>';
		endif;
		
		$forms .= '<!-- Special anti-spam input: hidden type -->
			<input type="hidden" name="GB'.$trap_1_value.'" id="GB'.$trap_1_value.'" alt="Cherim-Hartmann Anti-Spam Trap One" value="" />
			<!-- Special anti-spam input: non-displayed type -->
			<div style="position:absolute; top: -9000px; left:-9000px;"><label for="p-mail"><small><strong>Note:</strong> The input below should <em>not</em> be filled in. It is a spam trap. Please ignore it. If you populate this input, the form will return an error.</small></label><input type="text" name="p-mail" id="p-mail" alt="Cherim-Hartmann Anti-Spam Trap Two" value="" /></div>
			<!-- Special anti-spam form id field -->
			<input type="hidden" name="form_id" id="form_id" alt="Form ID Field" value="'.md5($form_id).'" />
			<!-- Form Buttons -->'."\n";

		if($options['gb_show_cc'] == "yes") {
		    $forms .= '<input tabindex="'.$options['tab_cc'].'" class="checkbox" type="checkbox" name="gbcc" id="gbcc" value="gbcc" /> <label for="gbcc"><small>Check this box if you want an email copy.</small></label><br />'."\n"; 
		}
			
		$forms .= '<input tabindex="'.$options['tab_submit'].'" style="cursor:pointer;" class="button" type="submit" alt="Click Button to '.$options['send_button'].'" value="'.$options['send_button'].'" name="'.$send_value.'" id="'.$send_value.'" title="Click Button to Submit Form" />'."\n"; 

		if($options['showcredit'] == "yes") {
		    $forms .= '<p class="creditline"><small>Secure and Accessible <abbr><span class="abbr" title="PHP Hypertext Preprocessor">PHP</span></abbr> Contact Form <span title="'.$build.'">'.$form_version.'</span> by <a href="http://green-beast.com/" title="Green-Beast.com">Mike Cherim</a> &amp; <a href="http://blue-anvil.com/" title="Blue-Anvil.com">Mike Jolley</a>.</small></p>'."\n"; 
		} else {
		    $forms .= '<!--'.$build.'-->'."\n";
		}
		
		$forms .= '</fieldset></form>'."\n";
	}

	$forms .= '</div><!-- END: Secure and Accessible PHP Contact Form '.$form_version.' by Mike Cherim (http://green-beast.com/) and Mike Jolley (http://blue-anvil.com/) -->'."\n\n";
	
	return $forms; 
}
?>