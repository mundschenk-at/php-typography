<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2017-2020 Peter Putzer.
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  of the License, or ( at your option ) any  version.
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
 *  @package mundschenk-at/php-typography/tests
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace PHP_Typography\Tests\Hyphenator;

use PHP_Typography\Tests\Testcase;
use PHP_Typography\Hyphenator\Trie_Node;

/**
 * Trie_Node unit test.
 *
 * @coversDefaultClass \PHP_Typography\Hyphenator\Trie_Node
 * @usesDefaultClass \PHP_Typography\Hyphenator\Trie_Node
 */
class Trie_Node_Test extends Testcase {

	/**
	 * Tests build_trie.
	 *
	 * @covers ::build_trie
	 * @covers ::__construct
	 *
	 * @uses ::get_node
	 * @uses PHP_Typography\Strings::mb_str_split
	 *
	 * @return Trie_Node
	 */
	public function test_build_trie() {
		$trie = Trie_Node::build_trie(
			[
				'_aba'  => '00010',
				'_abl'  => '00030',
				'_abo'  => '00002',
				'_abol' => '000300',
				'_abor' => '000100',
				'_abs'  => '00032',
				'_abu'  => '00030',
				'_aden' => '000030',
			]
		);

		$this->assertInstanceOf( Trie_Node::class, $trie );

		return $trie;
	}

	/**
	 * Test exists.
	 *
	 * @covers ::exists
	 * @depends test_build_trie

	 * @param  Trie_Node $trie A trie.
	 *
	 * @return Trie_Node
	 */
	public function test_exists( Trie_Node $trie ) {
		$this->assertTrue( $trie->exists( '_' ) );
		$this->assertFalse( $trie->exists( 'foobar' ) );

		return $trie;
	}

	/**
	 * Test get_node.
	 *
	 * @covers ::get_node
	 * @depends test_build_trie
	 *
	 * @param  Trie_Node $trie A trie.
	 *
	 * @return Trie_Node
	 */
	public function test_get_node( Trie_Node $trie ) {
		$node = $trie->get_node( '_' );

		$this->assertInstanceOf( Trie_Node::class, $node );

		return $trie;
	}

	/**
	 * Test get_node.
	 *
	 * @covers ::get_node
	 * @depends test_get_node
	 *
	 * @uses ::__construct
	 *
	 * @param  Trie_Node $trie A trie.
	 *
	 * @return Trie_Node
	 */
	public function test_get_node_new( Trie_Node $trie ) {
		$node = $trie->get_node( '*' );

		$this->assertInstanceOf( Trie_Node::class, $node );

		return $trie;
	}

	/**
	 * Test offsets.
	 *
	 * @covers ::offsets
	 * @depends test_build_trie
	 *
	 * @uses ::get_node
	 *
	 * @param  Trie_Node $trie A trie.
	 *
	 * @return Trie_Node
	 */
	public function test_offsets( Trie_Node $trie ) {
		$node = $trie->get_node( '_' );
		$node = $node->get_node( 'a' );
		$node = $node->get_node( 'b' );
		$node = $node->get_node( 'a' );

		$this->assertInstanceOf( Trie_Node::class, $node );
		$this->assert_is_array( $node->offsets() );
		$this->assertGreaterThan( 0, count( $node->offsets() ) );

		return $trie;
	}
}
