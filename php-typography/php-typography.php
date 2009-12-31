<?php
/*
	Project: PHP Typography
	Project URI: http://kingdesk.com/projects/php-tyography/
	Version: 1.21


	Copyright 2009, KINGdesk, LLC. Licensed under the GNU General Public License 2.0. If you use, modify and/or redistribute this software, you must leave the KINGdesk, LLC copyright information, the request for a link to http://kingdesk.com, and the web design services contact information unchanged. If you redistribute this software, or any derivative, it must be released under the GNU General Public License 2.0. This program is distributed without warranty (implied or otherwise) of suitability for any particular purpose. See the GNU General Public License for full license terms <http://creativecommons.org/licenses/GPL/2.0/>.

	WE DON'T WANT YOUR MONEY: NO TIPS NECESSARY!  If you enjoy this plugin, a link to http://kingdesk.com from your website would be appreciated.
	
	For web design services, please contact jeff@kingdesk.com.
*/

# if used with multibyte language, UTF-8 encoding is required!
class phpTypography {

	var $mb = FALSE; //cannot be changed after load
	var $chr = array();
	var $settings = array();  // operational attributes
	var $parsedHTML = array(); // to hold current instance of class parseHTML
	var $parsedText = array(); // to hold current instance of class parseText
	

	#=======================================================================
	#=======================================================================
	#==	METHODS - SET ATTRIBUTES
	#=======================================================================
	#=======================================================================

	// __ naming defines constructor that is automatically called on each newly-createy object
	function __construct($setDefaults = TRUE) {
		$this->chr["noBreakSpace"] = $this->uchr(160);
		$this->chr["noBreakNarrowSpace"] = $this->uchr(160); //should be 8239, but not supported consistently, used in unit spacing
		$this->chr["copyright"] = $this->uchr(169);
		$this->chr["guillemetOpen"] = $this->uchr(171);
		$this->chr["softHyphen"] = $this->uchr(173);
		$this->chr["registeredMark"] = $this->uchr(174);
		$this->chr["guillemetClose"] = $this->uchr(187);
		$this->chr["multiplication"] = $this->uchr(215);
		$this->chr["division"] = $this->uchr(247);
		$this->chr["figureSpace"] = $this->uchr(8199);
		$this->chr["thinSpace"] = $this->uchr(8201);
		$this->chr["zeroWidthSpace"] = $this->uchr(8203);
		$this->chr["hyphen"] = "-";  // should be $this->uchr(8208), but IE6 chokes;
		$this->chr["noBreakHyphen"] = $this->uchr(8209);
		$this->chr["enDash"] = $this->uchr(8211);
		$this->chr["emDash"] = $this->uchr(8212);
		$this->chr["singleQuoteOpen"] = $this->uchr(8216); // reset in set_smart_quotes_language()
		$this->chr["singleQuoteClose"] = $this->uchr(8217); // reset in set_smart_quotes_language()
		$this->chr["apostrophe"] = $this->uchr(8217); // defined seperate from singleQuoteClose so quotes can be redefined in set_smart_quotes_language() without disrupting apostrophies
		$this->chr["singleLow9Quote"] = $this->uchr(8218);
		$this->chr["doubleQuoteOpen"] = $this->uchr(8220); // reset in set_smart_quotes_language()
		$this->chr["doubleQuoteClose"] = $this->uchr(8221); // reset in set_smart_quotes_language()
		$this->chr["doubleLow9Quote"] = $this->uchr(8222);
		$this->chr["ellipses"] = $this->uchr(8230);
		$this->chr["singlePrime"] = $this->uchr(8242);
		$this->chr["doublePrime"] = $this->uchr(8243);
		$this->chr["singleAngleQuoteOpen"] = $this->uchr(8249);
		$this->chr["singleAngleQuoteClose"] = $this->uchr(8250);
		$this->chr["fractionSlash"] = $this->uchr(8260);
		$this->chr["soundCopyMark"] = $this->uchr(8471);
		$this->chr["serviceMark"] = $this->uchr(8480);
		$this->chr["tradeMark"] = $this->uchr(8482);
		$this->chr["minus"] = $this->uchr(8722);
		$this->chr["leftCornerBracket"] = $this->uchr(12300);
		$this->chr["rightCornerBracket"] = $this->uchr(12301);
		$this->chr["leftWhiteCornerBracket"] = $this->uchr(12302);
		$this->chr["rightWhiteCornerBracket"] = $this->uchr(12303);

		if($setDefaults) {
			$this->set_defaults();
		}
		
		return TRUE;
	}

	function set_defaults() {

		// general attributes
		$this->set_tags_to_ignore();
		$this->set_classes_to_ignore();
		$this->set_ids_to_ignore();
		
		//smart characters
		$this->set_smart_quotes();
		//DEPRECIATED $this->set_smart_quotes_language();
		$this->set_smart_quotes_primary(); /* added in version 1.15 */
		$this->set_smart_quotes_secondary(); /* added in version 1.15 */
		$this->set_smart_dashes();
		$this->set_smart_ellipses();
		$this->set_smart_diacritics();
		$this->set_diacritic_language();
		$this->set_diacritic_custom_replacements();
		$this->set_smart_marks();
		$this->set_smart_ordinal_suffix();
		$this->set_smart_math();
		$this->set_smart_fractions();
		$this->set_smart_exponents();
		// DEPRECIATED: $this->set_smart_multiplication();
		
		//smart spacing
		$this->set_single_character_word_spacing();
		$this->set_fraction_spacing();
		$this->set_unit_spacing();
		$this->set_units();
		$this->set_dash_spacing();
		$this->set_dewidow();
		$this->set_max_dewidow_length();
		$this->set_max_dewidow_pull();
		$this->set_wrap_hard_hyphens();
		$this->set_url_wrap();
		$this->set_email_wrap();
		$this->set_min_after_url_wrap();
		$this->set_space_collapse();
		
		//character styling
		$this->set_style_ampersands();
		$this->set_style_caps();
		$this->set_style_initial_quotes();
		$this->set_style_numbers();
		$this->set_initial_quote_tags();
		
		//hyphenation
		$this->set_hyphenation();
		$this->set_hyphenation_language();
		$this->set_min_length_hyphenation();
		$this->set_min_before_hyphenation();
		$this->set_min_after_hyphenation();
		$this->set_hyphenate_headings();
		$this->set_hyphenate_all_caps();
		$this->set_hyphenate_title_case(); // added in version 1.5
		$this->set_hyphenation_exceptions();
		
		return TRUE;
	}

	// sets tags where typography of children will be untouched
	function set_tags_to_ignore($tags = array("code", "head", "kbd", "object", "option", "pre", "samp", "script", "select", "style", "textarea", "title", "var", "math")) {
		if(!is_array($tags)) 
			$tags = preg_split("/[\s,]+/", $tags, -1, PREG_SPLIT_NO_EMPTY);
		foreach($tags as &$tag){
			$tag = strtolower($tag);
		}
		
		// self closing tags shouldn't be in $tags
		$selfClosingTags = array('area', 'base', 'basefont', 'br', 'frame', 'hr', 'img', 'input', 'link', 'meta');
		$tagsCount = count($tags);
		// don't use foreach, we need to modify the array we are indexing through
		$key = 0; //we need to look through every initial key ($i), but the total key count will reduce over time ($key)
		for($i=0; $i<$tagsCount; $i++) {
			if(FALSE !== array_search($tags[$key], $selfClosingTags)) {
				$tags =array_merge(array_slice($tags, 0, $key), array_slice($tags, $key+1)); // array_merge renumbers numeric keys!
				$key--; //adjust for shorter array
			}
			$key++;
		}

		// include all inappropriate tags in $tags
		$inappropriateTags = array('iframe', 'textarea', 'button', 'select', 'optgroup', 'option' ,'map', 'style', 'head', 'title', 'script', 'applet', 'object', 'param');
		foreach($inappropriateTags as $inappropriateTag) {
			if(FALSE === array_search($inappropriateTag, $tags)) {
				array_push($tags, $inappropriateTag);
			}
		}
		
		$this->settings["ignoreTags"] = $tags;
		return TRUE;
	}

	// sets classes where typography of children will be untouched
	function set_classes_to_ignore($classes = array("vcard", "noTypo")) {
		if(!is_array($classes)) 
			$classes = preg_split("/[\s,]+/", $classes, -1, PREG_SPLIT_NO_EMPTY);
		$this->settings["ignoreClasses"] = $classes;
		return TRUE;
	}

	// sets IDs where typography of children will be untouched
	function set_ids_to_ignore($ids = array()) {
		if(!is_array($ids)) 
			$ids = preg_split("/[\s,]+/", $ids, -1, PREG_SPLIT_NO_EMPTY);
		$this->settings["ignoreIDs"] = $ids;
		return TRUE;
	}

	// curl quotemarks
	function set_smart_quotes($on = TRUE) {
		$this->settings["smartQuotes"] = $on;
		return TRUE;
	}

	// DEPRECIATED
	// language preferences for curling quotemarks
	// allowed values for $lang
	//		"en" = English style quotes, replaces "foo" with “foo”
	//		"de" = German style quotes, replaces "foo" with „foo”
	//		"fr" = French guillemets, replaces "foo" with «foo»
	//		"fr-reverse" = Reverse French guillemets, replaces "foo" with »foo«
	function set_smart_quotes_language($lang = "en") {
		if($lang == "de") {
			$this->chr["doubleQuoteOpen"] = $this->chr["doubleLow9Quote"];
			$this->chr["doubleQuoteClose"] = $this->uchr(8220);
			$this->chr["singleQuoteOpen"] = $this->chr["singleLow9Quote"];
			$this->chr["singleQuoteClose"] = $this->uchr(8216);
		} elseif($lang == "fr") {
			$this->chr["doubleQuoteOpen"] = $this->chr["guillemetOpen"];
			$this->chr["doubleQuoteClose"] = $this->chr["guillemetClose"];
			$this->chr["singleQuoteOpen"] = $this->chr["singleAngleQuoteOpen"];
			$this->chr["singleQuoteClose"] = $this->chr["singleAngleQuoteClose"];
		} elseif($lang == "fr-reverse") {
			$this->chr["doubleQuoteOpen"] = $this->chr["guillemetClose"];
			$this->chr["doubleQuoteClose"] = $this->chr["guillemetOpen"];
			$this->chr["singleQuoteOpen"] = $this->chr["singleAngleQuoteClose"];
			$this->chr["singleQuoteClose"] = $this->chr["singleAngleQuoteOpen"];
		} else {
			$this->chr["doubleQuoteOpen"] = $this->uchr(8220);
			$this->chr["doubleQuoteClose"] = $this->uchr(8221);
			$this->chr["singleQuoteOpen"] = $this->uchr(8216);
			$this->chr["singleQuoteClose"] = $this->uchr(8217);
		}

		return TRUE;
	}


