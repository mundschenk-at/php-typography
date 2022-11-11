#!/usr/bin/env php
<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2017-2022 Peter Putzer.
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

/**
 * Autoload parser classes
 */
$autoload = dirname( dirname( __DIR__ ) ) . '/vendor/autoload.php';
if ( file_exists( $autoload ) ) {
	require_once $autoload;
} else {
	// We are a dependency of another project.
	require_once dirname( dirname( dirname( dirname( __DIR__ ) ) ) ) . '/autoload.php';
}

$target_directory = dirname( __DIR__ ) . '/lang';
$pattern_files    = file_get_contents( __DIR__ . '/patterns.json' );

if ( ! is_string( $pattern_files ) ) {
	echo "Error: Could not read '" . __DIR__ . "/patterns.json'\n";
	die( -3 );
}

$patterns_list = json_decode( $pattern_files, true );

foreach ( $patterns_list['list'] as $pattern ) {
	$language = $pattern['name'];
	$url      = $pattern['url'];
	$filename = $pattern['short'] . '.json';

	$converter = new Pattern_Converter( $url, $language );

	echo "Parsing $language TeX file and converting it to lang/$filename ...";

	try {
		$json_pattern = $converter->convert();
		file_put_contents( $target_directory . '/' . $filename, $json_pattern );
		echo " done\n";
	} catch ( \Exception $e ) {
		echo " error, skipping\n";
	}
}
