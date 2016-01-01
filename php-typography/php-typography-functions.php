<?php

/**
 *  This file is part of wp-Typography.
 *
 *	Copyright 2014-2015 Peter Putzer.
 *	Copyright 2012-2013 Marie Hogebrandt.
 *	Coypright 2009-2011 KINGdesk, LLC.
 *
 *	This program is free software; you can redistribute it and/or
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
 *  @author Jeffrey D. King <jeff@kingdesk.com>
 *  @author Peter Putzer <github@mundschenk.at>
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */


namespace PHP_Typography;

/**
 * HTML5 element introspection
 */
require_once( __DIR__ . '/../vendor/Masterminds/HTML5/Elements.php' ); // @codeCoverageIgnore

/**
 * Retrieves intersection of two object arrays using strict comparison.
 *
 * @param array $array1
 * @param array $array2
 * @return array An array that contains the common elements of the two input arrays.
 */
function array_intersection( array $array1, array $array2 ) {
	$max = count( $array1 );

	$out = array();
	for ( $i = 0; $i < $max; ++$i ) {
		if ( in_array( $array1[ $i ], $array2, true ) ) {
			$out[] = $array1[ $i ];
		}
	}

	return $out;
}

/**
 * Convert \DOMNodeList to array;
 *
 * @param \DOMNodeList $list
 * @return array An array of \DOMNodes.
 */
function nodelist_to_array( \DOMNodeList $list ) {
	$out = array();

	foreach ( $list as $node ) {
		$out[] = $node;
	}

	return $out;
}

/**
 * Retrieve an array containing all the ancestors of the node.
 *
 * @param \DOMNode $node
 * @return array An array of \DOMNode.
 */
function get_ancestors( \DOMNode $node ) {
	$result = array();

	while ( $node = $node->parentNode ) {
		$result[] = $node;
	}

	return $result;
}

/**
 * Checks whether the \DOMNode has one of the given classes.
 * If $tag is a \DOMText, the parent DOMElement is checked instead.
 *
 * @param \DOMNode $tag An element or textnode.
 * @param string|array $classnames A single classname or an array of classnames.
 *
 * @return boolean True if the element has the given class(es).
 */
function has_class( \DOMNode $tag, $classnames ) {
	if ( $tag instanceof \DOMText ) {
		$tag = $tag->parentNode;
	}

	// bail if we are not working with a tag or if there is no classname
	if ( ! ( $tag instanceof \DOMElement ) || empty( $classnames ) ) {
		return false;
	}

	// ensure we always have an array of classnames
	if ( ! is_array( $classnames ) ) {
		$classnames = array( $classnames );
	}

	if ( $tag->hasAttribute( 'class' ) ) {
		$tag_classes = array_flip( explode(' ', $tag->getAttribute( 'class' ) ) );

		foreach ( $classnames as $classname ) {
			if ( isset( $tag_classes[ $classname ] ) ) {
				return true;
			}
		}
	}

	return false;
}

/**
 * Convert decimal value to unicode character.
 *
 * @param string|array $codes Decimal value(s) coresponding to unicode character(s).
 * @return string Unicode character(s).
 */
function uchr( $codes ) {
	if ( is_scalar( $codes ) ) {
		$codes = func_get_args();
	}

	$str= '';
	foreach ( $codes as $code ) {
		$str .= html_entity_decode( '&#' . $code . ';', ENT_NOQUOTES, 'UTF-8' );
	}

	return $str;
}

/**
 * Is a number odd?
 *
 * @param integer $number
 * @return boolean true if $number is odd, false if it is even.
 */
function is_odd( $number ) {
	return (boolean) ( $number % 2 );
}

/**
 * Multibyte-safe str_split function.
 *
 * @param string $str
 * @param int    $length Optional. Default 1.
 * @param string $encoding Optional. Default 'UTF-8'.
 */
function mb_str_split( $str, $length = 1, $encoding = 'UTF-8' ) {
	if ( $length < 1 ) {
		return false;
	}

	$result = array();
	$multibyte_length = mb_strlen( $str, $encoding );
	for ( $i = 0; $i < $multibyte_length; $i += $length ) {
		$result[] = mb_substr( $str, $i, $length, $encoding );
	}

	return $result;
}


