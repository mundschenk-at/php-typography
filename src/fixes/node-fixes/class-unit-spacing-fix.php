<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2014-2019 Peter Putzer.
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

namespace PHP_Typography\Fixes\Node_Fixes;

use PHP_Typography\Settings;
use PHP_Typography\DOM;
use PHP_Typography\U;

/**
 * Prevents values being split from their units (if enabled).
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 */
class Unit_Spacing_Fix extends Simple_Regex_Replacement_Fix {

	const REPLACEMENT = '$1' . U::NO_BREAK_NARROW_SPACE . '$2';
	const REGEX       = '/(\d\.?)\s(' . self::_STANDARD_UNITS . ')' . self::WORD_BOUNDARY . '/Sxu';

	const _STANDARD_UNITS = '
		### Temporal units
		(?:ms|s|secs?|mins?|hrs?)\.?|
		milliseconds?|seconds?|minutes?|hours?|days?|years?|decades?|century|centuries|millennium|millennia|

		### Imperial units
		(?:in|ft|yd|mi)\.?|
		(?:ac|ha|oz|pt|qt|gal|lb|st)\.?
		s\.f\.|sf|s\.i\.|si|square[ ]feet|square[ ]foot|
		inch|inches|foot|feet|yards?|miles?|acres?|hectares?|ounces?|pints?|quarts?|gallons?|pounds?|stones?|

		### Metric units (with prefixes)
		(?:p|µ|[mcdhkMGT])?
		(?:[mgstAKNJWCVFSTHBL]|mol|cd|rad|Hz|Pa|Wb|lm|lx|Bq|Gy|Sv|kat|Ω)|
		(?:nano|micro|milli|centi|deci|deka|hecto|kilo|mega|giga|tera)?
		(?:liters?|meters?|grams?|newtons?|pascals?|watts?|joules?|amperes?)|

		### Computers units (KB, Kb, TB, Kbps)
		[kKMGT]?(?:[oBb]|[oBb]ps|flops)|

		### Money
		¢|M?(?:£|¥|€|\$)|

		### Other units
		°[CF]? |
		%|pi|M?px|em|en|[NSEOW]|[NS][EOW]|mbar
	'; // required modifiers: x (multiline pattern), u (unicode).

	// (?=\p{^L})|\z) is used instead of \b because otherwise the special symbols ($, € etc.) would not match properly (they are not word characters).
	const WORD_BOUNDARY = '(?:(?=\p{^L})|\z)';

	/**
	 * Creates a new fix object.
	 *
	 * @param bool $feed_compatible Optional. Default false.
	 */
	public function __construct( $feed_compatible = false ) {
		parent::__construct( self::REGEX, self::REPLACEMENT, Settings::UNIT_SPACING, $feed_compatible );
	}

	/**
	 * Apply the fix to a given textnode.
	 *
	 * @since 7.0.0 All parameters are now required.
	 *
	 * @param \DOMText $textnode The DOM node.
	 * @param Settings $settings The settings to apply.
	 * @param bool     $is_title Indicates if the processed tokens occur in a title/heading context.
	 *
	 * @return void
	 */
	public function apply( \DOMText $textnode, Settings $settings, $is_title ) {
		// Update regex with custom units.
		$this->regex = "/(\d\.?)\s({$settings->custom_units()}" . self::_STANDARD_UNITS . ')' . self::WORD_BOUNDARY . '/Sxu';

		parent::apply( $textnode, $settings, $is_title );
	}
}
