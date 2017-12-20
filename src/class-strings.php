<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2014-2017 Peter Putzer.
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

/**
 * A utility class to handle fast and save string function access.
 *
 * @since 4.2.0
 */
abstract class Strings {
	/**
	 * Utility patterns for splitting string parameter lists into arrays.
	 *
	 * @internal
	 *
	 * @var string
	 */
	const RE_PARAMETER_SPLITTING = '/[\s,]+/S';

	/**
	 * An array of encodings in detection order.
	 *
	 * ASCII has to be first to have a chance of detection.
	 *
	 * @internal
	 *
	 * @var array
	 */
	const ENCODINGS = [ 'ASCII', 'UTF-8' ];

	/**
	 * A hash map for string functions according to encoding.
	 *
	 * @internal
	 *
	 * @var array $encoding => [ 'strlen' => $function_name, ... ].
	 */
	const STRING_FUNCTIONS = [
		'UTF-8' => [
			'strlen'     => 'mb_strlen',
			'str_split'  => [ __CLASS__, 'mb_str_split' ],
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
		return self::STRING_FUNCTIONS[ \mb_detect_encoding( $str, self::ENCODINGS, true ) ];
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
		$result = \preg_split( '//u', $str , -1, PREG_SPLIT_NO_EMPTY );

		if ( $split_length > 1 ) {
			$splits = [];
			foreach ( \array_chunk( $result, $split_length ) as $chunk ) {
				$splits[] = \join( '', $chunk );
			}

			$result = $splits;
		}

		return $result;
	}

	/**
	 * Converts decimal value to unicode character.
	 *
	 * @param int|string|array $codes Decimal value(s) coresponding to unicode character(s).
	 *
	 * @return string Unicode character(s).
	 */
	public static function uchr( $codes ) {

		// Single character code.
		if ( \is_scalar( $codes ) ) {
			$codes = \func_get_args();
		}

		// Deal with an array of character codes.
		$json = '"';
		foreach ( $codes as $code ) {
			$json .= sprintf( '\u%04x', $code );
		}
		$json .= '"';

		return \json_decode( $json );
	}

	/**
	 * If necessary, split the passed parameters string into an array.
	 *
	 * @param  array|string $params Parameters.
	 *
	 * @return array
	 */
	public static function maybe_split_parameters( $params ) {
		if ( ! \is_array( $params ) ) {
			$params = \preg_split( self::RE_PARAMETER_SPLITTING, $params, -1, PREG_SPLIT_NO_EMPTY );
		}

		return $params;
	}
}
