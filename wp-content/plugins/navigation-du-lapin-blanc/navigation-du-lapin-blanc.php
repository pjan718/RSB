<?php
/**
 * @package Bjoerne
 * @subpackage NavigationDuLapinBlanc
 * @version 1.0.3
 */
/*
 Plugin Name: Navigation Du Lapin Blanc
 Plugin URI: http://www.bjoerne.com/navigation-du-lapin-blanc
 Description: This plugin provides integrated navigation for your website. Thus you can use WordPress as a CMS for your website and think in terms of main navigation, sub navigation etc. A navigation item can link to page, a category, directly to the first sub navigation item (if no own content exist for this item), an external url or a sitemap page. There are a lot of helpful methods to realize a website navigation with little effort like printing the navigation on any level (main, sub, sub sub etc.), searching single navigation items and handle them individually, using cross links in the content, providing a sitemap page and so on.
 Version: 1.0.3
 Author: Björn Weinbrenner
 Author URI: http://www.bjoerne.com/

 Contributors:
 Björn Weinbrenner

 Copyright 2009 Björn Weinbrenner
 */

/**
 * Requires classes.php
 */
require_once('classes.php');

/**
 * This method initialised the plugin. After the method completed there exists a few globals which are access later by other plugin methods.
 * @access private
 * @return void
 */
function bjoerne_init() {

	$args = array(
    	'sort_column' => 'menu_order, post_title');
	$pages =& get_pages($args);
	if ((null == $pages) || empty($pages)) {
		return null;
	}
	// create page hierarchie and get metadata
	$page_ids = array();
	$parent_to_pages_map = array();
	foreach ($pages as $page) {
		$page_ids[] = $page->ID;
		$parent_to_pages_map[$page->post_parent][] = $page;
	}
	update_postmeta_cache($page_ids);
	$rootNodes = array();
	foreach ($parent_to_pages_map[0] as $page) {
		$rootNodes[] =& bjoerne_create_navigation_root_node($page, $parent_to_pages_map);
	}
	$GLOBALS['bjoerne_root_nodes'] =& $rootNodes;
	$currentNode =& bjoerne_find_current_node($rootNodes);
	if (null != $currentNode) {
		$pathReverse = array();
		$currentNode->set_selected(true);
		$GLOBALS['bjoerne_current_node'] =& $currentNode;
		$pathReverse[] =& $currentNode;
		$iNode =& $currentNode->get_parent();
		while (null != $iNode) {
			$iNode->set_on_selected_path(true);
			$pathReverse[] =& $iNode;
			$iNode =& $iNode->get_parent();
		}
		$GLOBALS['bjoerne_current_path'] =& array_reverse($pathReverse);
	}
	// register resolvers
	bjoerne_register_name_resolver(new Bjoerne_NameFromMetadataResolver());
	bjoerne_register_default_name_resolver(new Bjoerne_DefaultNameResolver());
	bjoerne_register_url_resolver(new Bjoerne_CategoryUrlResolver());
	bjoerne_register_url_resolver(new Bjoerne_DelegateToFirstChildUrlResolver());
	bjoerne_register_url_resolver(new Bjoerne_ExternalUrlResolver());
	bjoerne_register_default_url_resolver(new Bjoerne_DefaultUrlResolver());
}

/**
 * This method is registered as a content filter and inserts a complete sitemap if the current page is a sitemap page.
 * @access private
 * @param String $content
 * @return String the filtered content
 */
function bjoerne_filter_content($content) {
	$current_node = bjoerne_get_current_node();
	if (null == $current_node) {
		return $content;
	}
	$current_metadata = $current_node->get_metadata();
	if (array_key_exists('bjoerne_page_type', $current_metadata)) {
		$page_types = $current_metadata['bjoerne_page_type'];
		if (in_array('sitemap', $page_types)) {
			if (!bjoerne_str_contains($content, '[bjoerne_sitemap')) {
				return '[bjoerne_sitemap]';
			}
		}
	}
	return $content;
}

/**
 * This method is registered as a posts where filter and avoids that technical sites (e.g. delegating to first child)
 * are considered of the query.
 * @access private
 * @param String $where
 * @return String the filtered where statement
 */
