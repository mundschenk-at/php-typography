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
 * @BeforeMethods("set_up")
 */
class DOM_Bench {

	private \DOMDocument $dom;

	/**
	 * Provide parameters for bench_process.
	 *
	 * @return array
	 */
	public function provide_dom() {

		return [
			[
				'html' => '<!DOCTYPE html><html><body><span>A short fragment 1+2=3</span></body></html>',
			],
			[
				'html' => "<!DOCTYPE html><html><body><div class=\"w-video align_none ratio_16x9\" onclick=\"return {&quot;player_html&quot;:&quot;&lt;iframe title=\&quot;Nine Inch Nails &amp;amp; David Bowie \u2013 Hurt\&quot; width=\&quot;640\&quot; height=\&quot;360\&quot; src=\&quot;https:\/\/www.youtube.com\/embed\/XalI3NR6mxc?feature=oembed\&quot; frameborder=\&quot;0\&quot; allow=\&quot;accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture\&quot; allowfullscreen&gt;&lt;\/iframe&gt;&quot;}\">Test</div></body></html>",
			],
		];
	}

	/**
	 * Set up fixtures.
	 *
	 * @param array $params  Parameters.
	 */
	public function set_up( array $params ) {
		$parser    = new \Masterminds\HTML5( [ 'disable_html_ns' => true ] );
		$this->dom = $parser->loadHTML( $params['html'] );
	}

	/**
	 * Benchmark the process method.
	 *
	 * @ParamProviders({"provide_dom"})
	 *
	 * @param  array $params The parameters.
	 */
	public function bench_get_element_by_tag_name( $params ) {
		for ( $i = 0; $i < 1000; ++$i ) {
			$body_node = $this->dom->getElementsByTagName( 'body' )->item( 0 );
		}
	}

	/**
	 * Benchmark the process method.
	 *
	 * @ParamProviders({"provide_dom"})
	 *
	 * @param  array $params The parameters.
	 */
	public function bench_xpath_query( $params ) {
		for ( $i = 0; $i < 1000; ++$i ) {
			$xpath     = new \DOMXPath( $this->dom );
			$body_node = $xpath->query( '/html/body' )->item( 0 );
		}
	}

	/**
	 * Benchmark the process method.
	 *
	 * @ParamProviders({"provide_dom"})
	 *
	 * @param  array $params The parameters.
	 */
	public function bench_xpath_query_without_object_creation( $params ) {
		$xpath = new \DOMXPath( $this->dom );

		for ( $i = 0; $i < 1000; ++$i ) {
			$body_node = $xpath->query( '/html/body' )->item( 0 );
		}
	}
}
