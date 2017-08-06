<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2015-2017 Peter Putzer.
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
 *  @package wpTypography/PHPTypography/Converter
 *  @author Peter Putzer <github@mundschenk.at>
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace PHP_Typography\Bin;

define( 'WP_TYPOGRAPHY_DEBUG', true );

/**
 * Autoload parser classes
 */
require_once dirname( __DIR__ ) . '/php-typography-autoload.php';

$shortopts = 'l:f:hv';
$longopts = [ 'lang:', 'file:', 'help', 'version' ];

$options = getopt( $shortopts, $longopts );

// Print version.
if ( isset( $options['v'] ) || isset( $options['version'] ) ) {
	echo "wp-Typography hyhpenation pattern converter 2.0-beta\n\n";
	die( 0 );
}

// Print help.
if ( isset( $options['h'] ) || isset( $options['help'] ) ) {
	echo "Usage: convert_pattern [arguments]\n";
	echo "pattern2json -l <language> -f <filename>\t\tconvert <filename>\n";
	echo "pattern2json --lang <language> --file <filename>\tconvert <filename>\n";
	echo "pattern2json -v|--version\t\t\t\tprint version\n";
	echo "pattern2json -h|--help\t\t\t\tprint help\n";
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
