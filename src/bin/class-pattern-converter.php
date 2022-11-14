<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2015-2022 Peter Putzer.
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
 *  @author Peter Putzer <github@mundschenk.at>
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace PHP_Typography\Bin;

use PHP_Typography\U;

/**
 *  Convert LaTeX hyphenation pattern files to JSON.
 *
 *  @author Peter Putzer <github@mundschenk.at>
 */
class Pattern_Converter {

	/**
	 * Pattern file URL(s) to fetch.
	 *
	 * @since 6.1.0
	 *
	 * @var string[]
	 */
	protected $urls;

	/**
	 * Human-readable language name.
	 *
	 * @var string
	 */
	protected $language;

	/**
	 * A word character class in PCRE2 syntax.
	 *
	 * @var string
	 */
	protected $word_class;

	/**
	 * Creates a new converter object.
	 *
	 * @param string|string[] $urls     The TeX pattern file URL(s).
	 * @param string          $language A human-readable language name.
	 */
	public function __construct( $urls, $language ) {
		$this->urls     = (array) $urls;
		$this->language = $language;

		// We need to use a non-matching group here because strangely PCRE2 does
		// not allow the "script" classes to be used as part of a real character class.
		$this->word_class = '(?:' .
			\join(
				'|',
				[
					'\p{Xan}',     // Alphanumeric characters.
					"[.'ʼ᾽ʼ᾿’\-]", // Allowed punctuation.
					'\p{S}',       // Symbols.
					'\p{Mn}',      // Non-spacing marks (diacritics).

					// Additional code points used by Non-latin scripts.
					'\p{Bengali}',
					'\p{Cyrillic}',
					'\p{Devanagari}',
					'\p{Ethiopic}',
					'\p{Gujarati}',
					'\p{Gurmukhi}',
					'\p{Kannada}',
					'\p{Malayalam}',
					'\p{Oriya}',
					'\p{Tamil}',
					'\p{Telugu}',
					'\p{Thai}',

					// Very special characters.
					'[' . U::ZERO_WIDTH_JOINER . U::ZERO_WIDTH_NON_JOINER . ']',
				]
			)
		. ')';
	}

	/**
	 * Retrieve patgen segment from TeX hyphenation pattern.
	 *
	 * @param string $pattern TeX hyphenation pattern.
	 * @return string
	 */
	protected function get_segment( $pattern ) {
		return \preg_replace( '/[0-9]/', '', \str_replace( '.', '_', $pattern ) );
	}

	/**
	 * Calculate patgen sequence from TeX hyphenation pattern.
	 *
	 * @param string $pattern TeX hyphenation pattern.
	 *
	 * @throws \RangeException Thrown when the calculated pattern length is invalid.
	 *
	 * @return string
	 */
	protected function get_sequence( $pattern ) {
		$characters = \mb_str_split( \str_replace( '.', '_', $pattern ) );
		$result     = [];

		foreach ( $characters as $index => $chr ) {
			if ( \ctype_digit( $chr ) ) {
				$result[] = $chr;
			} else {
				// Append '0' if this is the first character or the previous character was not a number.
				if ( ! isset( $characters[ $index - 1 ] ) || ! \ctype_digit( $characters[ $index - 1 ] ) ) {
					$result[] = '0';
				}

				// Append '0' if this is the last character.
				if ( ! isset( $characters[ $index + 1 ] ) ) {
					$result[] = '0';
				}
			}
		}

		// Do some error checking.
		$count     = \count( $result );
		$count_seg = \mb_strlen( $this->get_segment( $pattern ) );
		$sequence  = \implode( '', $result );

		if ( $count !== $count_seg + 1 ) {
			throw new \RangeException( "Invalid segment length $count for pattern $pattern (result sequence $sequence)." );
		}

		return $sequence;
	}

	/**
	 * Format hyphenation pattern file for wp-Typography.
	 *
	 * @param string[] $patterns An array of TeX hyphenation patterns.
	 * @param string[] $exceptions {
	 *      An array of hyphenation exceptions.
	 *
	 *      @type string $key Hyphenated key (e.g. 'something' => 'some-thing').
	 * }
	 * @param string[] $comments An array of TeX comments.
	 *
	 * @return string
	 */
	protected function format_results( array $patterns, array $exceptions, array $comments ) {
		$pattern_mapping = [];

		foreach ( $patterns as $pattern ) {
			$segment = $this->get_segment( $pattern );

			if ( ! isset( $pattern_mapping[ $segment ] ) ) {
				$pattern_mapping[ $segment ] = $this->get_sequence( $pattern );
			}
		}

		// Produce a nice exceptions mapping.
		$json_exceptions = [];
		foreach ( $exceptions as $exception ) {
			$json_exceptions[ \mb_strtolower( \str_replace( '-', '', $exception ) ) ] = \mb_strtolower( $exception );
		}

		$json_results = [
			'language'    => $this->language,
			'source_url'  => \count( $this->urls ) > 1 ? $this->urls : $this->urls[0],
			'copyright'   => \array_map( 'rtrim', $comments ),
			'exceptions'  => $json_exceptions,
			'patterns'    => $pattern_mapping,
		];

		return (string) \json_encode( $json_results, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE );
	}

