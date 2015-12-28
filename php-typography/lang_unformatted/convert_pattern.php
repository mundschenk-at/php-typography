<?php

/**
 * Multibyte-safe str_split function.
 *
 * @param string $str
 * @param int    $length Optional. Default 1.
 * @param string $encoding Optional. Default 'UTF-8'.
 */
function mb_str_split( $str, $length = 1, $encoding = 'UTF-8' ) {
	if ( $length < 1 ) {
		return false;
	}

	$result = array();
	$multibyte_length = mb_strlen( $str, $encoding );
	for ( $i = 0; $i < $multibyte_length; $i += $length ) {
		$result[] = mb_substr( $str, $i, $length, $encoding );
	}

	return $result;
}

function get_segment( $pattern ) {
	return preg_replace( '/[0-9]/', '', str_replace( '.', '', $pattern ) );
}

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
	$count_seg = mb_strlen( get_segment( $pattern ) );
	if ( $count !== $count_seg + 1 ) {
		error_log("Invalid segment length $count for pattern $pattern (result sequence " . implode( $result ) . ")" );

		die( -3000 );
	}

	return implode( $result );
}

function write_results( array $patterns, array $exceptions, array $comments, $language ) {
	$begin_patterns = array();
	$end_patterns   = array();
	$all_patterns   = array();

	foreach ( $patterns as $pattern ) {
		if ( preg_match( '/^\.(.+)$/', $pattern, $matches ) ) {
			$begin_patterns[ get_segment( $matches[1] ) ] = get_sequence( $matches[1] );
		} elseif ( preg_match( '/^(.+)\.$/', $pattern, $matches ) ) {
			$end_patterns[ get_segment( $matches[1] ) ]   = get_sequence( $matches[1] );
		} else {
			$all_patterns[ get_segment( $pattern ) ]      = get_sequence( $pattern );
		}
	}

?>
/**
 * Project: PHP Typography
 * Project URI: http://kingdesk.com/projects/php-typography/
 *
 * File modified to place pattern and exceptions in arrays that can be understood in php files.
 * This file is released under the same copyright as the below referenced original file
 * Original unmodified file is available at: http://mirror.unl.edu/ctan/language/hyph-utf8/tex/generic/hyph-utf8/patterns/
 * Original file name: hyph-_______________.tex
 *
 * ============================================================================================================
 *
<?php
	foreach ( $comments as $comment ) {
		echo " * " . $comment;
	}
?>

$patgenLanguage = __( '<?php echo $language ?>', 'wp-Typography' );

$patgenExceptions = array(<?php

	foreach ( $exceptions as $exception ) {
		echo "\t'" . mb_strtolower( str_replace( '-', '', $exception ) ) . "'\t=>\t'" . $exception . "',\n";
	}

?>);

$patgenMaxSeg = <?php echo max( array_map( 'strlen', array_map( 'get_segment', $patterns ) ) ); ?>;

$patgen = array(
	'begin' => array(<?php

	foreach ( $begin_patterns as $key => $pat ) {
		echo "\t'" . $key . "'\t=>\t'" . $pat . "',\n";
	}

?>
	);

	'end'	=> array(<?php

	foreach ( $end_patterns as $key => $pat ) {
		echo "\t'" . $key . "'\t=>\t'" . $pat . "',\n";
	}

?>
	);

	'all'	=> array(<?php

	foreach ( $all_patterns as $key => $pat ) {
		echo "\t'" . $key . "'\t=>\t'" . $pat . "',\n";
	}

?>
	);
);

<?php
}

function convert_pattern( $filename, $language ) {
	if ( file_exists( $filename ) ) {
		echo "Converting file $filename for language $language\n";

		// results
		$comments   = array();
		$patterns   = array();
		$exceptions = array();

		// status indicators
		$reading_patterns   = false;
		$reading_exceptions = false;

		$file = new \SplFileObject( $filename );
		while ( ! $file->eof() ) {
			$line = $file->fgets();

			if ( $reading_patterns ) {
				if ( preg_match( '/^\s*([\w.]+)\s*}\s*(?:%.*)?$/u', $line, $matches ) ) {
					$reading_patterns = false;
 					$patterns[] = $matches[1];
				} elseif ( preg_match( '/^\s*}\s*(?:%.*)?$/u', $line, $matches ) ) {
					$reading_patterns = false;
				} elseif ( preg_match( '/^\s*([\w.]+)\s*(?:%.*)?$/u',  $line, $matches ) ) {
 					$patterns[] = $matches[1];
				} elseif ( preg_match( '/^\s*(?:%.*)$/u', $line, $matches ) ) {
					// ignore comments and whitespace in patterns
				} else {
					echo "Error: unknown line $line\n";
					die(-1000);
				}
 			} elseif ( $reading_exceptions ) {
 				if ( preg_match( '/^\s*([\w-]+)\s*}\s*(?:%.*)?$/u', $line, $matches ) ) {
 					$reading_exceptions = false;
 					$exceptions[] = $matches[1];
 				} elseif ( preg_match( '/^\s*}\s*(?:%.*)?$/u', $line, $matches ) ) {
					$reading_exceptions = false;
				} elseif ( preg_match( '/^\s*([\w-]+)\s*(?:%.*)?$/u',  $line, $matches ) ) {
 					$exceptions[] = $matches[1];
 				} elseif ( preg_match( '/^\s(?:*%.*)$/u', $line, $matches ) ) {
					// ignore comments and whitespace in exceptions
				} else {
					echo "Error: unknown line $line\n";
					die(-1000);
				}
			} else {
				// something else

				if ( preg_match( '/^\s*%.*$/u', $line, $matches ) ) {
					// comment line
					$comments[] = $line;

				} elseif ( preg_match( '/^\s*\\\patterns\s*\{\s*(?:%.*)?$/u', $line, $matches ) ) {
					$reading_patterns = true;
				} elseif ( preg_match( '/^\s*\\\hyphenation\s*{\s*(?:%.*)?$/u', $line, $matches ) ) {
					$reading_exceptions = true;
				} elseif ( preg_match( '/^\s*$/u', $line, $matches ) ) {
					// do nothing
				} else {
					echo "Error: unknown line $line\n";
					die(-1000);
				}
			}
		}

		write_results( $patterns, $exceptions, $comments, $language );

	} else {
		echo "Error: unknown pattern file '$filename'\n";

		die(-3);
	}
}

$shortopts = "l:f:hv";
$longopts = array( "lang:", "file:", "help", "version" );

$options = getopt( $shortopts, $longopts );

// print version
if ( isset( $options['v'] ) || isset( $options['version'] ) ) {
	echo "wp-Typography hyhpenationa pattern converter 1.0-alpha\n\n";
	die( 0 );
}

// print help
if ( isset( $options['h'] ) || isset( $options['help'] ) ) {
	echo "Usage: convert_pattern [arguments]\n";
	echo "convert_pattern -l <language> -f <filename>\t\tconvert <filename>\n";
	echo "convert_pattern --lang <language> --file <filename>\tconvert <filename>\n";
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

convert_pattern( $filename, $language );
