<?php
/**
 *  This file is part of wp-Typography.
 *
 *	Copyright 2014-2016 Peter Putzer.
 *	Copyright 2012-2013 Marie Hogebrandt.
 *	Coypright 2009-2011 KINGdesk, LLC.
 *
 *	This program is free software; you can redistribute it and/or
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

/**
 * A few utility functions.
 */
require_once __DIR__ . '/php-typography-functions.php'; // @codeCoverageIgnore

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
	protected $trie;
	protected $hyphen;
	protected $min_length = 2;
	protected $min_before = 2;
	protected $min_after = 2;
	protected $pattern;
	protected $pattern_max_segment;
	protected $pattern_exceptions;
	protected $hyphenation_exceptions;
	protected $custom_exceptions;
	protected $hyphenation_exception_patterns;
	protected $language;
	/**
	 * An array of encodings in detection order.
	 *
	 * @var array
	 */
	private $encodings = array( 'ASCII', 'UTF-8' );

	/**
	 * A hash map for string functions according to encoding.
	 *
	 * @var array $encoding => array( 'strlen' => $function_name, ... ).
	 */
	private $str_functions = array(
		'UTF-8' => array(),
		'ASCII' => array(),
		false   => array(),
	);

	public function __construct( $hyphen = "-" ) {
		$this->hyphen = $hyphen;

		// Set up both UTF-8 and ASCII string functions.
		// UTF-8 first.
		$this->str_functions['UTF-8']['strlen']     = 'mb_strlen';
		$this->str_functions['UTF-8']['str_split']  = __NAMESPACE__ . '\mb_str_split';
		$this->str_functions['UTF-8']['strtolower'] = 'mb_strtolower';
		$this->str_functions['UTF-8']['substr']     = 'mb_substr';
		$this->str_functions['UTF-8']['u']          = 'u'; // unicode flag for regex.
		// Now ASCII.
		$this->str_functions['ASCII']['strlen']     = 'strlen';
		$this->str_functions['ASCII']['str_split']  = 'str_split';
		$this->str_functions['ASCII']['strtolower'] = 'strtolower';
		$this->str_functions['ASCII']['substr']     = 'substr';
		$this->str_functions['ASCII']['u']			= ''; // no regex flag needed.
	}

	public function set_hyphen( $hyphen ) {
		$this->hyphen = $hyphen;
	}

	public function set_min_length( $min_length ) {
		$this->min_length = $min_length;
	}

	public function set_min_before( $min_before ) {
		$this->min_before = $min_before;
	}

	public function set_min_after( $min_after ) {
		$this->min_after = $min_after;
	}

	public function set_pattern_max_segment( $max_segment ) {
		$this->pattern_max_segment = $max_segment;
	}

	/**
	 * Sets custom word hyphenations.
	 *
	 * @param string|array $exceptions An array of words with all hyphenation points marked with a hard hyphen (or a string list of such words).
	 *        In the latter case, only alphanumeric characters and hyphens are recognized. The default is empty.
	 */
	public function set_custom_exceptions( array $exceptions = array() ) {
		$exception_keys = array();
		$func = array();
		foreach ( $exceptions as $exception ) {
			$func = $this->str_functions[ mb_detect_encoding( $exception, $this->encodings, true ) ];
			if ( empty( $func ) || empty( $func['strlen'] ) ) {
				continue; // unknown encoding, abort.
			}

			$exception = $func['strtolower']( $exception );
			$exception_keys[ $exception ] = preg_replace( "#-#{$func['u']}", '', $exception );
		}

		$this->custom_exceptions = array_flip( $exception_keys );

		// Make sure hyphenationExceptions is not set to force remerging of patgen and custom exceptions.
		if ( isset( $this->hyphenation_exceptions ) ) {
			unset( $this->hyphenation_exceptions );
		}
	}

	/**
	 * Set the hyphenation pattern language.
	 *
	 * @param string $lang Has to correspond to a filename in 'lang'. Optional. Default 'en-US'.
	 */
	public function set_language( $lang = 'en-US' ) {
		if ( isset( $this->language ) && $this->language === $lang ) {
			return; // Bail out, no need to do anything.
		}

		$this->language = $lang;
		$language_file_name = dirname( __FILE__ ) . '/lang/' . $this->language . '.json';

		if ( file_exists( $language_file_name ) ) {
			$language_file = json_decode( file_get_contents( $language_file_name ), true );

			$this->pattern             = $language_file['patterns'];
			$this->pattern_max_segment = $language_file['max_segment_size'];
			$this->pattern_exceptions  = $language_file['exceptions'];

			unset( $language_file );
		} else {
			unset( $this->pattern );
			unset( $this->pattern_max_segment );
			unset( $this->pattern_exceptions );
		}

		// Make sure hyphenationExceptions is not set to force remerging of patgen and custom exceptions.
		if ( isset( $this->hyphenation_exceptions ) ) {
			unset( $this->hyphenation_exceptions );
		}
	}

	public function hyphenate( $parsed_text_tokens, $hyphenate_title_case = false ) {
		if ( empty( $this->min_length ) || empty( $this->min_before ) || ! isset( $this->pattern_max_segment ) || ! isset( $this->pattern_exceptions ) ) {
			return $parsed_text_tokens;
		}

		// Make sure we have full exceptions list.
 		if ( ! isset( $this->hyphenation_exceptions ) ) {
 			$this->merge_hyphenation_exceptions();
 		}

		$func = array(); // quickly reference string functions according to encoding.
		foreach ( $parsed_text_tokens as &$text_token ) {
			$func = $this->str_functions[ mb_detect_encoding( $text_token['value'], $this->encodings, true ) ];
			if ( empty( $func ) || empty( $func['strlen'] ) ) {
				continue; // unknown encoding, abort.
			}

			$word_length = $func['strlen']( $text_token['value'] );
			$the_key     = $func['strtolower']( $text_token['value'] );

			if ( $word_length < $this->min_length ) {
				continue;
			}

			// If this is a capitalized word, and settings do not allow hyphenation of such, abort!
			// Note: This is different than uppercase words, where we are looking for title case.
			if ( ! $hyphenate_title_case && $func['substr']( $the_key , 0 , 1 ) !== $func['substr']( $text_token['value'], 0, 1 ) ) {
				continue;
			}

			// Give exceptions preference.
			if ( isset( $this->hyphenation_exception_patterns[ $the_key ] ) ) {
				$word_pattern = $this->hyphenation_exception_patterns[ $the_key ];
			}

			if ( ! isset( $word_pattern ) ) {
				// First we set up the matching pattern to be a series of zeros one character longer than $text_token.
				$word_pattern = array();
				for ( $i = 0; $i < $word_length + 1; $i++ ) {
					$word_pattern[] = '0';
				}

				// We grab all possible segments from $parsedTextToken of length 1 through $this->pattern_max_segment.
				for ( $segment_length = 1; ( $segment_length <= $word_length ) && ( $segment_length <= $this->pattern_max_segment ); $segment_length++ ) {
					for ( $segment_position = 0; $segment_position + $segment_length <= $word_length; $segment_position++ ) {
						$segment = $func['strtolower']( $func['substr']( $text_token['value'], $segment_position, $segment_length ) );

						if ( 0 === $segment_position && isset( $this->pattern['begin'][ $segment ] ) ) {
							$segment_pattern = $func['str_split']( $this->pattern['begin'][ $segment ], 1 );
							$word_pattern = $this->hyphenation_pattern_injection( $word_pattern, $segment_pattern, $segment_position, $segment_length );
						}

						if ( $segment_position + $segment_length === $word_length && isset( $this->pattern['end'][ $segment ] ) ) {
							$segment_pattern = $func['str_split']( $this->pattern['end'][ $segment ], 1 );
							$word_pattern = $this->hyphenation_pattern_injection( $word_pattern, $segment_pattern, $segment_position, $segment_length );
						}

						if ( isset( $this->pattern['all'][ $segment ] ) ) {
							$segment_pattern = $func['str_split']( $this->pattern['all'][ $segment ], 1 );
							$word_pattern = $this->hyphenation_pattern_injection( $word_pattern, $segment_pattern, $segment_position, $segment_length );
						}
					}
				}
			}

			// Add soft-hyphen based on $word_pattern.
			$word_parts = $func['str_split']( $text_token['value'], 1 );

			$hyphenated_word = '';
			for ( $i = 0; $i < $word_length; $i++ ) {
				if ( is_odd( intval( $word_pattern[ $i ] ) ) && ( $i >= $this->min_before) && ( $i < $word_length - $this->min_after ) ) {
					$hyphenated_word .= $this->hyphen . $word_parts[ $i ];
				} else {
					$hyphenated_word .= $word_parts[ $i ];
				}
			}

			$text_token['value'] = $hyphenated_word;
			unset( $word_pattern );
		}

		return $parsed_text_tokens;
	}


	/**
	 * Inject the PatGen segments pattern into the PatGen words pattern.
	 *
	 * @param array  $word_pattern     Required.
	 * @param array  $segment_pattern  Required.
	 * @param number $segment_position Required.
	 * @param number $segment_length   Required.
	 */
	function hyphenation_pattern_injection( array $word_pattern, array $segment_pattern, $segment_position, $segment_length ) {

		for ( $number_position = $segment_position;
		$number_position <= $segment_position + $segment_length;
		$number_position++ ) {

			$word_pattern[ $number_position ] =
			( intval( $word_pattern[ $number_position ] ) >= intval( $segment_pattern[ $number_position - $segment_position ] ) ) ?
			$word_pattern[ $number_position ] : $segment_pattern[ $number_position - $segment_position ];
		}

		return $word_pattern;
	}

	/**
	 * Merge hyphenation exceptions from the language file and custom hyphenation exceptions and
	 * generate patterns for all of them.
	 */
	function merge_hyphenation_exceptions() {
		// Make sure we have full exceptions list.
		if ( ! isset( $this->hyphenation_exceptions ) ) {
			$exceptions = array();

			if ( $this->pattern_exceptions || ! empty( $this->custom_exceptions ) ) {
				if ( isset( $this->custom_exceptions ) ) {
					// Nerges custom and language specific word hyphenations.
					$exceptions = array_merge( $this->custom_exceptions, $this->pattern_exceptions );
				} else {
					$exceptions = $this->pattern_exceptions;
				}
			}

			$this->hyphenation_exceptions = $exceptions;

			// Update patterns as well.
			$exception_patterns = array();
			foreach ( $exceptions as $exception_key => $exception ) {
				$exception_patterns[ $exception_key ] = $this->convert_hyphenation_exception_to_pattern( $exception );
			}
			$this->hyphenation_exception_patterns = $exception_patterns;
		}

	}

	/**
	 * Generate a hyphenation pattern from an exception.
	 *
	 * @param string $exception A hyphenation exception in the form "foo-bar". Needs to be encoded in ASCII or UTF-8.
	 * @return void|string[] Returns the hyphenation pattern or null if `$exception` is using an invalid encoding.
	 */
	function convert_hyphenation_exception_to_pattern( $exception ) {
		$func = $this->str_functions[ mb_detect_encoding( $exception, $this->encodings, true ) ];
		if ( empty( $func ) || empty( $func['strlen'] ) ) {
			return; // unknown encoding, abort.
		}

		// Set the word_pattern - this method keeps any contextually important capitalization.
		$lowercase_hyphened_word_parts  = $func['str_split']( $exception, 1 );
		$lowercase_hyphened_word_length = $func['strlen']( $exception );

		$word_pattern = array();
		for ( $i = 0; $i < $lowercase_hyphened_word_length; $i++ ) {
			if ( '-' === $lowercase_hyphened_word_parts[ $i ] ) {
				$word_pattern[] = '9';
				$i++;
			} else {
				$word_pattern[] = '0';
			}
		}
		$word_pattern[] = '0'; // For consistent length with the other word patterns.

		return $word_pattern;
	}
}
