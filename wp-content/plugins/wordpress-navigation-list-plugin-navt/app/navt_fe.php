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
 * @subpackage navt front end functions
 * @author Greg A. Bellucci <greg[AT]gbellucci[DOT]us
 * @copyright Copyright &copy; 2006-2008 Greg A. Bellucci
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * -----------------------------------------------------------------------------
 * $Id: navt_fe.php 81144 2008-12-19 15:39:14Z gbellucci $:
 * $Date: 2008-12-19 15:39:14 +0000 (Fri, 19 Dec 2008) $:
 * $Revision: 81144 $:
 * -----------------------------------------------------------------------------
 *
 */
global $navt_map;
global $navt_groups;

/**
 * NAVT Front end functions
 */
class NAVT_FE {

    /**
     * Determines if a navigation group can be shown
     *
     * @param string $group
     * @return 1 = can show, 0 = cant show
     */
    function test_can_show($group) {
        //do_action('dbnavt', NAVT_GEN, sprintf("%s::%s group: %s\n", __CLASS__, __FUNCTION__, $group));

        global $navt_groups;
        $rc = 0;
        if( !empty($group) && !isBlank($group) ) {

            $sNavGroupName = strtolower($group);
            $navt_groups = NAVT::get_option(GCONFIG);

            $rc = (( !(NAVT_FE::can_display($sNavGroupName, $navt_groups)) ||
            (NAVT_FE::is_private_group($sNavGroupName, $navt_groups) && !is_user_logged_in()) ) ? 0: 1);
        }
        return($rc);
    }

    /** ----------------------------------------
     * Builds requested navigation lists (API)
     * -----------------------------------------
     *
     * @param string $sGroupName
     * @param string $sTitle
     * @param string $sBeforeGroup
     * @param string $sAfterGroup
     * @param string $sBeforeItem
     * @param string $sAfterItem
     */
    function getlist($sNavGroupName, $sTitle, $sBeforeGroup, $sAfterGroup, $sBeforeItem, $sAfterItem) {

        do_action('dbnavt', NAVT_GEN, sprintf("%s::%s group: %s\n", __CLASS__, __FUNCTION__, $sNavGroupName));

        global $navt_groups;
        $in = 1;
        $ng = $tmp = $forms = $uargs = array();
        $form_open = $form_count = $seq = 0;
        $listout = $before_text = $after_text = $breadcrumb = '';
        $sAfterItem = "<$sAfterItem>";

        if( !empty($sNavGroupName) && !isBlank($sNavGroupName) ) {

            $sNavGroupName = strtolower($sNavGroupName);
            $navt_groups = NAVT::get_option(GCONFIG);
            $show_debug = NAVT_FE::show_debug_in_source($sNavGroupName, $navt_groups);
            do_action('dbnavt', NAVT_GEN, sprintf("%s::%s show comment debug: %s\n", __CLASS__, __FUNCTION__, $show_debug));

            if( !(NAVT_FE::can_display($sNavGroupName, $navt_groups)) ||
            (NAVT_FE::is_private_group($sNavGroupName, $navt_groups) && !is_user_logged_in()) ) {
                // terminate this early
                do_action('dbnavt', NAVT_GEN, sprintf("%s::%s can't show\n", __CLASS__, __FUNCTION__));
                do_action('dbnavt', NAVT_GEN, sprintf("%s::%s groups\n", __CLASS__, __FUNCTION__), $navt_groups);

                $msg = (($show_debug) ? sprintf("\n<!-- %s v%s - ** MSG: Group '%s' cannot be shown on this page ** -->\n",
                NAVT_SCRIPTNAME, NAVT_SCRIPTVERS, $sNavGroupName) : '');

                return($msg);
            }

            // get group information
            $style_flags = NAVT_FE::set_style_flags($sNavGroupName, $navt_groups);
            $is_select = NAVT_FE::is_html_select($sNavGroupName, $navt_groups);
            $select_size = ( ( $is_select ) ? NAVT_FE::get_select_size($sNavGroupName, $navt_groups) : 0 );

            $uargs['user_ul_id'] = (($is_select) ? '' : $navt_groups[$sNavGroupName]['css']['ulid']);
            $uargs['user_ul_class'] = (($is_select) ? '' : $navt_groups[$sNavGroupName]['css']['ul']);
            $uargs['user_li_class'] = (($is_select) ? '' : $navt_groups[$sNavGroupName]['css']['li']);
            $uargs['user_li_class_current'] = (($is_select) ? '' : $navt_groups[$sNavGroupName]['css']['licurrent']);
            $uargs['user_li_parent_class'] = (($is_select) ? '' : $navt_groups[$sNavGroupName]['css']['liparent']);
            $uargs['user_li_parent_class_current'] = (($is_select) ? '' : $navt_groups[$sNavGroupName]['css']['liparent_active']);

            $use_page_folding = (($is_select) ? 0: NAVT_FE::use_pagefolding($sNavGroupName, $navt_groups));
            $add_backlink = (($is_select) ? 0: NAVT_FE::get_page_return($sNavGroupName, $navt_groups));
            $add_breadcrumbs = (($is_select) ? 0: NAVT_FE::use_breadcrumbs($sNavGroupName, $navt_groups));
            $add_before = stripslashes(html_entity_decode($navt_groups[$sNavGroupName]['selector']['before'], ENT_QUOTES));
            $add_before = str_replace('"', "'", $add_before);
            $add_after = stripslashes(html_entity_decode($navt_groups[$sNavGroupName]['selector']['after'], ENT_QUOTES));
            $add_after = str_replace('"', "'", $add_after);

            if(empty($sBeforeGroup) || isBlank($sBeforeGroup)) {
                $sAfterGroup = '';
            }
            else {
                $language = 'en-US';
                if( defined(WPLANG) ) {
                    $language = (isBlank(WPLANG) ? $language: WPLANG);
                }
                $language = str_replace('_', '-', $language);
                if( class_exists('browzer') ) {
                    $brz = new Browzer();
                    $browzer = sanitize_title_with_dashes($brz->Name);
                    $browzer .= ' v' . sanitize_title($brz->Version);
                }

                $sB = $sBeforeGroup;
                $sA = $sAfterGroup;

                $ul_class = NAVT_FE::set_item_class($style_flags, $sBeforeGroup,
                "menu $sNavGroupName $browzer $language", $uargs['user_ul_class']);

                $sBeforeGroup = sprintf("%s%s\n%s%s", _indentt($in), $add_before, _indentt($in), "<$sBeforeGroup");

                if( $style_flags & USE_USER_CLASSES ) {
                    if( !isBlank($uargs['user_ul_id']) ) {
                        $sBeforeGroup .= " id='".$uargs['user_ul_id']."'";
                    }
                }
                $sBeforeGroup .= ((!isBlank($ul_class)) ? " $ul_class>" : ">");
                $sAfterGroup  = sprintf("%s<%s>%s\n%s", _indentt($in), $sAfterGroup, _indentt($in+1), $add_after);
            }

            if( !isset($_POST['navt-noanounc']) ) {
                $before_text = sprintf("\n%s<!-- %s v%s -->", _indentt($in), NAVT_SCRIPTNAME, NAVT_SCRIPTVERS);
                $sAfterGroup .= sprintf("%s<!--/%s-->\n\n", _indentt($in), NAVT_SCRIPTNAME);
            }
            else {
                $before_text = "\n";
                $sAfterGroup .= "\n";
            }

            if( $add_breadcrumbs ) {$before_text .= _indentt($in) . "@BC@\n";}
            if( !isBlank($sTitle) ) $before_text .= _indentt($in) . "$sTitle\n";
            if( !isBlank($sBeforeGroup) ) $before_text .= "$sBeforeGroup\n";
            $after_text = $sAfterGroup;

            // collect all of the items in the named group
            $all_nav_items = NAVT::load_map();

            if( !is_array($all_nav_items) || count($all_nav_items) <= 0 ) {
                // terminate this early
                $msg = (($show_debug) ? sprintf("\n<!-- %s v%s - ** MSG: Navigation groups have not been created -->\n",
                NAVT_SCRIPTNAME, NAVT_SCRIPTVERS) : '');
                return($msg);
            }

            foreach( $all_nav_items as $nav_item ) {

                // case insensitive name compare
                if( strcasecmp($nav_item[GRP], $sNavGroupName) || !strcasecmp($sNavGroupName, ID_DEFAULT_GROUP) ) {
                    continue;
                }

                $nav_item[GRP] = strtolower($nav_item[GRP]); // force group names to lowercase
                $ng[] = $nav_item;
            }

            if( !is_array($ng) || count($ng) <= 0 ) {
                $msg = (($show_debug) ? sprintf("\n<!-- %s v%s - ** MSG: no items found for navigation group '%s' -->\n",
                NAVT_SCRIPTNAME, NAVT_SCRIPTVERS, $sNavGroupName): '');
                return($msg);
            }

            // preprocess the group contents
            $tmp = (($is_select) ? NAVT_FE::calc_selects($ng) :
            NAVT_FE::calc_nest($ng, $use_page_folding, $add_backlink, $add_breadcrumbs));

            //do_action('dbnavt', NAVT_GEN, sprintf("%s::%s working list:\n", __CLASS__, __FUNCTION__), $tmp);

            if( !is_array($tmp) || count($tmp) <= 0 ) {
                $msg = (($show_debug) ?
                sprintf("\n<!-- %s v%s - ** MSG: processing group: '%s' has resulted in an empty navigation list ** -->\n",
                NAVT_SCRIPTNAME, NAVT_SCRIPTVERS, $sNavGroupName) : '');
                return($msg);
            }

            // Start creating the list
            do_action('dbnavt', NAVT_GEN, sprintf("%s::%s list directive\n", __CLASS__, __FUNCTION__), $tmp);
            $in++;
            foreach( $tmp as $idx => $item ) {

                if( !$is_select ) {

                    if( isset($item['tok']) ) {

                        if($item['tok'] == 's-ul' ) {
                            $user_ul_class = (($style_flags & USE_USER_CLASSES && !isBlank($uargs['user_ul_class'])) ? $uargs['user_ul_class']: '');
                            $name = $item['nm']; $sub = $item['level'];
                            $ul_class = NAVT_FE::set_item_class($style_flags, 'ul', "$name $sNavGroupName-sublevel-$sub", $user_ul_class, 'children');
                            $listout .= _indentt($in++) . "<$sB" . ((isBlank($ul_class)) ? ">\n" : " $ul_class>\n");
                        }
                        elseif( $item['tok'] == 'e-ul' ) {
                            $in--;
                            $listout .= _indentt($in) . "<$sA>\n";
                        }
                        elseif( $item['tok'] == 's-li' ) {
                            $nav_item = $item['item'];
                            $is_current_page = $item['is_current_page'];
                            $is_backlink = $item['is_backlink'];
                            $parent_class = 'item_parent';

                            if($style_flags & USE_USER_CLASSES && !isBlank($uargs['user_li_parent_class']) ) {
                                $parent_class = $uargs['user_li_parent_class'];
                            }
                            if( isset($item['active_parent']) && $item['active_parent'] ) {
                                $parent_class = 'current_item_parent';
                                if($style_flags & USE_USER_CLASSES && !isBlank($uargs['user_li_parent_class_current']) )
                                $parent_class = $uargs['user_li_parent_class_current'];
                            }

                            $listout .= NAVT_FE::mk_navlist($nav_item, $sBeforeItem, '', $in, $seq++, $style_flags,
                            $is_current_page, $is_backlink, $parent_class, $uargs);
                        }
                        elseif( $item['tok'] == 'e-li' ) {
                            $listout .= _indentt($in) . "$sAfterItem\n";
                        }
                        elseif( $item['tok'] == 's-li-e-li' ) {
                            $nav_item = $item['item'];
                            $is_current_page = $item['is_current_page'];
                            $parent_class = '';
                            if( isset($item['active_parent']) && $item['active_parent'] ) {
                                $parent_class = 'current_item_parent';
                            }
                            $is_backlink = $item['is_backlink'];
                            $listout .= NAVT_FE::mk_navlist($nav_item, $sBeforeItem, $sAfterItem, $in, $seq++, $style_flags,
                            $is_current_page, $is_backlink, $parent_class, $uargs);
                        }
                        elseif( $item['tok'] == 's-bc' ) {
                            if( $add_breadcrumbs ) {
                                $breadcrumb = $item['trail'];
                                $before_text = str_replace('@BC@', "<div class='breadcrumbs'><p class='trail'>$breadcrumb</p></div>", $before_text);
                            }
                        }
                        elseif( $item['tok'] == 'code' ) {
                            $listout .= _indentt($in) . $item['code'] ."\n";
                        }
                    }
                }
                else {

                    // HTML Select format processing
                    if( isset($item['tok']) ) {
                        if( $item['tok'] == 's' ) {

                            // Start a form with a select
                            $fid = NAVT::get_option(FORMNUM);
                            if( $fid == false ) { NAVT::add_option(FORMNUM, 1); $fid = 1;}
                            $fid = ( $fid > MAX_FORM ) ? 1: $fid;
                            NAVT::update_option(FORMNUM, $fid+1);

                            $forms[$form_count] .= NAVT_FE::mk_form($sNavGroupName, $fid, $in+2, $style_flags, $select_size);
                            $form_open = 1; // form is open
                        }
                        elseif( $item['tok'] == 'e' ) {
                            $forms[$form_count] .= NAVT_FE::end_form($in+2);
                            $form_open = 0; // form is closed
                            $form_count++; // advance the form number
                        }
                        else {
                            // just one of the items
                            $nav_item = $item['tok'];
                            if( is_array($nav_item) ) {
                                $forms[$form_count] .= NAVT_FE::mk_html_select($nav_item, $in+4, $style_flags);
                            }
                        }
                    }
                }
            }// end for

            //
            // Final stages
            //
            // select item(s)
            if( $is_select ) {
                if( $form_open ) {
                    $forms[$form_count] .= NAVT_FE::end_form($in+1);
                }

                // if the select is to be wrapped in a 'ul' or 'ol' then it must be inside an 'li'
                $is_ul = strpos($before_text, '<ul');
                $is_ol = strpos($before_text, '<ol');
                if( $is_ul !== false || $is_ol !== false ) {
                    // wrap it in a list item
                    $before_text .= _indentt($in+1) . '<li>' . "\n";
                    $after_text   = _indentt($in+1) . '</li>' . "\n" . $after_text;
                }

                $out = $before_text;
                for( $i = 0; $i < count($forms); $i++ ) {
                    $out .= $forms[$i];
                }
                $out .= $after_text;
                do_action('dbnavt', NAVT_MAP, sprintf("%s output: %s\n", __FUNCTION__, $out));
            }
            else {
                // simple list
                $out = $before_text . $listout . $after_text;
                do_action('dbnavt', NAVT_MAP, sprintf("%s output: %s\n", __FUNCTION__, $out));
            }
        } // end if
        else {
            // bad group name comment
            $out = _indentt($in);
            $out .= (($show_debug) ? sprintf("<!-- %s v%s ** MSG: Empty or missing group name. -->\n",
            NAVT_SCRIPTNAME, NAVT_SCRIPTVERS) : '');
            do_action('dbnavt', NAVT_GEN, sprintf("%s::%s Empty or missing group name\n", __CLASS__, __FUNCTION__));
        }

        do_action('dbnavt', NAVT_GEN, sprintf("%s::%s output: %s\n", __CLASS__, __FUNCTION__, $out));
        return($out);

    }// end navt_getlist

