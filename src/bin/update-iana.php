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

$source_file = 'https://data.iana.org/TLD/tlds-alpha-by-domain.txt';
$target_file = dirname( __DIR__ ) . '/IANA/tlds-alpha-by-domain.txt';

if ( ! file_exists( $source_file ) && 404 === File_Operations::get_http_response_code( $source_file ) ) {
	echo "Error: unknown TLD file '{$source_file}'\n";
	die( -3 );
}

try {
	echo 'Trying to update IANA top-level domain list ...';
	$domain_list = file_get_contents( $source_file );

	if ( ! is_string( $domain_list ) ) {
		echo " error retrieving TLD file '{$source_file}'\n";
		die( -3 );
	}

	// Ensure directory exists.
	if ( ! is_dir( dirname( $target_file ) ) ) {
		mkdir( dirname( $target_file ), 0755, true );
	}

	$file = new \SplFileObject( $target_file, 'w' );
	if ( 0 === $file->fwrite( $domain_list ) ) {
		echo " error writing file\n";
	} else {
		echo " done\n";
	}
} catch ( \Exception $e ) {
	echo " error\n";
}
