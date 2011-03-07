<?php
/**
 * @package Bjoerne
 * @subpackage NavigationDuLapinBlanc
 * @version 1.0.3
 * Classes used by navigation-du-lapin-blanc WordPress plugin
 */

/**
 * @package Bjoerne
 * This class represents a node of the navigation. The whole navigation structure of a project
 * is tranformed into a model of nodes. Because of the parent-child-relationship you can browse
 * through this model to find nodes, create a breadcrumb path etc.
 * @author Bjoerne
 *
 */
class Bjoerne_PageNode {

	/**
	 * @access private
	 * @var array
	 */
	var $page;

	/**
	 * @access private
	 * @var array
	 */
	var $children = array();

	/**
	 * @access private
	 * @var boolean
	 */
	var $selected;

	/**
	 * @access private
	 * @var boolean
	 */
	var $on_selected_path;

	/**
	 * Constructor to create an node for a given WordPress page data object.
	 * @param $page
	 * @return node with a given WordPress page object
	 */
	function Bjoerne_PageNode(&$page) {
		$this->page = $page;
	}

	/**
	 * Returns the WordPress page data object of this node.
	 *
	 */
	function &get_page() {
		return $this->page;
	}

	/**
	 * Returns the parent node.
	 *
	 */
	function &get_parent() {
		return $this->parent;
	}

	/**
	 * Sets the parent node.
	 * @return void
	 */
	function set_parent(&$parent) {
		$this->parent =& $parent;
	}

	/**
	 * Returns metadata of this node
	 * @return array Array of metadata (key => value)
	 */
	function get_metadata() {
		return $this->metadata;
	}

	/**
	 * Sets metadata of this node.
	 * @param array $metadata Array of metadata (key => value)
	 * @return void
	 */
	function set_metadata($metadata) {
		$this->metadata =& $metadata;
	}

	/**
	 * Returns the child nodes.
	 * @return array Array of nodes.
	 */
	function &get_children() {
		return $this->children;
	}

	/**
	 * Adds a child node.
	 * @param Bjoerne_PageNode child node
	 * @return void
	 */
	function add_child(&$child) {
		$this->children[] =& $child;
		$child->set_parent($this);
	}

	/**
	 * Returns the name of this node. The name is resolved by registered resolvers.
	 * @return String name
	 */
	function get_name() {
		foreach (bjoerne_get_name_resolvers() as $resolver) {
			$result = $resolver->get_name($this);
			if (null != $result) {
				return $result;
			}
		}
		$resolver = bjoerne_get_default_name_resolver();
		return $resolver->get_name($this);
	}

	/**
	 * Returns the url of this node. The url is resolved by registered resolvers.
	 * @return String url
	 */
	function get_url() {
		foreach (bjoerne_get_url_resolvers() as $resolver) {
			$result = $resolver->get_url($this);
			if (null != $result) {
				return $result;
			}
		}
		$resolver = bjoerne_get_default_url_resolver();
		return $resolver->get_url($this);
	}

	/**
	 * Returns if this node is the currently displayed node.
	 * @return boolean
	 */
	function is_selected() {
		return $this->selected;
	}

	/**
	 * Sets if this node is the currently displayed node.
	 * @return void
	 */
	function set_selected($selected) {
		$this->selected = $selected;
	}

	/**
	 * Returns if this node is an descendant node of the currently displayed node.
	 * @return boolean
	 */
	function is_on_selected_path() {
		return $this->on_selected_path;
	}

	/**
	 * Sets if this node is an descendant node of the currently displayed node.
	 * @return void
	 */
	function set_on_selected_path($on_selected_path) {
		$this->on_selected_path = $on_selected_path;
	}

	/**
	 * Returns a String representation of this node.
	 * @return String
	 */
	function __toString() {
		$page =& $this->get_page();
		return $this->get_name().' (id='.$page->ID.')';
	}
}

/**
 * @package Bjoerne
 * Interface to check if a node matches some criterias.
 * @abstract
 * @author Bjoerne
 *
 */
class Bjoerne_PageNodeMatcher {

	/**
	 * Returns if the criterias of this object matches the node.
	 * @param Bjoerne_PageNode node to match
	 * @param array additional parameters needed to decide if the node matches.
	 * @abstract
	 */
	function matches($node, $arg) {
		// abstract
	}
}

/**
 * @package Bjoerne
 * Matcher checking if the id matches.
 * @author Bjoerne
 *
 */
class Bjoerne_PageNodeMatcherById extends Bjoerne_PageNodeMatcher {

	/**
	 * @param Bjoerne_PageNode node to match
	 * @param String page id
	 */
	function matches(&$node, $arg) {
		$page =& $node->get_page();
		return $page->ID == $arg;
	}
}

/**
 * @package Bjoerne
 * Matcher checking if the name matches.
 * @author Bjoerne
 *
 */
class Bjoerne_PageNodeMatcherByName extends Bjoerne_PageNodeMatcher {
	/**
	 * @param Bjoerne_PageNode node to match
	 * @param String name to match.
	 */
	function matches(&$node, $arg) {
		return $node->get_name() == $arg;
	}
}


/**
 * @package Bjoerne
 * Matcher checking if metadata attribute exists.
 * @author Bjoerne
 *
 */
