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
 * HTML5 element introspection
 */
require_once( __DIR__ . '/../vendor/Masterminds/HTML5/Elements.php' ); // @codeCoverageIgnore

/**
 * Determines whether two object arrays intersect. The second array is expected
 * to use the spl_object_hash for its keys.
 *
 * @param array $array1 The keys are ignored.
 * @param array $array2 This array has to be in the form ( $spl_object_hash => $object ).
 * @return boolean
 */
function arrays_intersect( array $array1, array $array2 ) {
	foreach ( $array1 as $value ) {
		if ( isset( $array2[ spl_object_hash( $value ) ] ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Converts \DOMNodeList to array;
 *
 * @param \DOMNodeList $list Required.
 *
 * @return array An associative array in the form ( $spl_object_hash => $node ).
 */
function nodelist_to_array( \DOMNodeList $list ) {
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
function get_ancestors( \DOMNode $node ) {
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
function has_class( \DOMNode $tag, $classnames ) {
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
 * Is a number odd?
 *
 * @param ubt $number Required.
 *
 * @return boolean true if $number is odd, false if it is even.
 */
function is_odd( $number ) {
	return (boolean) ( $number % 2 );
}

/**
 * Retrieves the list of valid language plugins in the given directory.
 *
 * @param string $path The path in which to look for language plugin files.
 *
 * @return array An array in the form ( $language_code => $translated_language_name ).
 */
function get_language_plugin_list( $path ) {
	$language_name_pattern = '/"language"\s*:\s*((".+")|(\'.+\'))\s*,/';
	$languages = [];
	$handler = opendir( $path );

	// Read all files in directory.
	while ( $file = readdir( $handler ) ) {
		// We only want the JSON files.
		if ( '.json' === substr( $file, -5 ) ) {
			$file_content = file_get_contents( $path . $file );
			if ( preg_match( $language_name_pattern, $file_content, $matches ) ) {
				$language_name = substr( $matches[1], 1, -1 );
				$language_code = substr( $file, 0, -5 );
				$languages[ $language_code ] = $language_name;
			}
		}
	}
	closedir( $handler );

	// Sort translated language names according to current locale.
	asort( $languages );

	return $languages;
}

/**
 * Calculates binary-safe hash from data object.
 *
 * @param mixed $object Any datatype.
 *
 * @return string
 */
function get_object_hash( $object ) {
	return md5( json_encode( $object ), false );
}

/**
 * Include debugging helpers.
 */
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) { // @codeCoverageIgnoreStart
	define( 'WP_TYPOGRAPHY_DEBUG', true );
}
if ( defined( 'WP_TYPOGRAPHY_DEBUG' ) && WP_TYPOGRAPHY_DEBUG ) {
	include_once 'php-typography-debug.php';
} // @codeCoverageIgnoreEnd
