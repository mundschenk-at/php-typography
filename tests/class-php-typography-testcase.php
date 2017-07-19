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
	 * Return encoded HTML string (everything except <>"').
	 *
	 * @param string $html A HTML fragment.
	 */
	protected function clean_html( $html ) {
		// Convert everything except Latin and Cyrillic and Thai.
		static $convmap = [
			// Simple Latin characters.
			0x80,   0x03ff,   0, 0xffffff, // @codingStandardsIgnoreLine.
			// Cyrillic characters.
			0x0514, 0x0dff, 0, 0xffffff, // @codingStandardsIgnoreLine.
			// Thai characters.
			0x0e7f, 0x10ffff, 0, 0xffffff, // @codingStandardsIgnoreLine.
		];

		return str_replace( [ '&lt;', '&gt;' ], [ '<', '>' ], mb_encode_numericentity( htmlentities( $html, ENT_NOQUOTES, 'UTF-8', false ), $convmap, 'UTF-8' ) );
	}

	/**
	 * Call protected/private method of a class.
	 *
	 * @param object $object      Instantiated object that we will run method on.
	 * @param string $method_name Method name to call.
	 * @param array  $parameters  Array of parameters to pass into method.
	 *
	 * @return mixed Method return.
	 */
	protected function invokeMethod( &$object, $method_name, array $parameters = [] ) {
		$reflection = new \ReflectionClass( get_class( $object ) );
		$method = $reflection->getMethod( $method_name );
		$method->setAccessible( true );

		return $method->invokeArgs( $object, $parameters );
	}

	/**
	 * Call protected/private method of a class.
	 *
	 * @param string $classname   Instantiated object that we will run method on.
	 * @param string $method_name Method name to call.
	 * @param array  $parameters  Array of parameters to pass into method.
	 *
	 * @return mixed Method return.
	 */
	protected function invokeStaticMethod( $classname, $method_name, array $parameters = [] ) {
		$reflection = new \ReflectionClass( $classname );
		$method = $reflection->getMethod( $method_name );
		$method->setAccessible( true );

		return $method->invokeArgs( null, $parameters );
	}

	/**
	 * Helper function to generate a valid token list from strings.
	 *
	 * @param string $value The string to tokenize.
	 * @param string $type  Optional. Default 'word'.
	 *
	 * @return array
	 */
	protected function tokenize( $value, $type = \PHP_Typography\Text_Parser\Token::WORD ) {
		return [
			new \PHP_Typography\Text_Parser\Token( $value, $type ),
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
			$tokens[] = new \PHP_Typography\Text_Parser\Token( $word, \PHP_Typography\Text_Parser\Token::WORD );
		}

		return $tokens;
	}

	/**
	 * Reports an error identified by $message if the combined token values differ from the expected value.
	 *
	 * @param string|array $expected_value Either a word/sentence or a token array.
	 * @param array        $actual_tokens  A token array.
	 * @param string       $message        Optional. Default ''.
	 */
	protected function assertTokensSame( $expected_value, array $actual_tokens, $message = '' ) {
		$this->assertContainsOnlyInstancesOf( \PHP_Typography\Text_Parser\Token::class, $actual_tokens, '$actual_tokens has to be an array of tokens.' );
		foreach ( $actual_tokens as $index => $token ) {
			$actual_tokens[ $index ] = $token->with_value( $this->clean_html( $token->value ) );
		}

		if ( is_scalar( $expected_value ) ) {
			if ( false !== strpos( $expected_value, ' ' ) ) {
				$expected_value = $this->tokenize_sentence( $expected_value );
			} else {
				$expected_value = $this->tokenize( $expected_value );
			}
		}

		// Ensure clean HTML even when a scalar was passed.
		$this->assertContainsOnlyInstancesOf( \PHP_Typography\Text_Parser\Token::class, $expected_value, '$expected_value has to be a string or an array of tokens.' );
		foreach ( $expected_value as $index => $token ) {
			$expected[ $index ] = $token->with_value( $this->clean_html( $token->value ) );
		}

		$this->assertSame( count( $expected ), count( $actual_tokens ) );

		foreach ( $actual_tokens as $key => $token ) {
			$this->assertSame( $expected[ $key ]->value, $token->value, $message );
			$this->assertSame( $expected[ $key ]->type,  $token->type,  $message );
		}

		return true;
	}

	/**
	 * Reports an error identified by $message if the combined token values do
	 * not differ from the expected value.
	 *
	 * @param string|array $expected_value Either a word/sentence or a token array.
	 * @param array        $actual_tokens  A token array.
	 * @param string       $message        Optional. Default ''.
	 */
	protected function assertTokensNotSame( $expected_value, array $actual_tokens, $message = '' ) {
		$this->assertContainsOnlyInstancesOf( \PHP_Typography\Text_Parser\Token::class, $actual_tokens, '$actual_tokens has to be an array of tokens.' );
		foreach ( $actual_tokens as $index => $token ) {
			$actual_tokens[ $index ] = $token->with_value( $this->clean_html( $token->value ) );
		}

		if ( is_scalar( $expected_value ) ) {
			if ( false !== strpos( $expected_value, ' ' ) ) {
				$expected = $this->tokenize_sentence( $expected_value );
			} else {
				$expected = $this->tokenize( $expected_value );
			}
		} else {
			$this->assertContainsOnlyInstancesOf( \PHP_Typography\Text_Parser\Token::class, $expected_value, '$expected_value has to be a string or an array of tokens.' );
			$expected = $expected_value;
		}

		$this->assertSame( count( $expected ), count( $actual_tokens ) );

		$result = false;
		foreach ( $actual_tokens as $key => $token ) {
			if ( $expected[ $key ]->value !== $token->value || $expected[ $key ]->type !== $token->type ) {
				$result = true;
			}
		}

		return $this->assertTrue( $result, $message );
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
