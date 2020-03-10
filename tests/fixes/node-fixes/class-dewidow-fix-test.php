<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2015-2019 Peter Putzer.
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  of the License, or ( at your option ) any later version.
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

namespace PHP_Typography\Tests\Fixes\Node_Fixes;

use PHP_Typography\Fixes\Node_Fixes\Dewidow_Fix;
use PHP_Typography\Settings;
use PHP_Typography\U;

/**
 * Dewidow_Fix unit test.
 *
 * @coversDefaultClass \PHP_Typography\Fixes\Node_Fixes\Dewidow_Fix
 * @usesDefaultClass \PHP_Typography\Fixes\Node_Fixes\Dewidow_Fix
 *
 * @uses ::__construct
 * @uses PHP_Typography\DOM
 * @uses PHP_Typography\Settings
 * @uses PHP_Typography\Settings\Dash_Style
 * @uses PHP_Typography\Settings\Quote_Style
 * @uses PHP_Typography\Settings\Simple_Dashes
 * @uses PHP_Typography\Settings\Simple_Quotes
 * @uses PHP_Typography\Strings
 */
class Dewidow_Fix_Test extends Node_Fix_Testcase {
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		parent::setUp();

		$this->fix = new Dewidow_Fix();
	}

	/**
	 * Provide data for testing dewidowing.
	 *
	 * @return array
	 */
	public function provide_dewidow_data() {
		return [
			[ 'bla foo b', 'bla foo&nbsp;b', 3, 2, 1 ],
			[ 'bla foo&thinsp;b', 'bla foo&thinsp;b', 3, 2, 1 ], // don't replace thin space...
			[ 'bla foo&#8202;b', 'bla foo&#8202;b', 3, 2, 1 ],   // ... or hair space.
			[ 'bla foo bar', 'bla foo bar', 2, 2, 1 ],
			[ 'bla foo bär...', 'bla foo&nbsp;b&auml;r...', 3, 3, 1 ],
			[ 'bla foo bär&hellip;!', 'bla foo&nbsp;b&auml;r&hellip;!', 3, 3, 1 ],
			[ 'bla foo bär&shy;!', 'bla foo&nbsp;b&auml;r!', 3, 3, 1 ],
			[ 'bla foo b&shy;är!', 'bla foo&nbsp;b&auml;r!', 3, 3, 1 ],
			[ 'bla foo b&#8203;är!', 'bla foo&nbsp;b&auml;r!', 3, 3, 1 ],
			[ 'bla foo&nbsp;bär...', 'bla foo&nbsp;b&auml;r...', 3, 3, 1 ],
			[ 'bla föö&#8203;bar s', 'bla f&ouml;&ouml;&#8203;bar&nbsp;s', 3, 2, 1 ],
			[ 'bla foo&#8203;bar s', 'bla foo&#8203;bar s', 2, 2, 1 ],
			[ 'bla foo&shy;bar', 'bla foo&shy;bar', 3, 3, 1 ], // &shy; not matched.
			[ 'bla foo&shy;bar bar', 'bla foo&shy;bar&nbsp;bar', 3, 3, 1 ], // &shy; not matched, but syllable after is.
			[ 'bla foo&#8203;bar bar', 'bla foo&#8203;bar&nbsp;bar', 3, 3, 1 ],
			[ 'bla foo&nbsp;bar bar', 'bla foo&nbsp;bar bar', 3, 3, 1 ], // widow not replaced because the &nbsp; would pull too many letters from previous.
			[ 'bla foo bar bar', 'bla foo bar&nbsp;bar', 3, 3, 1 ],
			[ 'bla foo bar bar', 'bla foo bar&nbsp;bar', 3, 3, 2 ],
			[ 'bla foo bar bar', 'bla foo bar&nbsp;bar', 3, 3, 3 ],
			[ 'bla foo bar bar', 'bla foo bar&nbsp;bar', 3, 7, 1 ],
			[ 'bla foo bar bar', 'bla foo&nbsp;bar&nbsp;bar', 3, 7, 2 ],
			[ 'bla foo bar bar', 'bla foo&nbsp;bar&nbsp;bar', 3, 7, 3 ],
			[ 'bla bla foo bar bar', 'bla bla foo bar&nbsp;bar', 3, 11, 1 ],
			[ 'bla bla foo bar bar', 'bla bla foo&nbsp;bar&nbsp;bar', 3, 11, 2 ],
			[ 'bla bla foo bar bar', 'bla bla&nbsp;foo&nbsp;bar&nbsp;bar', 3, 11, 3 ],
			[ 'bla bla foo bar bar', 'bla bla foo&nbsp;bar&nbsp;bar', 3, 11, 2 ],
			[ 'bla bla foo bar&thinsp;bar', 'bla bla foo&nbsp;bar&#8239;bar', 3, 11, 2 ],
			[ 'bla bla foo bar&#8239;bar', 'bla bla foo&nbsp;bar&#8239;bar', 3, 11, 2 ],
		];
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 * @covers ::dewidow
	 *
	 * @uses ::make_space_nonbreaking
	 * @uses ::strip_breaking_characters
	 * @uses ::is_narrow_space
	 *
	 * @dataProvider provide_dewidow_data
	 *
	 * @param string $html         HTML input.
	 * @param string $result       Expected result.
	 * @param int    $max_pull     Maximum number of pulled characters.
	 * @param int    $max_length   Maximum word length for dewidowing.
	 * @param int    $word_number  Maximum number of words in widow.
	 */
	public function test_apply( $html, $result, $max_pull, $max_length, $word_number ) {
		$this->s->set_dewidow( true );
		$this->s->set_max_dewidow_pull( $max_pull );
		$this->s->set_max_dewidow_length( $max_length );
		$this->s->set_dewidow_word_number( $word_number );

		$this->assertFixResultSame( $html, $result );
	}

	/**
	 * Test dewidow.
	 *
	 * @covers ::apply
	 * @covers ::dewidow
	 *
	 * @uses ::make_space_nonbreaking
	 * @uses ::strip_breaking_characters
	 * @uses ::is_narrow_space
	 *
	 * @dataProvider provide_dewidow_data
	 *
	 * @param string $html        HTML input.
	 * @param string $result      Expected result.
	 * @param int    $max_pull    Maximum number of pulled characters.
	 * @param int    $max_length  Maximum word length for dewidowing.
	 * @param int    $word_number Maximum number of words in widow.
	 */
	public function test_apply_off( $html, $result, $max_pull, $max_length, $word_number ) {
		$this->s->set_dewidow( false );
		$this->s->set_max_dewidow_pull( $max_pull );
		$this->s->set_max_dewidow_length( $max_length );
		$this->s->set_dewidow_word_number( $word_number );

		$this->assertFixResultSame( $html, $html );
	}

	/**
	 * Test strip_breaking_characters.
	 *
	 * @covers ::strip_breaking_characters
	 */
	public function test_strip_breaking_characters() {
		$result = $this->invokeStaticMethod( Dewidow_Fix::class, 'strip_breaking_characters', [ 'foo' . U::SOFT_HYPHEN . 'bar' . U::ZERO_WIDTH_SPACE . 'baz' . U::ZERO_WIDTH_SPACE ] );

		$this->assertSame( 'foobarbaz', $result );
	}

	/**
	 * Test make_space_nonbreaking.
	 *
	 * @covers ::make_space_nonbreaking
	 */
	public function test_make_space_nonbreaking() {
		$result = $this->invokeStaticMethod( Dewidow_Fix::class, 'make_space_nonbreaking', [ 'foo' . U::SOFT_HYPHEN . 'bar ' . U::ZERO_WIDTH_SPACE . ' baz' . U::ZERO_WIDTH_SPACE, U::NO_BREAK_SPACE, 'u' ] );

		$this->assertSame( 'foo' . U::SOFT_HYPHEN . 'bar' . U::NO_BREAK_SPACE . U::ZERO_WIDTH_SPACE . U::NO_BREAK_SPACE . 'baz' . U::ZERO_WIDTH_SPACE, $result );
	}

	/**
	 * Test is_narrow_space.
	 *
	 * @covers ::is_narrow_space
	 */
	public function test_is_narrow_space() {
		$this->assertTrue( $this->invokeStaticMethod( Dewidow_Fix::class, 'is_narrow_space', [ U::THIN_SPACE ] ) );
		$this->assertTrue( $this->invokeStaticMethod( Dewidow_Fix::class, 'is_narrow_space', [ U::HAIR_SPACE ] ) );
		$this->assertTrue( $this->invokeStaticMethod( Dewidow_Fix::class, 'is_narrow_space', [ U::NO_BREAK_NARROW_SPACE ] ) );
		$this->assertFalse( $this->invokeStaticMethod( Dewidow_Fix::class, 'is_narrow_space', [ ' ' ] ) );
		$this->assertFalse( $this->invokeStaticMethod( Dewidow_Fix::class, 'is_narrow_space', [ 'xxx' ] ) );
	}
}
