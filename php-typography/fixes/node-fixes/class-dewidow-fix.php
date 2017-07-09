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
use \PHP_Typography\Strings;

/**
 * Prevents widows (if enabled).
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 */
class Dewidow_Fix extends Abstract_Node_Fix {

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
			$chr       = $settings->get_named_characters();
			$textnode->data = preg_replace_callback( $settings->regex( 'dewidow' ), function( array $widow ) use ( $settings, $chr ) {
				$func = Strings::functions( $widow[0] );

				// If we are here, we know that widows are being protected in some fashion
				// with that, we will assert that widows should never be hyphenated or wrapped
				// as such, we will strip soft hyphens and zero-width-spaces.
				$widow['widow']    = str_replace( $chr['zeroWidthSpace'], '', $widow['widow'] ); // TODO: check if this can match here.
				$widow['widow']    = str_replace( $chr['softHyphen'],     '', $widow['widow'] ); // TODO: check if this can match here.
				$widow['trailing'] = preg_replace( "/\s+/{$func['u']}", $chr['noBreakSpace'], $widow['trailing'] );
				$widow['trailing'] = str_replace( $chr['zeroWidthSpace'], '', $widow['trailing'] );
				$widow['trailing'] = str_replace( $chr['softHyphen'],     '', $widow['trailing'] );

				// Eject if widows neighbor is proceeded by a no break space (the pulled text would be too long).
				if ( '' === $widow['space_before'] || strstr( $chr['noBreakSpace'], $widow['space_before'] ) ) {
					return $widow['space_before'] . $widow['neighbor'] . $widow['space_between'] . $widow['widow'] . $widow['trailing'];
				}

				// Eject if widows neighbor length exceeds the max allowed or widow length exceeds max allowed.
				if ( $func['strlen']( $widow['neighbor'] ) > $settings['dewidowMaxPull'] ||
					 $func['strlen']( $widow['widow'] ) > $settings['dewidowMaxLength'] ) {
						return $widow['space_before'] . $widow['neighbor'] . $widow['space_between'] . $widow['widow'] . $widow['trailing'];
				}

				// Never replace thin and hair spaces with &nbsp;.
				switch ( $widow['space_between'] ) {
					case $chr['thinSpace']:
					case $chr['hairSpace']:
						return $widow['space_before'] . $widow['neighbor'] . $widow['space_between'] . $widow['widow'] . $widow['trailing'];
				}

				// Let's protect some widows!
				return $widow['space_before'] . $widow['neighbor'] . $chr['noBreakSpace'] . $widow['widow'] . $widow['trailing'];
			}, $textnode->data );
		}
	}
}
