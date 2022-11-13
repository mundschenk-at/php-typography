<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2014-2022 Peter Putzer.
 *  Copyright 2009-2011 KINGdesk, LLC.
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License along
 *  with this program; if not, write to the Free Software Foundation, Inc.,
 *  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 *  ***
 *
 *  @package mundschenk-at/php-typography
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace PHP_Typography;

use PHP_Typography\Fixes\Registry;
use PHP_Typography\Fixes\Default_Registry;

/**
 * Parses HTML5 (or plain text) and applies various typographic fixes to the text.
 *
 * If used with multibyte language, UTF-8 encoding is required.
 *
 * Portions of this code have been inspired by:
 *  - typogrify (https://code.google.com/p/typogrify/)
 *  - WordPress code for wptexturize (https://developer.wordpress.org/reference/functions/wptexturize/)
 *  - PHP SmartyPants Typographer (https://michelf.ca/projects/php-smartypants/typographer/)
 *
 *  @author Jeffrey D. King <jeff@kingdesk.com>
 *  @author Peter Putzer <github@mundschenk.at>
 */
class PHP_Typography {

	/**
	 * A DOM-based HTML5 parser.
	 *
	 * @var \Masterminds\HTML5
	 */
	private $html5_parser;

	/**
	 * The hyphenator cache.
	 *
	 * @var Hyphenator\Cache
	 */
	protected $hyphenator_cache;

	/**
	 * The node fixes registry.
	 *
	 * @var Registry|null;
	 */
	private $registry;

	/**
	 * Whether the Hyphenator\Cache of the $registry needs to be updated.
	 *
	 * @var bool
	 */
	private $update_registry_cache;

	/**
	 * Sets up a new PHP_Typography object.
	 *
	 * @param Registry|null $registry Optional. A fix registry instance. Default null,
	 *                                meaning the default fixes are used.
	 */
	public function __construct( Registry $registry = null ) {
		$this->registry              = $registry;
		$this->update_registry_cache = ! empty( $registry );
	}

	/**
	 * Modifies $html according to the defined settings.
	 *
	 * @since 6.0.0 Parameter $body_classes added.
	 *
	 * @param string   $html         A HTML fragment.
	 * @param Settings $settings     A settings object.
	 * @param bool     $is_title     Optional. If the HTML fragment is a title. Default false.
	 * @param string[] $body_classes Optional. CSS classes added to the virtual
	 *                               <body> element used for processing. Default [].
	 *
	 * @return string The processed $html.
	 */
	public function process( $html, Settings $settings, $is_title = false, array $body_classes = [] ) {
		return $this->process_textnodes(
			$html,
			function( $html, $settings, $is_title ) {
				$this->get_registry()->apply_fixes( $html, $settings, $is_title, false );
			},
			$settings,
			$is_title,
			$body_classes
		);
	}

	/**
	 * Modifies $html according to the defined settings, in a way that is appropriate for RSS feeds
	 * (i.e. excluding processes that may not display well with limited character set intelligence).
	 *
	 * @since 6.0.0 Parameter $body_classes added.
	 *
	 * @param string   $html         A HTML fragment.
	 * @param Settings $settings     A settings object.
	 * @param bool     $is_title     Optional. If the HTML fragment is a title. Default false.
	 * @param string[] $body_classes Optional. CSS classes added to the virtual
	 *                               <body> element used for processing. Default [].
	 *
	 * @return string The processed $html.
	 */
	public function process_feed( $html, Settings $settings, $is_title = false, array $body_classes = [] ) {
		return $this->process_textnodes(
			$html,
			function( $html, $settings, $is_title ) {
				$this->get_registry()->apply_fixes( $html, $settings, $is_title, true );
			},
			$settings,
			$is_title,
			$body_classes
		);
	}