	// Primary quotemarks style
	// allowed values for $style
	//	"doubleCurled" => "&ldquo;foo&rdquo;",
	//	"doubleCurledReversed" => "&rdquo;foo&rdquo;",
	//	"doubleLow9" => "&bdquo;foo&rdquo;",
	//	"doubleLow9Reversed" => "&bdquo;foo&ldquo;",
	//	"singleCurled" => "&lsquo;foo&rsquo;",
	//	"singleCurledReversed" => "&rsquo;foo&rsquo;",
	//	"singleLow9" => "&sbquo;foo&rsquo;",
	//	"singleLow9Reversed" => "&sbquo;foo&lsquo;",
	//	"doubleGuillemetsFrench" => "&laquo;&nbsp;foo&nbsp;&raquo;",
	//	"doubleGuillemets" => "&laquo;foo&raquo;",
	//	"doubleGuillemetsReversed" => "&raquo;foo&laquo;",
	//	"singleGuillemets" => "&lsaquo;foo&rsaquo;",
	//	"singleGuillemetsReversed" => "&rsaquo;foo&lsaquo;",
	//	"cornerBrackets" => "&#x300c;foo&#x300d;",
	//	"whiteCornerBracket" => "&#x300e;foo&#x300f;",
	function set_smart_quotes_primary($style = "doubleCurled") {
		if($style == "doubleCurled") {
			$this->chr["doubleQuoteOpen"] = $this->uchr(8220);
			$this->chr["doubleQuoteClose"] = $this->uchr(8221);
		} elseif($style == "doubleCurledReversed") {
			$this->chr["doubleQuoteOpen"] = $this->uchr(8221);
			$this->chr["doubleQuoteClose"] = $this->uchr(8221);
		} elseif($style == "doubleLow9") {
			$this->chr["doubleQuoteOpen"] = $this->chr["doubleLow9Quote"];
			$this->chr["doubleQuoteClose"] = $this->uchr(8221);
		} elseif($style == "doubleLow9Reversed") {
			$this->chr["doubleQuoteOpen"] = $this->chr["doubleLow9Quote"];
			$this->chr["doubleQuoteClose"] = $this->uchr(8220);
		} elseif($style == "singleCurled") {
			$this->chr["doubleQuoteOpen"] = $this->uchr(8216);
			$this->chr["doubleQuoteClose"] = $this->uchr(8217);
		} elseif($style == "singleCurledReversed") {
			$this->chr["doubleQuoteOpen"] = $this->uchr(8217);
			$this->chr["doubleQuoteClose"] = $this->uchr(8217);
		} elseif($style == "singleLow9") {
			$this->chr["doubleQuoteOpen"] = $this->chr["singleLow9Quote"];
			$this->chr["doubleQuoteClose"] = $this->uchr(8217);
		} elseif($style == "singleLow9Reversed") {
			$this->chr["doubleQuoteOpen"] = $this->chr["singleLow9Quote"];
			$this->chr["doubleQuoteClose"] = $this->uchr(8216);
		} elseif($style == "doubleGuillemetsFrench") {
			$this->chr["doubleQuoteOpen"] = $this->chr["guillemetOpen"].$this->chr["noBreakSpace"];
			$this->chr["doubleQuoteClose"] = $this->chr["noBreakSpace"].$this->chr["guillemetClose"];
		} elseif($style == "doubleGuillemets") {
			$this->chr["doubleQuoteOpen"] = $this->chr["guillemetOpen"];
			$this->chr["doubleQuoteClose"] = $this->chr["guillemetClose"];
		} elseif($style == "doubleGuillemetsReversed") {
			$this->chr["doubleQuoteOpen"] = $this->chr["guillemetClose"];
			$this->chr["doubleQuoteClose"] = $this->chr["guillemetOpen"];
		} elseif($style == "singleGuillemets") {
			$this->chr["doubleQuoteOpen"] = $this->chr["singleAngleQuoteOpen"];
			$this->chr["doubleQuoteClose"] = $this->chr["singleAngleQuoteClose"];
		} elseif($style == "singleGuillemetsReversed") {
			$this->chr["doubleQuoteOpen"] = $this->chr["singleAngleQuoteClose"];
			$this->chr["doubleQuoteClose"] = $this->chr["singleAngleQuoteOpen"];
		} elseif($style == "cornerBrackets") {
			$this->chr["doubleQuoteOpen"] = $this->chr["leftCornerBracket"];
			$this->chr["doubleQuoteClose"] = $this->chr["rightCornerBracket"];
		} elseif($style == "whiteCornerBracket") {
			$this->chr["doubleQuoteOpen"] = $this->chr["leftWhiteCornerBracket"];
			$this->chr["doubleQuoteClose"] = $this->chr["rightWhiteCornerBracket"];
		} else {
			$this->chr["doubleQuoteOpen"] = $this->uchr(8220);
			$this->chr["doubleQuoteClose"] = $this->uchr(8221);
		}
		return TRUE;
	}
	// Secondary quotemarks style
	// allowed values for $style
	//	"doubleCurled" => "&ldquo;foo&rdquo;",
	//	"doubleCurledReversed" => "&rdquo;foo&rdquo;",
	//	"doubleLow9" => "&bdquo;foo&rdquo;",
	//	"doubleLow9Reversed" => "&bdquo;foo&ldquo;",
	//	"singleCurled" => "&lsquo;foo&rsquo;",
	//	"singleCurledReversed" => "&rsquo;foo&rsquo;",
	//	"singleLow9" => "&sbquo;foo&rsquo;",
	//	"singleLow9Reversed" => "&sbquo;foo&lsquo;",
	//	"doubleGuillemetsFrench" => "&laquo;&nbsp;foo&nbsp;&raquo;",
	//	"doubleGuillemets" => "&laquo;foo&raquo;",
	//	"doubleGuillemetsReversed" => "&raquo;foo&laquo;",
	//	"singleGuillemets" => "&lsaquo;foo&rsaquo;",
	//	"singleGuillemetsReversed" => "&rsaquo;foo&lsaquo;",
	//	"cornerBrackets" => "&#x300c;foo&#x300d;",
	//	"whiteCornerBracket" => "&#x300e;foo&#x300f;",
	function set_smart_quotes_secondary($style = "singleCurled") {
		if($style == "doubleCurled") {
			$this->chr["singleQuoteOpen"] = $this->uchr(8220);
			$this->chr["singleQuoteClose"] = $this->uchr(8221);
		} elseif($style == "doubleCurledReversed") {
			$this->chr["singleQuoteOpen"] = $this->uchr(8221);
			$this->chr["singleQuoteClose"] = $this->uchr(8221);
		} elseif($style == "doubleLow9") {
			$this->chr["singleQuoteOpen"] = $this->chr["doubleLow9Quote"];
			$this->chr["singleQuoteClose"] = $this->uchr(8221);
		} elseif($style == "doubleLow9Reversed") {
			$this->chr["singleQuoteOpen"] = $this->chr["doubleLow9Quote"];
			$this->chr["singleQuoteClose"] = $this->uchr(8220);
		} elseif($style == "singleCurled") {
			$this->chr["singleQuoteOpen"] = $this->uchr(8216);
			$this->chr["singleQuoteClose"] = $this->uchr(8217);
		} elseif($style == "singleCurledReversed") {
			$this->chr["singleQuoteOpen"] = $this->uchr(8217);
			$this->chr["singleQuoteClose"] = $this->uchr(8217);
		} elseif($style == "singleLow9") {
			$this->chr["singleQuoteOpen"] = $this->chr["singleLow9Quote"];
			$this->chr["singleQuoteClose"] = $this->uchr(8217);
		} elseif($style == "singleLow9Reversed") {
			$this->chr["singleQuoteOpen"] = $this->chr["singleLow9Quote"];
			$this->chr["singleQuoteClose"] = $this->uchr(8216);
		} elseif($style == "doubleGuillemetsFrench") {
			$this->chr["singleQuoteOpen"] = $this->chr["guillemetOpen"].$this->chr["noBreakSpace"];
			$this->chr["singleQuoteClose"] = $this->chr["noBreakSpace"].$this->chr["guillemetClose"];
		} elseif($style == "doubleGuillemets") {
			$this->chr["singleQuoteOpen"] = $this->chr["guillemetOpen"];
			$this->chr["singleQuoteClose"] = $this->chr["guillemetClose"];
		} elseif($style == "doubleGuillemetsReversed") {
			$this->chr["singleQuoteOpen"] = $this->chr["guillemetClose"];
			$this->chr["singleQuoteClose"] = $this->chr["guillemetOpen"];
		} elseif($style == "singleGuillemets") {
			$this->chr["singleQuoteOpen"] = $this->chr["singleAngleQuoteOpen"];
			$this->chr["singleQuoteClose"] = $this->chr["singleAngleQuoteClose"];
		} elseif($style == "singleGuillemetsReversed") {
			$this->chr["singleQuoteOpen"] = $this->chr["singleAngleQuoteClose"];
			$this->chr["singleQuoteClose"] = $this->chr["singleAngleQuoteOpen"];
		} elseif($style == "cornerBrackets") {
			$this->chr["singleQuoteOpen"] = $this->chr["leftCornerBracket"];
			$this->chr["singleQuoteClose"] = $this->chr["rightCornerBracket"];
		} elseif($style == "whiteCornerBracket") {
			$this->chr["singleQuoteOpen"] = $this->chr["leftWhiteCornerBracket"];
			$this->chr["singleQuoteClose"] = $this->chr["rightWhiteCornerBracket"];
		} else {
			$this->chr["singleQuoteOpen"] = $this->uchr(8216);
			$this->chr["singleQuoteClose"] = $this->uchr(8217);
		}
		return TRUE;
	}





	// replaces "a--a" with En Dash " -- " and "---" with Em Dash
	function set_smart_dashes($on = TRUE) {
		$this->settings["smartDashes"] = $on;
		return TRUE;
	}

	// replaces "..." with "…"
	function set_smart_ellipses($on = TRUE) {
		$this->settings["smartEllipses"] = $on;
		return TRUE;
	}
	
	// replaces "creme brulee" with "crème brûlée"
	function set_smart_diacritics($on = TRUE) {
		$this->settings["smartDiacritics"] = $on;
		return TRUE;
	}

	// defines hyphenation language for text
	function set_diacritic_language($lang = "en-US") {
		if (isset($this->settings["diacriticLanguage"]) &&	$this->settings["diacriticLanguage"] == $lang)
			return TRUE;
		
		$this->settings["diacriticLanguage"] = $lang;

		if(file_exists(dirname(__FILE__).'/diacritics/'.$this->settings["diacriticLanguage"].'.php')) {
			include('diacritics/'.$this->settings["diacriticLanguage"].'.php');
		} else {
			include('diacritics/en-US.php');
		}
		$this->settings["diacriticWords"] = $diacriticWords;
		
		return TRUE;
	}

	// $customReplacements must be
	//		an array formatted array(needle=>replacement, needle=>replacement...), or
	//		a string formatted `"needle"=>"replacement","needle"=>"replacement",...`
	function set_diacritic_custom_replacements($customReplacements = array()) {
		$replacements = array();
		if(!is_array($customReplacements)) 
			$customReplacements = preg_split("/,/", $customReplacements, -1, PREG_SPLIT_NO_EMPTY);
		foreach($customReplacements as $customReplacement) {
			//account for single and double quotes
			preg_match("/(?:\")([^\"]+)(?:\"\s*=>)/", $customReplacement, $doubleQuoteKeyMatch);
			preg_match("/(?:')([^']+)(?:'\s*=>)/", $customReplacement, $singleQuoteKeyMatch);
			preg_match("/(?:=>\s*\")([^\"]+)(?:\")/", $customReplacement, $doubleQuoteValueMatch);
			preg_match("/(?:=>\s*')([^']+)(?:')/", $customReplacement, $singleQuoteValueMatch);

			if( isset($doubleQuoteKeyMatch[1]) && ( $doubleQuoteKeyMatch[1] != "" ) ) {
				$key = $doubleQuoteKeyMatch[1];
			} elseif( isset($singleQuoteKeyMatch[1]) && ( $singleQuoteKeyMatch[1] != "" ) ) {
				$key = $singleQuoteKeyMatch[1];
			}
			
			if( isset($doubleQuoteValueMatch[1]) && ( $doubleQuoteValueMatch[1] != "" ) ) {
				$value = $doubleQuoteValueMatch[1];
			} elseif( isset($singleQuoteValueMatch[1]) && ( $singleQuoteValueMatch[1] != "" ) ) {
				$value = $singleQuoteValueMatch[1];
			}
			
			if( isset($key) && isset($value) ) {
				$replacements[strip_tags(trim($key))] = strip_tags(trim($value));
			}
		}
			
		$this->settings["diacriticCustomReplacements"] = $replacements;
		return TRUE;
	}


	// replaces (r) (c) (tm) (sm) (p) (R) (C) (TM) (SM) (P) with ® © ™ ℠ ℗
	function set_smart_marks($on = TRUE) {
		$this->settings["smartMarks"] = $on;
		return TRUE;
	}

	// replaces 1/4  with <sup>1</sup>&#8260;<sub>4</sub>
	function set_smart_math($on = TRUE) {
		$this->settings["smartMath"] = $on;
		return TRUE;
	}

	// replaces 1/4  with <sup>1</sup>&#8260;<sub>4</sub>
	function set_smart_exponents($on = TRUE) {
		$this->settings["smartExponents"] = $on;
		return TRUE;
	}

	// replaces 1/4  with <sup>1</sup>&#8260;<sub>4</sub>
	function set_smart_fractions($on = TRUE) {
		$this->settings["smartFractions"] = $on;
		return TRUE;
	}

	// DEPRECIATED
	function set_smart_multiplication($on = TRUE) {
		$this->settings["smartMath"] = $on;
		return TRUE;
	}
	
	// wrap numbers in <span class="numbers">
	function set_smart_ordinal_suffix($on = TRUE) {
		$this->settings["smartOrdinalSuffix"] = $on;
		return TRUE;
	}

	// single character words are forced to next line with insertion of &nbsp;
	function set_single_character_word_spacing($on = TRUE) {
		$this->settings["singleCharacterWordSpacing"] = $on;
		return TRUE;
	}
	
	// units and values are kept together with insertion of &nbsp;
	function set_fraction_spacing($on = TRUE) {
		$this->settings["fractionSpacing"] = $on;
		return TRUE;
	}

	// units and values are kept together with insertion of &nbsp;
	function set_unit_spacing($on = TRUE) {
		$this->settings["unitSpacing"] = $on;
		return TRUE;
	}

	// a list of units to keep with their values
	function set_units($units = array()) {
		if(!is_array($units)) 
			$units = preg_split("/[\s,]+/", $units, -1, PREG_SPLIT_NO_EMPTY);
		$this->settings["units"] = $units;
		return TRUE;
	}

	// Em and En dashes are wrapped in thin spaces
	function set_dash_spacing($on = TRUE) {
		$this->settings["dashSpacing"] = $on;
		return TRUE;
	}
	
	// Remove extra space Characters
	function set_space_collapse($on = TRUE) {
		$this->settings["spaceCollapse"] = $on;
		return TRUE;
	}

	// enables widow handling
	function set_dewidow($on = TRUE) {
		$this->settings["dewidow"] = $on;
		return TRUE;
	}
	
	// establishes maximum length of a widows that will be protected
	function set_max_dewidow_length($len = 5) {
		$len = ($len > 1) ? $len : 5;
		$this->settings["dewidowMaxLength"] = $len;
		return TRUE;
	}
	
	// establishes maximum length of pulled text to keep widows company
	function set_max_dewidow_pull($len = 5) {
		$len = ($len > 1) ? $len : 5;
		$this->settings["dewidowMaxPull"] = $len;
		return TRUE;
	}
	
	// enables wrapping at hard hyphens internal to a word with the insertion of a zero-width-space
	function set_wrap_hard_hyphens($on = TRUE) {
		$this->settings["hyphenHardWrap"] = $on;
		return TRUE;
	}

	// enables wrapping of urls
	function set_url_wrap($on = TRUE) {
		$this->settings["urlWrap"] = $on;
		return TRUE;
	}

	// enables wrapping of email addresses
	function set_email_wrap($on = TRUE) {
		$this->settings["emailWrap"] = $on;
		return TRUE;
	}
	
	// establishes minimum character requirement after a url wrapping point
	function set_min_after_url_wrap($len = 5) {
		$len = ($len > 0) ? $len : 5;
		$this->settings["urlMinAfterWrap"] = $len;
		return TRUE;
	}

	// wrap ampersands in <span class="amp">
	function set_style_ampersands($on = TRUE) {
		$this->settings["styleAmpersands"] = $on;
		return TRUE;
	}

	// wrap caps in <span class="caps">
	function set_style_caps($on = TRUE) {
		$this->settings["styleCaps"] = $on;
		return TRUE;
	}

	// wrap initial quotes in <span class="quo"> or <span class="dquo">
	function set_style_initial_quotes($on = TRUE) {
		$this->settings["styleInitialQuotes"] = $on;
		return TRUE;
	}
	
	// wrap numbers in <span class="numbers">
	function set_style_numbers($on = TRUE) {
		$this->settings["styleNumbers"] = $on;
		return TRUE;
	}

