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
 * Applies smart dashes (if enabled).
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 */
class Smart_Dashes_Fix extends Abstract_Node_Fix {

	/**
	 * Apply the fix to a given textnode.
	 *
	 * @param \DOMText $textnode Required.
	 * @param Settings $settings Required.
	 * @param bool     $is_title Optional. Default false.
	 */
	public function apply( \DOMText $textnode, Settings $settings, $is_title = false ) {
		if ( empty( $settings['smartDashes'] ) ) {
			return;
		}

		// Various special characters and regular expressions.
		$s     = $settings->dash_style();
		$regex = $settings->get_regular_expressions();

		$textnode->data = str_replace( '---', U::EM_DASH, $textnode->data );
		$textnode->data = preg_replace( $regex['smartDashesParentheticalDoubleDash'], "\$1{$s->parenthetical_dash()}\$2", $textnode->data );
		$textnode->data = str_replace( '--', U::EN_DASH, $textnode->data );
		$textnode->data = preg_replace( $regex['smartDashesParentheticalSingleDash'], "\$1{$s->parenthetical_dash()}\$2", $textnode->data );

		$textnode->data = preg_replace( $regex['smartDashesEnDashWords'] ,       '$1' . U::EN_DASH . '$2',         $textnode->data );
		$textnode->data = preg_replace( $regex['smartDashesEnDashNumbers'],      "\$1{$s->interval_dash()}\$3",    $textnode->data );
		$textnode->data = preg_replace( $regex['smartDashesEnDashPhoneNumbers'], '$1' . U::NO_BREAK_HYPHEN . '$2', $textnode->data ); // phone numbers.
		$textnode->data = str_replace( 'xn' . U::EN_DASH,                        'xn--',                           $textnode->data ); // revert messed-up punycode.

		// Revert dates back to original formats
		// YYYY-MM-DD.
		$textnode->data = preg_replace( $regex['smartDashesYYYY-MM-DD'], '$1-$2-$3',     $textnode->data );
		// MM-DD-YYYY or DD-MM-YYYY.
		$textnode->data = preg_replace( $regex['smartDashesMM-DD-YYYY'], '$1$3-$2$4-$5', $textnode->data );
		// YYYY-MM or YYYY-DDDD next.
		$textnode->data = preg_replace( $regex['smartDashesYYYY-MM'],    '$1-$2',        $textnode->data );
	}
}