	/**
	 * Applies specific fixes to all textnodes of the HTML fragment.
	 *
	 * @since 6.0.0 Parameter $body_classes added.
	 *
	 * @param string   $html         A HTML fragment.
	 * @param callable $fixer        A callback that applies typography fixes to a single textnode.
	 * @param Settings $settings     A settings object.
	 * @param bool     $is_title     Optional. If the HTML fragment is a title. Default false.
	 * @param string[] $body_classes Optional. CSS classes added to the virtual
	 *                               <body> element used for processing. Default [].
	 *
	 * @return string The processed $html.
	 */
	public function process_textnodes( $html, callable $fixer, Settings $settings, $is_title = false, array $body_classes = [] ) {
		if ( isset( $settings['ignoreTags'] ) && $is_title && ( \in_array( 'h1', $settings['ignoreTags'], true ) || \in_array( 'h2',  $settings['ignoreTags'], true ) ) ) {
			return $html;
		}

		// Lazy-load our parser (the text parser is not needed for feeds).
		$html5_parser = $this->get_html5_parser();

		// Parse the HTML.
		$dom = $this->parse_html( $html5_parser, $html, $settings, $body_classes );

		// Abort if there were parsing errors.
		if ( $dom instanceof \DOMDocument && $dom->hasChildNodes() ) {

			// Retrieve the document body.
			$body_node = $dom->getElementsByTagName( 'body' )->item( 0 );
			if ( $body_node instanceof \DOMElement ) {

				// Process text nodes in the document body.
				$this->process_textnodes_internal( $body_node, $fixer, $settings, $is_title );

				return $html5_parser->saveHTML( $body_node->childNodes );
			}
		}

		return $html;
	}

	/**
	 * Processes the text nodes below the <body> node.
	 *
	 * @since 6.7.0
	 *
	 * @param \DOMNode $body_node  The body node containing the HTML fragment to process.
	 * @param callable $fixer      A callback that applies typography fixes to a single textnode.
	 * @param Settings $settings   A settings object.
	 * @param bool     $is_title   A flag indicating whether the HTML fragment in the DOM is a title.
	 */
	private function process_textnodes_internal( \DOMNode $body_node, callable $fixer, Settings $settings, bool $is_title ) : void {
		// Get the list of tags that should be ignored.
		$xpath          = new \DOMXPath( $body_node->ownerDocument );
		$tags_to_ignore = $this->query_tags_to_ignore( $xpath, $body_node, $settings );

		// Start processing.
		foreach ( $xpath->query( '//text()', $body_node ) as $textnode ) { // @phpstan-ignore-line -- The query is valid.
			if (
				// One of the ancestors should be ignored.
				self::arrays_intersect( DOM::get_ancestors( $textnode ), $tags_to_ignore ) ||
				// The node contains only whitespace.
				$textnode->isWhitespaceInElementContent()
			) {
				continue;
			}

			// Store original content.
			$original = $textnode->data;

			// Apply fixes.
			$fixer( $textnode, $settings, $is_title );

			/**
			 * Until now, we've only been working on a textnode: HTMLify result.
			 *
			 * @var string $new
			 */
			$new = $textnode->data;

			// Replace original node (if anthing was changed).
			if ( $new !== $original ) {
				$this->replace_node_with_html( $textnode, $settings->apply_character_mapping( $new ) );
			}
		}
	}

