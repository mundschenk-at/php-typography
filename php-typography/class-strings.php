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
 * A utility class to handle fast and save string function access.
 */
abstract class Strings {
	/**
	 * An array of encodings in detection order.
	 *
	 * ASCII has to be first to have a chance of detection.
	 *
	 * @var array
	 */
	private static $encodings = [ 'ASCII', 'UTF-8' ];

	/**
	 * A hash map for string functions according to encoding.
	 *
	 * @var array $encoding => [ 'strlen' => $function_name, ... ].
	 */
	private static $str_functions = [
		'UTF-8' => [
			'strlen'     => 'mb_strlen',
			'str_split'  => [ '\PHP_Typography\Strings', 'mb_str_split' ],
			'strtolower' => 'mb_strtolower',
			'strtoupper' => 'mb_strtoupper',
			'substr'     => 'mb_substr',
			'u'          => 'u',
		],
		'ASCII' => [
			'strlen'     => 'strlen',
			'str_split'  => 'str_split',
			'strtolower' => 'strtolower',
			'strtoupper' => 'strtoupper',
			'substr'     => 'substr',
			'u'          => '',
		],
		false   => [],
	];

	/**
	 * Retrieves str* functions.
	 *
	 * @param  string $str A string to detect the encoding from.
	 * @return array {
	 *         An array of string functions.
	 *
	 *         'strlen'     => callable,
	 *         'str_split'  => callable,
	 *         'strtolower' => callable,
	 *         'strtoupper' => callable,
	 *         'substr'     => callable,
	 *         'u'          => modifier string
	 * }
	 */
	public static function functions( $str ) {
		return self::$str_functions[ mb_detect_encoding( $str, self::$encodings, true ) ];
	}

	/**
	 * Multibyte-safe str_split function.
	 *
	 * Unlike str_split, a $split_length less than 1 is ignored (and thus
	 * equivalent to the default).
	 *
	 * @param string $str           Required.
	 * @param int    $split_length  Optional. Default 1.
	 *
	 * @return array                An array of $split_length character chunks.
	 */
	public static function mb_str_split( $str, $split_length = 1 ) {
		$result = preg_split( '//u', $str , -1, PREG_SPLIT_NO_EMPTY );

		if ( $split_length > 1 ) {
			$splits = [];
			foreach ( array_chunk( $result, $split_length ) as $chunk ) {
				$splits[] = join( '', $chunk );
			}

			$result = $splits;
		}

		return $result;
	}

	/**
	 * Converts decimal value to unicode character.
	 *
	 * @param string|array $codes Decimal value(s) coresponding to unicode character(s).
	 *
	 * @return string Unicode character(s).
	 */
	public static function uchr( $codes ) {

		// Single character code.
		if ( is_scalar( $codes ) ) {
			$codes = func_get_args();
		}

		// Deal with an array of character codes.
		$str = '';
		foreach ( $codes as $code ) {
			$str .= self::_uchr( $code );
		}

		return $str;
	}
	/**
	 * Converts decimal value to unicode character.
	 *
	 * For internal use only.
	 *
	 * @param int $code Decimal value coresponding to unicode character.
	 *
	 * @return string Unicode character.
	 */
	public static function _uchr( $code ) {
		return html_entity_decode( '&#' . $code . ';', ENT_NOQUOTES, 'UTF-8' );
	}
}

/**
 * Set UTF-8 as the default encoding for mb_* functions.
 *
 * Not sure if this is necessary - but error_log seems to have problems with
 * the strings otherwise.
 */
mb_internal_encoding( 'UTF-8' );  // @codeCoverageIgnore
