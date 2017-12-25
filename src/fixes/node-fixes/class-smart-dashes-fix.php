<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2014-2017 Peter Putzer.
 *  Copyright 2009-2011 KINGdesk, LLC.
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
use PHP_Typography\RE;
use PHP_Typography\Settings;
use PHP_Typography\U;

/**
 * Applies smart dashes (if enabled).
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 */
class Smart_Dashes_Fix extends Abstract_Node_Fix {

	// Standard dashes.
	const PARENTHETICAL_DOUBLE_DASH = '/(\s|' . RE::HTML_SPACES . ')--(\s|' . RE::HTML_SPACES . ')/xui'; // ' -- '.
	const PARENTHETICAL_SINGLE_DASH = '/(\s|' . RE::HTML_SPACES . ')-(\s|' . RE::HTML_SPACES . ')/xui';  // ' - '.
	const EN_DASH_WORDS             = '/([\w])\-(' . U::THIN_SPACE . '|' . U::HAIR_SPACE . '|' . U::NO_BREAK_NARROW_SPACE . '|' . U::NO_BREAK_SPACE . ')/u';
	const EN_DASH_NUMBERS           = "/(\b\d+(\.?))\-(\d+\\2)/";
	const EN_DASH_PHONE_NUMBERS     = "/(\b\d{3})" . U::EN_DASH . "(\d{4}\b)/";

	// Date handling.
	const DATE_YYYY_MM_DD = '/
		(
			(?<=\s|\A|' . U::NO_BREAK_SPACE . ')
			[12][0-9]{3}
		)
		[\-' . U::EN_DASH . ']
		(
			(?:[0][1-9]|[1][0-2])
		)
		[\-' . U::EN_DASH . "]
			(
				(?:[0][1-9]|[12][0-9]|[3][0-1])
				(?=\s|\Z|\)|\]|\.|\,|\?|\;|\:|\'|\"|\!|" . U::NO_BREAK_SPACE . ')
		)
	/xu';

	const DATE_MM_DD_YYYY = '/
		(?:
			(?:
				(
					(?<=\s|\A|' . U::NO_BREAK_SPACE . ')
					(?:[0]?[1-9]|[1][0-2])
				)
				[\-' . U::EN_DASH . ']
				(
					(?:[0]?[1-9]|[12][0-9]|[3][0-1])
				)
			)
			|
			(?:
				(
					(?<=\s|\A|' . U::NO_BREAK_SPACE . ')
					(?:[0]?[1-9]|[12][0-9]|[3][0-1])
				)
				[\-' . U::EN_DASH . ']
				(
					(?:[0]?[1-9]|[1][0-2])
				)
			)
		)
		[\-' . U::EN_DASH . "]
		(
			[12][0-9]{3}
			(?=\s|\Z|\)|\]|\.|\,|\?|\;|\:|\'|\"|\!|" . U::NO_BREAK_SPACE . ')
		)
	/xu';

	const DATE_YYYY_MM = '/
		(
			(?<=\s|\A|' . U::NO_BREAK_SPACE . ')
			[12][0-9]{3}
		)
		[\-' . U::EN_DASH . "]
		(
			(?:
				(?:[0][1-9]|[1][0-2])
				|
				(?:[0][0-9][1-9]|[1-2][0-9]{2}|[3][0-5][0-9]|[3][6][0-6])
			)
			(?=\s|\Z|\)|\]|\.|\,|\?|\;|\:|\'|\"|\!|" . U::NO_BREAK_SPACE . ')
		)
	/xu';

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
		$s = $settings->dash_style();

		// Cache textnode content.
		$node_data = $textnode->data;

		$node_data = \str_replace( '---', U::EM_DASH, $node_data );
		$node_data = \preg_replace( self::PARENTHETICAL_DOUBLE_DASH, "\$1{$s->parenthetical_dash()}\$2", $node_data );
		$node_data = \str_replace( '--', U::EN_DASH, $node_data );
		$node_data = \preg_replace( self::PARENTHETICAL_SINGLE_DASH, "\$1{$s->parenthetical_dash()}\$2", $node_data );

		$node_data = \preg_replace( self::EN_DASH_WORDS ,        '$1' . U::EN_DASH . '$2',         $node_data );
		$node_data = \preg_replace( self::EN_DASH_NUMBERS,       "\$1{$s->interval_dash()}\$3",    $node_data );
		$node_data = \preg_replace( self::EN_DASH_PHONE_NUMBERS, '$1' . U::NO_BREAK_HYPHEN . '$2', $node_data ); // phone numbers.
		$node_data = \str_replace( 'xn' . U::EN_DASH,            'xn--',                           $node_data ); // revert messed-up punycode.

		// Revert dates back to original formats
		// YYYY-MM-DD.
		$node_data = \preg_replace( self::DATE_YYYY_MM_DD, '$1-$2-$3',     $node_data );
		// MM-DD-YYYY or DD-MM-YYYY.
		$node_data = \preg_replace( self::DATE_MM_DD_YYYY, '$1$3-$2$4-$5', $node_data );
		// YYYY-MM or YYYY-DDDD next.
		$node_data = \preg_replace( self::DATE_YYYY_MM,    '$1-$2',        $node_data );

		// Restore textnode content.
		$textnode->data = $node_data;
	}
}