	/**
	 * Determines whether two object arrays intersect. The second array is expected
	 * to use the spl_object_hash for its keys.
	 *
	 * @template T
	 *
	 * @param array<T> $array1 The keys are ignored.
	 * @param array<T> $array2 This array has to be in the form ( $spl_object_hash => $object ).
	 *
	 * @return bool
	 */
	protected static function arrays_intersect( array $array1, array $array2 ) {
		foreach ( $array1 as $value ) {
			if ( isset( $array2[ \spl_object_hash( $value ) ] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Parse HTML5 fragment while ignoring certain warnings for invalid HTML code (e.g. duplicate IDs).
	 *
	 * @since 6.0.0 Parameter $body_classes added.
	 *
	 * @param \Masterminds\HTML5 $parser       An intialized parser object.
	 * @param string             $html         The HTML fragment to parse (not a complete document).
	 * @param Settings           $settings     The settings to apply.
	 * @param string[]           $body_classes Optional. CSS classes added to the virtual
	 *                                         <body> element used for processing. Default [].
	 *
	 * @return \DOMDocument|null The encoding has already been set to UTF-8. Returns null if there were parsing errors.
	 */
	public function parse_html( \Masterminds\HTML5 $parser, $html, Settings $settings, array $body_classes = [] ) {
		// Silence some parsing errors for invalid HTML.
		\set_error_handler( [ $this, 'handle_parsing_errors' ] ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler
		$xml_error_handling = \libxml_use_internal_errors( true );

		// Inject <body> classes.
		$body = empty( $body_classes ) ? 'body' : 'body class="' . \implode( ' ', $body_classes ) . '"';

		// Do the actual parsing.
		$dom           = $parser->loadHTML( "<!DOCTYPE html><html><{$body}>{$html}</body></html>" );
		$dom->encoding = 'UTF-8';

		// Restore original error handling.
		\libxml_clear_errors();
		\libxml_use_internal_errors( $xml_error_handling );
		\restore_error_handler();

		// Handle any parser errors.
		$errors = $parser->getErrors();
		if ( ! empty( $settings[ Settings::PARSER_ERRORS_HANDLER ] ) && ! empty( $errors ) ) {
			$errors = $settings[ Settings::PARSER_ERRORS_HANDLER ]( $errors );
		}

		// Return null if there are still unhandled parsing errors.
		if ( ! empty( $errors ) && ! $settings[ Settings::PARSER_ERRORS_IGNORE ] ) {
			$dom = null;
		}

		return $dom;
	}

	/**
	 * Silently handle certain HTML parsing errors.
	 *
	 * @since 6.0.0 Unused parameters $errline and $errcontext removed.
	 *
	 * @param int    $errno      Error number.
	 * @param string $errstr     Error message.
	 * @param string $errfile    The file in which the error occurred.
	 *
	 * @return boolean Returns true if the error was handled, false otherwise.
	 */
	public function handle_parsing_errors( $errno, $errstr, $errfile ) {
		if ( ! ( \error_reporting() & $errno ) ) { // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_error_reporting,WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting
			return true; // not interesting.
		}

		// Ignore warnings from parser & let PHP handle the rest.
		return $errno & E_USER_WARNING && 0 === \substr_compare( $errfile, 'DOMTreeBuilder.php', -18 );
	}

	/**
	 * Retrieves an array of nodes that should be skipped during processing.
	 *
	 * @param \DOMXPath $xpath        A valid XPath instance for the DOM to be queried.
	 * @param \DOMNode  $initial_node The starting node of the XPath query.
	 * @param Settings  $settings     The settings to apply.
	 *
	 * @return \DOMNode[] An array of \DOMNode (can be empty).
	 */
	public function query_tags_to_ignore( \DOMXPath $xpath, \DOMNode $initial_node, Settings $settings ) {
		$elements    = [];
		$query_parts = [];
		if ( ! empty( $settings['ignoreTags'] ) ) {
			$query_parts[] = '//' . \implode( ' | //', $settings['ignoreTags'] );
		}
		if ( ! empty( $settings['ignoreClasses'] ) ) {
			$query_parts[] = "//*[contains(concat(' ', @class, ' '), ' " . \implode( " ') or contains(concat(' ', @class, ' '), ' ", $settings['ignoreClasses'] ) . " ')]";
		}
		if ( ! empty( $settings['ignoreIDs'] ) ) {
			$query_parts[] = '//*[@id=\'' . \implode( '\' or @id=\'', $settings['ignoreIDs'] ) . '\']';
		}

		if ( ! empty( $query_parts ) ) {
			$ignore_query = \implode( ' | ', $query_parts );

			$nodelist = $xpath->query( $ignore_query, $initial_node );
			if ( false !== $nodelist ) {
				$elements = DOM::nodelist_to_array( $nodelist );
			}
		}

		return $elements;
	}

	/**
	 * Replaces the given node with HTML content. Uses the HTML5 parser.
	 *
	 * @param \DOMNode $node    The node to replace.
	 * @param string   $content The HTML fragment used to replace the node.
	 *
	 * @return \DOMNode[]|\DOMNode An array of \DOMNode containing the new nodes or the old \DOMNode if the replacement failed.
	 */
	public function replace_node_with_html( \DOMNode $node, $content ) {
		$result = $node;

		$parent = $node->parentNode;
		if ( empty( $parent ) ) {
			return $node; // abort early to save cycles.
		}

		// Encode bare < > & and decode escaped HTML tag.
		$content = RE::unescape_tags( \htmlspecialchars( $content, ENT_NOQUOTES | ENT_HTML5 | ENT_SUBSTITUTE, 'UTF-8', true ) );

		\set_error_handler( [ $this, 'handle_parsing_errors' ] ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler

		/**
		 * Create DOM nodes from HTML fragment.
		 *
		 * @var \DOMNode|false $html_fragment
		 */
		$html_fragment = $this->get_html5_parser()->loadHTMLFragment( $content );
		if ( ! empty( $html_fragment ) ) {
			/**
			 * Import fragment into existing DOM.
			 *
			 * @var \DOMNode|false $imported_fragment
			 */
			$imported_fragment = $node->ownerDocument->importNode( $html_fragment, true );

			if ( ! empty( $imported_fragment ) ) {
				// Save the children of the imported DOMDocumentFragment before replacement.
				$children = DOM::nodelist_to_array( $imported_fragment->childNodes );

				if ( false !== $parent->replaceChild( $imported_fragment, $node ) ) {
					// Success! We return the saved array of DOMNodes as
					// $imported_fragment is just an empty DOMDocumentFragment now.
					$result = $children;
				}
			}
		}

		\restore_error_handler();

		return $result;
	}

	/**
	 * Retrieves the fix registry.
	 *
	 * @return Registry
	 */
	public function get_registry() {
		if ( ! isset( $this->registry ) ) {
			$this->registry = new Default_Registry( $this->get_hyphenator_cache() );
		} elseif ( $this->update_registry_cache ) {
			$this->registry->update_hyphenator_cache( $this->get_hyphenator_cache() );
			$this->update_registry_cache = false;
		}

		return $this->registry;
	}

	/**
	 * Retrieves the HTML5 parser instance.
	 *
	 * @return \Masterminds\HTML5
	 */
	public function get_html5_parser() {
		// Lazy-load HTML5 parser.
		if ( null === $this->html5_parser ) {
			$this->html5_parser = new \Masterminds\HTML5( [ 'disable_html_ns' => true ] );
		}

		return $this->html5_parser;
	}

	/**
	 * Retrieves the hyphenator cache.
	 *
	 * @return Hyphenator\Cache
	 */
	public function get_hyphenator_cache() {
		if ( null === $this->hyphenator_cache ) {
			$this->hyphenator_cache = new Hyphenator\Cache();
		}

		return $this->hyphenator_cache;
	}

	/**
	 * Injects an existing Hyphenator\Cache (to facilitate persistent language caching).
	 *
	 * @param Hyphenator\Cache $cache A hyphenator cache instance.
	 */
	public function set_hyphenator_cache( Hyphenator\Cache $cache ) : void {
		$this->hyphenator_cache = $cache;

		// Change hyphenator cache for existing token fixes.
		if ( isset( $this->registry ) ) {
			$this->registry->update_hyphenator_cache( $cache );
		}
	}

	/**
	 * Retrieves the list of valid language plugins in the given directory.
	 *
	 * @param string $path The path in which to look for language plugin files.
	 *
	 * @return string[] An array in the form ( $language_code => $language_name ).
	 */
	private static function get_language_plugin_list( $path ) {
		$languages = [];

		// Try to open the given directory.
		$handle = \opendir( $path );
		if ( false === $handle ) {
			// Abort.
			return $languages; // @codeCoverageIgnore
		}

		// Read all files in directory.
		$file = \readdir( $handle );
		while ( $file ) {
			// We only want the JSON files.
			if ( '.json' === \substr( $file, -5 ) ) {
				$file_content = (string) \file_get_contents( $path . $file );
				if ( \preg_match( '/"language"\s*:\s*((".+")|(\'.+\'))\s*,/', $file_content, $matches ) ) {
					$language_name = \substr( $matches[1], 1, -1 );
					$language_code = \substr( $file, 0, -5 );

					$languages[ $language_code ] = $language_name;
				}
			}

			// Read next file.
			$file = \readdir( $handle );
		}
		\closedir( $handle );

		// Sort translated language names according to current locale.
		\asort( $languages );

		return $languages;
	}

	/**
	 * Retrieves the list of valid hyphenation languages.
	 *
	 * Note that this method reads all the language files on disc, so you should
	 * cache the results if possible.
	 *
	 * @return string[] An array in the form of ( LANG_CODE => LANGUAGE ).
	 */
	public static function get_hyphenation_languages() {
		return self::get_language_plugin_list( __DIR__ . '/lang/' );
	}

	/**
	 * Retrieves the list of valid diacritic replacement languages.
	 *
	 * Note that this method reads all the language files on disc, so you should
	 * cache the results if possible.
	 *
	 * @return string[] An array in the form of ( LANG_CODE => LANGUAGE ).
	 */
	public static function get_diacritic_languages() {
		return self::get_language_plugin_list( __DIR__ . '/diacritics/' );
	}
}
