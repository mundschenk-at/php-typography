<?php
/**
 *  This file is part of PHP-Typography.
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
 *  @package mundschenk-at/php-typography
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace PHP_Typography\Fixes\Node_Fixes;

use \PHP_Typography\Fixes\Node_Fix;
use \PHP_Typography\Settings;
use \PHP_Typography\Strings;

/**
 * All fixes that apply to textnodes should implement this interface.
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 */
abstract class Abstract_Node_Fix implements Node_Fix {

	/**
	 * Is this fix compatible with feeds?
	 *
	 * @var bool
	 */
	private $feed_compatible;

	/**
	 * Creates a new fix instance.
	 *
	 * @param bool $feed_compatible Optional. Default false.
	 */
	public function __construct( $feed_compatible = false ) {
		$this->feed_compatible = $feed_compatible;
	}

	/**
	 * Apply the fix to a given textnode.
	 *
	 * @param \DOMText $textnode Required.
	 * @param Settings $settings Required.
	 * @param bool     $is_title Optional. Default false.
	 *
	 * @return void
	 */
	abstract public function apply( \DOMText $textnode, Settings $settings, $is_title = false );

	/**
	 * Determines whether the fix should be applied to (RSS) feeds.
	 *
	 * @return bool
	 */
	public function feed_compatible() {
		return $this->feed_compatible;
	}

	/**
	 * Remove adjacent characters from given string.
	 *
	 * @since 4.2.2
	 *
	 * @param  string $string    The string.
	 * @param  string $prev_char Optional. Default ''. The removed character is not required to be the same.
	 * @param  string $next_char Optional. Default ''. The removed character is not required to be the same.
	 *
	 * @return string            The string without `$prev_char` and `$next_char`.
	 */
	protected static function remove_adjacent_characters( $string, $prev_char = '', $next_char = '' ) {
		// Use the most efficient string functions.
		$func = Strings::functions( $string );

		// Remove previous character.
		if ( '' !== $prev_char ) {
			$string = $func['substr']( $string, 1, $func['strlen']( $string ) );
		}

		// Remove next character.
		if ( '' !== $next_char ) {
			$string = $func['substr']( $string, 0, $func['strlen']( $string ) - 1 );
		}

		return $string;
	}
}