    /**
     * Group preprocessing
     * Builds a set of directives for creating the list of items
     *
     * @param array $ng - contents of the group
     * @return array
     * @since .95.4.6 - updated to make an active item's parent active
     */
    function calc_nest($ng, $use_page_folding=0, $add_backlink=0, $add_breadcrumbs=0) {
        $ar = array();
        $backlink_idx = (-1);

        // locate the current item
        $loc = NAVT_FE::get_current_location($ng);
        do_action('dbnavt', NAVT_MAP, sprintf("%s::%s - location:\n", __CLASS__, __FUNCTION__), $loc);

        $tmp = array();
        $i = $open_ul = $open_li = $complete = $nlevel = 0;
        $groupname = $ng[0][GRP];

        // remove all disconnected items from the array
        // these get ignored.
        for( $idx = 0; $idx < count($ng); $idx++ ) {
            $o = intval($ng[$idx][OPT],10);
            if( $o & DISCONNECTED ) {
                continue;
            }
            $ar[] = $ng[$idx];
        }

        // set the modified array
        $ng = $ar;
        $sv = $ar;

        if( count($ng) <= 0 ) {
            // terminate this early
            return(null);
        }

        if( $use_page_folding ) {
            $ar = array();

            // determine which elements to eliminate
            // eliminates child pages
            $force_top = (is_home() ? 1: 0);
            if( is_home() || is_page() || is_category() || is_single() || is_archive() ) {
                // immediate children of the currently viewed page
                for( $end = $idx = 0; $idx < count($ng) && !$end; $idx++ ) {
                    $this_loc = ($ng[$idx][TYP] . $ng[$idx][IDN]);
                    $is_current_page = (($this_loc == $loc['page']) ? 1: $force_top);

                    if( $is_current_page ) {
                        $cur_page_idx = $idx;
                        $p = (($force_top) ? (-1): intval($ng[$idx][LVL],10)); // parent level
                        for($end = 1, $x = $idx; $x < count($ng); $x++) {
                            $c = intval($ng[$x][LVL],10); // level of this item
                            if( $c == ($p+1) ) {
                                $ar[] = $ng[$x];
                            }
                            elseif( ($c == $p) && ($x != $idx) ) {
                                break;
                            }
                        }
                        // get the parent of the current page
                        if( $add_backlink ) {
                            if( !is_home() ) {
                                $c = intval($ng[$cur_page_idx][LVL],10); // level of this item
                                for($has_backlink = $end = 0, $x = $cur_page_idx; $x >= 0 && !$end; $x--) {
                                    $p = (($x > 0) ? intval($ng[$x-1][LVL],10) : 0);
                                    if( $p < $c ) {
                                        $has_backlink = $end = 1;
                                        $backlink = $ng[$x-1];
                                        $backlink[NME] = __('Return to ', 'navt_domain') . $backlink[NME];
                                        $ar[] = $backlink;
                                        $backlink_idx = count($ar)-1;
                                    }
                                }
                                if( !$has_backlink ) {
                                    $backlink = NAVT::make_home_link();
                                    $backlink[GRP] = $groupname;
                                    $backlink[NME] = __('Return to Home page', 'navt_domain');
                                    $ar[] = $backlink;
                                    $backlink_idx = count($ar)-1;
                                }
                            }
                        }
                        // set the modified array
                        // ---------------------
                        $ng = $ar;
                        // ---------------------
                    }// is_current_page
                }// end for
            }// end if
        }// end $use_page_folding

        // process the final navigation list
        $i = 0;
        if( $add_breadcrumbs ) {
            $tmp[$i]['tok'] = 's-bc';
            $tmp[$i++]['trail'] = NAVT_FE::get_crumbs($loc['page'], $sv);
        }

        for( $idx = 0; $idx < count($ng); $idx++ ) {

            $c = intval($ng[$idx][LVL],10);
            $n = ($idx+1 >= count($ng) ? 0: intval($ng[$idx+1][LVL],10));

            $this_loc = ($ng[$idx][TYP] . $ng[$idx][IDN]);
            $is_current_page = (($this_loc == $loc['page']) ? 1: 0);
            $is_backlink = (($idx == $backlink_idx && $backlink_idx != (-1)) ? 1: 0);

            if( $n > $c ) {

                // add 'above' code
                $codeblock = NAVT_FE::get_code('above', $ng[$idx]);
                if( !isBlank($codeblock) ) {
                    $tmp[$i]['tok'] = 'code';
                    $tmp[$i++]['code'] = $codeblock . sprintf("<!-- %s -->", __LINE__);
                }

                $tmp[$i]['tok'] = 's-li';
                $tmp[$i]['is_current_page'] = $is_current_page;
                $tmp[$i]['level'] = $nlevel;
                $tmp[$i]['is_parent'] = (( !$is_current_page ) ? 1: 0);
                $tmp[$i]['is_backlink'] = $is_backlink;
                $tmp[$i++]['item'] = $ng[$idx];
                $open_li++;

                // add 'below' code
                $codeblock = NAVT_FE::get_code('below', $ng[$idx]);
                if( !isBlank($codeblock) ) {
                    $tmp[$i]['tok'] = 'code';
                    $tmp[$i++]['code'] = $codeblock . sprintf("<!-- %s -->", __LINE__);
                }

                // add the child 'before' code
                $codeblock = NAVT_FE::get_code('before', $ng[$idx+1]);
                if( !isBlank($codeblock) ) {
                    $tmp[$i]['tok'] = 'code';
                    $tmp[$i++]['code'] = $codeblock . sprintf("<!-- %s -->", __LINE__);
                }

                // start a new level
                $tmp[$i]['tok'] = 's-ul';
                $tmp[$i]['is_current_page'] = 0;
                $tmp[$i]['nm'] = sanitize_title($ng[$idx][TTL]);
                $tmp[$i++]['level'] = ++$nlevel;
                $open_ul++;
            }

            elseif( $n < $c ) {

                // terminate this nested list
                // first add the current item

                // add 'above' code
                $codeblock = NAVT_FE::get_code('above', $ng[$idx]);
                if( !isBlank($codeblock) ) {
                    $tmp[$i]['tok'] = 'code';
                    $tmp[$i++]['code'] = $codeblock . sprintf("<!-- %s -->", __LINE__);
                }

                $tmp[$i]['tok'] = 's-li-e-li';
                $tmp[$i]['is_current_page'] = $is_current_page;
                $tmp[$i]['is_backlink'] = $is_backlink;
                if( $this_loc == $loc['parent'] ) {
                    $tmp[$i]['active_parent'] = 1;
                }
                $tmp[$i]['level'] = $nlevel;
                $tmp[$i++]['item'] = $ng[$idx];

                // rewind to next item level
                while( $nlevel > $n ) {

                    if( $open_ul > 0 ) {

                        // add 'below' code
                        $codeblock = NAVT_FE::get_code('below', $ng[$idx]);
                        if( !isBlank($codeblock) ) {
                            $tmp[$i]['tok'] = 'code';
                            $tmp[$i++]['code'] = $codeblock . sprintf("<!-- %s -->", __LINE__);
                        }

                        // end UL tag
                        $tmp[$i++]['tok'] = 'e-ul';
                        $open_ul--;

                        // add 'after' code
                        $codeblock = NAVT_FE::get_code('after', $ng[$idx]);
                        if( !isBlank($codeblock) ) {
                            $tmp[$i]['tok'] = 'code';
                            $tmp[$i++]['code'] = $codeblock . sprintf("<!-- %s -->", __LINE__);
                        }
                    }

                    if($open_li > 0) {
                        // end LI tag
                        $tmp[$i++]['tok'] = 'e-li';
                        $open_li--;
                    }
                    $nlevel--;
                }
            }

            else {
                // no change in level
                // just add the item

                // LI -> /LI tag

                $codeblock = NAVT_FE::get_code('above', $ng[$idx]);
                if( !isBlank($codeblock) ) {
                    $tmp[$i]['tok'] = 'code';
                    $tmp[$i++]['code'] = $codeblock . sprintf("<!-- %s -->", __LINE__);
                }

                $tmp[$i]['tok'] = 's-li-e-li';
                $tmp[$i]['is_current_page'] = $is_current_page;
                $tmp[$i]['is_backlink'] = $is_backlink;
                if( $this_loc == $loc['parent'] ) {
                    $tmp[$i]['active_parent'] = 1;
                }
                $tmp[$i]['level'] = $nlevel;
                $tmp[$i++]['item'] = $ng[$idx];

                $codeblock = NAVT_FE::get_code('below', $ng[$idx]);
                if( !isBlank($codeblock) ) {
                    $tmp[$i]['tok'] = 'code';
                    $tmp[$i++]['code'] = $codeblock . sprintf("<!-- %s -->", __LINE__);
                }
            }
        }

        // Unwind the nest
        $complete = 0;
        while( !$complete ) {

            if( $open_ul > 0 ) {

                // end UL tag
                $tmp[$i++]['tok'] = 'e-ul';
                $open_ul--;

                $codeblock = NAVT_FE::get_code('after', $ng[$idx]);
                if( !isBlank($codeblock) ) {
                    $tmp[$i]['tok'] = 'code';
                    $tmp[$i++]['code'] = $codeblock . sprintf("<!-- %s -->", __LINE__);
                }
            }

            if($open_li > 0) {

                // end LI tag
                $tmp[$i++]['tok'] = 'e-li';
                $open_li--;

                $codeblock = NAVT_FE::get_code('below', $ng[$idx]);
                if( !isBlank($codeblock) ) {
                    $tmp[$i]['tok'] = 'code';
                    $tmp[$i++]['code'] = $codeblock . sprintf("<!-- %s -->", __LINE__);
                }
            }

            $complete = ($open_ul == 0) ? 1: 0;
        }

        // Walk the list backward to locate the parent container
        for($end = 0, $x = (count($tmp)-1); $x >= 0 && !$end; $x--) {
            if( $tmp[$x]['is_current_page'] ) {
                for( $xx = ($x-1); $xx >= 0 && !$end; $xx-- ) {
                    if( $tmp[$xx]['is_parent'] == 1) {
                        if( $tmp[$xx]['level'] < $tmp[$x]['level'] ) {
                            $tmp[$xx]['active_parent'] = 1;
                            $end = 1;
                        }
                    }
                }
            }
        }

        return($tmp);
    }


