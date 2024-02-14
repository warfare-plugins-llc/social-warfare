<?php

/**
 * SWP_Compatibility: A class to enhance compatibility with other plugins
 *
 * @package   SocialWarfare\Functions
 * @copyright Copyright (c) 2018, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     1.0.0
 * @since     3.0.0 | 22 FEB 2018 | Refactored into a class-based system.
 * @since     3.6.1 | 22 MAY 2019 | Added the Pin Button Cleaner. Updated docblocks.
 *
 */
class SWP_Compatibility {


	/**
	 * The magic method used to insantiate this class.
	 *
	 * This adds compatibility with Simple Podcast Press, the Duplicate Posts
	 * plugin, and Really Simple SSL.
	 *
	 * @since  2.1.4
	 * @since  3.6.1 | 22 MAY 2019 | Added the Pin Button Cleaner
	 * @since  3.6.1 | 22 MAY 2019 | Moved core functionality into separate methods.
	 * @access public
	 * @param  integer $id The post ID
	 * @return none
	 *
	 */
	public function __construct() {
		$this->queue_compatibility_filter_hooks();
		$this->make_simple_podcast_press_compatible();
	}


	/**
	 * A method to hook into custom hooks provided by other plugins. These allow
	 * us access to their functionality to allow for easy compatibility patches.
	 *
	 * @since  3.6.1 | 22 MAY 2019 | Created
	 * @param  void
	 * @return void
	 *
	 */
	public function queue_compatibility_filter_hooks() {

		/**
		 * This will remove our custom meta fields from a newly duplicated post
		 * so that when Duplicate Post plugin does it's thing, the share counts
		 * and other data won't be duplicated with the post.
		 *
		 */
		add_filter( 'duplicate_post_meta_keys_filter', array( $this, 'filter_duplicate_meta_keys' ) );

		/**
		 * This will make it so that the Really Simple SSL plugin won't be able
		 * to hijack and manipulate the URL's that we are attempting to use for
		 * the share recovery features.
		 *
		 */
		add_filter( 'rsssl_fixer_output', array( $this, 'rsssl_fix_compatibility' ) );

		/**
		 * A method used to clean out pin buttons that were generated by the JS
		 * inside of a page builder html and then subsequently saved into the content.
		 *
		 */
		add_filter( 'the_content', array( $this, 'clean_out_pin_buttons' ) );
	}


	/**
	 * A method used to disable the open graph tags from the Podcast Press
	 * plugin to allow ours to be the only ones in the markup.
	 *
	 * @since  3.6.1 | 22 MAY 2019 | Created
	 * @param  void
	 * @return void
	 *
	 */
	public function make_simple_podcast_press_compatible() {
		if ( is_plugin_active( 'simple-podcast-press/simple-podcast-press.php' ) ) {
			global $ob_wp_simplepodcastpress;
			remove_action( 'wp_head', array( $ob_wp_simplepodcastpress, 'spp_open_graph' ), 1 );
		}
	}


