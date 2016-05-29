<?php

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

/**
 * Some additional subclasses for testing.
 */
require_once __DIR__ . '/class-php-typography-css.php';
