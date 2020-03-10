<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2017-2019 Peter Putzer.
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

use PHP_Typography\RE;
use PHP_Typography\Settings;
use PHP_Typography\U;

/**
 * Applies smart marks (if enabled).
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 */
class Smart_Marks_Fix extends Abstract_Node_Fix {

	const ESCAPE_501C = '/\b(501\()(c)(\)\((?:[1-9]|[1-2][0-9])\))/S';

	const MARKS = [
		'(c)'  => U::COPYRIGHT,
		'(C)'  => U::COPYRIGHT,
		'(r)'  => U::REGISTERED_MARK,
		'(R)'  => U::REGISTERED_MARK,
		'(p)'  => U::SOUND_COPY_MARK,
		'(P)'  => U::SOUND_COPY_MARK,
		'(sm)' => U::SERVICE_MARK,
		'(SM)' => U::SERVICE_MARK,
		'(tm)' => U::TRADE_MARK,
		'(TM)' => U::TRADE_MARK,
	];

	/**
	 * An array of marks to match.
	 *
	 * @since 6.0.0
	 *
	 * @var array
	 */
	private $marks;

	/**
	 * An array of replacement marks.
	 *
	 * @since 6.0.0
	 *
	 * @var array
	 */
	private $replacements;

	/**
	 * Creates a new fix instance.
	 *
	 * @param bool $feed_compatible Optional. Default false.
	 */
	public function __construct( $feed_compatible = false ) {
		parent::__construct( $feed_compatible );

		$this->marks        = \array_keys( self::MARKS );
		$this->replacements = \array_values( self::MARKS );
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
		if ( empty( $settings[ Settings::SMART_MARKS ] ) ) {
			return;
		}

		// Cache textnode content.
		$node_data = $textnode->data;

		// Escape usage of "501(c)(1...29)" (US non-profit).
		$node_data = \preg_replace( self::ESCAPE_501C, '$1' . RE::ESCAPE_MARKER . '$2' . RE::ESCAPE_MARKER . '$3', $node_data );

		// Replace marks.
		$node_data = \str_replace( $this->marks, $this->replacements, $node_data );

		// Un-escape escaped sequences & resetore textnode content.
		$textnode->data = \str_replace( RE::ESCAPE_MARKER, '', $node_data );
	}
}
