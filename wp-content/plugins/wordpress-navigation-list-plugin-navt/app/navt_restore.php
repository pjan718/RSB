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
 * @author Greg A. Bellucci <greg[AT]gbellucci[DOT]us
 * @copyright Copyright &copy; 2006-2008 Greg A. Bellucci
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * -----------------------------------------------------------------------------
 * $Id: navt_restore.php 81144 2008-12-19 15:39:14Z gbellucci $:
 * $Date: 2008-12-19 15:39:14 +0000 (Fri, 19 Dec 2008) $:
 * $Revision: 81144 $:
 * -----------------------------------------------------------------------------
 *
 * PHP Progressbar credit:
 * Juha Suni <juha.suni@sparecom.fi>
 */
global $backup_version;
global $r_gcfg;
global $r_icfg;
global $i__cfg;
global $g__cfg;
global $backup_version;

// error messages
$err = array(
__('no error [0]', 'navt_domain'),
__('Restore file is too large. [1]', 'navt_domain'),
__('Restore file is too large. [2]', 'nat_domain'),
__('Restore file was only partially uploaded.', 'navt_domain'),
__('Please select a restore file.'),
__('Unknown Error. [5]', 'navt_domain'),
__('A temp directory is missing on your server.', 'navt_domain'),
__('Cannot write to temporary directory on server.', 'navt_domain'),
__('File upload failed. Restore could not be performed.', 'navt_domain')
);

// restore result messages
$res = array(
__('Restore completed', 'navt_domain'),
__('Incorrect file type. File must be an XML file.', 'navt_domain'),
__('Restore file is empty.', 'navt_domain'),
__('Restore failed, file is corrupt.', 'navt_domain'),
__('Restore failed, file is incomplete.', 'navt_domain')
);

if( isset( $_REQUEST['navt_action'] ) && $_REQUEST['navt_action'] == 'restore' ) {
    $error = $_FILES['restore_file']['error'];
    if( !empty($error) && $error != 0 ) {
        $_GET['message'] = $err[intval($error)];
    }
    else {
        $restore_how      = ((isset($_POST['restore_how']) ) ? $_POST['restore_how'] : RESTORE_IGNORE);
        $match_title      = ((isset($_POST['match']['title']) ) ? 1: 0 );
        $match_alias      = ((isset($_POST['match']['alias']) ) ? 1: 0 );
        $use_backup_alias = ((isset($_POST['match']['use_backup_alias'])) ? 1: 0);
        $publish_pages    = ((isset($_POST['match']['publish_pages'])) ? 1: 0);
        $discard_dups     = ((isset($_POST['discard_duplicates'])) ? 1: 0);
        $doin_the_deed    = 1;
    }
}// end if
else {
    $doin_the_deed = 0;
}

