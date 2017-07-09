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

use \PHP_Typography\Settings;
use \PHP_Typography\DOM;

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

	/**
	 * Apply the fix to a given textnode.
	 *
	 * @param \DOMText $textnode Required.
	 * @param Settings $settings Required.
	 * @param bool     $is_title Optional. Default false.
	 */
	public function apply( \DOMText $textnode, Settings $settings, $is_title = false ) {
		if ( empty( $settings['frenchPunctuationSpacing'] ) ) {
			return;
		}

		// Various special characters and regular expressions.
		$chr   = $settings->get_named_characters();
		$regex = $settings->get_regular_expressions();

		$textnode->data = preg_replace( $regex['frenchPunctuationSpacingClosingQuote'], '$1' . $chr['noBreakNarrowSpace'] . '$3$4', $textnode->data );
		$textnode->data = preg_replace( $regex['frenchPunctuationSpacingNarrow'],       '$1' . $chr['noBreakNarrowSpace'] . '$3$4', $textnode->data );
		$textnode->data = preg_replace( $regex['frenchPunctuationSpacingFull'],         '$1' . $chr['noBreakSpace'] . '$3$4',       $textnode->data );
		$textnode->data = preg_replace( $regex['frenchPunctuationSpacingSemicolon'],    '$1' . $chr['noBreakNarrowSpace'] . '$3$4', $textnode->data );
		$textnode->data = preg_replace( $regex['frenchPunctuationSpacingOpeningQuote'], '$1$2' . $chr['noBreakNarrowSpace'] . '$4', $textnode->data );
	}
}
