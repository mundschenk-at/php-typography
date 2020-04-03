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

namespace PHP_Typography\Tests\Fixes\Token_Fixes;

use PHP_Typography\Tests\Testcase;
use PHP_Typography\Settings;
use PHP_Typography\Fixes\Token_Fix;

/**
 * Abstract base class for \PHP_Typography\* unit tests.
 */
abstract class Token_Fix_Testcase extends Testcase {

	/**
	 * Settings object.
	 *
	 * @var Settings
	 */
	protected $s;

	/**
	 * Our test object.
	 *
	 * @var Token_Fix
	 */
	protected $fix;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function set_up() {
		parent::set_up();

		$this->s = new Settings( false );
	}

	/**
	 * Assert that the output of the fix is the same as the expected result.
	 *
	 * @param string        $input    Text node value.
	 * @param string        $result   Expected result.
	 * @param bool          $is_title Optional. Default false.
	 * @param \DOMText|null $textnode Optional. Default null.
	 */
	protected function assertFixResultSame( $input, $result, $is_title = false, $textnode = null ) {
		$tokens        = $this->tokenize_sentence( $input );
		$result_tokens = $this->fix->apply( $tokens, $this->s, $is_title, $textnode );
		$this->assert_tokens_same( $result, $result_tokens );
	}
}