    /**
     * Returns user code for injection
     *
     * @param string $which - which codeblock to return
     * @param array $item - navigation item
     * @return string codeblock
     */
    function get_code($which, $item) {

        do_action('dbnavt', NAVT_GEN, sprintf("%s::%s\n", __CLASS__, __FUNCTION__));
        $c = split(':::', $item[WRAPCODE]);
        $code = '';

        if( $which == 'above' ) {
            if( ($item[OP2] & CODEABOVE) ) {
                $code = $c[0];
            }
        }
        elseif( $which == 'above|before' || $which == 'before|above' ) {
            $code = $c[0];
        }

        elseif( $which == 'before' ) {
            if( !($item[OP2] & CODEABOVE) ) {
                $code = $c[0];
            }
        }

        elseif( $which == 'after' ) {
            if( !($item[OP2] & CODEBELOW) ) {
                $code = $c[1];
            }
        }

        elseif( $which == 'after|below' || $which == 'below|after' ) {
            $code = $c[1];
        }

        elseif( $which == 'below' ) {
            if($item[OP2] & CODEBELOW) {
                $code = $c[1];
            }
        }

        do_action('dbnavt', NAVT_GEN, sprintf("%s::%s returning which: %s, code: %s\n", __CLASS__, __FUNCTION__, $which, $code));
        return($code);
    }

    /**
     * Group preprocessing
     * Builds a set of directives for creating an HTML select dropdown
     *
     * @param array $ng - contents of a group
     * @return array
     */
    function calc_selects($ng) {

        // for select menus - calculate selects
        $tmp = array();
        $nidx = $sm = 0;
        foreach( $ng as $idx => $item ) {
            if( !$sm ) {
                if( $item[TYP] == TYPE_SEP ) {
                    // just add the item - outside of a select
                    $tmp[$nidx++]['tok'] = $item;
                }
                else {
                    // add the item before the select begins
                    $tmp[$nidx++]['tok'] = 's';
                    $tmp[$nidx++]['tok'] = $item;
                    $sm = 1;
                }
                continue;
            }
            else {
                if( $item[TYP] == TYPE_SEP ) {
                    // end the select then add the item
                    $tmp[$nidx++]['tok'] = 'e';
                    $tmp[$nidx++]['tok'] = $item;
                    $sm = 0;
                    continue;
                }
            }
            // add the item
            $tmp[$nidx++]['tok'] = $item;
        }

        if( $sm ) {
            $tmp[$nidx++]['tok'] = 'e';
        }

        //do_action('dbnavt', NAVT_MAP, sprintf("%s::%s \n", __CLASS__, __FUNCTION__), $tmp);
        return($tmp);
    }

    /**
     * Returns a breadcrumb trail
     *
     * @param string $location - current page location
     * @param array $ar
     * @return array
     */
    function get_crumbs($location, $ar) {

        //do_action('dbnavt', NAVT_MAP, sprintf("%s::%s ar :\n", __CLASS__, __FUNCTION__), $ar);
        //do_action('dbnavt', NAVT_MAP, sprintf("%s::%s loc: %s \n", __CLASS__, __FUNCTION__, $location));

        $trail = array();
        $crumb_trail = '';

        // walk this backward
        for($end = 0, $i = (count($ar)-1); $i >= 0; $i--) {
            $this_loc = ($ar[$i][TYP] . $ar[$i][IDN]);
            if( $this_loc == $location ) {
                //do_action('dbnavt', NAVT_MAP, sprintf("%s::%s current page idx: %s\n", __CLASS__, __FUNCTION__, $i));
                break;
            }
        }

        if( $i >= 0 ) {
            $c = intval($ar[$i][LVL],10); $x = $i; $end = 0;
            if($c > 0) {
                while(!$end) {
                    $x = NAVT_FE::get_item_parent($x, $ar);
                    //do_action('dbnavt', NAVT_MAP, sprintf("%s::%s parent idx: %s\n", __CLASS__, __FUNCTION__, $x));
                    if( $x != (-1) ) {
                        $trail[] = $ar[$x];
                    }
                    else {
                        $end = 1;
                    }
                }
            }
            $trail[] = NAVT::make_home_link();
        }

        if( count($trail) > 0 ) {
            //do_action('dbnavt', NAVT_MAP, sprintf("%s::%s item count: %s\n", __CLASS__, __FUNCTION__, count($trail)));

            for( $i = (count($trail)-1); $i >= 0; $i-- ) {
                $item = $trail[$i];
                //do_action('dbnavt', NAVT_MAP, sprintf("%s::%s item:\n", __CLASS__, __FUNCTION__), $item);

                if( $item[TYP] == TYPE_CAT ) {
                    $link_url = get_category_link($item[IDN]);
                }
                elseif( $item[TYP] == TYPE_PAGE ) {
                    $link_url = get_permalink($item[IDN]);
                }
                elseif( $item[TYP] == TYPE_AUTHOR ) {
                    $link_url = get_author_link(false, $trail[$i][IDN]);
                }
                elseif( $item[IDN] == HOMEIDN ) {
                    $link_url = get_option('siteurl');
                }
                else {
                    //do_action('dbnavt', NAVT_MAP, sprintf("%s::%s ??\n", __CLASS__, __FUNCTION__));
                }

                if( $i != (count($trail)-1) ) {
                    $crumb_trail .= ' &raquo; ';
                }
                $crumb_trail .= "<a href='$link_url' title='" . attribute_escape($item[TTL]) . "'>".$item[NME]."</a>";
            }
        }

        //do_action('dbnavt', NAVT_MAP, sprintf("%s::%s crumb trail:\n", __CLASS__, __FUNCTION__), $trail);
        //do_action('dbnavt', NAVT_MAP, sprintf("%s::%s trail output: %s\n", __CLASS__, __FUNCTION__, $crumb_trail));
        return($crumb_trail);
    }

    /**
     * Returns the index to a menu item parent
     *
     * @param integer $idx - index of a menu item
     * @param array $ar - complete array of items
     * @return integer > 0 = index of parent, -1 no parent
     */
    function get_item_parent($idx, $ar) {

        $rc = (-1);
        $c = intval($ar[$idx][LVL],10);
        for( $x = $idx; $x >= 0; $x-- ) {
            if( ($x-1) >= 0 ) {
                $p = intval($ar[$x-1][LVL],10);
                if( $p < $c ) {
                    $rc = ($x-1);
                    break;
                }
            }
            else {
                break;
            }
        }
        return($rc);
    }

