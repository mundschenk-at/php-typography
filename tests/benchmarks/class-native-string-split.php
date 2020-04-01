<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2020 Peter Putzer.
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  of the License, or ( at your option ) any later version.
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
class Native_String_Split {

	/**
	 * Number of iterations necessary to properly measure function speed.
	 *
	 * @var int
	 */
	const ITERATIONS = 100;

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

		$result           = [];
		$multibyte_length = \mb_strlen( $str, $encoding );
		for ( $i = 0; $i < $multibyte_length; $i += $length ) {
			$result[] = \mb_substr( $str, $i, $length, $encoding );
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
		$result = \preg_split( '//u', $str , -1, \PREG_SPLIT_NO_EMPTY );

		if ( $split_length > 1 ) {
			$splits = [];
			foreach ( \array_chunk( $result, $split_length ) as $chunk ) {
				$splits[] = \join( '', $chunk );
			}

			$result = $splits;
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
	public static function mb_str_split_regex( $str, $split_length = 1 ) {
		return \preg_split( "/(.{{$split_length}})/us", $str , -1, \PREG_SPLIT_NO_EMPTY | \PREG_SPLIT_DELIM_CAPTURE );
	}


	/**
	 * Wrapper for the native mb_str_split function.
	 *
	 * Unlike str_split, a $split_length less than 1 is ignored (and thus
	 * equivalent to the default).
	 *
	 * @param string $str           Required.
	 * @param int    $split_length  Optional. Default 1.
	 *
	 * @return array                An array of $split_length character chunks.
	 */
	public static function mb_str_split_wrapped( $str, $split_length = 1 ) {
		return \mb_str_split( $str, $split_length, 'UTF-8' );
	}

	/**
	 * Wrapper for the native mb_str_split function.
	 *
	 * Unlike str_split, a $split_length less than 1 is ignored (and thus
	 * equivalent to the default).
	 *
	 * @param string $str           Required.
	 * @param int    $split_length  Optional. Default 1.
	 *
	 * @return array                An array of $split_length character chunks.
	 */
	public static function mb_str_split_polyfill_native( $str, $split_length = 1 ) {
		if ( \function_exists( 'mb_str_split' ) ) {
			return \mb_str_split( $str, $split_length, 'UTF-8' );
		}

		return \preg_split( "/(.{{$split_length}})/us", $str , -1, \PREG_SPLIT_NO_EMPTY | \PREG_SPLIT_DELIM_CAPTURE );
	}

	/**
	 * Wrapper for the native mb_str_split function.
	 *
	 * Unlike str_split, a $split_length less than 1 is ignored (and thus
	 * equivalent to the default).
	 *
	 * @param string $str           Required.
	 * @param int    $split_length  Optional. Default 1.
	 *
	 * @return array                An array of $split_length character chunks.
	 */
	public static function mb_str_split_polyfill_regex( $str, $split_length = 1 ) {
		if ( \function_exists( 'mb_str_splitfoos' ) ) {
			return \mb_str_split( $str, $split_length, 'UTF-8' );
		}

		return \preg_split( "/(.{{$split_length}})/us", $str , -1, \PREG_SPLIT_NO_EMPTY | \PREG_SPLIT_DELIM_CAPTURE );
	}

	/**
	 * Wrapper for the native mb_str_split function.
	 *
	 * Unlike str_split, a $split_length less than 1 is ignored (and thus
	 * equivalent to the default).
	 *
	 * @param string $str           Required.
	 * @param int    $split_length  Optional. Default 1.
	 *
	 * @return array                An array of $split_length character chunks.
	 */
	public static function mb_str_split_polyfill_dyn( $str, $split_length = 1 ) {
		static $foo = null;

		$foo = ! isset( $foo ) ? $foo : \function_exists( 'mb_str_splitfoos' );

		if ( ! empty( $foo ) ) {
			return \mb_str_split( $str, $split_length, 'UTF-8' );
		}

		return \preg_split( "/(.{{$split_length}})/us", $str , -1, \PREG_SPLIT_NO_EMPTY | \PREG_SPLIT_DELIM_CAPTURE );
	}

	/**
	 * Provide parameters for process_bench.
	 *
	 * @return array
	 */
	public function provide_process_filenames() {
		return [
			[
				'html' => \file_get_contents( __DIR__ . '/data/example1.html' ),
			],
			[
				'html' => '<span>A short fragment 1+2=3</span>',
			],
		];
	}

	/**
	 * Benchmark mb_str_split method.
	 *
	 * @ParamProviders({"provide_process_filenames"})
	 * @Revs(10)
	 *
	 * @Skip()
	 *
	 * @param  array $params The parameters.
	 */
	public function bench_mb_str_split_421( $params ) {
		for ( $i = 0; $i < self::ITERATIONS; ++$i ) {
			self::mb_str_split_421( $params['html'] );
		}
	}

	/**
	 * Benchmark mb_str_split method.
	 *
	 * @ParamProviders({"provide_process_filenames"})
	 *
	 * @Skip()
	 *
	 * @param  array $params The parameters.
	 */
	public function bench_mb_str_split_422( $params ) {
		for ( $i = 0; $i < self::ITERATIONS; ++$i ) {
			self::mb_str_split_422( $params['html'] );
		}
	}

	/**
	 * Benchmark PHP 7.4 native mb_str_split method.
	 *
	 * @ParamProviders({"provide_process_filenames"})
	 *
	 * @param  array $params The parameters.
	 */
	public function bench_mb_str_split_native( $params ) {
		for ( $i = 0; $i < self::ITERATIONS; ++$i ) {
			\mb_str_split( $params['html'], 1, 'UTF-8' );
		}
	}

	/**
	 * Benchmark PHP 7.4 native mb_str_split method.
	 *
	 * @ParamProviders({"provide_process_filenames"})
	 *
	 * @param  array $params The parameters.
	 */
	public function bench_mb_str_split_native_wrapped( $params ) {
		for ( $i = 0; $i < self::ITERATIONS; ++$i ) {
			self::mb_str_split_wrapped( $params['html'] );
		}
	}

	/**
	 * Benchmark mb_str_split method.
	 *
	 * @ParamProviders({"provide_process_filenames"})
	 *
	 * @param  array $params The parameters.
	 */
	public function bench_mb_str_split_regex( $params ) {
		for ( $i = 0; $i < self::ITERATIONS; ++$i ) {
			self::mb_str_split_regex( $params['html'] );
		}
	}

	/**
	 * Benchmark PHP 7.4 native mb_str_split method.
	 *
	 * @ParamProviders({"provide_process_filenames"})
	 *
	 * @param  array $params The parameters.
	 */
	public function bench_mb_str_split_polyfill_native( $params ) {
		for ( $i = 0; $i < self::ITERATIONS; ++$i ) {
			self::mb_str_split_polyfill_native( $params['html'] );
		}
	}

	/**
	 * Benchmark mb_str_split method.
	 *
	 * @ParamProviders({"provide_process_filenames"})
	 *
	 * @param  array $params The parameters.
	 */
	public function bench_mb_str_split_polyfill_regex( $params ) {
		for ( $i = 0; $i < self::ITERATIONS; ++$i ) {
			self::mb_str_split_polyfill_regex( $params['html'] );
		}
	}
}
