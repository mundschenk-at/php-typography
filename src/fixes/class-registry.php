<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2017-2022 Peter Putzer.
 *
 *  This program is free software; you can redistribute it and/or modify modify
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

namespace PHP_Typography\Fixes;

use PHP_Typography\Settings;

use PHP_Typography\Hyphenator\Cache;

use PHP_Typography\Fixes\Node_Fix;
use PHP_Typography\Fixes\Token_Fix;
use PHP_Typography\Fixes\Token_Fixes\Hyphenate_Fix;

/**
 * Manages the fixes used by PHP_Typography.
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 6.0.0
 */
class Registry {

	const CHARACTERS         = 10;
	const SPACING_PRE_WORDS  = 20;
	const PROCESS_WORDS      = 30;
	const SPACING_POST_WORDS = 40;
	const HTML_INSERTION     = 50;

	const GROUPS = [ self::CHARACTERS, self::SPACING_PRE_WORDS, self::PROCESS_WORDS, self::SPACING_POST_WORDS, self::HTML_INSERTION ];

	/**
	 * An array of Node_Fix implementations indexed by groups.
	 *
	 * @var array<int,Node_Fix[]>
	 */
	private $node_fixes = [
		self::CHARACTERS         => [],
		self::SPACING_PRE_WORDS  => [],
		self::PROCESS_WORDS      => [],
		self::SPACING_POST_WORDS => [],
		self::HTML_INSERTION     => [],
	];

	/**
	 * The token fix registry.
	 *
	 * @var Node_Fixes\Process_Words_Fix
	 */
	private $process_words_fix;

	/**
	 * Creates new registry instance.
	 */
	public function __construct() {
		// Parse and process individual words.
		$this->process_words_fix = new Node_Fixes\Process_Words_Fix();
		$this->register_node_fix( $this->process_words_fix, self::PROCESS_WORDS );
	}

	/**
	 * Retrieves the registered node fixes.
	 *
	 * @return Node_Fix[][]
	 */
	public function get_node_fixes() {
		return $this->node_fixes;
	}

	/**
	 * Registers a node fix.
	 *
	 * @since 5.0.0
	 *
	 * @param Node_Fix               $fix   Required.
	 * @param value-of<self::GROUPS> $group Required. Only the constants CHARACTERS, SPACING_PRE_WORDS, SPACING_POST_WORDS, HTML_INSERTION are valid.
	 *
	 * @throws \InvalidArgumentException Group is invalid.
	 */
	public function register_node_fix( Node_Fix $fix, $group ) : void {
		if ( isset( $this->node_fixes[ $group ] ) ) {
			$this->node_fixes[ $group ][] = $fix;
		} else {
			throw new \InvalidArgumentException( "Invalid fixer group $group." );
		}
	}

	/**
	 * Registers a token fix.
	 *
	 * @param Token_Fix $fix Required.
	 */
	public function register_token_fix( Token_Fix $fix ) : void {
		$this->process_words_fix->register_token_fix( $fix );
	}

	/**
	 * Sets the hyphenator cache for all registered token fixes (that require one).
	 *
	 * @param Cache $cache A hyphenator cache instance.
	 */
	public function update_hyphenator_cache( Cache $cache ) : void {
		$this->process_words_fix->update_hyphenator_cache( $cache );
	}

	/**
	 * Applies typography fixes to a textnode.
	 *
	 * @param \DOMText $textnode The node to process.
	 * @param Settings $settings The settings to apply.
	 * @param bool     $is_title Treat as title/heading tag if true.
	 * @param bool     $is_feed  Check for feed compatibility if true.
	 */
	public function apply_fixes( \DOMText $textnode, Settings $settings, $is_title, $is_feed ) : void {
		foreach ( $this->node_fixes as $fix_group ) {
			foreach ( $fix_group as $fix ) {
				if ( ! $is_feed || $fix->feed_compatible() ) {
					$fix->apply( $textnode, $settings, $is_title );
				}
			}
		}
	}
}
