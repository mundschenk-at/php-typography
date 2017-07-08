<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2014-2017 Peter Putzer.
 *  Copyright 2009-2011 KINGdesk, LLC.
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; either version 2
 *  of the License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 *  ***
 *
 *  @package wpTypography/PHPTypography
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace PHP_Typography;

/**
 * Some static methods for DOM manipulation.
 *
 * @since 4.2.0
 */
abstract class DOM {

	/**
	 * An array of block tag names.
	 *
	 * @var array
	 */
	private static $block_tags;

	/**
	 * Retrieves an array of block tag names.
	 *
	 * @param bool $reset Optional. Default false.
	 *
	 * @return array
	 */
	public static function block_tags( $reset = false ) {
		if ( empty( self::$block_tags ) || $reset ) {
			self::$block_tags = array_merge(
				array_flip( array_filter( array_keys( \Masterminds\HTML5\Elements::$html5 ), function( $tag ) {
					return \Masterminds\HTML5\Elements::isA( $tag, \Masterminds\HTML5\Elements::BLOCK_TAG );
				} ) ),
				array_flip( [ 'li', 'td', 'dt' ] ) // not included as "block tags" in current HTML5-PHP version.
			);
		}

		return self::$block_tags;
	}


	/**
	 * Converts \DOMNodeList to array;
	 *
	 * @param \DOMNodeList $list Required.
	 *
	 * @return array An associative array in the form ( $spl_object_hash => $node ).
	 */
	public static function nodelist_to_array( \DOMNodeList $list ) {
		$out = [];

		foreach ( $list as $node ) {
			$out[ spl_object_hash( $node ) ] = $node;
		}

		return $out;
	}

	/**
	 * Retrieves an array containing all the ancestors of the node. This could be done
	 * via an XPath query for "ancestor::*", but DOM walking is in all likelyhood faster.
	 *
	 * @param \DOMNode $node Required.
	 *
	 * @return array An array of \DOMNode.
	 */
	public static function get_ancestors( \DOMNode $node ) {
		$result = [];

		while ( ( $node = $node->parentNode ) && ( $node instanceof \DOMElement ) ) { // @codingStandardsIgnoreLine.
			$result[] = $node;
		}

		return $result;
	}

