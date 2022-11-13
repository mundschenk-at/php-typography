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

use PHP_Typography\Fixes\Node_Fix;
use PHP_Typography\Fixes\Token_Fix;

use PHP_Typography\Hyphenator\Cache;


/**
 * A registry implementation containing the default fixes for PHP_Typography.
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 6.0.0
 */
class Default_Registry extends Registry {

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
	 * Creates new default registry instance.
	 *
	 * @param Cache|null $cache       Optional. A hyphenatation cache instance to use. Default null.
	 * @param string[]   $css_classes Optional. An array of CSS classes to use. Defaults to null (i.e. use the predefined classes).
	 */
	public function __construct( Cache $cache = null, array $css_classes = [] ) {
		parent::__construct();

		if ( empty( $css_classes ) ) {
			$css_classes = self::DEFAULT_CSS_CLASSES;
		}

		// Initialize node fixes.
		foreach ( self::get_default_node_fixes() as $group => $node_fixes ) {
			/**
			 * Iterate over the node fixes and their additional parameters.
			 *
			 *  @var Node_Fix $fix A node fix class.
			 */
			foreach ( $node_fixes as $fix => $params ) {
				$arguments = [];

				if ( ! empty( $params['classes'] ) ) {
					$arguments += \array_map(
						function( $index ) use ( $css_classes ) {
							return $css_classes[ $index ];
						},
						$params['classes']
					);
				}

				if ( isset( $params['feed'] ) ) {
					$arguments += [ $params['feed'] ];
				}

				$this->register_node_fix( new $fix( ...$arguments ), $group );
			}
		}

		/**
		 * Also register the token fixes.
		 *
		 *  @var Token_Fix $fix A token fix class.
		 */
		foreach ( self::get_default_token_fixes() as $fix => $params ) {
			$arguments = [];

			if ( ! empty( $params['cache'] ) ) {
				$arguments += [ $cache ];
			}

			$this->register_token_fix( new $fix( ...$arguments ) );
		}
	}

	/**
	 * Returns a configuration array for the default node fixes.
	 *
	 * @return array<value-of<Registry::GROUPS>,array<class-string,mixed[]>> {
	 *     @type array $group {
	 *           A group of fixes.
	 *
	 *           @type array $fix_class Additional parameters for the fix constructor.
	 *     }
	 * }
	 */
	protected static function get_default_node_fixes() {
		return [
			self::CHARACTERS         => [
				// Nodify anything that requires adjacent text awareness here.
				Node_Fixes\Smart_Maths_Fix::class      => [],
				Node_Fixes\Smart_Diacritics_Fix::class => [],
				Node_Fixes\Smart_Quotes_Fix::class     => [ 'feed' => true ],
				Node_Fixes\Smart_Dashes_Fix::class     => [ 'feed' => true ],
				Node_Fixes\Smart_Ellipses_Fix::class   => [ 'feed' => true ],
				Node_Fixes\Smart_Marks_Fix::class      => [ 'feed' => true ],
				Node_Fixes\Smart_Area_Units_Fix::class => [ 'feed' => true ],
			],

			self::SPACING_PRE_WORDS  => [
				// Keep spacing after smart character replacement.
				Node_Fixes\Single_Character_Word_Spacing_Fix::class => [],
				Node_Fixes\Dash_Spacing_Fix::class                  => [],
				Node_Fixes\Unit_Spacing_Fix::class                  => [],
				Node_Fixes\Numbered_Abbreviation_Spacing_Fix::class => [],
				Node_Fixes\French_Punctuation_Spacing_Fix::class    => [],
			],

			self::SPACING_POST_WORDS => [
				// Some final space manipulation.
				Node_Fixes\Dewidow_Fix::class        => [],
				Node_Fixes\Space_Collapse_Fix::class => [],
			],

			self::HTML_INSERTION     => [
				// Everything that requires HTML injection occurs here (functions above assume tag-free content)
				// pay careful attention to functions below for tolerance of injected tags.
				Node_Fixes\Smart_Ordinal_Suffix_Fix::class      => [
					// call before "Style_Numbers_Fix" and "Smart_Fractions_Fix".
					'classes' => [ 'ordinal' ],
				],
				Node_Fixes\Smart_Exponents_Fix::class           => [
					// call before "Style_Numbers_Fix".
				],
				Node_Fixes\Smart_Fractions_Fix::class           => [
					// call before "Style_Numbers_Fix" and after "Smart_Ordinal_Suffix_Fix".
					'classes' => [ 'numerator', 'denominator' ],
				],
				Node_Fixes\Style_Caps_Fix::class                => [
					// Call before "Style_Numbers_Fix".
					'classes' => [ 'caps' ],
				],
				Node_Fixes\Style_Numbers_Fix::class             => [
					// Call after "Smart_Ordinal_Suffix_Fix", "Smart_Exponents_Fix", "Smart_Fractions_Fix", and "Style_Caps_Fix".
					'classes' => [ 'numbers' ],
				],
				Node_Fixes\Style_Ampersands_Fix::class          => [
					'classes' => [ 'amp' ],
				],
				Node_Fixes\Style_Initial_Quotes_Fix::class      => [
					'classes' => [ 'quo', 'dquo' ],
				],
				Node_Fixes\Style_Hanging_Punctuation_Fix::class => [
					'classes' => [ 'push-single', 'push-double', 'pull-single', 'pull-double' ],
				],
			],
		];
	}

	/**
	 * Returns a configuration array for the default token fixes.
	 *
	 * @return array<class-string,mixed[]>
	 */
	protected static function get_default_token_fixes() {
		return [
			Token_Fixes\Wrap_Hard_Hyphens_Fix::class   => [],
			Token_Fixes\Smart_Dashes_Hyphen_Fix::class => [],
			Token_Fixes\Hyphenate_Compounds_Fix::class => [ 'cache' => true ],
			Token_Fixes\Hyphenate_Fix::class           => [ 'cache' => true ],
			Token_Fixes\Wrap_URLs_Fix::class           => [ 'cache' => true ],
			Token_Fixes\Wrap_Emails_Fix::class         => [],
		];
	}
}
