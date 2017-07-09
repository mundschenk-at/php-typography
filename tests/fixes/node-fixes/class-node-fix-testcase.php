<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2017 Peter Putzer.
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

namespace PHP_Typography\Tests\Fixes\Node_Fixes;

use \PHP_Typography\Tests\PHP_Typography_Testcase;
use \PHP_Typography\Settings;
use \PHP_Typography\Fixes\Node_Fix;

/**
 * Abstract base class for \PHP_Typography\* unit tests.
 */
abstract class Node_Fix_Testcase extends PHP_Typography_Testcase {

	/**
	 * Settings object.
	 *
	 * @var Ssttings
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
	protected function setUp() { // @codingStandardsIgnoreLine
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
		// returns < > & to encoded HTML characters (&lt; &gt; and &amp; respectively).
		return new \DOMText( htmlspecialchars( html_entity_decode( $value ), ENT_NOQUOTES, 'UTF-8', false ) );
	}

	/**
	 * Assert that the output of the fix is the same as the expected result.
	 *
	 * @param string $input  Text node value.
	 * @param string $result Expected result.
	 */
	protected function assertFixResultSame( $input, $result ) {
		$node = $this->create_textnode( $input );
		$this->fix->apply( $node, $this->s );
		$this->assertSame( $this->clean_html( $result ), $this->clean_html( $node->data ) );
	}
}
