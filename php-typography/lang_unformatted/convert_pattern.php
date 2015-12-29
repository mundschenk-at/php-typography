<?php

/**
 *  This file is part of wp-Typography.
 *
 *	Copyright 2015 Peter Putzer.
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

require_once( dirname( __DIR__ ) . '/php-typography-functions.php' );

class Pattern_Converter {

	private $url;
	private $language;
	private $quote;

	/**
	 * Retrieve patgen segment from TeX hyphenation pattern.
	 *
	 * @param string $pattern
	 * @return string
	 */
	function get_segment( $pattern ) {
		return preg_replace( '/[0-9]/', '', str_replace( '.', '', $pattern ) );
	}

	/**
	 * Calculate patgen sequence from TeX hyphenation pattern.
	 *
	 * @param string $pattern
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

		// error checking
		$count = count( $result );
		$count_seg = mb_strlen( $this->get_segment( $pattern ) );
		$sequence = implode( $result );

		if ( $count !== $count_seg + 1 ) {
			error_log("Invalid segment length $count for pattern $pattern (result sequence $sequence)" );

			die( -3000 );
		}

		return $sequence;
	}

	/**
	 * Echo hyphenation pattern file for wp-Typography.
	 *
	 * @param array  $patterns
	 * @param array  $exceptions
	 * @param array  $comments
	 * @param string $language
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

		echo "<?php \n\n";
	?>
/*
	Project: wp-Typography
	Project URI: https://code.mundschenk.at/wp-typography/

	File modified to place pattern and exceptions in arrays that can be understood in php files.
	This file is released under the same copyright as the below referenced original file
	Original unmodified file is available at: <?= dirname( $this->url ) . "\n" ?>
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

$patgenLanguage = __( <?= $this->quote . $this->language . $this->quote ?>, <?= $this->quote ?>wp-typography<?= $this->quote ?> );

$patgenExceptions = array(<?php if ( count( $exceptions ) > 0 ) echo "\n";

		foreach ( $exceptions as $exception ) {
			echo "\t{$this->quote}" . mb_strtolower( str_replace( '-', '', $exception ) ) . "{$this->quote}\t=>\t{$this->quote}" . mb_strtolower( $exception ) . "{$this->quote},\n";
		}
?>
);

$patgenMaxSeg = <?= max( array_map( 'mb_strlen', array_map( array( $this, 'get_segment' ), $patterns ) ) ) ?>;

$patgen = array(
	<?= $this->quote ?>begin<?= $this->quote ?> => array(<?php if ( count( $begin_patterns ) > 0 ) echo "\n";

		foreach ( $begin_patterns as $key => $pat ) {
			echo "\t\t{$this->quote}" . addslashes( $key ) . "{$this->quote}\t=>\t{$this->quote}" . $pat. "{$this->quote},\n";
		}

?>
	),

	<?= $this->quote ?>end<?= $this->quote ?> => array(<?php if ( count( $end_patterns ) > 0 ) echo "\n";

		foreach ( $end_patterns as $key => $pat ) {
			echo "\t\t{$this->quote}" . addslashes( $key ) . "{$this->quote}\t=>\t{$this->quote}" . $pat . "{$this->quote},\n";
		}
?>
	),

	<?= $this->quote ?>all<?= $this->quote ?> => array(<?php if ( count( $all_patterns ) > 0 ) echo "\n";

		foreach ( $all_patterns as $key => $pat ) {
			echo "\t\t{$this->quote}" . addslashes( $key ) . "{$this->quote}\t=>\t{$this->quote}" . $pat. "{$this->quote},\n";
		}
?>
	),
);

<?php
	}

	function __construct( $url, $language, $quote = '"' ) {
		$this->url      = $url;
		$this->language = $language;
		$this->quote    = $quote;
	}

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
			// sometimes there are multiple exceptions on a single line
			foreach ( preg_split( '/\s+/u', $matches[1], -1, PREG_SPLIT_NO_EMPTY ) as $match ) {
				$exceptions[] = $match;
			}
		} elseif ( preg_match( '/^\s*(?:%.*)$/u', $line, $matches ) ) {
			// ignore comments and whitespace in exceptions
		} else {
			echo "Error: unknown exception line $line\n";
			die(-1000);
		}

		return true;
	}

	function match_patterns( $line, array &$patterns ) {
		if ( preg_match( '/^\s*([\w.\'ʼ᾽ʼ᾿’]+)\s*}\s*(?:%.*)?$/u', $line, $matches ) ) {
			$patterns[] = $matches[1];
			return false;
		} elseif ( preg_match( '/^\s*}\s*(?:%.*)?$/u', $line, $matches ) ) {
			return false;
		} elseif ( preg_match( '/^\s*([\w.\'ʼ᾽ʼ᾿’]+)\s*(?:%.*)?$/u',  $line, $matches ) ) {
			$patterns[] = $matches[1];
		} elseif ( preg_match( '/^\s*((?:[\w.\'ʼ᾽ʼ᾿’]+\s*)+)(?:%.*)?$/u',  $line, $matches ) ) {
			// sometimes there are multiple patterns on a single line
			foreach ( preg_split( '/\s+/u', $matches[1], -1, PREG_SPLIT_NO_EMPTY ) as $match ) {
				$patterns[] = $match;
			}
		} elseif ( preg_match( '/^\s*(?:%.*)$/u', $line, $matches ) ) {
			// ignore comments and whitespace in patterns
		} else {
			echo "Error: unknown pattern line $line\n";
			die(-1000);
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

		// results
		$comments   = array();
		$patterns   = array();
		$exceptions = array();

		// status indicators
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
				// something else
				if ( preg_match( '/^\s*%.*$/u', $line, $matches ) ) {
					$comments[] = $line;
				} elseif ( preg_match( '/^\s*\\\patterns\s*\{\s*(.*)$/u', $line, $matches ) ) {
					$reading_patterns = $this->match_patterns( $matches[1], $patterns );
				} elseif ( preg_match( '/^\s*\\\hyphenation\s*{\s*(.*)$/u', $line, $matches ) ) {
					$reading_exceptions = $this->match_exceptions( $matches[1], $exceptions );
				} elseif ( preg_match( '/^\s*\\\endinput.*$/u', $line, $matches ) ) {
					// ignore this line completely
				} elseif ( preg_match( '/^\s*\\\[\w]+.*$/u', $line, $matches ) ) {
					// treat other commands as comments unless we are matching exceptions or patterns
					$comments[] = $line;
				} elseif ( preg_match( '/^\s*$/u', $line, $matches ) ) {
					// do nothing
				} else {
					echo "Error: unknown line $line\n";
					die(-1000);
				}
			}
		}

		$this->write_results( $patterns, $exceptions, $comments );
	}
}

$shortopts = "l:f:hvs";
$longopts = array( "lang:", "file:", "help", "version", "single-quotes" );

$options = getopt( $shortopts, $longopts );

// print version
if ( isset( $options['v'] ) || isset( $options['version'] ) ) {
	echo "wp-Typography hyhpenationa pattern converter 1.0-alpha\n\n";
	die( 0 );
}

// print help
if ( isset( $options['h'] ) || isset( $options['help'] ) ) {
	echo "Usage: convert_pattern [arguments]\n";
	echo "convert_pattern -l <language> -f <filename>\t\tconvert <filename> -s\n";
	echo "convert_pattern --lang <language> --file <filename>\tconvert <filename> --single-quotes\n";
	echo "convert_pattern -v|--version\t\t\t\tprint version\n";
	echo "convert_pattern -h|--help\t\t\t\tprint help\n";
	die( 0 );
}

// read necessary options
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

$quote = '"';
if ( isset( $options['s'] ) || isset( $options['single-quote'] ) ) {
	$quote = "'";
}

$converter = new Pattern_Converter( $filename, $language, $quote );
$converter->convert();
