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
}
