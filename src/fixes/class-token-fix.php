<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2017-2018 Peter Putzer.
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

namespace PHP_Typography\Fixes;

use PHP_Typography\Settings;
use PHP_Typography\Text_Parser\Token;

/**
 * All fixes that apply to parsed text tokens should implement this interface.
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 */
interface Token_Fix {

	const MIXED_WORDS    = 1;
	const COMPOUND_WORDS = 2;
	const WORDS          = 3;
	const OTHER          = 4;

	/**
	 * Apply the fix to a given set of tokens
	 *
	 * @since 7.0.0 The parameter order has been re-arranged to mirror Node_Fix.
	 *
	 * @param Token[]  $tokens   The set of tokens.
	 * @param \DOMText $textnode The context DOM node.
	 * @param Settings $settings The settings to apply.
	 * @param bool     $is_title Indicates if the processed tokens occur in a title/heading context.
	 *
	 * @return Token[]           The fixed set of tokens.
	 */
	public function apply( array $tokens, \DOMText $textnode, Settings $settings, $is_title );

	/**
	 * Determines whether the fix should be applied to (RSS) feeds.
	 *
	 * @return bool
	 */
	public function feed_compatible();

	/**
	 * Retrieves the target token array for this fix.
	 *
	 * @return int
	 */
	public function target();
}