	// sets tags where initial quotes and guillemets should be styled
	function set_initial_quote_tags($tags = array("p", "h1", "h2", "h3", "h4", "h5", "h6", "blockquote", "li", "dd", "dt")) {
		if(!is_array($tags)) 
			$tags = preg_split("/[^a-z0-9]+/", $tags, -1, PREG_SPLIT_NO_EMPTY);
		foreach($tags as &$tag){
			$tag = strtolower($tag);
		}
		$this->settings["initialQuoteTags"] = $tags;
		return TRUE;
	}

	// enables hyphenation of text
	function set_hyphenation($on = TRUE) {
		$this->settings["hyphenation"] = $on;
		return TRUE;
	}
	
	// defines hyphenation language for text
	function set_hyphenation_language($lang = "en-US") {
		if (isset($this->settings["hyphenLanguage"]) &&	$this->settings["hyphenLanguage"] == $lang)
			return TRUE;
		
		$this->settings["hyphenLanguage"] = $lang;

		if(file_exists(dirname(__FILE__).'/lang/'.$this->settings["hyphenLanguage"].'.php')) {
			include('lang/'.$this->settings["hyphenLanguage"].'.php');
		} else {
			include('lang/en-US.php');
		}
		$this->settings["hyphenationPattern"] = $patgen;
		$this->settings["hyphenationPatternMaxSegment"] = $patgenMaxSeg;
		$this->settings["hyphenationPatternExceptions"] = $patgenExceptions;
		
		// make sure hyphenationExceptions is not set to force remerging of patgen and custom exceptions
		if(isset($this->settings["hyphenationExceptions"])) unset($this->settings["hyphenationExceptions"]);
		
		return TRUE;
	}
	
	// establishes minimum length of a word that may be hyphenated
	function set_min_length_hyphenation($len = 5) {
		$len = ($len > 1) ? $len : 5;
		$this->settings["hyphenMinLength"] = $len;
		return TRUE;
	}
	
	// establishes minimum character requirement before a hyphenation point
	function set_min_before_hyphenation($len = 3) {
		$len = ($len > 0) ? $len : 3;
		$this->settings["hyphenMinBefore"] = $len;
		return TRUE;
	}
	
	// establishes minimum character requirement after a hyphenation point
	function set_min_after_hyphenation($len = 2) {
		$len = ($len > 0) ? $len : 2;
		$this->settings["hyphenMinAfter"] = $len;
		return TRUE;
	}
	
	// allows/disallows hyphenation of title/heading text
	function set_hyphenate_headings($on = TRUE) {
		$this->settings["hyphenateTitle"] = $on;
		return TRUE;
	}
	
	// allows hyphenation of strings of all capital characters
	function set_hyphenate_all_caps($on = TRUE) {
		$this->settings["hyphenateAllCaps"] = $on;
		return TRUE;
	}
	
	// allows hyphenation of strings of all capital characters
	// added in version 1.5
	function set_hyphenate_title_case($on = TRUE) {
		$this->settings["hyphenateTitleCase"] = $on;
		return TRUE;
	}
	
	// defines custom word hyphenations
	// expected input is an array of words with all hyphenation points marked with a hard hyphen
	function set_hyphenation_exceptions($exceptions = array()) {

		$encodings = array("ASCII","UTF-8", "ISO-8859-1");
		$multibyte = FALSE;
		$u = "";
		if(!is_array($exceptions)) 
			$exceptions = preg_split("/[^a-zA-Z0-9\-]+/", $exceptions, -1, PREG_SPLIT_NO_EMPTY);
		
		$exceptionKeys = array();
		foreach($exceptions as $key => &$exception) {
			
			
			$encoding = mb_detect_encoding($exception."a", $encodings);
			if("UTF-8" == $encoding) {
				$multibyte = TRUE;
				$u = "u";
				if(!function_exists('mb_strlen')) return FALSE;
			} elseif("ASCII" == $encoding) {
				$multibyte = FALSE;
			} else {
				return FALSE;
			}
			
			if($multibyte) {
				$exception = mb_strtolower($exception, "UTF-8");
			} else {  //same as above without multibyte string functions to improve preformance
				$exception = strtolower($exception);
			}
			$exceptionKeys[$key] = preg_replace("#-#$u", "", $exception);
		}
		$e = array();
		foreach($exceptionKeys as $key => $value)    {
			$e[$value] = $exceptions[$key];
		}
		
		$this->settings["hyphenationCustomExceptions"] = $e;
				
		// make sure hyphenationExceptions is not set to force remerging of patgen and custom exceptions
		if(isset($this->settings["hyphenationExceptions"])) unset($this->settings["hyphenationExceptions"]);

		return TRUE;
	}

	

	#=======================================================================
	#=======================================================================
	#==	METHODS - ACTIONS, let's do something!
	#=======================================================================
	#=======================================================================


	#	Returns:	ARRAY of supported hyphenation languages in the form array( language code => language name)
	function get_languages() {
		$languages = array();
		$langDir = dirname(__FILE__)."/lang/";
		$handler = opendir($langDir);
		
		// read all files in directory
		while ($file = readdir($handler)) {
			// we only want the php files
			if (substr($file, -4) == ".php") {
				$fileContent = file_get_contents($langDir.$file);
				preg_match('/\$patgenLanguage\s*=\s*((".+")|(\'.+\'))\s*;/', $fileContent, $matches);
				$languageName = substr($matches[1], 1, -1);
				$languageCode = substr($file, 0, -4);
				$results[$languageCode] = $languageName;
			}
		}
		closedir($handler);

		asort($results);
		return $results;
	}

	#	Returns:	ARRAY of supported hyphenation languages in the form array( language code => language name)
	function get_diacritic_languages() {
		$languages = array();
		$langDir = dirname(__FILE__)."/diacritics/";
		$handler = opendir($langDir);
		
		// read all files in directory
		while ($file = readdir($handler)) {
			// we only want the php files
			if (substr($file, -4) == ".php") {
				$fileContent = file_get_contents($langDir.$file);
				preg_match('/\$diacriticLanguage\s*=\s*((".+")|(\'.+\'))\s*;/', $fileContent, $matches);
				$languageName = substr($matches[1], 1, -1);
				$languageCode = substr($file, 0, -4);
				$results[$languageCode] = $languageName;
			}
		}
		closedir($handler);

		asort($results);
		return $results;
	}

	#	Action:		modifies $html according to the defined settings
	#	Returns:	processed $html
	function process($html, $isTitle = FALSE) {
		
		if( isset($this->settings["ignoreTags"] ) && $isTitle && ( in_array('h1', $this->settings["ignoreTags"]) || in_array('h2', $this->settings["ignoreTags"]) ) )
			return $html;
		
		require_once("php-parser/php-parser.php");
		
		// parse the html
		$this->parsedHTML = new parseHTML();
		$this->parsedHTML->load($html);
		$this->parsedHTML->unlock_text();
		$tagsToIgnore = $this->parsedHTML->get_tags_by_name($this->settings["ignoreTags"]);
		if(isset($this->settings["ignoreClasses"]))
			$tagsToIgnore += $this->parsedHTML->get_tags_by_class($this->settings["ignoreClasses"]); //union to avoid dup keys
		if(isset($this->settings["ignoreIDs"]))
			$tagsToIgnore += $this->parsedHTML->get_tag_by_id($this->settings["ignoreIDs"]); //union to avoid dup keys
		$this->parsedHTML->lock_children($tagsToIgnore);
		$unlockedTexts = $this->parsedHTML->get_unlocked_text();

		foreach($unlockedTexts as &$unlockedText) {
		
			// we won't be doing anything with spaces, so we can jump ship if that is all we have
			if (0 == strlen(trim($unlockedText["value"]))) continue;
		
			// decode all characters except < > &
			$unlockedText["value"] = html_entity_decode($unlockedText["value"], ENT_QUOTES, "UTF-8"); //converts all HTML entities to their applicable characters
			$unlockedText["value"] = htmlspecialchars($unlockedText["value"], ENT_NOQUOTES, "UTF-8"); //returns < > & to encoded HTML characters (&lt; &gt; and &amp; respectively)

			// modify anything that requires adjacent text awareness here
			$unlockedText = $this->smart_math($unlockedText);
			$unlockedText = $this->smart_diacritics($unlockedText);
			$unlockedText = $this->smart_quotes($unlockedText);
			$unlockedText = $this->smart_dashes($unlockedText);
			$unlockedText = $this->smart_ellipses($unlockedText);
			$unlockedText = $this->smart_marks($unlockedText);
			
			//keep spacing after smart character replacement
			$unlockedText = $this->single_character_word_spacing($unlockedText);
			$unlockedText = $this->dash_spacing($unlockedText);
			$unlockedText = $this->unit_spacing($unlockedText);

			//break it down for a bit more granularity
			$this->parsedText = new parseText();
			$this->parsedText->load($unlockedText);
			$parsedMixedWords = $this->parsedText->get_words(-1,0); // prohibit letter only words, allow caps
			$caps = (isset($this->settings["hyphenateAllCaps"]) && $this->settings["hyphenateAllCaps"]) ? 0 : -1 ;
			$parsedWords = $this->parsedText->get_words(1,$caps);  // require letter only words, caps allowance in settingibutes; mutually exclusive with $parsedMixedWords
			$parsedOther = $this->parsedText->get_other();
			
			// process individual text parts here
			$parsedMixedWords = $this->wrap_hard_hyphens($parsedMixedWords);
			$parsedWords = $this->hyphenate($parsedWords, $isTitle);
			$parsedOther = $this->wrap_urls($parsedOther);
			$parsedOther = $this->wrap_emails($parsedOther);
			
			//apply updates to unlockedText
			$this->parsedText->update($parsedMixedWords+$parsedWords+$parsedOther);
			$unlockedText = $this->parsedText->unload();
			
			//some final space manipulation
			$unlockedText = $this->dewidow($unlockedText);
			$unlockedText = $this->space_collapse($unlockedText);

			//everything that requires HTML injection occurs here (functions above assume tag-free content)
			//pay careful attention to functions below for tolerance of injected tags
			$unlockedText = $this->smart_ordinal_suffix($unlockedText);	// call before "style_numbers" and "smart_fractions"	
			$unlockedText = $this->smart_exponents($unlockedText); // call before "style_numbers"
			$unlockedText = $this->smart_fractions($unlockedText); // call before "style_numbers" and after "smart_ordinal_suffix"
			if(!$this->parsedHTML->in_class('caps', $unlockedText))
				$unlockedText = $this->style_caps($unlockedText); // call before "style_numbers"		
			if(!$this->parsedHTML->in_class('numbers', $unlockedText))
				$unlockedText = $this->style_numbers($unlockedText); // call after "smart_ordinal_suffix", "smart_exponents", "smart_fractions", and "style_caps"	
			if(!$this->parsedHTML->in_class('amp', $unlockedText))
				$unlockedText = $this->style_ampersands($unlockedText);			
			if(!$this->parsedHTML->in_class(array('quo','dquo'), $unlockedText)) 
				$unlockedText = $this->style_initial_quotes($unlockedText, $isTitle);
		}
		
		$this->parsedHTML->update($unlockedTexts);
		return $this->parsedHTML->unload();
	}

	
	#	Action:		modifies $html according to the defined settings as only appropriate for RSS feeds 
	#				(i.e. excluding processes that may not display well with limited character set inteligence)
	#	Returns:	processed $html
	function process_feed($html, $isTitle = FALSE) {

		if( isset($this->settings["ignoreTags"]) && $isTitle && ( in_array('h1', $this->settings["ignoreTags"]) || in_array('h2', $this->settings["ignoreTags"]) ) )
			return $html;

		require_once("php-parser/php-parser.php");
		
		// parse the html
		$this->parsedHTML = new parseHTML();
		$this->parsedHTML->load($html);
		$this->parsedHTML->unlock_text();
		$tagsToIgnore = $this->parsedHTML->get_tags_by_name($this->settings["ignoreTags"]);
		if(isset($this->settings["ignoreClasses"]))
			$tagsToIgnore += $this->parsedHTML->get_tags_by_class($this->settings["ignoreClasses"]); //union to avoid dup keys
		if(isset($this->settings["ignoreIDs"]))
			$tagsToIgnore += $this->parsedHTML->get_tag_by_id($this->settings["ignoreIDs"]); //union to avoid dup keys
		$this->parsedHTML->lock_children($tagsToIgnore);
		$unlockedTexts = $this->parsedHTML->get_unlocked_text();
		
		foreach($unlockedTexts as &$unlockedText) {
		
			// we won't be doing anything with spaces, so we can jump ship if that is all we have
			if (0 == strlen(trim($unlockedText["value"]))) continue;

			// decode all characters except < > &
			$unlockedText["value"] = html_entity_decode($unlockedText["value"], ENT_QUOTES, "UTF-8"); //converts all HTML entities to their applicable characters
			$unlockedText["value"] = htmlspecialchars($unlockedText["value"], ENT_NOQUOTES, "UTF-8"); //returns < > & to encoded HTML characters (&lt; &gt; and &amp; respectively)
			
			// modify anything that requires adjacent text awareness here
			$unlockedText = $this->smart_quotes($unlockedText);
			$unlockedText = $this->smart_dashes($unlockedText);
			$unlockedText = $this->smart_ellipses($unlockedText);
			$unlockedText = $this->smart_marks($unlockedText);
		}
		
		// add $initialChrs and $widows back into $unlockedTexts;
		
		$this->parsedHTML->update($unlockedTexts);
		return $this->parsedHTML->unload();
	}


	

	#=======================================================================
	#=======================================================================
	#==	OTHER METHODS
	#=======================================================================
	#=======================================================================
	
