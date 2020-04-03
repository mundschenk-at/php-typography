<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2015-2020 Peter Putzer.
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

namespace PHP_Typography\Tests;

use PHP_Typography\RE;

/**
 * RE unit test.
 *
 * @coversDefaultClass \PHP_Typography\RE
 * @usesDefaultClass \PHP_Typography\RE
 */
class RE_Test extends Testcase {

	/**
	 * Tests top_level_domains.
	 *
	 * @covers ::top_level_domains
	 *
	 * @uses ::get_top_level_domains_from_file
	 */
	public function test_top_level_domains() {
		$result = $this->invoke_static_method( RE::class, 'top_level_domains', [] );

		$this->assert_is_string( $result, 'RE::top_level_domains() should return a string.' );
		$this->assertGreaterThan( 0, strlen( $result ) );
	}

	/**
	 * Tests top_level_domains.
	 *
	 * @covers ::top_level_domains
	 *
	 * @uses ::get_top_level_domains_from_file
	 */
	public function test_top_level_domains_clean() {
		// Unset RE::$top_level_domains_pattern.
		$this->set_static_value( RE::class, 'top_level_domains_pattern', null );

		$result = $this->invoke_static_method( RE::class, 'top_level_domains', [] );

		$this->assert_is_string( $result, 'RE::top_level_domains() should return a string.' );
		$this->assertGreaterThan( 0, strlen( $result ) );
	}

	/**
	 * Tests get_top_level_domains_from_file.
	 *
	 * @covers ::get_top_level_domains_from_file
	 */
	public function test_get_top_level_domains_from_file() {
		$default = 'ac|ad|aero|ae|af|ag|ai|al|am|an|ao|aq|arpa|ar|asia|as|at|au|aw|ax|az|ba|bb|bd|be|bf|bg|bh|biz|bi|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|cat|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|com|coop|co|cr|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gg|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|im|info|int|in|io|iq|ir|is|it|je|jm|jobs|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|me|mg|mh|mil|mk|ml|mm|mn|mobi|mo|mp|mq|mr|ms|mt|museum|mu|mv|mw|mx|my|mz|name|na|nc|net|ne|nf|ng|ni|nl|no|np|nr|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pro|pr|ps|pt|pw|py|qa|re|ro|rs|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tel|tf|tg|th|tj|tk|tl|tm|tn|to|tp|travel|tr|tt|tv|tw|tz|ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw';

		$invalid_result = $this->invoke_static_method( RE::class, 'get_top_level_domains_from_file', [ '/some/invalid/path/to_a_non_existent_file.txt' ] );
		$valid_result   = $this->invoke_static_method( RE::class, 'get_top_level_domains_from_file', [ dirname( __DIR__ ) . '/src/IANA/tlds-alpha-by-domain.txt' ] );

		$this->assertSame( $default, $invalid_result );
		$this->assertNotSame( $valid_result, $invalid_result );
		$this->assertNotEmpty( $valid_result );
	}

	/**
	 * Tests escape_tags/unescape tags.
	 *
	 * @covers ::escape_tags
	 * @covers ::unescape_tags
	 */
	public function test_escape_tags() {
		$tags = '<a><real>tag soup</a>';

		$escaped = RE::escape_tags( $tags );
		$this->assertNotSame( $escaped, $tags );
		$this->assertSame( $tags, RE::unescape_tags( $escaped ) );

		// Multiple applications should not matter.
		$this->assertSame( $escaped, RE::escape_tags( $escaped ) );
		$this->assertSame( $tags, RE::unescape_tags( $tags ) );
	}
}
