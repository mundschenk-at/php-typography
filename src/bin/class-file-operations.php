<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2017-2019 Peter Putzer.
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
 * Encapsulate some common file operations (including on remote files).
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 */
abstract class File_Operations {

	/**
	 * Retrieve a HTTP response code via cURL.
	 *
	 * @param  string $url Required.
	 *
	 * @return int
	 */
	public static function get_http_response_code( $url ) {

		$curl = curl_init();
		curl_setopt_array(
			$curl,
			[
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_URL            => $url,
			]
		);
		curl_exec( $curl );
		$response_code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
		curl_close( $curl );

		return $response_code;
	}
}
