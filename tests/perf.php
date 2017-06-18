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

/* $php_typo = new \PHP_Typography\PHP_Typography( false );
$php_typo->set_hyphenation();
$php_typo->set_hyphenate_headings();
$php_typo->set_hyphenate_all_caps();
$php_typo->set_hyphenate_title_case();
$php_typo->set_hyphenate_compounds();
$php_typo->set_hyphenation_language();
$php_typo->set_min_length_hyphenation();
$php_typo->set_min_before_hyphenation();
$php_typo->set_min_after_hyphenation(); */

$iterations = 1000;

/**
 * [mb_str_split description]
 * @param  [type] $str [description].
 * @param  [type] $len [description].
 * @return [type]      [description]
 */
function mb_str_split_preg( $str, $len ) {
	/*if ( $len <= 0 ) {
		return false;
	}*/

//$chars = preg_split( '/(?<!^)(?!$)/u', $str /*, -1, PREG_SPLIT_NO_EMPTY */ );
	$chars = preg_split( '//u', $str , -1, PREG_SPLIT_NO_EMPTY  );

	if ( len > 1 ) {
		//$out = [];
		foreach ( array_chunk( $chars, $len ) as $a ) {
			$chars[] = join( '', $a );
		}

		//$chars = $out;
	}

	return $chars;
}

//$test_html = "Something or other Änderungsschneiderei mit scharfem ß & <allem>!";


// Variant A.
$start_time = microtime( true );
for ( $i = 0; $i < $iterations; ++$i ) {
	\PHP_Typography\Strings::mb_str_split( $test_html, 1 );
}
$end_time = microtime( true );
echo "$i iterations took " . ( $end_time - $start_time ) . " seconds (Variant A).\n"; // @codingStandardsIgnoreLine

// Variant B.
$start_time = microtime( true );
for ( $i = 0; $i < $iterations; ++$i ) {
	mb_str_split_preg( $test_html, 1 );
}
$end_time = microtime( true );
echo "$i iteratations w/ hyphenator-php took " . ( $end_time - $start_time ) . " seconds (Variant B).\n"; // @codingStandardsIgnoreLine

$foo = mb_str_split_preg( $test_html, 1 );
$bar = \PHP_Typography\Strings::mb_str_split( $test_html, 1 );

if ( $foo ===  $bar ) {
	echo "Results are equal.\n";
} else {
	//print_r( $foo );
	//print_r( $bar );
	var_dump( $foo[3727]);
	var_dump( $bar[3727]);
}
