<?php 
############################################################################
// Checks if user has configured options yet
############################################################################
function gb_check_config() {
    $email	=	get_option('gb_email_address'); 
    
	if( !function_exists('mail') ) {
		echo '<div class="updated error"><p><strong>Warning!</strong> It seems that the <abbr><span class="abbr" title="PHP Hypertext Preprocessor">PHP</span></abbr> <code>mail()</code> function isn&#8217;t enabled on your server. Sorry, but to use this plugin this function must be enabled. Please contact your web hosting provider to ask if they will enable this function for your domain.</p></div>';
	}
	if (!function_exists('wp_mail')) {
		echo '<div class="updated error"><p><strong>Wordpress Environment Error</strong> Your version of wordpress does not support wp_mail().</p></div>';
	}
	if($email=="youremail@yourdomain.com" || $email=="") {
    	echo ' <div class="updated error"><p><strong>Notice!</strong> It seems that your form is not yet configured. Please <a href="admin.php?page=configuration">Configure</a> before use. <a href="admin.php?page=documentation#form-help" title="See Frequently Asked Questions">Why?</a></p></div>'."\n";
	}
	if(strpos($email,".") === FALSE || strpos($email,"@") === FALSE) {
	    echo ' <div class="updated error"><p><strong>Warning!</strong> Please check the <a href="admin.php?page=configuration#gen">Configured</a> email address(es) as it/they could be somehow malformed. <a href="admin.php?page=documentation#form-help" title="See Frequently Asked Questions">Why?</a></p></div>'."\n";
	}
	return;
}

############################################################################
// Increase the spam counter
############################################################################

function inc_spam_count() {
    $count = get_option('spamCount');
    $count++;
	update_option('spamCount', $count);
}

############################################################################
// htmlspecialchars_decode
############################################################################

if(!function_exists('htmlspecialchars_decode')) :
	function htmlspecialchars_decode($text) {
		return strtr($text, array_flip(get_html_translation_table(HTML_SPECIALCHARS)));
	}
endif;

################################################################################
// getUniqueCode
################################################################################

if (!function_exists('getUniqueCode')) :
	function getUniqueCode($length = "") {
		$code = md5(uniqid(rand(), true));
	 	if ($length != "") return substr($code, 0, $length);
			else return $code;
	}
endif;

################################################################################
// Get options
################################################################################

function gb_get_options() {		
	
	$gb_options = array();
																
	$gb_options['gb_contact_name'] 	= get_option('gb_contact_name');														
	$gb_options['gb_email_address'] = get_option('gb_email_address');
	$gb_options['gb_possession'] 	= strtolower(get_option('gb_possession'));
	$gb_options['gb_website_name'] 	= get_option('gb_website_name');
	$gb_options['gb_options'] 		= get_option('gb_options');
	$gb_options['gb_randomq'] 		= get_option('gb_randomq');
	$gb_options['gb_randoma'] 		= get_option('gb_randoma');
	$gb_options['gb_heading'] 		= get_option('gb_heading');
	$gb_options['error_heading'] 	= get_option('error_heading');
	$gb_options['success_heading'] 	= get_option('success_heading');
	$gb_options['showformhead']		= get_option('showformhead');
	$gb_options['send_button'] 		= get_option('send_button');
	$gb_options['showcredit'] 		= strtolower(get_option('showcredit'));
	$gb_options['showprivacy'] 		= strtolower(get_option('showprivacy'));
	$gb_options['privacyurl'] 		= get_option('privacyurl');
	$gb_options['gb_show_cc'] 		= strtolower(get_option('gb_show_cc'));
	$gb_options['tabindex'] 		= get_option('tabindex');
	$gb_options['ip_blacklist'] 	= get_option('ip_blacklist');	
	$gb_options['gb_email_address'] = get_option('gb_email_address');
	$gb_options['gb_contact_name'] 	= get_option('gb_contact_name');
	$gb_options['gb_website_name'] 	= get_option('gb_website_name');
	$gb_options['gb_show_cc'] 		= get_option('gb_show_cc');
	$gb_options['gb_randomq'] 		= get_option('gb_randomq');
	$gb_options['gb_randoma'] 		= get_option('gb_randoma');
	$gb_options['gb_heading'] 		= get_option('gb_heading');
	$gb_options['error_heading'] 	= get_option('error_heading');
	$gb_options['success_heading'] 	= get_option('success_heading');
	$gb_options['privacyurl'] 		= get_option('privacyurl');
	$gb_options['send_button'] 		= get_option('send_button');
	$gb_options['showphone']		= strtolower(get_option('showphone'));
	$gb_options['showwebsite']		= strtolower(get_option('showwebsite'));
	
	
	// Special option handling	
	$gb_options['gb_options'] 		= explode(",", get_option('gb_options'));		
	$gb_options['gb_options'] 		= str_replace ( '"', "", $gb_options['gb_options'] );
		
    $gb_options['ip_blacklist']		= explode(",", get_option('ip_blacklist'));		
    $gb_options['ip_blacklist']		= str_replace ( '"', "", $gb_options['ip_blacklist'] );
	
	$gb_options['tabindex'] 		= get_option('tabindex');
    $tabindex_pieces 				= explode(",", $gb_options['tabindex']);                                     
	$gb_options['tab_privacy'] 		= $tabindex_pieces[0];          
	$gb_options['tab_name'] 		= $tabindex_pieces[1];
	$gb_options['tab_email'] 		= $tabindex_pieces[2];
	$gb_options['tab_phone'] 		= $tabindex_pieces[3];
	$gb_options['tab_url'] 			= $tabindex_pieces[4];
	$gb_options['tab_reason'] 		= $tabindex_pieces[5];
	$gb_options['tab_message'] 		= $tabindex_pieces[6];
	$gb_options['tab_spam'] 		= $tabindex_pieces[7];
	$gb_options['tab_why'] 			= $tabindex_pieces[8];
	$gb_options['tab_cc'] 			= $tabindex_pieces[9];
	$gb_options['tab_submit'] 		= $tabindex_pieces[10];    
	
	// Possession management conditions begin
	if ($gb_options['gb_possession'] == "org") {
	     $gb_options['i_or_we'] = "we";
	     $gb_options['me_or_us'] = "us";
	     $gb_options['my_or_our'] = "our";
	} else {
	     $gb_options['i_or_we'] = "I";
	     $gb_options['me_or_us'] = "me";
	     $gb_options['my_or_our'] = "my";
	}
	
	return $gb_options;
}

