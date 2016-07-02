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
	 * A file path.
	 */
	private $url;

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
	function write_results( array $patterns, $language ) {

		$json_results = array(
			'language'       => $language,
			'diacritic_words' => $patterns
		);

		echo json_encode( $json_results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
	}

	/**
	 * Creates a new converter object.
	 *
	 * @param string $url      The TeX pattern file URL.
	 */
	function __construct( $url ) {
		$this->url = $url;
	}


	/**
	 * Parse the given TeX file.
	 */
	function convert() {
		include_once $this->url;

		$this->write_results( $diacritic_words, $diacriticLanguage );
	}
}

$shortopts = 'f:hv';
$longopts = array( 'file:', 'help', 'version' );

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

$converter = new Pattern_Converter( $filename );
$converter->convert();
