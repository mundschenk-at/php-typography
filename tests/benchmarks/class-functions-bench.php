<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2017-2019 Peter Putzer.
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

		$result           = [];
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

	/**
	 * Unicode uchr implementation using json_decode.
	 *
	 * @param  int $code Unicode codepoint.
	 *
	 * @return string
	 */
	public function json_uchr( $code ) {
		if ( is_array( $code ) ) {
			$json = '';
			foreach ( $code as $item ) {
				$json .= sprintf( '"\u%04x"', $item );
			}
		} else {
			$json = sprintf( '"\u%04x"', $code );
		}

		return json_decode( $json );
	}

	/**
	 * Input data for uchr tests.
	 *
	 * @return array
	 */
	public function provide_uchr_codes() {
		return [
			[ 8205, 8204, 768, 769, 771, 772, 775, 776, 784, 803, 805, 814, 817 ],
			[ 8205 ],
		];
	}

	/**
	 * Benchmark uchr method.
	 *
	 * @ParamProviders({"provide_uchr_codes"})
	 * @OutputTimeUnit("microseconds", precision=3)

	 * @param  array $params The parameters.
	 */
	public function bench_uchr( $params ) {
		\PHP_Typography\Strings::uchr( $params );
	}

	/**
	 * Benchmark uchr method.
	 *
	 * @ParamProviders({"provide_uchr_codes"})
	 * @OutputTimeUnit("microseconds", precision=3)
	 *
	 * @param  array $params The parameters.
	 */
	public function bench_json_uchr( $params ) {
		self::json_uchr( $params );
	}

	/**
	 * Benchmark uchr method.
	 *
	 * @ParamProviders({"provide_uchr_codes"})
	 * @OutputTimeUnit("microseconds", precision=3)
	 *
	 * @param  array $params The parameters.
	 */
	public function bench_intlchar_chr( $params ) {
		if ( class_exists( 'IntlChar' ) ) {
			if ( is_array( $params ) ) {
				$foo = '';
				foreach ( $params as $code ) {
					$foo .= \IntlChar::chr( $code );
				}
			} else {
				\IntlChar::chr( $params );
			}
		}
	}

	/**
	 * Associative array mapping based on https://gist.github.com/jasand-pereza/84ecec7907f003564584.
	 *
	 * @param  callable $callback Required.
	 * @param  array    $array    Required.
	 *
	 * @return array
	 */
	public static function array_manipulate( callable $callback, array $array ) {
		$new = [];

		foreach ( $array as $k => $v ) {
			$u = $callback( $k, $v );
			if ( ! empty( $u ) ) {
				$new[ \key( $u ) ] = \current( $u );
			}
		}

		return $new;
	}

	/**
	 * Provides an array_map implementation with control over resulting array's keys.
	 *
	 * @param  callable $callable A callback function that needs to $key, $value pairs.
	 *                            The callback should return tuple where the first part
	 *                            will be used as the key and the second as the value.
	 * @param  array    $array    The array.
	 *
	 * @return array
	 */
	public static function array_map_assoc( callable $callable, array $array ) {
		return array_column( array_map( $callable, array_keys( $array ), $array ), 1, 0 );
	}

	/**
	 * Input data for array_manipulate tests.
	 *
	 * @return array
	 */
	public function provide_array_map_assoc() {
		return [
			[
				'array' => [
					'foo1'  => 'bar1',
					'foo2'  => 'bar2',
					'foo3'  => 'bar3',
					'foo4'  => 'bar4',
					'foo5'  => 'bar5',
					'foo6'  => 'bar6',
					'foo7'  => 'bar7',
					'foo8'  => 'bar8',
					'foo9'  => 'bar9',
					'foo10' => 'bar10',
					'foo11' => 'bar11',
					'foo12' => 'bar12',
					'foo13' => 'bar13',
					'foo14' => 'bar14',
					'foo15' => 'bar15',
					'foo16' => 'bar16',
					'foo17' => 'bar17',
					'foo18' => 'bar18',
					'foo19' => 'bar19',
					'foo20' => 'bar20',
				],
			],
		];
	}

	/**
	 * Benchmark Arrays::array_map_assoc method.
	 *
	 * @ParamProviders({"provide_array_map_assoc"})
	 * @OutputTimeUnit("microseconds", precision=3)
	 *
	 * @param  array $params The parameters.
	 */
	public function bench_arrays_array_map_assoc( $params ) {
		self::array_manipulate(
			function( $key, $value ) {
				return [ $value, $key ];
			},
			$params['array']
		);
	}

	/**
	 * Benchmark array_manipulate method.
	 *
	 * @ParamProviders({"provide_array_map_assoc"})
	 * @OutputTimeUnit("microseconds", precision=3)
	 *
	 * @param  array $params The parameters.
	 */
	public function bench_array_manipulate( $params ) {
		self::array_map_assoc(
			function( $key, $value ) {
				return [ $value => $key ];
			},
			$params['array']
		);
	}
}
