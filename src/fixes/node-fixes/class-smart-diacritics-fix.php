<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2017-2018 Peter Putzer.
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

use PHP_Typography\Settings;
use PHP_Typography\DOM;

/**
 * Applies smart diacritics (if enabled).
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 */
class Smart_Diacritics_Fix extends Abstract_Node_Fix {

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
		if ( empty( $settings['smartDiacritics'] ) ) {
			return; // abort.
		}

		if (
			! empty( $settings['diacriticReplacement'] ) &&
			! empty( $settings['diacriticReplacement']['patterns'] ) &&
			! empty( $settings['diacriticReplacement']['replacements'] )
		) {

			// Uses "word" => "replacement" pairs from an array to make fast preg_* replacements.
			$replacements   = $settings['diacriticReplacement']['replacements'];
			$textnode->data = \preg_replace_callback( $settings['diacriticReplacement']['patterns'], function( $match ) use ( $replacements ) {
				if ( isset( $replacements[ $match[0] ] ) ) {
					return $replacements[ $match[0] ];
				} else {
					return $match[0];
				}
			}, $textnode->data );
		}
	}
}
