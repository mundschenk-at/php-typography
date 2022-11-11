<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2017-2022 Peter Putzer.
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

namespace PHP_Typography\Hyphenator;

use PHP_Typography\Strings;

/**
 * A hyphenation pattern trie node.
 *
 * Portions of this code have been inspired by:
 *  - hyphenator-php (https://nikonyrh.github.io/phphyphenation.html)
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 */
final class Trie_Node {

	/**
	 * The offsets array.
	 *
	 * @var array<int,int[]>
	 */
	private $offsets = [];

	/**
	 * Linked trie nodes.
	 *
	 * @var array<string,Trie_Node> {
	 *      @type Trie_Node $char The next node in the given character path.
	 * }
	 */
	private $links = [];

	/**
	 * Create new Trie_Node.
	 */
	private function __construct() {
	}

	/**
	 * Retrieves the node for the given letter (or creates it).
	 *
	 * @param string $char A single character.
	 *
	 * @return Trie_Node
	 */
	public function get_node( $char ) {
		if ( ! isset( $this->links[ $char ] ) ) {
			$this->links[ $char ] = new Trie_Node();
		}

		return $this->links[ $char ];
	}

	/**
	 * Checks if there is a node for the given letter.
	 *
	 * @param string $char A single character.
	 *
	 * @return bool
	 */
	public function exists( $char ) {
		return ! empty( $this->links[ $char ] );
	}

	/**
	 * Retrieves the offsets array.
	 *
	 * @return array<int,int[]>
	 */
	public function offsets() {
		return $this->offsets;
	}

	/**
	 * Builds pattern search trie from pattern list(s).
	 *
	 * @param array<string,string> $patterns An array of hyphenation patterns.
	 *
	 * @return Trie_Node The starting node of the trie.
	 */
	public static function build_trie( array $patterns ) {
		$trie = new Trie_Node();

		foreach ( $patterns as $key => $pattern ) {
			$node = $trie;

			foreach ( Strings::mb_str_split( $key ) as $char ) {
				$node = $node->get_node( $char );
			}

			\preg_match_all( '/([1-9])/S', $pattern, $offsets, \PREG_OFFSET_CAPTURE );
			$node->offsets = $offsets[1]; // @phpstan-ignore-line -- The array contains only ints because of the regex.
		}

		return $trie;
	}
}
