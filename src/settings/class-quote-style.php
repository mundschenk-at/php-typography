<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2017-2019 Peter Putzer.
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
	 * @var array
	 */
	private static $styles = [
		self::DOUBLE_CURLED              => [
			self::_OPEN  => U::DOUBLE_QUOTE_OPEN,
			self::_CLOSE => U::DOUBLE_QUOTE_CLOSE,
		],
		self::DOUBLE_CURLED_REVERSED     => [
			self::_OPEN  => U::DOUBLE_QUOTE_CLOSE,
			self::_CLOSE => U::DOUBLE_QUOTE_CLOSE,
		],
		self::DOUBLE_LOW_9               => [
			self::_OPEN  => U::DOUBLE_LOW_9_QUOTE,
			self::_CLOSE => U::DOUBLE_QUOTE_CLOSE,
		],
		self::DOUBLE_LOW_9_REVERSED      => [
			self::_OPEN  => U::DOUBLE_LOW_9_QUOTE,
			self::_CLOSE => U::DOUBLE_QUOTE_OPEN,
		],
		self::SINGLE_CURLED              => [
			self::_OPEN  => U::SINGLE_QUOTE_OPEN,
			self::_CLOSE => U::SINGLE_QUOTE_CLOSE,
		],
		self::SINGLE_CURLED_REVERSED     => [
			self::_OPEN  => U::SINGLE_QUOTE_CLOSE,
			self::_CLOSE => U::SINGLE_QUOTE_CLOSE,
		],
		self::SINGLE_LOW_9               => [
			self::_OPEN  => U::SINGLE_LOW_9_QUOTE,
			self::_CLOSE => U::SINGLE_QUOTE_CLOSE,
		],
		self::SINGLE_LOW_9_REVERSED      => [
			self::_OPEN  => U::SINGLE_LOW_9_QUOTE,
			self::_CLOSE => U::SINGLE_QUOTE_OPEN,
		],
		self::DOUBLE_GUILLEMETS          => [
			self::_OPEN  => U::GUILLEMET_OPEN,
			self::_CLOSE => U::GUILLEMET_CLOSE,
		],
		self::DOUBLE_GUILLEMETS_REVERSED => [
			self::_OPEN  => U::GUILLEMET_CLOSE,
			self::_CLOSE => U::GUILLEMET_OPEN,
		],
		self::DOUBLE_GUILLEMETS_FRENCH   => [
			self::_OPEN  => U::GUILLEMET_OPEN . U::NO_BREAK_NARROW_SPACE,
			self::_CLOSE => U::NO_BREAK_NARROW_SPACE . U::GUILLEMET_CLOSE,
		],
		self::SINGLE_GUILLEMETS          => [
			self::_OPEN  => U::SINGLE_ANGLE_QUOTE_OPEN,
			self::_CLOSE => U::SINGLE_ANGLE_QUOTE_CLOSE,
		],
		self::SINGLE_GUILLEMETS_REVERSED => [
			self::_OPEN  => U::SINGLE_ANGLE_QUOTE_CLOSE,
			self::_CLOSE => U::SINGLE_ANGLE_QUOTE_OPEN,
		],
		self::CORNER_BRACKETS            => [
			self::_OPEN  => U::LEFT_CORNER_BRACKET,
			self::_CLOSE => U::RIGHT_CORNER_BRACKET,
		],
		self::WHITE_CORNER_BRACKETS      => [
			self::_OPEN  => U::LEFT_WHITE_CORNER_BRACKET,
			self::_CLOSE => U::RIGHT_WHITE_CORNER_BRACKET,
		],
	];

	/**
	 * Opening quote.
	 *
	 * @internal
	 *
	 * @var int
	 */
	const _OPEN = 0;

	/**
	 * Closing quote.
	 *
	 * @internal
	 *
	 * @var int
	 */
	const _CLOSE = 1;

	/**
	 * Creates a new Quotes object in the given style.
	 *
	 * @since 6.5.0 The $settings parameter has been deprecated.
	 * @since 7.0.0 Deprecated parameter $settings removed.
	 *
	 * @param string $style The quote style.
	 *
	 * @return Quotes|null Returns null in case of an invalid $style parameter.
	 */
	public static function get_styled_quotes( $style ) {
		if ( isset( self::$styles[ $style ] ) ) {
			return new Simple_Quotes( self::$styles[ $style ][ self::_OPEN ], self::$styles[ $style ][ self::_CLOSE ] );
		}

		return null;
	}
}
