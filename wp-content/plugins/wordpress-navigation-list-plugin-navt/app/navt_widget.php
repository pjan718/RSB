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
 * @subpackage navt widget
 * @author Greg A. Bellucci <greg[AT]gbellucci[DOT]us
 * @copyright Copyright &copy; 2006-2008 Greg A. Bellucci
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * -----------------------------------------------------------------------------
 * $Id: navt_widget.php 81144 2008-12-19 15:39:14Z gbellucci $:
 * $Date: 2008-12-19 15:39:14 +0000 (Fri, 19 Dec 2008) $:
 * $Revision: 81144 $:
 * -----------------------------------------------------------------------------
 */

if( function_exists('navt_loadtext_domain') ) {
    navt_loadtext_domain();
}

define('NAVT_WIDGET_OPTIONS', 'widget_navt');
define('NAVT_GRPIDX', __('group'), 'navt_domain');
define('NAVT_TTLIDX', __('title'), 'navt_domain');

/**
 * NAVT Widget registration
 *
 */
function widget_navt_register() {

    if( !$options = get_option(NAVT_WIDGET_OPTIONS) ) {
        $options = array();
        add_option(NAVT_WIDGET_OPTIONS, $options, 'NAVT Widget settings');
    }

    do_action('dbnavt', NAVT_GEN, sprintf("%s - widget options\n", __FUNCTION__), $options);

    if ( function_exists('wp_register_sidebar_widget') && function_exists('wp_register_widget_control') ) {
        $widget_ops = array('classname' => 'widget_navt', 'description' => __('Navigation Widget (NAVT)', 'navt_domain'));
        $control_ops = array('width' => 310, 'height' => 165, 'id_base' => 'navt');
        $registered = 0;

        if( count($options) > 0 ) {
            foreach ( array_keys($options) as $o ) {
                $name = __('NAVT Widget', 'navt_domain');
                if(!isset($options[$o][NAVT_GRPIDX]) || isBlank($options[$o][NAVT_GRPIDX])) {
                    continue;
                }
                $id = "navt-$o";
                do_action('dbnavt', NAVT_GEN, sprintf("%s - registering sidebar widget (id: %s, name: %s, o: %s)\n", __FUNCTION__, $id, $name, $o));
                wp_register_sidebar_widget($id, $name, 'navt_widget', $widget_ops, array( 'number' => $o ));
                wp_register_widget_control($id, $name, 'navt_widget_control', $control_ops, array( 'number' => $o));
                $registered = 1;
            }
            $options[$o][NAVT_TTLIDX] = $options[$o][NAVT_GRPIDX] = '';
        }

        if( !$registered ) {
            wp_register_sidebar_widget( 'navt-1', $name, 'navt_widget', $widget_ops, array( 'number' => -1 ) );
            wp_register_widget_control( 'navt-1', $name, 'navt_widget_control', $control_ops, array( 'number' => -1 ) );
            do_action('dbnavt', NAVT_GEN, sprintf("%s - registered sidebar widget (generic navt)\n", __FUNCTION__));
        }
    }
}

// Wait for the sidebar widget plugin to load
add_action( 'widgets_init', 'widget_navt_register' );
// --------------
/**
 * Widget
 *
 * @param array $args - display classes/args
 * @param array $widget args - widget instance
 */
function navt_widget($args, $widget_args = 1) {

    extract( $args, EXTR_SKIP );
    if ( is_numeric($widget_args) ) {
        $widget_args = array( 'number' => $widget_args );
    }
    $widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
    extract( $widget_args, EXTR_SKIP );

    wp_cache_flush();
    $options = get_option(NAVT_WIDGET_OPTIONS);
    if( !$options || isBlank($options[$number][NAVT_GRPIDX]) ) {
        return;
    }

    if( function_exists('navt_getlist') ) {
        $widget_title = $options[$number][NAVT_TTLIDX];
        $group  = $options[$number][NAVT_GRPIDX];

        if ( !empty($widget_title) ) {
            $title = $before_title . $widget_title . $after_title;
        }
        else {
            $title = '';
        }

        $out = navt_getlist($group, false);
        if( !empty($out) && '' != $out ) {
            echo ($before_widget . $title . $out . $after_widget);
        }
    }
}

