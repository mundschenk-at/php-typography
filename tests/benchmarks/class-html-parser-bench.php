<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2017 Peter Putzer.
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
class HTML_Parser_Bench {

	/**
	 * A DOM-based HTML5 parser.
	 *
	 * @var \Masterminds\HTML5
	 */
	private $html5_parser;

	/**
	 * Initialize fixtures.
	 */
	public function init() {
		$this->html5_parser = new \Masterminds\HTML5( [ 'disable_html_ns' => true ] );
	}

	/**
	 * Provide parameters for process_bench.
	 *
	 * @return array
	 */
	public function provide_process_filenames() {
		return [

			/*
			[
				'filename' => __DIR__ . '/data/example1.html',
			],
			*/
			[
				'html' => '<span>A short fragment 1+2=3</span>',
			],
			[
				'html' => '<div class="w-video align_none ratio_16x9" onclick="return {&quot;player_html&quot;:&quot;&lt;iframe title=\&quot;Nine Inch Nails &amp;amp; David Bowie \u2013 Hurt\&quot; width=\&quot;640\&quot; height=\&quot;360\&quot; src=\&quot;https:\/\/www.youtube.com\/embed\/XalI3NR6mxc?feature=oembed\&quot; frameborder=\&quot;0\&quot; allow=\&quot;accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture\&quot; allowfullscreen&gt;&lt;\/iframe&gt;&quot;}">Test</div>',
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

		$dom           = $this->html5_parser->loadHTML( "<!DOCTYPE html><html><body>{$html}</body></html>" );
		$dom->encoding = 'UTF-8';
		$xpath         = new \DOMXPath( $dom );
		$body_node     = $xpath->query( '/html/body' )->item( 0 );

		$result = $this->html5_parser->saveHTML( $body_node->childNodes );
		if ( $html !== $result ) {
			echo "*********\n";
			echo $html;
			echo "\n";
			echo $result;
			echo "\n";

		}
	}
}
