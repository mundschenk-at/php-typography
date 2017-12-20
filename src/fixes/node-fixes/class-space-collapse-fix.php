<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2014-2017 Peter Putzer.
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

use PHP_Typography\DOM;
use PHP_Typography\RE;
use PHP_Typography\Settings;
use PHP_Typography\U;

/**
 * Collapse spaces (if enabled).
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 */
class Space_Collapse_Fix extends Abstract_Node_Fix {

	const COLLAPSE_NORMAL_SPACES            = '/[' . RE::NORMAL_SPACES . ']+/xu';
	const COLLAPSE_NON_BREAKABLE_SPACES     = '/(?:[' . RE::NORMAL_SPACES . ']|' . RE::HTML_SPACES . ')*' . U::NO_BREAK_SPACE . '(?:[' . RE::NORMAL_SPACES . ']|' . RE::HTML_SPACES . ')*/xu';
	const COLLAPSE_OTHER_SPACES             = '/(?:[' . RE::NORMAL_SPACES . '])*(' . RE::HTML_SPACES . ')(?:[' . RE::NORMAL_SPACES . ']|' . RE::HTML_SPACES . ')*/xu';
	const COLLAPSE_SPACES_AT_START_OF_BLOCK = '/\A(?:[' . RE::NORMAL_SPACES . ']|' . RE::HTML_SPACES . ')+/xu';

	/**
	 * Apply the fix to a given textnode.
	 *
	 * @param \DOMText $textnode Required.
	 * @param Settings $settings Required.
	 * @param bool     $is_title Optional. Default false.
	 */
	public function apply( \DOMText $textnode, Settings $settings, $is_title = false ) {
		if ( empty( $settings['spaceCollapse'] ) ) {
			return;
		}

		// Normal spacing.
		$textnode->data = preg_replace( self::COLLAPSE_NORMAL_SPACES, ' ', $textnode->data );

		// Non-breakable space get's priority. If non-breakable space exists in a string of spaces, it collapses to a single non-breakable space.
		$textnode->data = preg_replace( self::COLLAPSE_NON_BREAKABLE_SPACES, U::NO_BREAK_SPACE, $textnode->data );

		// For any other spaceing, replace with the first occurance of an unusual space character.
		$textnode->data = preg_replace( self::COLLAPSE_OTHER_SPACES, '$1', $textnode->data );

		// Remove all spacing at beginning of block level elements.
		if ( '' === DOM::get_prev_chr( $textnode ) ) { // we have the first text in a block level element.
			$textnode->data = preg_replace( self::COLLAPSE_SPACES_AT_START_OF_BLOCK, '', $textnode->data );
		}
	}
}