function bjoerne_filter_posts_where($where) {
	if (is_user_logged_in()) {
		return $where;
	}
	global $wpdb;
	$inner_where = "meta_key='bjoerne_page_type' AND meta_value='delegate_to_first_child'";
	$where .= " AND $wpdb->posts.ID NOT IN (SELECT post_id FROM $wpdb->postmeta WHERE $inner_where)";
	return $where;
}

/**
 * @access private
 * @param $rootNodes
 * @return Bjoerne_PageNode the found node
 */
function &bjoerne_find_current_node(&$rootNodes) {
	if (is_page()) {
		global $page_id;
		if (!$page_id) {
			global $wp_query;
			$page_obj = $wp_query->get_queried_object();
			$page_id = $page_obj->ID;
		}
		return bjoerne_find_page_by_id($page_id);
	}
	if (is_single()) {
		session_start();
		$result = bjoerne_find_category_by_id($_SESSION['bjoerne_last_category_id']);
		if (null != $result) {
			return $result;
		}
		$result = bjoerne_find_category_by_name($_SESSION['bjoerne_last_category_name']);
		if (null != $result) {
			return $result;
		}
	}
	if (is_category() || is_single()) {
		// check name first, $cat is also set in this case
		global $category_name;
		$result = bjoerne_find_category_by_name($category_name);
		if (null != $result) {
			return $result;
		}
		global $cat;
		$result = bjoerne_find_category_by_id($cat);
		if (null != $result) {
			return $result;
		}
	}
	if (is_single()) {
		$categories = get_the_category();
		foreach (get_the_category() as $category) {
			$result = bjoerne_find_category_by_id($category->cat_ID);
			if (null != $result) {
				return $result;
			}
			$result = bjoerne_find_category_by_name($category->category_nicename);
			if (null != $result) {
				return $result;
			}
		}
	}
	return bjoerne_null();
}

/**
 * Looks for the category with the given id. If the category is found the id is set in the session
 * as 'bjoerne_last_category_id'. This is used if a single post is invoked and plugin finds out which
 * category should be selected in the navigation.
 * @param int category id
 * @return Bjoerne_PageNode the found node
 */
function &bjoerne_find_category_by_id($category_id) {
	if (null == $category_id) {
		return bjoerne_null();
	}
	$criteria = array('bjoerne_page_type' => 'category', 'bjoerne_category_id' => $category_id);
	$result = bjoerne_find_page_internal(bjoerne_get_navigation_nodes(), $criteria, new Bjoerne_PageNodeMatcherByMetadata());
	if (null != $result) {
		session_start();
		$_SESSION['bjoerne_last_category_id'] = $category_id;
		unset($_SESSION['bjoerne_last_category_name']);
	}
	return $result;
}

/**
 * Looks for the category with the given name. If the category is found the id is set in the session
 * as 'bjoerne_last_category_name'. This is used if a single post is invoked and plugin finds out which
 * category should be selected in the navigation.
 * @param int category id
 * @return Bjoerne_PageNode the found node
 */
function &bjoerne_find_category_by_name($category_name) {
	if (null == $category_name) {
		return bjoerne_null();
	}
	$criteria = array('bjoerne_page_type' => 'category', 'bjoerne_category_name' => $category_name);
	$result = bjoerne_find_page_internal(bjoerne_get_navigation_nodes(), $criteria, new Bjoerne_PageNodeMatcherByMetadata());
	if (null != $result) {
		session_start();
		$_SESSION['bjoerne_last_category_name'] = $category_name;
		unset($_SESSION['bjoerne_last_category_id']);
	}
	return $result;
}

/**
 * Returns the element of the path on the given level.
 * @param int $level
 * @return @return Bjoerne_PageNode the found node
 */
function &bjoerne_get_current_path_element($level) {
	$path = &bjoerne_get_current_path_elements();
	if ($level > sizeof($path)) {
		return bjoerne_null();
	}
	return $path[$level];
}

/**
 * Returns an array of all path elements. Path means all descendant nodes
 * of the current node and the current node itself.
 * @return array Array of nodes
 */
function &bjoerne_get_current_path_elements() {
	if (array_key_exists('bjoerne_current_path', $GLOBALS)) {
		$current_path =& $GLOBALS['bjoerne_current_path'];
		$result = array();
		for ($i=0; $i<sizeof($current_path); $i++) {
			$result[$i] =& $current_path[$i];
		}
		return $result;
	}
	return bjoerne_empty_array();
}

