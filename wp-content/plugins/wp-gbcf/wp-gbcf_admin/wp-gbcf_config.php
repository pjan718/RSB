<?php 
################################################################################
// ADMIN PAGE
################################################################################
function gb_contact_form_admin(){

	global $form_version;

	if ($_POST['sub']) {

		update_option('gb_email_address', trim($_POST['email']));
		update_option('gb_contact_name', trim(stripslashes($_POST['gbname'])));
		update_option('gb_possession', $_POST['poss']);
		update_option('gb_website_name', trim(stripslashes($_POST['webname'])));
		update_option('gb_time_offset', trim($_POST['time_offset']));
		
		$opt = $_POST['options'];
		$opt = explode("\n", $opt);
		$opts = array();
		foreach ($opt as $item){
			$item = trim(stripslashes($item));
			if(!empty($item)){
				$opts[] = $item;
			}
		}
		update_option('gb_options', implode(',', $opts));
		
		update_option('gb_randomq', trim(stripslashes($_POST['ranqu'])));
		update_option('gb_randoma', trim(stripslashes($_POST['ranquans'])));
		
		update_option('gb_heading', $_POST['gb_heading']);
		update_option('error_heading', trim(stripslashes($_POST['errorheading'])));
		update_option('success_heading', trim(stripslashes($_POST['successheading'])));
		update_option('showformhead', $_POST['showformhead']);
		update_option('send_button', trim(stripslashes($_POST['send_button'])));
		update_option('showcredit', $_POST['showcredit']);
		update_option('showprivacy', $_POST['showprivacy']);
		
		update_option('showphone', $_POST['showphone']);
		update_option('showwebsite', $_POST['showwebsite']);
		
		update_option('privacyurl', trim($_POST['privacyurl']));
		update_option('gb_show_cc', trim($_POST['gb_show_cc']));
		
		$tabindex= $_POST['tab_privacy']; $tabindex.=",";
		$tabindex.=$_POST['tab_name']; $tabindex.=",";
		$tabindex.=$_POST['tab_email']; $tabindex.=",";
		$tabindex.=$_POST['tab_phone']; $tabindex.=",";
		$tabindex.=$_POST['tab_url']; $tabindex.=",";
		$tabindex.=$_POST['tab_reason']; $tabindex.=",";
		$tabindex.=$_POST['tab_message']; $tabindex.=",";
		$tabindex.=$_POST['tab_spam']; $tabindex.=",";
		$tabindex.=$_POST['tab_why']; $tabindex.=",";
		$tabindex.=$_POST['tab_cc']; $tabindex.=",";
		$tabindex.=$_POST['tab_submit'];
															
		update_option('tabindex',$tabindex);
		
		$opt = $_POST['blacklist'];
		$opt = explode("\n", $opt);
		$opts = array();
		foreach ($opt as $item){
			$item = trim(stripslashes($item));
			if(!empty($item)){
				$opts[] = $item;
			}
		}
		update_option('ip_blacklist', implode(',', $opts));
		
		echo '<div id="message" class="updated fade"><p><strong>Contact Form Configuration Updated</strong></p></div>'."\n";
	} 
	
	$options = gb_get_options();

?>
<div class="wrap gbcf" id="form-config">
	<div id="secureformsadminicon" class="icon32"><br/></div>
	<h2>Configuration &laquo; Secure and Accessible <abbr><span class="abbr" title="PHP Hypertext Preprocessor">PHP</span></abbr> Contact Form</h2>
	<?php gb_check_config(); ?>
	 <p>Use this page to configure the form for use on your site. If you need help or have questions, please review the <a href="admin.php?page=documentation">Documentation</a>.</p>	
	<form action="admin.php?page=configuration" method="post" id="gb_form" name="config">
		<fieldset class="options" id="gen">
			<legend>Section 1 of 6 &raquo; General Configuration 
			<?php
			if($options['gb_email_address']=="youremail@yourdomain.com" || $options['gb_email_address']=="") {
			    echo '- <span class="alert">Required!</span> ';
			} else if(strpos($options['gb_email_address'],".") === FALSE || strpos($options['gb_email_address'],"@") === FALSE) {
			    echo '- <span class="alert">Malformation?</span>';
			}
			?></legend>	
			<table class="optiontable form-table"> 
				<tr> 
					<th scope="row">Your name or company name: </th> 
					<td><input type="text" value="<?php echo $options['gb_contact_name']; ?>" name="gbname" id="gbname" class="regular-text" /> <span class="description">The Name you want to see at the top of the email sent to you, as in &ldquo;Hello <code>Form User</code>&rdquo;.</span></td> 
				</tr>
				<tr> 
					<th scope="row"><?php
						if($options['gb_email_address']=="youremail@yourdomain.com" || $options['gb_email_address']==""){
						    echo '<span class="alert">Your email address(<span class="abbr" title="Mulitple addresses must be comma-separated">es</span>)</span>: ';
						} else {
						     echo 'Your email address(<span class="abbr" title="Mulitple addresses must be comma-separated">es</span>): ';
						} 
					?></th> 
					<td>
						<input type="text" value="<?php echo $options['gb_email_address']; ?>" name="email" id="email" class="regular-text" /> <span class="description"><?php 
							if(strpos($options['gb_email_address'],".") === FALSE || strpos($options['gb_email_address'],"@") === FALSE) {
							     echo ' <span class="reg_alert">&laquo; Malformed Email Address(es)?</span>';
							} 
						?> The Email address (or comma separated addresses) you want the form to submit to. Fear not, this information will remain hidden from spammers..</span>						
					</td> 
				</tr>
				<tr> 
					<th scope="row">Form possession: </th> 
					<td><select style="cursor:pointer;" name="poss" id="poss">
						<?php 			
						if($gb_possession=='pers') {
						    echo '       <option value="pers" selected="selected">Personal</option>'."\n";
						    echo '       <option value="org">Organization</option>'."\n";
						} else {
						    echo '       <option value="pers">Personal</option>'."\n";
						    echo '       <option value="org" selected="selected">Organization</option>'."\n";
						} ?></select> <span class="description">The form Possession refers to how statements and errors on the form read, e.g. <strong>Personal</strong> reads as <code>me</code>, <code>my</code>, and <code>I</code> wheras <strong>organization</strong> reads as <code>we</code>, <code>our</code>, and <code>us</code>.</span>
					</td> 
				</tr>
				<tr> 
					<th scope="row">Your website&#8217;s name: </th> 
					<td><input type="text" value="<?php echo $options['gb_website_name']; ?>" name="webname" id="webname" class="regular-text" /> <span class="description">This name will appear on the main form heading (if enabled) and will be on the Subject line of the email in brackets.</span></td> 
				</tr>	
			</table>
			<p class="jump"><a href="#wphead" class="">Top</a></p>
  		</fieldset>		
		<fieldset class="options" id="rsn">
			<legend>Section 2 of 6 &raquo; Contact Reason Menu</legend>	
			<table class="optiontable form-table"> 				
				<tr valign="top">
					<th scope="row">Pull-down menu options: </th>
					<td><textarea rows="5" cols="45" name="options" class="large-text"><?php
					foreach ($options['gb_options'] as $option){
					    $option = str_replace('"', "", $option);
					    $option = trim($option);
						if(!empty($option)){											
					   		echo $option;
					   		echo ''."\n";
					  	}
					} 
					?></textarea> <span class="description">Enter some Reasons your visitors might want to contact you (each option on a new line). Some basic reasons are provided with the form.</span></td>
				</tr>
			</table>
			<p class="jump"><a href="#wphead">Top</a></p>
		</fieldset>	
		<fieldset class="options" id="ats">
			<legend>Section 3 of 6 &raquo; Anti-Spam Question/Answer</legend>
			<p class="description">Change question/answer now and then, make sure it&#8217;s easy to answer by a person.</p>
			<table class="optiontable form-table"> 				
				<tr>
 					<th scope="row">Enter a simple question: </th>
					<td><input type="text" value="<?php echo $options['gb_randomq']; ?>" name="ranqu" id="ranqu" class="regular-text" /> <span class="description"></td>
				</tr>
				<tr>
					<th scope="row">Enter the logical answer: </th>
					<td><input type="text" value="<?php echo $options['gb_randoma']; ?>" name="ranquans" id="ranquans" class="regular-text" /></td>
				</tr>		
			</table>
			<p class="jump"><a href="#wphead">Top</a></p>
		</fieldset>	
		<fieldset class="options" id="hdo">
			<legend>Section 4 of 6 &raquo; Heading Options</legend>	
			<table class="optiontable form-table"> 
				<tr>
 					<th scope="row">Error heading level: </th>
			      <td><select style="cursor:pointer;" name="gb_heading" >
			        <option<?php if ($options['gb_heading']=='1') echo ' selected="selected"'; ?>>1</option>
			        <option<?php if ($options['gb_heading']=='2') echo ' selected="selected"'; ?>>2</option>
			        <option<?php if ($options['gb_heading']=='3') echo ' selected="selected"'; ?>>3</option>
			        <option<?php if ($options['gb_heading']=='4') echo ' selected="selected"'; ?>>4</option>
			        <option<?php if ($options['gb_heading']=='5') echo ' selected="selected"'; ?>>5</option>
			        <option<?php if ($options['gb_heading']=='6') echo ' selected="selected"'; ?>>6</option>		
			       </select> <span class="description">Select a heading level to use for your error messages and, if enabled, your form heading. E.g. "1" = h1 tag</span></td>
 				</tr>
				<tr>
					<th scope="row">Error heading text: </th>
					<td><input type="text" value="<?php echo $options['error_heading']; ?>" name="errorheading" id="errorheading" class="regular-text" /> <span class="description">Enter some Error heading text your visitors will hopefully never see.</span></td>
				</tr>
				<tr>
					<th scope="row">Success heading text: </th>
					<td><input type="text" value="<?php echo $options['success_heading']; ?>" name="successheading" id="successheading" class="regular-text" /> <span class="description">Enter some Success heading text your visitors will see when they submit the form.</span></td>
				</tr>
				<tr>
					<th scope="row" valign="top">Show form heading?: </th>
					<td><select style="cursor:pointer;" name="showformhead">
					<?php 
					if ($options['showformhead']=='yes') { 
					    echo '   <option selected="selected">yes</option>'."\n";
					    echo '   <option>no</option>'."\n";
					} else {
					    echo '   <option>yes</option>'."\n";
					    echo '   <option selected="selected">no</option>'."\n";
					} ?></select> <span class="description">Decide if you want to Show the heading for the form. This selection will not affect error headings.</span></td>
				</tr>
			</table>
 			<p class="jump"><a href="#wphead">Top</a></p>
		</fieldset>		
		<fieldset class="options" id="tab">
			<legend>Section 5 of 6 &raquo; Custom Tabindex Assignments/Enable &amp; Disable Fields</legend>
			<p class="description">Enter your preferred tabindexing on the contact form. Leave tabindex &#8220;0&#8221; if field is not shown.</p>
			<table class="optiontable form-table"> 
				<tr>
					<th scope="row">Privacy link: </th>
					<td><label for="tab_privacy">Tabindex:</label> <input type="text" value="<?php echo $options['tab_privacy']; ?>" style="width:32px" name="tab_privacy" id="tab_privacy" /> <label for="showprivacy" style="padding-left: 10px">Show?</label> <select name="showprivacy" id="showprivacy">
						<option<?php if ($options['showprivacy']=='yes') echo ' selected="selected"'; ?>>yes</option>
						<option<?php if ($options['showprivacy']=='no') echo ' selected="selected"'; ?>>no</option>
					</select> <label for="privacyurl" style="padding-left: 10px">Full privacy page <abbr><span class="abbr" title="Uniform Resource Locator">URL</span></abbr>:</label> <input type="text" value="<?php echo $options['privacyurl']; ?>" name="privacyurl" id="privacyurl" class="regular-text" /></td>
				</tr>
				<tr>
					<th scope="row">Name field: </th>
					<td><label for="tab_name">Tabindex:</label> <input type="text" value="<?php echo $options['tab_name']; ?>" style="width:32px" name="tab_name" id="tab_name" /></td>
				</tr>		
				<tr>
					<th scope="row">Email field: </th>
					<td><label for="tab_email">Tabindex:</label> <input type="text" value="<?php echo $options['tab_email']; ?>" style="width:32px" name="tab_email" id="tab_email" /></td>
				</tr>	
				<tr>
					<th scope="row">Phone field: </th>
					<td><label for="tab_phone">Tabindex:</label> <input type="text" value="<?php echo $options['tab_phone']; ?>" style="width:32px" name="tab_phone" id="tab_phone" /> <label for="showphone" style="padding-left: 10px">Show?</label> <select id="showphone" name="showphone">
						<option<?php if ($options['showphone']=='yes') echo ' selected="selected"'; ?>>yes</option>
						<option<?php if ($options['showphone']=='no') echo ' selected="selected"'; ?>>no</option>
					</select></td>
				</tr>	
				<tr>
					<th scope="row">Website field: </th>
					<td><label for="tab_url">Tabindex:</label> <input type="text" value="<?php echo $options['tab_url']; ?>" style="width:32px" name="tab_url" id="tab_url" /> <label for="showwebsite" style="padding-left: 10px">Show?</label> <select id="showwebsite" name="showwebsite">
						<option<?php if ($options['showwebsite']=='yes') echo ' selected="selected"'; ?>>yes</option>
						<option<?php if ($options['showwebsite']=='no') echo ' selected="selected"'; ?>>no</option>
					</select></td>
				</tr>	
				<tr>
					<th scope="row">Reason field: </th>
					<td><label for="tab_reason">Tabindex:</label> <input type="text" value="<?php echo $options['tab_reason']; ?>" style="width:32px" name="tab_reason" id="tab_reason" /></td>
				</tr>	
				<tr>
					<th scope="row">Message field: </th>
					<td><label for="tab_message">Tabindex:</label> <input type="text" value="<?php echo $options['tab_message']; ?>" style="width:32px" name="tab_message" id="tab_message" /></td>
				</tr>	
				<tr>
					<th scope="row">Spam-Q field: </th>
					<td><label for="tab_spam">Tabindex:</label> <input type="text" value="<?php echo $options['tab_spam']; ?>" style="width:32px" name="tab_spam" id="tab_spam" /></td>
				</tr>	
				<tr>
					<th scope="row">Why? Link: </th>
					<td><label for="tab_why">Tabindex:</label> <input type="text" value="<?php echo $options['tab_why']; ?>" style="width:32px" name="tab_why" id="tab_why" /></td>
				</tr>
				<tr>
					<th scope="row">Carbon Copy checkbox: </th>
					<td><label for="tab_cc">Tabindex:</label> <input type="text" value="<?php echo $options['tab_cc']; ?>" style="width:32px" name="tab_cc" id="tab_cc" /> <label for="show_cc" style="padding-left: 10px">Show?</label> <select id="show_cc" name="gb_show_cc">
						<option<?php if ($options['gb_show_cc']=='yes') echo ' selected="selected"'; ?>>yes</option>
						<option<?php if ($options['gb_show_cc']=='no') echo ' selected="selected"'; ?>>no</option>
					</select></td>
				</tr>
				<tr>
					<th scope="row">Submit button: </th>
					<td><label for="tab_submit">Tabindex:</label> <input type="text" value="<?php echo $options['tab_submit']; ?>" style="width:32px" name="tab_submit" id="tab_submit" /> <label for="send_button" style="padding-left: 10px">Submit button text:</label> <input type="text" value="<?php echo $options['send_button']; ?>" name="send_button" id="send_button" class="regular-text" /></td>
				</tr>
				<tr>
					<th scope="row">Show form credits line? </th>
					<td><select name="showcredit">
					<option<?php if ($options['showcredit']=='yes') echo ' selected="selected"'; ?>>yes</option>
					<option<?php if ($options['showcredit']=='no') echo ' selected="selected"'; ?>>no</option>
					</select></td>
				</tr>
			</table>
			<p class="jump"><a href="#wphead">Top</a></p>
		</fieldset>
		<fieldset class="options" id="ipb">
			<legend>Section 6 of 6 &raquo; <abbr><span class="abbr" title="Internet Protocol">IP</span></abbr> Blacklist</legend>		
			<table class="optiontable form-table"> 
				<tr valign="top">
					<th scope="row">Blacklisted <abbr><span class="abbr" title="Internet Protocol">IP</span></abbr>s: </th>
					<td><textarea rows="5" cols="45" name="blacklist" class="large-text code"><?php
					foreach ($options['ip_blacklist'] as $item){		
						$item = str_replace('"', "", "$item");
						$item = trim($item);					
						if (!empty($item)){
							echo $item;
							echo ''."\n";
						}
					} 
					?></textarea> <span class="description">Block IP addresses ONLY if necessary &ndash; Enter each on a new line</span></td>
				</tr>
			</table>
			<p class="jump"><a href="#wphead">Top</a></p>
		</fieldset>
		<p class="submit"><input type="hidden" name="sub" value="sub" /><input type="submit" class="btn button-primary" name="save" value="Save Form Configuration" /></p>
	</form>
</div>
<?php
}