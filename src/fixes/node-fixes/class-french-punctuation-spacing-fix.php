<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2016-2019 Peter Putzer.
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
use PHP_Typography\Strings;
use PHP_Typography\U;

/**
 * Adds a narrow no-break space before
 * - exclamation mark (!)
 * - question mark (?)
 * - semicolon (;)
 * - colon (:)
 *
 * If there already is a space there, it is replaced.
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 */
class French_Punctuation_Spacing_Fix extends Abstract_Node_Fix {
	// Regular expressions with mandatary Unicode modifier.
	const INSERT_NARROW_SPACE               = '/(\w+(?:\s?»)?)(\s?)([?!;])(\s|\Z)/u';
	const INSERT_FULL_SPACE                 = '/(\w+(?:\s?»)?)(\s?)(:)(\s|\Z)/u';
	const INSERT_SPACE_AFTER_OPENING_QUOTE  = '/(\s|\A|[\(\[])(«)(\s?)(\w+)/u';
	const INSERT_SPACE_BEFORE_CLOSING_QUOTE = '/(\w+[.?!]?)(\s?)(»)(\s|[.,?!:\)\]]|\Z)/u';

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
		if ( empty( $settings[ Settings::FRENCH_PUNCTUATION_SPACING ] ) ) {
			return;
		}

		// Need to get context of adjacent characters outside adjacent inline tags or HTML comment
		// if we have adjacent characters add them to the text.
		$previous_character = DOM::get_prev_chr( $textnode );
		$next_character     = DOM::get_next_chr( $textnode );
		$node_data          = "{$previous_character}{$textnode->data}"; // $next_character is not included on purpose.
		$f                  = Strings::functions( "{$node_data}{$next_character}" ); // Include $next_character for determining encodiing.

		$node_data = \preg_replace(
			[
				self::INSERT_SPACE_BEFORE_CLOSING_QUOTE,
				self::INSERT_NARROW_SPACE,
				self::INSERT_FULL_SPACE,
			],
			[
				'$1' . U::NO_BREAK_NARROW_SPACE . '$3$4',
				'$1' . U::NO_BREAK_NARROW_SPACE . '$3$4',
				'$1' . U::NO_BREAK_SPACE . '$3$4',
			],
			$node_data
		);

		// The next rule depends on the following characters as well.
		$node_data = \preg_replace( self::INSERT_SPACE_AFTER_OPENING_QUOTE,  '$1$2' . U::NO_BREAK_NARROW_SPACE . '$4', "{$node_data}{$next_character}" );

		// If we have adjacent characters remove them from the text.
		$textnode->data = self::remove_adjacent_characters( $node_data, $f['strlen'], $f['substr'], $f['strlen']( $previous_character ), $f['strlen']( $next_character ) );
	}
}
