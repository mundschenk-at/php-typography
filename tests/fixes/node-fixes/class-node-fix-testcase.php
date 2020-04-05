<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2017-2020 Peter Putzer.
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

use PHP_Typography\Tests\Testcase;

use PHP_Typography\Fixes\Node_Fix;
use PHP_Typography\RE;
use PHP_Typography\Settings;

/**
 * Abstract base class for \PHP_Typography\* unit tests.
 */
abstract class Node_Fix_Testcase extends Testcase {

	/**
	 * Settings object.
	 *
	 * @var Settings
	 */
	protected $s;

	/**
	 * Our test object.
	 *
	 * @var Node_Fix
	 */
	protected $fix;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function set_up() {
		$this->s = new Settings( true );
	}

	/**
	 * Create a normalilzed textnode.
	 *
	 * @param  string $value Required.
	 *
	 * @return \DOMText
	 */
	protected function create_textnode( $value ) {
		return new \DOMText( html_entity_decode( $value ) );
	}

	/**
	 * Assert that the output of the fix is the same as the expected result.
	 *
	 * @param string      $input         Text node value.
	 * @param string      $result        Expected result.
	 * @param string|null $left_sibling  Optional. Left sibling node value. Default null.
	 * @param string|null $right_sibling Optional. Right sibling node value. Default null.
	 * @param string      $parent_tag    Optional. Parent tag. Default 'p'.
	 * @param bool        $is_title      Optional. Default false.
	 */
	protected function assertFixResultSame( $input, $result, $left_sibling = null, $right_sibling = null, $parent_tag = 'p', $is_title = false ) {
		$node = $this->create_textnode( $input );

		if ( ! empty( $left_sibling ) || ! empty( $right_sibling ) ) {
			$dom    = new \DOMDocument();
			$parent = new \DOMElement( $parent_tag );
			$dom->appendChild( $parent );

			if ( ! empty( $left_sibling ) ) {
				$parent->appendChild( $this->create_textnode( $left_sibling ) );
			}

			$parent->appendChild( $node );

			if ( ! empty( $right_sibling ) ) {
				$parent->appendChild( $this->create_textnode( $right_sibling ) );
			}
		}

		$this->fix->apply( $node, $this->s, $is_title );
		$this->assertSame( $this->clean_html( $result ), $this->clean_html( str_replace( [ RE::ESCAPED_HTML_OPEN, RE::ESCAPED_HTML_CLOSE ], [ '<', '>' ], $node->data ) ) );
	}
}