    /**
     * Returns the current page location
     *
     * @return string
     */
    function get_current_location($group_list=array()) {
        global $wp_query;
        $qo = $wp_query->get_queried_object();
        $loc = array();
        $loc['parent'] = '';
        $loc['page'] = TYPE_LINK . HOMEIDN; // default
        $loc['set'] = 0;

        $is_front_page = ((function_exists('is_front_page')) ? is_front_page() : 0);

        do_action('dbnavt', NAVT_GEN, sprintf("%s::%s query object\n", __CLASS__, __FUNCTION__), $qo);

        if( $wp_query->is_posts_page ) {
            $p = get_page(get_option('page_for_posts'));
            $loc['parent'] = '';
            $loc['page'] = TYPE_LINK . $p->ID;
            $loc['set'] = 1;
        }

        if( $is_front_page && !$loc['set'] ) {
            if ( 'posts' == get_option('show_on_front') && is_home() ) {
                $loc['parent'] = '';
                $loc['page'] = TYPE_LINK . HOMEIDN;
                $loc['set'] = 1;
            }
            elseif('page' == get_option('show_on_front') && get_option('page_on_front') && is_page(get_option('page_on_front')) ) {
                $loc['parent'] = '';
                $loc['page'] = TYPE_LINK . get_option('page_on_front');
                $loc['set'] = 1;
            }
        }

        if( is_page() && !empty($qo->ID) && !$loc['set'] ) {
            $loc['page'] = TYPE_PAGE . $qo->ID;
            $loc['set'] = 1;
            $page_in_group = NAVT_FE::is_item_in_group($group_list, TYPE_PAGE, $qo->ID);

            if( !$page_in_group ) {
                $end = 0; $stack = array();
                if( $qo->post_parent != 0 ) {
                    array_push($stack, $qo->post_parent);
                    $post_parent = $qo->post_parent;
                    while( !$end ) {
                        $p = get_post($post_parent);
                        if( $p->post_parent != 0 ) {
                            array_push($stack, $p->post_parent);
                            $post_parent = $p->post_parent;
                        }
                        else {
                            $end = 1;
                        }
                    }
                }

                if( count($stack) > 0 ) {
                    for( $i = 0; $i < count($group_list); $i++ ) {
                        $item = $group_list[$i];
                        if( in_array($item[IDN], $stack) ) {
                            $loc['parent'] = TYPE_PAGE.$item[IDN];
                            $loc['page'] = '';
                            break;
                        }
                    }
                }
            }
        }
        elseif( is_category() && !empty($qo->cat_ID) && !$loc['set']) {
            $loc['page'] = TYPE_CAT . $qo->cat_ID;
            $loc['set'] = 1;
            $page_in_group = NAVT_FE::is_item_in_group($group_list, TYPE_CAT, $qo->cat_ID);

            if( !$page_in_group ) {
                $end = 0; $stack = array();

                if( $qo->category_parent != 0 ) {
                    array_push($stack, $qo->category_parent);
                    $cat_parent = $qo->category_parent;
                    while( !$end ) {
                        $p = get_category($cat_parent);
                        if( $p->category_parent != 0 ) {
                            array_push($stack, $p->category_parent);
                            $cat_parent = $p->category_parent;
                        }
                        else {
                            $end = 1;
                        }
                    }
                }

                if( count($stack) > 0 ) {
                    for( $i = 0; $i < count($group_list); $i++ ) {
                        $item = $group_list[$i];
                        if( in_array($item[IDN], $stack) ) {
                            $loc['parent'] = TYPE_CAT.$item[IDN];
                            $loc['page'] = '';
                            break;
                        }
                    }
                }
            }
        }
        elseif( is_single() && !$loc['set'] ) {
            $cats = get_the_category();
            //do_action('dbnavt', NAVT_MAP, sprintf("%s::%s categories:\n", __CLASS__, __FUNCTION__), $cats);
            if( is_array($cats) ) {
                $loc['parent'] = TYPE_CAT . $cats[0]->cat_ID;
                $loc['page'] = '';
                $loc['set'] = 1;
            }
        }

        do_action('dbnavt', NAVT_MAP, sprintf("%s::%s calculated location:\n", __CLASS__, __FUNCTION__), $loc);
        return($loc);
    }

    /**
     * Determines if the current page is contained within a
     * navigation group
     *
     * @param array $group_list - navigation group
     * @param integer $typ - TYPE_PAGE, TYPE_CAT ... etc
     * @param integer $this_id - the current page's database ID
     * @return boolean - 1 = in group, 0 = not in gorup
     */
    function is_item_in_group($group_list, $typ, $this_id) {

        $rc = 0;

        // Is this page part of this group?
        for( $i = 0; $i < count($group_list); $i++ ) {
            $item = $group_list[$i];
            if( ($item[TYP] == $typ) && ($item[IDN] == $this_id) ) {
                $rc = 1;
                break;
            }
        }

        //do_action('dbnavt', NAVT_MAP, sprintf("%s::%s returning: %s\n", __CLASS__, __FUNCTION__, $rc));
        return($rc);
    }

    /**
     * Create one list item of the HTML unnumbered list
     *
     * @param array $nav_item - the item to be created
     * @param string $sBeforeItem - the opening HTML tag
     * @param string $sAfterItem - the closing HTML tag
     * @param integer $in - the indentation level
     * @param integer $seq - a sequence number
     * @param integer $style_flags - the group styles (bits)
     * @param boolean $is_current_page - 1 = is current page, 0 = not current page
     * @param string $parent_class - the parent container class
     * @param array $uargs - the user's defined classes for the group
     * @return string - html output
     */
    function mk_navlist($nav_item, $sBeforeItem, $sAfterItem, $in, $seq,
    $style_flags, $is_current_page=0, $is_backlink=0, $parent_class='', $uargs) {

        $args = array();
        $li = '';
        $idn = $nav_item[IDN];
        $opt = intval($nav_item[OPT],10) & 0xffffff;
        $typ = $nav_item[TYP];

        $is_private = ($opt & ISPRIVATE) ? 1: 0;
        $can_show = (($is_private) ? (is_user_logged_in() ? 1: 0) : 1);
        $can_show = (($opt & DISCONNECTED) ? 0: $can_show);

        if($can_show) {

            if( $style_flags & USE_NAVT_DEFAULTS ) {
                $row = (($seq % 2 > 0) ? 'orow' : 'erow');
                $page_class = (($is_current_page) ? CURPAGEITEM: PAGEITEM);
                $args['navt_item_class'] = "$row ". $nav_item[GRP] . "_item $page_class" .
                (!isBlank($parent_class) ? " $parent_class" : '') .
                (($is_private) ? " private" :'') . (($is_backlink) ? ' ' . RETURN_ANCHOR: '');
            }

            elseif( $style_flags & USE_WP_DEFAULTS ) {
                if( $typ != TYPE_CAT ) {
                    $args['wp_item_class'] = (( $is_current_page ) ? CURPAGEITEM: PAGEITEM);
                }
                else {
                    $args['wp_item_class'] = CATITEM . ' ' . CATITEM . "-$idn" . ($is_current_page ? ' ' . CURCATITEM: '');
                }
                $args['wp_item_class'] .= (!isBlank($parent_class) ? " $parent_class":'') .
                (($is_backlink) ? ' ' . RETURN_ANCHOR: '');
            }

            if( $style_flags & USE_USER_CLASSES ) {
                $page_class = $uargs['user_li_class'];
                $page_class .= ($is_current_page ? ' ' . $uargs['user_li_class_current'] : '');
                $args['user_item_class'] = $page_class;
                if( !isBlank($parent_class) ) {
                    $args['user_item_class'] .= (!isBlank($args['user_item_class']) ? " $parent_class" : $parent_class);
                }
            }

            // Parameters for creating items
            $args['nav_item'] = $nav_item;
            $args['sBeforeItem'] = $sBeforeItem;
            $args['sAfterItem'] = $sAfterItem;
            $args['in'] = $in;
            $args['style_flags'] = $style_flags;
            $args['img_src'] = NAVT::get_url() . '/' . IMG_BLANK;
            $args['reltags'] = (($opt & NOFOLLOW) ? "rel='nofollow'" : '');
            $args['post_count'] = '';
            $args['tooltip'] =
            $args['alt_text'] = $nav_item[NME];
            $args['usr_defined_class'] = $nav_item[EXT];
            $args['is_current_page'] = $is_current_page;

            switch( $typ ) {
                case TYPE_CAT:    { $li = NAVT_FE::mk_category_item($args); break; }
                case TYPE_PAGE:   { $li = NAVT_FE::mk_page_item($args); break; }
                case TYPE_LINK:   { $li = NAVT_FE::mk_link_item($args); break; }
                case TYPE_SEP:    { $li = NAVT_FE::mk_divider_item($args); break; }
                case TYPE_ELINK:  { $li = NAVT_FE::mk_uri_item($args);  break; }
                case TYPE_AUTHOR: { $li = NAVT_FE::mk_author_item($args);  break; }
                case TYPE_CODE:   { $li = NAVT_FE::mk_code_item($args);  break; }
            }// end switch item type
        }

        // some cleanup of extra spaces
        $li = str_replace(" '>", "'>", $li);
        $li = str_replace("' >", "'>", $li);
        $li = str_replace("a  href", "a href", $li);
        $li = str_replace('<p  ', '<p ', $li);

        return($li);
    }

    /**
     * Create a category item for the navigation group
     *
     * @param array $args
     * @return  HTML string
     */
    function mk_category_item($args) {

        extract($args);

        $li = '';
        $opt = intval($nav_item[OPT],10);
        $idn = intval($nav_item[IDN],10);
        $cat = get_category($idn);
        $num_posts = $cat->category_count;

        $args['anchor_class'] = 'navt_clink';
        $args['link_url'] = get_category_link($idn);
        $args['navt_item_class'] = ($navt_item_class .= ' ' . $cat->category_nicename . '-cat ' . TAB_CATEGORY);
        $args['img_class'] = $cat->category_nicename . '-img';

        if( $opt & USE_CAT_DESC ) {
            $args['tooltip'] = $cat->category_description;
        }

        if( $opt & SHOW_IF_EMPTY || $num_posts > 0 ) {

            if( $opt & GRAPHIC_LINK ) {
                $args['anchor_class'] = 'navt_glink';
                $li = NAVT_FE::mk_graphic_link($args);
            }
            else {
                // get post count and tooltip
                $c = NAVT_FE::set_item_class($style_flags, 'span', 'catcount');
                $args['post_count'] = (($opt & APPEND_POST_COUNT) ? "<span" . (!isBlank($c) ? " $c": '') . ">($num_posts)</span>" : '');

                // Link text with a side graphic
                if( $opt & TEXT_WITH_SIDE_GRAPHIC ) {
                    $li = NAVT_FE::mk_text_with_side_graphic($args);
                }
                // link with text over graphic
                else if( $opt & TEXT_OVER_GRAPHIC ) {
                    $li = NAVT_FE::mk_text_over_graphic($args);
                }
                else {
                    // plain text
                    extract($args);

                    $anchor_class .= ($is_current_page ? ' current_item' : '');
                    $item_class = NAVT_FE::set_item_class($style_flags, $sBeforeItem, $navt_item_class, $user_item_class, $wp_item_class);
                    $anchor_class = NAVT_FE::set_item_class($style_flags, 'a', $anchor_class, $usr_defined_class, '', 1);

                    $li  = _indentt($in) . "<$sBeforeItem" .
                    (!isBlank($item_class) ? " $item_class":'') . "><a href='$link_url' title='" . attribute_escape($tooltip) . "'" .
                    (!isBlank($reltags) ? " $reltags": '') . (!isBlank($anchor_class) ? " $anchor_class": '') .">$alt_text" .
                    (!isBlank($post_count) ? " $post_count":'') . "</a>$sAfterItem\n";
                }
            }
        }

        return( $li );
    }

    /**
     * Create a page item for the navigation group
     *
     * @param array $args
     * @return  HTML string
     */
    function mk_page_item($args) {

        extract($args);

        $li = '';
        $opt = intval($nav_item[OPT],10) & 0xffffff;
        $idn = intval($nav_item[IDN],10);

        $page = &get_post($idn);
        $args['navt_item_class'] .= ' ' . $page->post_name . '-page ' . TAB_PAGE;
        $args['img_class'] = $page->post_name . '-img ';
        $args['link_url'] = get_permalink($idn);
        $args['anchor_class'] = 'navt_plink';

        if( $opt & GRAPHIC_LINK ) {
            $args['anchor_class'] = 'navt_glink';
            $li = NAVT_FE::mk_graphic_link($args);
        }
        else {
            // Link text with a side graphic
            if( $opt & TEXT_WITH_SIDE_GRAPHIC ) {
                $li = NAVT_FE::mk_text_with_side_graphic($args);
            }
            // link with text over graphic
            else if( $opt & TEXT_OVER_GRAPHIC ) {
                $li = NAVT_FE::mk_text_over_graphic($args);
            }
            else {

                //Plain Text
                extract($args);

                $anchor_class .= ($is_current_page ? ' current_item' : '');
                $item_class = NAVT_FE::set_item_class($style_flags, $sBeforeItem, $navt_item_class, $user_item_class, $wp_item_class);
                $anchor_class = NAVT_FE::set_item_class($style_flags, 'a', $anchor_class, $usr_defined_class, '', 1);

                $li  = _indentt($in) . "<$sBeforeItem" .
                (!isBlank($item_class) ? " $item_class":'') . "><a href='$link_url' title='" . attribute_escape($tooltip) . "'" .
                (!isBlank($reltags) ? " $reltags": '') . (!isBlank($anchor_class) ? " $anchor_class": '') .">$alt_text</a>$sAfterItem\n";
            }
        }
        return( $li );
    }

