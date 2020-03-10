<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2017-2019 Peter Putzer.
 *
 *  This program is free software; you can redistribute it and/or modify modify
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

namespace PHP_Typography\Fixes\Node_Fixes;

use PHP_Typography\Text_Parser;
use PHP_Typography\Settings;
use PHP_Typography\DOM;
use PHP_Typography\Hyphenator\Cache;
use PHP_Typography\Fixes\Token_Fix;
use PHP_Typography\Fixes\Token_Fixes\Hyphenate_Fix;

/**
 * Tokenizes the content of a textnode and process the individual words separately.
 *
 * Currently this functions applies the following enhancements:
 *   - wrapping hard hyphens
 *   - hyphenation
 *   - wrapping URLs
 *   - wrapping email addresses
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 */
class Process_Words_Fix extends Abstract_Node_Fix {

	/**
	 * An array of token fixes.
	 *
	 * @var array
	 */
	private $token_fixes = [];

	/**
	 * A custom parser for \DOMText to separate words, whitespace etc. for HTML injection.
	 *
	 * @var Text_Parser
	 */
	private $text_parser;

	/**
	 * Apply the fix to a given textnode.
	 *
	 * @since 7.0.0 All parameters are now required.
	 *
	 * @param \DOMText $textnode The DOM node.
	 * @param Settings $settings The settings to apply.
	 * @param bool     $is_title Indicates if the processed tokens occur in a title/heading context.
	 *
	 * @return void
	 */
	public function apply( \DOMText $textnode, Settings $settings, $is_title ) {
		// Lazy-load text parser.
		$text_parser = $this->get_text_parser();
		$tokens      = [];

		// Set up parameters for word categories.
		$mixed_caps       = empty( $settings[ Settings::HYPHENATE_ALL_CAPS ] ) ? Text_Parser::ALLOW_ALL_CAPS : Text_Parser::NO_ALL_CAPS;
		$letter_caps      = empty( $settings[ Settings::HYPHENATE_ALL_CAPS ] ) ? Text_Parser::NO_ALL_CAPS : Text_Parser::ALLOW_ALL_CAPS;
		$mixed_compounds  = empty( $settings[ Settings::HYPHENATE_COMPOUNDS ] ) ? Text_Parser::ALLOW_COMPOUNDS : Text_Parser::NO_COMPOUNDS;
		$letter_compounds = empty( $settings[ Settings::HYPHENATE_COMPOUNDS ] ) ? Text_Parser::NO_COMPOUNDS : Text_Parser::ALLOW_COMPOUNDS;

		// Break text down for a bit more granularity.
		$text_parser->load( $textnode->data );
		$tokens[ Token_Fix::MIXED_WORDS ]    = $text_parser->get_words( Text_Parser::NO_ALL_LETTERS, $mixed_caps, $mixed_compounds );  // prohibit letter-only words, allow caps, allow compounds (or not).
		$tokens[ Token_Fix::COMPOUND_WORDS ] = ! empty( $settings[ Settings::HYPHENATE_COMPOUNDS ] ) ? $text_parser->get_words( Text_Parser::NO_ALL_LETTERS, $letter_caps, Text_Parser::REQUIRE_COMPOUNDS ) : [];
		$tokens[ Token_Fix::WORDS ]          = $text_parser->get_words( Text_Parser::REQUIRE_ALL_LETTERS, $letter_caps, $letter_compounds ); // require letter-only words allow/prohibit caps & compounds vice-versa.
		$tokens[ Token_Fix::OTHER ]          = $text_parser->get_other();

		// Process individual text parts here.
		foreach ( $this->token_fixes as $fix ) {
			$t = $fix->target();

			$tokens[ $t ] = $fix->apply( $tokens[ $t ], $textnode, $settings, $is_title );
		}

		// Apply updates to our text.
		$text_parser->update( $tokens[ Token_Fix::MIXED_WORDS ] + $tokens[ Token_Fix::COMPOUND_WORDS ] + $tokens[ Token_Fix::WORDS ] + $tokens[ Token_Fix::OTHER ] );
		$textnode->data = $text_parser->unload();
	}

	/**
	 * Retrieves the text parser instance.
	 *
	 * @return \PHP_Typography\Text_Parser
	 */
	public function get_text_parser() {
		// Lazy-load text parser.
		if ( ! isset( $this->text_parser ) ) {
			$this->text_parser = new Text_Parser();
		}

		return $this->text_parser;
	}

	/**
	 * Registers a new token fix.
	 *
	 * @param Token_Fix $fix Required.
	 */
	public function register_token_fix( Token_Fix $fix ) {
		$this->token_fixes[] = $fix;
	}

	/**
	 * Sets the hyphenator cache for all registered token fixes (that require one).
	 *
	 * @param Cache $cache A hyphenator cache instance.
	 */
	public function update_hyphenator_cache( Cache $cache ) {
		foreach ( $this->token_fixes as $fix ) {
			if ( $fix instanceof Hyphenate_Fix ) {
				$fix->set_hyphenator_cache( $cache );
			}
		}
	}
}