	//expecting parsedHTML token of type text
	function smart_quotes($parsedHTMLtoken) {
		if(!isset($this->settings["smartQuotes"]) || !$this->settings["smartQuotes"]) return $parsedHTMLtoken;

		$nonEnglishWordCharacters = "
					[0-9A-Za-z]|\x{00c0}|\x{00c1}|\x{00c2}|\x{00c3}|\x{00c4}|\x{00c5}|\x{00c6}|\x{00c7}|\x{00c8}|\x{00c9}|
					\x{00ca}|\x{00cb}|\x{00cc}|\x{00cd}|\x{00ce}|\x{00cf}|\x{00d0}|\x{00d1}|\x{00d2}|\x{00d3}|\x{00d4}|
					\x{00d5}|\x{00d6}|\x{00d8}|\x{00d9}|\x{00da}|\x{00db}|\x{00dc}|\x{00dd}|\x{00de}|\x{00df}|\x{00e0}|
					\x{00e1}|\x{00e2}|\x{00e3}|\x{00e4}|\x{00e5}|\x{00e6}|\x{00e7}|\x{00e8}|\x{00e9}|\x{00ea}|\x{00eb}|
					\x{00ec}|\x{00ed}|\x{00ee}|\x{00ef}|\x{00f0}|\x{00f1}|\x{00f2}|\x{00f3}|\x{00f4}|\x{00f5}|\x{00f6}|
					\x{00f8}|\x{00f9}|\x{00fa}|\x{00fb}|\x{00fc}|\x{00fd}|\x{00fe}|\x{00ff}|\x{0100}|\x{0101}|\x{0102}|
					\x{0103}|\x{0104}|\x{0105}|\x{0106}|\x{0107}|\x{0108}|\x{0109}|\x{010a}|\x{010b}|\x{010c}|\x{010d}|
					\x{010e}|\x{010f}|\x{0110}|\x{0111}|\x{0112}|\x{0113}|\x{0114}|\x{0115}|\x{0116}|\x{0117}|\x{0118}|
					\x{0119}|\x{011a}|\x{011b}|\x{011c}|\x{011d}|\x{011e}|\x{011f}|\x{0120}|\x{0121}|\x{0122}|\x{0123}|
					\x{0124}|\x{0125}|\x{0126}|\x{0127}|\x{0128}|\x{0129}|\x{012a}|\x{012b}|\x{012c}|\x{012d}|\x{012e}|
					\x{012f}|\x{0130}|\x{0131}|\x{0132}|\x{0133}|\x{0134}|\x{0135}|\x{0136}|\x{0137}|\x{0138}|\x{0139}|
					\x{013a}|\x{013b}|\x{013c}|\x{013d}|\x{013e}|\x{013f}|\x{0140}|\x{0141}|\x{0142}|\x{0143}|\x{0144}|
					\x{0145}|\x{0146}|\x{0147}|\x{0148}|\x{0149}|\x{014a}|\x{014b}|\x{014c}|\x{014d}|\x{014e}|\x{014f}|
					\x{0150}|\x{0151}|\x{0152}|\x{0153}|\x{0154}|\x{0155}|\x{0156}|\x{0157}|\x{0158}|\x{0159}|\x{015a}|
					\x{015b}|\x{015c}|\x{015d}|\x{015e}|\x{015f}|\x{0160}|\x{0161}|\x{0162}|\x{0163}|\x{0164}|\x{0165}|
					\x{0166}|\x{0167}|\x{0168}|\x{0169}|\x{016a}|\x{016b}|\x{016c}|\x{016d}|\x{016e}|\x{016f}|\x{0170}|
					\x{0171}|\x{0172}|\x{0173}|\x{0174}|\x{0175}|\x{0176}|\x{0177}|\x{0178}|\x{0179}|\x{017a}|\x{017b}|
					\x{017c}|\x{017d}|\x{017e}|\x{017f}
					";

		//need to get context of adjacent characters outside adjacent inline tags or HTML comment
		//if we have adjacent characters add them to the text
		$nextChr = "";
		$prevChr = "";
		if(isset($parsedHTMLtoken["prevChr"]) && $parsedHTMLtoken["prevChr"] != "") {
			$prevChr = $parsedHTMLtoken["prevChr"];
			$parsedHTMLtoken["value"] = $prevChr.$parsedHTMLtoken["value"];
		}
		if(isset($parsedHTMLtoken["nextChr"]) && $parsedHTMLtoken["nextChr"] != "") {
			$nextChr = $parsedHTMLtoken["nextChr"];
			$parsedHTMLtoken["value"] = $parsedHTMLtoken["value"].$nextChr;
		}
		////Logic
		
		// before primes, handle quoted numbers
		$parsedHTMLtoken["value"] = preg_replace("/(?<=\W|\A)'(\d+)'(?=\W|\Z)/u", $this->chr["singleQuoteOpen"].'$1'.$this->chr["singleQuoteClose"], $parsedHTMLtoken["value"]);
		$parsedHTMLtoken["value"] = preg_replace("/(?<=\W|\A)\"(\d+)\"(?=\W|\Z)/u", $this->chr["doubleQuoteOpen"].'$1'.$this->chr["doubleQuoteClose"], $parsedHTMLtoken["value"]);

		// guillemets
		$parsedHTMLtoken["value"] = str_replace("<<", $this->chr["guillemetOpen"], $parsedHTMLtoken["value"]);
		$parsedHTMLtoken["value"] = str_replace("&lt;&lt;", $this->chr["guillemetOpen"], $parsedHTMLtoken["value"]);
		$parsedHTMLtoken["value"] = str_replace(">>", $this->chr["guillemetClose"], $parsedHTMLtoken["value"]);
		$parsedHTMLtoken["value"] = str_replace("&gt;&gt;", $this->chr["guillemetClose"], $parsedHTMLtoken["value"]);


		// primes
		$parsedHTMLtoken["value"] = preg_replace("/(\b\d+)''(?=\W|\Z)/u", '$1'.$this->chr["doublePrime"], $parsedHTMLtoken["value"]);
		$parsedHTMLtoken["value"] = preg_replace("/(\b\d+)\"(?=\W|\Z)/u", '$1'.$this->chr["doublePrime"], $parsedHTMLtoken["value"]);
		$parsedHTMLtoken["value"] = preg_replace("/(\b\d+)'(?=\W|\Z)/u", '$1'.$this->chr["singlePrime"], $parsedHTMLtoken["value"]);
		
		// backticks
		$parsedHTMLtoken["value"] = str_replace("``", $this->chr["doubleQuoteOpen"], $parsedHTMLtoken["value"]);
		$parsedHTMLtoken["value"] = str_replace("`", $this->chr["singleQuoteOpen"], $parsedHTMLtoken["value"]);
		$parsedHTMLtoken["value"] = str_replace("''", $this->chr["doubleQuoteClose"], $parsedHTMLtoken["value"]);
		
		// comma quotes
		$parsedHTMLtoken["value"] =str_replace(",,", $this->chr["doubleLow9Quote"], $parsedHTMLtoken["value"]);
		$parsedHTMLtoken["value"] = preg_replace("/(?<=\s|\A),(?=\S)/", $this->chr["singleLow9Quote"], $parsedHTMLtoken["value"]); //like _,¿hola?'_
		
		// apostrophes
		$parsedHTMLtoken["value"] = preg_replace("/(?<=[\w|$nonEnglishWordCharacters])'(?=[\w|$nonEnglishWordCharacters])/u", $this->chr["apostrophe"], $parsedHTMLtoken["value"]);
		$parsedHTMLtoken["value"] = preg_replace("/'(\d\d\b)/", $this->chr["apostrophe"].'$1', $parsedHTMLtoken["value"]); // decades: '98
		$exceptions = array("'tain".$this->chr["apostrophe"]."t", "'twere", "'twas", "'tis", "'til", "'bout", "'nuff", "'round", "'cause", "'splainin");
		$replacements = array($this->chr["apostrophe"]."tain".$this->chr["apostrophe"]."t", $this->chr["apostrophe"]."twere", $this->chr["apostrophe"]."twas", $this->chr["apostrophe"]."tis", $this->chr["apostrophe"]."til", $this->chr["apostrophe"]."bout", $this->chr["apostrophe"]."nuff", $this->chr["apostrophe"]."round", $this->chr["apostrophe"]."cause", $this->chr["apostrophe"]."splainin");
		$parsedHTMLtoken["value"] = str_replace($exceptions, $replacements, $parsedHTMLtoken["value"]);
		
		//quotes
		$quoteRules = array("['", "{'", "('", "']", "'}", "')", "[\"", "{\"", "(\"", "\"]", "\"}", "\")", "\"'", "'\"");
		$quoteRulesReplace = array("[".$this->chr["singleQuoteOpen"], "{".$this->chr["singleQuoteOpen"], "(".$this->chr["singleQuoteOpen"], $this->chr["singleQuoteClose"]."]", $this->chr["singleQuoteClose"]."}", $this->chr["singleQuoteClose"].")", "[".$this->chr["doubleQuoteOpen"], "{".$this->chr["doubleQuoteOpen"], "(".$this->chr["doubleQuoteOpen"], $this->chr["doubleQuoteClose"]."]", $this->chr["doubleQuoteClose"]."}", $this->chr["doubleQuoteClose"].")", $this->chr["doubleQuoteOpen"].$this->chr["singleQuoteOpen"], $this->chr["singleQuoteClose"].$this->chr["doubleQuoteClose"]);
		$parsedHTMLtoken["value"] =str_replace($quoteRules, $quoteRulesReplace, $parsedHTMLtoken["value"]);
		$parsedHTMLtoken["value"] = preg_replace("/'(?=[\w|$nonEnglishWordCharacters])/u", $this->chr["singleQuoteOpen"], $parsedHTMLtoken["value"]);
		$parsedHTMLtoken["value"] = preg_replace("/(?<=[\w|$nonEnglishWordCharacters])'/u", $this->chr["singleQuoteClose"], $parsedHTMLtoken["value"]);
		$parsedHTMLtoken["value"] = preg_replace("/(?<=\s|\A)'(?=\S)/", $this->chr["singleQuoteOpen"], $parsedHTMLtoken["value"]); //like _'¿hola?'_
		$parsedHTMLtoken["value"] = preg_replace("/(?<=\S)'(?=\s|\Z)/", $this->chr["singleQuoteClose"], $parsedHTMLtoken["value"]);
		$parsedHTMLtoken["value"] = preg_replace("/\"(?=[\w|$nonEnglishWordCharacters])/u", $this->chr["doubleQuoteOpen"], $parsedHTMLtoken["value"]);
		$parsedHTMLtoken["value"] = preg_replace("/(?<=[\w|$nonEnglishWordCharacters])\"/u", $this->chr["doubleQuoteClose"], $parsedHTMLtoken["value"]);
		$parsedHTMLtoken["value"] = preg_replace("/(?<=\s|\A)\"(?=\S)/", $this->chr["doubleQuoteOpen"], $parsedHTMLtoken["value"]);
		$parsedHTMLtoken["value"] = preg_replace("/(?<=\S)\"(?=\s|\Z)/", $this->chr["doubleQuoteClose"], $parsedHTMLtoken["value"]);

		//quote catch-alls - assume left over quotes are closing - as this is often the most complicated position, thus most likely to be missed
		$parsedHTMLtoken["value"] = str_replace("'", $this->chr["singleQuoteClose"], $parsedHTMLtoken["value"]);
		$parsedHTMLtoken["value"] = str_replace('"', $this->chr["doubleQuoteClose"], $parsedHTMLtoken["value"]);


		//if we have adjacent characters remove them from the text
		$encodings = array("ASCII","UTF-8");
		$e = mb_detect_encoding($parsedHTMLtoken["value"]."a", $encodings);// ."a" is a hack; see http://www.php.net/manual/en/function.mb-detect-encoding.php#81936
		if(!isset($e) || $e == "") $e = "ASCII";
		if($prevChr != "") {
			$parsedHTMLtoken["value"] = mb_substr($parsedHTMLtoken["value"], 1, mb_strlen($parsedHTMLtoken["value"], $e), $e);
		}
		if($nextChr != "") {
			$parsedHTMLtoken["value"] = mb_substr($parsedHTMLtoken["value"], 0, mb_strlen($parsedHTMLtoken["value"], $e)-1, $e);
		}
				
		return $parsedHTMLtoken;
	}

