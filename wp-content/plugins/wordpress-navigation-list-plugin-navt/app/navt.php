<?php
/**
 * NAVT Word Press Plugin
 * Copyright (c) 2006-2008 Greg A. Bellucci/Atalaya Studio
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software
 * and associated documentation files (the "Software"), to deal in the Software without restriction,
 * including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT
 * LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @package NAVT Word Press Plugin
 * @subpackage navt class
 * @author Greg A. Bellucci <greg[AT]gbellucci[DOT]us
 * @copyright Copyright &copy; 2006-2008 Greg A. Bellucci
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * -----------------------------------------------------------------------------
 * $Id: navt.php 134443 2009-07-12 13:37:29Z gbellucci $:
 * $Date: 2009-07-12 13:37:29 +0000 (Sun, 12 Jul 2009) $:
 * $Revision: 134443 $:
 * -----------------------------------------------------------------------------
 */
global $wp_db_version;
if($wp_db_version >= 7796) {
    require('navt_widget.php'); // includes the widget code
}
else {
    require('navt_widget2.5.php');
}

/**
 * @global $navt_map
 */
global $navt_map;
global $brz;

/**
 * Navigation plugin class
 */
class NAVT {

    /**
    * plugin init
    * @uses $navt_map
    */
    function init() {
        do_action('dbnavt', NAVT_INIT, sprintf("\n%s::%s - start\n", __CLASS__, __FUNCTION__));

        global $navt_map;
        global $brz;
        $navt_map = array();

        $role = get_role('administrator');
        if( is_object($role) && !empty($role) ) {
            $role->add_cap('manage_navt_lists');
        }
        /**
         * add editor role capability
         * @since 1.0.27
         */
        $role = get_role('editor');
        if( is_object($role) && !empty($role)) {
            $role->add_cap('manage_navt_lists');
        }

        /**
         * Load the language domain
         */
        navt_loadtext_domain();

        if( class_exists('browzer') ) {
            $brz = new Browzer();
            do_action('dbnavt', NAVT_INIT, sprintf("%s::%s Browser name: %s version: %s\n",
            __CLASS__, __FUNCTION__, $brz->Name, $brz->Version));
        }

        /**
         * Initialize the plugin for the backend
         */
        NAVT::install_check();
        NAVT::register_scripts();

        /**
         * Determine which page is being loaded
         */
        $uri = $_SERVER['REQUEST_URI'];

        if( stristr($uri, 'navt_restore.php') != false ) {
            add_action('wp_print_scripts', array('NAVT', 'restore_head'), 1);
        }

        if( stristr($uri, 'plugins.php') != false ) {
            add_action('admin_print_scripts', array('NAVT', 'plugin_page'), 1);
        }
        else {
            /**
             * Add header information to the backend header for
             * certain backend pages
             */
            if((stristr($uri,'page.php') !== false) || (stristr($uri, 'plugins.php') !== false) ||
            (stristr($uri, 'categories.php') !== false) || (stristr($uri, 'user-edit.php') !== false) ) {
                add_action('admin_print_scripts', array('NAVT', 'admin_head'), 1);
            }
        }

        /**
         * Set hooks for posts
         */
        add_action('save_post', array('NAVT_BE', 'save_post_wpcb'), 10, 2);
        add_action('delete_post', array('NAVT_BE', 'delete_post_wpcb'));

        /**
         * Set hooks for user profiles
         */
        add_action('profile_update', array('NAVT_BE', 'profile_update_wpcb'));
        add_action('user_register', array('NAVT_BE', 'user_register_wpcb'));
        add_action('delete_user', array('NAVT_BE', 'delete_user_wpcb'));

        /**
         * Set hooks for categories
         */
        add_action('created_term', array('NAVT_BE', 'created_category_wpcb')); // @since 95.30
        add_action('edited_term', array('NAVT_BE', 'edited_category_wpcb')); // @since 95.30
        add_action('delete_term', array('NAVT_BE', 'delete_category_wpcb'));

        /**
         * Admin menu
         */
        add_action('admin_menu', array('NAVT', 'admin_menu_wpcb'), 10);

        /**
         * category exclusions
         */
        add_filter('list_terms_exclusions', array('NAVT_BE', 'list_terms_exclusions_wpcb'));

        /**
         * Content filer hook (v1.0.15: added short code)
         */
        if( function_exists('add_shortcode') ) { // wordpress 2.5+
            add_shortcode('navt', array('NAVT', 'navt_content'));
        }
        else {
            // wordpress 2.3
            add_filter('the_content', array('NAVT_FE','the_content_wpcb'));
        }

        /**
         * Attach the plugin to the theme
         */
        add_action('wp_head', array('NAVT_FE', 'wp_head_wpcb'), 10);

        // kick off a backup?

        /**
         * Temporarily removed @since 1.0.27 (12/19/08)
         * @todo - fix backup/restore
         *
        if(isset($_REQUEST['navtbackuprequest'])) {
            NAVT::do_backup();
            die();
        }
        */

    }// end function


    /**
     * Content shortcode handler for embedded navt lists
     *
     * [navt id="group name"]
     *
     * @param array $atts
     * @param string $content
     */
    function navt_content($atts, $content=null) {
        $out = '';
        $id = '@nolist@';
        $title = '@notitle@';
        
        do_action('dbnavt', NAVT_GEN, sprintf("%s::%s attributes\n", __CLASS__, __FUNCTION__), $atts);
        do_action('dbnavt', NAVT_GEN, sprintf("%s::%s - content: %s\n", __CLASS__, __FUNCTION__, $content));
        extract( shortcode_atts( array( 'id' => '@nolist@', 'title' => '@notitle@'), $atts) );

        if($id != '@nolist@' && $id != '') {
            $id = strip_tags($id);
            $id = stripslashes($id);

            if( $id != '' ) {
                do_action('dbnavt', NAVT_GEN, sprintf("%s::%s - id: %s\n", __CLASS__, __FUNCTION__, $id));

                if($title != '@notitle@' && $title != '') {
                    $title = strip_tags($title);
                    $title = stripslashes($title);
                    do_action('dbnavt', NAVT_GEN, sprintf("%s::%s - title: %s\n", __CLASS__, __FUNCTION__, $title));
                }
                else {
                    $title = '';
                }

                $_POST['navt-noanounc'] = 1; // turn off NAVT announcment comment
                $out = navt_getlist($id, false, $title);
                unset($_POST['navt-noanounc']);
            }
        }

        do_action('dbnavt', NAVT_GEN, sprintf("%s::%s - out: %s\n", __CLASS__, __FUNCTION__, $out));
        return($out);
    }

    /**
     * Registered callback function - adds the plugin options to the Management submenu
     *
     * @see Word Press function add_action('admin_menu')
     * @since 95.40 - moved plugin admin from plugin page to the bottom of the list page
     * this is because WordPress MU can block plugin page access by users.
     *
     */
    function admin_menu_wpcb() {

        // Add this menu item to the "Manage" menu to manage menus
        if ( function_exists('add_management_page') ) {
            if( current_user_can('manage_navt_lists') ) {
                $page = add_management_page('Menu Management', __('NAVT Lists', 'navt_domain'),
                3, __FILE__, array('NAVT', 'configure'));

                // admin print script callback for NAVT List page
                add_action('admin_print_scripts-'. $page, array('NAVT', 'navt_list_page'));
            }
        }
    }// end function

    /**
     * Registered Word Press callback that displays plugins administration page
     */
    function configure() {
        do_action('dbnavt', NAVT_INIT, sprintf("%s::%s - start\n", __CLASS__, __FUNCTION__));

        // includes the option-form script
        if(!current_user_can('manage_navt_lists')) {
            die(__('Access Denied','navt'));
        }

        // kick off a restore?
        if( (isset( $_REQUEST['navt_action'] ) && $_REQUEST['navt_action'] == 'restore') ||
        isset($_REQUEST['navtrestorerequest']) ) {
            do_action('dbnavt', NAVT_INIT, sprintf("%s::%s including navt_restore php\n", __CLASS__, __FUNCTION__));
            include(NAVT_PLUGINPATH . '/app/navt_restore.php');
        }
        else {
            do_action('dbnavt', NAVT_INIT, sprintf("%s::%s including navt_display.php php\n", __CLASS__, __FUNCTION__));
            include(NAVT_PLUGINPATH . '/app/navt-display.php');
        }

        do_action('dbnavt', NAVT_INIT, sprintf("%s::%s - end\n", __CLASS__, __FUNCTION__));

    }// end function

    /**
     * Registered WordPress callback that adds css and script information to the admin page header.
	 */
    function navt_list_page() {
        wp_deregister_script('jquery');
        wp_deregister_script('prototype');
        wp_deregister_script('scriptaculous');

        wp_print_scripts(array('navt_admin')); ?>

<script type="text/javascript">
//<![CDATA[
var navtpath = '<?php navt_output_url();?>';
//]]>
</script>
<?php printf("<!-- %s v%s -->\n", NAVT_SCRIPTNAME, NAVT_SCRIPTVERS); ?>
<link type="text/css" rel="stylesheet" href="<?php navt_output_url(); ?>/css/navt.css" media="screen"/>
<?php
if(isset($_REQUEST['navtrestorerequest'])) {?>
    <link type="text/css" rel="stylesheet" href="<?php navt_output_url(); ?>/css/restore.css" media="screen"/>
<?php } ?>
<!--[if lt IE 7]><link rel="stylesheet" href="<?php navt_output_url();?>/css/navtIe6.css" type="text/css" media="screen" /><![endif]-->
<!--[if IE 7]><link rel="stylesheet" href="<?php navt_output_url();?>/css/navtIe7.css" type="text/css" media="screen" /><![endif]-->
    <?php
    }// end function

    /**
     * Registered callback for the plugins page
     */
    function plugin_page() {
       ?>
<script type="text/javascript">
//<![CDATA[
var navtpath = '<?php navt_output_url();?>';
//]]>
</script>
	   <?php
	   wp_print_scripts(array('navt_options_js'));
    }

    /**
     * Registered callback for backend page/category and user screens
     */
    function admin_head() {
    }

