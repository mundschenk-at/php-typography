<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2017 Peter Putzer.
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; either version 2
 *  of the License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 *  ***
 *
 *  @package wpTypography/PHPTypography
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace PHP_Typography\Fixes\Node_Fixes;

use \PHP_Typography\DOM;
use \PHP_Typography\Settings;
use \PHP_Typography\U;

/**
 * Applies smart fractions (if enabled).
 *
 * Call before style_numbers, but after smart_ordinal_suffix.
 * Purposefully seperated from smart_math because of HTML code injection.
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 */
class Smart_Fractions_Fix extends Abstract_Node_Fix {

	/**
	 * CSS class applied to the numerator part.
	 *
	 * @var string
	 */
	protected $numerator_class;

	/**
	 * CSS class applied to the denominator part.
	 *
	 * @var string
	 */
	protected $denominator_class;

	/**
	 * Creates a new fix instance.
	 *
	 * @param string $css_numerator   CSS class applied to the numerator part.
	 * @param string $css_denominator CSS class applied to the denominator part.
	 * @param bool   $feed_compatible Optional. Default false.
	 */
	public function __construct( $css_numerator, $css_denominator, $feed_compatible = false ) {
		parent::__construct( $feed_compatible );

		$this->numerator_class   = $css_numerator;
		$this->denominator_class = $css_denominator;

	}

	/**
	 * Apply the fix to a given textnode.
	 *
	 * @param \DOMText $textnode Required.
	 * @param Settings $settings Required.
	 * @param bool     $is_title Optional. Default false.
	 */
	public function apply( \DOMText $textnode, Settings $settings, $is_title = false ) {
		if ( empty( $settings['smartFractions'] ) && empty( $settings['fractionSpacing'] ) ) {
			return;
		}

		// Various special characters and regular expressions.
		$regex      = $settings->get_regular_expressions();
		$components = $settings->get_components();

		if ( ! empty( $settings['fractionSpacing'] ) && ! empty( $settings['smartFractions'] ) ) {
			$textnode->data = preg_replace( $regex['smartFractionsSpacing'], '$1' . $settings->no_break_narrow_space() . '$2', $textnode->data );
		} elseif ( ! empty( $settings['fractionSpacing'] ) && empty( $settings['smartFractions'] ) ) {
			$textnode->data = preg_replace( $regex['smartFractionsSpacing'], '$1' . U::NO_BREAK_SPACE . '$2', $textnode->data );
		}

		if ( ! empty( $settings['smartFractions'] ) ) {
			// Escape sequences we don't want fractionified.
			$textnode->data = preg_replace( $regex['smartFractionsEscapeYYYY/YYYY'], '$1' . $components['escapeMarker'] . '$2$3$4', $textnode->data );
			$textnode->data = preg_replace( $regex['smartFractionsEscapeMM/YYYY'],   '$1' . $components['escapeMarker'] . '$2$3$4', $textnode->data );

			// Replace fractions.
			$numerator_css   = empty( $this->numerator_class ) ? '' : ' class="' . $this->numerator_class . '"';
			$denominator_css = empty( $this->denominator_class ) ? '' : ' class="' . $this->denominator_class . '"';

			$textnode->data  = preg_replace( $regex['smartFractionsReplacement'], "<sup{$numerator_css}>\$1</sup>" . U::FRACTION_SLASH . "<sub{$denominator_css}>\$2</sub>\$3", $textnode->data );

			// Unescape escaped sequences.
			$textnode->data = str_replace( $components['escapeMarker'], '', $textnode->data );
		}
	}
}
