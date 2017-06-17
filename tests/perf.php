<?php
/**
 * Performance test suite for wp-Typography. Licensed under the terms of the GNU General Public License 2.0.
 *
 * (c) 2015, 2017 Peter Putzer
 *
 * @package WP_Typography\Tests
 */

error_reporting( E_ALL & E_STRICT );

require_once realpath( __DIR__ . '/../php-typography/php-typography-autoload.php' );
require_once realpath( __DIR__ . '/../vendor/hyphenator-php/src/NikoNyrh/Hyphenator/Hyphenator.php' );

// Don't break without translation function.
if ( ! function_exists( '__' ) ) {
	function &__( $string, $domain = null ) { return $string; } // @codingStandardsIgnoreLine
}

$test_html = <<<'EOD'
To deal with one page that has fixed width for text, the practical move would be to add a couple of SOFT HYPHEN characters (U+00AD), using the entity reference &shy; if you find it more comfortable than entering the (invisible) character itself. You can rather quickly find out which words need to be hyphenated to produce a good result.

In a more complex case (several pages, flexible width), this includes foobar use a preprocessor, or server-side code, or client-side code that adds soft hyphens. The client-side approach is simplest and can be applied independently of server-side technologies and authoring tools. Beware that automatic hyphenation may go wrong and needs some help: the language(s) of the text need to be indicated in markup (or otherwise, depending on the library used).

At the minimum, you could just put the attributes lang=en class=hyphenate into the <body> tag and the following code in the head part
To deal with one page that has fixed width for text, the practical move would be to add a couple of SOFT HYPHEN characters (U+00AD), using the entity reference &shy; if you find it more comfortable than entering the (invisible) character itself. You can rather quickly find out which words need to be hyphenated to produce a good result.

In a more complex case (several pages, flexible width), this includes foobar use a preprocessor, or server-side code, or client-side code that adds soft hyphens. The client-side approach is simplest and can be applied independently of server-side technologies and authoring tools. Beware that automatic hyphenation may go wrong and needs some help: the language(s) of the text need to be indicated in markup (or otherwise, depending on the library used).

At the minimum, you could just put the attributes lang=en class=hyphenate into the <body> tag and the following code in the head part
To deal with one page that has fixed width for text, the practical move would be to add a couple of SOFT HYPHEN characters (U+00AD), using the entity reference &shy; if you find it more comfortable than entering the (invisible) character itself. You can rather quickly find out which words need to be hyphenated to produce a good result.

In a more complex case (several pages, flexible width), this includes foobar use a preprocessor, or server-side code, or client-side code that adds soft hyphens. The client-side approach is simplest and can be applied independently of server-side technologies and authoring tools. Beware that automatic hyphenation may go wrong and needs some help: the language(s) of the text need to be indicated in markup (or otherwise, depending on the library used).

At the minimum, you could just put the attributes lang=en class=hyphenate into the <body> tag and the following code in the head part
To deal with one page that has fixed width for text, the practical move would be to add a couple of SOFT HYPHEN characters (U+00AD), using the entity reference &shy; if you find it more comfortable than entering the (invisible) character itself. You can rather quickly find out which words need to be hyphenated to produce a good result.

In a more complex case (several pages, flexible width), this includes foobar use a preprocessor, or server-side code, or client-side code that adds soft hyphens. The client-side approach is simplest and can be applied independently of server-side technologies and authoring tools. Beware that automatic hyphenation may go wrong and needs some help: the language(s) of the text need to be indicated in markup (or otherwise, depending on the library used).

At the minimum, you could just put the attributes lang=en class=hyphenate into the <body> tag and the following code in the head part...
	aflying

EOD;

$php_typo = new \PHP_Typography\PHP_Typography( false );
$php_typo->set_hyphenation();
$php_typo->set_hyphenate_headings();
$php_typo->set_hyphenate_all_caps();
$php_typo->set_hyphenate_title_case();
$php_typo->set_hyphenate_compounds();
$php_typo->set_hyphenation_language();
$php_typo->set_min_length_hyphenation();
$php_typo->set_min_before_hyphenation();
$php_typo->set_min_after_hyphenation();

$iterations = 100;

// Variant A.
$start_time = microtime( true );
for ( $i = 0; $i < $iterations; ++$i ) {
	$php_typo->set_hyphenation_exceptions( [ 'foo-bar', 'pre-pro-ces-sor', 'fo-ll-o-w-i-ng' ] );
	$php_typo->process( $test_html, false );
}
$end_time = microtime( true );
echo "$i iterations took " . ( $end_time - $start_time ) . " seconds.\n"; // @codingStandardsIgnoreLine

// Variant B.
$start_time = microtime( true );
$hyphenator = new \NikoNyrh\Hyphenator\Hyphenator();
$html = new \Masterminds\HTML5();
for ( $i = 0; $i < $iterations; ++$i ) {
	$html->loadHTML( $test_html );
	$html->saveHTML();
	$hyphenator->hyphenate( $test_html );
}
$end_time = microtime( true );
echo "$i iteratations w/ hyphenator-php took " . ( $end_time - $start_time ) . " seconds.\n"; // @codingStandardsIgnoreLine