if( $doin_the_deed ) {

    // read/parse the restore file
    $r_gcfg = $r_icfg = array();

    $retcode = get_navt_backup_version();

    if( $retcode == 0 ) {
        if( $backup_version > 9600 ) {
            $retcode = navt_restore_96();
        }
        else {
            $retcode = navt_restore_95();
        }
    }

    if( $retcode != 0 ) {
        // encountered an error of some kind while parsing the file
        $doin_the_deed = 0;
        $_GET['message'] = $res[$retcode];
    }

    else {
?>

<div class="wrap">

  <h2><?php _e('NAVT Restore', 'navt_domain');?></h2>
  <p><?php _e('Restoring', 'navt_domain');?> <?php echo $_FILES['restore_file']['name'];?></p>
  <div class="progbar">
    <div class="bitem">
        <?php
        // start the restore
        ob_end_flush();
        flush();
        $_POST['restore_active'] = 1;

        $pp = $pl = $iter = 0;
        $member_count = $inc = 0;
        if( count($r_icfg) ) {
            foreach( $r_icfg as $group => $members ) {
                $member_count += count($members);
            }
            $inc = 100/$member_count;
        }

        do_action('dbnavt', NAVT_RESTORE, sprintf("backup ver: %s\n", $backup_version));
        $i__cfg = NAVT::get_option(ICONFIG);
        $g__cfg = NAVT::get_option(GCONFIG);
        $update_items = $update_groups = $restore_complete = 0;
        $assets = NAVT::build_assets();

        foreach($r_icfg as $group => $members) {
            foreach($members as $id => $data) {
                $new_item = navt_restore_item($data, $restore_how, $match_title,
                $use_backup_alias, $publish_pages, $i__cfg, $assets, $discard_dups);
                //do_action('dbnavt', NAVT_RESTORE, sprintf("item returned:\n"), $new_item);

                if( count($new_item) > 0 ) {
                    /* add this group if necessary */
                    $group = $g__cfg[$new_item[GRP]];
                    if( empty($group) ) {
                        $g__cfg[$new_item[GRP]] = $r_gcfg[$new_item[GRP]];
                        //do_action('dbnavt', NAVT_RESTORE, sprintf("new group: %s \n",
                        //$new_item[GRP]), $gcfg[$new_item[GRP]]);
                        $update_groups++;
                    }
                    $id = NAVT::make_id($new_item, $member_count++);
                    $i__cfg[$new_item[GRP]][$id] = $new_item;
                    $update_items++;
                    //do_action('dbnavt', NAVT_RESTORE, sprintf("added item\n"), $icfg[$new_item[GRP]][$id]);
                }

                navt_update_progress(&$iter, $inc, &$pl);
                flush();
            }
        }
        $restore_complete = 1;
    }
    ?>
      </div><!-- bitem -->
    </div><!-- progbar -->

    <?php

    if( $restore_complete ) {
        unset($_REQUEST['navt_action']);
        unset($_FILES['restore_file']);

        echo "<p class='info'>";
        if( $update_groups ) {
            NAVT::update_option(GCONFIG, $g__cfg);
            do_action('dbnavt', NAVT_RESTORE, sprintf("restored group config\n"), $g__cfg);
            _e('New groups:', 'navt_domain'); printf("&nbsp;<span class='digit'>%s</span>&nbsp;&nbsp;", $update_groups);
        }
        if( $update_items ) {
            NAVT::update_option(ICONFIG, $i__cfg);
            do_action('dbnavt', NAVT_RESTORE, sprintf("restored item config\n"), $i__cfg);
            _e('Updated items:', 'navt_domain'); printf("&nbsp;<span class='digit'>%s</span>", $update_items);
        }
        if( !$update_groups && !$update_items ) {
            _e('The configuration was not changed.', 'navt_domain');
        }
        echo "</p><div class='completemsg'><p>&bull;&nbsp;".__('Restore Complete', 'navt_domain')."&nbsp;&bull;</p></div>";
    } ?>
   </div><!-- wrap -->
   <?php
} // end if $doin_the_deed
?>

