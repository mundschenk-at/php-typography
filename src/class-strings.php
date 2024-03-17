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

use PHP_Typography\Exceptions\Invalid_Encoding_Exception;

/**
 * A utility class to handle fast and save string function access.
 *
 * @since 4.2.0
 * @since 7.0.0 The deprecated static methods `mb_str_split`, and `uchr` have been removed.
 *
 * @phpstan-type String_Functions array{
 *         'strlen'     : callable,
 *         'str_split'  : callable,
 *         'strtolower' : callable,
 *         'strtoupper' : callable,
 *         'substr'     : callable,
 *         'u'          : String
 * }
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
	 * @var string[]
	 */
	const ENCODINGS = [ 'ASCII', 'UTF-8' ];

	/**
	 * A hash map for string functions according to encoding.
	 *
	 * @internal
	 *
	 * @var array{
	 *     'UTF-8' : String_Functions,
	 *     'ASCII' : String_Functions,
	 * }
	 */
	private const STRING_FUNCTIONS = [
		'UTF-8' => [
			'strlen'     => 'mb_strlen',
			'str_split'  => 'mb_str_split',
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
	];

	/**
	 * Retrieves str* functions.
	 *
	 * @param  string $str A string to detect the encoding from.
	 *
	 * @return array
	 *
	 * @throws Invalid_Encoding_Exception Throws an exception if the string is not encoded in ASCII or UTF-8.
	 *
	 * @phpstan-return String_Functions
	 */
	public static function functions( $str ) {
		foreach ( self::ENCODINGS as $encoding ) {
			if ( \mb_check_encoding( $str, $encoding ) ) {
				return self::STRING_FUNCTIONS[ $encoding ];
			}
		}

		throw new Invalid_Encoding_Exception( "String '$str' uses neither ASCII nor UTF-8 encoding." );
	}

	/**
	 * If necessary, split the passed parameters string into an array.
	 *
	 * @param  string[]|string $params Parameters.
	 *
	 * @return string[]
	 */
	public static function maybe_split_parameters( $params ) {
		if ( ! \is_array( $params ) ) {
			// We can safely assume an array here, as long as $params convertible to a string.
			$params = \preg_split( self::RE_PARAMETER_SPLITTING, $params, -1, PREG_SPLIT_NO_EMPTY ) ?: []; // phpcs:ignore Universal.Operators.DisallowShortTernary -- Ensure array type in case of error.
		}

		return $params;
	}
}
