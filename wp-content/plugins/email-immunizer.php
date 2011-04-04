<?php
/*
Plugin Name: Email Immunizer
Plugin URI: http://guff.szub.net/email-immunizer/
Description: Protect email addresses and mailto links on your blog from email harvesters.
Version: R1
Author: Kaf Oseo
Author URI: http://szub.net/

	Copyright (c) 2005, Kaf Oseo (http://szub.net)
	Email Immunizer is released under the GNU General Public License
	http://www.gnu.org/licenses/gpl.txt

	This is a WordPress plugin (http://wordpress.org).
*/

function email_immunizer($text) {
	return preg_replace('%((mailto:)?([\w\d][\w\d$.-]*[\w\d]@[\w\d][\w\d.-]*[\w\d]\.[a-z0-9]{2,5}))%ie', 'antispambot(\'$1\')', $text);
}

/* post and Page filters */
add_filter('the_author_email', 'email_immunizer', 9);
add_filter('the_content', 'email_immunizer', 9);
add_filter('the_excerpt', 'email_immunizer', 9);

/* comment filters */
add_filter('comment_author_email', 'email_immunizer', 9);
add_filter('comment_text', 'email_immunizer', 9);
add_filter('comment_excerpt', 'email_immunizer', 9);

/* rss filters */
add_filter('the_content_rss', 'email_immunizer', 9);
add_filter('the_excerpt_rss', 'email_immunizer', 9);
add_filter('comment_text_rss', 'email_immunizer', 9);
?>