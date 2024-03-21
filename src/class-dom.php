<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2014-2024 Peter Putzer.
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

use Masterminds\HTML5\Elements;

/**
 * Some static methods for DOM manipulation.
 *
 * @since 4.2.0
 */
abstract class DOM {

	/**
	 * A flipped array of block tag names. Checked via isset/has_key.
	 *
	 * @var array<string,int>
	 */
	private static $block_tags;

	/**
	 * A flipped array of tags that should never be modified. Checked via isset/has_key.
	 *
	 * @var array<string,int>
	 */
	private static $inappropriate_tags;

	const ADDITIONAL_INAPPROPRIATE_TAGS = [
		'button',
		'select',
		'optgroup',
		'option',
		'map',
		'head',
		'applet',
		'object',
		'svg',
		'math',
	];

	/**
	 * Retrieves an array of block tags.
	 *
	 * @param bool $reset Optional. Default false.
	 *
	 * @return array<string,int> {
	 *         An array of integer values indexed by tagname.
	 *
	 *         @type int $tag Only the existence of the $tag key is relevant.
	 * }
	 */
	public static function block_tags( $reset = false ) {
		if ( empty( self::$block_tags ) || $reset ) {
			self::$block_tags = \array_merge(
				\array_flip(
					\array_filter(
						\array_keys( Elements::$html5 ),
						function ( $tag ) {
							return Elements::isA( $tag, Elements::BLOCK_TAG );
						}
					)
				),
				\array_flip( [ 'li', 'td', 'dt' ] ) // not included as "block tags" in current HTML5-PHP version.
			);
		}

		return self::$block_tags;
	}

	/**
	 * Retrieves an array of tags that we should never touch.
	 *
	 * @param bool $reset Optional. Default false.
	 *
	 * @return array<string,int> {
	 *         An array of boolean values indexed by tagname.
	 *
	 *         @type int $tag Only the existence of the $tag key is relevant.
	 * }
	 */
	public static function inappropriate_tags( $reset = false ) {
		if ( empty( self::$inappropriate_tags ) || $reset ) {
			self::$inappropriate_tags = \array_flip(
				\array_merge(
					\array_filter(
						\array_keys( Elements::$html5 ),
						function ( $tag ) {
							return Elements::isA( $tag, Elements::VOID_TAG )
								|| Elements::isA( $tag, Elements::TEXT_RAW )
								|| Elements::isA( $tag, Elements::TEXT_RCDATA );
						}
					),
					self::ADDITIONAL_INAPPROPRIATE_TAGS
				)
			);
		}

		return self::$inappropriate_tags;
	}

	/**
	 * Converts a DOMNodeList to array.
	 *
	 * @since 7.0.0 The Parameter $list has been renamed to $node_list.
	 *
	 * @param \DOMNodeList $node_list Required.
	 *
	 * @return array<string,\DOMNode> An associative array in the form ( $spl_object_hash => $node ).
	 *
	 * @phpstan-param \DOMNodeList<\DOMNode> $node_list
	 */
	public static function nodelist_to_array( \DOMNodeList $node_list ) {
		$out = [];

		foreach ( $node_list as $node ) {
			$out[ \spl_object_hash( $node ) ] = $node;
		}

		return $out;
	}

