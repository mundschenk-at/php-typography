<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2017 Peter Putzer.
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
	 * An array of CSS classes that are added for ampersands, numbers etc.
	 */
	const DEFAULT_CSS_CLASSES = [
		'caps'        => 'caps',
		'numbers'     => 'numbers',
		'amp'         => 'amp',
		'quo'         => 'quo',
		'dquo'        => 'dquo',
		'pull-single' => 'pull-single',
		'pull-double' => 'pull-double',
		'push-single' => 'push-single',
		'push-double' => 'push-double',
		'numerator'   => 'numerator',
		'denominator' => 'denominator',
		'ordinal'     => 'ordinal',
	];

	/**
	 * An array of Node_Fix implementations.
	 *
	 * @var array
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
	 * @param Node_Fix $fix   Required.
	 * @param int      $group Required. Only the constants CHARACTERS, SPACING_PRE_WORDS, SPACING_POST_WORDS, HTML_INSERTION are valid.
	 *
	 * @throws \InvalidArgumentException Group is invalid.
	 */
	public function register_node_fix( Node_Fix $fix, $group ) {
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
	public function register_token_fix( Token_Fix $fix ) {
		$this->process_words_fix->register_token_fix( $fix );
	}

	/**
	 * Sets the hyphenator cache for all registered token fixes (that require one).
	 *
	 * @param Cache $cache A hyphenator cache instance.
	 */
	public function update_hyphenator_cache( Cache $cache ) {
		$this->process_words_fix->update_hyphenator_cache( $cache );
	}

	/**
	 * Creates a new default registry instance.
	 *
	 * @param Cache|null    $cache       Optional. A hyphenatation cache instance to use. Default null.
	 * @param string[]|null $css_classes Optional. An array of CSS classes to use. Defaults to null (i.e. use the predefined classes).
	 *
	 * @return Registry
	 */
	public static function create( Cache $cache = null, array $css_classes = null ) {
		$registry = new Registry();

		if ( null === $css_classes ) {
			$css_classes = self::DEFAULT_CSS_CLASSES;
		}

		// Nodify anything that requires adjacent text awareness here.
		$registry->register_node_fix( new Node_Fixes\Smart_Maths_Fix(),          self::CHARACTERS );
		$registry->register_node_fix( new Node_Fixes\Smart_Diacritics_Fix(),     self::CHARACTERS );
		$registry->register_node_fix( new Node_Fixes\Smart_Quotes_Fix( true ),   self::CHARACTERS );
		$registry->register_node_fix( new Node_Fixes\Smart_Dashes_Fix( true ),   self::CHARACTERS );
		$registry->register_node_fix( new Node_Fixes\Smart_Ellipses_Fix( true ), self::CHARACTERS );
		$registry->register_node_fix( new Node_Fixes\Smart_Marks_Fix( true ),    self::CHARACTERS );

		// Keep spacing after smart character replacement.
		$registry->register_node_fix( new Node_Fixes\Single_Character_Word_Spacing_Fix(), self::SPACING_PRE_WORDS );
		$registry->register_node_fix( new Node_Fixes\Dash_Spacing_Fix(),                  self::SPACING_PRE_WORDS );
		$registry->register_node_fix( new Node_Fixes\Unit_Spacing_Fix(),                  self::SPACING_PRE_WORDS );
		$registry->register_node_fix( new Node_Fixes\Numbered_Abbreviation_Spacing_Fix(), self::SPACING_PRE_WORDS );
		$registry->register_node_fix( new Node_Fixes\French_Punctuation_Spacing_Fix(),    self::SPACING_PRE_WORDS );

		// Some final space manipulation.
		$registry->register_node_fix( new Node_Fixes\Dewidow_Fix(),        self::SPACING_POST_WORDS );
		$registry->register_node_fix( new Node_Fixes\Space_Collapse_Fix(), self::SPACING_POST_WORDS );

		// Everything that requires HTML injection occurs here (functions above assume tag-free content)
		// pay careful attention to functions below for tolerance of injected tags.
		$registry->register_node_fix( new Node_Fixes\Smart_Ordinal_Suffix_Fix( $css_classes['ordinal'] ),                           self::HTML_INSERTION ); // call before "style_numbers" and "smart_fractions".
		$registry->register_node_fix( new Node_Fixes\Smart_Exponents_Fix(),                                                         self::HTML_INSERTION ); // call before "style_numbers".
		$registry->register_node_fix( new Node_Fixes\Smart_Fractions_Fix( $css_classes['numerator'], $css_classes['denominator'] ), self::HTML_INSERTION ); // call before "style_numbers" and after "smart_ordinal_suffix".
		$registry->register_node_fix( new Node_Fixes\Style_Caps_Fix( $css_classes['caps'] ),                                        self::HTML_INSERTION ); // Call before "style_numbers".
		$registry->register_node_fix( new Node_Fixes\Style_Numbers_Fix( $css_classes['numbers'] ),                                  self::HTML_INSERTION ); // Call after "smart_ordinal_suffix", "smart_exponents", "smart_fractions", and "style_caps".
		$registry->register_node_fix( new Node_Fixes\Style_Ampersands_Fix( $css_classes['amp'] ),                                   self::HTML_INSERTION );
		$registry->register_node_fix( new Node_Fixes\Style_Initial_Quotes_Fix( $css_classes['quo'], $css_classes['dquo'] ),         self::HTML_INSERTION );
		$registry->register_node_fix( new Node_Fixes\Style_Hanging_Punctuation_Fix( $css_classes['push-single'], $css_classes['push-double'], $css_classes['pull-single'], $css_classes['pull-double'] ), self::HTML_INSERTION );

		// Register token fixes.
		$registry->register_token_fix( new Token_Fixes\Wrap_Hard_Hyphens_Fix() );
		$registry->register_token_fix( new Token_Fixes\Hyphenate_Compounds_Fix( $cache ) );
		$registry->register_token_fix( new Token_Fixes\Hyphenate_Fix( $cache ) );
		$registry->register_token_fix( new Token_Fixes\Wrap_URLs_Fix( $cache ) );
		$registry->register_token_fix( new Token_Fixes\Wrap_Emails_Fix() );

		return $registry;
	}
}
