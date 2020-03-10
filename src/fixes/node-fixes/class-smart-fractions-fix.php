<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2017-2019 Peter Putzer.
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

namespace PHP_Typography\Fixes\Node_Fixes;

use PHP_Typography\DOM;
use PHP_Typography\RE;
use PHP_Typography\Settings;
use PHP_Typography\U;

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

	const SPACING = '/\b(\d+)\s(\d+\s?\/\s?\d+)\b/';

	const FRACTION_MATCHING = '/
		# lookbehind assertion: makes sure we are not messing up a url
		(?<=\A|\s|' . U::NO_BREAK_SPACE . '|' . U::NO_BREAK_NARROW_SPACE . ')

		(\d+)

		# strip out any zero-width spaces inserted by wrap_hard_hyphens
		(?:\s?\/\s?' . U::ZERO_WIDTH_SPACE . '?)

		(
			# lookahead assertion: do not make fractions from x:x if x > 1
			(?:
				# ignore x:x where x > 1
				(?!\1(?:[^0-9]|\Z)) |

				# but allow 1:1
				(?=\1)(?=1(?:[^0-9]|\Z))
			)

			# Any numbers, except those above
			\d+
		)
		(
			# handle fractions followed by prime symbols
			(?:' . U::SINGLE_PRIME . '|' . U::DOUBLE_PRIME . ')?

			# handle ordinals after fractions
			(?:\<sup\>(?:st|nd|rd|th)<\/sup\>)?

			# makes sure we are not messing up a url
			(?:\Z|\s|' . U::NO_BREAK_SPACE . '|' . U::NO_BREAK_NARROW_SPACE . '|\.|,|\!|\?|\)|\;|\:|\'|")
		)
		/Sxu';

	const ESCAPE_DATE_MM_YYYY = '/
			# capture valid one- or two-digit months
			( \b (?: 0?[1-9] | 1[0-2] ) )

			# capture any zero-width spaces inserted by wrap_hard_hyphens
			(\s?\/\s?' . U::ZERO_WIDTH_SPACE . '?)

			# handle 4-decimal years
			( [12][0-9]{3}\b )

		/Sxu';

	/**
	 * Regular expression matching consecutive years in the format YYYY/YYYY+1.
	 *
	 * @var string
	 */
	protected $escape_consecutive_years;

	/**
	 * Replacement expression including optional CSS classes.
	 *
	 * @var string
	 */
	protected $replacement;

	/**
	 * Creates a new fix instance.
	 *
	 * @param string $css_numerator   CSS class applied to the numerator part.
	 * @param string $css_denominator CSS class applied to the denominator part.
	 * @param bool   $feed_compatible Optional. Default false.
	 */
	public function __construct( $css_numerator, $css_denominator, $feed_compatible = false ) {
		parent::__construct( $feed_compatible );

		// Escape consecutive years.
		$year_regex = [];
		for ( $year = 1900; $year < 2100; ++$year ) {
			$year_regex[] = "(?: ( $year ) (\s?\/\s?" . U::ZERO_WIDTH_SPACE . '?) ( ' . ( $year + 1 ) . ' ) )';
		}
		$this->escape_consecutive_years = '/\b (?| ' . implode( '|', $year_regex ) . ' ) \b/Sxu';

		// Replace fractions.
		$numerator_css     = empty( $css_numerator ) ? '' : ' class="' . $css_numerator . '"';
		$denominator_css   = empty( $css_denominator ) ? '' : ' class="' . $css_denominator . '"';
		$this->replacement = RE::escape_tags( "<sup{$numerator_css}>\$1</sup>" . U::FRACTION_SLASH . "<sub{$denominator_css}>\$2</sub>\$3" );
	}

	/**
	 * Apply the fix to a given textnode.
	 *
	 * @since 7.0.0 All parameters are now required.
	 *
	 * @param \DOMText $textnode The DOM node.
	 * @param Settings $settings The settings to apply.
	 * @param bool     $is_title Indicates if the processed tokens occur in a title/heading context.
	 *
	 * @return void
	 */
	public function apply( \DOMText $textnode, Settings $settings, $is_title ) {
		if ( empty( $settings[ Settings::SMART_FRACTIONS ] ) && empty( $settings[ Settings::FRACTION_SPACING ] ) ) {
			return;
		}

		// Cache textnode content.
		$node_data = $textnode->data;

		if ( ! empty( $settings[ Settings::FRACTION_SPACING ] ) && ! empty( $settings[ Settings::SMART_FRACTIONS ] ) ) {
			$node_data = \preg_replace( self::SPACING, '$1' . U::NO_BREAK_NARROW_SPACE . '$2', $node_data );
		} elseif ( ! empty( $settings[ Settings::FRACTION_SPACING ] ) && empty( $settings[ Settings::SMART_FRACTIONS ] ) ) {
			$node_data = \preg_replace( self::SPACING, '$1' . U::NO_BREAK_SPACE . '$2', $node_data );
		}

		if ( ! empty( $settings[ Settings::SMART_FRACTIONS ] ) ) {
			$node_data = \preg_replace(
				[
					// Escape sequences we don't want fractionified.
					$this->escape_consecutive_years,
					self::ESCAPE_DATE_MM_YYYY,

					// Replace fractions.
					self::FRACTION_MATCHING,
				],
				[
					'$1' . RE::ESCAPE_MARKER . '$2$3',
					'$1' . RE::ESCAPE_MARKER . '$2$3',

					$this->replacement,
				],
				$node_data
			);

			// Unescape escaped sequences.
			$node_data = \str_replace( RE::ESCAPE_MARKER, '', $node_data );
		}

		// Restore textnode content.
		$textnode->data = $node_data;
	}
}
