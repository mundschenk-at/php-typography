<?php
/**
 *  This file is part of wp-Typography.
 *
 *	Copyright 2015-2016 Peter Putzer.
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
 *  @package wpTypography/PHPTypography/Converter
 *  @author Peter Putzer <github@mundschenk.at>
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace PHP_Typography;

define( 'WP_TYPOGRAPHY_DEBUG', true );

require_once( dirname( __DIR__ ) . '/php-typography-functions.php' );

/**
 *  Convert LaTeX hyphenation pattern files to JSON.
 *
 *  @author Peter Putzer <github@mundschenk.at>
 */
class Pattern_Converter {

	/**
	 * Pattern file URL to fetch.
	 *
	 * @var string
	 */
	private $url;

	/**
	 * Human-readable language name.
	 *
	 * @var string
	 */
	private $language;

	/**
	 * Allowed word characters in PCRE syntax.
	 *
	 * @var string
	 */
	private $word_characters;

	/**
	 * Retrieve patgen segment from TeX hyphenation pattern.
	 *
	 * @param string $pattern TeX hyphenation pattern.
	 * @return string
	 */
	function get_segment( $pattern ) {
		return preg_replace( '/[0-9]/', '', str_replace( '.', '', $pattern ) );
	}

	/**
	 * Calculate patgen sequence from TeX hyphenation pattern.
	 *
	 * @param string $pattern TeX hyphenation pattern.
	 * @return string
	 */
	function get_sequence( $pattern ) {
		$characters = mb_str_split( str_replace( '.', '', $pattern ) );
		$result = array();

		foreach ( $characters as $index => $chr ) {
			if ( ctype_digit( $chr ) ) {
				$result[] = $chr;
			} else {
				if ( ! isset( $characters[ $index - 1 ] ) || ! ctype_digit( $characters[ $index - 1 ] ) ) {
					$result[] = '0';
				}

				if ( ! isset( $characters[ $index + 1 ] ) && ! ctype_digit( $characters[ $index ] ) ) {
					$result[] = '0';
				}
			}
		}

		// Do some error checking.
		$count = count( $result );
		$count_seg = mb_strlen( $this->get_segment( $pattern ) );
		$sequence = implode( $result );

		if ( $count !== $count_seg + 1 ) {
			error_log( "Invalid segment length $count for pattern $pattern (result sequence $sequence)" );

			die( -3000 );
		}

		return $sequence;
	}

	/**
	 * Echo hyphenation pattern file for wp-Typography.
	 *
	 * @param array $patterns An array of TeX hyphenation patterns.
	 * @param array $exceptions {
	 * 		An array of hyphenation exceptions.
	 *
	 * 		@type string $key Hyphenated key (e.g. 'something' => 'some-thing').
	 * }
	 * @param array $comments An array of TeX comments.
	 */
	function write_results( array $patterns, array $exceptions, array $comments ) {
		$begin_patterns = array();
		$end_patterns   = array();
		$all_patterns   = array();

		foreach ( $patterns as $pattern ) {
			if ( preg_match( '/^\.(.+)$/', $pattern, $matches ) ) {
				$segment = $this->get_segment( $matches[1] );
				if ( ! isset( $begin_patterns[ $segment ] ) ) {
					$begin_patterns[ $segment ] = $this->get_sequence( $matches[1] );
				}
			} elseif ( preg_match( '/^(.+)\.$/', $pattern, $matches ) ) {
				$segment = $this->get_segment( $matches[1] );
				if ( ! isset( $end_patterns[ $segment ] ) ) {
					$end_patterns[ $segment ] = $this->get_sequence( $matches[1] );
				}
			} else {
				$segment = $this->get_segment( $pattern );
				if ( ! isset( $all_patterns[ $segment ] ) ) {
					$all_patterns[ $segment ] = $this->get_sequence( $pattern );
				}
			}
		}

		// Produce a nice exceptions mapping
		$json_exceptions = array();
		foreach ( $exceptions as $exception ) {
			$json_exceptions[ mb_strtolower( str_replace( '-', '', $exception ) ) ] = mb_strtolower( $exception );
		}

		$json_results = array(
			'language'         => $this->language,
			'exceptions'       => $json_exceptions,
			'max_segment_size' => max( array_map( 'mb_strlen', array_map( array( $this, 'get_segment' ), $patterns ) ) ),
			'patterns'         => array(
				'begin' => $begin_patterns,
				'end'   => $end_patterns,
				'all'   => $all_patterns,
			),
		);
	?>
/*
	Project: wp-Typography
	Project URI: https://code.mundschenk.at/wp-typography/

	File modified to place pattern and exceptions in arrays that can be understood in php files.
	This file is released under the same copyright as the below referenced original file
	Original unmodified file is available at: <?= dirname( $this->url ) . "/\n" ?>
	Original file name: <?= basename( $this->url ) . "\n" ?>

//============================================================================================================
	ORIGINAL FILE INFO

<?php
	foreach ( $comments as $comment ) {
		echo "\t\t" . $comment;
	}
?>


//============================================================================================================

*/

<?php echo json_encode( $json_results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ); ?>

<?php
	}

