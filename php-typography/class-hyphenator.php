<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2014-2017 Peter Putzer.
 *  Copyright 2009-2011 KINGdesk, LLC.
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; either version 2
 *  of the License, or (at your option) any later version.
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
 *  ***
 *
 *  @package wpTypography/PHPTypography
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace PHP_Typography;

use PHP_Typography\Hyphenator\Trie_Node;

/**
 * Hyphenates tokenized text.
 *
 * If used with multibyte language, UTF-8 encoding is required.
 *
 * Portions of this code have been inspired by:
 *  - hyphenator-php (https://nikonyrh.github.io/phphyphenation.html)
 *
 *  @author Peter Putzer <github@mundschenk.at>
 */
class Hyphenator {

	/**
	 * The hyphenation patterns, stored in a trie for easier searching.
	 *
	 * @var Trie_Node
	 */
	protected $pattern_trie;

	/**
	 * The hyphenation exceptions from the pattern file.
	 * Stored as an array of "hy-phen-at-ed" strings.
	 *
	 * @var array
	 */
	protected $pattern_exceptions;

	/**
	 * Custom hyphenation exceptions set by the user.
	 * Stored as an array of "hy-phen-at-ed" strings.
	 *
	 * @var array
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
	 * @var array|null
	 */
	protected $merged_exception_patterns;

	/**
	 * The current hyphenation language.
	 * Stored in the short form (e.g. "en-US").
	 *
	 * @var string
	 */
	protected $language;

	/**
	 * Constructs new Hyphenator instance.
	 *
	 * @param string|null $language   Optional. Short-form language name. Default null.
	 * @param array       $exceptions Optional. Custom hyphenation exceptions. Default empty array.
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
	 * @param string|array $exceptions Optional. An array of words with all hyphenation points marked with a hard hyphen (or a string list
	 *                                 of such words). In the latter case, only alphanumeric characters and hyphens are recognized.
	 *                                 Default empty array.
	 */
	public function set_custom_exceptions( array $exceptions = [] ) {
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
			$func = Strings::functions( $exception );
			if ( empty( $func ) ) {
				continue; // unknown encoding, abort.
			}

			$exception = $func['strtolower']( $exception );
			$exception_keys[ $exception ] = preg_replace( "#-#{$func['u']}", '', $exception );
		}

		// Update exceptions.
		$this->custom_exceptions      = array_flip( $exception_keys );
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
		return md5( json_encode( $object ), false );
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

		$success = false;
		$this->language = $lang;
		$language_file_name = dirname( __FILE__ ) . '/lang/' . $this->language . '.json';

		if ( file_exists( $language_file_name ) ) {
			$raw_language_file = file_get_contents( $language_file_name );

			if ( false !== $raw_language_file ) {
				$language_file = json_decode( $raw_language_file, true );

				if ( false !== $language_file ) {
					$this->pattern_exceptions = $language_file['exceptions'];
					$this->pattern_trie       = Trie_Node::build_trie( $language_file['patterns'] );

					$success = true;
				}

				unset( $raw_language_file );
				unset( $language_file );
			}
		}

		// Clean up.
		if ( ! $success ) {
			unset( $this->language );
			unset( $this->pattern_trie );
			unset( $this->pattern_exceptions );
		}

		// Make sure hyphenationExceptions is not set to force remerging of patgen and custom exceptions.
		$this->merged_exception_patterns = null;

		return $success;
	}

	/**
	 * Hyphenates parsed text tokens.
	 *
	 * @param array  $parsed_text_tokens   An array of text tokens.
	 * @param string $hyphen               Optional. The hyphen character. Default '-'.
	 * @param bool   $hyphenate_title_case Optional. Whether words in Title Case should be hyphenated. Default false.
	 * @param int    $min_length           Optional. Minimum word length for hyphenation. Default 2.
	 * @param int    $min_before           Optional. Minimum number of characters before a hyphenation point. Default 2.
	 * @param int    $min_after            Optional. Minimum number of characters after a hyphenation point. Default 2.
	 *
	 * @return array The modified text tokens.
	 */
	public function hyphenate( $parsed_text_tokens, $hyphen = '-', $hyphenate_title_case = false, $min_length = 2, $min_before = 2, $min_after = 2 ) {
		if ( empty( $min_length ) || empty( $min_before ) || ! isset( $this->pattern_trie ) || ! isset( $this->pattern_exceptions ) ) {
			return $parsed_text_tokens;
		}

		// Make sure we have full exceptions list.
		if ( ! isset( $this->merged_exception_patterns ) ) {
			$this->merge_hyphenation_exceptions();
		}

		foreach ( $parsed_text_tokens as $key => $text_token ) {
			// Quickly reference string functions according to encoding.
			$func = Strings::functions( $text_token->value );
			if ( empty( $func ) ) {
				continue; // unknown encoding, abort.
			}

			$word_length = $func['strlen']( $text_token->value );
			$the_key     = $func['strtolower']( $text_token->value );

			if ( $word_length < $min_length ) {
				continue;
			}

			// If this is a capitalized word, and settings do not allow hyphenation of such, abort!
			// Note: This is different than uppercase words, where we are looking for title case.
			if ( ! $hyphenate_title_case && $func['substr']( $the_key , 0 , 1 ) !== $func['substr']( $text_token->value, 0, 1 ) ) {
				continue;
			}

			// Give exceptions preference.
			if ( isset( $this->merged_exception_patterns[ $the_key ] ) ) {
				$word_pattern = $this->merged_exception_patterns[ $the_key ];
			}

			if ( ! isset( $word_pattern ) ) {
				// Add underscores to make out-of-index checks unnecessary,
				// also hyphenation is done in lower case.
				$search        = '_' . $the_key . '_';
				$search_length = $func['strlen']( $search );
				$chars         = $func['str_split']( $search );
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
			}

			// Add soft-hyphen based on $word_pattern.
			$word_parts = $func['str_split']( $text_token->value, 1 );
			$hyphenated_word = '';

			for ( $i = 0; $i < $word_length; $i++ ) {
				if ( isset( $word_pattern[ $i ] ) && self::is_odd( $word_pattern[ $i ] ) && ( $i >= $min_before) && ( $i <= $word_length - $min_after ) ) {
					$hyphenated_word .= $hyphen . $word_parts[ $i ];
				} else {
					$hyphenated_word .= $word_parts[ $i ];
				}
			}

			// Ensure "copy on write" semantics.
			if ( $hyphenated_word !== $text_token->value ) {
				$parsed_text_tokens[ $key ] = new Text_Parser\Token( $hyphenated_word, $text_token->type );
			}

			// Clear word pattern for next iteration.
			unset( $word_pattern );
		}

		return $parsed_text_tokens;
	}

	/**
	 * Merges hyphenation exceptions from the language file and custom hyphenation exceptions and
	 * generates patterns for all of them.
	 */
	protected function merge_hyphenation_exceptions() {
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
	 * @return array|null Returns the hyphenation pattern or null if `$exception` is using an invalid encoding.
	 */
	protected static function convert_hyphenation_exception_to_pattern( $exception ) {
		$func = Strings::functions( $exception );
		if ( empty( $func ) ) {
			return null; // unknown encoding, abort.
		}

		// Set the word_pattern - this method keeps any contextually important capitalization.
		$lowercase_hyphened_word_parts  = $func['str_split']( $exception, 1 );
		$lowercase_hyphened_word_length = $func['strlen']( $exception );

		$word_pattern = [];
		$index = 0;

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