################################################################################
// Set default options
################################################################################

function gb_set_options() {	
	add_option('spamCount', '0', 'Spam counter', 'yes');
	add_option('wp_gb_pid_key', getUniqueCode(12));												
	add_option('gb_contact_name', 'Form User', 'Your name or company name', 'yes');														
	add_option('gb_email_address', get_bloginfo('admin_email'), 'Your email address(es)', 'yes');
	add_option('gb_possession', 'pers', 'Form posession', 'yes');
	add_option('gb_website_name', get_bloginfo('name'), 'Your website name', 'yes');
	add_option('gb_options', 'To make a comment'.",".'To ask a question'.",".'Report a site problem'.",".'Other (explain below)'.",", 'Pull-down menu options', 'yes');
	add_option('gb_randomq', 'Is fire hot or cold?', 'Random qu', 'yes');
	add_option('gb_randoma', 'hot', 'Random qu Answer', 'yes');
	add_option('gb_heading', '2', 'Error heading size (1 is largest)', 'yes');
	add_option('error_heading', 'Whoops! Error Made!', 'Enter error heading text', 'yes');
	add_option('success_heading', 'Success! Mail Sent!', 'Enter success heading text', 'yes');
	add_option('showformhead', 'no', 'Show the form header', 'yes');
	add_option('send_button', 'Submit Form', 'Submit button text', 'yes');
	add_option('showcredit', 'yes', 'Enter credit link option', 'yes');##
	add_option('showphone', 'yes', '', 'yes');##
	add_option('showwebsite', 'yes', '', 'yes');##
	add_option('showprivacy', 'no', 'Enter privacy link option', 'yes');##
	add_option('privacyurl', 'http://yourdomain.com/privacy/', 'Enter privacy link URL', 'yes');
	add_option('gb_show_cc', 'no', 'Show CC', 'yes');##
	add_option('tabindex', '0,0,0,0,0,0,0,0,0,0,0', 'Tabindex assignments', 'yes');##
	add_option('ip_blacklist', '0.0.0.0'.",".'00.11.22.33'.",".'00.255.255.255'.",", 'Block IPs', 'yes');##
}

############################################################################
// Delete Options
############################################################################

function gb_del_options() {
	delete_option('showphone');
	delete_option('showwebsite');
	delete_option('wp_gb_pid_key');
	delete_option('spamCount');	
	delete_option('gb_contact_name');																													
	delete_option('gb_email_address');
	delete_option('gb_possession');##
	delete_option('gb_website_name');	
	delete_option('gb_options');
	delete_option('gb_randomq');
	delete_option('gb_randoma');
	delete_option('gb_heading');
	delete_option('error_heading');
	delete_option('success_heading');
	delete_option('showformhead');
	delete_option('send_button');
	delete_option('showcredit');##
	delete_option('showprivacy');##
	delete_option('privacyurl');
	delete_option('gb_show_cc');
	delete_option('tabindex');##
	delete_option('ip_blacklist');##
}