<?php
if( !isset($doin_the_deed) || !$doin_the_deed ) {

   // Displayed only if we're not doing the actual restore ?>
   <?php if (isset($_GET['message'])) : ?>
   <div id="message" class="updated fade"><p><?php echo $_GET['message']; ?></p></div>
   <?php endif; ?>

<div class="wrap">
  <h2><?php _e('NAVT Restore', 'navt_domain');?></h2>
  <form id="navt_restore" action='' method="post" enctype="multipart/form-data">
    <div class='instruct'><?php _e('NAVT Backup Restoration enables you to reconstruct a set of previously created navigation groups by loading a backup file created by the NAVT plugin. The information stored in the backup is merged with your current set of navigation items. The data merge is controlled by selecting one of the options in Step Two.', 'navt_domain');?>
    </div>

    <h3><?php _e('Duplicate Items in the same Group', 'navt_domain') ?></h3>
    <div class='instruct'><?php _e('If you merge a backup with existing navigation groups it is possible for a single item to appear multiple times within the same group. To avoid duplicating items, delete your current configuration before restoring the backup file or select the option to discard duplicates within the same group in Step Two.', 'navt_domain');?>

      <h3><?php _e('Terminology', 'navt_domain');?></h3>
      <?php _e('A <strong>matched</strong> item is defined on this page as a navigation item that exists in both the backup and within your current configuration. For example, a page or a category that is now in use on your web site that also existed at the time the backup was created. An <strong>unmatched</strong> item is a page, category or other item that exists in the backup but no longer exists on your web site. ', 'navt_domain');?>
    </div>

    <p class="warn center">&bull; <?php _e('Please backup any existing navigation groups before proceeding', 'navt_domain');?> &bull;</p>
    <hr />

    <h3><?php _e('Step One', 'navt_domain');?></h3>
    <p class='instruct'><?php _e('Select the file from your computer that contains the NAVT backup you wish to restore. This must be a backup file in XML format that was created by the NAVT plugin.', 'navt_domain');?></p>
<p class='padleft'><?php _e('File Name', 'navt_domain')?>: <input type="file" id="restore_file" name="restore_file" size="40" maxlength="80" value="" class="button" /></p><br /><hr />

   <h3><?php _e('Step Two', 'navt_domain');?></h3>
   <p class='instruct'><?php _e('Select one of the merge options below.', 'navt_domain');?></p>
   <fieldset>
      <p class='padleft'><input type="radio" name="restore_how" value="<?php echo MERGE_DISCARD_UNMATCHED;?>" checked="checked" /> <?php _e('Ignore unmatched items', 'navt_domain');?><em> (<?php _e('Default setting','navt_domain');?>)</em></p>
      <p class="explain"><?php _e('Matched items are merged with existing navigation groups. Unmatched items are discarded.', 'navt_domain');?></p>
      <p class='padleft'><input type="radio" name="restore_how" value="<?php echo MERGE_CREATE_UNMATCHED;?>" /> <?php _e('Create unmatched items.', 'navt_domain');?></p>
      <p class="explain"><?php _e('Pages, categories or users are automatically created for unmatched items.', 'navt_domain');?><em> <?php _e('(Useful for creating new web sites from a backup)', 'navt_domain');?></em></p>
      <p style='padding: 0 0 0 35px; margin: 0 !important;'><input type="checkbox" name="match[publish_pages]" value="1" /> <?php _e('Publish pages created from restored page items.', 'navt_domain');?><em> (<?php _e('Default is to create a draft', 'navt_domain');?>)</em></p>
      <p class='padleft'><input type="checkbox" name="discard_duplicates" value="1" checked="checked" /> <?php _e('Discard duplicates in the same group','navt_domain');?> <em>(<?php _e('Default is checked', 'navt_domain');?>)</em></p>
   </fieldset><hr />

   <h3><?php _e('Step Three', 'navt_domain');?></h3>
   <p class='instruct'><?php _e('Item matching is primarily determined by the identifier assigned to an item by the database when the item was created. For example, a page item in the backup that has an identifier that is equal to a page identifier in your current set of pages is considered loosely matched. However, you can indicate that stricter matching be used by checking the option below.', 'navt_domain');?></p>
<p class='padleft'><input type="checkbox" name="match[title]" value="1" /> <?php _e('Backup item title must also equal matched item title.', 'navt_domain');?></p>

   <h4><?php _e('Additional Options', 'navt_domain');?></h4>
   <p class='padleft'><input type="checkbox" name="match[use_backup_alias]" value="1" /> <?php _e('Use the alias name in the backup as the preferred alias name', 'navt_domain');?> <em>(<?php _e('Default is current alias', 'navt_domain');?>)</em></p>
   <p class="submit"><input type="submit" id="navt_restore_plugin" name="navt_restore_plugin" value="<?php _e('Begin Restore &raquo;', 'navt_domain');?>" class="button delete" /></p>

   <input type='hidden' name='navt_action' value='restore' />
   </form>
</div>

<?php }
?>