/**
 * Generic method to find a node with the given matcher.
 * @param $arg
 * @param $matcher
 * @return Bjoerne_PageNode the found node
 */
function &bjoerne_find_page($arg, &$matcher) {
	$root_nodes = bjoerne_get_navigation_nodes();
	return bjoerne_find_page_internal($root_nodes, $arg, $matcher);
}
/**
 * @access private
 * @param $nodes
 * @param $arg
 * @param $matcher
 * @return Bjoerne_PageNode the found node
 */
function &bjoerne_find_page_internal(&$nodes, $arg, &$matcher) {
	for ($i=0; $i<sizeof($nodes); $i++) {
		$node =& $nodes[$i];
		if ($matcher->matches($node, $arg)) {
			return $node;
		}
		$result =& bjoerne_find_page_internal($node->get_children(), $arg, $matcher);
		if (null != $result) {
			return $result;
		}
	}
	$result = null;
	return $result;
}

/**
 * @access private
 * @param array $page WordPress page data object
 * @param array $parent_to_pages_map internal used array
 * @return Bjoerne_PageNode the created node
 */
function &bjoerne_create_navigation_root_node(&$page, &$parent_to_pages_map) {
	return bjoerne_create_navigation_node_internal($page, $parent_to_pages_map);
}

/**
 * @access private
 * @param Bjoerne_PageNode $parent the parent node
 * @param array $page WordPress page data object
 * @param array $parent_to_pages_map internal used array
 * @return Bjoerne_PageNode the created node
 */
function &bjoerne_create_navigation_node(&$parent, &$page, &$parent_to_pages_map) {
	$node =& bjoerne_create_navigation_node_internal($page, $parent_to_pages_map);
	$parent->add_child($node);
	return $node;
}

/**
 * @access private
 * @param array $page WordPress page data object
 * @param array $parent_to_pages_map internal used array
 * @return Bjoerne_PageNode the created node
 */
function &bjoerne_create_navigation_node_internal(&$page, &$parent_to_pages_map) {
	$node = new Bjoerne_PageNode($page);
	$page_id = $page->ID;
	if (array_key_exists($page_id, $parent_to_pages_map)) {
		$childPages =& $parent_to_pages_map[$page_id];
		for ($i=0; $i<sizeof($childPages); $i++) {
			$childPage =& $childPages[$i];
			bjoerne_create_navigation_node($node, $childPage, $parent_to_pages_map);
		}
	}
	$node->set_metadata(get_post_custom($page_id));
	return $node;
}

/**
 * Print the sitemap.
 * @param array $attributes
 * @return void
 */
function bjoerne_print_sitemap($attributes) {
	print(bjoerne_get_sitemap($attributes));
}

/**
 *
 * @param array $attributes
 * @return String the sitemap
 */
function bjoerne_get_sitemap($attributes) {
	$style_class;
	if ((null != $attributes) && array_key_exists('style_class', $attributes)) {
		$style_class = $attributes['style_class'];
	} else {
		$style_class = 'sitemap';
	}
	$result = '';
	$result .= '<div class="'.$style_class.'">';
	$result .= bjoerne_get_sitemap_internal(bjoerne_get_navigation_nodes());
	$result .= '</div>';
	return $result;
}

/**
 * @access private
 * @param array $nodes
 * @return String the sitemap
 */
function bjoerne_get_sitemap_internal($nodes) {
	if ((null == $nodes) || empty($nodes)) {
		return;
	}
	$result = '';
	$result .= '<ul>';
	foreach ($nodes as $node) {
		if (!bjoerne_is_node_visible($node)) {
			continue;
		}
		$result .= '<li>';
		$result .= bjoerne_get_link($node);
		$result .= bjoerne_get_sitemap_internal($node->get_children());
		$result .= '</li>';
	}
	$result .= '</ul>';
	return $result;
}

/**
 *
 * @param Bjoerne_PageNode $node
 * @param String $attr_name
 * @param boolean $inherit true if metadata of the parent node are used if a key can't be found.
 * @return array
 */
function &bjoerne_get_metadata_values(&$node, $attr_name, $inherit = false) {
	if (null == $node) {
		return bjoerne_null();
	}
	$metadata =& $node->get_metadata();
	if ((null == $metadata) || !array_key_exists($attr_name, $metadata)) {
		if ($inherit && (null != $node->get_parent())) {
			return bjoerne_get_metadata_values($node->get_parent(), $attr_name, true);
		} else {
			return bjoerne_empty_array();
		}
	}
	return $metadata[$attr_name];
}