    /**
     * Checks NAVT installation
     */
    function install_check() {
        global $navt_map;

        // get the timestamp on this file and the last one we saved
        do_action('dbnavt', NAVT_INIT, sprintf("%s::%s - start\n", __CLASS__, __FUNCTION__));
        $dir_timestamp = filemtime(dirname(__FILE__));

        if( defined('NAVT2') ) {
            // versions 1.0.0 and above
            $this_version = (int) ((NAVT_MAJVER * 1000000) + (NAVT_MINVER * 100000) + (NAVT_BUILD * 10000));
        }
        else {
            // versions before 1.0.0
            if(defined('MAJVER') && defined('MINVER')) {
                $ver = MAJVER.MINVER;
                $ver .= (defined('BUILD') ? BUILD: '0');
                $this_version  = (intval($ver, 10) & 0xffff);
            }
            else {
                $this_version = 0; // don't know what this is
            }
        }

        $installed_ver = NAVT::get_installed_version();
        $is_installed  = NAVT::get_option(INSTALLED);
        $is_installed = (( empty($is_installed) || false === $is_installed ) ? 0: $is_installed);

        do_action('dbnavt', NAVT_INIT, sprintf("%s::%s installed version: %d, running version: %d\n",
        __CLASS__, __FUNCTION__, $installed_ver, $this_version));

        /**
         * Check for a version upgrade
         */
        if( $is_installed && ($installed_ver != $this_version) ) {
            // convert the configuration if necessary
            NAVT::data_conversion($installed_ver, $this_version);
            NAVT::update_option(LASTMODIFIED, $dir_timestamp, 'last updated');
            NAVT::update_option(VERSIONID, $this_version);
            $f = NAVT::get_option(FORMNUM);
            if( $f == false ) {NAVT::add_option(FORMNUM, 1);}
        }
        else {
            if( !$is_installed ) {
                // install for the first time
                do_action('dbnavt', NAVT_INIT, sprintf("%s::%s - installing plugin\n", __CLASS__, __FUNCTION__));
                NAVT::build_assets();

                // make sure we install clean options
                NAVT::uninstall();

                NAVT::add_option(SCHEME, '1', NAVT_SCRIPTNAME.' next group color scheme');
                NAVT::add_option(VERSIONID, $this_version, NAVT_SCRIPTNAME . ' installed version');
                NAVT::add_option(ASSETS, $m, NAVT_SCRIPTNAME.' Navigation assets');
                NAVT::add_option(LASTMODIFIED, $dir_timestamp, NAVT_SCRIPTNAME.' version tracking.');
                NAVT::add_option(INSTALLED, 1, NAVT_SCRIPTNAME . ' installation status');
                NAVT::add_option(ICONFIG, array(), NAVT_SCRIPTNAME . ' navigation item configuration');
                NAVT::add_option(GCONFIG, array(), NAVT_SCRIPTNAME . ' group options');
                NAVT::add_option(FORMNUM, 1, NAVT_SCRIPTNAME . ' next form number');

                //NAVT::add_option(VERCHECK, 0, SCRIPTNAME . ' automatic version checking (Off by default)');
                //NAVT::add_option(DEF_ADD_GROUP, ID_DEFAULT_GROUP, NAVT_SCRIPTNAME.' default navigation group.');
                //NAVT::add_option(ITEM_CRC, array(), NAVT_SCRIPTNAME . ' item configuration checksum');
                //NAVT::add_option(GROUP_CRC, array(), NAVT_SCRIPTNAME . ' group configuration checksum');
            }
        }

        $navt_map = array();
        do_action('dbnavt', NAVT_INIT, sprintf("%s::%s - end\n", __CLASS__, __FUNCTION__));
    }

    /**
     * Plugin release conversions
     * Convert from the user's current release to the new one if necessary
     *
     * @uses $navt_map
     *
     * @param string $installed_version
     * @param string $this_version
     */
    function data_conversion($installed_version, $this_version = 0 ) {
        global $navt_map;

        do_action('dbnavt', NAVT_INIT, sprintf("%s::%s - start\n",__CLASS__, __FUNCTION__));

        $installed_version = (empty($installed_version) || isBlank($installed_version) ? 1 : (intval($installed_version, 10)));
        $this_version = intval($this_version, 10);
        do_action('dbnavt', NAVT_INIT, sprintf("%s::%s - installed: %s\n",__CLASS__, __FUNCTION__, $installed_version));

        /**
         * For versions prior to .95.42
         */
        if( ($installed_version < 9542) && ($this_version >= 9542) ) {

            // .95.30 adds ext field
            // .95.42 adds ex2 field
            do_action('dbnavt', NAVT_INIT, sprintf("%s::%s 9452+ conversion\n", __CLASS__, __FUNCTION__));
            $new_map = array();
            $old_map = NAVT::load_map();

            for( $idx = 0; $idx < count($old_map); $idx++ ) {
                $ttl = $old_map[$idx][TTL];
                $nme = $old_map[$idx][NME];
                $nme = wp_specialchars( $nme, 1 );
                $ttl = wp_specialchars( $ttl, 1 );
                $nme = (isBlank($nme)) ? $ttl: $nme;    // make sure this is not empty
                $grp = strtoupper($old_map[$idx][GRP]); // forces names to upper case

                $new_map = array( GRP => $grp, TYP => $old_map[$idx][TYP], IDN => $old_map[$idx][IDN], TTL => $ttl,
                NME => $nme, OPT => $old_map[$idx][OPT], EXT => $old_map[$idx][EXT], LVL => '0', EX2 => '');
            }

            // Update the map and the installed version id
            NAVT::update_option(SERIALIZED_MAP, $new_map);
            $navt_map = array();
        }

        /**
         * For versions prior to .96
         */
        if( ($installed_version < 9600) && ($this_version >= 9600) ) {
            do_action('dbnavt', NAVT_INIT, sprintf("%s::%s 9600+ conversion\n",__CLASS__, __FUNCTION__));
            $map = NAVT::load_map();

            if( $map === false ) {
                // tried to load the map of a previous NAVT version but it isn't there.
                // just build the assets
                do_action('dbnavt', NAVT_INIT, sprintf("%s::%s - data conversion empty map option\n",__CLASS__, __FUNCTION__));
                $map = array(); // use an empty map
            }

            $avatar = NAVT::get_url() . '/' . IMG_AVATAR;

            $assets = array();        // indexed by item type and idn
            $config = array();        // indexed by group and css selector id
            $group_options = array(); // indexed by group name

            $assets[TYPE_ELINK][ELINKIDN] = array(
            TYP => TYPE_ELINK, IDN => ELINKIDN,
            TTL => 'http://',
            NME => __('User defined URI', 'navt_domain'),
            OPT => '0', EXT => '', LVL => '0', EX2 => '');

            $assets[TYPE_SEP][SEPIDN] = array(
            TYP => TYPE_SEP, IDN => SEPIDN,
            TTL => __('List divider', 'navt_domain'),
            NME => __('List divider', 'navt_domain'),
            OPT => '0', EXT => '', LVL => '0', EX2 => '');

            $assets[TYPE_LINK][HOMEIDN] = array(
            TYP => TYPE_LINK, IDN => HOMEIDN,
            TTL => __('Home', 'navt_domain'),
            NME => __('Home', 'navt_domain'),
            OPT => '0', EXT => '', LVL => '0', EX2 => '');

            $assets[TYPE_LINK][LOGINIDN] = array(
            TYP => TYPE_LINK, IDN => LOGINIDN,
            TTL => __('Sign in', 'navt_domain'),
            NME => __('Sign in', 'navt_domain'),
            OPT => '0', EXT => '', LVL => '0', EX2 => '');

            // Create the 'asset' array -
            // contains each wp asset
            for( $idx = 0; $idx < count($map); $idx++ ) {

                if( $map[$idx][TYP] === TYPE_PAGE || $map[$idx][TYP] === TYPE_CAT || $map[$idx][TYP] === TYPE_AUTHOR ) {

                    $opt = '0';
                    $ext = '';
                    $nme = wp_specialchars( $map[$idx][NME], 1 );
                    $ttl = wp_specialchars( $map[$idx][TTL], 1 );
                    $nme = (isBlank($nme) ? $ttl: $nme);

                    if( TYPE_AUTHOR === $map[$idx][TYP] ) {
                        $opt = (SHOW_AVATAR | USE_DEF_AVATAR);
                        $ext = $avatar;
                    }
                    elseif( TYPE_PAGE === $map[$idx][TYP] ) {
                        $is_draft = (( intval($map[$idx][OPT], 10) & ISDRAFTPAGE ) ? 1: 0);
                        if($is_draft) {
                            $opt = ISDRAFTPAGE;
                        }
                    }

                    // indexed by type and idn
                    $assets[ $map[$idx][TYP] ][ $map[$idx][IDN] ] = array(
                    TYP => $map[$idx][TYP], IDN => $map[$idx][IDN], TTL => $ttl,
                    NME => $nme, OPT => $opt, LVL => '0', EXT => $ext, EX2 => ''
                    );
                }
            }

            // Create the 'config' array -
            // contains each configured item
            $seq = 10000;
            for( $idx = 0; $idx < count($map); $idx++ ) {

                $grp = $map[$idx][GRP];

                if( $grp != ID_DEFAULT_GROUP && !isBlank($grp) ) {
                    $item = $map[$idx];
                    $id = NAVT::make_id($item, $seq++);
                    $grp = strtolower($grp);
                    $grp = substr($grp, 0, MAX_GROUP_NAME);
                    $opt = (intval($item[OPT], 10) & 0xffff);

                    if(!array_key_exists($grp, $group_options) ) {
                        // add group to the group options array
                        //do_action('dbnavt', NAVT_INIT, sprintf("%s::%s group options created for: %s\n",
                        //__CLASS__, __FUNCTION__, $grp));
                        $group_options[$grp] = NAVT::mk_group_config();
                    }

                    if($opt & HAS_DD_OPTION) {
                        if( !($group_options[$grp]['options'] & HAS_DD_OPTION) ) {
                            // move this option to the group
                            $group_options[$grp]['options'] |= HAS_DD_OPTION;
                        }
                        // remove this option from the item
                        $opt -= HAS_DD_OPTION;
                    }

                    if($opt & HAS_NOSTYLE) {
                        if( !($group_options[$grp]['options'] & HAS_NOSTYLE) ) {
                            // move this option to the group
                            $group_options[$grp]['options'] |= HAS_NOSTYLE;
                        }
                        // remove this option from the item
                        $opt -= HAS_NOSTYLE;
                    }

                    $nme = wp_specialchars( $item[NME], 1 );
                    $ttl = wp_specialchars( $item[TTL], 1 );
                    $nme = (isBlank($nme) ? $ttl: $nme);

                    // indexed by group and id
                    $config[$grp][ $id ] = array(
                    GRP => $grp, TYP => $item[TYP], IDN => $item[IDN], TTL => $ttl,
                    NME => $nme, OPT => $opt, LVL => $item[LVL], EXT => $item[EXT],
                    EX2 => $item[EX2]);

                    //do_action('dbnavt', NAVT_INIT, sprintf("%s::%s group: %s id: %s\n",
                    //__CLASS__, __FUNCTION__, $grp, $id));
                    //do_action('dbnavt', NAVT_INIT, sprintf("%s::%s group options\n",
                    //__CLASS__, __FUNCTION__), $config[$grp][$id]);
                }
            }// end for */

            //do_action('dbnavt', NAVT_INIT, sprintf("%s::%s assets\n",__CLASS__, __FUNCTION__), $assets);
            //do_action('dbnavt', NAVT_INIT, sprintf("%s::%s group options\n",__CLASS__, __FUNCTION__), $group_options);
            //do_action('dbnavt', NAVT_INIT, sprintf("%s::%s config\n",__CLASS__, __FUNCTION__), $config);

            NAVT::update_option(ASSETS, $assets);
            NAVT::update_option(ICONFIG, $config);
            //$sum = NAVT::calc_checksum($config);
            //NAVT::update_option(ITEM_CRC, $sum, 'item');
            //do_action('dbnavt', NAVT_INIT, sprintf("%s::%s - item checksum: %s\n",__CLASS__, __FUNCTION__, $sum));

            NAVT::update_option(GCONFIG, $group_options);
            //$sum = NAVT::calc_checksum($group_options);
            //NAVT::update_option(GROUP_CRC, $sum, 'group');
            //do_action('dbnavt', NAVT_INIT, sprintf("%s::%s - group checksum: %s\n",__CLASS__, __FUNCTION__, $sum));
            NAVT::delete_option(SERIALIZED_MAP); // remove this
            $navt_map = array();
        }

        if( ($installed_version < 1230000) && ($this_version >= 1230000) ) {
            do_action('dbnavt', NAVT_INIT, sprintf("%s::%s 122+ conversion\n",__CLASS__, __FUNCTION__));

            $n_icfg = array();
            $n_assets = array();
            $assets = NAVT::get_option(ASSETS);
            $icfg   = NAVT::get_option(ICONFIG);

            $assets[TYPE_CODE][CBIDN] = array(
            TYP => TYPE_CODE, IDN => CBIDN,
            TTL => __('Code block', 'navt_domain'),
            NME => __('Code block', 'navt_domain'),
            OPT => '0', EXT => '', LVL => '0', EX2 => '');

            if(is_array($assets)) {
                foreach( $assets as $type ) {
                    foreach($type as $item) {
                        $item[OP2] = 0;
                        $item[EX3] = $item[EX4] = $item[EX5] = '';
                        $n_assets[$item[TYP]][$item[IDN]] = $item;
                    }
                }
            }

            foreach( $icfg as $group ) {
                foreach( $group as $member_id => $item ) {
                    $item[OP2] = 0;
                    $item[EX3] = $item[EX4] = $item[EX5] = '';
                    $n_icfg[$item[GRP]][$member_id] = $item;
                }
            }

            NAVT::update_option(ASSETS,  $n_assets);
            NAVT::update_option(ICONFIG, $n_icfg);

            do_action('dbnavt', NAVT_INIT, sprintf("%s::%s assets\n",__CLASS__, __FUNCTION__), $n_assets);
            do_action('dbnavt', NAVT_INIT, sprintf("%s::%s config\n",__CLASS__, __FUNCTION__), $n_icfg);
        }

        do_action('dbnavt', NAVT_INIT, sprintf("%s::%s - end\n",__CLASS__, __FUNCTION__));
        return;
    }

