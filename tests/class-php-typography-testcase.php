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

namespace PHP_Typography\Tests;

/**
 * Abstract base class for \PHP_Typography\* unit tests.
 */
abstract class PHP_Typography_Testcase extends \PHPUnit\Framework\TestCase {

	/**
	 * Helper function to generate a valid token list from strings.
	 *
	 * @param string $value The string to tokenize.
	 * @param string $type  Optional. Default 'word'.
	 *
	 * @return array
	 */
	protected function tokenize( $value, $type = 'word' ) {
		return [
			[
				'type'  => $type,
				'value' => $value,
			],
		];
	}

	/**
	 * Helper function to generate a valid word token list from strings.
	 *
	 * @param string $value Token value.
	 *
	 * @return array
	 */
	protected function tokenize_sentence( $value ) {
		$words = explode( ' ', $value );
		$tokens = [];

		foreach ( $words as $word ) {
			$tokens[] = [
				'type'  => 'word',
				'value' => $word,
			];
		}

		return $tokens;
	}

	/**
	 * Reports an error identified by $message if the combined token values differ from the expected value.
	 *
	 * @param string $expected_value Either a word or sentence.
	 * @param array  $actual_tokens  A token array.
	 * @param string $message        Optional. Default ''.
	 */
	protected function assertTokensSame( $expected_value, $actual_tokens, $message = '' ) {
		foreach ( $actual_tokens as $index => $token ) {
			$actual_tokens[ $index ]['value'] = clean_html( $actual_tokens[ $index ]['value'] );
		}

		if ( false !== strpos( $expected_value, ' ' ) ) {
			$expected = $this->tokenize_sentence( $expected_value );
		} else {
			$expected = $this->tokenize( $expected_value );
		}

		$this->assertSame( count( $expected ), count( $actual_tokens ) );

		foreach ( $actual_tokens as $key => $token ) {
			$this->assertSame( $expected[ $key ]['value'], $token['value'], $message );
		}

		return true;
	}

	/**
	 * Reports an error identified by $message if $attribute in $object does not have the $key.
	 *
	 * @param string $key       The array key.
	 * @param string $attribute The attribute name.
	 * @param object $object    The object.
	 * @param string $message   Optional. Default ''.
	 */
	protected function assertAttributeArrayHasKey( $key, $attribute, $object, $message = '' ) {
		$ref = new \ReflectionClass( get_class( $object ) );
		$prop = $ref->getProperty( $attribute );
		$prop->setAccessible( true );

		return $this->assertArrayHasKey( $key, $prop->getValue( $object ), $message );
	}

	/**
	 * Reports an error identified by $message if $attribute in $object does have the $key.
	 *
	 * @param string $key       The array key.
	 * @param string $attribute The attribute name.
	 * @param object $object    The object.
	 * @param string $message   Optional. Default ''.
	 */
	protected function assertAttributeArrayNotHasKey( $key, $attribute, $object, $message = '' ) {
		$ref = new \ReflectionClass( get_class( $object ) );
		$prop = $ref->getProperty( $attribute );
		$prop->setAccessible( true );

		return $this->assertArrayNotHasKey( $key, $prop->getValue( $object ), $message );
	}
}