/**
 * Widget control
 *
 * @param array $widget_args
 */
function navt_widget_control($widget_args = 1) {

    global $wp_registered_widgets;
    static $updated = false;

    if ( is_numeric($widget_args) ) {
        $widget_args = array( 'number' => $widget_args );
    }

    $widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
    extract( $widget_args, EXTR_SKIP );
    wp_cache_flush();
    $options = get_option(NAVT_WIDGET_OPTIONS);

    if ( !$updated && !empty($_POST['sidebar']) ) {
        $sidebar = (string) $_POST['sidebar'];

        $this_sidebar = array();
        $sidebars_widgets = wp_get_sidebars_widgets();

        if ( isset($sidebars_widgets[$sidebar]) ) {
            $this_sidebar =& $sidebars_widgets[$sidebar];
        }

        foreach ( $this_sidebar as $_widget_id ) {
            if ( 'navt_widget' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number']) ) {
                $widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
                if ( !in_array( "navt-$widget_number", $_POST['widget-id'] ) ) { // the widget has been removed.
                    unset($options[$widget_number]);
                }
            }
        }

        foreach ( (array) $_POST['widget-navt'] as $widget_number => $widget_navt_instance ) {
            do_action('dbnavt', NAVT_GEN, sprintf("%s - widget-navt\n", __FUNCTION__), $_POST['widget-navt']);
            do_action('dbnavt', NAVT_GEN, sprintf("%s - widget-number\n", __FUNCTION__), $widget_number);
            do_action('dbnavt', NAVT_GEN, sprintf("%s - widget instance\n", __FUNCTION__), $widget_navt_instance);
            $widget_title = wp_specialchars( $widget_navt_instance['title'] );
            $widget_group = $widget_navt_instance['group'];
            $options[$widget_number][NAVT_TTLIDX] = $widget_title;
            $options[$widget_number][NAVT_GRPIDX] = $widget_group;
        }

        wp_cache_flush();
        update_option(NAVT_WIDGET_OPTIONS, $options);
        do_action('dbnavt', NAVT_GEN, sprintf("%s - options updated\n", __FUNCTION__));
        $updated = true;
    }

    $selected_group = '';
    if ( -1 == $number ) {
        $title = '';
        $number = '%i%';
    }
    else {
        $selected_group = $options[$number][NAVT_GRPIDX];
        $title = htmlspecialchars($options[$number][NAVT_TTLIDX], ENT_QUOTES);
    }

    do_action('dbnavt', NAVT_GEN, sprintf("%s - options\n", __FUNCTION__), $options);

    $navt_title  = sprintf("widget-navt[%s][title]", $number);
    $navt_group  = sprintf("widget-navt[%s][group]", $number);

    $title = htmlspecialchars($options[$number][NAVT_TTLIDX], ENT_QUOTES);
    $groups = null;
    if( function_exists('navt_get_all_groups') ) {
        $groups = navt_get_all_groups();
    }

    if( count($groups) > 0 ) {
        $ddlist = sprintf("<select name='%s'>\n", $navt_group);
        $curItem = (isBlank($curItem) ? $groups[0]: $selected_group);

        for( $i = 0; $i < count($groups); $i++ ) {
            $is_selected = ($selected_group == $groups[$i] ? " selected='selected'":'');
            $ddlist .= sprintf("<option value='%s' %s>%s</option>\n", $groups[$i], $is_selected, $groups[$i]);
        }

        $ddlist .= sprintf("</select>\n"); ?>
        <p><label style="text-align:left;line-height:35px;"><?php _e('Title', 'navt_domain'); ?>: </label>
        <input style="width: 200px;" type="text" name="<?php echo $navt_title;?>" value="<?php echo $title;?>" /><br />
        <label><?php _e('Select Navigation Group:', 'navt_domain'); ?></label><?php echo $ddlist;?><br /></p>
        <input type="hidden" id="widget-navt-submit-<?php echo $number; ?>" name="widget-navt[<?php echo $number; ?>][submit]" value="1" />

        <?php
    }
     else {?>
        <p style="color:red;">&bull; <?php _e('Navigation Lists have not been created.', 'navt_domain');?> &bull;</p>
        <?php
     }
}
?>