/**
 * Returns a single metadata. If there exist more than one the first one is returned.
 * @param Bjoerne_PageNode $node
 * @param String $attr_name
 * @param $inherit true if metadata of the parent node are used if a key can't be found.
 * @return String
 */
function bjoerne_get_metadata_single(&$node, $attr_name, $inherit = false) {
	$values =  bjoerne_get_metadata_values($node, $attr_name, $inherit);
	if (null == $values) {
		return null;
	}
	return $values[0];
}

/**
 * Helping function to print text with a trailed &#92;n.
 * @param String $text
 * @return String
 */
function bjoerne_println($text = '') {
	print($text);
	print("\n");
}

/**
 * Helping function to print text with a trailed html line break.
 *
 * @param String $text
 * @param int $num_of_breaks
 * @return String
 */
function bjoerne_println_br($text, $num_of_breaks = 1) {
	print($text);
	for ($i=0; $i<$num_of_breaks; $i++) {
		print('<br />');
	}
}

/**
 * Helping function to print an array with a trailed html line break.
 *
 * @param String $text
 * @param int $num_of_breaks
 * @return String
 */
function bjoerne_println_r($text, $num_of_breaks = 1) {
	print_r($text);
	for ($i=0; $i<$num_of_breaks; $i++) {
		print('<br />');
	}
}

/**
 * Returns if a String contains another String.
 * @param String $haystack
 * @param String $needle
 * @return boolean
 */
function bjoerne_str_contains($haystack, $needle) {
	return 0 < strpos($haystack, $needle);
}

/**
 * Returns the value of an array for a given key. Checks if the key exists
 * before returning.
 * @access private
 * @param String $key
 * @param array $arr
 * @return Object
 */
function &bjoerne_array_get($key, &$arr) {
	$result = null;
	if ((null == $arr) || !array_key_exists($key, $arr)) {
		return $result;
	}
	return $arr[$key];
}

/**
 * Print an &raquo; sign which I like to use.
 * @access private
 * @return unknown_type
 */
function bjoerne_print_arrow() {
	echo '<span class="arrow">&raquo;</span>';
}

/**
 * Returns the current node.
 * @return Bjoerne_PageNode
 */
function &bjoerne_get_current_node() {
	return $GLOBALS['bjoerne_current_node'];
}


/**
 * Returns the navigation nodes on the given level or an empty array if no nodes can be found.
 * @param int $level
 * @return array Array of Bjoerne_PageNode elements.
 */
function &bjoerne_get_navigation_nodes($level = 0) {
	if (0 == $level) {
		return $GLOBALS['bjoerne_root_nodes'];
	}
	$parent = bjoerne_get_current_path_element($level-1);
	if (null == $parent) {
		return bjoerne_empty_array();
	}
	return $parent->get_children();
}

/**
 * Search all nodes and looks for one with the given id.
 * @param String $id
 * @return Bjoerne_PageNode the found node.
 */
function &bjoerne_find_page_by_id($id) {
	return bjoerne_find_page($id, new Bjoerne_PageNodeMatcherById());
}

/**
 * Search all nodes and looks for one with the given type.
 * Returns the first metching node.
 * @param String $type
 * @return Bjoerne_PageNode the found node.
 */
function &bjoerne_find_page_by_type($type) {
	$criteria = array('bjoerne_page_type' => $type);
	return bjoerne_find_page($criteria, new Bjoerne_PageNodeMatcherByMetadata());
}

/**
 * Search all nodes and looks for one with the given name.
 * Returns the first metching node.
 * @param $name
 * @return Bjoerne_PageNode the found node.
 */
function &bjoerne_find_page_by_name($name) {
	return bjoerne_find_page($name, new Bjoerne_PageNodeMatcherByName());
}

/**
 * Returns the home node which is configured by WordPress.
 * @return Bjoerne_PageNode the home node.
 */
function &bjoerne_get_home_page() {
	return bjoerne_find_page_by_id(get_option('page_on_front'));
}

/**
 * This methods realises the 'bjoerne_link' short code.
 * @access private
 * @param array $args
 * @return String the link.
 */
