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

use PHP_Typography\Hyphenator;

/**
 * Differnet possible implementations of Hyphenator\Cache serialization benchmark.
 *
 * @Iterations(10)
 * @Revs(100)
 * @OutputTimeUnit("milliseconds", precision=1)
 * @BeforeMethods({"init"})
 */
class Hyphenator_Cache_Bench {

	/**
	 * Hyphenator instance.
	 *
	 * @var Hyphenator
	 */
	protected $hyphenator;

	/**
	 * Serialized version of same hyphenator.
	 *
	 * @var string
	 */
	protected $serialized;

	/**
	 * Serialized and compressed.
	 *
	 * @var string
	 */
	protected $compressed;

	/**
	 * Initialize fixtures.
	 */
	public function init() {
		$this->hyphenator = new Hyphenator( 'de' );
		$this->serialized = serialize( $this->hyphenator ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
		$this->compressed = base64_encode( gzcompress( $this->serialized ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}

	/**
	 * Benchmark new object creation.
	 */
	public function bench_new_hyphenator() {
		$de_hyphen = new Hyphenator( 'de' );
	}

	/**
	 * Benchmark new object creation.
	 */
	public function bench_serialized_hyphenator() {
		$de_hyphen = unserialize( $this->serialized ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
	}

	/**
	 * Benchmark new object creation.
	 */
	public function bench_compressed_serialized_hyphenator() {
		$de_hyphen = unserialize( gzuncompress( base64_decode( $this->compressed ) ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
	}

	/**
	 * Benchmark new object creation.
	 */
	public function bench_unserialize_serialized_hyphenator() {
		$de_hyphen = unserialize( serialize( $this->hyphenator ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
	}

	/**
	 * Benchmark new object creation.
	 */
	public function bench_compressed_unserialize_serialized_hyphenator() {
		$de_hyphen = unserialize( gzuncompress( base64_decode( base64_encode( gzcompress( serialize( $this->hyphenator ) ) ) ) ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
	}

	/**
	 * Benchmark new object creation.
	 */
	public function bench_gzencoded_unserialize_serialized_hyphenator() {
		$de_hyphen = unserialize( gzdecode( base64_decode( base64_encode( gzencode( serialize( $this->hyphenator ) ) ) ) ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
	}
}