	/**
	 * Creates a new converter object.
	 *
	 * @param string $url      The TeX pattern file URL.
	 * @param string $language A human-readable language name.
	 */
	function __construct( $url, $language ) {
		$this->url      = $url;
		$this->language = $language;

		$this->word_characters = "\w.'ʼ᾽ʼ᾿’" .
			uchr( 8205, 8204, 768, 769, 771, 772, 775, 776, 784, 803, 805, 814, 817 ) .
			"\p{Devanagari}" . uchr( 2385, 2386 ) .
			"\p{Bengali}" .
			"\p{Gujarati}" .
			"\p{Kannada}" .
			"\p{Telugu}" .
			"\p{Malayalam}" .
			"\p{Thai}" .
			"-";
			//  2366, 2367, 2368, 2369, 2370, 2371, 2372, 2402 ) .
	}

	/**
	 * Try to match squences of TeX hyphenation exceptions.
	 *
	 * @param string $line A line from the TeX pattern file.
	 * @param array  $exceptions {
	 * 		An array of hyphenation exceptions.
	 *
	 * 		@type string $key Hyphenated key (e.g. 'something' => 'some-thing').
	 * }
	 * @return boolean
	 */
	function match_exceptions( $line, array &$exceptions ) {
		if ( preg_match( '/^\s*([\w-]+)\s*}\s*(?:%.*)?$/u', $line, $matches ) ) {
			$exceptions[] = $matches[1];
			return false;
		} if ( preg_match( '/^\s*((?:[\w-]+\s*)+)\s*}\s*(?:%.*)?$/u', $line, $matches ) ) {
			$this->match_exceptions( $matches[1], $exceptions );
			return false;
		} elseif ( preg_match( '/^\s*}\s*(?:%.*)?$/u', $line, $matches ) ) {
			return false;
		} elseif ( preg_match( '/^\s*([\w-]+)\s*(?:%.*)?$/u',  $line, $matches ) ) {
			$exceptions[] = $matches[1];
		} elseif ( preg_match( '/^\s*((?:[\w-]+\s*)+)(?:%.*)?$/u',  $line, $matches ) ) {
			// Sometimes there are multiple exceptions on a single line.
			foreach ( preg_split( '/\s+/u', $matches[1], -1, PREG_SPLIT_NO_EMPTY ) as $match ) {
				$exceptions[] = $match;
			}
		} elseif ( preg_match( '/^\s*(?:%.*)?$/u', $line, $matches ) ) {
			// Ignore comments and whitespace in exceptions.
		} else {
			echo "Error: unknown exception line $line\n";
			die(-1000);
		}

		return true;
	}

	/**
	 * Try to match a pattern.
	 *
	 * @param string $line    A line from the TeX pattern file.
	 * @param array $patterns An array of patterns.
	 * @return boolean
	 */
	function match_patterns( $line, array &$patterns ) {
		if ( preg_match( '/^\s*([' . $this->word_characters . ']+)\s*}\s*(?:%.*)?$/u', $line, $matches ) ) {
			$patterns[] = $matches[1];
			return false;
		} elseif ( preg_match( '/^\s*}\s*(?:%.*)?$/u', $line, $matches ) ) {
			return false;
		} elseif ( preg_match( '/^\s*([' . $this->word_characters . ']+)\s*(?:%.*)?$/u',  $line, $matches ) ) {
			$patterns[] = $matches[1];
		} elseif ( preg_match( '/^\s*((?:[' . $this->word_characters .  ']+\s*)+)(?:%.*)?$/u',  $line, $matches ) ) {
			// Sometimes there are multiple patterns on a single line.
			foreach ( preg_split( '/\s+/u', $matches[1], -1, PREG_SPLIT_NO_EMPTY ) as $match ) {
				$patterns[] = $match;
			}
		} elseif ( preg_match( '/^\s*(?:%.*)?$/u', $line, $matches ) ) {
			// Ignore comments and whitespace in patterns.
		} else {
			echo "Error: unknown pattern line " . clean_html( $line ) . "\n";
			die( -1000 );
		}

		return true;
	}

