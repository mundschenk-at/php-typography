<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2015-2017 Peter Putzer.
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; either version 2
 *  of the License, or ( at your option ) any later version.
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
 *  @package wpTypography/Tests
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * DOM unit test.
 *
 * @coversDefaultClass \PHP_Typography\Strings
 * @usesDefaultClass \PHP_Typography\Strings
 */
class Strings_Test extends \PHPUnit\Framework\TestCase {

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() { // @codingStandardsIgnoreLine
		$this->typo = new \PHP_Typography\PHP_Typography( false );
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() { // @codingStandardsIgnoreLine
	}

	/**
	 * Provide data for testing uchr.
	 *
	 * @return array
	 */
	public function provide_uchr_data() {
		return [
			[ 33,   '!' ],
			[ 9,    "\t" ],
			[ 10,   "\n" ],
			[ 35,   '#' ],
			[ 103,  'g' ],
			[ 336,  'Ő' ],
			[ 497,  'Ǳ' ],
			[ 1137, 'ѱ' ],
			[ 2000, 'ߐ' ],
		];
	}

	/**
	 * Test uchr.
	 *
	 * @covers \PHP_Typography\uchr
	 * @dataProvider provide_uchr_data
	 *
	 * @param  int    $code   Character code.
	 * @param  string $result Expected result.
	 */
	public function test_uchr( $code, $result ) {
		$this->assertSame( $result, \PHP_Typography\Strings::uchr( $code ) );
	}

	/**
	 * Provide data for testing mb_str_split.
	 *
	 * @return array
	 */
	public function provide_mb_str_split_data() {
		return [
			[ '', 1, 'UTF-8', [] ],
			[ 'A ship', 1, 'UTF-8', [ 'A', ' ', 's', 'h', 'i', 'p' ] ],
			[ 'Äöüß', 1, 'UTF-8', [ 'Ä', 'ö', 'ü', 'ß' ] ],
			[ 'Äöüß', 2, 'UTF-8', [ 'Äö', 'üß' ] ],
			[ 'Äöüß', 0, 'UTF-8', false ],
		];
	}

	/**
	 * Test mb_str_split.
	 *
	 * @covers \PHP_Typography\mb_str_split
	 * @dataProvider provide_mb_str_split_data
	 *
	 * @param  string $string   A multibyte string.
	 * @param  int    $length   Split length.
	 * @param  string $encoding Encoding to use.
	 * @param  array  $result   Expected result.
	 */
	public function test_mb_str_split( $string, $length, $encoding, $result ) {
		$this->assertSame( $result, \PHP_Typography\Strings::mb_str_split( $string, $length, $encoding ) );
	}
}