    /**
     * Creates a CRC value for item and group arrays
     *
     * @param array $ar
     * @param string $type - 'item' or 'group'
     * @return unsigned integer
     */
    function calc_checksum($ar, $type) {
        do_action('dbnavt', NAVT_INIT, sprintf("%s::%s type: %s\n",__CLASS__, __FUNCTION__, $type));

        $crc = 0;

        if( is_array($ar) && count($ar) > 0 ) {
            if( $type == 'group' ) {
                do_action('dbnavt', NAVT_INIT, sprintf("%s::%s group array\n",__CLASS__, __FUNCTION__), $ar);
            }

            else if( $type == 'item' ) {
                do_action('dbnavt', NAVT_INIT, sprintf("%s::%s item array\n",__CLASS__, __FUNCTION__), $ar);
                $s = '';
                foreach( $ar as $group_ar ) {
                    foreach( $group_ar as $member_id => $item ) {
                        $s .= implode('@@', $item);
                    }
                }
                do_action('dbnavt', NAVT_INIT, sprintf("%s::%s \texploded: %s\n",__CLASS__, __FUNCTION__, $s));
                $crc = crc32($s);
                do_action('dbnavt', NAVT_INIT, sprintf("%s::%s \tcrc: %u\n",__CLASS__, __FUNCTION__, $crc));
            }
        }

        return($crc);
    }

    /**
     * Create a default group configuration
     *
     * @return array
     */
    function mk_group_config() {
        return( array(
        'options' => USE_NAVT_DEFAULTS,
        'select_size' => 1,
        'css' => array('ulid' => '' , 'ul' => '', 'li' => '', 'licurrent' => '', 'liparent' => '', 'liparent_active' => ''),
        'selector' => array('xpath' => '', 'before' => '', 'after' => '', 'option' => 0),
        'display' => array('show_on' => (SHOW_ON_HOME | SHOW_ON_ARCHIVES | SHOW_ON_SEARCH |
                                         SHOW_ON_ERROR | SHOW_ON_PAGES | SHOW_ON_POSTS | SHOW_ON_CATS),
        'posts' => array('on_selected' => 'show', 'ids' => array()),
        'pages' => array('on_selected' => 'show', 'ids' => array()),
        'cats'  => array('on_selected' => 'show', 'ids' => array())
        )));
    }

    /**
     * Returns the version last saved in the data options
     *
     * @return integer
     */
    function get_installed_version() {

        $ver = NAVT::get_option(VERSIONID);
        if( empty($ver) || $ver === false ) {
            $ver = 1;
        }
        else {
            $ver = str_replace('.', '', $ver);
        }

        do_action('dbnavt', NAVT_INIT, sprintf("%s::%s returning %s\n", __CLASS__, __FUNCTION__, $ver));
        return($ver);
    }

    /**
     * Uninstalls the plugin - removes the plugin options from the database
     */
    function uninstall() {
        do_action('dbnavt', NAVT_INIT, sprintf("%s::%s - start\n", __CLASS__, __FUNCTION__));
        NAVT::delete_option(SERIALIZED_MAP); /* depreciated */
        NAVT::delete_option(LASTMODIFIED);
        NAVT::delete_option(SCHEME);
        NAVT::delete_option(VERSIONID);
        NAVT::delete_option(VERCHECK);
        NAVT::delete_option(DEF_ADD_GROUP);
        NAVT::delete_option(INSTALLED);
        NAVT::delete_option(TIPS); /* depreciated */
        NAVT::delete_option(ASSETS);
        NAVT::delete_option(ICONFIG);
        NAVT::delete_option(GCONFIG);
        NAVT::delete_option(ITEM_CRC);
        NAVT::delete_option(GROUP_CRC);
        do_action('dbnavt', NAVT_INIT, sprintf("%s::%s - end\n", __CLASS__, __FUNCTION__));
    }// end function

    /**
     * register scripts used by this plugin
     */
    function register_scripts() {

        //do_action('dbnavt', NAVT_INIT, sprintf("%s::%s - start\n", __CLASS__, __FUNCTION__));
        $url = NAVT::get_url();
        $jquery_ui_lib = '/js/jquery-ui';
        $jquery_plugins = '/js/plugins';

        wp_register_script('jquery121',       $url . '/js/jquery.js', array(), '1.2.1');
        wp_register_script('json',            $url . '/js/json.js', array(), '2.0');
        wp_register_script('rc_js',           $url . '/js/rc.js', array('jquery121'), '1.92');
        wp_register_script('navt_options_js', $url . '/js/navtoptions.js', array('jquery', 'json')); // wp jquery version

        // plugins
        wp_register_script('jquery_dim_ui',   $url . $jquery_plugins . '/jquery.dimensions.js', array('jquery121'), '1.0');
        wp_register_script('jquery_modal',    $url . $jquery_plugins . '/jquery.modal.js',  array('jquery_dim_ui'), '1.1.1');

        // ui
        wp_register_script('jquery_mouse_ui', $url . $jquery_ui_lib  . '/ui.mouse.js',  array('jquery_modal'), '1.0');
        wp_register_script('jquery_drag_ui',  $url . $jquery_ui_lib  . '/ui.draggable.js', array('jquery_mouse_ui'), '1.0');
        wp_register_script('jquery_drop_ui',  $url . $jquery_ui_lib  . '/ui.droppable.js', array('jquery_drag_ui'), '1.0');
        wp_register_script('jquery_sort_ui',  $url . $jquery_ui_lib  . '/ui.sortable.js',  array('jquery_drop_ui'), '1.0');

        wp_register_script('navt_admin', $url . '/js/navtadmin.js.php', array('jquery_sort_ui', 'rc_js', 'json'), NAVT_SCRIPTVERS);
        //do_action('dbnavt', NAVT_INIT, sprintf("%s::%s - end\n", __CLASS__, __FUNCTION__));
    }

    /**
     * Computes this plugin's url
     *
     * @return string URL
     */
    function get_url() {
        global $navt_root_url;
        return $navt_root_url;
    }// end function

    /**
     * Creates the run time configuration array from the ICONFIG array stored
     * in the database.
     *
     * @return array menu map
     */
    function load_map() {
        global $navt_map;

        $installed_version = NAVT::get_installed_version();
        do_action('dbnavt', NAVT_INIT, sprintf("%s::%s installed version %s\n", __CLASS__, __FUNCTION__, $installed_version));

        if( !is_array($navt_map) || count($navt_map) <= 0) {

            if( $installed_version < 9600 ) {
                do_action('dbnavt', NAVT_INIT, sprintf("%s::%s reading map\n", __CLASS__, __FUNCTION__));
                $navt_map = NAVT::get_option(SERIALIZED_MAP);
                if( $navt_map === false || empty($navt_map) ) {
                    $navt_map = array();
                }
            }
            else {
                // get the configuration data and convert it
                $groups = NAVT::get_option(ICONFIG);

                if( is_array($groups) && count($groups) > 0 ) {
                    foreach( $groups as $group => $members) {
                        if(is_array($members) && count($members) > 0) {
                            foreach($members as $member ) {
                                $navt_map[] = array(
                                GRP => $group,
                                TYP => $member[TYP],
                                IDN => $member[IDN],
                                TTL => $member[TTL],
                                NME => $member[NME],
                                OPT => $member[OPT],
                                EXT => $member[EXT],
                                LVL => $member[LVL],
                                EX2 => $member[EX2],
                                OP2 => $member[OP2],
                                EX3 => $member[EX3],
                                EX4 => $member[EX4],
                                EX5 => $member[EX5]
                                );
                            }
                        }
                    }
                }
            }
        }

        do_action('dbnavt', NAVT_INIT, sprintf("%s::%s version: %s\n", __CLASS__, __FUNCTION__,
        $installed_version), $navt_map);

        return($navt_map);
    }