	//expecting parsedHTML token of type text
	function smart_dashes($parsedHTMLtoken) {
		if(!isset($this->settings["smartDashes"]) || !$this->settings["smartDashes"]) return $parsedHTMLtoken;

		$nonEnglishWordCharacters = "
					[0-9A-Za-z]|\x{00c0}|\x{00c1}|\x{00c2}|\x{00c3}|\x{00c4}|\x{00c5}|\x{00c6}|\x{00c7}|\x{00c8}|\x{00c9}|
					\x{00ca}|\x{00cb}|\x{00cc}|\x{00cd}|\x{00ce}|\x{00cf}|\x{00d0}|\x{00d1}|\x{00d2}|\x{00d3}|\x{00d4}|
					\x{00d5}|\x{00d6}|\x{00d8}|\x{00d9}|\x{00da}|\x{00db}|\x{00dc}|\x{00dd}|\x{00de}|\x{00df}|\x{00e0}|
					\x{00e1}|\x{00e2}|\x{00e3}|\x{00e4}|\x{00e5}|\x{00e6}|\x{00e7}|\x{00e8}|\x{00e9}|\x{00ea}|\x{00eb}|
					\x{00ec}|\x{00ed}|\x{00ee}|\x{00ef}|\x{00f0}|\x{00f1}|\x{00f2}|\x{00f3}|\x{00f4}|\x{00f5}|\x{00f6}|
					\x{00f8}|\x{00f9}|\x{00fa}|\x{00fb}|\x{00fc}|\x{00fd}|\x{00fe}|\x{00ff}|\x{0100}|\x{0101}|\x{0102}|
					\x{0103}|\x{0104}|\x{0105}|\x{0106}|\x{0107}|\x{0108}|\x{0109}|\x{010a}|\x{010b}|\x{010c}|\x{010d}|
					\x{010e}|\x{010f}|\x{0110}|\x{0111}|\x{0112}|\x{0113}|\x{0114}|\x{0115}|\x{0116}|\x{0117}|\x{0118}|
					\x{0119}|\x{011a}|\x{011b}|\x{011c}|\x{011d}|\x{011e}|\x{011f}|\x{0120}|\x{0121}|\x{0122}|\x{0123}|
					\x{0124}|\x{0125}|\x{0126}|\x{0127}|\x{0128}|\x{0129}|\x{012a}|\x{012b}|\x{012c}|\x{012d}|\x{012e}|
					\x{012f}|\x{0130}|\x{0131}|\x{0132}|\x{0133}|\x{0134}|\x{0135}|\x{0136}|\x{0137}|\x{0138}|\x{0139}|
					\x{013a}|\x{013b}|\x{013c}|\x{013d}|\x{013e}|\x{013f}|\x{0140}|\x{0141}|\x{0142}|\x{0143}|\x{0144}|
					\x{0145}|\x{0146}|\x{0147}|\x{0148}|\x{0149}|\x{014a}|\x{014b}|\x{014c}|\x{014d}|\x{014e}|\x{014f}|
					\x{0150}|\x{0151}|\x{0152}|\x{0153}|\x{0154}|\x{0155}|\x{0156}|\x{0157}|\x{0158}|\x{0159}|\x{015a}|
					\x{015b}|\x{015c}|\x{015d}|\x{015e}|\x{015f}|\x{0160}|\x{0161}|\x{0162}|\x{0163}|\x{0164}|\x{0165}|
					\x{0166}|\x{0167}|\x{0168}|\x{0169}|\x{016a}|\x{016b}|\x{016c}|\x{016d}|\x{016e}|\x{016f}|\x{0170}|
					\x{0171}|\x{0172}|\x{0173}|\x{0174}|\x{0175}|\x{0176}|\x{0177}|\x{0178}|\x{0179}|\x{017a}|\x{017b}|
					\x{017c}|\x{017d}|\x{017e}|\x{017f}
					";

		$parsedHTMLtoken["value"] = str_replace("---", $this->chr["emDash"], $parsedHTMLtoken["value"]);
		$parsedHTMLtoken["value"] = str_replace(" -- ", " ".$this->chr["emDash"]." ", $parsedHTMLtoken["value"]);
		$parsedHTMLtoken["value"] = str_replace("--", $this->chr["enDash"], $parsedHTMLtoken["value"]);
		$parsedHTMLtoken["value"] = str_replace(" - ", " ".$this->chr["emDash"]." ", $parsedHTMLtoken["value"]);

		$parsedHTMLtoken["value"] = preg_replace("/(\A|\s)\-([\w|$nonEnglishWordCharacters])/u", '$1'.$this->chr["enDash"].'$2', $parsedHTMLtoken["value"]);
		$parsedHTMLtoken["value"] = preg_replace("/([\w|$nonEnglishWordCharacters])\-(\Z|\s)/u", '$1'.$this->chr["enDash"].'$2', $parsedHTMLtoken["value"]);
		$parsedHTMLtoken["value"] = preg_replace("/(\b\d+)\-(\d+\b)/", '$1'.$this->chr["enDash"].'$2', $parsedHTMLtoken["value"]);
		$parsedHTMLtoken["value"] = preg_replace("/(\b\d{3})".$this->chr["enDash"]."(\d{4}\b)/", '$1'.$this->chr["noBreakHyphen"].'$2', $parsedHTMLtoken["value"]); // phone numbers
		$parsedHTMLtoken["value"] = str_replace("xn".$this->chr["enDash"], "xn--", $parsedHTMLtoken["value"]);


		// revert dates back to original formats
		
		// YYYY-MM-DD
		$pattern = "/
				(
					(?<=\s|\A|".$this->chr["noBreakSpace"].")
					[12][0-9]{3}
				)
				[\-".$this->chr["enDash"]."]
				(
					(?:[0][1-9]|[1][0-2])
				)
				[\-".$this->chr["enDash"]."]
				(
					(?:[0][1-9]|[12][0-9]|[3][0-1])
					(?=\s|\Z|\)|\]|\.|\,|\?|\;|\:|\'|\"|\!|".$this->chr["noBreakSpace"].")
				)
			/xu";
		$parsedHTMLtoken["value"] = preg_replace($pattern, "$1-$2-$3", $parsedHTMLtoken["value"]);
		