	/**
	 * A method to clean out Pinterest save buttons that have been erroneously
	 * saved to the database.
	 *
	 * Some theme/page builders have taken the HTML generated by the javascript
	 * and saved that HTML directly into the database. Since the JS creates the
	 * pinterest save button on each page load, this has resulted in multiple
	 * instances of the save button being generated, saved, generated, saved, etc.
	 *
	 * This function will search the_content via the WordPress hook of the same
	 * name and remove them.
	 *
	 * @since  3.6.1 | 21 MAY 2019 | Created
	 * @param  string $content The string of content passed in by WordPress core.
	 * @return string $content The modified content.
	 *
	 */
	public function clean_out_pin_buttons( $content ) {

		/**
		 * If the content that is passed in is empty, then just bail out as we
		 * don't have anything that will need processing/filtering.
		 *
		 */
		if ( empty( $content ) ) {
			return $content;
		}

		/**
		 * If the content doesn't contain any pinit buttons, just bail out and
		 * return the content. We don't have anything to process here.
		 *
		 */
		if ( strpos( $content, 'sw-pinit-button' ) === false ) {
			return $content;
		}

		/**
		 * We'll be using PHP's DOMDocument to make our alterations to the
		 * content, so if it doesn't exist, we'll need to bail out. This has
		 * been available since PHP 5, but some server's may have it manually
		 * turned off in their configuration settings. Better safe than sorry.
		 *
		 */
		if ( ! class_exists( 'DOMDocument' ) ) {
			return $content;
		}

		// DOMDocument works better with an XML delcaration.
		if ( false === strpos( $content, '?xml version' ) ) {
			$xml_statement       = '<?xml version="1.0" encoding="UTF-8"?>';
			$content             = $xml_statement . $content;
			$added_xml_statement = true;
		}

		/**
		 * The content is most likely not going to be a properly formatted
		 * HTML document. As such, it's going to throw some annoying PHP errors
		 * whilst still getting and parsing the information that we need. As
		 * such, we'll just turn off error reporting and then turn it back on
		 * after we're done here.
		 *
		 * This function returns the previous error reporting status. We can use
		 * this to revert the setting back to the user's default when we are done.
		 *
		 */
		$libxml_error_status = libxml_use_internal_errors( true );

		/**
		 * Load the content text into a DOMDocument object, and then we'll use
		 * that object to create a DOMXPath object which will allow us jQuery-like
		 * traversal of the DOM to make our adjustments.
		 *
		 */
		$dom = new DOMDocument();
		$dom->loadHTML( $content, LIBXML_HTML_NODEFDTD );
		$xpath = new DOMXPath( $dom );

		/**
		 * This will locate and remove all of the .sw-pinit-button anchor tags
		 * that have been placed throughout the content.
		 *
		 */
		$nodes = $xpath->query( "//*[contains(@class, 'sw-pinit-button')]" );
		foreach ( $nodes as $node ) {
			$parent = $node->parentNode;
			$parent->removeChild( $node );
		}

		/**
		 * The anchor tags, along with the images in the content, were wrapped
		 * in a div wrapper. This loop will locate those wrappers and remove
		 * them without removing their content (i.e. the user's images).
		 *
		 */
		$nodes = $xpath->query( "//*[contains(@class, 'sw-pinit')]" );
		foreach ( $nodes as $node ) {
			$parent = $node->parentNode;
			while ( $node->hasChildNodes() ) {
				$parent->insertBefore( $node->lastChild, $node->nextSibling );
			}
			$parent->removeChild( $node );
		}

		/**
		 * When everything is done, we'll save the HTML, turn error reporting
		 * back to their default settings, clear the errors, and remove the XML
		 * information that we added above. Then, of course, we'll return the
		 * modified content.
		 *
		 */
		$content = $dom->saveHTML();
		$start   = strpos( $html, '<body>' ) + 6;
		$end     = strrpos( $html, '</body>' ) - strlen( $html );
		$content = substr( $html, $start, $end );
		libxml_use_internal_errors( $libxml_error_status );
		libxml_clear_errors();

		if ( $added_xml_statement ) {
			$content = str_replace( $xml_statement, '', $content );
		}

		return $content;
	}


	/**
	 * A function to fix the share recovery conflict with Really Simple SSL
	 * plugin. Their plugin was using some sort of find/replace to ensure that
	 * all links on the site use the HTTPS protocol. However, share recovery is
	 * specifically attempting to fetch share counts for the old non-ssl
	 * protocol, so we need to make sure that we undo this replacement before
	 * fetching share counts.
	 *
	 * @since 2.2.2 | 01 JAN 2018 | Created
	 * @param  string $html A string of html to be filtered
	 * @return string $html The filtered string of html
	 * @access public
	 *
	 */
	public function rsssl_fix_compatibility( $html ) {
		//replace the https back to http
		$html = str_replace( "swp_post_recovery_url = 'https://", "swp_post_recovery_url = 'http://", $html );
		return $html;
	}


	/**
	 * Removes Social Warfare keys from the meta before post is duplicated.
	 *
	 * This method is a specific compatibility patch for the plugin Duplicate
	 * Posts. Since our share counts, social media images, descriptions, etc.,
	 * are all stored in post meta fields, they get duplicated when a post is
	 * duplicated. This results in wrong or unuseful data on the new post. This
	 * method stops that from happening.
	 *
	 * @since  3.4.2 | 10 DEC 2018 | Created
	 * @param  array  $meta_keys All meta keys prepared for duplication.
	 * @return array  $meta_keys $meta_keys with no Social Warfare keys.
	 *
	 */
	public function filter_duplicate_meta_keys( $meta_keys = array() ) {
		$blacklist = array( 'swp_', '_shares', 'bitly_link' );

		foreach ( $meta_keys as $key ) {
			foreach ( $blacklist as $forbidden ) {
				if ( strpos( $forbidden, $key ) ) {
					unset( $meta_keys[ $key ] );
				}
			}
		}

		return $meta_keys;
	}
}