	/**
	 * Parse the given TeX file.
	 */
	function convert() {
		if ( ! file_exists( $this->url ) ) {
			$file_headers = @get_headers( $this->url );
			if ( $file_headers[0] === 'HTTP/1.0 404 Not Found' ) {
				echo "Error: unknown pattern file '{$this->url}'\n";
				die(-3);
			}
		}

		// Results.
		$comments   = array();
		$patterns   = array();
		$exceptions = array();

		// Status indicators.
		$reading_patterns   = false;
		$reading_exceptions = false;

		$file = new \SplFileObject( $this->url );
		while ( ! $file->eof() ) {
			$line = $file->fgets();

			if ( $reading_patterns ) {
				$reading_patterns = $this->match_patterns( $line, $patterns );
			} elseif ( $reading_exceptions ) {
				$reading_exceptions = $this->match_exceptions( $line, $exceptions );
			} else {
				// Not a pattern & not an exception.
				if ( preg_match( '/^\s*%.*$/u', $line, $matches ) ) {
					$comments[] = $line;
				} elseif ( preg_match( '/^\s*\\\patterns\s*\{\s*(.*)$/u', $line, $matches ) ) {
					$reading_patterns = $this->match_patterns( $matches[1], $patterns );
				} elseif ( preg_match( '/^\s*\\\hyphenation\s*{\s*(.*)$/u', $line, $matches ) ) {
					$reading_exceptions = $this->match_exceptions( $matches[1], $exceptions );
				} elseif ( preg_match( '/^\s*\\\endinput.*$/u', $line, $matches ) ) {
					// Ignore this line completely.
				} elseif ( preg_match( '/^\s*\\\[\w]+.*$/u', $line, $matches ) ) {
					// Treat other commands as comments unless we are matching exceptions or patterns.
					$comments[] = $line;
				} elseif ( preg_match( '/^\s*$/u', $line, $matches ) ) {
					// Do nothing.
				} else {
					echo "Error: unknown line $line\n";
					die( -1000 );
				}
			}
		}

		$this->write_results( $patterns, $exceptions, $comments );
	}
}

$shortopts = 'l:f:hvs';
$longopts = array( 'lang:', 'file:', 'help', 'version', 'single-quotes' );

$options = getopt( $shortopts, $longopts );

// Print version.
if ( isset( $options['v'] ) || isset( $options['version'] ) ) {
	echo "wp-Typography hyhpenation pattern converter 2.0-alpha\n\n";
	die( 0 );
}

// Print help.
if ( isset( $options['h'] ) || isset( $options['help'] ) ) {
	echo "Usage: convert_pattern [arguments]\n";
	echo "convert_pattern -l <language> -f <filename>\t\tconvert <filename>\n";
	echo "convert_pattern --lang <language> --file <filename>\tconvert <filename>\n";
	echo "convert_pattern -v|--version\t\t\t\tprint version\n";
	echo "convert_pattern -h|--help\t\t\t\tprint help\n";
	die( 0 );
}

// Read necessary options.
if ( isset( $options['f'] ) ) {
	$filename = $options['f'];
} elseif ( isset( $options['file'] ) ) {
	$filename = $options['file'];
}
if ( empty( $filename ) ) {
	echo "Error: no filename\n";
	die( -1 );
}

if ( isset( $options['l'] ) ) {
	$language = $options['l'];
} elseif ( isset( $options['lang'] ) ) {
	$language = $options['lang'];
}
if ( empty( $language ) ) {
	echo "Error: no language\n";
	die( -2 );
}

$converter = new Pattern_Converter( $filename, $language );
$converter->convert();