    /**
     * Builds the assets configuration from the currently defined pages, categories and users
     *
     * @return array
     * @version .96
     */
    function build_assets( $page_order='post_title', $cat_order='name', $user_order='user_nicename') {
        global $wpdb;
        $assets = array();

        $page_order  = ($page_order == 'default' ? 'menu_order' : $page_order);
        $cat_order   = ($cat_order ==  'default' ? 'menu_order' : $cat_order);
        $user_order  = ($user_order == 'default' ? 'user_nicename' : $user_order);

        do_action('dbnavt', NAVT_INIT, sprintf("%s::%s - start\n", __CLASS__, __FUNCTION__));

        /**
         * Add default assets
         * @since .96.00
         */
        $assets[TYPE_ELINK][ELINKIDN] =  array(
        TYP => TYPE_ELINK, IDN => ELINKIDN,
        TTL => 'http://',
        NME => __('User defined URI', 'navt_domain'),
        OPT => 0, EXT => '', LVL => '0', EX2 => '',
        OP2 => 0, EX3 => '', EX4 => '', EX5 => '',
        'asset_ttl' => __('User defined URI', 'navt_domain'));

        $assets[TYPE_SEP][SEPIDN] = array(
        TYP => TYPE_SEP, IDN => SEPIDN,
        TTL => __('List divider', 'navt_domain'),
        NME => __('List divider', 'navt_domain'),
        OPT => 0, EXT => '', LVL => 0, EX2 => '',
        OP2 => 0, EX3 => '', EX4 => '', EX5 => '',
        'asset_ttl' => __('List divider', 'navt_domain'));

        $assets[TYPE_CODE][CBIDN] = array(
        TYP => TYPE_CODE, IDN => CBIDN,
        TTL => __('Code block', 'navt_domain'),
        NME => __('Code block', 'navt_domain'),
        OPT => 0, EXT => '', LVL => 0, EX2 => '',
        OP2 => 0, EX3 => '', EX4 => '', EX5 => '',
        'asset_ttl' => __('Code block', 'navt_domain'));

        $assets[TYPE_LINK][HOMEIDN] = array(
        TYP => TYPE_LINK, IDN => HOMEIDN,
        TTL => __('Home', 'navt_domain'),
        NME => __('Home', 'navt_domain'),
        OPT => 0, EXT => '', LVL => 0, EX2 => '',
        OP2 => 0, EX3 => '', EX4 => '', EX5 => '',
        'asset_ttl' => __('Home', 'navt_domain'));

        $assets[TYPE_LINK][LOGINIDN] = array(
        TYP => TYPE_LINK, IDN => LOGINIDN,
        TTL => __('Sign in', 'navt_domain'),
        NME => __('Sign in', 'navt_domain'),
        OPT => 0, EXT => '', LVL => 0, EX2 => '',
        OP2 => 0, EX3 => '', EX4 => '', EX5 => '',
        'asset_ttl' => __('Sign in', 'navt_domain'));

        /** Create the controls for each author
         */
        $users = $wpdb->get_results("SELECT ID FROM $wpdb->users ORDER BY $user_order");
        foreach($users as $user) {
            $u = get_userdata($user->ID);
            //do_action('dbnavt', NAVT_INIT, sprintf("%s::%s user\n", __CLASS__, __FUNCTION__), $u);

            $avatar = NAVT::get_url() . '/' . IMG_AVATAR;
            $opt = (SHOW_AVATAR | USE_DEF_AVATAR);
            $nme = ( isBlank($u->display_name) ? $u->user_login : $u->display_name);
            //do_action('dbnavt', NAVT_INIT, sprintf("%s::%s USER\n", __CLASS__, __FUNCTION__), $u);

            // Set options
            $assets[TYPE_AUTHOR][$user->ID] = array(
            TYP => TYPE_AUTHOR, IDN => $user->ID, TTL => $u->user_login, NME => $nme,
            OPT => $opt, EXT => $avatar, LVL => 0, EX2 => '',
            OP2 => 0, EX3 => '', EX4 => '', EX5 => '', 'asset_ttl' => $nme);
        }

        /**
         * Create the controls for each static page
         * page ordered by menu order showing wp hierarchy
         */
        $order = array();
        $sql = "SELECT * FROM $wpdb->posts WHERE post_type = 'page' ORDER BY $page_order";
        $pages = $wpdb->get_results($sql);
        $hier = get_page_hierarchy($pages);

        foreach( $hier as $page_id => $page_title ) {
            $p = get_page($page_id);
            $level = count($p->ancestors);
            $order[] = array('level' => $level, 'page' => $p);
        }

        foreach($order as $key => $page_data) {
            $page = $page_data['page'];
            $level = $page_data['level'];
            $id = (int) $page->ID;
            $ttl = $nme = $asset_ttl = (($page->post_title == '') ? __('no title', 'navt_domain') : $page->post_title);
            $nme = wp_specialchars( $nme, 1 ); // convert quotes

            if($level > 0) { for($x = 0; $x < $level; $x++) { $asset_ttl = '&#8212;' . $asset_ttl; }}
            $opt = (($page->post_status == 'draft') ? ISDRAFTPAGE: 0);
            //$opt |= (($page->post_status == 'private') ? ISPRIVATE: 0);
            $assets[TYPE_PAGE][$id] = array(TYP => TYPE_PAGE, IDN => $id, TTL => $ttl,
            NME => $nme, OPT => $opt, EXT => '', LVL => 0, EX2 => '',
            OP2 => 0, EX3 => '', EX4 => '', EX5 => '', 'asset_ttl' => $asset_ttl);
        }

        /**
         * Create the controls for each category
         * Ordered by category order showing wp hierarchy
         */
        $order = $t = array();
        $level = 0;
        $cats = (array) get_categories("hide_empty=0&type=category&orderby=$cat_order&order=ASC&hierarchical=1");

        // determine the category level
        foreach($cats as $cat) {
            if( $cat->parent == 0 ) {
                $t[$cat->cat_ID] = array('level' => 0, 'cat' => $cat);
            }
            else {
                $plevel = $t[$cat->parent]['level'] + 1;
                $t[$cat->cat_ID] = array('level' => $plevel, 'cat' => $cat);
            }
        }

        foreach( $t as $cat_id => $cat_data ) {
            $cat = $cat_data['cat'];
            $level = $cat_data['level'];
            $id = (int) $cat->cat_ID;
            $ttl = $nme = $asset_ttl = (($cat->name == '') ? __('no title', 'navt_domain') : $cat->name);
            $nme = wp_specialchars( $nme, 1 ); // convert quotes
            if($level > 0) { for($x = 0; $x < $level; $x++) { $asset_ttl = '&#8212;' . $asset_ttl; }}
            $assets[TYPE_CAT][$id] = array(TYP => TYPE_CAT, IDN => $id, TTL => $ttl,
            NME => $nme, OPT => $opt, EXT => '', LVL => 0, EX2 => '',
            OP2 => 0, EX3 => '', EX4 => '', EX5 => '', 'asset_ttl' => $asset_ttl);
            //do_action('dbnavt', NAVT_INIT, sprintf("%s::%s asset\n", __CLASS__, __FUNCTION__), $assets[TYPE_CAT][$id]);
        }

        //do_action('dbnavt', NAVT_INIT, sprintf("%s::%s assets\n", __CLASS__, __FUNCTION__), $assets);
        //do_action('dbnavt', NAVT_INIT, sprintf("%s::%s - end\n", __CLASS__, __FUNCTION__));
        do_action('dbnavt', NAVT_INIT, sprintf("%s::%s - end\n", __CLASS__, __FUNCTION__));

        return($assets);

    }// end function

    /**
     * Returns the value of the option identified by 'key'
     * Calls the Word Press function by the same name
     *
     * @param string_type $key
     * @return mixed value of key
     */
    function get_option($key) {
        wp_cache_flush();
        do_action('dbnavt', NAVT_GEN, sprintf("%s::%s Key: %s\n", __CLASS__, __FUNCTION__, $key));
        return(get_option($key));
    }

    /**
     * Adds the key/Value and description to the database
     * Calls the Word Press function by the same name
     *
     * @param string $key
     * @param mixed $value
     * @param string $description of option
     */
    function add_option($key, $value, $description=NULL) {
        do_action('dbnavt', NAVT_GEN, sprintf("%s::%s Key: %s\n", __CLASS__, __FUNCTION__, $key));
        add_option($key, $value, $description, 'no');
    }

    /**
     * Updates the value of 'key' in the database
     * Calls the Word Press function by the same name
     *
     * @param string $key
     * @param mixed $value of key
     */
    function update_option($key, $value) {
        global $navt_map, $icfg, $gcfg;

        do_action('dbnavt', NAVT_GEN, sprintf("%s::%s: Key: %s\n", __CLASS__, __FUNCTION__, $key));

        if( $key == SERIALIZED_MAP ) {
            $navt_map = $value;
        }
        if( $key == ICONFIG ) {
            do_action('dbnavt', NAVT_GEN, sprintf("%s::%s: updating item configuration\n", __CLASS__, __FUNCTION__));
            $icfg = $value;
        }
        if( $key == GCONFIG ) {
            do_action('dbnavt', NAVT_GEN, sprintf("%s::%s: updating group configuration\n", __CLASS__, __FUNCTION__));
            $gcfg = $value;
        }

        wp_cache_flush();
        update_option($key, $value);
    }

    /**
     * Deletes the option identified by 'key' from the database
     * Calls the Word Press function by the same name
     *
     * @param string $key
     */
    function delete_option($key) {
        do_action('dbnavt', NAVT_GEN, sprintf("%s::%s Key: %s\n", __CLASS__, __FUNCTION__, $key));
        delete_option($key);
    }

    /**
     * Truncates text to $n length and adds an elipse
     *
     * @param string $text
     * @param integer $n
     */
    function truncate($text, $n) {
        $name = substr($text, 0, $n-3);
        $text = (($name != $text) ? $name .= '...' : $text);

        //do_action('dbnavt', NAVT_GEN, sprintf("%s::%s truncated to: %s\n", __CLASS__, __FUNCTION__, $text));
        return $text;
    }

