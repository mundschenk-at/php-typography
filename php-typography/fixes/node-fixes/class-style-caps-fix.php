<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2017 Peter Putzer.
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

namespace PHP_Typography\Fixes\Node_Fixes;

use \PHP_Typography\DOM;
use \PHP_Typography\Settings;
use \PHP_Typography\U;

/**
 * Wraps words of all caps (may include numbers) in <span class="caps"> if enabled.
 *
 * Call before style_numbers().Only call if you are certain that no html tags have been
 * injected containing capital letters.
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 */
class Style_Caps_Fix extends Simple_Style_Fix {
	/*
	 // \p{Lu} equals upper case letters and should match non english characters; since PHP 4.4.0 and 5.1.0
	 // for more info, see http://www.regextester.com/pregsyntax.html#regexp.reference.unicode
	 $this->components['styleCaps']  = '
	 (?<![\w\-_'.U::ZERO_WIDTH_SPACE.U::SOFT_HYPHEN.'])
	 # negative lookbehind assertion
	 (
	 (?:							# CASE 1: " 9A "
	 [0-9]+					# starts with at least one number
	 \p{Lu}					# must contain at least one capital letter
	 (?:\p{Lu}|[0-9]|\-|_|'.U::ZERO_WIDTH_SPACE.'|'.U::SOFT_HYPHEN.')*
	 # may be followed by any number of numbers capital letters, hyphens, underscores, zero width spaces, or soft hyphens
	 )
	 |
	 (?:							# CASE 2: " A9 "
	 \p{Lu}					# starts with capital letter
	 (?:\p{Lu}|[0-9])		# must be followed a number or capital letter
	 (?:\p{Lu}|[0-9]|\-|_|'.U::ZERO_WIDTH_SPACE.'|'.U::SOFT_HYPHEN.')*
	 # may be followed by any number of numbers capital letters, hyphens, underscores, zero width spaces, or soft hyphens

	 )
	 )
	 (?![\w\-_'.U::ZERO_WIDTH_SPACE.U::SOFT_HYPHEN.'])
	 # negative lookahead assertion
	 '; // required modifiers: x (multiline pattern) u (utf8)
	 */

	// Servers with PCRE compiled without "--enable-unicode-properties" fail at \p{Lu} by returning an empty string (this leaving the screen void of text
	// thus are testing this alternative.
	const REGEX = '/
		(?<![\w\-_' . U::ZERO_WIDTH_SPACE . U::SOFT_HYPHEN . ']) # negative lookbehind assertion
		(
			(?:							# CASE 1: " 9A "
				[0-9]+					# starts with at least one number
				(?:\-|_|' . U::ZERO_WIDTH_SPACE . '|' . U::SOFT_HYPHEN . ')*
						                # may contain hyphens, underscores, zero width spaces, or soft hyphens,
				[A-ZÀ-ÖØ-Ý]				# but must contain at least one capital letter
				(?:[A-ZÀ-ÖØ-Ý]|[0-9]|\-|_|' . U::ZERO_WIDTH_SPACE . '|' . U::SOFT_HYPHEN . ')*
										# may be followed by any number of numbers capital letters, hyphens, underscores, zero width spaces, or soft hyphens
			)
			|
			(?:							# CASE 2: " A9 "
				[A-ZÀ-ÖØ-Ý]				# starts with capital letter
				(?:[A-ZÀ-ÖØ-Ý]|[0-9])	# must be followed a number or capital letter
				(?:[A-ZÀ-ÖØ-Ý]|[0-9]|\-|_|' . U::ZERO_WIDTH_SPACE . '|' . U::SOFT_HYPHEN . ')*
										# may be followed by any number of numbers capital letters, hyphens, underscores, zero width spaces, or soft hyphens
			)
		)
		(?![\w\-_' . U::ZERO_WIDTH_SPACE . U::SOFT_HYPHEN . ']) # negative lookahead assertion
	/xu';

	const SETTINGS_SWITCH = 'styleCaps';

	/**
	 * Creates a new node fix with a class.
	 *
	 * @param string $css_class       HTML class used in markup.
	 * @param bool   $feed_compatible Optional. Default false.
	 */
	public function __construct( $css_class, $feed_compatible = false ) {
		parent::__construct( self::REGEX, self::SETTINGS_SWITCH, $css_class, $feed_compatible );
	}
}
