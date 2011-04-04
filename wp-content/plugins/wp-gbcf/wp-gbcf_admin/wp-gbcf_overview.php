<?php 
################################################################################
// OVERVIEW PAGE
################################################################################
function gb_contact_form_admin_welcome(){
	
	global $gb_root;
	
	if ($_GET['resetformsettings']==true) gb_del_options();	
	gb_set_options();
	
	?>
	<div class="wrap gbcf">
		<div id="secureformsadminicon" class="icon32"><br/></div>
		<h2>Secure and Accessible <abbr><span class="abbr" title="PHP Hypertext Preprocessor">PHP</span></abbr> Contact Form</h2>
		 <?php gb_check_config(); ?>
		<p>This powerful yet easy-to-install <abbr><span class="abbr" title="PHP Hypertext Preprocessor">PHP</span></abbr> contact form, brought to you by <a href="http://green-beast.com/">Mike Cherim</a> and <a href="http://blue-anvil.com/">Mike Jolley</a>, features exceptional accessibility and usability while still providing extensive anti-spam and anti-exploit security features. A perfect marriage of communication and peace-of-mind. And it works. So far this form has saved you from getting 
		   <code class="alert"><?php echo get_option('spamCount');?></code> spam emails! Happy now? Please make a Donation :)</p>	
		<h3><?php _e('Contact Form Overview'); ?></h3>
		<p>Shown below is an overview of the configuration of your contact form. Questions? Please review the <a href="admin.php?page=documentation">Documentation</a> or <a href="admin.php?page=documentation#form-help"><abbr><span class="abbr" title="Frequently Asked Questions">FAQs</span></abbr></a>.</p>
			<table class="widefat">
				<tbody>
					<tr class="alternate">
						<td class="row-title"><?php _e('Version'); ?></td>
						<td class="desc"><?php
							global $form_version, $build;
							echo '<strong class="help">Form Version:</strong> <span>'.$form_version.'</span> - <strong class="help">Build Number:</strong> <span>'.$build.'</span>';
						?></td>
					</tr>
					<tr class="alternate">
						<td class="row-title"><?php _e('Total Spam Count to date'); ?></td>
						<td class="desc"><strong><?php echo get_option('spamCount'); ?></strong></td>
					</tr>
					<tr class="alternate">
						<td class="row-title"><?php _e('Mail Configuration'); ?></td>
						<td class="desc">Sending mail from <strong><?php echo get_option('gb_website_name'); ?></strong> to <strong><?php echo get_option('gb_contact_name'); ?></strong> (<strong><?php echo get_option('gb_email_address'); ?></strong>)</td>
					</tr>
					<tr class="alternate">
						<td class="row-title"><?php _e('Theme'); ?></td>
						<td class="desc">As configured, <?php
						    $style=get_option('gb_style');
						    $theme=get_option('gb_theme');
						    $themename=str_replace(".css", "", $theme);
						if ($style=='none') {
						    echo 'you currently have <strong>no form theme</strong> selected &#8212;';
						} else {
						if ($theme == 'default.css') {
						    echo 'you&#8217;re currently using the <strong>default</strong> theme &#8212;';
						} else if ($theme == 'custom.css') {
						    echo 'you&#8217;re currently using your own <strong>custom</strong> theme &#8212;';
						} else {
						    echo 'you&#8217;re currently using the pre-made <strong>'.$themename.'</strong> theme &#8212;';
						 }                 
						}   
						?> Change this on the <a href="admin.php?page=styling">Styling</a> page.</td>
					</tr>
					<tr class="alternate">
						<td class="row-title"><?php _e('Anti-Spam question'); ?></td>
						<td class="desc"><?php _e('Your anti-spam question is'); ?> <strong><?php echo get_option('gb_randomq'); ?></strong>.</td>
					</tr>
					<tr class="alternate">
						<td class="row-title"><?php _e('Anti-Spam answer'); ?></td>
						<td class="desc"><?php _e('Your anti-spam answer is'); ?> <strong><?php echo get_option('gb_randoma'); ?></strong>.</td>
					</tr>
					<tr class="alternate">
						<td class="row-title"><?php _e('Current time'); ?></td>
						<td class="desc"><?php _e('Wordpress reports that the time is'); ?> <strong><?php echo date("l, F jS, Y \\a\\t g:i a", current_time('timestamp')); ?></strong>.</td>
					</tr>
				</tbody>
			</table>
			<p class="restore" style="float:left;"><a href="admin.php?<?php echo $_SERVER['QUERY_STRING']; ?>&amp;resetformsettings=true"><?php _e('Restore Default Values &amp; Spam Counters'); ?></a><br/><small><?php _e('(you will need to re-configure your form)'); ?></small></p>
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
				<div style="width:605px; height:auto; margin:0; padding-top:12px; text-align:right; float:right ">
					<input type="hidden" name="cmd" value="_s-xclick" alt="cmd" />
					<label for="submit"><input id="submit" type="image" src="<?php echo $gb_root; ?>wp-gbcf_themes/wp-gbcf_images/wp-gbcf_donate.jpg" name="submit" alt="Make a Donation to the Fight Against Spam via PayPal" title="Make a Donation to the Fight Against Spam via PayPal" style="width:600px;height:60px;cursor:pointer;" /></label>
					<img alt="" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
					<input alt="encrypted" type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----" />
				</div>
		</form>
		<div class="clear"></div>	
		<script type="text/javascript">
		/* <![CDATA[ */
			jQuery('.restore a').click(function() {
				if (confirm("<?php _e('Are you sure you want to restore the settings?'); ?>")) {				
					return true;
				} else return false;
			});			
		/* ]]> */
		</script>
		<h3>Copyright and Disclaimer</h3>
	  	<p>This Secure and Accessible PHP Contact Form &ndash; &copy; Copyright 2006&mdash;<?php echo date('Y'); ?>, <a href="http://green-beast.com/">Mike Cherim</a> and <a href="http://blue-anvil.com/">Mike Jolley</a>. All rights reserved. You are free to use this application but may not redistribute it without written permission. Use of this application will be at your own risk. No guarantees or warranties are made, direct or implied. The creators cannot and will not be liable or held accountable for damages, direct or consequential. By using this application it implies agreement to these conditions.</p>
	  	<p class="submit jump"><a href="#wphead">Top</a></p>
	</div>
	<?php
}