	/**
	 * Retrieves an array containing all the ancestors of the node. This could be done
	 * via an XPath query for "ancestor::*", but DOM walking is in all likelyhood faster.
	 *
	 * @param \DOMNode $node Required.
	 *
	 * @return \DOMNode[] An array of \DOMNode.
	 */
	public static function get_ancestors( \DOMNode $node ) {
		$result = [];

		while ( ( $node = $node->parentNode ) && ( $node instanceof \DOMElement ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
			$result[] = $node;
		}

		return $result;
	}

	/**
	 * Checks whether the \DOMNode has one of the given classes.
	 * If $tag is a \DOMText, the parent DOMElement is checked instead.
	 *
	 * @param \DOMNode        $tag        An element or textnode.
	 * @param string|string[] $classnames A single classname or an array of classnames.
	 *
	 * @return bool True if the element has any of the given class(es).
	 */
	public static function has_class( \DOMNode $tag, $classnames ) {
		if ( $tag instanceof \DOMText ) {
			$tag = $tag->parentNode;
		}

		// Bail if we are not working with a tag or if there is no classname.
		if ( ! ( $tag instanceof \DOMElement ) || empty( $classnames ) ) {
			return false;
		}

		// Ensure we always have an array of classnames.
		if ( ! \is_array( $classnames ) ) {
			$classnames = [ $classnames ];
		}

		if ( $tag->hasAttribute( 'class' ) ) {
			$tag_classes = \array_flip( \explode( ' ', $tag->getAttribute( 'class' ) ) );

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
	 * @param \DOMNode $node The content node.
	 *
	 * @return string A single character (or the empty string).
	 */
	public static function get_prev_chr( \DOMNode $node ) {
		return self::get_adjacent_character( $node, -1, 1, [ __CLASS__, 'get_previous_acceptable_node' ] );
	}

	/**
	 * Retrieves the first character of the next \DOMText sibling (if there is one).
	 *
	 * @param \DOMNode $node The content node.
	 *
	 * @return string A single character (or the empty string).
	 */
	public static function get_next_chr( \DOMNode $node ) {
		return self::get_adjacent_character( $node, 0, 1, [ __CLASS__, 'get_next_acceptable_node' ] );
	}

	/**
	 * Retrieves a character from the given \DOMNode.
	 *
	 * @since 5.0.0
	 * @since 7.0.0 Renamed to `get_adjacent_character`. Parameter `$get_textnode` renamed to `$get_node`
	 *
	 * @param  \DOMNode $node     The starting node.
	 * @param  int      $position The position parameter for `substr`.
	 * @param  int      $length   The length parameter for `substr`.
	 * @param  callable $get_node A function to retrieve the adjacent node from the starting node.
	 *
	 * @return string The character or an empty string.
	 */
	private static function get_adjacent_character( \DOMNode $node, $position, $length, callable $get_node ) {
		$adjacent_node = $get_node( [ __CLASS__, 'is_text_or_linebreak' ], $node );
		$character     = '';

		if ( null !== $adjacent_node ) {
			if ( self::is_linebreak( $adjacent_node ) ) {
				$character = ' ';
			} elseif ( $adjacent_node instanceof \DOMText ) {
				$node_data = $adjacent_node->data;
				$character = \preg_replace( '/\p{C}/Su', '', Strings::functions( $node_data )['substr']( $node_data, $position, $length ) );
			}
		}

		return $character;
	}

	/**
	 * Determines if the node is a <br> element.
	 *
	 * @since 7.0.0
	 *
	 * @param ?\DOMNode $node The node to test.
	 *
	 * @return bool
	 */
	private static function is_linebreak( ?\DOMNode $node ): bool {
		return $node instanceof \DOMElement && ( $node->tagName ?? '' ) === 'br';
	}

	/**
	 * Determines if the node is a textnode.
	 *
	 * @since 7.0.0
	 *
	 * @param ?\DOMNode $node The node to test.
	 *
	 * @return bool
	 */
	private static function is_textnode( ?\DOMNode $node ): bool {
		return $node instanceof \DOMText;
	}

	/**
	 * Determines if the node is a textnode or <br> element.
	 *
	 * @since 7.0.0
	 *
	 * @param ?\DOMNode $node The node to test.
	 *
	 * @return bool
	 */
	private static function is_text_or_linebreak( ?\DOMNode $node ): bool {
		return $node instanceof \DOMText || ( $node instanceof \DOMElement && 'br' === $node->tagName );
	}


	/**
	 * Retrieves the previous \DOMText sibling (if there is one).
	 *
	 * @param \DOMNode|null $node Optional. The content node. Default null.
	 *
	 * @return \DOMText|null Null if $node is a block-level element or no text sibling exists.
	 */
	public static function get_previous_textnode( ?\DOMNode $node ): ?\DOMText {
		$result = self::get_previous_acceptable_node( [ __CLASS__, 'is_textnode' ], $node );

		return $result instanceof \DOMText ? $result : null;
	}

	/**
	 * Retrieves the next \DOMText sibling (if there is one).
	 *
	 * @param \DOMNode|null $node Optional. The content node. Default null.
	 *
	 * @return \DOMText|null Null if $node is a block-level element or no text sibling exists.
	 */
	public static function get_next_textnode( ?\DOMNode $node ): ?\DOMText {
		$result = self::get_next_acceptable_node( [ __CLASS__, 'is_textnode' ], $node );

		return $result instanceof \DOMText ? $result : null;
	}

	/**
	 * Retrieves the previous \DOMText sibling (if there is one).
	 *
	 * @param callable      $is_acceptable Returns true if the \DOMnode is acceptable.
	 * @param \DOMNode|null $node          Optional. The content node. Default null.
	 *
	 * @return \DOMNode|null Null if $node is a block-level element or no text sibling exists.
	 */
	public static function get_previous_acceptable_node( callable $is_acceptable, ?\DOMNode $node ): ?\DOMNode {
		return self::get_adjacent_node(
			$is_acceptable,
			function ( callable $is_node_acceptable, \DOMNode &$another_node ): ?\DOMNode {
				$another_node = $another_node->previousSibling ?? null;
				return self::get_last_acceptable_node( $is_node_acceptable, $another_node );
			},
			[ __CLASS__, __FUNCTION__ ],
			$node
		);
	}

	/**
	 * Retrieves the next \DOMText sibling (if there is one).
	 *
	 * @param callable      $is_acceptable Returns true if the \DOMnode is acceptable.
	 * @param \DOMNode|null $node          Optional. The content node. Default null.
	 *
	 * @return \DOMNode|null Null if $node is a block-level element or no text sibling exists.
	 */
	public static function get_next_acceptable_node( callable $is_acceptable, ?\DOMNode $node ): ?\DOMNode {
		return self::get_adjacent_node(
			$is_acceptable,
			function ( callable $is_node_acceptable, \DOMNode &$another_node ): ?\DOMNode {
				$another_node = $another_node->nextSibling;
				return self::get_first_acceptable_node( $is_node_acceptable, $another_node );
			},
			[ __CLASS__, __FUNCTION__ ],
			$node
		);
	}

	/**
	 * Retrieves an adjacent \DOMText sibling if there is one.
	 *
	 * @since 5.0.0
	 * @since 7.0.0 Renamed to `get_adjacent_node` and refactored to take a callable to determine acceptable nodes.
	 *
	 * @param callable      $is_acceptable       Returns true if the \DOMnode is acceptable.
	 * @param callable      $iterate             Takes a reference \DOMElement and returns a \DOMText (or null).
	 * @param callable      $get_adjacent_parent Takes a single \DOMElement parameter and returns a \DOMText (or null).
	 * @param \DOMNode|null $node                Optional. The content node. Default null.
	 *
	 * @return \DOMNode|null Null if $node is a block-level element or no acceptable sibling exists.
	 */
	private static function get_adjacent_node( callable $is_acceptable, callable $iterate, callable $get_adjacent_parent, \DOMNode $node = null ): ?\DOMNode {
		if ( ! isset( $node ) || self::is_block_tag( $node ) ) {
			return null;
		}

		/**
		 * The result node.
		 *
		 * @var \DOMNode|null
		 */
		$adjacent = null;

		/**
		 * The initial node.
		 *
		 * @var \DOMNode
		 */
		$iterated_node = $node;

		// Iterate to find adjacent node.
		while ( null !== $iterated_node && null === $adjacent ) {
			$adjacent = $iterate( $is_acceptable, $iterated_node );
		}

		// Last ressort.
		if ( null === $adjacent ) {
			$adjacent = $get_adjacent_parent( $is_acceptable, $node->parentNode );
		}

		return $adjacent;
	}

	/**
	 * Retrieves the first \DOMText child of the element. Block-level child elements are ignored.
	 *
	 * @param \DOMNode|null $node      Optional. Default null.
	 * @param bool          $recursive Should be set to true on recursive calls. Optional. Default false.
	 *
	 * @return \DOMText|null The first child of type \DOMText, the element itself if it is of type \DOMText or null.
	 */
	public static function get_first_textnode( \DOMNode $node = null, $recursive = false ) {
		$result = self::get_first_acceptable_node( [ __CLASS__, 'is_textnode' ], $node, $recursive );

		return $result instanceof \DOMText ? $result : null;
	}

	/**
	 * Retrieves the last \DOMText child of the element. Block-level child elements are ignored.
	 *
	 * @param \DOMNode|null $node      Optional. Default null.
	 * @param bool          $recursive Should be set to true on recursive calls. Optional. Default false.
	 *
	 * @return \DOMText|null The last child of type \DOMText, the element itself if it is of type \DOMText or null.
	 */
	public static function get_last_textnode( \DOMNode $node = null, $recursive = false ) {
		$result = self::get_last_acceptable_node( [ __CLASS__, 'is_textnode' ], $node, $recursive );

		return $result instanceof \DOMText ? $result : null;
	}

	/**
	 * Retrieves the first acceptable child of the element. Block-level child elements are ignored.
	 *
	 * @param callable      $is_acceptable Returns true if the \DOMnode is acceptable.
	 * @param \DOMNode|null $node          Optional. Default null.
	 * @param bool          $recursive     Should be set to true on recursive calls. Optional. Default false.
	 *
	 * @return \DOMNode|null The first acceptable child node, the element itself if it is acceptable or null.
	 */
	public static function get_first_acceptable_node( callable $is_acceptable, \DOMNode $node = null, bool $recursive = false ): ?\DOMNode {
		return self::get_edge_node( $is_acceptable, [ __CLASS__, __FUNCTION__ ], $node, $recursive, false );
	}

	/**
	 * Retrieves the last acceptable child of the element. Block-level child elements are ignored.
	 *
	 * @param callable      $is_acceptable Returns true if the \DOMnode is acceptable.
	 * @param \DOMNode|null $node          Optional. Default null.
	 * @param bool          $recursive     Should be set to true on recursive calls. Optional. Default false.
	 *
	 * @return \DOMNode|null The last acceptable child node, the element itself if it is acceptable or null.
	 */
	public static function get_last_acceptable_node( callable $is_acceptable, \DOMNode $node = null, bool $recursive = false ): ?\DOMNode {
		return self::get_edge_node( $is_acceptable, [ __CLASS__, __FUNCTION__ ], $node, $recursive, true );
	}

	/**
	 * Retrieves an edge child of the element specified by the callable.
	 * Block-level child elements are ignored.
	 *
	 * @since 5.0.0
	 * @since 7.0.0 Renamed to `get_edge_node` and refactored to take a callable to determine acceptable nodes.
	 *
	 * @param callable      $is_acceptable       Returns true if the \DOMnode is acceptable.
	 * @param callable      $get_acceptable_node Takes two parameters, a \DOMNode and a boolean flag for recursive calls.
	 * @param \DOMNode|null $node                Optional. Default null.
	 * @param bool          $recursive           Should be set to true on recursive calls. Optional. Default false.
	 * @param bool          $reverse             Whether to iterate forward or backward. Optional. Default false.
	 *
	 * @return \DOMNode|null The last acceptable child, the element itself if it is acceptable or null.
	 */
	private static function get_edge_node( callable $is_acceptable, callable $get_acceptable_node, \DOMNode $node = null, $recursive = false, $reverse = false ): ?\DOMNode {
		if ( $is_acceptable( $node ) ) {
			return $node;
		} elseif ( ! $node instanceof \DOMElement || $recursive && self::is_block_tag( $node ) ) {
			// Return null if $node is neither an acceptable node nor \DOMElement or
			// when we are recursing and already at the block level.
			return null;
		}

		/**
		 * The result node.
		 *
		 * @var \DOMNode|null
		 */
		$acceptable_edge_node = null;

		if ( $node->hasChildNodes() ) {
			$children    = $node->childNodes;
			$max         = $children->length;
			$index       = $reverse ? $max - 1 : 0;
			$incrementor = $reverse ? -1 : +1;

			while ( $index >= 0 && $index < $max && null === $acceptable_edge_node ) {
				$acceptable_edge_node = $get_acceptable_node( $is_acceptable, $children->item( $index ), true );
				$index               += $incrementor;
			}
		}

		return $acceptable_edge_node;
	}

	/**
	 * Returns the nearest block-level parent (or null).
	 *
	 * @param \DOMNode $node Required.
	 *
	 * @return \DOMElement|null
	 */
	public static function get_block_parent( \DOMNode $node ) {
		$parent = $node->parentNode;
		if ( ! $parent instanceof \DOMElement ) {
			return null;
		}

		while ( ! self::is_block_tag( $parent ) && $parent->parentNode instanceof \DOMElement ) {
			/**
			 * The parent is sure to be a \DOMElement.
			 *
			 * @var \DOMElement
			 */
			$parent = $parent->parentNode;
		}

		return $parent;
	}

	/**
	 * Retrieves the tag name of the nearest block-level parent.
	 *
	 * @param \DOMNode $node A node.

	 * @return string The tag name (or the empty string).
	 */
	public static function get_block_parent_name( \DOMNode $node ) {
		$parent = self::get_block_parent( $node );

		if ( ! empty( $parent ) ) {
			return $parent->tagName;
		} else {
			return '';
		}
	}

	/**
	 * Determines if a node is a block tag.
	 *
	 * @since 6.0.0
	 *
	 * @param  \DOMNode $node Required.
	 *
	 * @return bool
	 */
	public static function is_block_tag( \DOMNode $node ) {
		return $node instanceof \DOMElement && isset( self::$block_tags[ $node->tagName ] );
	}
}

/**
 *  Initialize block tags on load.
 */
DOM::block_tags(); // @codeCoverageIgnore