function bjoerne_get_link_shortcode($args) {
	$args['visible'] = true;
	if (array_key_exists('page_id', $args)) {
		$node =& bjoerne_find_page_by_id($args['page_id']);
	} else if (array_key_exists('page_name', $args)) {
		$node =& bjoerne_find_page_by_name($args['page_name']);
	} else if (array_key_exists('page_type', $args)) {
		$node =& bjoerne_find_page_by_type($args['page_type']);
	}
	return bjoerne_get_link($node, $args);
}

/**
 * Returns a complete &lt;a&gt;-tag for the given node.
 * @param $node
 * @param $args
 * @return String the link.
 */
function bjoerne_get_link(&$node, $args = null) {
	$result = "";
	$visible = false;
	if ((null != $args) && array_key_exists('visible', $args)) {
		$visible = $args['visible'];
	}
	if (!($visible || bjoerne_is_node_visible($node))) {
		return "";
	}
	if (null == $node) {
		echo 'X';
	}
	$metadata = $node->get_metadata();
	$style_class = bjoerne_array_get('style_class', $args);
	$style_class_metadata = bjoerne_get_metadata_single($node, 'bjoerne_link_attribute:style_class');
	if (null != $style_class_metadata) {
		if (null == $style_class) {
			$style_class = $style_class_metadata;
		} else {
			$style_class .= ' '.$style_class_metadata;
		}
	}
	$style = bjoerne_array_get('style_class', $args);
	$style_metadata = bjoerne_get_metadata_single($node, 'bjoerne_link_attribute:style');
	if (null != $style_metadata) {
		if (null == $style) {
			$style = $style_metadata;
		} else {
			$style_ .= $style_metadata;
		}
	}
	$selected_class = bjoerne_array_get('selected_class', $args);
	$selected_class_metadata = bjoerne_get_metadata_single($node, 'bjoerne_link_attribute:selected_class');
	if (null != $selected_class_metadata) {
		if (null == $selected_class) {
			$selected_class = $selected_class_metadata;
		} else {
			$selected_class .= ' '.$selected_class_metadata;
		}
	}
	$selected_style = bjoerne_array_get('selected_style', $args);
	$selected_style_metadata = bjoerne_get_metadata_single($node, 'bjoerne_link_attribute:selected_style');
	if (null != $selected_style_metadata) {
		if (null == $selected_style) {
			$selected_style = $selected_style_metadata;
		} else {
			$selected_style .= ' '.$selected_style_metadata;
		}
	}
	if ($node->is_selected()) {
		if (null == $style_class) {
			$style_class = $selected_class;
		} else {
			$style_class .= ' '.$selected_class;
		}
		if (null == $style) {
			$style = $selected_style;
		} else {
			$style .= $selected_style;
		}
	}
	$target = bjoerne_array_get('target', $args);
	$target_metadata = bjoerne_get_metadata_single($node, 'bjoerne_link_attribute:target');
	if (null != $target_metadata) {
		$target = $target_metadata;
	}
	$title = bjoerne_array_get('title', $args);
	$title_metadata = bjoerne_get_metadata_single($node, 'bjoerne_link_attribute:title');
	if (null != $title_metadata) {
		$title = $title_metadata;
	}
	$before_link = bjoerne_array_get('before_link', $args);
	$after_link = bjoerne_array_get('after_link', $args);
	if (null != $before_link) {
		$result .= $before_link;
	}
	$result .= '<a href="'.$node->get_url().'"';
	if (null != $style_class) {
		$result .= ' class="'.$style_class.'"';
	}
	if (null != $style){
		$result .= ' style="'.$style.'"';
	}
	if (null != $title) {
		$result .= ' title="'.$title.'"';
	}
	if (null != $target) {
		$result .= ' target="'.$target.'"';
	}
	$result .= '>'.$node->get_name().'</a>';
	if (null != $after_link) {
		$result .= $after_link;
	}
	return $result;
}

/**
 * Prints a complete &lt;a&gt;-tag for the given node.
 * @param $node
 * @param $args
 * @return void
 */
function bjoerne_print_link(&$node, $args = null) {
	print(bjoerne_get_link($node, $args));
}

/**
 * Returns if the node is visible. To decide if a node is visible
 * the metadata are looked for a key 'bjoerne_visibility'. If it set to 'hidden'
 * the node is not visible and otherwise it is.
 * @param $node
 * @return boolean
 */
