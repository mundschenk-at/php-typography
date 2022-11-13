<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2017-2022 Peter Putzer.
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
use PHP_Typography\RE;

/**
 * Prevents the number part of numbered abbreviations from being split from the basename (if enabled).
 *
 * E.G. "ISO 9000" gets replaced with "ISO&nbsp;9000".
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 */
class Numbered_Abbreviation_Spacing_Fix extends Simple_Regex_Replacement_Fix {
	private const ISO           = 'ISO(?:\/(?:IEC|TR|TS))?';
	private const ABBREVIATIONS = '
		### Internationl standards
		' . self::ISO . '|

		### German standards
		DIN|
		DIN[ ]EN(?:[ ]' . self::ISO . ')?|
		DIN[ ]EN[ ]ISP
		DIN[ ]' . self::ISO . '|
		DIN[ ]IEC|
		DIN[ ]CEN\/TS|
		DIN[ ]CLC\/TS|
		DIN[ ]CWA|
		DIN[ ]VDE|

		LN|VG|VDE|VDI

		### Austrian standards
		ÖNORM|
		ÖNORM[ ](?:A|B|C|E|F|G|H|K|L|M|N|O|S|V|Z)|
		ÖNORM[ ]EN(?:[ ]' . self::ISO . ')?|
		ÖNORM[ ]ETS|

		ÖVE|ONR|

		### Food additives
		E
	'; // required modifiers: x (multiline pattern).

	const REPLACEMENT = '$1' . U::NO_BREAK_SPACE . '$2';
	const REGEX       = '/\b(' . self::ABBREVIATIONS . ')[' . RE::NORMAL_SPACES . ']+([0-9]+)/xu';

	/**
	 * Creates a new fix object.
	 *
	 * @param bool $feed_compatible Optional. Default false.
	 */
	public function __construct( $feed_compatible = false ) {
		parent::__construct( self::REGEX, self::REPLACEMENT, Settings::NUMBERED_ABBREVIATION_SPACING, $feed_compatible );
	}
}