		// MM-DD-YYYY or DD-MM-YYYY
		$pattern = "/
				(?:
					(?:
						(
							(?<=\s|\A|".$this->chr["noBreakSpace"].")
							(?:[0]?[1-9]|[1][0-2])
						)
						[\-".$this->chr["enDash"]."]
						(
							(?:[0]?[1-9]|[12][0-9]|[3][0-1])
						)
					)
					|
					(?:
						(
							(?<=\s|\A|".$this->chr["noBreakSpace"].")
							(?:[0]?[1-9]|[12][0-9]|[3][0-1])
						)
						[\-".$this->chr["enDash"]."]
						(
							(?:[0]?[1-9]|[1][0-2])
						)
					)
				)
				[\-".$this->chr["enDash"]."]
				(
					[12][0-9]{3}
					(?=\s|\Z|\)|\]|\.|\,|\?|\;|\:|\'|\"|\!|".$this->chr["noBreakSpace"].")
				)
			/xu";
		$parsedHTMLtoken["value"] = preg_replace($pattern, "$1$3-$2$4-$5", $parsedHTMLtoken["value"]);
		
		// YYYY-MM or YYYY-DDDD next
		$pattern = "/
				(
					(?<=\s|\A|".$this->chr["noBreakSpace"].")
					[12][0-9]{3}
				)
				[\-".$this->chr["enDash"]."]
				(
					(?:
						(?:[0][1-9]|[1][0-2])
						|
						(?:[0][0-9][1-9]|[1-2][0-9]{2}|[3][0-5][0-9]|[3][6][0-6])
					)
					(?=\s|\Z|\)|\]|\.|\,|\?|\;|\:|\'|\"|\!|".$this->chr["noBreakSpace"].")
				)
			/xu";
		$parsedHTMLtoken["value"] = preg_replace($pattern, "$1-$2", $parsedHTMLtoken["value"]);



		return $parsedHTMLtoken;
	}

	//expecting parsedHTML token of type text
	function smart_ellipses($parsedHTMLtoken) {
		if(!isset($this->settings["smartEllipses"]) || !$this->settings["smartEllipses"]) return $parsedHTMLtoken;
		$parsedHTMLtoken["value"] = str_replace(array("....",     ". . . .",), ".".$this->chr["ellipses"], $parsedHTMLtoken["value"]);
		$parsedHTMLtoken["value"] = str_replace(array("...",     ". . .",), $this->chr["ellipses"], $parsedHTMLtoken["value"]);
		return $parsedHTMLtoken;
	}

	//expecting parsedHTML token of type text
	function smart_diacritics($parsedHTMLtoken) {
		if(!isset($this->settings["smartDiacritics"]) || !$this->settings["smartDiacritics"]) return $parsedHTMLtoken;

		if( isset($this->settings["diacriticCustomReplacements"]) && ( count($this->settings["diacriticCustomReplacements"]) > 0 ) ) {
			foreach($this->settings["diacriticCustomReplacements"] as $needle => $replacement) {
				$parsedHTMLtoken["value"] = preg_replace("/\b$needle\b/", $replacement, $parsedHTMLtoken["value"]);
			}
		}
		if( isset($this->settings["diacriticWords"]) && ( count($this->settings["diacriticWords"]) > 0 ) ) {
			foreach($this->settings["diacriticWords"] as $needle => $replacement) {
				$parsedHTMLtoken["value"] = preg_replace("/\b$needle\b/", $replacement, $parsedHTMLtoken["value"]);
			}
		}

		return $parsedHTMLtoken;
	}




	//expecting parsedHTML token of type text
	function smart_marks($parsedHTMLtoken) {
		if(!isset($this->settings["smartMarks"]) || !$this->settings["smartMarks"]) return $parsedHTMLtoken;
		$parsedHTMLtoken["value"] = str_replace(array("(c)", "(C)"), $this->chr["copyright"], $parsedHTMLtoken["value"]);
		$parsedHTMLtoken["value"] = str_replace(array("(r)", "(R)"), $this->chr["registeredMark"], $parsedHTMLtoken["value"]);
		$parsedHTMLtoken["value"] = str_replace(array("(p)", "(P)"), $this->chr["soundCopyMark"], $parsedHTMLtoken["value"]);
		$parsedHTMLtoken["value"] = str_replace(array("(sm)", "(SM)"), $this->chr["serviceMark"], $parsedHTMLtoken["value"]);
		$parsedHTMLtoken["value"] = str_replace(array("(tm)", "(TM)"), $this->chr["tradeMark"], $parsedHTMLtoken["value"]);
		return $parsedHTMLtoken;
	}

	//expecting parsedHTML token of type text
	function smart_math($parsedHTMLtoken) {
		
		if(!isset($this->settings["smartMath"]) || !$this->settings["smartMath"]) return $parsedHTMLtoken;

		//first, let's find math equations
		$pattern = "/
				(?<=\A|\s)										# lookbehind assertion: proceeded by beginning of string or space
				[\.,\'\"\¿\¡".$this->chr["ellipses"].$this->chr["singleQuoteOpen"].$this->chr["doubleQuoteOpen"].$this->chr["guillemetOpen"].$this->chr["guillemetClose"].$this->chr["singleLow9Quote"].$this->chr["doubleLow9Quote"]."]*
																# allowed proceeding punctuation
				[\-\(".$this->chr["minus"]."]*					# optionally proceeded by dash, minus sign or open parenthesis
				[0-9]+											# must begin with a number 
				(\.[0-9]+)?										# optionally allow decimal values after first integer
				(												# followed by a math symbol and a number
					[\/\*x\-+=\^".$this->chr["minus"].$this->chr["multiplication"].$this->chr["division"]."]
																# allowed math symbols
					[\-\(".$this->chr["minus"]."]*				# opptionally preceeded by dash, minus sign or open parenthesis
					[0-9]+										# must begin with a number 
					(\.[0-9]+)?									# optionally allow decimal values after first integer
					[\-\(\)".$this->chr["minus"]."]*			# opptionally preceeded by dash, minus sign or parenthesis
				)+
				[\.,;:\'\"\?\!".$this->chr["ellipses"].$this->chr["singleQuoteClose"].$this->chr["doubleQuoteClose"].$this->chr["guillemetOpen"].$this->chr["guillemetClose"]."]*
																# allowed trailing punctuation
				(?=\Z|\s)										# lookahead assertion: followed by end of string or space
			/ux";
		$parsedHTMLtoken["value"] = preg_replace_callback(
			$pattern,
			array($this, '_smart_math_callback'),
			$parsedHTMLtoken["value"]
		);
		
		// revert 4-4 to plain minus-hyphen so as to not mess with ranges of numbers (i.e. pp. 46-50)
		$pattern = "/
				(
					(?<=\s|\A|".$this->chr["noBreakSpace"].")
					\d+
				)
				[\-".$this->chr["minus"]."]
				(
					\d+
					(?=\s|\Z|\)|\]|\.|\,|\?|\;|\:|\'|\"|\!|".$this->chr["noBreakSpace"].")
				)
			/xu";
		$parsedHTMLtoken["value"] = preg_replace($pattern, "$1-$2", $parsedHTMLtoken["value"]);


		//revert fractions to basic slash
		// we'll leave styling fractions to smart_fractions
		$pattern = "/
				(
					(?<=\s|\A|\'|\"|".$this->chr["noBreakSpace"].")
					\d+
				)
				".$this->chr["division"]."
				(
					\d+
					(?:st|nd|rd|th)?
					(?=\s|\Z|\)|\]|\.|\,|\?|\;|\:|\'|\"|\!|".$this->chr["noBreakSpace"].")
				)
			/xu";
		$parsedHTMLtoken["value"] = preg_replace($pattern, "$1/$2", $parsedHTMLtoken["value"]);

		
		// revert date back to original formats
		
		// YYYY-MM-DD
		$pattern = "/
				(
					(?<=\s|\A|".$this->chr["noBreakSpace"].")
					[12][0-9]{3}
				)
				[\-".$this->chr["minus"]."]
				(
					(?:[0]?[1-9]|[1][0-2])
				)
				[\-".$this->chr["minus"]."]
				(
					(?:[0]?[1-9]|[12][0-9]|[3][0-1])
					(?=\s|\Z|\)|\]|\.|\,|\?|\;|\:|\'|\"|\!|".$this->chr["noBreakSpace"].")
				)
			/xu";
		$parsedHTMLtoken["value"] = preg_replace($pattern, "$1-$2-$3", $parsedHTMLtoken["value"]);
		
		// MM-DD-YYYY or DD-MM-YYYY
		$pattern = "/
				(?:
					(?:
						(
							(?<=\s|\A|".$this->chr["noBreakSpace"].")
							(?:[0]?[1-9]|[1][0-2])
						)
						[\-".$this->chr["minus"]."]
						(
							(?:[0]?[1-9]|[12][0-9]|[3][0-1])
						)
					)
					|
					(?:
						(
							(?<=\s|\A|".$this->chr["noBreakSpace"].")
							(?:[0]?[1-9]|[12][0-9]|[3][0-1])
						)
						[\-".$this->chr["minus"]."]
						(
							(?:[0]?[1-9]|[1][0-2])
						)
					)
				)
				[\-".$this->chr["minus"]."]
				(
					[12][0-9]{3}
					(?=\s|\Z|\)|\]|\.|\,|\?|\;|\:|\'|\"|\!|".$this->chr["noBreakSpace"].")
				)
			/xu";
		$parsedHTMLtoken["value"] = preg_replace($pattern, "$1$3-$2$4-$5", $parsedHTMLtoken["value"]);
		
		// YYYY-MM or YYYY-DDD next
		$pattern = "/
				(
					(?<=\s|\A|".$this->chr["noBreakSpace"].")
					[12][0-9]{3}
				)
				[\-".$this->chr["minus"]."]
				(
					(?:
						(?:[0][1-9]|[1][0-2])
						|
						(?:[0][0-9][1-9]|[1-2][0-9]{2}|[3][0-5][0-9]|[3][6][0-6])
					)
					(?=\s|\Z|\)|\]|\.|\,|\?|\;|\:|\'|\"|\!|".$this->chr["noBreakSpace"].")
				)
			/xu";
			
		// MM/DD/YYYY or DD/MM/YYYY
		$pattern = "/
				(?:
					(?:
						(
							(?<=\s|\A|".$this->chr["noBreakSpace"].")
							(?:[0][1-9]|[1][0-2])
						)
						[\/".$this->chr["division"]."]
						(
							(?:[0][1-9]|[12][0-9]|[3][0-1])
						)
					)
					|
					(?:
						(
							(?<=\s|\A|".$this->chr["noBreakSpace"].")
							(?:[0][1-9]|[12][0-9]|[3][0-1])
						)
						[\/".$this->chr["division"]."]
						(
							(?:[0][1-9]|[1][0-2])
						)
					)
				)
				[\/".$this->chr["division"]."]
				(
					[12][0-9]{3}
					(?=\s|\Z|\)|\]|\.|\,|\?|\;|\:|\'|\"|\!|".$this->chr["noBreakSpace"].")
				)
			/xu";
		$parsedHTMLtoken["value"] = preg_replace($pattern, "$1$3/$2$4/$5", $parsedHTMLtoken["value"]);

		return $parsedHTMLtoken;
	}
	
	function _smart_math_callback($matches) {
		$matches[0] = str_replace("-", $this->chr["minus"], $matches[0]);
		$matches[0] = str_replace("/", $this->chr["division"], $matches[0]);
		$matches[0] = str_replace("x", $this->chr["multiplication"], $matches[0]);
		$matches[0] = str_replace("*", $this->chr["multiplication"], $matches[0]);
		return $matches[0];
	}

	//expecting parsedHTML token of type text
	// purposefully seperatred from smart_math because of HTML code injection
	function smart_exponents($parsedHTMLtoken) {
		if(!isset($this->settings["smartExponents"]) || !$this->settings["smartExponents"]) return $parsedHTMLtoken;
		
		//handle exponents (ie. 4^2)
		$pat = "/
			\b
			(\d+)
			\^
			(\w+)
			\b
		/xu";
		$parsedHTMLtoken["value"] = preg_replace($pat, '$1<sup>$2</sup>', $parsedHTMLtoken["value"]);

		return $parsedHTMLtoken;
	}

	// expecting parsedHTML token of type text
	// call before sytle_numbers
	// call after smart_ordinal_suffix
	// purposefully seperatred from smart_math because of HTML code injection
	function smart_fractions($parsedHTMLtoken) {
		if((!isset($this->settings["smartFractions"]) || !$this->settings["smartFractions"]) && (!isset($this->settings["fractionSpacing"]) || !$this->settings["fractionSpacing"])) return $parsedHTMLtoken;
		
		$pat = "/\b(\d+)\s(\d+\s?\/\s?\d+)\b/";
		if((isset($this->settings["fractionSpacing"]) && $this->settings["fractionSpacing"]) && (isset($this->settings["smartFractions"]) && $this->settings["smartFractions"])) {
			$parsedHTMLtoken["value"] = preg_replace($pat, '$1'.$this->chr["noBreakNarrowSpace"].'$2', $parsedHTMLtoken["value"]);
		} elseif((isset($this->settings["fractionSpacing"]) && $this->settings["fractionSpacing"]) && (!isset($this->settings["fractionSpacing"]) || !$this->settings["smartFractions"])) {
			$parsedHTMLtoken["value"] = preg_replace($pat, '$1'.$this->chr["noBreakSpace"].'$2', $parsedHTMLtoken["value"]);
		}
		
		if(isset($this->settings["smartFractions"]) && $this->settings["smartFractions"]) {
			// because without simple variables, the pattern fails...
			$nbsp = $this->chr['noBreakSpace'];
			$nbnsp = $this->chr['noBreakNarrowSpace'];
			$pat = "/
				(?<=\A|\s|$nbsp|$nbnsp)																# lookbehind assertion: makes sure we are not messing up a url
				(\d+)
				(?:\s?\/\s?".$this->chr["zeroWidthSpace"].")										# strip out any zero-width spaces inserted by wrap_hard_hyphens
				(\d+)
				(
					(?:\<sup\>(?:st|nd|rd|th)<\/sup\>)?												# handle ordinals after fractions
					(?:\Z|\s|$this->chr['noBreakSpace']|$this->chr['noBreakNarrowSpace']|\.|\!|\?|\)|\;|\:|\'|\")			# makes sure we are not messing up a url
				)
			/xu";
			
			$parsedHTMLtoken["value"] = preg_replace($pat, '<sup>$1</sup>'.$this->chr["fractionSlash"].'<sub>$2</sub>$3', $parsedHTMLtoken["value"]);
		}
		
		return $parsedHTMLtoken;
	}

	//DEPRECIATED!!
	//expecting parsedHTML token of type text
	function smart_multiplication($parsedHTMLtoken) {
		return $this->smart_math($parsedHTMLtoken);
	}

	// expecting parsedHTML token of type text
	// call before sytle_numbers
	function smart_ordinal_suffix($parsedHTMLtoken) {
		if(!isset($this->settings["smartOrdinalSuffix"]) || !$this->settings["smartOrdinalSuffix"]) return $parsedHTMLtoken;

		$parsedHTMLtoken["value"] = preg_replace("/\b(\d+)(st|nd|rd|th)\b/", '$1'.'<sup>$2</sup>', $parsedHTMLtoken["value"]);

		return $parsedHTMLtoken;
	}



	//expecting parsedHTML token of type text
	function single_character_word_spacing($parsedHTMLtoken) {
		if(!isset($this->settings["singleCharacterWordSpacing"]) || !$this->settings["singleCharacterWordSpacing"]) return $parsedHTMLtoken;

		// add $nextChr and $prevChr for context
		$nextChr = "";
		$prevChr = "";
		if(isset($parsedHTMLtoken["prevChr"]) && $parsedHTMLtoken["prevChr"] != "") {
			$prevChr = $parsedHTMLtoken["prevChr"];
			$parsedHTMLtoken["value"] = $prevChr.$parsedHTMLtoken["value"];
		}
		if(isset($parsedHTMLtoken["nextChr"]) && $parsedHTMLtoken["nextChr"] != "") {
			$nextChr = $parsedHTMLtoken["nextChr"];
			$parsedHTMLtoken["value"] = $parsedHTMLtoken["value"].$nextChr;
		}

		$parsedHTMLtoken["value"] = preg_replace(
			"/
				(?:
					(\s)
					(\w)
					\s
					(?=\w)
				)
			/xu", 
			'$1$2'.$this->chr['noBreakSpace'], 
			$parsedHTMLtoken["value"]
			);
			
		//if we have adjacent characters remove them from the text
		$encodings = array("ASCII","UTF-8");
		$e = mb_detect_encoding($parsedHTMLtoken["value"]."a", $encodings);// ."a" is a hack; see http://www.php.net/manual/en/function.mb-detect-encoding.php#81936
		if(!isset($e) || $e == "") $e = "ASCII";
		if($prevChr != "") {
			$parsedHTMLtoken["value"] = mb_substr($parsedHTMLtoken["value"], 1, mb_strlen($parsedHTMLtoken["value"], $e), $e);
		}
		if($nextChr != "") {
			$parsedHTMLtoken["value"] = mb_substr($parsedHTMLtoken["value"], 0, mb_strlen($parsedHTMLtoken["value"], $e)-1, $e);
		}

		return $parsedHTMLtoken;

	}



	//expecting parsedHTML token of type text
	function dash_spacing($parsedHTMLtoken) {
		if(!isset($this->settings["dashSpacing"]) || !$this->settings["dashSpacing"]) return $parsedHTMLtoken;
	    $parsedHTMLtoken["value"] = preg_replace(
			"/
				(?:
					\s
					(".$this->chr['emDash'].")
					\s
				)
				|
				(?:
					(?<=\S)							# lookbehind assertion
					(".$this->chr['emDash'].")
					(?=\S)							# lookahead assertion
				)
			/xu", 
			$this->chr['thinSpace'].'$1$2'.$this->chr['thinSpace'], 
			$parsedHTMLtoken["value"]
			);

	    $parsedHTMLtoken["value"] = preg_replace(
			"/
				(?:
					\s
					(".$this->chr['enDash'].")
					\s
				)
				|
				(?:
					(?<=\S)							# lookbehind assertion
					(".$this->chr['enDash'].")
					(?=\S)							# lookahead assertion
				)
			/xu", 
			$this->chr['thinSpace'].'$1$2'.$this->chr['thinSpace'], 
			$parsedHTMLtoken["value"]
			);
			
		return $parsedHTMLtoken;
	}



	//expecting parsedHTML token of type text
	function space_collapse($parsedHTMLtoken) {
		if(!isset($this->settings["spaceCollapse"]) || !$this->settings["spaceCollapse"]) return $parsedHTMLtoken;


		# find the HTML character representation for the following characters:
		#		tab | line feed | carriage return | space | non-breaking space | ethiopic wordspace
		#		ogham space mark | en quad space | em quad space | en-space | three-per-em space
		#		four-per-em space | six-per-em space | figure space | punctuation space | em-space
		#		thin space | hair space | narrow no-break space
		#		medium mathematical space | ideographic space
		# Some characters are used inside words, we will not count these as a space for the purpose
		# of finding word boundaries:
		#		zero-width-space ("&#8203;", "&#x200b;")
		#		zero-width-joiner ("&#8204;", "&#x200c;", "&zwj;")
		#		zero-width-non-joiner ("&#8205;", "&#x200d;", "&zwnj;")

		$htmlSpaces = '
			\x{00a0}		# no-break space
			|
			\x{1361}		# ethiopic wordspace
			|
			\x{2000}		# en quad-space
			|
			\x{2001}		# em quad-space
			|
			\x{2002}		# en space
			|
			\x{2003}		# em space
			|
			\x{2004}		# three-per-em space
			|
			\x{2005}		# four-per-em space
			|
			\x{2006}		# six-per-em space
			|
			\x{2007}		# figure space
			|
			\x{2008}		# punctuation space
			|
			\x{2009}		# thin space
			|
			\x{200a}		# hair space
			|
			\x{200b}		# zero-width space
			|
			\x{200c}		# zero-width joiner
			|
			\x{200d}		# zero-width non-joiner
			|
			\x{202f}		# narrow no-break space
			|
			\x{205f}		# medium mathematical space
			|
			\x{3000}		# ideographic space
			'; // required modifiers: x (multiline pattern) i (case insensitive) u (utf8)

		// normal spacing
	    $parsedHTMLtoken["value"] = preg_replace(
			"/\s+/xu", 
			" ", 
			$parsedHTMLtoken["value"]
			);
		
		// nbsp get's priority.  if nbsp exists in a string of spaces, it collapses to nbsp
	    $parsedHTMLtoken["value"] = preg_replace(
			"/(?:\s|$htmlSpaces)*".$this->chr["noBreakSpace"]."(?:\s|$htmlSpaces)*/xu", 
			$this->chr["noBreakSpace"], 
			$parsedHTMLtoken["value"]
			);
		
		// for any other spaceing, replace with the first occurance of an unusual space character
	    $parsedHTMLtoken["value"] = preg_replace(
			"/(?:\s)*($htmlSpaces)(?:\s|$htmlSpaces)*/xu", 
			"$1", 
			$parsedHTMLtoken["value"]
			);

		// remove all spacing at beginning of block level elements
		if(!isset($parsedHTMLtoken["prevChr"]) || $parsedHTMLtoken["prevChr"] == NULL) { // we have the first text in a block level element
	    $parsedHTMLtoken["value"] = preg_replace(
			"/\A(?:\s|$htmlSpaces)+/xu", 
			"", 
			$parsedHTMLtoken["value"]
			);
		}
/**/
		
		return $parsedHTMLtoken;
	}




	//expecting parsedHTML token of type text
	function unit_spacing($parsedHTMLtoken) {
		if(!isset($this->settings["unitSpacing"]) || !$this->settings["unitSpacing"]) return $parsedHTMLtoken;
		
		$units = array();
		if(isset($this->settings["units"])) {
			foreach($this->settings["units"] as $unit) {
				$units[] = preg_replace("#([\[\\\^\$\.\|\?\*\+\(\)\{\}])#", "\\\\$1", $unit ); // escape special chrs
			}
		}
		
		$customUnits = implode("|", $units);
		$customUnits .= ($customUnits) ? "|" : "" ;
		$unitPattern = $customUnits.'

			### Temporal units
			(?:ms|s|secs?|mins?|hrs?)\.?|
			milliseconds?|seconds?|minutes?|hours?|days?|years?|decades?|century|centuries|millennium|millennia|

			### Imperial units
			(?:in|ft|yd|mi)\.?|
			(?:ac|ha|oz|pt|qt|gal|lb|st)\.?
			s\.f\.|sf|s\.i\.|si|square[ ]feet|square[ ]foot|
			inch|inches|foot|feet|yards?|miles?|acres?|hectares?|ounces?|pints?|quarts?|gallons?|pounds?|stones?|

			### Metric units (with prefixes)
			(?:p|µ|[mcdhkMGT])?
			(?:[mgstAKNJWCVFSTHBL]|mol|cd|rad|Hz|Pa|Wb|lm|lx|Bq|Gy|Sv|kat|Ω|Ohm|&Omega;|&\#0*937;|&\#[xX]0*3[Aa]9;)|
			(?:nano|micro|milli|centi|deci|deka|hecto|kilo|mega|giga|tera)?
			(?:liters?|meters?|grams?|newtons?|pascals?|watts?|joules?|amperes?)|

			### Computers units (KB, Kb, TB, Kbps)
			[kKMGT]?(?:[oBb]|[oBb]ps|flops)|

			### Money
			¢|M?(?:£|¥|€|$)|

			### Other units
			°[CF]? | 
			%|pi|M?px|em|en|[NSEOW]|[NS][EOW]|mbar

		'; // required modifiers: x (multiline pattern)

		$parsedHTMLtoken["value"] = preg_replace("/(\d\.?)\s($unitPattern)\b/x", '$1'.$this->chr["noBreakNarrowSpace"].'$2', $parsedHTMLtoken["value"]);
		return $parsedHTMLtoken;
	}

	//expecting parsedHTML token of type text
	function wrap_hard_hyphens($parsedTextTokens) {
		if((isset($this->settings["hyphenHardWrap"]) && $this->settings["hyphenHardWrap"]) || (isset($this->settings["smartDashes"]) && $this->settings["smartDashes"])) {
			foreach($parsedTextTokens as &$parsedTextToken) {
				if(isset($this->settings["hyphenHardWrap"]) && $this->settings["hyphenHardWrap"]) {
					$hyphens = array('-',$this->chr["hyphen"]);
					$parsedTextToken["value"] = str_replace($hyphens, "-".$this->chr["zeroWidthSpace"], $parsedTextToken["value"]);
					$parsedTextToken["value"] = str_replace("_", "_".$this->chr["zeroWidthSpace"], $parsedTextToken["value"]);
					$parsedTextToken["value"] = str_replace("/", "/".$this->chr["zeroWidthSpace"], $parsedTextToken["value"]);
				}
				if(isset($this->settings["smartDashes"]) && $this->settings["smartDashes"]) // handled here because we need to know we are inside a word and not a url
					$parsedTextToken["value"] = str_replace("-", $this->chr["hyphen"], $parsedTextToken["value"]);
			}
		}		
		return $parsedTextTokens;
	}
	
	//expecting parsedHTML token of type text
	function dewidow($parsedHTMLtoken) {
		// intervening inline tags may interfere with widow identification, but that is a sacrifice of using the parser
		// intervening tags will only interfere if they separate the widow from previous or preceding whitespace
		if(!isset($this->settings["dewidow"]) || !$this->settings["dewidow"]) return $parsedHTMLtoken;
		if(!isset($parsedHTMLtoken["nextChr"])) { // we have the last type "text" child of a block level element
			$encodings = array("ASCII","UTF-8", "ISO-8859-1");
			$encoding = mb_detect_encoding($parsedHTMLtoken["value"]."a", $encodings); // ."a" is a hack; see http://www.php.net/manual/en/function.mb-detect-encoding.php#81936
			$u = '';

			if("UTF-8" == $encoding) {
				$u = "u";
				if(!function_exists('mb_strlen')) return $parsedHTMLtoken;
			} elseif("ASCII" != $encoding) {
				return $parsedHTMLtoken;
			}

			$widowPattern = "/
				(?:
					\A
					|
					(?:
						(																#subpattern 1: space before
							[\s".$this->chr["zeroWidthSpace"].$this->chr["softHyphen"]."]+
						)
						(																#subpattern 2: neighbors widow (short as possible)
							[^\s".$this->chr["zeroWidthSpace"].$this->chr["softHyphen"]."]+
						)
					)
				)
				(																		#subpattern 3: space between
					[\s".$this->chr["noBreakSpace"]."]+
				)
				(																		#subpattern 4: widow
					[^\s".$this->chr["zeroWidthSpace"]."]+?
				)
				(																		#subpattern 5: any trailing punctuation or spaces
					[^\w]*
				)
				\Z
			/x$u";

			$parsedHTMLtoken["value"] = preg_replace_callback(
				$widowPattern,
				array($this, '_dewidow_callback'),
				$parsedHTMLtoken["value"]
				);
			
		}
		return $parsedHTMLtoken;
	}
	

	function _dewidow_callback($widow) {
		if(!isset($this->settings["dewidowMaxPull"]) || !$this->settings["dewidowMaxPull"] || !isset($this->settings["dewidowMaxLength"]) || !$this->settings["dewidowMaxLength"]) return $widow[0];
		
		$encodings = array("ASCII","UTF-8", "ISO-8859-1");
		$multibyte = FALSE;
		$encoding = mb_detect_encoding($widow[0]."a", $encodings); // ."a" is a hack; see http://www.php.net/manual/en/function.mb-detect-encoding.php#81936
		if("UTF-8" == $encoding) $multibyte = TRUE;

		// if we are here, we know that widows are being protected in some fashion
		//   with that, we will assert that widows should never be hyphenated or wrapped
		//   as such, we will strip soft hyphens and zero-width-spaces
		$widow[4] = str_replace($this->chr["zeroWidthSpace"], "", $widow[4]);
		$widow[4] = str_replace($this->chr["softHyphen"], "", $widow[4]);
						
//		$widow[5] = preg_replace("/\s+/", $this->chr["noBreakSpace"], $widow[5]);
		$widow[5] = mb_ereg_replace("/\s+/", $this->chr["noBreakSpace"], $widow[5], "p");; // fixes multibyte unicode corruption that occurs in some instances in the line above.
		
		$widow[5] = str_replace($this->chr["zeroWidthSpace"], "", $widow[5]);
		$widow[5] = str_replace($this->chr["softHyphen"], "", $widow[5]);
		
		// eject if widows neighbor is proceeded by a no break space (the pulled text would be too long)
		if($widow[1] == "" || strstr($this->chr["noBreakSpace"], $widow[1])) return $widow[1].$widow[2].$widow[3].$widow[4].$widow[5];
		
		if($multibyte) {
			// eject if widows neighbor length exceeds the max allowed or widow length exceeds max allowed
			if(
				($widow[2] != "" && mb_strlen($widow[2]) > $this->settings["dewidowMaxPull"])
				||
				mb_strlen($widow[4]) > $this->settings["dewidowMaxLength"]
				)
					return $widow[1].$widow[2].$widow[3].$widow[4].$widow[5];
		} else {
			// single byte version of previous
			if(
				($widow[2] != "" && strlen($widow[2]) > $this->settings["dewidowMaxPull"])
				||
				strlen($widow[4]) > $this->settings["dewidowMaxLength"]
				)
					return $widow[1].$widow[2].$widow[3].$widow[4].$widow[5];
		}
		
		// lets protect some widows!
		return $widow[1].$widow[2].$this->chr["noBreakSpace"].$widow[4].$widow[5];
	}


	// expecting parsedText tokens
	function wrap_urls($parsedTextTokens) {
		if(!isset($this->settings["urlWrap"]) || !$this->settings["urlWrap"] || !isset($this->settings["urlMinAfterWrap"]) || !$this->settings["urlMinAfterWrap"]) return $parsedTextTokens;


		// test for and parse urls 
		$validTLD = 'ac|ad|aero|ae|af|ag|ai|al|am|an|ao|aq|arpa|ar|asia|as|at|au|aw|ax|az|ba|bb|bd|be|bf|bg|bh|biz|bi|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|cat|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|com|coop|co|cr|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gg|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|im|info|int|in|io|iq|ir|is|it|je|jm|jobs|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|me|mg|mh|mil|mk|ml|mm|mn|mobi|mo|mp|mq|mr|ms|mt|museum|mu|mv|mw|mx|my|mz|name|na|nc|net|ne|nf|ng|ni|nl|no|np|nr|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pro|pr|ps|pt|pw|py|qa|re|ro|rs|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tel|tf|tg|th|tj|tk|tl|tm|tn|to|tp|travel|tr|tt|tv|tw|tz|ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw';
		$urlScheme = '(?:https?|ftps?|file|nfs|feed|itms|itpc)';
		$urlPattern = "(?:
			\A
			($urlScheme:\/\/)?									# Subpattern 1: contains _http://_ if it exists
			(													# Subpattern 2: contains subdomains.domain.tld
				(?:
					[a-z0-9]									# first chr of (sub)domain can not be a hyphen
					[a-z0-9\-]{0,61}							# middle chrs of (sub)domain may be a hyphen;
																# limit qty of middle chrs so total domain does not exceed 63 chrs
					[a-z0-9]									# last chr of (sub)domain can not be a hyphen
					\.											# dot separator
				)+
				(?:
					$validTLD									# validates top level domain
				)
				(?:												# optional port numbers
					:
					(?:
						[1-5]?[0-9]{1,4} | 6[0-4][0-9]{3} | 65[0-4][0-9]{2} | 655[0-2][0-9] | 6553[0-5]
					)
				)?
			)
			(													# Subpattern 3: contains path following domain
				(?:
					\/											# marks nested directory
					[a-z0-9\"\$\-_\.\+!\*\'\(\),;\?:@=&\#]+		# valid characters within directory structure
				)*
				[\/]?											# trailing slash if any
			)
			\Z
		)"; // required modifiers: x (multiline pattern) i (case insensitive)
		
		foreach($parsedTextTokens as &$parsedTextToken) {
			if(preg_match("`$urlPattern`xi", $parsedTextToken["value"], $urlMatch)) {
				// $urlMatch[1] holds "http://"
				// $urlMatch[2] holds "subdomains.domain.tld"
				// $urlMatch[3] holds the path after the domain
	
				$http = ($urlMatch[1]) ? $urlMatch[1].$this->chr["zeroWidthSpace"] : "" ;

				$domainParts = preg_split('#(\-|\.)#', $urlMatch[2], -1, PREG_SPLIT_DELIM_CAPTURE);

				//this is a hack, but it works
				// first, we hyphenate each part
				// we need it formated like a group of words
				$parsedWordsLike = array();
				foreach($domainParts as $key => $domainPart) {
					$parsedWordsLike[$key]["value"] = $domainPart;
				}
	
				// do the hyphenation
				$parsedWordsLike = $this->do_hyphenate($parsedWordsLike);

				// restore format
				foreach($parsedWordsLike as $key => $parsedWordLike) {
					$domainParts[$key] = $parsedWordLike["value"];
				}
				foreach ($domainParts as $key => &$domainPart) {
					//then we swap out each soft-hyphen" with a zero-space
					$domainPart = str_replace($this->chr["softHyphen"], $this->chr["zeroWidthSpace"], $domainPart);
				
					//we also insert zero-spaces before periods and hyphens
					if($key > 0 && strlen($domainPart) == 1) {
						$domainPart = $this->chr["zeroWidthSpace"].$domainPart;
					}
				}

				//lastly let's recombine
				$domain = implode($domainParts);
	
				//break up the URL path to individual characters
				$pathParts = str_split($urlMatch[3], 1);
				$pathCount = count($pathParts);
				$path = "";
				for($i = 0; $i < $pathCount; $i++) {
					$path .= (0 == $i || $pathCount - $i < $this->settings["urlMinAfterWrap"]) ? $pathParts[$i] : $this->chr["zeroWidthSpace"].$pathParts[$i];
				}
	
				$parsedTextToken["value"] = $http.$domain.$path;
			}
		}
		
		return $parsedTextTokens;
	}
	
	// expecting parsedText tokens
	function wrap_emails($parsedTextTokens) {
		if(!isset($this->settings["emailWrap"]) || !$this->settings["emailWrap"]) return $parsedTextTokens;
		// test for and parse urls 
		$validTLD = 'ac|ad|aero|ae|af|ag|ai|al|am|an|ao|aq|arpa|ar|asia|as|at|au|aw|ax|az|ba|bb|bd|be|bf|bg|bh|biz|bi|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|cat|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|com|coop|co|cr|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gg|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|im|info|int|in|io|iq|ir|is|it|je|jm|jobs|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|me|mg|mh|mil|mk|ml|mm|mn|mobi|mo|mp|mq|mr|ms|mt|museum|mu|mv|mw|mx|my|mz|name|na|nc|net|ne|nf|ng|ni|nl|no|np|nr|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pro|pr|ps|pt|pw|py|qa|re|ro|rs|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tel|tf|tg|th|tj|tk|tl|tm|tn|to|tp|travel|tr|tt|tv|tw|tz|ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw';
		$emailPattern = "(?:
			\A
			[a-z0-9\!\#\$\%\&\'\*\+\/\=\?\^\_\`\{\|\}\~\-]+
			(?:
				\.
				[a-z0-9\!\#\$\%\&\'\*\+\/\=\?\^\_\`\{\|\}\~\-]+
			)*
				@
			(?:
				[a-z0-9]
				[a-z0-9\-]{0,61}
				[a-z0-9]
				\.
			)+
			(?:
				$validTLD
			)
			\Z
		)"; // required modifiers: x (multiline pattern) i (case insensitive)
		
		foreach($parsedTextTokens as &$parsedTextToken) {
			if(preg_match("/$emailPattern/xi", $parsedTextToken["value"], $urlMatch)) {
				$parsedTextToken["value"] = preg_replace("/([^a-zA-Z])/", '$1'.$this->chr["zeroWidthSpace"], $parsedTextToken["value"]);
			}
		}
		return $parsedTextTokens;
	}

	// expecting parsedHTML token of type text
	// wraps words of all caps (may include numbers) in <span class="caps">
	// only call if you are certain that no html tags have been injected containing capital letters
	// call before style_numbers
	function style_caps($parsedHTMLtoken) {
		if(!isset($this->settings["styleCaps"]) || !$this->settings["styleCaps"]) return $parsedHTMLtoken;
		
		// \p{Lu} equals upper case letters and should match non english characters; since PHP 4.4.0 and 5.1.0
		// for more info, see http://www.regextester.com/pregsyntax.html#regexp.reference.unicode
		$pattern = '
				(?<![\w\-_'.$this->chr["zeroWidthSpace"].$this->chr["softHyphen"].'])
												# negative lookbehind assertion
				(
					(?:							# CASE 1: " 9A "
						[0-9]+					# starts with at least one number
						\p{Lu}					# must contain at least one capital letter
						(?:\p{Lu}|[0-9]|\-|_|'.$this->chr["zeroWidthSpace"].'|'.$this->chr["softHyphen"].')*
												# may be followed by any number of numbers capital letters, hyphens, underscores, zero width spaces, or soft hyphens
					)
					|
					(?:							# CASE 2: " A9 "
						\p{Lu}					# starts with capital letter
						(?:\p{Lu}|[0-9])		# must be followed a number or capital letter
						(?:\p{Lu}|[0-9]|\-|_|'.$this->chr["zeroWidthSpace"].'|'.$this->chr["softHyphen"].')*
												# may be followed by any number of numbers capital letters, hyphens, underscores, zero width spaces, or soft hyphens

					)
				)
				(?![\w\-_'.$this->chr["zeroWidthSpace"].$this->chr["softHyphen"].'])
							# negative lookahead assertion
			'; // required modifiers: x (multiline pattern) u (utf8)
		
		$parsedHTMLtoken["value"] = preg_replace("/$pattern/xu", '<span class="caps">$1</span>', $parsedHTMLtoken["value"]);
		
		return $parsedHTMLtoken;
	}
	
	// expecting parsedHTML token of type text
	// wraps numbers in <span class="numbers"> (even numbers that appear inside a word, i.e. A9 becomes A<span class="numbers">9</span>)
	// call after style_caps so A9 becomes <span class="caps">A<span class="numbers">9</span></span>)
	// only call if you are certain that no html tags have been injected containing numbers
	// call after smart_fractions, smart_ordinal_suffix and style_caps
	function style_numbers($parsedHTMLtoken) {
		if(!isset($this->settings["styleNumbers"]) || !$this->settings["styleNumbers"]) return $parsedHTMLtoken;

		$pattern = '([0-9]+)'; // required modifier: u (utf8)
		$parsedHTMLtoken["value"] = preg_replace("/$pattern/u", '<span class="numbers">$1</span>', $parsedHTMLtoken["value"]);
		
		return $parsedHTMLtoken;
	}
	
	// expecting parsedHTML token of type text
	// wraps ampersands in <span class="amp"> (i.e. H&amp;J becomes H<span class="amp">&amp;</span>J)
	// call after style_caps so H&amp;J becomes <span class="caps">H<span class="amp">&amp;</span>J</span>)
	// note that all standalone ampersands were previously converted to &amp;
	// only call if you are certain that no html tags have been injected containing "&amp;"
	function style_ampersands($parsedHTMLtoken) {
		if(!isset($this->settings["styleAmpersands"]) || !$this->settings["styleAmpersands"]) return $parsedHTMLtoken;

		$pattern = '(\&amp\;)'; // required modifier: u (utf8)
		$parsedHTMLtoken["value"] = preg_replace("/$pattern/u", '<span class="amp">$1</span>', $parsedHTMLtoken["value"]);
		
		return $parsedHTMLtoken;
	}
	
	// expecting parsedHTML token of type text
	// styles initial quotes and guillemets
	function style_initial_quotes($parsedHTMLtoken, $isTitle = FALSE) {
		if(!isset($this->settings["styleInitialQuotes"]) || !$this->settings["styleInitialQuotes"] || !isset($this->settings["initialQuoteTags"]) || !$this->settings["initialQuoteTags"]) return $parsedHTMLtoken;
	
		if(!isset($parsedHTMLtoken["prevChr"]) || $parsedHTMLtoken["prevChr"] == NULL) { // we have the first text in a block level element

			$encodings = array("ASCII","UTF-8", "ISO-8859-1");
			$e = mb_detect_encoding($parsedHTMLtoken["value"]."a", $encodings);// ."a" is a hack; see http://www.php.net/manual/en/function.mb-detect-encoding.php#81936
			if(!isset($e) || $e == "") $e = "ASCII";

			$firstChr = mb_substr($parsedHTMLtoken["value"], 0, 1, $e);
			if($firstChr == "'" || $firstChr == $this->chr["singleQuoteOpen"] || $firstChr == $this->chr["singleLow9Quote"] || $firstChr == "," || $firstChr == "\"" || $firstChr == $this->chr["doubleQuoteOpen"] || $firstChr == $this->chr["guillemetOpen"] || $firstChr == $this->chr["guillemetClose"] || $firstChr == $this->chr["doubleLow9Quote"]) {

				$style = FALSE;
				$immediateParent = "";
				if($parsedHTMLtoken["parents"]) {
					$immediateParent = end($parsedHTMLtoken["parents"]);
				} elseif($isTitle) {
					// assume page title is h2
					$immediateParent = array("tagName" => "h2");
				}
				if($immediateParent["tagName"]) {
					foreach($this->settings["initialQuoteTags"] as $tag) {
						if($tag == $immediateParent["tagName"])
							$style = TRUE;
					}
				}
				
				if($style) {
					if($firstChr == "'" || $firstChr == $this->chr["singleQuoteOpen"] || $firstChr == $this->chr["singleLow9Quote"] || $firstChr == ",") {
						$parsedHTMLtoken["value"] = '<span class="quo">'.$firstChr.'</span>'.mb_substr($parsedHTMLtoken["value"], 1, mb_strlen($parsedHTMLtoken["value"], $e), $e);
					} else { // double quotes or guillemets
						$parsedHTMLtoken["value"] = '<span class="dquo">'.$firstChr.'</span>'.mb_substr($parsedHTMLtoken["value"], 1, mb_strlen($parsedHTMLtoken["value"], $e), $e);
					}
				}
			}
		}

		return $parsedHTMLtoken;
	}
	

	//injects the PatGen segments pattern into the PatGen words pattern
	function hyphenation_pattern_injection($wordPattern, $segmentPattern, $segmentPosition, $segmentLength) {
	
		for($numberPosition=$segmentPosition; $numberPosition <= $segmentPosition + $segmentLength; $numberPosition++) {
			$wordPattern[$numberPosition] = 
				(intval($wordPattern[$numberPosition]) >= intval($segmentPattern[$numberPosition-$segmentPosition])) ?
				$wordPattern[$numberPosition] :
				$segmentPattern[$numberPosition-$segmentPosition];
		}
		return $wordPattern;
	}
	
	// expecting parseText tokens filtered to words
	function hyphenate($parsedTextTokens, $isTitle = FALSE) {
		if(!isset($this->settings["hyphenation"]) || !$this->settings["hyphenation"]) return $parsedTextTokens;

		$isHeading = FALSE;
		if(isset($parsedTextTokens["parents"])) {
			foreach($parsedTextTokens["parents"] as $tagName) {
				if($tagName == "h1" || $tagName == "h2" || $tagName == "h3" || $tagName == "h4" || $tagName == "h5" || $tagName == "h6") $isHeading = TRUE;
			}
		}
		if((!isset($this->settings["hyphenateTitle"]) || !$this->settings["hyphenateTitle"]) && ($isTitle || $isHeading)) return $parsedTextTokens;

		// call functionality as seperate function so it can be run without test for setting["hyphenation"] - such as with url wrapping
		return $this->do_hyphenate($parsedTextTokens);
	}	
	// expecting parsedText tokens filtered to words
	function do_hyphenate($parsedTextTokens) {
		if(!isset($this->settings["hyphenMinLength"]) || !$this->settings["hyphenMinLength"]) return $parsedTextTokens;				
		if(!isset($this->settings["hyphenMinBefore"]) || !$this->settings["hyphenMinBefore"]) return $parsedTextTokens;				
		if(!isset($this->settings["hyphenationPatternMaxSegment"])) return $parsedTextTokens;				
		if(!isset($this->settings["hyphenationPatternExceptions"])) return $parsedTextTokens;				
		if(!isset($this->settings["hyphenationPattern"])) return $parsedTextTokens;				
		
		$encodings = array("ASCII","UTF-8", "ISO-8859-1");
		$multibyte = FALSE;
		$u = "";
		// make sure we have full exceptions list
		if(!isset($this->settings["hyphenationExceptions"])) {
			if($this->settings["hyphenationPatternExceptions"] || (isset($this->settings["hyphenationCustomExceptions"]) && $this->settings["hyphenationCustomExceptions"])) {
				$exceptions = array();
				if(isset($this->settings["hyphenationCustomExceptions"])) {
					// merges custom and language specific word hyphenations
					$exceptions = array_merge($this->settings["hyphenationCustomExceptions"], $this->settings["hyphenationPatternExceptions"]);
				} else {
					$exceptions = $this->settings["hyphenationPatternExceptions"];
				}
				
				$this->settings["hyphenationExceptions"] = $exceptions;
			} else {
				$this->settings["hyphenationExceptions"]=array();
			}
		}
		foreach($parsedTextTokens as &$parsedTextToken) {
			// ."a" is a hack; see http://www.php.net/manual/en/function.mb-detect-encoding.php#81936
			$encoding = mb_detect_encoding($parsedTextToken["value"]."a", $encodings);

			if("UTF-8" == $encoding) {
				$multibyte = TRUE;
				$u = "u";
				if(!function_exists('mb_strlen')) continue;
			} elseif("ASCII" != $encoding) {
				continue;
			}

			if($multibyte) {
				$wordLength = mb_strlen($parsedTextToken["value"], "UTF-8");
				$theKey = mb_strtolower($parsedTextToken["value"], "UTF-8");
			} else {  //same as above without mutlibyte string functions to improve preformance
				$wordLength = strlen($parsedTextToken["value"]);
				$theKey = strtolower($parsedTextToken["value"]);
			}
		
			if($wordLength < $this->settings["hyphenMinLength"]) continue;

			//if this is a capitalized word, and settings do not allow hyphenation of such, abort!
			// note. this is different than uppercase words, where we are looking for title case
			if((!isset($this->settings["hyphenateTitleCase"]) || !$this->settings["hyphenateTitleCase"]) && substr($theKey,0,1) != substr($parsedTextToken["value"],0,1)) continue;
			
			// give exceptions preference
			if(isset($this->settings["hyphenationExceptions"][$theKey])) {
				//Set the wordPattern - this method keeps any contextually important capitalization
				if($multibyte) {			
					$lowercaseHyphenedWord = $this->settings["hyphenationExceptions"][$theKey];
					$lhwArray = $this->mb_str_split($lowercaseHyphenedWord, 1, "UTF-8");
					$lhwLength = mb_strlen($lowercaseHyphenedWord, "UTF-8");
				} else {  //same as above without mutlibyte string functions to improve preformance
					$lowercaseHyphenedWord = $this->settings["hyphenationExceptions"][$theKey];
					$lhwArray = str_split($lowercaseHyphenedWord, 1);
					$lhwLength = strlen($lowercaseHyphenedWord);
				}
			
				$wordPattern=array();
				for($i=0; $i < $lhwLength; $i++) {
					if("-" == $lhwArray[$i]) {
						array_push($wordPattern, "9");
						$i++;
					} else {
						array_push($wordPattern, "0");
					}
				}
				array_push($wordPattern, "0"); //for consistent length with the other word patterns
			}
			if(!isset($wordPattern)) {
				// first we set up the matching pattern to be a series of zeros one character longer than $parsedTextToken
				$wordPattern = array();
				for($i=0; $i < $wordLength +1; $i++) {
					array_push($wordPattern, "0");
				}
				// we grab all possible segments from $parsedTextToken of length 2 through $this->settings["hyphenationPatternMaxSegment"]
				for($segmentLength=2; ($segmentLength <= $wordLength) && ($segmentLength <= $this->settings["hyphenationPatternMaxSegment"]); $segmentLength++) {
					for($segmentPosition=0; $segmentPosition + $segmentLength <= $wordLength; $segmentPosition++) {
						if($multibyte)
							$segment = mb_strtolower(mb_substr($parsedTextToken["value"], $segmentPosition, $segmentLength, "UTF-8"), "UTF-8");
						else
							$segment = strtolower(substr($parsedTextToken["value"], $segmentPosition, $segmentLength));
						if(0 == $segmentPosition) {
							if(isset($this->settings["hyphenationPattern"]["begin"][$segment])) {
								if($multibyte)
									$segmentPattern = $this->mb_str_split($this->settings["hyphenationPattern"]["begin"][$segment], 1, "UTF-8");
								else
									$segmentPattern = str_split($this->settings["hyphenationPattern"]["begin"][$segment], 1);
								$wordPattern = $this->hyphenation_pattern_injection($wordPattern, $segmentPattern, $segmentPosition, $segmentLength);
							}
						}
						if($segmentPosition + $segmentLength == $wordLength) {
							if(isset($this->settings["hyphenationPattern"]["end"][$segment])) {
								if($multibyte)
									$segmentPattern = $this->mb_str_split($this->settings["hyphenationPattern"]["end"][$segment], 1, "UTF-8");
								else
									$segmentPattern = str_split($this->settings["hyphenationPattern"]["end"][$segment], 1);
								$wordPattern = $this->hyphenation_pattern_injection($wordPattern, $segmentPattern, $segmentPosition, $segmentLength);
							}
						}
						if(isset($this->settings["hyphenationPattern"]["all"][$segment])) {
							if($multibyte)
								$segmentPattern = $this->mb_str_split($this->settings["hyphenationPattern"]["all"][$segment], 1, "UTF-8");
							else
								$segmentPattern = str_split($this->settings["hyphenationPattern"]["all"][$segment], 1);
							$wordPattern = $this->hyphenation_pattern_injection($wordPattern, $segmentPattern, $segmentPosition, $segmentLength);
						}
					}
				}

			}
			//add soft-hyphen based on $wordPattern
			if($multibyte) {
				$wordArray = $this->mb_str_split($parsedTextToken["value"], 1, "UTF-8");
			} else {  //same as above without mutlibyte string functions to improve preformance
				$wordArray = str_split($parsedTextToken["value"], 1);
			}
			
			$hyphenatedWord = "";
			for($i=0; $i < $wordLength; $i++) {
				if(($this->is_odd(intval($wordPattern[$i]))) && ($i >= $this->settings["hyphenMinBefore"]) && ($i < $wordLength - $this->settings["hyphenMinAfter"])) {
					$hyphenatedWord .= $this->chr["softHyphen"].$wordArray[$i];
				} else {
					$hyphenatedWord .= $wordArray[$i];
				}
			}
	
			$parsedTextToken["value"] = $hyphenatedWord;
			unset($wordPattern);	
		}
		return $parsedTextTokens;
	}

	########################################################################
	#   params:		$codes = decimal value cooresponding to unicode character
	#   Returns:    unicode character
	function uchr ($codes) {
	    if (is_scalar($codes)) $codes= func_get_args();
	    $str= '';
	    foreach ($codes as $code) $str.= html_entity_decode('&#'.$code.';',ENT_NOQUOTES,'UTF-8');
	    return $str;
	}

	//is a number odd? returns 0 if even and 1 if odd
	function is_odd($number) {
		return $number % 2;
	}

	//multibyte character support is built in to accomodate language support of multibyte alphabets
	function mb_str_split($str, $length = 1, $encoding = 'UTF-8') {
	    if(!function_exists('mb_strlen')) return FALSE;
		if ($length < 1) return FALSE;
		$result = array();
		for ($i = 0; $i < mb_strlen($str, $encoding); $i += $length) {
			$result[] = mb_substr($str, $i, $length, $encoding);
		}
		return $result;
	}




##########################################################################################
##########################################################################################
##########################################################################################
###		
###		portions of this code have been inspired by:
###			-typogrify (http://code.google.com/p/typogrify/)
###			-WordPress code for wptexturize (http://xref.redalt.com/wptrunk/nav.htm?index.htm)
###			-PHP SmartyPants Typographer (http://michelf.com/projects/php-smartypants/)
###		
	
}