/**
 * Retrieve the supported hyphenation languages.
 *
 * @return array An associative array in the form array( language code => language name )
 */
function get_hyphenation_languages() {
	static $hyphenation_language_name_untranslated = '/\$patgenLanguage\s*=\s*((".+")|(\'.+\'))\s*;/';
	static $hyphenation_language_name_translated   = '/\$patgenLanguage\s*=\s*__\(\s*((".+")|(\'.+\'))\s*,\s*((".+")|(\'.+\'))\s*\)\s*;/';

	$languages = array();
	$langDir = dirname( __FILE__ ) . '/lang/';
	$handler = opendir( $langDir );

	// read all files in directory
	while ( $file = readdir( $handler ) ) {
		// we only want the php files
		if ('.php' == substr( $file, -4 ) ) {
			$file_content = file_get_contents( $langDir . $file );

			preg_match( $hyphenation_language_name_untranslated, $file_content, $matches );
			if ( ! isset($matches[1]) ) {
				// maybe the language name is being translated
				preg_match( $hyphenation_language_name_translated, $file_content, $matches );
			}
			$language_name = __( substr( $matches[1], 1, -1 ), 'wp-typography' ); // normally this doesn't work, but we may have added the
			// language name in the patgen file already.
			$language_code = substr( $file, 0, -4 );
			$languages[ $language_code ] = $language_name;
		}
	}
	closedir( $handler );

	asort( $languages );
	return $languages;
}

/**
 * Retrieve the supported diacritics replacement languages.
 *
 * @return array An associative array in the form array( language code => language name )
 */
function get_diacritic_languages() {
	static $diacritic_language_name_untranslated = '/\$diacriticLanguage\s*=\s*((".+")|(\'.+\'))\s*;/';
	static $diacritic_language_name_translated   = '/\$diacriticLanguage\s*=\s*__\(\s*((".+")|(\'.+\'))\s*,\s*((".+")|(\'.+\'))\s*\)\s*;/';

	$languages = array();
	$lang_dir = dirname( __FILE__ ) . '/diacritics/';
	$handler = opendir( $lang_dir );

	// read all files in directory
	while ( $file = readdir( $handler ) ) {
		// we only want the php files
		if ('.php' == substr( $file, -4 ) ) {
			$file_content = file_get_contents( $lang_dir.$file );
			preg_match( $diacritic_language_name_untranslated, $file_content, $matches );
			if ( ! isset($matches[1]) ) {
				// maybe the language name is being translated
				preg_match( $diacritic_language_name_translated, $file_content, $matches );
			}
			$language_name = __( substr( $matches[1], 1, -1 ), 'wp-typography' ); // normally this doesn't work, but we may have added the
			// language name in the patgen file already.
			$language_code = substr( $file, 0, -4 );
			$languages[ $language_code ] = $language_name;
		}
	}
	closedir( $handler );

	asort( $languages );
	return $languages;
}

/**
 * Uses "word" => "replacement" pairs from an array to make fast preg_* replacements.
 *
 * @param string $source
 * @param string|array $pattern Either a regex pattern or an array of such patterns.
 * @param array $words A hash in the form "plain word" => "word with diacritics"
 *
 * @return string The result string.
 */
function translate_words( $source, $patterns, array $words ) {
	return preg_replace_callback( $patterns, function( $match ) use ( $words ) {
		if ( isset( $words[ $match[0] ] ) ) {
			return $words[ $match[0] ];
		} else {
			return $match[0];
		}
	}, $source );
}

/**
 * Include debugging helpers
 */
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) { // @codeCoverageIgnoreStart
	define( 'WP_TYPOGRAPHY_DEBUG', true );
}
if ( defined( 'WP_TYPOGRAPHY_DEBUG' ) && WP_TYPOGRAPHY_DEBUG ) {
	include_once 'php-typography-debug.php';
} // @codeCoverageIgnoreEnd