	/**
	 * Checks whether the \DOMNode has one of the given classes.
	 * If $tag is a \DOMText, the parent DOMElement is checked instead.
	 *
	 * @param \DOMNode     $tag        An element or textnode.
	 * @param string|array $classnames A single classname or an array of classnames.
	 *
	 * @return boolean True if the element has any of the given class(es).
	 */
	public static function has_class( \DOMNode $tag, $classnames ) {
		if ( $tag instanceof \DOMText ) {
			$tag = $tag->parentNode; // @codingStandardsIgnoreLine.
		}

		// Bail if we are not working with a tag or if there is no classname.
		if ( ! ( $tag instanceof \DOMElement ) || empty( $classnames ) ) {
			return false;
		}

		// Ensure we always have an array of classnames.
		if ( ! is_array( $classnames ) ) {
			$classnames = [ $classnames ];
		}

		if ( $tag->hasAttribute( 'class' ) ) {
			$tag_classes = array_flip( explode( ' ', $tag->getAttribute( 'class' ) ) );

			foreach ( $classnames as $classname ) {
				if ( isset( $tag_classes[ $classname ] ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Retrieves the last character of the previous \DOMText sibling (if there is one).
	 *
	 * @param \DOMNode $element The content node.
	 *
	 * @return string A single character (or the empty string).
	 */
	public static function get_prev_chr( \DOMNode $element ) {
		$previous_textnode = self::get_previous_textnode( $element );

		if ( isset( $previous_textnode ) && isset( $previous_textnode->data ) ) {
			// First determine encoding.
			$func = Strings::functions( $previous_textnode->data );

			if ( ! empty( $func ) ) {
				return preg_replace( '/\p{C}/Su', '', $func['substr']( $previous_textnode->data, - 1 ) );
			}
		} // @codeCoverageIgnore

		return '';
	}

	/**
	 * Retrieves the first character of the next \DOMText sibling (if there is one).
	 *
	 * @param \DOMNode $element The content node.
	 *
	 * @return string A single character (or the empty string).
	 */
	public static function get_next_chr( \DOMNode $element ) {
		$next_textnode = self::get_next_textnode( $element );

		if ( isset( $next_textnode ) && isset( $next_textnode->data ) ) {
			// First determine encoding.
			$func = Strings::functions( $next_textnode->data );

			if ( ! empty( $func ) ) {
				return preg_replace( '/\p{C}/Su', '', $func['substr']( $next_textnode->data, 0, 1 ) );
			}
		} // @codeCoverageIgnore

		return '';
	}

	/**
	 * Retrieves the previous \DOMText sibling (if there is one).
	 *
	 * @param \DOMNode|null $element Optional. The content node. Default null.
	 *
	 * @return \DOMText|null Null if $element is a block-level element or no text sibling exists.
	 */
	public static function get_previous_textnode( \DOMNode $element = null ) {
		return self::get_adjacent_textnode( function( &$node = null ) {
			$node = $node->previousSibling;
			return self::get_last_textnode( $node );
		}, __METHOD__, $element );
	}

	/**
	 * Retrieves the next \DOMText sibling (if there is one).
	 *
	 * @param \DOMNode|null $element Optional. The content node. Default null.
	 *
	 * @return \DOMText|null Null if $element is a block-level element or no text sibling exists.
	 */
	public static function get_next_textnode( \DOMNode $element = null ) {
		return self::get_adjacent_textnode( function( &$node = null ) {
			$node = $node->nextSibling;
			return self::get_first_textnode( $node );
		}, __METHOD__, $element );
	}

	/**
	 * Retrieves an adjacent \DOMText sibling if there is one.
	 *
	 * @param callable      $iterate             Takes a reference \DOMElement and returns a \DOMText (or null).
	 * @param callable      $get_adjacent_parent Takes a single \DOMElement parameter and returns a \DOMText (or null).
	 * @param \DOMNode|null $element Optional. The content node. Default null.
	 *
	 * @return \DOMText|null Null if $element is a block-level element or no text sibling exists.
	 */
	private static function get_adjacent_textnode( callable $iterate, callable $get_adjacent_parent, \DOMNode $element = null ) {
		if ( ! isset( $element ) ) {
			return null;
		} elseif ( $element instanceof \DOMElement && isset( self::$block_tags[ $element->tagName ] ) ) {
			return null;
		}

		$adjacent = null;
		$node     = $element;

		while ( ! empty( $node ) && empty( $adjacent ) ) {
			$adjacent = $iterate( $node );
		}

		if ( empty( $adjacent ) ) {
			$adjacent = $get_adjacent_parent( $element->parentNode );
		}

		return $adjacent;
	}

	/**
	 * Retrieves the first \DOMText child of the element. Block-level child elements are ignored.
	 *
	 * @param \DOMNode|null $element    Optional. Default null.
	 * @param bool          $recursive  Should be set to true on recursive calls. Optional. Default false.
	 *
	 * @return \DOMText|null The first child of type \DOMText, the element itself if it is of type \DOMText or null.
	 */
	public static function get_first_textnode( \DOMNode $element = null, $recursive = false ) {
		return self::get_edge_textnode( function( \DOMNodeList $children, \DOMText &$first_textnode = null ) {
			$i = 0;

			while ( $i < $children->length && empty( $first_textnode ) ) {
				$first_textnode = self::get_first_textnode( $children->item( $i ), true );
				$i++;
			}
		}, $element, $recursive );
	}

	/**
	 * Retrieves the last \DOMText child of the element. Block-level child elements are ignored.
	 *
	 * @param \DOMNode|null $element   Optional. Default null.
	 * @param bool          $recursive Should be set to true on recursive calls. Optional. Default false.
	 *
	 * @return \DOMText|null The last child of type \DOMText, the element itself if it is of type \DOMText or null.
	 */
	public static function get_last_textnode( \DOMNode $element = null, $recursive = false ) {
		return self::get_edge_textnode( function( \DOMNodeList $children, \DOMText &$last_textnode = null ) {
			$i = $children->length - 1;

			while ( $i >= 0 && empty( $last_textnode ) ) {
				$last_textnode = self::get_last_textnode( $children->item( $i ), true );
				$i--;
			}
		}, $element, $recursive );
	}

	/**
	 * Retrieves an edge \DOMText child of the element specified by the callable.
	 * Block-level child elements are ignored.
	 *
	 * @param callable      $iteration Takes two parameters, a \DOMNodeList and
	 *                                 a reference to the \DOMText used as the result.
	 * @param \DOMNode|null $element   Optional. Default null.
	 * @param bool          $recursive Should be set to true on recursive calls. Optional. Default false.
	 *
	 * @return \DOMText|null The last child of type \DOMText, the element itself if it is of type \DOMText or null.
	 */
	private static function get_edge_textnode( callable $iteration, \DOMNode $element = null, $recursive = false ) {
		if ( ! isset( $element ) ) {
			return null;
		}

		if ( $element instanceof \DOMText ) {
			return $element;
		} elseif ( ! $element instanceof \DOMElement ) {
			// Return null if $element is neither \DOMText nor \DOMElement.
			return null;
		} elseif ( $recursive && isset( self::$block_tags[ $element->tagName ] ) ) {
			return null;
		}

		$edge_textnode = null;

		if ( $element->hasChildNodes() ) {
			$iteration( $element->childNodes, $edge_textnode );
		}

		return $edge_textnode;
	}

	/**
	 * Returns the nearest block-level parent.
	 *
	 * @param \DOMNode $element The node to get the containing block-level tag.
	 *
	 * @return \DOMElement
	 */
	public static function get_block_parent( \DOMNode $element ) {
		$parent = $element->parentNode;

		while ( isset( $parent->tagName ) && ! isset( self::$block_tags[ $parent->tagName ] ) && ! empty( $parent->parentNode ) && $parent->parentNode instanceof \DOMElement ) {
			$parent = $parent->parentNode;
		}

		return $parent;
	}
}

/**
 *  Initialize block tags on load.
 */
DOM::block_tags(); // @codeCoverageIgnore