	/**
	 * Try to match squences of TeX hyphenation exceptions.
	 *
	 * @param string   $line A line from the TeX pattern file.
	 * @param string[] $exceptions {
	 *      An array of hyphenation exceptions.
	 *
	 *      @type string $key Hyphenated key (e.g. 'something' => 'some-thing').
	 * }
	 * @param int      $line_no  Optional. Line number. Default 0.
	 *
	 * @throws \RangeException Thrown when the exception line is malformed.
	 *
	 * @return bool
	 */
	protected function match_exceptions( $line, array &$exceptions, $line_no = 0 ) {
		$continue_reading_exceptions = true;

		if ( \preg_match( "/^\s*({$this->word_class}+)\s*}\s*(?:%.*)?$/u", $line, $matches ) ) {
			$exceptions[]                = $matches[1];
			$continue_reading_exceptions = false;
		} elseif ( \preg_match( "/^\s*((?:{$this->word_class}+\s*)+)\s*}\s*(?:%.*)?$/u", $line, $matches ) ) {
			$this->match_exceptions( $matches[1], $exceptions, $line_no );
			$continue_reading_exceptions = false;
		} elseif ( \preg_match( '/^\s*}\s*(?:%.*)?$/u', $line, $matches ) ) {
			$continue_reading_exceptions = false;
		} elseif ( \preg_match( "/^\s*({$this->word_class}+)\s*(?:%.*)?$/u",  $line, $matches ) ) {
			$exceptions[] = $matches[1];
		} elseif ( \preg_match( "/^\s*((?:{$this->word_class}+\s*)+)(?:%.*)?$/u",  $line, $matches ) ) {
			// Sometimes there are multiple exceptions on a single line.
			foreach ( self::split_at_whitespace( $matches[1] ) as $match ) {
				$exceptions[] = $match;
			}
		} elseif ( ! \preg_match( '/^\s*(?:%.*)?$/u', $line, $matches ) ) {
			// Ignore comments and whitespace in exceptions, but everything else
			// unaccounted for at this point means we should abort.
			throw new \RangeException( "Error: unknown exception $line on line $line_no\n" );
		}

		return $continue_reading_exceptions;
	}

	/**
	 * Try to match a pattern.
	 *
	 * @param string   $line     A line from the TeX pattern file.
	 * @param string[] $patterns An array of patterns.
	 * @param int      $line_no  Optional. Line number. Default 0.
	 *
	 * @throws \RangeException Thrown when the pattern line is malformed.
	 *
	 * @return bool Whether the parser should stay in "reading patterns" mode.
	 */
	protected function match_patterns( $line, array &$patterns, $line_no = 0 ) {
		$continue_reading_patterns = true;

		if ( \preg_match( "/^\s*({$this->word_class}+)\s*\}\s*(?:%.*)?$/u", $line, $matches ) ) {
			$patterns[]                = $matches[1];
			$continue_reading_patterns = false;
		} elseif ( \preg_match( '/^\s*\}\s*(?:%.*)?$/u', $line, $matches ) ) {
			$continue_reading_patterns = false;
		} elseif ( \preg_match( "/^\s*({$this->word_class}+)\s*(?:%.*)?$/u",  $line, $matches ) ) {
			$patterns[] = $matches[1];
		} elseif ( \preg_match( "/^\s*((?:{$this->word_class}+\s*)+)(?:%.*)?$/u",  $line, $matches ) ) {
			foreach ( self::split_at_whitespace( $matches[1] ) as $match ) {
				$patterns[] = $match;
			}
		} elseif ( ! \preg_match( '/^\s*(?:%.*)?$/u', $line, $matches ) ) {
			// Ignore comments and whitespace in patterns, but everything else
			// unaccounted for at this point means we should abort.
			throw new \RangeException( "Error: unknown pattern $line on line $line_no\n" );
		}

		return $continue_reading_patterns;
	}

	/**
	 * Replace macros in the given line.
	 *
	 * @since 6.1.0
	 *
	 * @param  string   $line   The input string.
	 * @param  string[] $macros The macros.
	 *
	 * @return string
	 */
	protected function expand_macros( $line, array $macros ) {
		if ( 0 < \preg_match_all( '/\\\(?<name>\w+)\{(?<arg>[^\}]+)\}/u', $line, $matches, \PREG_SET_ORDER ) ) {
			foreach ( $matches as $m ) {
				if ( ! empty( $macros[ $m['name'] ] ) ) {
					$expanded = \preg_replace( '/#1/', $m['arg'], $macros[ $m['name'] ] );
					$pattern  = \preg_quote( $m[0], '/' );
					$line     = \preg_replace( "/{$pattern}/u", $expanded, $line );
				}
			}
		}

		return $line;
	}

