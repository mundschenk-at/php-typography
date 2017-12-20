<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2017 Peter Putzer.
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

namespace PHP_Typography\Fixes\Token_Fixes;

use PHP_Typography\Fixes\Token_Fix;
use PHP_Typography\Settings;
use PHP_Typography\Text_Parser\Token;

/**
 * An abstract base class for token fixes.
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 */
abstract class Abstract_Token_Fix implements Token_Fix {

	/**
	 * Is this fix compatible with feeds?
	 *
	 * @var bool
	 */
	private $feed_compatible;

	/**
	 * The target token type.
	 *
	 * @var int
	 */
	private $target;

	/**
	 * Creates a new fix instance.
	 *
	 * @param int  $target          Required.
	 * @param bool $feed_compatible Optional. Default false.
	 */
	protected function __construct( $target, $feed_compatible = false ) {
		$this->target          = $target;
		$this->feed_compatible = $feed_compatible;
	}

	/**
	 * Apply the tweak to a given textnode.
	 *
	 * @param Token[]       $tokens   Required.
	 * @param Settings      $settings Required.
	 * @param bool          $is_title Optional. Default false.
	 * @param \DOMText|null $textnode Optional. Default null.
	 *
	 * @return Token[] An array of tokens.
	 */
	abstract public function apply( array $tokens, Settings $settings, $is_title = false, \DOMText $textnode = null );

	/**
	 * Determines whether the fix should be applied to (RSS) feeds.
	 *
	 * @return bool
	 */
	public function feed_compatible() {
		return $this->feed_compatible;
	}

	/**
	 * Retrieves the target token array for this fix.
	 *
	 * @return int
	 */
	public function target() {
		return $this->target;
	}
}