class Bjoerne_PageNodeMatcherByMetadata extends Bjoerne_PageNodeMatcher {
	/**
	 * @param Bjoerne_PageNode node to match
	 * @param array metadata to match (key => value)
	 */
	function matches(&$node, $arg) {
		foreach ($arg as $key => $value) {
			if ($value != bjoerne_get_metadata_single($node, $key)) {
				return false;
			}
		}
		return true;
	}
}

/**
 * @package Bjoerne
 * Interface to get a name for a node. If get_name() returns not null
 * the result is used as the display name for the node.
 * @abstract
 * @author Bjoerne
 *
 */
class Bjoerne_PageNodeNameResolver {
	/**
	 * Returns the name of this node. If this resolver is not appropriate it must return null.
	 * @abstract
	 * @return String name
	 */
	function get_name(&$node) {
		// abstract
	}
}

/**
 * @package Bjoerne
 * Interface to get an URL for a node. If get_url() returns not null
 * the result is used as the URL for the node.
 * @abstract
 * @author Bjoerne
 *
 */
class Bjoerne_PageNodeUrlResolver {
	/**
	 * Returns the url of this node. If this resolver is not appropriate it must return null.
	 * @abstract
	 * @return String url
	 */
	function get_url(&$node) {
		// abstract
	}
}

/**
 * @package Bjoerne
 * Default name resolver returning the post_title attribute of the WordPress page data object.
 * @author Bjoerne
 *
 */
class Bjoerne_DefaultNameResolver extends Bjoerne_PageNodeNameResolver {
	/**
	 * Returns the post_title attribute of the WordPress page data object.
	 * @return String name
	 */
	function get_name(&$node) {
		$page =& $node->get_page();
		return $page->post_title;
	}
}

/**
 * @package Bjoerne
 * This resolver returns the name from the metadata if there exists an entry with the key 'bjoerne_navigation_name'.
 * @author Bjoerne
 *
 */
class Bjoerne_NameFromMetadataResolver extends Bjoerne_PageNodeNameResolver {

	/**
	 * Returns the name from the metadata if there exists an entry with the key 'bjoerne_navigation_name'.
	 * @return String name
	 */
	function get_name(&$node) {
		$metadata =& $node->get_metadata();
		if (null == $metadata) {
			return null;
		}
		if (!array_key_exists('bjoerne_navigation_name', $metadata)) {
			return null;
		}
		return $metadata['bjoerne_navigation_name'][0];
	}
}

/**
 * @package Bjoerne
 * This url resolver returns the url of the first child. This is used if a navigation node should not have own
 * content, but the first child should be displayed when clicking the menu item.
 * @author Bjoerne
 *
 */
class Bjoerne_DelegateToFirstChildUrlResolver extends Bjoerne_PageNodeUrlResolver {
	/**
	 * Returns the url of the first child
	 * @return String url
	 */
	function get_url(&$node) {
		$page_type = bjoerne_get_metadata_single($node, 'bjoerne_page_type');
		if ('delegate_to_first_child' == $page_type) {
			$children = $node->get_children();
			return $children[0]->get_url();
		}
		return null;
	}
}

/**
 * @package Bjoerne
 * Resolver returning the url to a category. This is done by looking up the metadata of this node.
 * 'bjoerne_page_type' mus be 'category' and there must exist an entry with the key 'bjoerne_category_id'
 * or 'bjoerne_category_name'.
 * @author Bjoerne
 *
 */
class Bjoerne_CategoryUrlResolver extends Bjoerne_PageNodeUrlResolver {
	/**
	 * Returns the url to a category
	 * @return String url
	 */
	function get_url(&$node) {
		$page_type = bjoerne_get_metadata_single($node, 'bjoerne_page_type');
		if ('category' == $page_type) {
			$category_id = bjoerne_get_metadata_single($node, 'bjoerne_category_id');
			if (null != $category_id) {
				return get_category_link($category_id);
			}
			$category_name = bjoerne_get_metadata_single($node, 'bjoerne_category_name');
			if (null != $category_name) {
				return get_option( 'home' ).'/?category_name='.$category_name;
			}
		}
		return null;
	}
}

/**
 * @package Bjoerne
 * Resolver returning the url to an external page. This is done by looking up the metadata of this node.
 * 'bjoerne_page_type' mus be 'external' and there must exist an entry with the key 'bjoerne_external_url'.
 * @author Bjoerne
 *
 */
class Bjoerne_ExternalUrlResolver extends Bjoerne_PageNodeUrlResolver {
	/**
	 * Returns the url to an external page.
	 * @return String url
	 */
	function get_url(&$node) {
		$page_type = bjoerne_get_metadata_single($node, 'bjoerne_page_type');
		if ('external' == $page_type) {
			$external_url = bjoerne_get_metadata_single($node, 'bjoerne_external_url');
			if (null != $external_url) {
				return $external_url;
			}
		}
		return null;
	}
}

/**
 * @package Bjoerne
 * Default url resolver used for pages.
 * @author Bjoerne
 *
 */
class Bjoerne_DefaultUrlResolver extends Bjoerne_PageNodeUrlResolver {
	/**
	 * Return url to a page.
	 * @return String url
	 */
	function get_url(&$node) {
		$page = $node->get_page();
		return get_page_link($page->ID);
	}
}

?>