<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2017-2022 Peter Putzer.
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

namespace PHP_Typography\Settings;

use PHP_Typography\Settings;
use PHP_Typography\U;

/**
 * A factory class for different quote styles.
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 */
abstract class Quote_Style {

	// Valid quote styles.
	const DOUBLE_CURLED              = 'doubleCurled';
	const DOUBLE_CURLED_REVERSED     = 'doubleCurledReversed';
	const DOUBLE_LOW_9               = 'doubleLow9';
	const DOUBLE_LOW_9_REVERSED      = 'doubleLow9Reversed';
	const SINGLE_CURLED              = 'singleCurled';
	const SINGLE_CURLED_REVERSED     = 'singleCurledReversed';
	const SINGLE_LOW_9               = 'singleLow9';
	const SINGLE_LOW_9_REVERSED      = 'singleLow9Reversed';
	const DOUBLE_GUILLEMETS          = 'doubleGuillemets';
	const DOUBLE_GUILLEMETS_REVERSED = 'doubleGuillemetsReversed';
	const DOUBLE_GUILLEMETS_FRENCH   = 'doubleGuillemetsFrench';
	const SINGLE_GUILLEMETS          = 'singleGuillemets';
	const SINGLE_GUILLEMETS_REVERSED = 'singleGuillemetsReversed';
	const CORNER_BRACKETS            = 'cornerBrackets';
	const WHITE_CORNER_BRACKETS      = 'whiteCornerBracket';

	/**
	 * Available quote styles.
	 *
	 * @var array<string,string[]>
	 */
	private static $styles = [
		self::DOUBLE_CURLED              => [
			self::OPEN  => U::DOUBLE_QUOTE_OPEN,
			self::CLOSE => U::DOUBLE_QUOTE_CLOSE,
		],
		self::DOUBLE_CURLED_REVERSED     => [
			self::OPEN  => U::DOUBLE_QUOTE_CLOSE,
			self::CLOSE => U::DOUBLE_QUOTE_CLOSE,
		],
		self::DOUBLE_LOW_9               => [
			self::OPEN  => U::DOUBLE_LOW_9_QUOTE,
			self::CLOSE => U::DOUBLE_QUOTE_CLOSE,
		],
		self::DOUBLE_LOW_9_REVERSED      => [
			self::OPEN  => U::DOUBLE_LOW_9_QUOTE,
			self::CLOSE => U::DOUBLE_QUOTE_OPEN,
		],
		self::SINGLE_CURLED              => [
			self::OPEN  => U::SINGLE_QUOTE_OPEN,
			self::CLOSE => U::SINGLE_QUOTE_CLOSE,
		],
		self::SINGLE_CURLED_REVERSED     => [
			self::OPEN  => U::SINGLE_QUOTE_CLOSE,
			self::CLOSE => U::SINGLE_QUOTE_CLOSE,
		],
		self::SINGLE_LOW_9               => [
			self::OPEN  => U::SINGLE_LOW_9_QUOTE,
			self::CLOSE => U::SINGLE_QUOTE_CLOSE,
		],
		self::SINGLE_LOW_9_REVERSED      => [
			self::OPEN  => U::SINGLE_LOW_9_QUOTE,
			self::CLOSE => U::SINGLE_QUOTE_OPEN,
		],
		self::DOUBLE_GUILLEMETS          => [
			self::OPEN  => U::GUILLEMET_OPEN,
			self::CLOSE => U::GUILLEMET_CLOSE,
		],
		self::DOUBLE_GUILLEMETS_REVERSED => [
			self::OPEN  => U::GUILLEMET_CLOSE,
			self::CLOSE => U::GUILLEMET_OPEN,
		],
		self::DOUBLE_GUILLEMETS_FRENCH   => [
			self::OPEN  => U::GUILLEMET_OPEN . U::NO_BREAK_NARROW_SPACE,
			self::CLOSE => U::NO_BREAK_NARROW_SPACE . U::GUILLEMET_CLOSE,
		],
		self::SINGLE_GUILLEMETS          => [
			self::OPEN  => U::SINGLE_ANGLE_QUOTE_OPEN,
			self::CLOSE => U::SINGLE_ANGLE_QUOTE_CLOSE,
		],
		self::SINGLE_GUILLEMETS_REVERSED => [
			self::OPEN  => U::SINGLE_ANGLE_QUOTE_CLOSE,
			self::CLOSE => U::SINGLE_ANGLE_QUOTE_OPEN,
		],
		self::CORNER_BRACKETS            => [
			self::OPEN  => U::LEFT_CORNER_BRACKET,
			self::CLOSE => U::RIGHT_CORNER_BRACKET,
		],
		self::WHITE_CORNER_BRACKETS      => [
			self::OPEN  => U::LEFT_WHITE_CORNER_BRACKET,
			self::CLOSE => U::RIGHT_WHITE_CORNER_BRACKET,
		],
	];

	/**
	 * Opening quote.
	 *
	 * @internal
	 *
	 * @var int
	 */
	private const OPEN = 0;

	/**
	 * Closing quote.
	 *
	 * @internal
	 *
	 * @var int
	 */
	private const CLOSE = 1;

	/**
	 * Creates a new Quotes object in the given style.
	 *
	 * @since 6.5.0 The $settings parameter has been deprecated.
	 *
	 * @param string   $style    The quote style.
	 * @param Settings $settings The current settings.
	 *
	 * @return Quotes|null Returns null in case of an invalid $style parameter.
	 */
	public static function get_styled_quotes( $style, Settings $settings ) {
		if ( isset( self::$styles[ $style ] ) ) {
			return new Simple_Quotes( self::$styles[ $style ][ self::OPEN ], self::$styles[ $style ][ self::CLOSE ] );
		}

		return null;
	}
}