    /**
     * Builds a HTML select containing the available avatars
     */
    function build_avatar_list($options_only=1, $set_to_disabled=0, $id='avatars', $picked='', $in=0) {

        $html = '';
        $url = NAVT::get_url();
        $path =  NAVT_PLUGINPATH . AVATAR_IMAGES . '/';
        $files = NAVT::files_scan($path, array('png','gif','jpg','jpeg','bmp'), 1, false);
        $state = $set_to_disabled ? "disabled='disabled'":'';

        if( is_array($files) && count($files) > 0 ) {
            if(!$options_only) {
                $html = sprintf("%s<select id='%s' class='%s' %s>", _indentt($in), $id, CLS_AV_SELECT, $state);
            }
            foreach($files as $k => $filename ) {
                $pi = pathinfo($filename);
                $name = $pi['basename'];
                $file_url = $url . AVATAR_IMAGES . '/' . $name;
                $select = ($picked == $file_url) ? "selected='selected'":'';
                $html .= sprintf("%s<option value='%s' %s>%s</option>", _indentt($in+1), $file_url, $select, $name);
            }
            if( !$options_only ) {
                $html .= sprintf('%s</select>', _indentt($in));
            }
        }
        return($html);
    }

    /**
     * Scan for files to be included in th
     *
     * @param string $path  - where to begin the search
     * @param string $ext - type of file to search for, false == everything
     * @param integer $depth - depth of search
     * @param boolean $relative - true = scan is relative
     * @return array $files - array of files
     */
    function files_scan($path, $ext = false, $depth = 1, $relative = true) {
        $files = array();

        // Scan for all matching files
        NAVT::_files_scan($path, '', $ext, $depth, $relative, $files);
        return $files;
    }

    /**
     * Returns an array of filenames scanned in a directory
     *
     * @param string $base_path  - where to begin the search
     * @param string $path - directory path
     * @param string $ext - type of file to search for
     * @param integer $depth - depth of search
     * @param boolean $relative - true = scan is relative
     * @param  array $files - array of files
     */
    function _files_scan($base_path, $path, $ext, $depth, $relative, &$files) {
        if (!empty($ext)) {
            if (!is_array($ext)) {
                $ext = array($ext);
            }
            $ext_match = implode('|', $ext);
        }

        // Open the directory
        if(($dir = @dir($base_path . $path)) !== false) {
            // Get all the files
            while(($file = $dir->read()) !== false) {
                // Construct an absolute & relative file path
                $file_path = $path . $file;
                $file_full_path = $base_path . $file_path;

                // If this is a directory, and the depth of scan is greater than 1 then scan it
                if(is_dir($file_full_path) && $depth > 1 && !($file == '.' || $file == '..')) {
                    NAVT::_files_scan($base_path, $file_path . '/', $ext, $depth - 1, $relative, $files);

                    // If this is a matching file then add it to the list
                } elseif(is_file($file_full_path) && (empty($ext) || preg_match('/\.(' . $ext_match . ')$/i', $file))) {
                    $files[] = $relative ? $file_path : $file_full_path;
                }
            }

            // Close the directory
            $dir->close();
        }
    }

    /**
     * create a unique selector id
     *
     * @param array $item
     * @return string - selector id
     * @since version .96
     */
    function make_id($item, $seq='') {
        $id = 'a'.SEP.$item[TYP].SEP.$item[IDN];
        if( $seq != '' ) {
            $id .= '--'.$seq;
        }
        return($id);
    }

    /**
     * Create the html for a default asset
     *
     * @param integer $in
     * @param array $item
     * @return string
     * @since .96
     */
    function make_default_asset($in, $item, $is_alt) {

        $id    = NAVT::make_id($item);
        $alt   = (($is_alt) ? 'alt' : '');
        $asset = wp_specialchars($item['asset_ttl']);
        $nme   = wp_specialchars($item[NME]);
        $icon  = NAVT::get_icon($item); // v1.0.5
        $html  = _indentt($in) . "<option value='$id::$icon::$nme' class='asset $alt'>$asset</option>";
        return($html);
    }

    function get_icon($item) {
        $opts = intval($item[OPT], 10);
        $icon = '';
        if( $item[TYP] == TYPE_PAGE ) {
            $icon = (($opts & ISDRAFTPAGE) ? 'draftpage' : 'page');
        }

        if( $item[TYP] == TYPE_CODE )  { $icon = 'codeblock'; }
        if( $item[TYP] == TYPE_CAT )   { $icon = 'category'; }
        if( $item[TYP] == TYPE_SEP )   { $icon = 'divider'; }
        if( $item[TYP] == TYPE_AUTHOR) { $icon = 'user'; }
        if( $item[TYP] == TYPE_ELINK ) { $icon = 'elink'; }
        if( $item[TYP] == TYPE_LINK && $item[IDN] == HOMEIDN )  { $icon = 'home'; }
        if( $item[TYP] == TYPE_LINK && $item[IDN] == LOGINIDN ) { $icon = 'admin'; }
        return($icon);
    }

    /**
     * Returns a qualified group name
     *
     * @param string $n
     * @return string - cleaned string
     */
    function clean_group_name($n) {
        $n = trim($n);
        $n = attribute_escape(strip_tags($n));
        $n = sanitize_title_with_dashes($n);
        $n = stripslashes($n);
        $n = str_replace(':','-', $n); // can't allow ':' in the name v1.0.5
        return($n);
    }

    /**
     * Returns a clean item alias
     *
     * @param string $n - cleaned string
     */
    function clean_item_alias($n) {
        $n = trim($n);
        $n = attribute_escape(strip_tags($n));
        $n = stripslashes($n);
        return($n);
    }

    /**
     * Adds the NAVT uninstall and reset rows to the NAVT plugin on the plugins page
     *
     * How hard is this?
     * @param string - name of the plugin
     */
    function after_plugin_row_wpcb($plugin_name) {

        $is_navt = (( stristr($plugin_name, 'navt.php') === FALSE ) ? 0: 1);
        do_action('dbnavt', NAVT_GEN, sprintf("%s - %s, is_navt: %s\n", __FUNCTION__, $plugin_name, $is_navt));

        if( $is_navt ) {
            $uninstall_url = NAVT::get_url() . "/app/navt_utl.php?navt_action=uninstall&amp;plugin=$plugin_name&amp;_wpnonce=@once@";
            $reset_url = NAVT::get_url() . "/app/navt_utl.php?navt_action=reset";
            $t[0] = __("Sets all NAVT created database options to their default values. This will cause any previously created Navigation Groups to be removed.", 'navt_domain');
            $t[1] = __("Removes all NAVT created database options and automatically deactivates the plugin.", 'navt_domain');

            $html =
            "<div class='navtinfo' style='display:none;'>
                <div>
                    <fieldset style='border: 1px solid #ccc;padding:5px; margin-top: 10px;'>
                        <legend style='font-size: 1.1em; font-weight: bold; color:#666;'>Uninstall/Reset NAVT Plugin Options</legend>
                        <ul>
                            <li><a href='$reset_url' title='". __('Reset this plugin', 'navt_domain')."'>" .__('Reset', 'navt_domain') . "</a><p>" . $t[0] ."</p></li>
                            <li><a class='navt_uninstall' href='$uninstall_url' title='" . __('Uninstall this plugin', 'navt_domain') ."'>". __('Uninstall', 'navt_domain') . "</a><p>" . $t[1] ."</p></li>
                        </ul>
                    </fieldset>
                </div>
            </div>";
            echo $html;
        }
    }

    /**
     * Returns a HOME page navigation item
     */
    function make_home_link() {
        return(array(TYP => TYPE_LINK, IDN => HOMEIDN, TTL => "Home",
        NME => "Home", OPT => 0, EXT => '', LVL => 0, EX2 => '',
        OP2 => 0, EX3 => '', EX4 => '', EX5 => ''));
    }

    /**
     * Backs up the current configuration and saves an XML file to the local drive
     */
    function do_backup() {

        // get the configuration data
        $groups = NAVT::get_option(ICONFIG);
        $group_options = NAVT::get_option(GCONFIG);
        $charset = get_option('blog_charset', true);
        $in = 1;
        $in0 = _indentt($in++);
        $in1 = _indentt($in++);
        $in2 = _indentt($in++);
        $in3 = _indentt($in++);
        $in4 = _indentt($in++);

        if( is_array($groups) && count($groups) > 0 ) {

            $filename = 'navt_plugin.' . date('Y-m-d') . '.xml';
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=$filename");
            header("Content-type: text/xml; charset='$charset'");
            printf("<?xml version='1.0' encoding='%s'?>\n", $charset);

            $html =
            "<!-- \n".
            $in1."generator='navt " . NAVT_SCRIPTVERS ."\n".
            $in1."created='" . date('Y-m-d H:i') . "'\n".
            $in1."This file contains the backup information for the Navigation Tool For Word Press Plugin (NAVT)\n".
            $in1."This backup can be restored using the plugin restore facility\n".
            $in1."Copyright (c) 2006-2008 Greg Bellucci, The MIT License.\n".
            "-->\n".
            "\n<navt major_version='".NAVT_MAJVER."' minor_version='".NAVT_MINVER."'>\n";

            foreach( $groups as $group => $group_members) {

                $page_ids = $post_ids = array();
                $posts = $group_options[$group]['display']['posts']['ids'];
                $pages = $group_options[$group]['display']['pages']['ids'];
                $cats = $group_options[$group]['display']['cats']['ids'];
                foreach($posts as $key => $value ) { $post_ids[] = $key; }
                foreach($pages as $key => $value ) { $page_ids[] = $key; }
                foreach($cats as $key => $value )  { $cat_ids[] = $key; }
                $postids = ((count($post_ids) > 0) ? implode($post_ids, ',') : '');
                $pageids = ((count($page_ids) > 0) ? implode($page_ids, ',') : '');
                $catids  = ((count($cat_ids)  > 0) ? implode($cat_ids, ',') : '');

                $html .=
                $in1."<group>\n" .
                $in2.NAVT::create_xml_entry('name', $group, true)."\n" .
                $in2.NAVT::create_xml_entry('options', $group_options[$group]['options'], false)."\n".
                $in2.NAVT::create_xml_entry('selectsize', $group_options[$group]['select_size'], false)."\n".

                $in2."<css>\n" .
                $in3.NAVT::create_xml_entry('ulid', $group_options[$group]['css']['ulid'], true). "\n" .
                $in3.NAVT::create_xml_entry('ulclass', $group_options[$group]['css']['ul'], true). "\n".
                $in3.NAVT::create_xml_entry('liclass', $group_options[$group]['css']['li'], true)."\n".
                $in3.NAVT::create_xml_entry('licurrent', $group_options[$group]['css']['licurrent'], true)."\n".
                $in3.NAVT::create_xml_entry('liparent', $group_options[$group]['css']['liparent'], true)."\n".
                $in3.NAVT::create_xml_entry('li_parent_active', $group_options[$group]['css']['liparent_active'], true)."\n".
                $in2."</css>\n" .

                $in2."<selector>\n" .
                $in3.NAVT::create_xml_entry('xpath', $group_options[$group]['selector']['xpath'], true)."\n".
                $in3.NAVT::create_xml_entry('before', $group_options[$group]['selector']['before'], true)."\n".
                $in3.NAVT::create_xml_entry('after', $group_options[$group]['selector']['after'], true)."\n".
                $in3.NAVT::create_xml_entry('seloption', $group_options[$group]['selector']['option'], false)."\n".
                $in2."</selector>\n" .

                $in2."<display>\n" .
                $in3.NAVT::create_xml_entry('show_on_options', $group_options[$group]['display']['show_on'], false) ."\n".
                $in3."<posts>\n" .
                $in4.NAVT::create_xml_entry('on_selected', $group_options[$group]['display']['posts']['on_selected'], false)."\n".
                $in4.NAVT::create_xml_entry('ids', $postids, false)."\n" .
                $in3."</posts>\n" .
                $in3."<pages>\n" .
                $in4.NAVT::create_xml_entry('on_selected', $group_options[$group]['display']['pages']['on_selected'], false)."\n".
                $in4.NAVT::create_xml_entry('ids', $pageids, false)."\n" .
                $in3."</pages>\n" .
                $in3."<cats>\n" .
                $in4.NAVT::create_xml_entry('on_selected', $group_options[$group]['display']['cats']['on_selected'], false)."\n".
                $in4.NAVT::create_xml_entry('ids', $catids, false)."\n" .
                $in3."</cats>\n" .
                $in2."</display>\n";

                foreach($group_members as $itm ) {

                    $opt = intval($itm[OPT], 10);
                    $lvl = intval($itm[LVL], 10);
                    $itm[OPT] = (($opt > 0) ? $opt: '0');
                    $itm[LVL] = (($lvl > 0) ? $lvl: '0');
                    $str = $itm[TYP].$itm[IDN].$itm[TTL];

                    if( !isBlank($itm[TYP]) && !isBlank($itm[IDN]) &&
                    !isBlank($itm[TTL]) && !isBlank($itm[NME]) ) {
                        $html .= $in2."<item>\n" .
                        $in3.NAVT::create_xml_entry('grp', $itm[GRP], true)."\n".
                        $in3.NAVT::create_xml_entry('typ', $itm[TYP], false)."\n".
                        $in3.NAVT::create_xml_entry('idn', $itm[IDN], false)."\n".
                        $in3.NAVT::create_xml_entry('ttl', $itm[TTL], true)."\n".
                        $in3.NAVT::create_xml_entry('nme', $itm[NME], true)."\n".
                        $in3.NAVT::create_xml_entry('opt', $itm[OPT], false)."\n".
                        $in3.NAVT::create_xml_entry('lvl', $itm[LVL], false)."\n".
                        $in3.NAVT::create_xml_entry('ext', $itm[EXT], true)."\n".
                        $in3.NAVT::create_xml_entry('ex2', $itm[EX2], true)."\n".
                        $in3.NAVT::create_xml_entry('op2', $itm[OP2], false)."\n".
                        $in3.NAVT::create_xml_entry('ex3', $itm[EX3], true)."\n".
                        $in3.NAVT::create_xml_entry('ex4', $itm[EX4], true)."\n".
                        $in3.NAVT::create_xml_entry('ex5', $itm[EX5], true)."\n".
                        $in2."</item>\n";
                    }
                }
                $html .= $in1."</group>\n";
            }
            $html .= "</navt>\n";
            echo $html;
        }
        else {
            // nothing to back up
            wp_redirect( wp_get_referer() );
        }
    }

