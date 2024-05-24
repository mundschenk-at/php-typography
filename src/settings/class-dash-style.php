<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2017-2024 Peter Putzer.
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

use PHP_Typography\U;
use PHP_Typography\Exceptions\Invalid_Style_Exception;

/**
 * A factory class for different dash styles.
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 */
abstract class Dash_Style {

	/**
	 * Traditional US dash style (using em dashes).
	 */
	const TRADITIONAL_US = 'traditionalUS';

	/**
	 * "International" dash style (using en dashes).
	 */
	const INTERNATIONAL = 'international';

	/**
	 * "International" dash style (using en dashes), Duden-style (without hair spaces).
	 */
	const INTERNATIONAL_NO_HAIR_SPACES = 'internationalNoHairSpaces';

	/**
	 * Available dash styles.
	 *
	 * @since 7.0.0 Now a private constant instead of a private property.
	 *
	 * @var array<string,string[]>
	 */
	private const STYLES = [
		self::TRADITIONAL_US               => [
			self::PARENTHETICAL       => U::EM_DASH,
			self::PARENTHETICAL_SPACE => U::THIN_SPACE,
			self::INTERVAL            => U::EN_DASH,
			self::INTERVAL_SPACE      => U::THIN_SPACE,
		],
		self::INTERNATIONAL                => [
			self::PARENTHETICAL       => U::EN_DASH,
			self::PARENTHETICAL_SPACE => ' ',
			self::INTERVAL            => U::EN_DASH,
			self::INTERVAL_SPACE      => U::HAIR_SPACE,
		],
		self::INTERNATIONAL_NO_HAIR_SPACES => [
			self::PARENTHETICAL       => U::EN_DASH,
			self::PARENTHETICAL_SPACE => ' ',
			self::INTERVAL            => U::EN_DASH,
			self::INTERVAL_SPACE      => '',
		],
	];

	/**
	 * Interval dash.
	 *
	 * @internal
	 *
	 * @var int
	 */
	private const INTERVAL = 0;

	/**
	 * Interval dash space.
	 *
	 * @internal
	 *
	 * @var int
	 */
	private const INTERVAL_SPACE = 1;

	/**
	 * Parenthetical dash.
	 *
	 * @internal
	 *
	 * @var int
	 */
	private const PARENTHETICAL = 2;

	/**
	 * Parenthetical dash space.
	 *
	 * @internal
	 *
	 * @var int
	 */
	private const PARENTHETICAL_SPACE = 3;

	/**
	 * Creates a new Dashes object in the given style.
	 *
	 * @since 6.5.0 The $settings parameter has been deprecated.
	 * @since 7.0.0 Deprecated parameter $settings removed. The $style parameter
	 *              can optionally now also be a Dashes object.
	 *
	 * @param Dashes|string $style The dash style.
	 *
	 * @return Dashes
	 *
	 * @throws Invalid_Style_Exception An exception is thrown if $style is not a Dashes object nor a valid style.
	 */
	public static function get_styled_dashes( $style ): Dashes {
		if ( $style instanceof Dashes ) {
			return $style;
		} elseif ( isset( self::STYLES[ $style ] ) ) {
			return new Simple_Dashes(
				self::STYLES[ $style ][ self::PARENTHETICAL ],
				self::STYLES[ $style ][ self::PARENTHETICAL_SPACE ],
				self::STYLES[ $style ][ self::INTERVAL ],
				self::STYLES[ $style ][ self::INTERVAL_SPACE ]
			);
		} else {
			throw new Invalid_Style_Exception( "Invalid dash style $style." );
		}
	}
}
