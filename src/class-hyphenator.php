<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2014-2022 Peter Putzer.
 *  Copyright 2009-2011 KINGdesk, LLC.
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

namespace PHP_Typography;

use PHP_Typography\Hyphenator\Trie_Node;
use PHP_Typography\Text_Parser\Token;

/**
 * Hyphenates tokenized text.
 *
 * If used with multibyte language, UTF-8 encoding is required.
 *
 * Portions of this code have been inspired by:
 *  - hyphenator-php (https://nikonyrh.github.io/phphyphenation.html)
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 3.4.0
 */
class Hyphenator {

	/**
	 * The hyphenation patterns, stored in a trie for easier searching.
	 *
	 * @var Trie_Node|null
	 */
	protected $pattern_trie;

	/**
	 * The hyphenation exceptions from the pattern file.
	 * Stored as an array of "hy-phen-at-ed" strings.
	 *
	 * @var string[]
	 */
	protected $pattern_exceptions = [];

	/**
	 * Custom hyphenation exceptions set by the user.
	 * Stored as an array of "hy-phen-at-ed" strings.
	 *
	 * @var string[]
	 */
	protected $custom_exceptions;

	/**
	 * A binary hash of $custom_exceptions array.
	 *
	 * @var string
	 */
	protected $custom_exceptions_hash;

	/**
	 * Patterns calculated from the merged hyphenation exceptions.
	 *
	 * @var array<string,int[]>|null
	 */
	protected $merged_exception_patterns;

	/**
	 * The current hyphenation language.
	 * Stored in the short form (e.g. "en-US").
	 *
	 * @var string|null
	 */
	protected $language;

	/**
	 * Constructs new Hyphenator instance.
	 *
	 * @param string|null $language   Optional. Short-form language name. Default null.
	 * @param string[]    $exceptions Optional. Custom hyphenation exceptions. Default empty array.
	 */
	public function __construct( $language = null, array $exceptions = [] ) {

		if ( ! empty( $language ) ) {
			$this->set_language( $language );
		}

		if ( ! empty( $exceptions ) ) {
			$this->set_custom_exceptions( $exceptions );
		}
	}

	/**
	 * Sets custom word hyphenations.
	 *
	 * @param array<string,string> $exceptions Optional. An array of words with all hyphenation points marked with a hard hyphen. Default empty array.
	 */
	public function set_custom_exceptions( array $exceptions = [] ) : void {
		if ( empty( $exceptions ) && empty( $this->custom_exceptions ) ) {
			return; // Nothing to do at all.
		}

		// Calculate hash & check against previous exceptions.
		$new_hash = self::get_object_hash( $exceptions );
		if ( $this->custom_exceptions_hash === $new_hash ) {
			return; // No need to update exceptions.
		}

		// Do our thing.
		$exception_keys = [];
		foreach ( $exceptions as $exception ) {
			$f = Strings::functions( $exception );
			if ( empty( $f ) ) {
				continue; // unknown encoding, abort.
			}

			/**
			 * Prepare exception keys.
			 *
			 * @var string $exception
			 */
			$exception                    = $f['strtolower']( $exception );
			$exception_keys[ $exception ] = (string) \preg_replace( "#-#{$f['u']}", '', $exception );
		}

		// Update exceptions.
		$this->custom_exceptions      = \array_flip( $exception_keys );
		$this->custom_exceptions_hash = $new_hash;

		// Force remerging of patgen and custom exception patterns.
		$this->merged_exception_patterns = null;
	}

	/**
	 * Calculates binary-safe hash from data object.
	 *
	 * @param mixed $object Any datatype.
	 *
	 * @return string
	 */
	protected static function get_object_hash( $object ) {
		return \md5( (string) \json_encode( $object ), false );
	}