    /**
     * Create a author item for the navigation group
     *
     * @param array $args - function arguments
     * @return string html output
     */
    function mk_author_item($args) {

        extract($args);

        $li = '';
        $opt = intval($nav_item[OPT],10);
        $idn = intval($nav_item[IDN],10);
        $nme = $nav_item[NME];

        $userdata = get_userdata($idn);
        $user_class = NAVT_FE::get_userlevel_class($idn);
        $num_user_posts = get_usernumposts($idn);
        $navt_item_class .= ' ' . 'user-' . $userdata->user_login . ' ' . $user_class . ' ' . TAB_AUTHOR;
        $args['img_class'] = $userdata->user_login . '-img';
        $args['link_url'] = get_author_link(false, $idn);
        $args['anchor_class'] = 'navt_alink';

        if( $opt & SHOW_IF_EMPTY || $num_user_posts > 0 ) {

            if( $opt & GRAPHIC_LINK ) {
                $args['anchor_class'] = 'navt_glink';
                if( $opt & SHOW_AVATAR ) {
                    $args['img_src'] = $nav_item[EXT];
                }
                $li = NAVT_FE::mk_graphic_link($args);
            }
            else {

                extract($args);
                $post_count = $userinfo = '';
                $meta = array();

                if(!($opt & NO_LINK_TEXT) && ($opt & APPEND_POST_COUNT)) {
                    $c = NAVT_FE::set_item_class($style_flags, 'span', 'post_count');
                    $post_count = "<span" . (!isBlank($c) ? " $c": '') . ">($num_user_posts)</span>";
                }

                if( $opt & INC_EMAIL && (!($opt & NO_LINK_TEXT)) ) {
                    if( !isBlank($userdata->user_email) )  {
                        $c = NAVT_FE::set_item_class($style_flags, 'a', 'navt_email');
                        $title = __("Send comments to", 'navt_domain') . " $alt_text";
                        $meta['email'] = "<a" . (!isBlank($c) ? " $c" : '') . " href='mailto:$userdata->user_email' title='$title'>".
                        __("email", 'navt_domain')."</a>";
                    }
                }

                if( $opt & INC_BIO && (!($opt & NO_LINK_TEXT)) ) {
                    if( !isBlank($userdata->description) ) {
                        $c = NAVT_FE::set_item_class($style_flags, 'p', 'navt_bio');
                        $meta['bio'] = "<p" . (!isBlank($c) ? " $c" : '') . ">$userdata->description</p>";
                    }
                }

                if( $opt & INC_WEBSITE && (!($opt & NO_LINK_TEXT)) )  {
                    if( !isBlank($userdata->user_url) && $userdata->user_url != 'http://' ) {
                        $c = NAVT_FE::set_item_class($style_flags, 'a', 'navt_weburl');
                        $title = __("Visit this web site", 'navt_domain');
                        $meta['web'] = "<a" . (!isBlank($c) ? " $c" : '') . " href='$userdata->user_url' title='$title' rel='external'>".
                        __("Web Site", 'navt_domain')."</a>";
                    }
                }

                if( count($meta) > 0 ) {
                    $c = NAVT_FE::set_item_class($style_flags, 'div', 'navt_user_details');
                    $userinfo = "\n" . _indentt($in+1) . "<div" . (!isBlank($c) ? " $c":'') .">\n";
                    foreach( $meta as $key => $option ) {
                        $userinfo .= _indentt($in+2) . $option . "\n";
                    }
                    $userinfo .= _indentt($in+1) . "</div>";
                }

                $title = __("View all entries by", 'navt_domain') . " $nme";
                $anchor_class .= ($is_current_page ? ' current_item' : '');
                $item_class = NAVT_FE::set_item_class($style_flags, $sBeforeItem, $navt_item_class, $user_item_class, $wp_item_class);
                $anchor_class = NAVT_FE::set_item_class($style_flags, 'a', $anchor_class, $usr_defined_class, '', 1);
                $img_class = NAVT_FE::set_item_class($style_flags, 'img', 'navt_avatar');

                // start the html
                $li  = _indentt($in) . "<$sBeforeItem" .
                (!isBlank($item_class) ? " $item_class":'') . "><a href='$link_url' title='" . attribute_escape($title) . "'" .
                (!isBlank($reltags) ? " $reltags": '') . (!isBlank($anchor_class) ? " $anchor_class": '') .">";

                if( $opt & SHOW_AVATAR ) {

                    if($opt & USE_GRAVATAR) {
                        $li .= "\n" . _indentt($in+1) . navt_get_avatar($userdata->user_email);
                    }
                    else {
                        if( !isBlank($nav_item[EX2]) ) {
                            $img_src = $nav_item[EX2]; // fixed v1.0.6
                        }
                        // add the image
                        $li .= "\n" . _indentt($in+1) . "<img" .
                        (!isBlank($img_class) ? " $img_class" : '') . " src='$img_src' alt='" . attribute_escape($alt_text) ."' />";
                    }
                }

                // finish it
                $li .= (!($opt & NO_LINK_TEXT) ? $nme: '') . (!isBlank($post_count) ? " $post_count":'') . "</a>" .
                (!isBlank($userinfo) ? $userinfo: '') . "$sAfterItem\n";
            }
        }
        return( $li );
    }

    /**
     * Create a uri item for the navigation group
     *
     * @param array $args - function arguments
     * @return string html output
     */
    function mk_uri_item($args) {

        extract($args);

        $li = '';
        $opt = intval($nav_item[OPT], 10);
        $nme = $nav_item[NME];
        $ttl = $nav_item[TTL];

        $nameAsClass = sanitize_title_with_dashes(strtolower($nme));
        $args['navt_item_class'] .= ' ' . $nameAsClass . '-uri ' . TAB_ELINK;
        $args['img_class'] = $nameAsClass . '-img';
        $args['reltags'] =  ($opt & NOFOLLOW) ? " rel='nofollow' " : '';
        $args['reltags'] .= ($opt & OPEN_SAMEWIN) ? '' : "target='_blank'";
        $args['anchor_class'] = 'navt_elink';
        $args['link_url'] = $ttl;

        if( $opt & GRAPHIC_LINK ) {
            $args['anchor_class'] = 'navt_glink';
            $li = NAVT_FE::mk_graphic_link($args);
        }
        else {
            // Link text with a side graphic
            if( $opt & TEXT_WITH_SIDE_GRAPHIC ) {
                $li = NAVT_FE::mk_text_with_side_graphic($args);
            }
            else if( $opt & TEXT_OVER_GRAPHIC ) {
                $li = NAVT_FE::mk_text_over_graphic($args);
            }
            else {

                // plain text
                extract($args);

                $item_class = NAVT_FE::set_item_class($style_flags, $sBeforeItem, $navt_item_class, $user_item_class, $wp_item_class);
                $anchor_class .= ($is_current_page ? ' current_item' : '');
                $anchor_class = NAVT_FE::set_item_class($style_flags, 'a', $anchor_class, $usr_defined_class, '', 1);

                $li  = _indentt($in) . "<$sBeforeItem" .
                (!isBlank($item_class) ? " $item_class":'') . "><a href='$link_url' title='" . attribute_escape($alt_text) . "'" .
                (!isBlank($reltags) ? " $reltags": '') . (!isBlank($anchor_class) ? " $anchor_class": '') . ">$alt_text</a> $sAfterItem\n";
            }
        }
        return( $li );
    }

    /**
     * Create a home or login item for the navigation group
     *
     * @param array $args - function arguments
     * @return string html output
     */
    function mk_link_item($args) {

        extract($args);

        $li = '';
        $opt = intval($nav_item[OPT],10);
        $nme = $nav_item[NME];
        $root = get_option('home'); // root dir
        $wp_url = get_option('siteurl');; // wordpress dir
        $users_can_register = get_option('users_can_register');
        $using_login_form = 0;
        $split_anchor = '';
        $args['anchor_class'] = 'navt_ilink';

        if( $nav_item[IDN] == HOMEIDN ) {
            $args['navt_item_class'] .= (!isBlank($args['navt_item_class']) ? ' ' . TAB_HOME : TAB_HOME);
            $args['img_class'] = $link_imgclass = 'home-img ';
            $args['link_url'] = $root;
        }
        else if( $nav_item[IDN] == LOGINIDN ) {
            $args['navt_item_class'] .= (!isBlank($args['navt_item_class']) ? ' ' . TAB_ADMIN : TAB_ADMIN);
            $args['img_class'] = 'admin-img ';
            $args['tooltip'] = __('Site Admin', 'navt_domain');
            $args['link_url'] = "$wp_url/wp-admin/";

            if( !is_user_logged_in() ) {
                if( $opt & USE_FORM ) {
                    $using_login_form = 1;

                    // Determine after login redirection
                    $redirect = ( ($opt & REDIRECT_REFER) ? $_SERVER['PHP_SELF'] : ($opt & REDIRECT_URL) ? $nav_item[EX2]: '');
                    if( isBlank($redirect) ) {
                        $redirect = $root;
                    }

                    // Create a login form
                    $li = _indentt($in) . "<li class='lform'><form name='loginform' id='loginform' class='loginform' ".
                    "action='$wp_url/wp-login.php' method='post'><input type='hidden' name='redirect_to' value='$redirect' /><fieldset id='loginbox'>".
                    "<p><label> " . __('Username', 'navt_domain') . ":<br /><input type='text' name='log' id='user_login' value='' tabindex='1' /></label></p>" .
                    "<p><label>" . __('Password', 'navt_domain') . ":<br /><input type='password' name='pwd' id='user_pass' value='' tabindex='2' /></label></p>" .
                    "<p class='rememberme'><label><input name='rememberme' type='checkbox' id='rememberme' value='forever' tabindex='3' /> " .
                    __('Remember me', 'navt_domain') . "</label></p><p><input type='submit' name='submit' id='login-form' value='Login' tabindex='4' /></p>" .
                    "<p class='lost'><a href='$wp_url/wp-login.php?action=lostpassword'>".__('Lost your password?', 'navt_domain')."</a></p></fieldset></form>";

                    if( $users_can_register ) {
                        $li .= _indentt($in+1) . "<p><a href='$wp_url/wp-register.php' title='Register' rel='nofollow internal' >" .
                        __("Register", 'navt_domain') . "</a></p>";
                    }

                    $li .= "</li>\n";
                }
                else {
                    $args['tooltip'] = $nme;
                    $args['img_class'] = 'login-img ';
                    $args['link_url'] = "$wp_url/wp-login.php";

                    if( get_option('users_can_register') ) {
                        $args['img_class'] = 'register-img ';
                        $args['link_url'] = "$wp_url/wp-register.php";
                    }
                }
            }
            else {
                // user is logged in
                if( $opt & USE_FORM ) {
                    $split_anchor = "&nbsp;&bull;&nbsp;<a href='$wp_url/wp-login.php?action=logout&amp;redirect_to=$root' title='logout'>" .
                    __("Sign out", 'navt_domain')."</a>";
                }
            }
        }
        else {
            ; // shouldn't get here...
        }

        if( $opt & GRAPHIC_LINK && !$using_login_form ) {
            $args['anchor_class'] = 'navt_glink';
            $li = NAVT_FE::mk_graphic_link($args);
        }
        else {
            // Link text with a side graphic
            if( $opt & TEXT_WITH_SIDE_GRAPHIC && !$using_login_form ) {
                $li = NAVT_FE::mk_text_with_side_graphic($args);
            }
            else if( $opt & TEXT_OVER_GRAPHIC && !$using_login_form ) {
                $li = NAVT_FE::mk_text_over_graphic($args);
            }
            else {

                if( !$using_login_form ) {

                    // Plain text
                    extract($args);

                    $item_class = NAVT_FE::set_item_class($style_flags, $sBeforeItem, $navt_item_class, $user_item_class, $wp_item_class);
                    $anchor_class .= ($is_current_page ? ' current_item' : '');
                    $anchor_class = NAVT_FE::set_item_class($style_flags, 'a', $anchor_class, $usr_defined_class, '', 1);

                    $li = _indentt($in) . "<$sBeforeItem" .
                    (!isBlank($item_class) ? " $item_class" :'') . "><a href='$link_url' title='" . attribute_escape($tooltip) . "'" .
                    (!isBlank($reltags) ? " $reltags": '') . (!isBlank($anchor_class) ? " $anchor_class" : '') . ">$tooltip</a>" .
                    (!isBlank($split_anchor) ? $split_anchor: '') . "$sAfterItem\n";
                }
            }
        }
        return( $li );
    }

