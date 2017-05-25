<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2015-2017 Peter Putzer.
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; either version 2
 *  of the License, or ( at your option ) any later version.
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
 *  @package wpTypography/Tests
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * Include debug helpers.
 */
require_once dirname( __DIR__ ) . '/php-typography/php-typography-debug.php';

/**
 * Autoloading.
 */
require_once dirname( __DIR__ ) . '/php-typography/php-typography-autoload.php';

/**
 * Load HTML parser for function testing.
 */
require_once dirname( __DIR__ ) . '/vendor/Masterminds/HTML5.php';
require_once dirname( __DIR__ ) . '/vendor/Masterminds/HTML5/autoload.php';
