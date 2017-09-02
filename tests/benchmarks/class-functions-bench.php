<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2017 Peter Putzer.
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; either version 2
 *  of the License, or ( at your option ) any later version.
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
 *  @package mundschenk-at/php-typography/tests
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace PHP_Typography\Tests\Benchmarks;

/**
 * Processing benchmark.
 *
 * @Iterations(10)
 * @Revs(100)
 * @OutputTimeUnit("milliseconds", precision=1)
 */
class Functions_Bench {

	/**
	 * Multibyte-safe str_split function.
	 *
	 * @param string $str      Required.
	 * @param int    $length   Optional. Default 1.
	 * @param string $encoding Optional. Default 'UTF-8'.
	 */
	public static function mb_str_split_421( $str, $length = 1, $encoding = 'UTF-8' ) {
		if ( $length < 1 ) {
			return false;
		}

		$result = [];
		$multibyte_length = mb_strlen( $str, $encoding );
		for ( $i = 0; $i < $multibyte_length; $i += $length ) {
			$result[] = mb_substr( $str, $i, $length, $encoding );
		}

		return $result;
	}

	/**
	 * Multibyte-safe str_split function.
	 *
	 * Unlike str_split, a $split_length less than 1 is ignored (and thus
	 * equivalent to the default).
	 *
	 * @param string $str           Required.
	 * @param int    $split_length  Optional. Default 1.
	 *
	 * @return array                An array of $split_length character chunks.
	 */
	public static function mb_str_split_422( $str, $split_length = 1 ) {
		$result = preg_split( '//u', $str , -1, PREG_SPLIT_NO_EMPTY );

		if ( $split_length > 1 ) {
			$splits = [];
			foreach ( array_chunk( $result, $split_length ) as $chunk ) {
				$splits[] = join( '', $chunk );
			}

			$result = $splits;
		}

		return $result;
	}

	/**
	 * Provide parameters for process_bench.
	 *
	 * @return array
	 */
	public function provide_process_filenames() {
		return [
			[
				'filename' => __DIR__ . '/data/example1.html',
			],
			[
				'html'     => '<span>A short fragment 1+2=3</span>',
			],
		];
	}

	/**
	 * Benchmark mb_str_split method.
	 *
	 * @ParamProviders({"provide_process_filenames"})
	 *
	 * @param  array $params The parameters.
	 */
	public function bench_mb_str_split_421( $params ) {

		if ( isset( $params['filename'] ) ) {
			$html = \file_get_contents( $params['filename'] );
		} else {
			$html = $params['html'];
		}

		self::mb_str_split_421( $html );
	}

	/**
	 * Benchmark mb_str_split method.
	 *
	 * @ParamProviders({"provide_process_filenames"})
	 *
	 * @param  array $params The parameters.
	 */
	public function bench_mb_str_split_422( $params ) {

		if ( isset( $params['filename'] ) ) {
			$html = \file_get_contents( $params['filename'] );
		} else {
			$html = $params['html'];
		}

		self::mb_str_split_422( $html );
	}
}
