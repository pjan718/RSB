<?php
################################################################################
// FORM STYLING OUTPUT
################################################################################
function gb_process_style($styleFile) {
//processes style page forms
if(!empty($_POST['saveFile'])){
//update css file
    $newfile = stripslashes($_POST['cssFile']);      	
if(is_writeable($styleFile)) {
    $f = fopen($styleFile, 'w+');
         fwrite($f, $newfile);
         fclose($f);

    $style=get_option('gb_style');
    $theme=get_option('gb_theme');
    $themename=$theme;

    echo ' <div id="message" class="updated fade"><p><strong>The &#8220;'.$themename.'&#8221; Stylesheet Has Been Updated</strong></p></div>'."\n";
} else {

    $style=get_option('gb_style');
    $theme=get_option('gb_theme');
    $themename=$theme;

    echo ' <div id="message" class="updated fade"><div class="update error" style="padding-top:14px;padding-bottom:14px;margin-left:15px;"><strong>Write Error!</strong> The &#8220;<code>'.$themename.'</code>&#8221; file is not currently editable/writable! File permissions must first be changed.</p></div>
   <p>To make the file editable, use your server admin or an <abbr><span class="abbr" title="File Transfer Protocol">FTP</span></abbr> program and go to <code>/wp-content/plugins/wp-gbcf/wp-gbcf_themes/</code> and change the file permissions of the <abbr><span class="abbr" title="Cascading Style Sheet">CSS</span></abbr> file to <code class="reg-alert">666</code>. You should then be able to edit the selected file. If you have made a lot of edits and wish not to lose them, change the &#8220;<code>'.$themename.'</code>&#8221; file permissions then simply refresh this page with your browser.</p></div>'."\n";
 }
} else if (!empty($_POST['saveTheme'])){
    $theme=$_POST['theme'];
if($theme=='none'){
      update_option('gb_style', 'none');
      update_option('gb_theme', '');
} else if ($theme=='custom'){
      update_option('gb_style', 'theme');
      update_option('gb_theme', 'custom.css');
} else {
      update_option('gb_style', 'theme');
      update_option('gb_theme', $_POST['theme']);
} 

    $style=get_option('gb_style');
    $theme=get_option('gb_theme');
    $themename=str_replace(".css", "", $theme);
if ($style=='none') {
    $themename = "No";
}

    echo ' <div id="message" class="updated fade"><p><strong>&#8220;'.$themename.'&#8221; Theme Selected</strong></p></div>'."\n";
 }
return($theme);
}

################################################################################
// FORM STYLING ADMIN
################################################################################
function gb_contact_form_admin_style(){
//make sure options exist for the style page
//config options
add_option('gb_style', 'theme', 'Method of styling the form', 'yes');						
add_option('gb_theme', 'default.css', 'Theme', 'yes');			

// $styleFile set to selected theme
    $style=get_option('gb_theme');           
    $styleFile = '../wp-content/plugins/wp-gbcf/wp-gbcf_themes/'.$style;
    $style=gb_process_style($styleFile);
?>
<div class="wrap gbcf">
	<div id="secureformsadminicon" class="icon32"><br/></div>
	<h2>Styling &laquo; Secure and Accessible <abbr><span class="abbr" title="PHP Hypertext Preprocessor">PHP</span></abbr> Contact Form</h2>
	<?php gb_check_config(); ?>
	<p>Use this page to modify your contact form&#8217;s styling, assign a specific pre-made theme, no theme, or create your own custom theme.</p> 

	<h3>Form Theme Selector</h3>
	<p>Select a pre-made theme, &#8220;none&#8221; to use your own style sheet, or select &#8220;custom.css&#8221; to create your own theme. <span class="lbump jump"><a href="admin.php?page=documentation#themes">Themes Help</a></span></p>
	<form action="admin.php?page=styling" method="post" id="gb_form" name="configtheme">
		<fieldset class="options" id="gen">
			<legend>Selected Form Theme</legend>		
				<table class="optiontable form-table"> 
					<tr> 
						<th scope="row">Choose a theme <abbr><span class="abbr" title="Cascading Style Sheet">CSS</span></abbr>: </th> 
						<td><select style="cursor:pointer;" name="theme"><?php
							// read css file in theme directory and magically add them
							   $d='../wp-content/plugins/wp-gbcf/wp-gbcf_themes'; #define which dir you want to read
							   $dir = opendir($d); #open directory
							   $found=false;
							   $printcustom=false;
							   $printcustomsel=false;
							while ($f = readdir($dir)) {
							if(eregi("\.css",$f)){ #if filename matches .txt in the name
							if($f<>'custom.css') {
							if($f==get_option('gb_theme')) {
							    echo '<option selected="selected">'.$f.'</option>'."\n";
							      $found=true;
							} else {
							    echo '<option>'.$f.'</option>';
							 }									
							} else {
							if ($f==get_option('gb_theme')) {
							    $printcustomsel=true;
							    $found=true;
							} else {
							    $printcustom=true;    								 
							   }					
							  }
							 }
							}
							if ($printcustomsel==true){
							    echo '<option selected="selected">custom.css</option>'."\n";
							} else if ($printcustom==true){
							    echo '<option>custom.css</option>';
							}
							if ($found==false){
							    echo '<option selected="selected">none</option>'."\n";
							} else {
							    echo '<option>none</option>';
							}
					?></select></td> 
				</tr>
			</table>
			<p class="submit"><input class="btn button-primary" type="submit" value="Select Theme" name="saveTheme" /></p>
		</fieldset> 		
 	</form>
</div>
<?php
if (get_option('gb_style')<>'none') {
// custom is selected so output the options
?>
<div id="edt" class="wrap">
<h2>Form Theme Editor</h2>
 <p>Use this simple <abbr><span class="abbr" title="Cascading Style Sheet">CSS</span></abbr> file editor to modify your form&#8217;s currently selected <strong><?php echo $tname; ?></strong> style sheet file. <span class="lbump jump"><a href="admin.php?page=documentation#editor">Editor Help</a></span></p>
 <form method="post" action="admin.php?page=styling">
  <fieldset>
   <legend>Contact Form Style File Editor. Now Editing: <code style="font-size:1.2em;"><?php echo $tfile; ?></code></legend>
    <textarea rows="20" cols="118" id="themebox" name="cssFile"><?php 
     $style=get_option('gb_theme');		
     $styleFile = '../wp-content/plugins/wp-gbcf/wp-gbcf_themes/'.$style;
//readfile("".$styleFile.""); 
if(!is_file($styleFile))
     $error = 1;
if(!$error && filesize($styleFile) > 0) {
     $f="";
     $f = fopen($styleFile, 'r');
     $file = fread($f, filesize($styleFile));
    echo $file;
      fclose($f);
} else 
    echo 'Sorry. The file you are looking for could not be found';
		?></textarea>
   <p class="submit"><input class="btn" type="submit" style="padding:5px 30px 5px 30px;" value="Save Changes" name="saveFile" /></p>
  </fieldset>
</form>
</div>
<?php 
//end custom styling
 }
}