    /**
     * Create a divider item for the navigation group
     *
     * @param array $args - function arguments
     * @return string html output
     */
    function mk_divider_item($args) {

        extract($args);

        $opt = intval($nav_item[OPT],10);
        $nme = $nav_item[NME];

        $nameAsClass = strtolower($nme);
        $nameAsClass = sanitize_title_with_dashes($nameAsClass) . '_cls ';
        $navt_item_class .= (!isBlank($navt_item_class) ? " $nameAsClass" : '');

        $item_class = NAVT_FE::set_item_class($style_flags, $sBeforeItem, $navt_item_class, $user_item_class, $wp_item_class);
        $anchor_class .= ($is_current_page ? ' current_item' : '');
        $anchor_class = NAVT_FE::set_item_class($style_flags, 'a', $anchor_class, $usr_defined_class, '', 1);

        $li  = _indentt($in) . "<$sBeforeItem";

        if( $opt & PLAIN_TEXT_OPTION ) {
            $navt_item_class .= (!isBlank($navt_item_class) ? " " . TAB_SUBHEAD : TAB_SUBHEAD);
            $item_class = NAVT_FE::set_item_class($style_flags, $sBeforeItem, $navt_item_class, $user_item_class, $wp_item_class);
            $div_class = NAVT_FE::set_item_class($style_flags, 'div', 'listdiv');
            $li .= (!isBlank($item_class) ? " $item_class" : '' ) . "><div" . (!isBlank($div_class) ? " $div_class" : '') ."><h3>$nme</h3></div>";
        }

        elseif( $opt & HRULE_OPTION ) {
            $navt_item_class .= (!isBlank($navt_item_class) ? " " . TAB_HRULE : TAB_HRULE);
            $item_class = NAVT_FE::set_item_class($style_flags, $sBeforeItem, $navt_item_class, $user_item_class, $wp_item_class);
            $li .= (!isBlank($item_class) ? " $item_class" : '' ) . "><hr />";
        }

        else {
            $navt_item_class .= (!isBlank($navt_item_class) ? " " . TAB_EMPTY : TAB_EMPTY);
            $item_class = NAVT_FE::set_item_class($style_flags, $sBeforeItem, $navt_item_class, $user_item_class, $wp_item_class);
            $li .= (!isBlank($item_class) ? " $item_class" : '' ) . ">&nbsp;";
        }

        $li .= $sAfterItem."\n";

        return( $li );
    }

    /**
     * extracts the user's html code block
     *
     * @param array $args
     */
    function mk_code_item($args) {
        extract($args);
        $class = '';
        if( !isBlank($usr_defined_class) ) {
            $class = ' ' . NAVT_FE::set_item_class(0, '', '', $usr_defined_class, '', 1);
        }

        /** search the codeblock for embedded navigation **/
        do_action('dbnavt', NAVT_GEN, sprintf("%s::%s %s\n", __CLASS__, __FUNCTION__, $nav_item[EX2]));
        $cb = NAVT_FE::the_content_wpcb($nav_item[EX2]);
        $codeblock = _indentt($in) . "<$sBeforeItem" . $class . ">" . $cb . $sAfterItem."\n";
        return($codeblock);
    }

    function mk_code_wrapper_item($args) {
        extract($args);
        $codeblock = _indentt($in) . $nav_item[EX2] . "\n";
        return($codeblock);
    }

    /**
     * Creates a graphic link from an item
     * (styled and unstyled)
     *
     * @param array $args
     * @return string (HTML)
     * @since 95.42
     */
    function mk_graphic_link($args) {
        extract($args);

        $anchor_class .= ($is_current_page ? ' current_item' : '');
        $anchor_class = NAVT_FE::set_item_class($style_flags, 'a', $anchor_class);
        $item_class = NAVT_FE::set_item_class($style_flags, $sBeforeItem, $navt_item_class, $user_item_class, $wp_item_class);
        $img_class = NAVT_FE::set_item_class($style_flags, 'img', $img_class . ' ' . $usr_defined_class);
        $div_class = NAVT_FE::set_item_class($style_flags, 'div', 'navt_gl');

        $li  = _indentt($in) . "<$sBeforeItem" . (!isBlank($item_class) ? " $item_class" : '' ) .
        "><div" . (!isBlank($div_class) ? " $div_class":'') . "><a href='$link_url' title='" . attribute_escape($tooltip) . "'" .
        (!isBlank($reltags) ? " $reltags":'') . (!isBlank($anchor_class) ? " $anchor_class":'') .
        "><img src='$img_src' alt='". attribute_escape($nme) ."'" . (!isBlank($img_class) ? " $img_class":'') . "/>" .
        "<span style='margin-left:-5000px;'>$alt_text</span></a></div>$sAfterItem\n";

        return($li);
    }

    /**
     * Create text with side graphic link
     *
     * @param array $args
     * @return string (HTML)
     * @since 95.42
     */
    function mk_text_with_side_graphic($args) {
        extract($args);

        $anchor_class .= ($is_current_page ? ' current_item' : '');
        $anchor_class = NAVT_FE::set_item_class($style_flags, 'a', $anchor_class);
        $item_class = NAVT_FE::set_item_class($style_flags, $sBeforeItem, $navt_item_class, $user_item_class, $wp_item_class);
        $div_class = NAVT_FE::set_item_class($style_flags, 'div', 'navt_twsg ' . $usr_defined_class );

        $li  = _indentt($in) . "<$sBeforeItem" . (!isBlank($item_class) ? " $item_class": '' ) .
        "><div" . (!isBlank($div_class) ? " $div_class" : '') . ">&nbsp;</div>" .
        "<a href='$link_url' title='" . attribute_escape($tooltip) . "'" . (!isBlank($reltags) ? " $reltags" : '') . ">$alt_text" .
        (!isBlank($post_count) ? " $post_count" : '') . "</a>$sAfterItem\n";

        return($li);
    }

    /**
     * Create text over graphic link
     *
     * @param array $args
     * @return string (HTML)
     * @since 95.42
     */
    function mk_text_over_graphic($args) {
        extract($args);

        $anchor_class .= ($is_current_page ? ' current_item' : '');
        $anchor_class = NAVT_FE::set_item_class($style_flags, 'a', $anchor_class);
        $item_class = NAVT_FE::set_item_class($style_flags, $sBeforeItem, $navt_item_class, $user_item_class, $wp_item_class);
        $div_class = NAVT_FE::set_item_class($style_flags, 'div', 'navt_tog '.$usr_defined_class);

        $li  = _indentt($in) . "<$sBeforeItem" . (!isBlank($item_class) ? " $item_class": '' ) .
        "><div" . (!isBlank($div_class) ? " $div_class" : '') . "><a href='$link_url' title='" . attribute_escape($tooltip) . "'" .
        (!isBlank($reltags) ? " $reltags" : '') . (!isBlank($anchor_class) ? " $anchor_class": '') . ">$alt_text" .
        (!isBlank($post_count) ? " $post_count" : '') . "</a></div>$sAfterItem\n";

        return($li);
    }

    /**
     * Builds a dropdown option of a navigation item
     *
     * @param array $nav_item
     * @param integer $in
     * @return returns a HTML formatted <option> </option> string
     */
    function mk_html_select($nav_item, $in, $style_flags) {

        //do_action('dbnavt', NAVT_MAP, sprintf("%s::%s \n", __CLASS__, __FUNCTION__), $nav_item);

        global $wp_query;
        $dd_option = '';
        $qo = $wp_query->get_queried_object();
        $location = (!empty($qo->cat_ID) ? (TYPE_CAT . $qo->cat_ID) : (!empty($qo->ID) ? (TYPE_PAGE . $qo->ID) : (TYPE_LINK . HOMEIDN)));

        $can_show = 0;
        $idn = $nav_item[IDN];
        $opt = intval($nav_item[OPT],10);
        $typ = $nav_item[TYP];
        $ttl = $nav_item[TTL];
        $nme = $nav_item[NME];

        $is_private = ($opt & ISPRIVATE) ? 1: 0;
        $is_draft = ($opt & ISDRAFTPAGE) ? 1: 0;

        if( $is_private ) {
            $can_show = is_user_logged_in() ? 1: 0;
        }
        else {
            $can_show = ($is_draft && $typ == TYPE_PAGE) ? 0: 1;
        }

        if( $can_show) {

            $is_selected = (($location == $typ.$idn) ? ' selected="selected"': '');

            switch( $typ ) {

                case TYPE_CAT: {
                    $cat = get_category($idn);
                    $num_posts = $cat->category_count;
                    $can_show = ($opt & SHOW_IF_EMPTY || $num_posts > 0) ? 1: 0;
                    if( $can_show ) {
                        $post_count = ( $opt & APPEND_POST_COUNT ) ? " ($num_posts)" : '';
                        $dd_option = _indentt($in) . "<option" . (!isBlank($is_selected) ? " $is_selected" : '') .
                        " value='" . get_category_link($idn) ."'>$nme" . (!isBlank($post_count) ? " $post_count" : '') .
                        "</option>\n";
                    }
                    break;
                }

                case TYPE_PAGE: {
                    $dd_option = _indentt($in) . "<option" . (!isBlank($is_selected) ? " $is_selected" : '') .
                    " value='" . get_permalink($idn) ."'>$nme</option>\n";
                    break;
                }

                case TYPE_LINK: {
                    switch( $idn ) {
                        case HOMEIDN: {
                            $dd_option = _indentt($in) . "<option" . (!isBlank($is_selected) ? " $is_selected" : '') .
                            " value='" . get_option('home') ."'>$nme</option>\n";
                            break;
                        }
                        case LOGINIDN: {
                            $link_text = __('Site Admin', 'navt_domain');
                            $wp_url = get_option('siteurl'); // wordpress location
                            $root = get_option('home'); // root location
                            $link_url = $wp_url . '/wp-admin/'; // admin directory

                            if( !is_user_logged_in() ) {
                                $link_text = $nme;
                                $link_url = $wp_url . '/wp-login.php';
                                $filter = 'loginout';

                                if( get_option('users_can_register') ) {
                                    $link_url = $wp_url . '/wp-register.php';
                                    $filter = 'register';
                                }
                            }
                            $href = "<a href='$link_url' title='".attribute_escape($link_text)."'>$link_text</a>";
                            if(isset($filter)) {
                                $href = apply_filters($filter, $href);
                            }

                            $dd_option = _indentt($in) . "<option" . (!isBlank($is_selected) ? " $is_selected" : '') .
                            " value='$link_url'>$link_text</option>\n";
                            break;
                        }
                        default: {
                            break;
                        }
                    }
                    break;
                }

                case TYPE_SEP: {
                    $div_class = NAVT_FE::set_item_class($style_flags, 'div', 'listdiv');

                    if( $opt & PLAIN_TEXT_OPTION ) {
                        $dd_option = _indentt($in-2) . "<div" . (!isBlank($div_class) ? " $div_class" : '') . ">" .
                        "<h2>$nme</h2></div>\n";
                    }
                    elseif( $opt & HRULE_OPTION ) {
                        $dd_option = _indentt($in-2) . "<div" . (!isBlank($div_class) ? " $div_class" : '') . ">" .
                        "<h2><hr /></h2></div>\n";
                    }
                    else {
                        $dd_option = _indentt($in-2) . "<div" . (!isBlank($div_class) ? " $div_class" : '') . ">" .
                        "<h2>&nbsp;</h2></div>\n";
                    }
                    break;
                }

                case TYPE_ELINK: {
                    $dd_option = _indentt($in) . "<option" . (!isBlank($is_selected) ? " $is_selected" : '') .
                    " value='" . attribute_escape($ttl) ."' >$nme</option>\n";
                    break;
                }
            }// end switch item type
        }

        return($dd_option);
    }

