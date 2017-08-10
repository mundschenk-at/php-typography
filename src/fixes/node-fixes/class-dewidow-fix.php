<?php
/**
 *  This file is part of PHP-Typography.
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
 *  @package mundschenk-at/php-typography
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace PHP_Typography\Fixes\Node_Fixes;

use \PHP_Typography\DOM;
use \PHP_Typography\RE;
use \PHP_Typography\Settings;
use \PHP_Typography\Strings;
use \PHP_Typography\U;

/**
 * Prevents widows (if enabled).
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 */
class Dewidow_Fix extends Abstract_Node_Fix {
	const REGEX = '/
		(?:
			\A
			|
			(?:
				(?<space_before>            # subpattern 1: space before (note: ZWSP is not a space)
					[\s' . U::ZERO_WIDTH_SPACE . U::SOFT_HYPHEN . ']+
				)
				(?<neighbor>                # subpattern 2: neighbors widow (short as possible)
					[^\s' . U::ZERO_WIDTH_SPACE . U::SOFT_HYPHEN . ']+?
				)
			)
		)
		(?<space_between>                   # subpattern 3: space between
			[\s]+                           # \s includes all special spaces (but not ZWSP) with the u flag
		)
		(?<widow>                           # subpattern 4: widow
			                                # \w includes all alphanumeric Unicode characters but not composed characters
			[\w\p{M}\-' . U::ZERO_WIDTH_SPACE . U::SOFT_HYPHEN . ']+?
		)
		(?<trailing>                        # subpattern 5: any trailing punctuation or spaces
			[^\w\p{M}]*
		)
		\Z
	/xu';

	/**
	 * Apply the fix to a given textnode.
	 *
	 * @param \DOMText $textnode Required.
	 * @param Settings $settings Required.
	 * @param bool     $is_title Optional. Default false.
	 */
	public function apply( \DOMText $textnode, Settings $settings, $is_title = false ) {
		// Intervening inline tags may interfere with widow identification, but that is a sacrifice of using the parser.
		// Intervening tags will only interfere if they separate the widow from previous or preceding whitespace.
		if ( empty( $settings['dewidow'] ) || empty( $settings['dewidowMaxPull'] ) || empty( $settings['dewidowMaxLength'] ) ) {
			return;
		}

		if ( '' === DOM::get_next_chr( $textnode ) ) {
			// We have the last type "text" child of a block level element.
			$textnode->data = preg_replace_callback( self::REGEX, function( array $widow ) use ( $settings ) {
				$func = Strings::functions( $widow[0] );

				// If we are here, we know that widows are being protected in some fashion
				// with that, we will assert that widows should never be hyphenated or wrapped
				// as such, we will strip soft hyphens and zero-width-spaces.
				$widow['widow']    = str_replace( U::ZERO_WIDTH_SPACE, '', $widow['widow'] );
				$widow['widow']    = str_replace( U::SOFT_HYPHEN,     '', $widow['widow'] );
				$widow['trailing'] = preg_replace( "/\s+/{$func['u']}", U::NO_BREAK_SPACE, $widow['trailing'] );
				$widow['trailing'] = str_replace( U::ZERO_WIDTH_SPACE, '', $widow['trailing'] );
				$widow['trailing'] = str_replace( U::SOFT_HYPHEN,     '', $widow['trailing'] );

				// Eject if widows neighbor is proceeded by a no break space (the pulled text would be too long).
				if ( '' === $widow['space_before'] || strstr( U::NO_BREAK_SPACE, $widow['space_before'] ) ) {
					return $widow['space_before'] . $widow['neighbor'] . $widow['space_between'] . $widow['widow'] . $widow['trailing'];
				}

				// Eject if widows neighbor length exceeds the max allowed or widow length exceeds max allowed.
				if ( $func['strlen']( $widow['neighbor'] ) > $settings['dewidowMaxPull'] ||
					 $func['strlen']( $widow['widow'] ) > $settings['dewidowMaxLength'] ) {
						return $widow['space_before'] . $widow['neighbor'] . $widow['space_between'] . $widow['widow'] . $widow['trailing'];
				}

				// Never replace thin and hair spaces with &nbsp;.
				switch ( $widow['space_between'] ) {
					case U::THIN_SPACE:
					case U::HAIR_SPACE:
						return $widow['space_before'] . $widow['neighbor'] . $widow['space_between'] . $widow['widow'] . $widow['trailing'];
				}

				// Let's protect some widows!
				return $widow['space_before'] . $widow['neighbor'] . U::NO_BREAK_SPACE . $widow['widow'] . $widow['trailing'];
			}, $textnode->data );
		}
	}
}
