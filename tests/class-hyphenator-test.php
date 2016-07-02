<?php

/**
 * Test Hyphenator class.
 *
 * @coversDefaultClass \PHP_Typography\Hyphenator
 * @usesDefaultClass \PHP_Typography\Hyphenator
 */
class Hyphenator_Test extends PHPUnit_Framework_TestCase
{
    /**
     * @var Hyphenator
     */
    protected $h;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->h = new \PHP_Typography\Hyphenator();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * Return encoded HTML string (everything except <>"').
     *
     * @param string $html
     */
    protected function clean_html( $html ) {
    	$convmap = array(0x80, 0x10ffff, 0, 0xffffff);

    	return str_replace( array('&lt;', '&gt;'), array('<', '>'), mb_encode_numericentity( htmlentities( $html, ENT_NOQUOTES, 'UTF-8', false ), $convmap ) );
    }

    /**
     * Helper function to generate a valid token list from strings.
     *
     * @param string $value
     * @param string $type Optional. Default 'word'.
     *
     * @return array
     */
    protected function tokenize( $value, $type = 'word' ) {
    	return array(
    		array(
	    		'type'  => $type,
	    		'value' => $value
    		)
    	);
    }

    /**
     *
     * @param string $expected_value
     * @param array $actual_tokens
     * @param string $message
     */
    protected function assertTokenSame( $expected_value, $actual_tokens, $message = '' ) {
    	foreach ( $actual_tokens as &$actual ) {
    		$actual['value'] = $this->clean_html( $actual['value'] );
    	}

    	return $this->assertSame( $this->tokenize( $expected_value ) , $actual_tokens, $message );
    }

    /**
     * @covers ::set_language
     */
    public function test_set_language()
    {
    	$h = $this->h;
    	//$h->hyphenation_exceptions = array(); // necessary for full coverage

		$h->set_language( 'en-US' );
		$this->assertAttributeNotEmpty( 'pattern', $h, 'Empty pattern array' );
		$this->assertAttributeGreaterThan( 0, 'pattern_max_segment', $h, 'Max segment size 0' );
 		$this->assertAttributeNotEmpty( 'pattern_exceptions', $h, 'Empty pattern exceptions array' );

 		$h->set_language( 'foobar' );
 		$this->assertFalse( isset( $h->pattern ) );
 		$this->assertFalse( isset( $h->pattern_max_segment ) );
 		$this->assertFalse( isset( $h->pattern_exceptions ) );

 		$h->set_language( 'no' );
 		$this->assertAttributeCount( 3, 'pattern', $h, 'Invalid Norwegian pattern.');
 		$this->assertAttributeGreaterThan( 0, 'pattern_max_segment', $h, 'Max segment size 0' );
 		$this->assertAttributeNotEmpty( 'pattern_exceptions', $h, 'Empty pattern exceptions array' ); // Norwegian has exceptions

 		$h->set_language( 'de' );
 		$this->assertAttributeCount( 3, 'pattern', $h, 'Invalid German pattern.');
 		$this->assertAttributeGreaterThan( 0, 'pattern_max_segment', $h, 'Max segment size 0' );
 		$this->assertAttributeEmpty( 'pattern_exceptions', $h, 'Unexpected pattern exceptions found' ); // no exceptions in the German pattern file
    }

    /**
     * @covers ::set_language
     */
    public function test_set_same_hyphenation_language()
    {
    	$h = $this->h;

    	$h->set_language( 'en-US' );
    	$this->assertAttributeNotEmpty( 'pattern', $h, 'Empty pattern array' );
    	$this->assertAttributeGreaterThan( 0, 'pattern_max_segment', $h, 'Max segment size 0' );
    	$this->assertAttributeNotEmpty( 'pattern_exceptions', $h, 'Empty pattern exceptions array' );

    	$h->set_language( 'en-US' );
    	$this->assertAttributeNotEmpty( 'pattern', $h, 'Empty pattern array' );
    	$this->assertAttributeGreaterThan( 0, 'pattern_max_segment', $h, 'Max segment size 0' );
    	$this->assertAttributeNotEmpty( 'pattern_exceptions', $h, 'Empty pattern exceptions array' );
    }

    /**
     * @covers ::set_min_length
     */
    public function test_set_min_length()
    {
		$this->h->set_min_length( 1 );
		$this->assertAttributeSame( 1, 'min_length', $this->h );

		$this->h->set_min_length( 2 );
		$this->assertAttributeSame( 2, 'min_length', $this->h );

		$this->h->set_min_length( 66 );
		$this->assertAttributeSame( 66, 'min_length', $this->h );
    }

    /**
     * @covers ::set_min_before
     */
    public function test_set_min_before()
    {
		$this->h->set_min_before( 0 );
		$this->assertAttributeSame( 0, 'min_before', $this->h );

		$this->h->set_min_before( 1 );
		$this->assertAttributeSame( 1, 'min_before', $this->h );

		$this->h->set_min_before( 66 );
		$this->assertAttributeSame( 66, 'min_before', $this->h );
    }

    /**
     * @covers ::set_min_after
     */
    public function test_set_min_after()
    {
		$this->h->set_min_after( 0 );
		$this->assertAttributeSame( 0, 'min_after', $this->h );

		$this->h->set_min_after( 1 );
		$this->assertAttributeSame( 1, 'min_after', $this->h );

		$this->h->set_min_after( 66 );
		$this->assertAttributeSame( 66, 'min_after', $this->h );
    }


    /**
     * @covers \PHP_Typography\Hyphenator::set_custom_exceptions
     */
    public function test_set_custom_exceptions_array()
    {
// 		$h = $this->h;
// 		$h->settings['hyphenationExceptions'] = array(); // necessary for full coverage
// 		$exceptions = array( "Hu-go", "Fö-ba-ß" );

// 		$h->set_custom_exceptions( $exceptions );
// 		$this->assertContainsOnly( 'string', $h->settings['hyphenationCustomExceptions'] );
// 		$this->assertArraySubset( array( 'hugo' => 'hu-go' ), $h->settings['hyphenationCustomExceptions'] );
// 		$this->assertArraySubset( array( 'föbaß' => 'fö-ba-ß' ), $h->settings['hyphenationCustomExceptions'] );
// 		$this->assertCount( 2, $h->settings['hyphenationCustomExceptions'] );
    }

    /**
     * @covers \PHP_Typography\Hyphenator::set_custom_exceptions
     */
    public function test_set_custom_exceptions_unknown_encoding()
    {
//     	$h = $this->h;
//     	$h->settings['hyphenationExceptions'] = array(); // necessary for full coverage
//     	$exceptions = array( "Hu-go", mb_convert_encoding( "Fö-ba-ß" , 'ISO-8859-2' ) );

//     	$h->set_custom_exceptions( $exceptions );
//     	$this->assertContainsOnly( 'string', $h->settings['hyphenationCustomExceptions'] );
//     	$this->assertArraySubset( array( 'hugo' => 'hu-go' ), $h->settings['hyphenationCustomExceptions'] );
//     	$this->assertArrayNotHasKey( 'föbaß', $h->settings['hyphenationCustomExceptions'] );
//     	$this->assertCount( 1, $h->settings['hyphenationCustomExceptions'] );
    }

    /**
     * @covers \PHP_Typography\Hyphenator::set_custom_exceptions
     */
    public function test_set_custom_exceptions_string()
    {
//     	$h = $this->h;
//     	$exceptions = "Hu-go, Fö-ba-ß";

//     	$h->set_custom_exceptions( $exceptions );
//     	$this->assertContainsOnly( 'string', $h->settings['hyphenationCustomExceptions'] );
//     	$this->assertArraySubset( array( 'hugo' => 'hu-go' ), $h->settings['hyphenationCustomExceptions'] );
//     	$this->assertArraySubset( array( 'föbaß' => 'fö-ba-ß' ), $h->settings['hyphenationCustomExceptions'] );
//     	$this->assertCount( 2, $h->settings['hyphenationCustomExceptions'] );
    }

    public function provide_hyphenate_data() {
    	return array(
    		array( 'A few words to hyphenate, like KINGdesk. Really, there should be more hyphenation here!', 'A few words to hy&shy;phen&shy;ate, like KING&shy;desk. Re&shy;ally, there should be more hy&shy;phen&shy;ation here!', 'en-US', true, true, true, false ),
    		array( 'Sauerstofffeldflasche', 'Sau&shy;er&shy;stoff&shy;feld&shy;fla&shy;sche', 'de', true, true, true, false ),
    		array( 'Sauerstoff-Feldflasche', 'Sau&shy;er&shy;stoff-Feld&shy;fla&shy;sche', 'de', true, true, true, true ),
    		array( 'Sauerstoff-Feldflasche', 'Sauerstoff-Feldflasche', 'de', true, true, true, false ),
    	);
    }

    /**
     * @covers ::hyphenate
     * @covers ::hyphenate
     * @covers ::hyphenate_compounds
     * @covers ::hyphenation_pattern_injection
     *
     * @dataProvider provide_hyphenate_data
     */
    public function test_hyphenate( $html, $result, $lang, $hyphenate_headings, $hyphenate_all_caps, $hyphenate_title_case, $hyphenate_compunds )
    {
    	$h = $this->h;
    	$h->set_language( $lang );
    	$h->set_min_length(2);
    	$h->set_min_before(2);
    	$h->set_min_after(2);
    	$h->set_custom_exceptions( array( 'KING-desk' ) );

    	/*	$this->assertSame( "This is a paragraph with no embedded hyphenation hints and no hyphen-related CSS applied. Corporate gibberish follows. Think visionary. If you generate proactively, you may have to e-enable interactively. We apply the proverb \"Grass doesn't grow on a racetrack\" not only to our re-purposing but our power to matrix. If all of this comes off as dumbfounding to you, that's because it is! Our feature set is unparalleled in the industry, but our reality-based systems and simple use is usually considered a remarkable achievement. The power to brand robustly leads to the aptitude to embrace seamlessly. What do we streamline? Anything and everything, regardless of reconditeness",
    	 $this->clean_html( $this->object->hyphenate("This is a paragraph with no embedded hyphenation hints and no hyphen-related CSS applied. Corporate gibberish follows. Think visionary. If you generate proactively, you may have to e-enable interactively. We apply the proverb \"Grass doesn't grow on a racetrack\" not only to our re-purposing but our power to matrix. If all of this comes off as dumbfounding to you, that's because it is! Our feature set is unparalleled in the industry, but our reality-based systems and simple use is usually considered a remarkable achievement. The power to brand robustly leads to the aptitude to embrace seamlessly. What do we streamline? Anything and everything, regardless of reconditeness") ) );
    	*/

    	$this->assertSame( $result, clean_html( $h->hyphenate( $html ) ) );
    }

    /**
     * @covers ::hyphenate
     */
    public function test_hyphenate_headings_disabled()
    {
    	$this->h->set_language( 'en-US' );
    	$this->h->set_min_length(2);
    	$this->h->set_min_before(2);
    	$this->h->set_min_after(2);
    	$this->h->set_custom_exceptions( array( 'KING-desk' ) );

    	$html = '<h2>A few words to hyphenate, like KINGdesk. Really, there should be no hyphenation here!</h2>';
    	$this->assertSame( $html, $this->clean_html( $this->h->hyphenate( $html ) ) );
    }

    /**
     * @covers ::hyphenate
     * @covers ::hyphenation_pattern_injection
     */
    public function test_hyphenate_2()
    {
    	$this->h->set_language( 'de' );
    	$this->h->set_min_length(2);
    	$this->h->set_min_before(2);
    	$this->h->set_min_after(2);

    	$tokens = $this->tokenize( mb_convert_encoding( 'Änderungsmeldung', 'ISO-8859-2' ) );
    	$hyphenated  = $this->h->hyphenate( $tokens );
	   	$this->assertEquals( $hyphenated, $tokens );

	   	$tokens = $this->tokenize( 'Änderungsmeldung' );
	   	$hyphenated  = $this->h->hyphenate( $tokens );
	   	$this->assertNotEquals( $hyphenated, $tokens );
    }

    /**
     * @covers ::hyphenate
     */
    public function test_hyphenate_no_title_case()
    {
    	$this->h->set_language( 'de' );
    	$this->h->set_min_length(2);
    	$this->h->set_min_before(2);
    	$this->h->set_min_after(2);

    	$tokens = $this->tokenize( 'Änderungsmeldung' );
    	$hyphenated  = $this->h->hyphenate( $tokens );
    	$this->assertEquals( $tokens, $hyphenated);
    }

    /**
     * @covers ::hyphenate
     */
    public function test_hyphenate_invalid()
    {
    	$this->h->set_language( 'de' );
    	$this->h->set_min_length(2);
    	$this->h->set_min_before(2);
    	$this->h->set_min_after(2);

    	$this->h->settings['hyphenMinBefore'] = 0; // invalid value

    	$tokens = $this->tokenize( 'Änderungsmeldung' );
    	$hyphenated  = $this->h->hyphenate( $tokens );
    	$this->assertEquals( $tokens, $hyphenated);
    }

    /**
     * @covers ::hyphenate
     */
    public function test_hyphenate_no_custom_exceptions()
    {
    	$this->h->set_language( 'en-US' );
    	$this->h->set_min_length(2);
    	$this->h->set_min_before(2);
    	$this->h->set_min_after(2);

    	$this->assertSame( 'A few words to hy&shy;phen&shy;ate, like KINGdesk. Re&shy;ally, there should be more hy&shy;phen&shy;ation here!',
    					   $this->clean_html( $this->h->hyphenate( 'A few words to hyphenate, like KINGdesk. Really, there should be more hyphenation here!' ) ) );
    }

    /**
     * @covers ::hyphenate
     */
    public function test_hyphenate_no_exceptions_at_all()
    {
    	$this->h->set_language( 'en-US' );
    	$this->h->set_min_length(2);
    	$this->h->set_min_before(2);
    	$this->h->set_min_after(2);
		$this->h->settings['hyphenationPatternExceptions'] = array();
		unset( $this->h->settings['hyphenationExceptions'] );

    	$this->assertSame( 'A few words to hy&shy;phen&shy;ate, like KINGdesk. Re&shy;ally, there should be more hy&shy;phen&shy;ation here!',
    					   $this->clean_html( $this->h->hyphenate( 'A few words to hyphenate, like KINGdesk. Really, there should be more hyphenation here!' ) ) );
    }
}