    /**
     * Create a n XML entry for the backup
     *
     * @param string $token
     * @param string $str
     * @param boolean $encap
     * @return string - XML tag
     */
    function create_xml_entry($token, $str, $encap) {

        $ret = '<' . $token;
        if( isBlank($str) ) {
            $ret .= '/>';
        }
        else {
            $ret .= '>' . (($encap == true) ? "<![CDATA[$str]]>" : $str);
            $ret .= '</' . $token . '>';
        }
        return($ret);
    }

    function restore_head() {?>
<link type="text/css" rel="stylesheet" href="<?php navt_output_url(); ?>/css/restore.css" media="screen"/>
    <?php }
}// end class NAVT

function navt_get_version() {
    global $wp_version;
    $v = split('.', $wp_version);
    $ar = array('major' => $v[0], 'minor' => $v[1], 'update' => $v[2]);
    return($ar);
}

/**
 * Test for an empty string
 *
 * @param string $string
 * @return boolean 1 = blank, 0 = not blank
 */
function isBlank($str) {
    $ret = 1; // assume it is blank
    if (!empty($str) && ($str != '')) {
        for ($i = 0; $i < strlen($str); $i++) {
            $c = substr($str, $i, 1);
            if ($c != " " ) {
                $ret = 0;
            }
        }
    }
    return($ret);
}

/**
 * Returns a string of spaces for HTML indentation
 *
 * @param integer $howmany_tabs- the number of indentation tabs to generate
 * @return string the indentation string
 */
function _indentt($howmany_tabs, $tabstring='    ') {
    $o = '';
    for($x = 0; $x < $howmany_tabs; $x++ )	$o .= $tabstring;
    return($o);
}

/**
 * Returns a comma delineated string containing the names
 * of all the menu groups.
 */
function navt_get_all_groups() {

    $gp = array();
    $g = NAVT::get_option(GCONFIG); // group configurations
    do_action('dbnavt', NAVT_INIT, sprintf("%s: group array", __FUNCTION__), $g);

    if( $g != false ) {
        foreach( $g as $n => $d ) {
            $gp[] = $n;
        }
    }

    return($gp);
}

/**
 * Avatar interface
 *
 * @param string $email_address
 */
function navt_get_avatar($email_address='') {

    if( function_exists('get_avatar') ) {
        $html = get_avatar($email_address);
    }
    else {
        $html = "<div class='avatar'><img src='".NAVT::get_url()."/images/default_avatar.jpg' alt='' /></div>\n";
    }

    return($html);
}

function navt_output_url() {
    echo(NAVT::get_url());
}

/**
 * Returns the language in lowercase
 * This is used as a CSS class
 */
function navt_localize() {
    $lang = 'en-us';
    if(defined('WPLANG')) {
        $lang = str_replace('_', '-', WPLANG);
        $lang = strtolower($lang);
    }
    return($lang);
}

//
// NAVT list functions
//
/**
 * creates the contents of the page sidebar
 *
 * @param integer $in
 * @return html string
 * @since .96
 */
function get_navt_topbar($in) {

    global $ie;
    $in0 = _indentt($in); $in1 = _indentt($in+1); $in2 = _indentt($in+2); $in3 = _indentt($in+3);

    $html =
    $in0 . "<!--[$ie]> <div class='IEtoolbar'> <![endif]-->\n".
    $in0 . "<h2 class='navt-header'>".__('WordPress Navigation Management', 'navt_domain')."</h2>\n" .
    $in0 . "<p>&bull; ". __('Navigation Tool for WordPress v', 'navt_domain') . NAVT_SCRIPTVERS . " &bull;</p>\n" .
    $in0 . "<div id='navt-topbar'>\n" .

    // create new group toolbar
    $in1 . "<div class='toolbar r4 newgroup left'>\n" .
    $in2 . "<h3>".__('Navigation Group', 'navt_domain')."</h3>\n" .

    // help
    $in2 . "<div class='toolhelp'>\n" .
    $in3 . "<a href='#' id='help-grp' title='".__('group help', 'navt_domain').
    "'><img src='@SRC@' alt=''/></a>\n" .
    $in2 . "</div>\n" .

    // controls
    $in2 . "<p id='add-controls'>".__('group name', 'navt_domain')."<br />\n" .
    $in2 . "<input type='text' id='new-group-name' value=''/></p><div id='add-spinner'></div>\n" .
    $in2 . "<a href='#' class='bttn' id='grp-create' title='".
    __('create new group', 'navt_domain')."'>".__('create', 'navt_domain')."</a>\n" .
    $in2 . "<div class='errormsg' id='create-msg' style='display:none;'></div>\n" .
    $in1 . "</div>\n" .

    /**
     * Temporarily removed @since 1.0.27 (12/19/08)
     * @todo - fix this!

    // backup-restore toolbar
    $in1 . "<div class='toolbar r4 backup-restore left'>\n" .
    $in2 . "<h3>".__('Backup-Restore', 'navt_domain')."</h3>\n" .

    // help
    $in2 . "<div class='toolhelp'>\n" .
    $in3 . "<a href='#' id='help-backup' title='".__('backup/restore help', 'navt_domain').
    "'><img src='@SRC@' alt=''/></a>\n" .
    $in2 . "</div>\n" .

    // controls
    $in2 . "<p id='br-controls'>".__('backup/restore navigation groups', 'navt_domain').".<br />\n" .
    $in2 . "<a href='#' class='bttn' id='do-restore' title='".__("restore", 'navt_domain')."'>".__('restore', 'navt_domain')."</a>\n" .
    $in2 . "<a href='#' class='bttn' id='do-backup' title='".__('backup', 'navt_domain')."'>".__('backup', 'navt_domain')."</a></p>\n" .
    $in1 . "</div>\n" .

    */

    // updates
    $in1 . "<div class='toolbar r4 updates left'>\n" .
    $in2 . "<h3>".__('Change Log', 'navt_domain')."</h3>\n" .

    // controls
    $in2 . "<select id='list-updates' class='selects' size='4'><option value=''>&nbsp;</option></select>\n" .
    $in1 . "</div>\n" .

    $in0 . "</div>\n" .
    $in0 . "<!--[$ie]></div> <![endif]-->\n";

    return($html);
}

/**
 * Creates the group options helper
 *
 * @param integer $in
 */
function navt_group_options_helper($in) {
    global $ie;
    $in0 = _indentt($in);
    $html = "\n" . $in0 . "

<div id='window-mask'></div>
<div id='option-outer-wrapper'>
    <div id='w-h'>
        <div id='w-tc'></div>
        <div id='option-wrapper'>
            <div id='option-helper'>&nbsp;</div>
        </div>
    </div>
</div>

<div id='modalOverlay'></div>
<div id='dialog'>
    <div id='wintitle'>&nbsp;</div>
    <div id='target'>&nbsp;</div>
</div>";

    return($html);
}

/**
 * creates the contents of the page footer
 *
 * @param integer $in
 * @return html string
 * @since .96
 */
