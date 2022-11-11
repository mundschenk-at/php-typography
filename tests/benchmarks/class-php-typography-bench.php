<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2017-2022 Peter Putzer.
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
 * @BeforeMethods({"init"})
 */
class PHP_Typography_Bench {

	/**
	 * PHP_Typography instance.
	 *
	 * @var \PHP_Typography\PHP_Typography
	 */
	protected $typo;

	/**
	 * Settings instance.
	 *
	 * @var \PHP_Typography\Settings
	 */
	protected $settings;

	/**
	 * Initialize fixtures.
	 */
	public function init() {
		$this->typo     = new \PHP_Typography\PHP_Typography();
		$this->settings = new \PHP_Typography\Settings();

		$this->typo->process( '', $this->settings );
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
	 * Benchmark the process method.
	 *
	 * @ParamProviders({"provide_process_filenames"})
	 *
	 * @param  array $params The parameters.
	 */
	public function bench_process( $params ) {

		if ( isset( $params['filename'] ) ) {
			$html = \file_get_contents( $params['filename'] );
		} else {
			$html = $params['html'];
		}

		$this->typo->process( $html, $this->settings );
	}
}
