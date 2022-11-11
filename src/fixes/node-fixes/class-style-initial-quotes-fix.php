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

namespace PHP_Typography\Fixes\Node_Fixes;

use PHP_Typography\DOM;
use PHP_Typography\RE;
use PHP_Typography\Settings;
use PHP_Typography\Strings;
use PHP_Typography\U;

/**
 * Styles initial quotes and guillemets (if enabled).
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 */
class Style_Initial_Quotes_Fix extends Classes_Dependent_Fix {

	/**
	 * CSS class for single quotes.
	 *
	 * @var string
	 */
	protected $single_quote_class;

	/**
	 * CSS class for double quotes.
	 *
	 * @var string
	 */
	protected $double_quote_class;

	/**
	 * Creates a new classes dependent fix.
	 *
	 * @param string $css_single      Required.
	 * @param string $css_double      Required.
	 * @param bool   $feed_compatible Optional. Default false.
	 */
	public function __construct( $css_single, $css_double, $feed_compatible = false ) {
		parent::__construct( [ $css_single, $css_double ], $feed_compatible );

		$this->single_quote_class = $css_single;
		$this->double_quote_class = $css_double;
	}

	/**
	 * Apply the fix to a given textnode.
	 *
	 * @since 6.0.0 The method was accidentally made public and is now protected.
	 *
	 * @param \DOMText $textnode Required.
	 * @param Settings $settings Required.
	 * @param bool     $is_title Optional. Default false.
	 */
	protected function apply_internal( \DOMText $textnode, Settings $settings, $is_title = false ) {
		if ( empty( $settings[ Settings::STYLE_INITIAL_QUOTES ] ) || empty( $settings[ Settings::INITIAL_QUOTE_TAGS ] ) || null !== DOM::get_previous_textnode( $textnode ) ) {
			return;
		}

		$node_data = $textnode->data;

		// Check encoding.
		$f = Strings::functions( $node_data );
		if ( empty( $f ) ) {
			return;
		}

		$first_character = $f['substr']( $node_data, 0, 1 );

		if ( self::is_single_quote( $first_character ) ) {
			$span_class = $this->single_quote_class;
		} elseif ( self::is_double_quote( $first_character ) ) {
			$span_class = $this->double_quote_class;
		}

		if ( ! empty( $span_class ) ) {
			// Assume page title is <h2>.
			$block_level_parent = $is_title ? 'h2' : DOM::get_block_parent_name( $textnode );

			if ( ! empty( $block_level_parent ) && isset( $settings[ Settings::INITIAL_QUOTE_TAGS ][ $block_level_parent ] ) ) {
				$textnode->data = RE::escape_tags( '<span class="' . $span_class . '">' ) . $first_character . RE::escape_tags( '</span>' ) . $f['substr']( $node_data, 1, $f['strlen']( $node_data ) );
			}
		}
	}

	/**
	 * Checks if the given string is a "single" quote character.
	 *
	 * @param string $quote Required.
	 *
	 * @return bool
	 */
	private static function is_single_quote( $quote ) {
		return ( "'" === $quote || U::SINGLE_QUOTE_OPEN === $quote || U::SINGLE_LOW_9_QUOTE === $quote || U::SINGLE_ANGLE_QUOTE_OPEN === $quote || U::SINGLE_ANGLE_QUOTE_CLOSE === $quote || ',' === $quote );
	}

	/**
	 * Checks if the given string is a "single" quote character.
	 *
	 * @param string $quote Required.
	 *
	 * @return bool
	 */
	private static function is_double_quote( $quote ) {
		return ( '"' === $quote || U::DOUBLE_QUOTE_OPEN === $quote || U::GUILLEMET_OPEN === $quote || U::GUILLEMET_CLOSE === $quote || U::DOUBLE_LOW_9_QUOTE === $quote );
	}
}
