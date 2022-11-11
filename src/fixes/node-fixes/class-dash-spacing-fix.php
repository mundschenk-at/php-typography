<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2014-2022 Peter Putzer.
 *  Copyright 2009-2011 KINGdesk, LLC.
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
use PHP_Typography\Settings;
use PHP_Typography\U;

/**
 * Applies spacing around dashes (if enabled).
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 */
class Dash_Spacing_Fix extends Abstract_Node_Fix {

	// Mandatory UTF-8 modifier.
	const EM_DASH_SPACING = '/
		(?:
			\s
			(' . U::EM_DASH . ')
			\s
		)
		|
		(?:
			(?<=\S)                   # lookbehind assertion
			(' . U::EM_DASH . ')
			(?=\S)                    # lookahead assertion
		)
	/xu';

	/**
	 * Regular expression matching cached parenthetical dash.
	 *
	 * @var string
	 */
	protected $parenthetical_dash_spacing;

	/**
	 * Regular expression matching cached interval dash.
	 *
	 * @var string
	 */
	protected $interval_dash_spacing;

	/**
	 * Replacement pattern for em-dash spacing.
	 *
	 * @var string
	 */
	protected $em_dash_replacement;

	/**
	 * Replacement pattern for parenthetical dash spacing.
	 *
	 * @var string
	 */
	protected $parenthetical_dash_replacement;

	/**
	 * Replacement pattern for interval dash spacing.
	 *
	 * @var string
	 */
	protected $interval_dash_replacement;

	/**
	 * Cached dashes style.
	 *
	 * @var \PHP_Typography\Settings\Dashes|null
	 */
	protected $cached_dash_style;

	/**
	 * Apply the fix to a given textnode.
	 *
	 * @param \DOMText $textnode Required.
	 * @param Settings $settings Required.
	 * @param bool     $is_title Optional. Default false.
	 */
	public function apply( \DOMText $textnode, Settings $settings, $is_title = false ) {
		if ( empty( $settings[ Settings::DASH_SPACING ] ) ) {
			return;
		}

		// Various special characters and regular expressions.
		$s = $settings->dash_style();

		if ( $s != $this->cached_dash_style ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison -- object value comparison.
			$this->update_dash_spacing_regex( $s->parenthetical_dash(), $s->parenthetical_space(), $s->interval_dash(), $s->interval_space() );
			$this->cached_dash_style = $s;
		}

		// Cache $textnode->data for this fix.
		$node_data = $textnode->data;

		$node_data = \preg_replace( self::EM_DASH_SPACING,             $this->em_dash_replacement,            $node_data );
		$node_data = \preg_replace( $this->parenthetical_dash_spacing, $this->parenthetical_dash_replacement, $node_data );
		$node_data = \preg_replace( $this->interval_dash_spacing,      $this->interval_dash_replacement,      $node_data );

		// Restore textnode content.
		$textnode->data = $node_data;
	}

	/**
	 * Update the dash spacing regular expression.
	 *
	 * @param string $parenthetical       The dash character used for parenthetical dashes.
	 * @param string $parenthetical_space The space character used around parenthetical dashes.
	 * @param string $interval            The dash character used for interval dashes.
	 * @param string $interval_space      The space character used around interval dashes.
	 */
	private function update_dash_spacing_regex( $parenthetical, $parenthetical_space, $interval, $interval_space ) : void {
		// Mandatory UTF-8 modifier.
		$this->parenthetical_dash_spacing = "/
			(?:
				\s
				({$parenthetical})
				\s
			)
		/xu";

		// Mandatory UTF-8 modifier.
		$this->interval_dash_spacing = "/
			(?:
				(?<=\S)             # lookbehind assertion
				({$interval})
				(?=\S)              # lookahead assertion
			)
		/xu";

		$this->em_dash_replacement            = "{$interval_space}\$1\$2{$interval_space}"; // is this correct?
		$this->parenthetical_dash_replacement = "{$parenthetical_space}\$1\$2{$parenthetical_space}";
		$this->interval_dash_replacement      = "{$interval_space}\$1\$2{$interval_space}";
	}
}