function get_navt_footer($in=0) {

    global $ie;

    $html =
    "\n\n" . _indentt($in) .
    "<!--[$ie]> <div class='IEfooter'> <![endif]-->\n" . _indentt($in) .
    "<br /><div id='navt-footer'>\n" ._indentt($in+1) .
    "<p>".__('copyright', 'navt_domain') . " &copy; 2007-2009 <a href='http://gbellucci.us'>g. a. bellucci, et ux</a> &bull;
    <a href='http://gbellucci.us/forums/'>".__('navt forum', 'navt_domain')."</a> &bull;
    <a href='http://atalayastudio.com'>".__('atalaya studio', 'navt_domain')."</a> &bull;
    <a href='http://atalayastudio.com'>".__('donations', 'navt_domain')."</a> &bull;
    <a rel='nofollow' href='http://en.wikipedia.org/wiki/MIT_License'>".__('the mit license', 'navt_domain')."</a><br /></p>
    <div class='badge'><img src='@SRC@' alt='NAVT Powered' /></div>\n" .
    _indentt($in) . "</div>\n".
    _indentt($in) . "<!--[$ie]></div> <![endif]-->\n\n";

    return($html);
}

//
// backup functions
//

/**
 * Get backup version from xml file
 */
function get_navt_backup_version() {

    global $backup_version;
    $retcode = 0;

    if( isset($_FILES['restore_file'])) {
        $file = $_FILES['restore_file']['tmp_name'];
        $user_file = $_FILES['restore_file']['name'];
        $mime_type = $_FILES['restore_file']['type'];

        if( $mime_type != 'text/xml' ) {
            $retcode = 1;// wrong type of file?
        }
        elseif(filesize($file) == 0 ) {
            $retcode = 2;// empty?
        }

        if( $retcode == 0 ) {
            $parse_error = $vals = $index = 0;
            $file_contents = file_get_contents($file);
            $p = xml_parser_create('UTF-8');
            $result = xml_parse_into_struct($p, $file_contents, $vals, $index);

            if( !$result ) {
                $parse_error = 1;
                do_action('dbnavt', NAVT_RESTORE, sprintf("XML restore parse error: %s\n", xml_error_string(xml_get_error_code($p))));
                $retcode = 3; // corrupt?
            }

            xml_parser_free($p);

            if(!$parse_error) {
                foreach ($vals as $k => $v) {
                    if( $v['tag'] == 'NAVT' && $v['type'] == 'open' ) {
                        $ver = $v['attributes']['MAJOR_VERSION'] . $v['attributes']['MINOR_VERSION'];
                        $backup_version = intval($ver, 10);
                    }
                    break;
                }
            }
        }
    }
    return($retcode);
}

/**
 * Read/Parse a restore file
 * versions < 96
 *
 */
function navt_restore_95() {

    global $r_gcfg;
    global $r_icfg;
    global $backup_version;

    $retcode = 0;

    if( isset($_FILES['restore_file'])) {
        $file = $_FILES['restore_file']['tmp_name'];
        $user_file = $_FILES['restore_file']['name'];
        $mime_type = $_FILES['restore_file']['type'];

        if( $mime_type != 'text/xml' ) {
            $retcode = 1;// wrong type of file?
        }
        elseif(filesize($file) == 0 ) {
            $retcode = 2;// empty?
        }
    }

    if( $retcode == 0 ) {
        $parser_error = $entries = $vals = $index = 0;
        $file_contents = file_get_contents($file);
        $p = xml_parser_create('UTF-8');
        $result = xml_parse_into_struct($p, $file_contents, $vals, $index);

        if( !$result ) {
            $parser_error = 1;
            do_action('dbnavt', NAVT_RESTORE, sprintf("XML restore parse error: %s\n",
            xml_error_string(xml_get_error_code($p))));
            $retcode = 3; // corrupt?
        }

        xml_parser_free($p);

        if( !$parser_error ) {
            do_action('dbnavt', NAVT_RESTORE, sprintf("* Parsing restore file *\n"));

            $seq = 1000;
            $cur_group = '';

            foreach ($vals as $k => $v) {

                if( $v['tag'] == 'NAVT' ) {

                    if( $v['type'] == 'open' ) {
                        $in_map = 1;
                    }
                    elseif( $v['type'] == 'close' ) {
                        $in_map = 0;
                    }
                    continue;
                }

                if( $in_map ) {

                    if( $v['tag'] == 'ITEM' ) {
                        if( $v['type'] == 'open' ) {
                            $in_item = 1;
                            $item = array();
                        }
                        elseif( $v['type'] == 'close' ) {
                            $in_item = 0;
                            $cur_group = $item['GRP'];

                            if( $cur_group != 'UNASSIGNED' && $cur_group != 'unassigned' ) {
                                $id = NAVT::make_id($item, $seq++);
                                //$item[VER] = md5($item[TYP].$item[IDN].$item[TTL]);
                                $item['EX3'] = $item['EX4'] = $item['EX5'] = '';

                                $r_icfg[$cur_group][ $id ] = array(
                                GRP => $item['GRP'], TYP => $item['TYP'], IDN => $item['IDN'], TTL => $item['TTL'],
                                NME => $item['NME'], OPT => $item['OPT'], LVL => $item['LVL'], EXT => $item['EXT'],
                                EX2 => $item['EX2'], EX4 => $item['EX3'], EX4 => $item['EX4'], EX5 => $item['EX5']);

                                if( empty($r_gcfg[$cur_group]) ) {
                                    $r_gcfg[$cur_group] = NAVT::mk_group_config();
                                }
                                do_action('dbnavt', NAVT_RESTORE, sprintf("\titem complete:"), $item);
                            }
                        }
                        continue;
                    }

                    if( $in_item ) {

                        if(( $v['tag'] == 'GRP' || $v['tag'] == 'TYP' || $v['tag'] == 'IDN' ||
                        $v['tag'] == 'OPT' || $v['tag'] == 'LVL' || $v['tag'] == 'TTL' || $v['tag'] == 'NME' ||
                        $v['tag'] == 'EXT' || $v['tag'] == 'EX2') && $v['type'] == 'complete') {
                            $t = $v['value'];
                            $item[$v['tag']] = $v['value'];
                            do_action('dbnavt', NAVT_RESTORE, sprintf("\t\titem param: %s, value = %s\n", $v['tag'], $v['value']));
                        }
                    }// end in_item
                }// in_map
            }// end for

            do_action('dbnavt', NAVT_RESTORE, sprintf("* Parsing restore end *\n"));
            do_action('dbnavt', NAVT_RESTORE, sprintf("r_gcfg array\n"), $r_gcfg);
            do_action('dbnavt', NAVT_RESTORE, sprintf("r_icfg array\n"), $r_icfg);
        }
    }
    return($retcode);
}

/**
 * Read/Parse a restore file
 * versions 96+
 */
