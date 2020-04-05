<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2016-2020 Peter Putzer.
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

namespace PHP_Typography\Tests\Hyphenator;

use PHP_Typography\Tests\Testcase;

/**
 * Test Hyphenator\Cache class.
 *
 * @coversDefaultClass \PHP_Typography\Hyphenator\Cache
 * @usesDefaultClass \PHP_Typography\Hyphenator\Cache
 *
 * @uses PHP_Typography\Hyphenator
 */
class Cache_Test extends Testcase {
	/**
	 * Hyphenator\Cache fixture.
	 *
	 * @var \PHP_Typography\Hyphenator\Cache|null
	 */
	protected $c;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function set_up() {
		parent::set_up();

		$this->c = new \PHP_Typography\Hyphenator\Cache();
	}

	/**
	 * Tests serialization & the has_changed property.
	 *
	 * @covers ::__sleep
	 * @covers ::has_changed
	 *
	 * @uses ::set_hyphenator
	 * @uses ::get_hyphenator
	 */
	public function test_has_changed() {
		$this->assertFalse( $this->c->has_changed() );
		$this->c->set_hyphenator( 'de', $this->createMock( \PHP_Typography\Hyphenator::class ) );
		$this->assertTrue( $this->c->has_changed() );

		$new_c = unserialize( serialize( $this->c ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize,WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
		$this->assertInstanceOf( \PHP_Typography\Hyphenator\Cache::class, $new_c );
		$this->assertInstanceOf( \PHP_Typography\Hyphenator::class, $new_c->get_hyphenator( 'de' ) );
		$this->assertFalse( $new_c->has_changed() );
	}

	/**
	 * Tests set_hyphenator.
	 *
	 * @covers ::set_hyphenator
	 * @covers ::get_hyphenator
	 */
	public function test_hyphenator_cache() {
		$hyphenator = new \PHP_Typography\Hyphenator();

		$this->assertSame( null, $this->c->get_hyphenator( 'de' ) );
		$this->c->set_hyphenator( 'de', $hyphenator );
		$this->assertSame( $hyphenator, $this->c->get_hyphenator( 'de' ) );
		$this->assertSame( null, $this->c->get_hyphenator( 'foobar' ) );
	}
}
