<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2017-2022 Peter Putzer.
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
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
 *  ***
 *
 *  @package mundschenk-at/php-typography
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace PHP_Typography\Fixes\Token_Fixes;

use PHP_Typography\Fixes\Token_Fix;
use PHP_Typography\Hyphenator\Cache;
use PHP_Typography\Settings;
use PHP_Typography\Text_Parser;
use PHP_Typography\Text_Parser\Token;

/**
 * Hyphenates hyphenated compound words (if enabled).
 *
 * Calls hyphenate() on the component words.
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 */
class Hyphenate_Compounds_Fix extends Hyphenate_Fix {

	/**
	 * Creates a new fix instance.
	 *
	 * @param Cache|null $cache           Optional. Default null.
	 * @param bool       $feed_compatible Optional. Default false.
	 */
	public function __construct( Cache $cache = null, $feed_compatible = false ) {
		parent::__construct( $cache, Token_Fix::COMPOUND_WORDS, $feed_compatible );
	}

	/**
	 * Apply the tweak to a given textnode.
	 *
	 * @param Token[]       $tokens   Required.
	 * @param Settings      $settings Required.
	 * @param bool          $is_title Optional. Default false.
	 * @param \DOMText|null $textnode Optional. Default null.
	 *
	 * @return Token[] An array of tokens.
	 */
	public function apply( array $tokens, Settings $settings, $is_title = false, \DOMText $textnode = null ) {
		if ( empty( $settings[ Settings::HYPHENATE_COMPOUNDS ] ) ) {
			return $tokens; // abort.
		}

		// Hyphenate compound words.
		foreach ( $tokens as $key => $word_token ) {
			$component_words = [];
			$word_parts      = \preg_split( '/(-)/', $word_token->value, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE ) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary -- Ensure array type.
			foreach ( $word_parts as $word_part ) {
				$component_words[] = new Text_Parser\Token( $word_part, Text_Parser\Token::WORD );
			}

			$tokens[ $key ] = $word_token->with_value(
				\array_reduce(
					parent::apply( $component_words, $settings, $is_title, $textnode ),
					function( $carry, $item ) {
						return $carry . $item->value;
					}
				)
			);
		}

		return $tokens;
	}
}
