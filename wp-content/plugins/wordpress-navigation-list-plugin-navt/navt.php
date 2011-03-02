<?php
/**
Plugin Name: Wordpress Navigation List Plugin NAVT
Plugin URI: http://atalayastudio.com
Description: Create, organize and manage your web site navigation by logically grouping your pages, categories and user's via a drag'n drop interface. Manage your navigation lists from the NAVT Lists menu tab in the Manage menu.
Version: 1.0.34
Author: Greg Bellucci
Author URI: http://gbellucci.us

 -----------------------------------------------------------------------------
 $Id: navt.php 138443 2009-07-23 17:16:00Z gbellucci $:
 $Date: 2009-07-23 17:16:00 +0000 (Thu, 23 Jul 2009) $:
 $Revision: 138443 $:
 -----------------------------------------------------------------------------


 * @since 1.0.28
 *
 * Due to the number of possible WordPress directory installation choices there can be... NAVT dynamically creates an include file
 * (wp-root.php) that defines the WordPress installation root directory for use by this plugin. If the include file cannot be created, this plugin
 * will not work. Dynamic creation of the include file corrects a problem with NAVT not being able to successfully include the WordPress
 * file "wp-config.php".
 *
 * If you MOVE your WordPress installation or MOVE the wp-content directory then you must delete NAVT's wp-root.php file. The file
 * will be recreated.
 *
 */
//define('WP_DEBUG', true);
define("WP_ROOT_INC", dirname(__FILE__) . "/wp-root.php");

if( !file_exists(WP_ROOT_INC) ) {
    @chmod(dirname(__FILE__), 0777); // @since 1.0.29
    $fh = @fopen(WP_ROOT_INC, "wb"); // open for writing
    if( $fh == false ){
        /**
         * just return... can't create the file.
         * This leaves the plugin disabled (but activated)
         */
        @chmod(dirname(__FILE__), 0755); // @since 1.0.29
        return;
    }
    else {
        // Fix the directory separator if necessary for the absolute path
        $abspath = (DIRECTORY_SEPARATOR != '/') ? str_replace(DIRECTORY_SEPARATOR, '/', ABSPATH): ABSPATH;

        // Create the include file
        $content = "<?php\n" .
        "/**\n" .
        " ** Date Created: " . date("l jS \of F Y h:i:s A") . "\n" .
        " ** Dynamically created by the NAVT WordPress plugin\n" .
        " ** Remove this file ONLY if you move or rename your WordPress site installation directory.\n" .
        " **/\n" .
        " define(\"WP_ROOTPATH\", \"". $abspath . "\"); // wp installation directory\n" .
        "\n?>";
        fwrite($fh, $content);
        fflush($fh);
        fclose($fh);
        @chmod(dirname(__FILE__), 0755); // @since 1.0.29
    }
}

define('NAVT_BASENAME', plugin_basename(dirname(__FILE__)));
$navt_root_url = (($_SERVER['HTTPS'] == "on") ?
str_replace("http://","https://", WP_PLUGIN_URL) : WP_PLUGIN_URL ) . '/' . NAVT_BASENAME; /** @since 1.0.31 */
$navt_root_dir = WP_PLUGIN_DIR . '/' . NAVT_BASENAME;

@define('NAVT_PLUGINPATH', (DIRECTORY_SEPARATOR != '/') ?
str_replace(DIRECTORY_SEPARATOR, '/', $navt_root_dir) : $navt_root_dir);

/**
 * These includes are relative to the current directory
 */
require_once('includes/navtinc.php');
require_once('includes/browser.php');
require('app/navt.php');
require_once('app/navt_be.php');
require_once('app/navt_fe.php');

/**
 * API function call
 *
 * @param string $sNavGroupName - group name to be displayed
 * @param boolean $bEcho - (default is true=echo the HTML output)
 * @param string $sTitle - optional title
 * @param string $sBefore - opening tag (default=ul)
 * @param string $sAfter - closing tag (default=/ul)
 * @param string $sBeforeItem - open item tag (default=li)
 * @param string $sAfterItem - close item tag (default=/li)
 * @return string - HTML output (if bEcho is true)
 */
function navt_getlist($sNavGroupName, $bEcho=true, $sTitle='', $sBefore='ul', $sAfter='/ul', $sBeforeItem='li', $sAfterItem='/li') {
    $out = NAVT_FE::getlist($sNavGroupName, $sTitle, $sBefore, $sAfter, $sBeforeItem, $sAfterItem);
    if( $bEcho ) {
        echo $out;
        $out = null;
    }
    return($out);
}

// load language domain
function navt_loadtext_domain() {

    $locale = 'en_US';

    if (defined('WPLANG')) {
        $locale = WPLANG;
    }

    if (empty($locale)) {
        $locale = 'en_US';
    }

    $mofile = NAVT_PLUGINPATH . "/app/lang/navt-$locale.mo";
    load_textdomain('navt_domain', $mofile);
}

?>