    /**
     * Creates a form for use with dropdown menus
     *
     * @param string $sGroupName
     * @param integer $num
     * @param integer $in
     * @return returns a HTML <form> statement
     */
    function mk_form($sGroupName, $num, $in, $style_flags, $select_size) {

        //do_action('dbnavt', NAVT_MAP, sprintf("%s::%s \n", __CLASS__, __FUNCTION__));
        $sGroupName = strtolower($sGroupName);
        $c = NAVT_FE::set_item_class($style_flags, 'select', 'dropdown_menu');

        $form_id = $sGroupName . $num;
        $html = _indentt($in) . "<form id='$form_id" . "_form' name='$form_id" . "_form' action=''>\n" .
        _indentt($in+1) . "<select size='$select_size' id='$form_id' name='$form_id'" . (!isBlank($c) ? " $c" : '') .
        " onchange='location.href=this.form.$form_id.options[this.form.$form_id.selectedIndex].value;return false;'>\n";
        return($html);
    }

    /**
     * Ends a select and form
     *
     * @param integer $in
     * @return returns a HTML </form> statement
     */
    function end_form($in) {
        //do_action('dbnavt', NAVT_MAP, sprintf("%s::%s \n", __CLASS__, __FUNCTION__));
        $html  = _indentt($in+1) . "</select>\n";
        $html .= _indentt($in) . "</form>\n";
        return($html);
    }

    /**
     * Returns a number representing the hierarchy of a page
     *
     * @param integer $id
     * @param integer $level - hierarchy value (0 = top level)
     * @return integer - hierarchy level
     */
    function get_page_level($id, $level=0) {

        $this_level = $level;
        $pg = get_page($id);
        if( $pg->post_parent != 0 ) {
            $this_level++;
            $this_level = NAVT_FE::get_page_level($pg->post_parent, $this_level);
        }
        return($this_level);
    }

    /**
     * Returns a number representing the hierarchy of a category
     *
     * @param integer $id - category id
     * @param integer $level - hierarchy value (0 = top level)
     * @return integer - hierarchy level
     */
    function get_category_level($id, $level=0) {

        $this_level = $level;
        $cat = get_category($id);
        if( $cat->category_parent != 0) {
            $this_level++;
            $this_level = NAVT_FE::get_category_level($cat->category_parent, $this_level);
        }
        return($this_level);
    }

    /**
     * Routine that does some rudimentatry checking of a user avatar
     *
     * @param string  - avatar url
     * @return boolean - 1 = url meets requirement, 0 - bad url or bad image file
     */
    function check_avatar($avatar_url) {

        // Parse the given url
        $arr = parse_url($avatar_url);
        $hasCorrectMime = 0;

        if( !empty($arr['path']) ) {
            $pi = pathinfo($arr['path']);
            if( !empty($pi['basename']) && !empty($pi['extension'])) {
                $ft = wp_check_filetype(trim($pi['basename']));
                if(!empty($ft['type'])) {
                    $hasCorrectMime = (strstr($ft['type'], 'image') != false) ? 1: 0;
                }
            }
        }
        return($hasCorrectMime);
    }

    /**
     * Returns a css class related to a user's wp role
     *
     * @param integer $user_id
     * @return string - css class
     */
    function get_userlevel_class($user_id) {
        global $wpdb;
        $level = intval(get_usermeta($user_id, $wpdb->prefix . 'user_level'), 10);
        if( $level > 8 ) $class = 'administrator';
        elseif( $level > 4 && $level < 8 ) $class = 'editor';
        elseif( $level > 1 && $level < 5 ) $class = 'author';
        elseif( $level == 1 ) 'contributor';
        else $class = 'subscriber';
        return($class);
    }

    /**
     * Returns group 'style' information
     *
     * @param string $name - name of group
     * @param array $cfg - the group configuration array
     * @return integer $css_rules
     */
    function set_style_flags($name, $cfg) {
        return( intval($cfg[$name]['options'],10) & 0xffff );
    }

    /**
     * Returns group 'HTML select' setting
     *
     * @param string $name - name of group
     * @param array $cfg - the group configuration array
     * @return boolean 1 if group is built using HTML select, 0 (zero) otherwise
     */
    function is_html_select($name, $cfg) {
        $options = (intval($cfg[$name]['options'],10) & 0xffff);
        return(($options & HAS_DD_OPTION) ? 1: 0);
    }

    /**
     * Returns group 'privacy' setting
     *
     * @param string $name - name of group
     * @param array $cfg - the group configuration array
     * @return boolean 1 if group is private, 0 (zero) otherwise
     */
    function is_private_group($name, $cfg) {
        $options = (intval($cfg[$name]['options'],10) & 0xffff);
        //do_action('dbnavt', NAVT_GEN, sprintf("%s::%s group private: %s\n", __CLASS__, __FUNCTION__, ($options & ISPRIVATE)));
        return(($options & ISPRIVATE) ? 1: 0);
    }

    /**
     * Returns 'page folding' setting
     *
     * @param string $name - name of group
     * @param array $cfg - the group configuration array
     * @return boolean 1 if group uses page folding, 0 (zero) otherwise
     */
    function use_pagefolding($name, $cfg) {
        $options = (intval($cfg[$name]['options'],10) & 0xffff);
        return(($options & PAGE_FOLDING) ? 1: 0);
    }

    /**
     * Returns show debug in comments setting
     *
     * @param string $name - name of group
     * @param array $cfg - the group configuration array
     * @return boolean 1 if show debug is true, 0 (zero) otherwise
     */
    function show_debug_in_source($name, $cfg) {
        $options = (intval($cfg[$name]['options'],10) & 0xffff);
        return(($options & SHOW_DEBUG) ? 1: 0);
    }

    /**
     * Returns 'page return' setting
     *
     * @param string $name - name of group
     * @param array $cfg - the group configuration array
     * @return boolean 1 if set to true, 0 (zero) otherwise
     */
    function get_page_return($name, $cfg) {
        $options = (intval($cfg[$name]['options'],10) & 0xffff);
        return(($options & ADD_PAGE_RETURN) ? 1: 0);
    }

    /**
     * Returns 'breadcrumb' setting
     *
     * @param string $name - name of group
     * @param array $cfg - the group configuration array
     * @return boolean 1 if set to true, 0 (zero) otherwise
     */
    function use_breadcrumbs($name, $cfg) {
        $options = (intval($cfg[$name]['options'],10) & 0xffff);
        return(($options & ADD_BREADCRUMBS) ? 1: 0);
    }

    /**
     * Returns the select box size for select menus
     *
     * @param string $name - name of group
     * @param array $cfg - the group configuration array
     * @return integer - selection size
     */
    function get_select_size($name, $cfg) {
        return( intval( $cfg[$name]['select_size'], 10 ) & 0xffff );
    }

    /**
     * Returns the user defined UL class
     *
     * @param string $name - name of group
     * @param array $cfg - the group configuration array
     * @return string - user class
     */
    function get_user_ul_class($name, $cfg) {
        return( $cfg[$name]['css']['ul'] );
    }

    /**
     * Returns the user defined UL class
     *
     * @param string $name - name of group
     * @param array $cfg - the group configuration array
     * @return string - user class
     */
    function get_user_li_class($name, $cfg) {
        return( $cfg[$name]['css']['li'] );
    }

    /**
     * Examines the content of a page or post
     *
     * WordPress callback function (@see Word Press add_action('the_content')
     *
     * @param string $content - post or page content
     * @since 95.38
     */
    function the_content_wpcb($content) {

        do_action('dbnavt', NAVT_GEN, sprintf("%s::%s %s\n", __CLASS__, __FUNCTION__, $content));

        // syntax: [navt id=groupname, title=heading title] ]
        // , title= is optional

        $pattern = '/\[\s*navt\s*id\s*=\s*[a-z0-9]+\s*(\]|\s*,\s*title\s*=.+\])/im';
        $matched = array();

        if(! preg_match_all($pattern, $content, $matched, PREG_SET_ORDER)) {
            return $content;
        }
        else {
            $replacement = array();
            foreach( $matched as $idx => $match ) {
                foreach( $match as $nidx => $value ) {
                    if( $nidx == 0 ) {
                        // save the content to be replaced
                        $replacement[$idx]['content_string'] = $value;
                        $m = array();

                        $pattern = '/\s*id\s*=\s*[a-z0-9]+(\s*|,|\])/i'; // id=
                        if( preg_match($pattern, $value, $m) ) {
                            // isolate the group name
                            $str = preg_replace('/\s+/', '',  $m[0]);     // strip spaces
                            $group = preg_replace('/id=/' , '', $str); // remove 'id='
                        }
                    }
                    else {
                        // *may* contain a title - may not
                        $title = '';
                        $pattern = '/,\s*title\s*=.+/i';
                        if( preg_match($pattern, $value, $m) ) {
                            // isolate the title
                            $str = preg_replace('/,\s*title\s*=\s*/i', '', $m[0]); // remove 'title='
                            $str = preg_replace('/\]/i', '', $str); // remove the last ']'
                            $title = trim($str); // trim it on both ends
                        }
                    }
                }
                // save these
                $replacement[$idx]['id'] = $group;
                $replacement[$idx]['title'] = $title;
            }

            $new_content = $content;
            foreach( $replacement as $idx => $arr ) {
                // get the navigation group and replace the post/page content
                $text = navt_getlist($arr['id'], 0, $arr['title']);
                $text = "<div class='navt_content_list'>$text</div>";
                $new_content = str_replace($arr['content_string'], $text, $new_content);
            }
            $content = $new_content;
        }
        return($content);
    }