function navt_restore_96() {

    global $r_gcfg;
    global $r_icfg;
    global $backup_version;

    $retcode = 0;

    if( isset($_FILES['restore_file'])) {
        $file = $_FILES['restore_file']['tmp_name'];
        $user_file = $_FILES['restore_file']['name'];
        $mime_type = $_FILES['restore_file']['type'];

        if( $mime_type != 'text/xml' ) {
            $retcode = 1;// wrong type of file?
        }
        elseif(filesize($file) == 0 ) {
            $retcode = 2;// empty?
        }
    }

    if( $retcode == 0 ) {
        $parser_error = $entries = $vals = $index = 0;
        $file_contents = file_get_contents($file);
        $p = xml_parser_create('UTF-8');
        $result = xml_parse_into_struct($p, $file_contents, $vals, $index);

        if( !$result ) {
            $parser_error = 1;
            do_action('dbnavt', NAVT_RESTORE, sprintf("XML restore parse error: %s\n",
            xml_error_string(xml_get_error_code($p))));
            $retcode = 3; // corrupt?
        }

        xml_parser_free($p);

        if( !$parser_error ) {
            do_action('dbnavt', NAVT_RESTORE, sprintf("* Parsing restore file *\n"));

            $NAVT_state = $GROUP_state = '';
            $GROUP_posts = $GROUP_pages = $GROUP_open_ITEM = 0;
            $seq = 1000;
            $item = array();
            $cur_group = '';

            foreach ($vals as $k => $v) {
                do_action('dbnavt', NAVT_RESTORE, sprintf("%s -> \n", $k), $v);

                if( $v['tag'] == 'NAVT' ) {
                    if( $v['type']  == 'open' ) {
                        $NAVT_state = 'open';
                        continue;
                    }
                    elseif( $v['type'] == 'close' || $v['type'] == 'complete' ) {
                        $NAVT_state = 'close';
                    }
                }

                if( $v['tag'] == 'GROUP' ) {
                    if( $v['type']  == 'open' ) {
                        $GROUP_state = 'open';
                    }
                    elseif( $v['type'] == 'close' || $v['type'] == 'complete' ) {
                        $GROUP_state = 'close';
                        $cur_group = '';
                    }
                }

                if( $NAVT_state == 'open' ) {

                    if( $GROUP_state == 'open' ) {

                        if( $v['tag'] == 'NAME' && $v['type'] == 'complete' ) {
                            $cur_group = $v['value'];
                            $r_gcfg[$cur_group] = NAVT::mk_group_config();
                        }
                        elseif($v['tag'] == 'OPTIONS' && $v['type'] == 'complete' ) {
                            $r_gcfg[$cur_group]['options'] = $v['value'];
                        }
                        elseif($v['tag'] == 'SELECTSIZE' && $v['type'] == 'complete' ) {
                            $r_gcfg[$cur_group]['select_size'] = $v['value'];
                        }
                        elseif($v['tag'] == 'ULID' && $v['type'] == 'complete' ) {
                            $r_gcfg[$cur_group]['css']['ulid'] = $v['value'];
                        }
                        elseif($v['tag'] == 'ULCLASS' && $v['type'] == 'complete' ) {
                            $r_gcfg[$cur_group]['css']['ul'] = $v['value'];
                        }
                        elseif($v['tag'] == 'LICLASS' && $v['type'] == 'complete' ) {
                            $r_gcfg[$cur_group]['css']['li'] = $v['value'];
                        }
                        elseif($v['tag'] == 'LIPARENT' && $v['type'] == 'complete' ) {
                            $r_gcfg[$cur_group]['css']['liparent'] = $v['value'];
                        }
                        elseif($v['tag'] == 'LI_PARENT_ACTIVE' && $v['type'] == 'complete' ) {
                            $r_gcfg[$cur_group]['css']['liparent_active'] = $v['value'];
                        }
                        elseif($v['tag'] == 'XPATH' && $v['type'] == 'complete' ) {
                            $r_gcfg[$cur_group]['selector']['xpath'] = $v['value'];
                        }
                        elseif($v['tag'] == 'BEFORE' && $v['type'] == 'complete' ) {
                            $r_gcfg[$cur_group]['selector']['before'] = $v['value'];
                        }
                        elseif($v['tag'] == 'AFTER' && $v['type'] == 'complete' ) {
                            $r_gcfg[$cur_group]['selector']['after'] = $v['value'];
                        }
                        elseif($v['tag'] == 'SELOPTION' && $v['type'] == 'complete' ) {
                            $r_gcfg[$cur_group]['selector']['option'] = $v['value'];
                        }
                        elseif($v['tag'] == 'SHOW_ON_OPTIONS' && $v['type'] == 'complete' ) {
                            $r_gcfg[$cur_group]['display']['show_on'] = $v['value'];
                        }
                        elseif($v['tag'] == 'POSTS' && $v['type'] == 'open' ) {
                            $GROUP_posts = 1;
                        }
                        elseif($v['tag'] == 'POSTS' && $v['type'] == 'close' ) {
                            $GROUP_posts = 0;
                        }
                        elseif($v['tag'] == 'ON_SELECTED' && $v['type'] == 'complete' && $GROUP_posts ) {
                            $r_gcfg[$cur_group]['posts']['on_selected'] = $v['value'];
                        }
                        elseif($v['tag'] == 'IDS' && $v['type'] == 'complete' && $GROUP_posts ) {
                            $r_gcfg[$cur_group]['posts']['ids'] = explode(',', $v['value']);
                        }
                        elseif($v['tag'] == 'PAGES' && $v['type'] == 'open' ) {
                            $GROUP_pages = 1;
                        }
                        elseif($v['tag'] == 'PAGES' && $v['type'] == 'close' ) {
                            $GROUP_pages = 0;
                        }
                        elseif($v['tag'] == 'ON_SELECTED' && $v['type'] == 'complete' && $GROUP_pages ) {
                            $r_gcfg[$cur_group]['pages']['on_selected'] = $v['value'];
                        }
                        elseif($v['tag'] == 'IDS' && $v['type'] == 'complete' && $GROUP_pages ) {
                            $r_gcfg[$cur_group]['pages']['ids'] = explode(',', $v['value']);
                        }
                        elseif($v['tag'] == 'CATS' && $v['type'] == 'open' ) {
                            $GROUP_cats = 1;
                        }
                        elseif($v['tag'] == 'CATS' && $v['type'] == 'close' ) {
                            $GROUP_cats = 0;
                        }
                        elseif($v['tag'] == 'ON_SELECTED' && $v['type'] == 'complete' && $GROUP_cats ) {
                            $r_gcfg[$cur_group]['cats']['on_selected'] = $v['value'];
                        }
                        elseif($v['tag'] == 'IDS' && $v['type'] == 'complete' && $GROUP_cats ) {
                            $r_gcfg[$cur_group]['cats']['ids'] = explode(',', $v['value']);
                        }
                        elseif($v['tag'] == 'ITEM' && $v['type'] == 'open' ) {
                            $GROUP_open_ITEM = 1;
                        }
                        elseif($v['tag'] == 'ITEM' && $v['type'] == 'close' ) {
                            $GROUP_open_ITEM = 0;
                            $id = NAVT::make_id($item, $seq++);

                            $r_icfg[$cur_group][ $id ] = array(
                            GRP => $item['GRP'], TYP => $item['TYP'], IDN => $item['IDN'], TTL => $item['TTL'],
                            NME => $item['NME'], OPT => $item['OPT'], LVL => $item['LVL'], EXT => $item['EXT'],
                            EX2 => $item['EX2'], EX3 => $item['EX3'], EX4 => $item['EX4'], EX5 => $item['EX5']);
                            $item = array();
                        }
                        elseif( ($v['tag'] == 'GRP' || $v['tag'] == 'TYP' || $v['tag'] == 'IDN' ||
                        $v['tag'] == 'TTL' || $v['tag'] == 'NME' || $v['tag'] == 'OPT' || $v['tag'] == 'LVL' ||
                        $v['tag'] == 'EXT' || $v['tag'] == 'EX2' || $v['tag'] == 'EX3' ||
                        $v['tag'] == 'EX4' || $v['tag'] == 'EX5') && $v['type'] == 'complete' && $GROUP_open_ITEM ) {
                            $item[$v['tag']] = $v['value'];
                        }
                    }
                }
            } // end for

            if( $NAVT_state == 'open' || $GROUP_state == 'open' ) {
                do_action('dbnavt', NAVT_RESTORE, sprintf("navt state: %s, group state: %s\n", $NAVT_state, $GROUP_state));
                // items are still open
                $retcode = 3;
            }

            do_action('dbnavt', NAVT_RESTORE, sprintf("* Parsing restore end *\n"));
            do_action('dbnavt', NAVT_RESTORE, sprintf("r_gcfg array\n"), $r_gcfg);
            do_action('dbnavt', NAVT_RESTORE, sprintf("r_icfg array\n"), $r_icfg);

        }// end if */
    }

    return($retcode);
}

/**
 * Restore an item
 *
 * @param unknown_type $item
 * @param unknown_type $restore_how
 * @param unknown_type $match_title
 * @param unknown_type $match_alias
 * @param unknown_type $use_backup_alias
 * @param unknown_type $publish_pages
 * @return unknown
 */
function navt_restore_item($item, $restore_how, $match_title, $use_backup_alias, $publish_pages, $icfg, $assets, $discard_dups) {

    //do_action('dbnavt', NAVT_RESTORE, sprintf("* Checking item from restore array\n"), $item);
    $strick_match = $matched_item = 0;
    $idn = $title = '';
    $new_item = null;

    if( ($item[IDN] != HOMEIDN) && ($item[IDN] != ELINKIDN) && ($item[IDN] != LOGINIDN) && ($item[IDN] != SEPIDN) ) {

        // try finding the item by type/title
        $ar = $assets[$item[TYP]];
        if( is_array($ar) ) {
            foreach( $ar as $idx => $data ) {
                //do_action('dbnavt', NAVT_RESTORE, sprintf("checking against\n"), $data);
                if( $item[TTL] == $data[TTL] ) {
                    $strick_match = $titles_match = $matched_item = 1;
                    $asset = $data;
                    break;
                }
            }
        }
    }
    else {
        // This must be a builtin asset
        $strick_match = $match_item = 1;
    }

    //do_action('dbnavt', NAVT_RESTORE, sprintf("item grp: %s, typ: %s, idn: %s, title: %s\n",
    //$item[GRP], $item[TYP], $item[IDN], $item[TTL]));
    //do_action('dbnavt', NAVT_RESTORE, sprintf("matched item = %s\n", $matched_item), $asset);

    if($restore_how == MERGE_DISCARD_UNMATCHED) {

        /* Merge item only if this asset exists in the asset list */
        if( $matched_item ) {
            if( !$discard_dups || ($discard_dups && !navt_is_duplicate($item, $icfg[$item[GRP]])) ) {
                $new_item = navt_mk_item($asset, $item[GRP], $item[NME], $use_backup_alias);
            }
        }
    }
    elseif( $restore_how == MERGE_CREATE_UNMATCHED ) {

        /* Merge item - create a new asset if the asset does not already exist */
        if( !$matched_item ) {

            if( !isBlank($item[TYP]) && !isBlank($item[TTL]) ) {
                // create this page
                if( $item[TYP] == TYPE_PAGE ) {
                    $item[IDN] = wp_insert_post( array(
                    'post_type' => 'page',
                    'post_title' => $item[TTL],
                    'post_name' => $item[TTL],
                    'post_content' => 'NAVT Restored page; temporary page contents',
                    'post_date' => current_time('mysql'),
                    'post_status' => (($publish_pages ) ? 'publish': 'draft')
                    ));

                    // get the permalink
                    $pl = get_permalink($item[IDN]);
                    $post = get_post($item[IDN]);
                    $post->guid = $pl;

                    // update the post with the permalink setting
                    wp_insert_post($post);
                    do_action('dbnavt', NAVT_RESTORE, sprintf("created page: %s: title=%s guid: %s\n", $item[IDN], $item[TTL], $pl));
                }
                // create this category
                elseif( $item[TYP] == TYPE_CAT ) {
                    $item[IDN] = wp_create_category($item[TTL]);
                    do_action('dbnavt', NAVT_RESTORE, sprintf("created category: %s: title=%s\n", $item[IDN], $item[TTL]));
                }
                // create this user
                elseif( $item[TTL] != 'admin' ) {
                    $item[IDN] = wp_create_user($item[TTL], 'navt_user', $email = '');
                    do_action('dbnavt', NAVT_RESTORE, sprintf("created user: %s: title=%s\n", $item[IDN], $item[TTL]));
                }
            }
        }

        if( !isBlank($item[TYP]) && !isBlank($item[TTL]) ) {
            /* make this item */
            $asset = $item;
            $asset[NME] = $item[NME];
            if( !$discard_dups || ($discard_dups && !navt_is_duplicate($item, $icfg[$item[GRP]])) ) {
                $new_item = navt_mk_item($asset, $item[GRP], $item[NME], $use_backup_alias);
            }
        }
    }

    return($new_item);
}


/**
 * Create an item from a backup
 *
 * @param array $asset
 * @param string $group
 * @param string $alias
 * @param boolean $use_alias
 * @return array
 */
function navt_mk_item($asset, $group, $alias, $use_alias) {
    $item = $asset;
    $item[GRP] = $group;
    $item[NME] = ( ($use_alias) ? $alias: $item[NME] );
    return($item);
}

/**
 * Determines if an item already appears in a group
 *
 * @param array $item
 * @param array $group
 * @return boolean
 */
function navt_is_duplicate($item, $group) {

    $is_dup = 0;

    if( is_array( $group ) ) {
        foreach( $group as $id => $member ) {
            if( $member[TYP] == $item[TYP] && $member[TTL] == $item[TTL] ) {
                $is_dup = 1;
                break;
            }
        }
    }
    return($is_dup);
}

/**
 * Update the progress bar
 *
 * @param integer $iter
 * @param integer $inc
 * @param integer $pl
 * @param string $plugin_url
 */
function navt_update_progress(&$iter, $inc, &$pl) {

    $pn = round(++$iter * $inc);

    if($pn != $pl) {
        print '<span class="pbox" style="z-index: ' . $pn . '; top: 265px;">' . $pn . '% </span>';
        $diff = $pn - $pl;
        for($j = 1; $j <= $diff; $j++) {
            print '<img src="' . NAVT::get_url() . '/images/bar-single.gif" width="5" height="15" alt=""/>';
        }
        $pl = $pn;
    }
}

// Automatically install the navt_module if using K2SBM
add_action('k2_init', 'install_navt_sbm_module');
function install_navt_sbm_module() {
    if(function_exists('register_sidebar_module')) {
        require('navt_sbm_module.php');
    }
}

/**
 * change debug function to an action
 * @since 1.27
 */
add_action('dbnavt', 'navt_write_debug', 4, 3);

add_action( 'after_plugin_row', array('NAVT', 'after_plugin_row_wpcb'), 10, 1);
add_action('init', array('NAVT', 'init'));
?>