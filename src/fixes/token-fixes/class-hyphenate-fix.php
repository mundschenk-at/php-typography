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
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace PHP_Typography\Fixes\Token_Fixes;

use PHP_Typography\Fixes\Token_Fix;
use PHP_Typography\DOM;
use PHP_Typography\Hyphenator;
use PHP_Typography\Hyphenator\Cache;
use PHP_Typography\Settings;
use PHP_Typography\Text_Parser\Token;
use PHP_Typography\U;

/**
 * Hyphenates a given text fragment (if enabled).
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 */
class Hyphenate_Fix extends Abstract_Token_Fix {
	/**
	 * An array of ( $tag => true ) for quick checking with `isset`.
	 *
	 * @var array<string,bool>
	 */
	private $heading_tags = [
		'h1' => true,
		'h2' => true,
		'h3' => true,
		'h4' => true,
		'h5' => true,
		'h6' => true,
	];

	/**
	 * The cache for Hyphenator instances.
	 *
	 * @var Hyphenator\Cache
	 */
	protected $cache;

	/**
	 * Creates a new fix instance.
	 *
	 * @param Cache|null $cache Optional. Default null.
	 * @param int        $target          Optional. Default Token_Fix::WORDS.
	 * @param bool       $feed_compatible Optional. Default false.
	 */
	public function __construct( Cache $cache = null, $target = Token_Fix::WORDS, $feed_compatible = false ) {
		parent::__construct( $target, $feed_compatible );

		if ( null === $cache ) {
			$cache = new Hyphenator\Cache();
		}

		$this->cache = $cache;
	}

	/**
	 * Apply the tweak to a given textnode.
	 *
	 * @param Token[]       $tokens   Required.
	 * @param Settings      $settings Required.
	 * @param bool          $is_title Optional. Default false.
	 * @param \DOMText|null $textnode Optional. Default null.
	 *
	 * @return Token[] An array of tokens.
	 */
	public function apply( array $tokens, Settings $settings, $is_title = false, \DOMText $textnode = null ) {
		if ( empty( $settings[ Settings::HYPHENATION ] ) ) {
			return $tokens; // abort.
		}

		$is_heading = false;
		if ( null !== $textnode && ! empty( $textnode->parentNode ) ) {
			$block_level_parent = DOM::get_block_parent_name( $textnode );

			if ( ! empty( $block_level_parent ) && isset( $this->heading_tags[ $block_level_parent ] ) ) {
				$is_heading = true;
			}
		}

		if ( empty( $settings[ Settings::HYPHENATE_HEADINGS ] ) && ( $is_title || $is_heading ) ) {
			return $tokens; // abort.
		}

		// Call functionality as seperate function so it can be run without test for setting[ Settings::HYPHENATION ] - such as with url wrapping.
		return $this->do_hyphenate( $tokens, $settings );
	}

	/**
	 * Really hyphenates given text fragment.
	 *
	 * @param Token[]  $tokens Filtered to words.
	 * @param Settings $settings           The settings to apply.
	 * @param string   $hyphen             Hyphenation character. Optional. Default is the soft hyphen character (`&shy;`).
	 *
	 * @return Token[] The hyphenated text tokens.
	 */
	protected function do_hyphenate( array $tokens, Settings $settings, $hyphen = U::SOFT_HYPHEN ) {
		if ( empty( $settings[ Settings::HYPHENATION_MIN_LENGTH ] ) || empty( $settings[ Settings::HYPHENATION_MIN_BEFORE ] ) ) {
			return $tokens;
		}

		return $this->get_hyphenator( $settings )->hyphenate( $tokens, $hyphen, ! empty( $settings[ Settings::HYPHENATE_TITLE_CASE ] ), $settings[ Settings::HYPHENATION_MIN_LENGTH ], $settings[ Settings::HYPHENATION_MIN_BEFORE ], $settings[ Settings::HYPHENATION_MIN_AFTER ] );
	}

	/**
	 * Retrieves the hyphenator instance.
	 *
	 * @param Settings $settings The settings to apply.
	 *
	 * @return Hyphenator
	 */
	public function get_hyphenator( Settings $settings ) {
		$lang       = $settings[ Settings::HYPHENATION_LANGUAGE ];
		$exceptions = (array) $settings[ Settings::HYPHENATION_CUSTOM_EXCEPTIONS ];
		$hyphenator = $this->cache->get_hyphenator( $lang );

		if ( null === $hyphenator ) {
			$hyphenator = new Hyphenator( $lang, $exceptions );
			$this->cache->set_hyphenator( $lang, $hyphenator );
		} else {
			$hyphenator->set_language( $lang ); // just for insurance.
			$hyphenator->set_custom_exceptions( $exceptions );
		}

		return $hyphenator;
	}

	/**
	 * Injects an existing Hyphenator instance (to facilitate language caching).
	 *
	 * @param Hyphenator\Cache $cache Required.
	 */
	public function set_hyphenator_cache( Hyphenator\Cache $cache ) : void {
		$this->cache = $cache;
	}
}