    /**
     * Determines whether or not the group can be displayed on the current web page
     *
     * @param string $name - name of group
     * @param array $cfg - the group configuration array
     * @return boolean 1 group can be displayed, 0 (zero) otherwise
     */
    function can_display($name, $cfg) {
        global $post, $wp_query;
        $qo = $wp_query->get_queried_object();
        $rc = 0; // do not show by default
        do_action('dbnavt', NAVT_GEN, sprintf("%s::%s\n", __CLASS__, __FUNCTION__));

        $show_on = intval($cfg[$name]['display']['show_on'], 10);
        $page_ids = $cfg[$name]['display']['pages']['ids'];
        $post_ids = $cfg[$name]['display']['posts']['ids'];
        $cat_ids  = $cfg[$name]['display']['cats']['ids'];
        $on_selected_pages = $cfg[$name]['display']['pages']['on_selected'];
        $on_selected_posts = $cfg[$name]['display']['posts']['on_selected'];
        $is_archived_page = 0;

        /*
        printf("<!--\n");
        printf("is_home: %s\n", is_home());
        printf("is_archive: %s\n", is_archive());
        printf("is_page: %s\n", is_page());
        printf("is_single: %s\n", is_single());
        printf("is_search: %s\n", is_search());
        printf("is_singular: %s\n", is_singular());
        printf("is_404: %s\n", is_404());
        printf("show on: 0x%04x, on selected pages: %s, on selected posts: %s\n", $show_on, $on_selected_pages, $on_selected_posts);
        printf("post/page id: %s\n", $post->ID);
        printf("-->\n");
        */

        do_action('dbnavt', NAVT_GEN, sprintf("%s::%s\n is_home:%s\n,is_archive:%s\n,is_search:%s\n,is_404:%s\n,is_page:%s\n,is_single:%s\nis_singular:%s\n",
        __CLASS__, __FUNCTION__, is_home(), is_archive(), is_search(), is_404(), is_page(), is_single(), is_singular()));

        do_action('dbnavt', NAVT_GEN, sprintf("%s::%s post id:%s\n", __CLASS__, __FUNCTION__, $post->ID));

        if(
        ( is_home()    && ($show_on & SHOW_ON_HOME) )     ||
        ( is_archive() && ($show_on & SHOW_ON_ARCHIVES) ) ||
        ( is_search()  && ($show_on & SHOW_ON_SEARCH) )   ||
        ( is_404()     && ($show_on & SHOW_ON_ERROR) )
        ) { $rc = 1; }

        $is_archived_page = ( is_archive() ? 1: 0);

        if( is_page() ) {

            if( $show_on & SET_ON_PAGES ) {
                if( $on_selected_pages == 'hide' ) {
                    // hide on selected
                    if( !isset($page_ids[$post->ID]) ) {
                        do_action('dbnavt', NAVT_GEN, sprintf("%s::%s can display %s on post: %s\n", __CLASS__, __FUNCTION__, $name, $post->ID));
                        $rc = 1;
                    }
                }
                else {
                    // show on selected
                    if(isset($page_ids[$post->ID])) {
                        do_action('dbnavt', NAVT_GEN, sprintf("%s::%s can display %s on post: %s\n", __CLASS__, __FUNCTION__, $name, $post->ID));
                        $rc = 1;
                    }
                }
            }
            else {
                // show on all pages
                $rc = 1;
                do_action('dbnavt', NAVT_GEN, sprintf("%s::%s can display %s all pages\n", __CLASS__, __FUNCTION__, $name));
            }
        }
        else if( is_single() || $is_archived_page ) {

            if( $show_on & SET_ON_POSTS ) {
                if( $on_selected_posts == 'hide' ) {
                    // hide on selected
                    if( !isset($post_ids[$post->ID]) ) {
                        do_action('dbnavt', NAVT_GEN, sprintf("%s::%s can display %s on page: %s\n", __CLASS__, __FUNCTION__, $name, $post->ID));
                        $rc = 1;
                    }
                }
                else {
                    // show on selected
                    if(isset($post_ids[$post->ID])) {
                        do_action('dbnavt', NAVT_GEN, sprintf("%s::%s can display on post: %s\n", __CLASS__, __FUNCTION__, $post->ID));
                        $rc = 1;
                    }
                }
            }
            else {
                // show on all posts
                $rc = 1;
                do_action('dbnavt', NAVT_GEN, sprintf("%s::%s can display all posts\n", __CLASS__, __FUNCTION__));
            }
        }

        do_action('dbnavt', NAVT_GEN, sprintf("%s::%s returning: %s\n", __CLASS__, __FUNCTION__, $rc));
        return($rc);
    }

    /**
     * Determines what categories to exclude
     *
     * @param array - current menu map
     * @return array -  returns an array of categories to exclude
	  */
    function get_exclusions( $map_array ) {
        $exc_array = array();
        //do_action('dbnavt', NAVT_WPHOOKS, sprintf("%s::%s\n", __CLASS__, __FUNCTION__));

        if( is_array($map_array) ) {

            foreach($map_array as $itm) {
                if( $itm[GRP] == ID_DEFAULT_GROUP ) {
                    continue;
                }
                // category ?
                if( $itm[TYP] == TYPE_CAT ) {

                    $opts = (intval($itm[OPT],10) & 0xffff);
                    $id = $itm[IDN];

                    $user_is_logged_in = is_user_logged_in();
                    $is_private = ($opts & ISPRIVATE);
                    $show_in_list = ($opts & SHOW_IN_LIST);

                    $exclude_this = 1; // normally excluded
                    $exclude_this = ($show_in_list) ? 0: $exclude_this;
                    $exclude_this = ($is_private && !$user_is_logged_in ) ? 1: $exclude_this;

                    if( !$exclude_this ) {
                        $category = get_category($id);
                        $parent = $category->category_parent;
                        if( $parent != 0 ) {
                            $exclude_this = (in_array($parent, $exc_array)) ? 1: 0;
                        }
                    }

                    if( ($exclude_this) && (!in_array($id, $exc_array)) ) {
                        $exc_array[] = $id;
                    }
                    else {
                        //do_action('dbnavt', NAVT_WPHOOKS, sprintf("\tallowing category '%s'\n: ", $itm[TTL]));
                    }
                }
            }// end for
        }// end if

        //do_action('dbnavt', NAVT_WPHOOKS, sprintf("%s::%s exclusion array\n", __CLASS__, __FUNCTION__), $exc_array);
        return( $exc_array );

    }// end function


    /**
     * sets a css class (or classes) for an item based on the selected
     * options for the group.
     *
     * @param integer $style_flags - rules
     * @param string $navt_style - default NAVT classes for the item
     * @return string - the classes to be applied
     */
    function set_item_class($style_flags, $tag, $navt_class='', $user_class='', $wp_class='', $apply_user_class = 0) {

        do_action('dbnavt', NAVT_GEN, sprintf("%s::%s style_flags: 0x%04x, tag: <%s>,
        navt classes: %s, user classes: %s, wp classes: %s apply_user_class: %s\n", __CLASS__, __FUNCTION__,
        $style_flags, $tag, $navt_class, $user_class, $wp_class, $apply_user_class));

        $rc = $css = '';
        $style_flags = (intval($style_flags,10) & 0xffffff);

        if( $style_flags & USE_WP_DEFAULTS ) {
            $css .= (!isBlank($wp_class) ? $wp_class: '');
        }

        elseif( $style_flags & USE_NAVT_DEFAULTS ) {
            $css .= (!isBlank($navt_class) ? $navt_class: '');
        }

        elseif( $style_flags & HAS_NOSTYLE ) {
        }

        if( $apply_user_class && !isBlank($user_class) ) {
            $css .= (!isBlank($css) ? ' ' . $user_class: $user_class);
        }

        $css = trim($css);

        // user classes are added to anything that occurred above
        if( $style_flags & USE_USER_CLASSES ) {
            $css .= (!isBlank($user_class) ? (!isBlank($css) ? " $user_class" : $user_class) : '');
        }

        if( !isBlank($css) ) {
            $rc = sprintf("class='%s'", $css);
        }

        return($rc);
    }

    /**
     * Attach the plugin to the user's theme
     *
     */
    function wp_head_wpcb() {

        // get groups
        do_action('dbnavt', NAVT_GEN, sprintf("%s::%s - start\n", __CLASS__, __FUNCTION__));

        $gcfg = NAVT::get_option(GCONFIG);
        $selector = array();
        $_POST['navt-noanounc'] = 1; // turn off NAVT announcment comment
        do_action('dbnavt', NAVT_GEN, sprintf("%s::%s gconfig\n", __CLASS__, __FUNCTION__), $gcfg);

        if( is_array($gcfg) && count($gcfg) > 0 ) {
            //do_action('dbnavt', NAVT_GEN, sprintf("%s::%s gconfig\n", __CLASS__, __FUNCTION__), $gcfg);

            foreach( $gcfg as $user_nav_group => $group_data ) {
                do_action('dbnavt', NAVT_GEN, sprintf("%s::%s group: %s\n", __CLASS__, __FUNCTION__,
                $user_nav_group), $group_data);

                $options = intval($gcfg[$user_nav_group]['options'], 10);

                if( $options & HAS_XPATH && !isBlank($gcfg[$user_nav_group]['selector']['xpath']) ) {
                    $html = navt_getlist($user_nav_group, false);

                    if( !isBlank($html) ) {
                        $html = str_replace("\r", "", $html);
                        $html = str_replace("\n", "", $html);
                        $html = preg_replace('/>\s\s+</', '><', $html); // squeeze whitespace
                        $html = preg_replace('/> </', '><', $html); // squeeze more whitespace
                        $html = preg_replace('|/|', '\\/', $html);

                        $html = trim($html);
                        $selector[$user_nav_group] = array(
                        'xpath' => $gcfg[$user_nav_group]['selector']['xpath'],
                        'option' => intval($gcfg[$user_nav_group]['selector']['option']),
                        'html' => $html);
                    }
                }
            }

            unset($_POST['navt-noanounc']);
            if( count($selector) > 0 ) {
                global $jQueryOption;
                do_action('dbnavt', NAVT_GEN, sprintf("%s::%s selectors:\n", __CLASS__, __FUNCTION__), $selector);
                $printed = wp_print_scripts('jquery');

                if( $printed ) {
                    printf("\n<!-- %s v%s -->\n", NAVT_SCRIPTNAME, NAVT_SCRIPTVERS);
            ?>
<script type="text/javascript">
//<![CDATA[
var navtpath = '<?php navt_output_url();?>';
jQuery(document).ready(function() {<?php
echo "\n\tvar html;\n";
foreach( $selector as $key => $ar ) {
    if( !isBlank($ar['xpath']) ) {
        echo "\t"; printf('html = "%s";', $ar['html']); echo "\n\t";
        printf('jQuery("%s").%s(html);', $ar['xpath'], $jQueryOption[$ar['option']]); echo "\n";
    }
}?>
});
//]]>
</script>
<!-- end -->
            <?php
                }// if printed
            }// end if count($selector) > 0
        }// end if count
        else {
            do_action('dbnavt', NAVT_GEN, sprintf("%s::%s gcfg empty?\n", __CLASS__, __FUNCTION__), $gcfg);
        }
        do_action('dbnavt', NAVT_GEN, sprintf("%s::%s - end\n", __CLASS__, __FUNCTION__));
    }// end function

}// end NAVT_FE class