	/**
	 * Sets the hyphenation pattern language.
	 *
	 * @param string $lang Optional. Has to correspond to a filename in 'lang'. Default 'en-US'.
	 *
	 * @return bool Whether loading the pattern file was successful.
	 */
	public function set_language( $lang = 'en-US' ) {
		if ( isset( $this->language ) && $this->language === $lang ) {
			return true; // Bail out, no need to do anything.
		}

		$success            = false;
		$language_file_name = \dirname( __FILE__ ) . '/lang/' . $lang . '.json';

		if ( \file_exists( $language_file_name ) ) {
			$raw_language_file = \file_get_contents( $language_file_name );

			if ( false !== $raw_language_file ) {
				$language_file = \json_decode( $raw_language_file, true );

				if ( false !== $language_file ) {
					$this->language           = $lang;
					$this->pattern_trie       = Trie_Node::build_trie( $language_file['patterns'] );
					$this->pattern_exceptions = $language_file['exceptions'];

					$success = true;
				}
			}
		}

		// Clean up.
		if ( ! $success ) {
			$this->language           = null;
			$this->pattern_trie       = null;
			$this->pattern_exceptions = [];
		}

		// Make sure hyphenationExceptions is not set to force remerging of patgen and custom exceptions.
		$this->merged_exception_patterns = null;

		return $success;
	}

	/**
	 * Hyphenates parsed text tokens.
	 *
	 * @param Token[] $parsed_text_tokens   An array of text tokens.
	 * @param string  $hyphen               Optional. The hyphen character. Default '-'.
	 * @param bool    $hyphenate_title_case Optional. Whether words in Title Case should be hyphenated. Default false.
	 * @param int     $min_length           Optional. Minimum word length for hyphenation. Default 2.
	 * @param int     $min_before           Optional. Minimum number of characters before a hyphenation point. Default 2.
	 * @param int     $min_after            Optional. Minimum number of characters after a hyphenation point. Default 2.
	 *
	 * @return Token[] The modified text tokens.
	 */
	public function hyphenate( array $parsed_text_tokens, $hyphen = '-', $hyphenate_title_case = false, $min_length = 2, $min_before = 2, $min_after = 2 ) {
		if ( empty( $min_length ) || empty( $min_before ) || ! isset( $this->pattern_trie ) ) {
			return $parsed_text_tokens;
		}

		// Make sure we have full exceptions list.
		if ( ! isset( $this->merged_exception_patterns ) ) {
			$this->merge_hyphenation_exceptions();
		}

		foreach ( $parsed_text_tokens as $key => $text_token ) {
			$parsed_text_tokens[ $key ] = $text_token->with_value( $this->hyphenate_word( $text_token->value, $hyphen, $hyphenate_title_case, $min_length, $min_before, $min_after ) );
		}

		return $parsed_text_tokens;
	}

	/**
	 * Hyphenates a single word.
	 *
	 * @param string $word                 The word to hyphenate.
	 * @param string $hyphen               The hyphen character.
	 * @param bool   $hyphenate_title_case Whether words in Title Case should be hyphenated.
	 * @param int    $min_length           Minimum word length for hyphenation.
	 * @param int    $min_before           Minimum number of characters before a hyphenation point.
	 * @param int    $min_after            Minimum number of characters after a hyphenation point.
	 *
	 * @return string
	 */
	protected function hyphenate_word( $word, $hyphen, $hyphenate_title_case, $min_length, $min_before, $min_after ) {
		// Quickly reference string functions according to encoding.
		$f = Strings::functions( $word );
		if ( empty( $f ) ) {
			return $word; // unknown encoding, abort.
		}

		// Check word length.
		$word_length = $f['strlen']( $word );
		if ( $word_length < $min_length ) {
			return $word;
		}

		// Trie lookup requires a lowercase search term.
		$the_key = $f['strtolower']( $word );

		// If this is a capitalized word, and settings do not allow hyphenation of such, abort!
		// Note: This is different than uppercase words, where we are looking for title case.
		if ( ! $hyphenate_title_case && $the_key !== $word ) {
			return $word;
		}

		// Determine pattern.
		if ( isset( $this->merged_exception_patterns[ $the_key ] ) ) {
			// Give preference to exceptions.
			$pattern = $this->merged_exception_patterns[ $the_key ];
		} else {
			// Lookup word pattern if there is no exception.
			$pattern = $this->lookup_word_pattern( $the_key, $f['strlen'], $f['str_split'] );
		}

		// Add hyphen character based on pattern.
		$word_parts      = $f['str_split']( $word, 1 );
		$hyphenated_word = '';

		for ( $i = 0; $i < $word_length; $i++ ) {
			if ( isset( $pattern[ $i ] ) && self::is_odd( $pattern[ $i ] ) && ( $i >= $min_before ) && ( $i <= $word_length - $min_after ) ) {
				$hyphenated_word .= $hyphen;
			}

			$hyphenated_word .= $word_parts[ $i ];
		}

		return $hyphenated_word;
	}

