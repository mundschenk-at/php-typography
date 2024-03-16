<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2019-2024 Peter Putzer.
 *
 *  This program is free software; you can redistribute it and/or modify modify
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

use PHP_Typography\DOM;
use PHP_Typography\Settings;
use PHP_Typography\U;

/**
 * Applies smart area units (if enabled).
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 */
class Smart_Area_Units_Fix extends Abstract_Node_Fix {

	const LENGTH_UNITS = '(?:p|µ|[mcdhkMGT])?m'; // Just metric for now.
	const NUMBER       = '[0-9]+(?:\.,)?[0-9]*';
	const WHITESPACE   = '\s*';

	const AREA_UNITS   = '/\b(' . self::NUMBER . ')(' . self::WHITESPACE . ')(' . self::LENGTH_UNITS . ')2\b/Su';
	const VOLUME_UNITS = '/\b(' . self::NUMBER . ')(' . self::WHITESPACE . ')(' . self::LENGTH_UNITS . ')3\b/Su';

	/**
	 * Apply the fix to a given textnode.
	 *
	 * @param \DOMText $textnode Required.
	 * @param Settings $settings Required.
	 * @param bool     $is_title Optional. Default false.
	 */
	public function apply( \DOMText $textnode, Settings $settings, $is_title = false ) {
		if ( empty( $settings[ Settings::SMART_AREA_UNITS ] ) ) {
			return;
		}

		$textnode->data = (string) \preg_replace(
			[ self::AREA_UNITS, self::VOLUME_UNITS ],
			[ '$1 $3²', '$1 $3³' ],
			$textnode->data
		);
	}
}