	/**
	 * Split line (fragment) at whitespace.
	 *
	 * @param  string $line A line (fragment).
	 *
	 * @return array<int, string>
	 */
	private static function split_at_whitespace( $line ) {
		return \preg_split( '/\s+/Su', $line, -1, PREG_SPLIT_NO_EMPTY ) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary -- We can safely assume an array here, as long as $line convertible to a string.
	}

	/**
	 * Convert the given TeX files.
	 *
	 * @throws \RangeException Thrown when a line cannot be parsed at all.
	 * @throws \RuntimeException Thrown when file does not exist.
	 *
	 * @return string
	 */
	public function convert() {
		// Results.
		$comments   = [];
		$patterns   = [];
		$exceptions = [];

		foreach ( $this->urls as $url ) {
			$this->convert_single_file( $url, $patterns, $exceptions, $comments );
		}

		return $this->format_results( $patterns, $exceptions, $comments );
	}

	/**
	 * Convert the given TeX file.
	 *
	 * @since 6.1.0
	 *
	 * @param string   $url        Pattern file URL.
	 * @param string[] $patterns   Extracted pattern lines. Passed by reference.
	 * @param string[] $exceptions Extracted hyphenation exception lines. Passed by reference.
	 * @param string[] $comments   Extracted comments lines. Passed by reference.
	 *
	 * @throws \RangeException   Thrown when a line cannot be parsed at all.
	 * @throws \RuntimeException Thrown when file does not exist or is not readable.
	 */
	protected function convert_single_file( $url, &$patterns, &$exceptions, &$comments ) : void {
		if ( ! \file_exists( $url ) && 404 === File_Operations::get_http_response_code( $url ) ) {
			throw new \RuntimeException( "Error: unknown pattern file '{$url}'\n" );
		}

		// Status indicators.
		$reading_patterns   = false;
		$reading_exceptions = false;

		// Macro definitions.
		$macros = [];

		$file    = new \SplFileObject( $url );
		$line_no = 0;
		while ( ! $file->eof() ) {
			// Read the next line.
			$line = $file->fgets();
			if ( ! \is_string( $line ) ) {
				throw new \RuntimeException( "Error reading file '{$url}'\n" );
			}

			$line_no++;

			// Parse the line.
			if ( $reading_patterns ) {
				$reading_patterns = $this->match_patterns( $this->expand_macros( $line, $macros ), $patterns, $line_no );
			} elseif ( $reading_exceptions ) {
				$reading_exceptions = $this->match_exceptions( $this->expand_macros( $line, $macros ), $exceptions, $line_no );
			} else {
				// Not a pattern & not an exception.
				if ( \preg_match( '/^\s*%.*$/u', $line, $matches ) ) {
					$comments[] = $line;
				} elseif ( \preg_match( '/^\s*\\\patterns\s*\{\s*(.*)$/u', $line, $matches ) ) {
					$reading_patterns = $this->match_patterns( $matches[1], $patterns, $line_no );
				} elseif ( \preg_match( '/^\s*\\\hyphenation\s*{\s*(.*)$/u', $line, $matches ) ) {
					$reading_exceptions = $this->match_exceptions( $matches[1], $exceptions, $line_no );
				} elseif ( \preg_match( '/^\s*\\\def\\\(\w+)#1\s*\{([^\}]*)\}\s*$/u', $line, $matches ) ) {
					// Add a macro definition.
					$macros[ $matches[1] ] = $matches[2];
				} elseif ( \preg_match( '/^\s*\\\edef\\\(\w+)#1\s*\{(.*)\}\s*$/u', $line, $matches ) ) {
					// Add a macro definition and expand any contained macros.
					$macros[ $matches[1] ] = $this->expand_macros( $matches[2], $macros );
				} elseif ( \preg_match( '/^\s*\\\[\w]+.*$/u', $line, $matches ) ) {
					// Ignore \endinput.
					if ( ! \preg_match( '/^\s*\\\endinput.*$/u', $line, $matches ) ) {
						// Treat other commands as comments unless we are matching exceptions or patterns.
						$comments[] = $line;
					}
				} elseif ( ! \preg_match( '/^\s*$/u', $line, $matches ) ) {
					// If this was not simply whitespace, we are in trouble.
					throw new \RangeException( "Error: unknown string $line at line $line_no\n" );
				}
			}
		}
	}
}