	/**
	 * Lookup the pattern for a word via the trie.
	 *
	 * @param  string   $key       The search key (lowercase word).
	 * @param  callable $strlen    A function equivalent to `strlen` for the appropriate encoding.
	 * @param  callable $str_split A function equivalent to `str_split` for the appropriate encoding.
	 *
	 * @return int[] The hyphenation pattern.
	 */
	protected function lookup_word_pattern( $key, callable $strlen, callable $str_split ) {
		if ( null === $this->pattern_trie ) {
			return []; // abort early.
		}

		// Add underscores to make out-of-index checks unnecessary,
		// also hyphenation is done in lower case.
		$search        = '_' . $key . '_';
		$search_length = $strlen( $search );
		$chars         = $str_split( $search );
		$word_pattern  = [];

		for ( $start = 0; $start < $search_length; ++$start ) {
			// Start from the trie root node.
			$node = $this->pattern_trie;

			// Walk through the trie while storing detected patterns.
			for ( $step = $start; $step < $search_length; ++$step ) {
				// No further path in the trie.
				if ( ! $node->exists( $chars[ $step ] ) ) {
					break;
				}

				// Look for next character.
				$node = $node->get_node( $chars[ $step ] );

				// Merge different offset values and keep maximum.
				foreach ( $node->offsets() as $pattern_offset ) {
					$value  = $pattern_offset[0];
					$offset = $pattern_offset[1] + $start - 1;

					$word_pattern[ $offset ] = isset( $word_pattern[ $offset ] ) ? max( $word_pattern[ $offset ], $value ) : $value;
				}
			}
		}

		return $word_pattern;
	}

	/**
	 * Merges hyphenation exceptions from the language file and custom hyphenation exceptions and
	 * generates patterns for all of them.
	 */
	protected function merge_hyphenation_exceptions() : void {
		$exceptions = [];

		// Merge custom and language specific word hyphenations.
		if ( ! empty( $this->pattern_exceptions ) && ! empty( $this->custom_exceptions ) ) {
			$exceptions = array_merge( $this->custom_exceptions, $this->pattern_exceptions );
		} elseif ( ! empty( $this->pattern_exceptions ) ) {
			$exceptions = $this->pattern_exceptions;
		} elseif ( ! empty( $this->custom_exceptions ) ) {
			$exceptions = $this->custom_exceptions;
		}

		// Update patterns as well.
		$exception_patterns = [];
		foreach ( $exceptions as $exception_key => $exception ) {
			$exception_patterns[ $exception_key ] = self::convert_hyphenation_exception_to_pattern( $exception );
		}

		$this->merged_exception_patterns = $exception_patterns;
	}

	/**
	 * Generates a hyphenation pattern from an exception.
	 *
	 * @param string $exception A hyphenation exception in the form "foo-bar". Needs to be encoded in ASCII or UTF-8.
	 *
	 * @return int[]|null Returns the hyphenation pattern or null if `$exception` is using an invalid encoding.
	 */
	protected static function convert_hyphenation_exception_to_pattern( $exception ) {
		$f = Strings::functions( $exception );
		if ( empty( $f ) ) {
			return null; // unknown encoding, abort.
		}

		// Set the word_pattern - this method keeps any contextually important capitalization.
		$lowercase_hyphened_word_parts  = $f['str_split']( $exception, 1 );
		$lowercase_hyphened_word_length = $f['strlen']( $exception );

		$word_pattern = [];
		$index        = 0;

		for ( $i = 0; $i < $lowercase_hyphened_word_length; $i++ ) {
			if ( '-' === $lowercase_hyphened_word_parts[ $i ] ) {
				$word_pattern[ $index ] = 9;
			} else {
				$index++;
			}
		}

		return $word_pattern;
	}

	/**
	 * Is a number odd?
	 *
	 * @param int $number Required.
	 *
	 * @return bool true if $number is odd, false if it is even.
	 */
	protected static function is_odd( $number ) {
		return (bool) ( $number % 2 );
	}
}