function bjoerne_is_node_visible(&$node) {
	$visibility = bjoerne_get_metadata_single($node, 'bjoerne_visibility');
	if (null == $visibility) {
		return true;
	}
	if ('hidden' == $visibility) {
		return false;
	}
	return true;
}

/**
 * Returns if two nodes are equal. This is a workaround because of a
 * PHP4 bug. PHP4 has a problem to find out if two node are the same
 * if they have parent-child-relationships like the Bjoerne_PageNode
 * objects have.
 * @param $node1
 * @param $node2
 * @return unknown_type
 */
function bjoerne_equals_nodes(&$node1, &$node2) {
	if ((null == $node1) && (null != $node2)) {
		return false;
	}
	if ((null != $node1) && (null == $node2)) {
		return false;
	}
	$page1 =& $node1->get_page();
	$page2 =& $node2->get_page();
	return $page1->guid == $page2->guid;
}

/**
 * Registers a name resolver.
 * @param Bjoerne_PageNodeNameResolver $resolver
 * @return void
 */
function bjoerne_register_name_resolver(&$resolver) {
	bjoerne_register_resolver('bjoerne_name_resolvers', $resolver);
}

/**
 * Returns array of registered name resolvers
 * @access private
 * @return array array of Bjoerne_PageNodeNameResolver
 */
function bjoerne_get_name_resolvers() {
	return bjoerne_get_resolvers('bjoerne_name_resolvers');
}

/**
 * Registers the default name resolver.
 * @param Bjoerne_PageNodeNameResolver $resolver
 * @return void
 */
function bjoerne_register_default_name_resolver(&$resolver) {
	$GLOBALS['bjoerne_default_name_resolver'] = $resolver;
}

/**
 * Returns the default name resolver
 * @return Bjoerne_PageNodeNameResolver
 */
function &bjoerne_get_default_name_resolver() {
	return $GLOBALS['bjoerne_default_name_resolver'];
}

/**
 * Registers a url resolver.
 * @param Bjoerne_PageNodeUrlResolver $resolver
 * @return void
 */
function bjoerne_register_url_resolver(&$resolver) {
	bjoerne_register_resolver('bjoerne_url_resolvers', $resolver);
}

/**
 * Returns array of registered url resolvers
 * @access private
 * @return array array of Bjoerne_PageNodeUrlResolver
 */
function &bjoerne_get_url_resolvers() {
	return bjoerne_get_resolvers('bjoerne_url_resolvers');
}

/**
 * Registers the default url resolver.
 * @param Bjoerne_PageNodeUrlResolver $resolver
 * @return void
 */
function bjoerne_register_default_url_resolver(&$resolver) {
	$GLOBALS['bjoerne_default_url_resolver'] = $resolver;
}

/**
 * Returns the default url resolver
 * @return Bjoerne_PageNodeUrlResolver
 */
function &bjoerne_get_default_url_resolver() {
	return $GLOBALS['bjoerne_default_url_resolver'];
}

/**
 * @access private
 * @param $key
 * @param Object $resolver
 * @return void
 */
function bjoerne_register_resolver($key, &$resolver) {
	if (!array_key_exists($key, $GLOBALS)) {
		$GLOBALS[$key] = array();
	}
	array_push($GLOBALS[$key], $resolver);
}

/**
 * @access private
 * @param String $key
 * @return Object
 */
function &bjoerne_get_resolvers($key) {
	if (array_key_exists($key, $GLOBALS)) {
		return $GLOBALS[$key];
	}
	return bjoerne_empty_array();
}

/**
 * Make sure to return a variable if a method returns a reference.
 * Used to avoid php warnings when returning an empty array by a function which returns references.
 * @access private
 * @return array empty array
 */
function &bjoerne_empty_array() {
	$result = array();
	return $result;
}

/**
 * Make sure to return a variable if a method returns a reference.
 * Used to avoid php warnings when returning simple null by a function which returns references.
 * @access private
 * @return null
 */
function &bjoerne_null() {
	$result = null;
	return $result;
}

add_action('template_redirect', 'bjoerne_init');
add_filter('the_content', 'bjoerne_filter_content');
add_filter('posts_where', 'bjoerne_filter_posts_where');
add_shortcode('bjoerne_sitemap', 'bjoerne_get_sitemap');
add_shortcode('bjoerne_link', 'bjoerne_get_link_shortcode');

?>