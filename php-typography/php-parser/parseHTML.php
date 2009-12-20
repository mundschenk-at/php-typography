<?php 

/*   
Project Name: PHP Parser
Project URI: http://kingdesk.com/projects/php-parser/
Author: Jeffrey D. King
Author URI: http://kingdesk.com/about/jeff/

	Copyright 2009, KINGdesk, LLC. Licensed under the GNU General Public License 2.0. If you use, modify and/or redistribute this software, you must leave the KINGdesk, LLC copyright information, the request for a link to http://kingdesk.com, and the web design services contact information unchanged. If you redistribute this software, or any derivative, it must be released under the GNU General Public License 2.0. This program is distributed without warranty (implied or otherwise) of suitability for any particular purpose. See the GNU General Public License for full license terms <http://creativecommons.org/licenses/GPL/2.0/>.

	WE DON'T WANT YOUR MONEY: NO TIPS NECESSARY!  If you enjoy this plugin, a link to http://kingdesk.com from your website would be appreciated.
	
	For web design services, please contact info@kingdesk.com.
*/


// first we define some constants
// Valid constant names
define("ALL_TAGS", 1);
define("OPENING_TAGS", 2);
define("CLOSING_TAGS", 3);
define("SELFCLOSING_TAGS", 4);
define("OPENING_AND_SELFCLOSING_TAGS", 5);
define("SELFCLOSING_AND_OPENING_TAGS", 5);
define("OPENING_AND_CLOSING_TAGS", 7);
define("CLOSING_AND_OPENING_TAGS", 7);
define("CLOSING_AND_SELFCLOSING_TAGS", 6);
define("SELFCLOSING_AND_CLOSING_TAGS", 6);

define("ALL_TOKENS", 1);
define("TEXT_TOKENS", 2);
define("TAG_TOKENS", 3);
define("COMMENT_TOKENS", 4);
define("CDATA_TOKENS", 5);
define("TEXT_AND_TAG_TOKENS", 6);
define("TAG_AND_TEXT_TOKENS", 6);
define("TEXT_AND_COMMENT_TOKENS", 7);
define("COMMENT_AND_TEXT_TOKENS", 7);
define("TEXT_AND_CDATA_TOKENS", 8);
define("CDATA_AND_TEXT_TOKENS", 8);
define("TAG_AND_COMMENT_TOKENS", 9);
define("COMMENT_AND_TAG_TOKENS", 9);
define("TAG_AND_CDATA_TOKENS", 10);
define("CDATA_AND_TAG_TOKENS", 10);
define("COMMENT_AND_CDATA_TOKENS", 11);
define("CDATA_AND_COMMENT_TOKENS", 11);
define("TEXT_TAG_AND_COMMENT_TOKENS", 12);
define("TEXT_COMMENT_AND_TAG_TOKENS", 12);
define("TAG_TEXT_AND_COMMENT_TOKENS", 12);
define("TAG_COMMENT_AND_TEXT_TOKENS", 12);
define("COMMENT_TAG_AND_TEXT_TOKENS", 12);
define("COMMENT_TEXT_AND_TAG_TOKENS", 12);
define("TEXT_TAG_AND_CDATA_TOKENS", 13);
define("TEXT_CDATA_AND_TAG_TOKENS", 13);
define("TAG_TEXT_AND_CDATA_TOKENS", 13);
define("TAG_CDATA_AND_TEXT_TOKENS", 13);
define("CDATA_TAG_AND_TEXT_TOKENS", 13);
define("CDATA_TEXT_AND_TAG_TOKENS", 13);
define("TEXT_COMMENT_AND_CDATA_TOKENS", 14);
define("TEXT_CDATA_AND_COMMENT_TOKENS", 14);
define("COMMENT_TEXT_AND_CDATA_TOKENS", 14);
define("COMMENT_CDATA_AND_TEXT_TOKENS", 14);
define("CDATA_COMMENT_AND_TEXT_TOKENS", 14);
define("CDATA_TEXT_AND_COMMENT_TOKENS", 14);
define("TAG_COMMENT_AND_CDATA_TOKENS", 15);
define("TAG_CDATA_AND_COMMENT_TOKENS", 15);
define("COMMENT_TAG_AND_CDATA_TOKENS", 15);
define("COMMENT_CDATA_AND_TAG_TOKENS", 15);
define("CDATA_COMMENT_AND_TAG_TOKENS", 15);
define("CDATA_TAG_AND_COMMENT_TOKENS", 15);


#########################################################################################################
#########################################################################################################
##
##	parsedXHTML assumes valid XHTML:
##		-every tag must be closed
##		-every attribute must have a value
##		-tag names and attributes are all lowercase
##
#########################################################################################################
#########################################################################################################
class parseHTML {

	var $blockTags = array("address", "article", "aside", "blockquote", "center", "dd", "dialog", "dir", "div", "dl", "dt", "fieldset", "figure", "footer", "form", "frameset", "h1", "h2", "h3", "h4", "h5", "h6", "header", "hgroup", "isindex", "li", "menu", "nav", "noframes", "noscript", "ol", "p", "pre", "section", "table", "tbody", "td", "tfoot", "th", "thead", "tr", "ul");
	var $html = array();
			/*
		$html is an ARRAY with the following structure:
		index	=> ARRAY: tokenized XHTML
				"type" 		=> STRING: REQUIRED; "comment" | "dtd" | "cdata" | "xml" | "tag" | "text"
				"value"		=> STRING: REQUIRED; token content
				"name"		=> STRING: REQUIRED for type "tag"; element name
				"openPos"	=> INTEGER: REQUIRED for closing tags (including self-closing); integer corresponding to the index of the opening tag
								// if a closing tag is missing an opening match, it will be treated as self-closing
				"closePos"	=> INTEGER: REQUIRED for opening and self-closing tags; integer corresponding to the index of the closing tag
								// if an opening tag is missing a closing match, it will be treated as closed by its parent's closing tag (or end of string)
				"attribute"	=> ARRAY: REQUIRED if "tag" has assigned attributes; attribute_names => values
				"parents"	=> ARRAY: REQUIRED if "tag" has parent tag(s); parent tags: "index" => array("tagName" => tagName, "attributes" => array(name => value, ... ))
				"locked"	=> BOOLEAN: OPTIONAL; TRUE by default for all types.  It is never set to FALSE, it is just unset.
				"ERROR"		=> STRING: error message (i.e. improperly nested tag...)
				"prevChr"	=> CHARACTER: REQUIRED for type "text" if previous character exists; last character of previous "text" if separated by inline tags or HTML comments
				"nextChr"	=> CHARACTER: REQUIRED for type "text" if next character exists; first character of next "text" if only separated by inline tags or HTML comments
			*/
	
	
	#=======================================================================
	#=======================================================================
	#==	METHODS
	#=======================================================================
	#=======================================================================
	
	
	########################################################################
	#	( UN | RE )LOAD, UPDATE AND CLEAR METHODS
	#
	# 

	#	Params:		STRING containing HTML markup.
	#	Action:		Tokenized $rawHTML saved to $this->html
	#	Returns:	TRUE on completion
	function load($rawHTML) {
		
		$this->clear();
		
		$tokens = array();
		$index = 0;
		$nestedTags = array(); // stores $index => "unclosed tag name"

		# find HTML comments
		$commentTag = '(?:<!(?:--.*?--\s*)+>)'; // required modifier: s (DotAll)
	
		# find Document Type Definition		
		$dtdTag = '(?:<![-a-zA-Z0-9:]+\b(?:.*?(?:--.*?--\s*)?)*>)'; // required modifier: s (DotAll)
	
		# find (Unparsed) Character Data
		$cdataTag = '(?:<\[CDATA\[.*?\]\]>)'; // required modifier: s (DotAll)
	
		# find XML Declaration
		$xmlTag = '(?:<\?xml\s.*?\?>)'; // required modifier: s (DotAll)
	
		# find XHTML Tags
		$htmlTag = '(?:</?[-a-zA-Z0-9:]+\b(?>[^"\'>]+|"[^"]*"|\'[^\']*\')*>)'; // required modifier: s (DotAll)

		# find XHTML Tags with ability to grab tag name and test for closing tags
		$htmlTagDetail = '
				<							# open of HTML element
				(/)?						# Subpattern 1: test for closing tag
				([-a-zA-Z0-9:]+)			# Subpattern 2: tag name			
				(?:
					[^\'">]+				# matches any attribute names
					|
					"[^"]*"					# double quoted attribute value
					|
					\'[^\']*\'				# single quoted attribute value
				)*
				((?<=/)>)?					# Subpattern 3: test for self-closing tag
			'; //required modifiers: x
	
		# find attribute/value pairs in HTML tags
		$attributePattern= '
				\s+							# one or more spaces
				([-a-zA-Z0-9:]+)			# Subpattern 1: attributeibute name
				\s*=\s*
				(?: 
					"([^"]+)"				# Subpattern 2: possibly attribute value
					|
					\'([^\']+)\'			# Subpattern 3: possibly attribute value
				)
			'; //required modifiers: x

		# find Find any tag
		$anyTag = "$commentTag|$dtdTag|$cdataTag|$xmlTag|$htmlTag"; // required modifiers: x (multiline pattern) s (DotAll)

		$parts = preg_split("@($anyTag)@s", $rawHTML, -1, PREG_SPLIT_DELIM_CAPTURE);

		// we will use "prevChr" and "nextChr" to give context to type "text"
		// "prevChr" is not relevant to the first child of type "text" in a block level HTML element
		// "nextChr" is not relevant to the last child of type "text" in a block level HTML element
		// we will use $prevTextIndex to help us properly assign "prevChr" and "nextChr"
		$prevTextIndex = NULL;
$i = 0;		
		foreach ($parts as $part) {
			if ($part != "") {
		
				if(preg_match("@\A$commentTag\Z@s", $part)) {
					$tokens[$index] = array(
									"type"		=> 'comment',
									"value"		=> $part,
									"locked"	=> TRUE,
									);

					// remember parents
					if(!empty($nestedTags))
						$tokens[$index]["parents"] = $nestedTags;
				} elseif(preg_match("@\A$dtdTag\Z@s", $part)) {
					$tokens[$index] = array(
									"type"		=> 'dtd',
									"value"		=> $part,
									"locked"	=> TRUE,
									);

					// remember parents
					if(!empty($nestedTags))
						$tokens[$index]["parents"] = $nestedTags;
				} elseif(preg_match("@\A$cdataTag\Z@s", $part)) {
					$tokens[$index] = array(
									"type"		=> 'cdata',
									"value"		=> $part,
									"locked"	=> TRUE,
									);

					// remember parents
					if(!empty($nestedTags))
						$tokens[$index]["parents"] = $nestedTags;
				} elseif(preg_match("@\A$xmlTag\Z@s", $part)) {
					$tokens[$index] = array(
									"type"		=> 'xml',
									"value"		=> $part,
									"locked"	=> TRUE,
									);

					// remember parents
					if(!empty($nestedTags))
						$tokens[$index]["parents"] = $nestedTags;
				} elseif(preg_match("@\A$htmlTagDetail@x", $part, $tagMatch)) {
					$tagName = $tagMatch[2];
					$selfClose = (isset($tagMatch[3]) && ($tagMatch[3])) ? TRUE : FALSE;
					$closing = ($tagMatch[1] || $selfClose) ? TRUE : FALSE;
				
					$tokens[$index] = array(
									"type"		=> 'tag',
									"value"		=> $part,
									"name"		=> $tagName,
									"locked"	=> TRUE,
										);

					// if tag was block, reset character context for type "text"
					$isBlock = FALSE;
					foreach($this->blockTags as $blockTag) {
						if(strtolower($tokens[$index]["name"]) == strtolower($blockTag)) {
							$isBlock = TRUE;
							break;
						}
					}
					if($isBlock)
						$prevTextIndex = NULL;

					if(!$closing) {
						// remember parents
						if(!empty($nestedTags))
							$tokens[$index]["parents"] = $nestedTags;

						$attribute = array();
						if(preg_match_all("@$attributePattern@x", $part, $attributeMatch)) {
							foreach($attributeMatch[1] as $key => $attributeName) {
								$attributeValue = $attributeMatch[2][$key].$attributeMatch[3][$key]; // one will be null, the other will contain the desired value
								$attribute[$attributeName] = $attributeValue;
							}
						}
						if(!empty($attribute))
							$tokens[$index]["attribute"] =  $attribute;
						
						//add to $nestedTags
						$nestedTags[$index]["tagName"] = $tagName;
						if (isset($tokens[$index]["attribute"])) {
							$nestedTags[$index]["attributes"] = $tokens[$index]["attribute"];
						} else {
							$nestedTags[$index]["attributes"] = NULL;
						}
					} else { // is closing
						if($selfClose) {
							// remember parents
							if(!empty($nestedTags))
								$tokens[$index]["parents"] = $nestedTags;
							
							 // set openPos and closePos to this index
							$tokens[$index]["openPos"] = $index;
							$tokens[$index]["closePos"] = $index;
						} else {
							//remove associated start tag from $nestedTags mark openPos in end tag and closePos in start tag
							$matched = FALSE;
							$tempNest = $nestedTags;
							while(count($nestedTags) > 0) {
								$lastTag = end($nestedTags);
								$lastTagIndex = key($nestedTags);
							
								unset($nestedTags[$lastTagIndex]);
							
								if($lastTag["tagName"] != $tagName) {
									// we have an improperly nested opening tag, close it at it's parent's closing tag
									$tokens[$lastTagIndex]["closePos"] = $index;
									$tokens[$lastTagIndex]["ERROR"] = "MISSING OR IMPROPERLY NESTED CLOSING TAG";

									// if improperly nested tag was block, reset character context for type "text"
									$isBlock = FALSE;
									foreach($this->blockTags as $blockTag) {
										if(strtolower($tokens[$lastTagIndex]["name"]) == strtolower($blockTag)) {
											$isBlock = TRUE;
											break;
										}
									}
									if($isBlock)
										$prevTextIndex = NULL;
								} else {
									// we have a matching start tag
									$tokens[$index]["openPos"] = $lastTagIndex;
									$tokens[$lastTagIndex]["closePos"] = $index;
									$matched = TRUE;
									
									break;
								}
							}
							if(!$matched) {
								// restore $nestedTags
								$nestedTags = $tempNest;
							
								// treat unmatched closing tag as self closing
								$tokens[$index]["openPos"] = $index;
								$tokens[$index]["closePos"] = $index;
								$tokens[$lastTagIndex]["ERROR"] = "MISSING OR IMPROPERLY NESTED OPENING TAG";
							}
						}
					}
				} else {
					$tokens[$index] = array(
									"type"=>'text',
									"value"=>$part,
									"locked"	=> TRUE,
									);
					// remember parents
					if(!empty($nestedTags))
						$tokens[$index]["parents"] = $nestedTags;

					// remember character context
					if($prevTextIndex != NULL) {
						// assign "prevChr"
						$tokens[$index]["prevChr"] = mb_substr($tokens[$prevTextIndex]["value"], -1, 1,"UTF-8");
						//set "nextChr" of previous text token
						$tokens[$prevTextIndex]["nextChr"] = mb_substr($tokens[$index]["value"], 0, 1,"UTF-8");						
					}
					//set $prevTextIndex for next text item
					$prevTextIndex = $index;
				}
				$index++;
			}
		}
		
		
		//look for opening tags that never got closed, close at end of file
		if(!empty($nestedTags))
			foreach($nestedTags as $key => $tagName) {
				$tokens[$key]["closePos"] = $index;
				$tokens[$key]["ERROR"] = "MISSING CLOSING TAG";

			}
		
		$this->html = $tokens;
		return TRUE;
	}

	#	Action:		reloads $html (i.e. capture new tags inserted in text, or remove those whose values are deleted)
	#	Returns:	TRUE on completion
	#	WARNING: Tokens acquired through "get" methods may not match new tokenization
	function reload() {
		return $this->load($this->unload());
	}
	
	#	Action:		outputs HTML as string
	#	Returns:	STRING of HTML markup
	function unload() {
		$output = "";
		foreach($this->html as $token) {
			$output .= $token["value"];
		}
		$this->clear();
		return $output;
	}
	
	#   Params:		ARRAY of tokens.
	#	Action:		overwrite "value" for all unlocked matching tokens
	#	Returns:	TRUE on completion
	function update($tokens) {
		foreach($tokens as $index => $token) {
			if(!isset($this->html[$index]["locked"]) || !$this->html[$index]["locked"])
				$this->html[$index]["value"] = $token["value"];
		}
		return TRUE;		
	}

	#	Action:		unsets $this->html
	#	Returns:	TRUE on completion
	function clear() {
		$this->html = array();
		return TRUE;		
	}


	########################################################################
	#	LOCK / UNLOCK METHODS
	#	Action:		lock matching tokens
	#	Returns:	TRUE on completion
	
	# Params:	ARRAY of tokens.
	function lock($tokens) {
		foreach($tokens as $index => $token) {
			if(isset($this->html[$index]))
				$this->html[$index]["locked"] = TRUE;
		}
		return TRUE;		
	}
	function unlock($tokens) {
		foreach($tokens as $index => $token) {
			if(isset($this->html[$index]["locked"]))
				unset($this->html[$index]["locked"]);
		}
		return TRUE;		
	}

	function lock_comments() {
		return $this->lock_type("comments");		
	}
	function unlock_comments() {
		return $this->unlock_type("comments");		
	}
	
	function lock_dtd() {
		return $this->lock_type("dtd");
	}
	function unlock_dtd() {
		return $this->unlock_type("dtd");		
	}
	
	function lock_cdata() {
		return $this->lock_type("cdata");
	}
	function unlock_cdata() {
		return $this->unlock_type("cdata");
	}
	
	function lock_xml() {
		return $this->lock_type("tag");
	}
	function unlock_xml() {
		return $this->unlock_type("tag");
	}
	
	#	Params:		$tagType INT equal to OPENING_TAGS, CLOSING_TAGS, SELFCLOSING_TAGS, OPENING_AND_SELFCLOSING_TAGS, SELFCLOSING_AND_CLOSING_TAGS, OPENING_AND_CLOSING_TAGS, ALL_TAGS
	function lock_tags($tagType = ALL_TAGS) {
		$tags = $this->get_type("tag");

		if($tagType == OPENING_TAGS) {
			$openingTags = array();
			foreach($tags as $index => $tag) {
				if(!isset($tag["openPos"]) && isset($tag["closePos"])) {
					$openingTags[$index] = $tag;
				}
			}
			return $this->lock($openingTags);
		}

		if($tagType == CLOSING_TAGS) {
			$closingTags = array();
			foreach($tags as $index => $tag) {
				if(isset($tag["openPos"]) && !isset($tag["closePos"])) {
					$closingTags[$index] = $tag;
				}
			}
			return $this->lock($closingTags);
		}

		if($tagType == SELFCLOSING_TAGS) {
			$selfClosingTags = array();
			foreach($tags as $index => $tag) {
				if(isset($tag["openPos"]) && isset($tag["closePos"])) {
					$selfClosingTags[$index] = $tag;
				}
			}
			return $this->lock($selfClosingTags);
		}

		if($tagType == OPENING_AND_SELFCLOSING_TAGS) {
			$openingAndSelfClosingTags = array();
			foreach($tags as $index => $tag) {
				if(isset($tag["closePos"])) {
					$openingAndSelfClosingTags[$index] = $tag;
				}
			}
			return $this->lock($openingAndSelfClosingTags);
		}

		if($tagType == SELFCLOSING_AND_CLOSING_TAGS) {
			$selfClosingAndClosingTags = array();
			foreach($tags as $index => $tag) {
				if(isset($tag["openPos"])) {
					$selfClosingAndClosingTags[$index] = $tag;
				}
			}
			return $this->lock($selfClosingAndClosingTags);
		}

		if($tagType == OPENING_AND_CLOSING_TAGS) {
			$openingAndClosingTags = array();
			foreach($tags as $index => $tag) {
				if((!isset($tag["openPos"]) && isset($tag["closePos"])) || (isset($tag["openPos"]) && !isset($tag["closePos"]))) {
					$openingAndClosingTags[$index] = $tag;
				}
			}
			return $this->lock($openingAndClosingTags);
		}	
		return $this->lock($tags);
	}
	#	Params:		$tagType INT equal to OPENING_TAGS, CLOSING_TAGS, SELFCLOSING_TAGS, OPENING_AND_SELFCLOSING_TAGS, SELFCLOSING_AND_CLOSING_TAGS, OPENING_AND_CLOSING_TAGS, ALL_TAGS
	function unlock_tags($tagType = ALL_TAGS) {
		$tags = $this->get_type("tag");

		if($tagType == OPENING_TAGS) {
			$openingTags = array();
			foreach($tags as $index => $tag) {
				if(!isset($tag["openPos"]) && isset($tag["closePos"])) {
					$openingTags[$index] = $tag;
				}
			}
			return $this->unlock($openingTags);
		}

		if($tagType == CLOSING_TAGS) {
			$closingTags = array();
			foreach($tags as $index => $tag) {
				if(isset($tag["openPos"]) && !isset($tag["closePos"])) {
					$closingTags[$index] = $tag;
				}
			}
			return $this->unlock($closingTags);
		}

		if($tagType == SELFCLOSING_TAGS) {
			$selfClosingTags = array();
			foreach($tags as $index => $tag) {
				if(isset($tag["openPos"]) && isset($tag["closePos"])) {
					$selfClosingTags[$index] = $tag;
				}
			}
			return $this->unlock($selfClosingTags);
		}

		if($tagType == OPENING_AND_SELFCLOSING_TAGS) {
			$openingAndSelfClosingTags = array();
			foreach($tags as $index => $tag) {
				if(isset($tag["closePos"])) {
					$openingAndSelfClosingTags[$index] = $tag;
				}
			}
			return $this->unlock($openingAndSelfClosingTags);
		}

		if($tagType == SELFCLOSING_AND_CLOSING_TAGS) {
			$selfClosingAndClosingTags = array();
			foreach($tags as $index => $tag) {
				if(isset($tag["openPos"])) {
					$selfClosingAndClosingTags[$index] = $tag;
				}
			}
			return $this->unlock($selfClosingAndClosingTags);
		}

		if($tagType == OPENING_AND_CLOSING_TAGS) {
			$openingAndClosingTags = array();
			foreach($tags as $index => $tag) {
				if((!isset($tag["openPos"]) && isset($tag["closePos"])) || (isset($tag["openPos"]) && !isset($tag["closePos"]))) {
					$openingAndClosingTags[$index] = $tag;
				}
			}
			return $this->unlock($openingAndClosingTags);
		}
		return $this->unlock($tags);
	}

	function lock_text() {
		return $this->lock_type("text");		
	}
	function unlock_text() {
		return $this->unlock_type("text");		
	}

	function lock_children($tokens, $tokenType = ALL_TOKENS) {
		foreach($tokens as $index => $token) {
			//only process opening tags
			if( (!isset($token["openPos"]) || !$token["openPos"]) && ( isset($token["closePos"]) && $token["closePos"]) ) {
				$begIndex = $index+1;
				$endIndex = $token["closePos"]-1;
				if($begIndex > $endIndex) continue;
				$childTokens = $this->get_sequential_tokens($begIndex, $endIndex, $tokenType);
//print_r($childTokens);
				$this->lock($childTokens);
			}
		}
		return TRUE;		
	}
	function unlock_children($tokens, $tokenType = ALL_TOKENS) {
		foreach($tokens as $index => $token) {
			//only process opening tags
			if( (!isset($token["openPos"]) || !$token["openPos"]) && (isset($token["closePos"]) && $token["closePos"]) ) {
				$begIndex = $index+1;
				$endIndex = $token["closePos"]-1;
				if($begIndex > $endIndex) continue;
				$childTokens = $this->get_sequential_tokens($begIndex, $endIndex, $tokenType);
				$this->unlock($childTokens);
			}
		}
		return TRUE;		
	}


	########################################################################
	#	GET METHODS
	#   Returns:    ARRAY of matching tokens
	
	function get_all() {
		return $this->html;
	}
	function get_locked() {
		$tokens = array();
		foreach($this->html as $index => $token) {
			if($token["locked"])
				$tokens[$index]=$token;
		}
		return $tokens;		
	}
	function get_unlocked() {
		$tokens = array();
		foreach($this->html as $index => $token) {
			if(!$token["locked"])
				$tokens[$index]=$token;
		}
		return $tokens;		
	}

	function get_comments() {
		return $this->get_type("comments");		
	}
	function get_locked_comments() {
		return $this->get_locked_type("comments");		
	}
	function get_unlocked_comments() {
		return $this->get_unlocked_type("comments");		
	}
	
	function get_dtd() {
		return $this->get_type("dtd");		
	}
	function get_locked_dtd() {
		return $this->get_locked_type("dtd");		
	}
	function get_unlocked_dtd() {
		return $this->get_unlocked_type("dtd");		
	}
	
	function get_cdata() {
		return $this->get_type("cdata");		
	}
	function get_locked_cdata() {
		return $this->get_locked_type("cdata");		
	}
	function get_unlocked_cdata() {
		return $this->get_unlocked_type("cdata");		
	}
	
	function get_xml() {
		return $this->get_type("tag");		
	}
	function get_locked_xml() {
		return $this->get_locked_type("tag");		
	}
	function get_unlocked_xml() {
		return $this->get_unlocked_type("tag");		
	}
	
	#	Params:		$tagType INT equal to OPENING_TAGS, CLOSING_TAGS, SELFCLOSING_TAGS, OPENING_AND_SELFCLOSING_TAGS, SELFCLOSING_AND_CLOSING_TAGS, OPENING_AND_CLOSING_TAGS, ALL_TAGS
	function get_tags($tagType = ALL_TAGS) {
		$tags = $this->get_type("tag");

		if($tagType == OPENING_TAGS) {
			$openingTags = array();
			foreach($tags as $index => $tag) {
				if(!isset($tag["openPos"]) && isset($tag["closePos"])) {
					$openingTags[$index] = $tag;
				}
			}
			return $openingTags;
		}

		if($tagType == CLOSING_TAGS) {
			$closingTags = array();
			foreach($tags as $index => $tag) {
				if(isset($tag["openPos"]) && !isset($tag["closePos"])) {
					$closingTags[$index] = $tag;
				}
			}
			return $closingTags;
		}

		if($tagType == SELFCLOSING_TAGS) {
			$selfClosingTags = array();
			foreach($tags as $index => $tag) {
				if(isset($tag["openPos"]) && isset($tag["closePos"])) {
					$selfClosingTags[$index] = $tag;
				}
			}
			return $selfClosingTags;
		}

		if($tagType == OPENING_AND_SELFCLOSING_TAGS) {
			$openingAndSelfClosingTags = array();
			foreach($tags as $index => $tag) {
				if(isset($tag["closePos"])) {
					$openingAndSelfClosingTags[$index] = $tag;
				}
			}
			return $openingAndSelfClosingTags;
		}

		if($tagType == SELFCLOSING_AND_CLOSING_TAGS) {
			$selfClosingAndClosingTags = array();
			foreach($tags as $index => $tag) {
				if(isset($tag["openPos"])) {
					$selfClosingAndClosingTags[$index] = $tag;
				}
			}
			return $selfClosingAndClosingTags;
		}

		if($tagType == OPENING_AND_CLOSING_TAGS) {
			$openingAndClosingTags = array();
			foreach($tags as $index => $tag) {
				if((!isset($tag["openPos"]) && isset($tag["closePos"])) || (isset($tag["openPos"]) && !isset($tag["closePos"]))) {
					$openingAndClosingTags[$index] = $tag;
				}
			}
			return $openingAndClosingTags;
		}	
		
		return $tags;
	}
	# 	Params:	$tagType INT equal to OPENING_TAGS, CLOSING_TAGS, SELFCLOSING_TAGS, OPENING_AND_SELFCLOSING_TAGS, SELFCLOSING_AND_CLOSING_TAGS, OPENING_AND_CLOSING_TAGS, ALL_TAGS
	function get_locked_tags($tagType = ALL_TAGS) {
		$tags = $this->get_locked_type("tag");		

		if($tagType == OPENING_TAGS) {
			$openingTags = array();
			foreach($tags as $index => $tag) {
				if(!isset($tag["openPos"]) && isset($tag["closePos"])) {
					$openingTags[$index] = $tag;
				}
			}
			return $openingTags;
		}

		if($tagType == CLOSING_TAGS) {
			$closingTags = array();
			foreach($tags as $index => $tag) {
				if(isset($tag["openPos"]) && !isset($tag["closePos"])) {
					$closingTags[$index] = $tag;
				}
			}
			return $closingTags;
		}

		if($tagType == SELFCLOSING_TAGS) {
			$selfClosingTags = array();
			foreach($tags as $index => $tag) {
				if(isset($tag["openPos"]) && isset($tag["closePos"])) {
					$selfClosingTags[$index] = $tag;
				}
			}
			return $selfClosingTags;
		}

		if($tagType == OPENING_AND_SELFCLOSING_TAGS) {
			$openingAndSelfClosingTags = array();
			foreach($tags as $index => $tag) {
				if(isset($tag["closePos"])) {
					$openingAndSelfClosingTags[$index] = $tag;
				}
			}
			return $openingAndSelfClosingTags;
		}

		if($tagType == SELFCLOSING_AND_CLOSING_TAGS) {
			$selfClosingAndClosingTags = array();
			foreach($tags as $index => $tag) {
				if(isset($tag["openPos"])) {
					$selfClosingAndClosingTags[$index] = $tag;
				}
			}
			return $selfClosingAndClosingTags;
		}

		if($tagType == OPENING_AND_CLOSING_TAGS) {
			$openingAndClosingTags = array();
			foreach($tags as $index => $tag) {
				if((!isset($tag["openPos"]) && isset($tag["closePos"])) || (isset($tag["openPos"]) && !isset($tag["closePos"]))) {
					$openingAndClosingTags[$index] = $tag;
				}
			}
			return $openingAndClosingTags;
		}	
		
		return $tags;
	}
	# 	Params:	$tagType INT equal to OPENING_TAGS, CLOSING_TAGS, SELFCLOSING_TAGS, OPENING_AND_SELFCLOSING_TAGS, SELFCLOSING_AND_CLOSING_TAGS, OPENING_AND_CLOSING_TAGS, ALL_TAGS
	function get_unlocked_tags($tagType = ALL_TAGS) {
		$tags = $this->get_unlocked_type("tag");		

		if($tagType == OPENING_TAGS) {
			$openingTags = array();
			foreach($tags as $index => $tag) {
				if(!isset($tag["openPos"]) && isset($tag["closePos"])) {
					$openingTags[$index] = $tag;
				}
			}
			return $openingTags;
		}

		if($tagType == CLOSING_TAGS) {
			$closingTags = array();
			foreach($tags as $index => $tag) {
				if(isset($tag["openPos"]) && !isset($tag["closePos"])) {
					$closingTags[$index] = $tag;
				}
			}
			return $closingTags;
		}

		if($tagType == SELFCLOSING_TAGS) {
			$selfClosingTags = array();
			foreach($tags as $index => $tag) {
				if(isset($tag["openPos"]) && isset($tag["closePos"])) {
					$selfClosingTags[$index] = $tag;
				}
			}
			return $selfClosingTags;
		}

		if($tagType == OPENING_AND_SELFCLOSING_TAGS) {
			$openingAndSelfClosingTags = array();
			foreach($tags as $index => $tag) {
				if(isset($tag["closePos"])) {
					$openingAndSelfClosingTags[$index] = $tag;
				}
			}
			return $openingAndSelfClosingTags;
		}

		if($tagType == SELFCLOSING_AND_CLOSING_TAGS) {
			$selfClosingAndClosingTags = array();
			foreach($tags as $index => $tag) {
				if(isset($tag["openPos"])) {
					$selfClosingAndClosingTags[$index] = $tag;
				}
			}
			return $selfClosingAndClosingTags;
		}

		if($tagType == OPENING_AND_CLOSING_TAGS) {
			$openingAndClosingTags = array();
			foreach($tags as $index => $tag) {
				if((!isset($tag["openPos"]) && isset($tag["closePos"])) || (isset($tag["openPos"]) && !isset($tag["closePos"]))) {
					$openingAndClosingTags[$index] = $tag;
				}
			}
			return $openingAndClosingTags;
		}	
		
		return $tags;
	}
	
	function get_text() {
		return $this->get_type("text");		
	}
	function get_locked_text() {
		return $this->get_locked_type("text");		
	}
	function get_unlocked_text() {
		return $this->get_unlocked_type("text");		
	}
	
	# 	Params:	$tagNames STRING tag name or ARRAY of tag names
	#			$tagType INT equal to OPENING_TAGS, CLOSING_TAGS, SELFCLOSING_TAGS, OPENING_AND_SELFCLOSING_TAGS, SELFCLOSING_AND_CLOSING_TAGS, OPENING_AND_CLOSING_TAGS, ALL_TAGS
	function get_tags_by_name($tagNames, $tagType = ALL_TAGS) {
		if(is_string($tagNames)) $tagNames = array($tagNames);
		$tags = $this->get_tags($tagType);
		$tagsByName = array();
		
		foreach ($tags as $index => $tag) {
			foreach($tagNames as $tagName) {
				if($tag["name"] == strtolower($tagName))
					$tagsByName[$index] = $tag;
			}
		}
		return $tagsByName;
	}
	#	Params:	$idNames STRING id name or ARRAY of id names
	function get_tag_by_id($idNames) {
		return $this->get_tags_by_attribute('id', $idNames, OPENING_AND_SELFCLOSING_TAGS);
	}
	#	Params:	$classNames STRING class name or ARRAY of class names
	#			$tagType INT equal to OPENING_TAGS, SELFCLOSING_TAGS, OPENING_AND_SELFCLOSING_TAGS
	function get_tags_by_class($classNames, $tagType = OPENING_AND_SELFCLOSING_TAGS) {
		return $this->get_tags_by_attribute('class', $classNames, $tagType);
	}
	#	Params:	$attribute STRING attribute type
	#			$attributeValue STRING class name or ARRAY of attribute values
	#			$tagType INT equal to OPENING_TAGS, SELFCLOSING_TAGS, OPENING_AND_SELFCLOSING_TAGS
	function get_tags_by_attribute($attribute, $attributeValues, $tagType = OPENING_TAGS) {
		if(is_string($attributeValues)) $attributeValues = array($attributeValues);
		$tags = $this->get_tags($tagType);
		$tagsByAttribute = array();

		if(strtolower($attribute) == "id") {
			foreach($attributeValues as $attributeValue) {
				foreach ($tags as $index => $tag) {
					if($tag["attribute"]["id"] == $attributeValue) {
						$tagsByAttribute[$index] = $tag;
						break;
					}
				}
			}
		} elseif(strtolower($attribute) == "class") {
			foreach ($tags as $index => $tag) {
				if(isset($tag["attribute"]["class"])) {
					//because there may be multiple classes
					$classList = preg_split('#\s+#', $tag["attribute"]["class"] , -1, PREG_SPLIT_NO_EMPTY);
					foreach($classList as $className) {
						foreach($attributeValues as $attributeValue) {
							if($className == $attributeValue) {
								$tagsByAttribute[$index] = $tag;
							}
						}
					}
				}
			}
		} else {
			foreach ($tags as $index => $tag) {
				if(isset($tags["attribute"][$attribute])) {
					foreach($attributeValues as $attributeValue) {
						if($tag["attribute"][$attribute] == $attributeValue)
							$tagsByAttribute[$index] = $tag;
					}
				}
			}
		}
		return $tagsByAttribute;
	}

	#	Params:	ARRAY of tokens
	function get_children($tokens, $tokenType = ALL_TOKENS) {
		$results = array();
		foreach($tokens as $index => $token) {
			//exclude (self)closing tags
			if( (isset($token["closePos"]) && $token["closePos"]) && (!isset($token["openPos"]) || !$token["openPos"]) ) {
				$begIndex = $index+1;
				$endIndex = $token["closePos"]-1;
				if($begIndex > $endIndex) continue;
				$results += $this->get_sequential_tokens($begIndex, $endIndex, $tokenType);  //union avoids dups.
			}
		}
		return $results;		
	}
	
	
	########################################################################
	#	CONDITIONAL METHODS
	#
	#   Returns:    TRUE or FALSE depending if condition is met
	
	#   Parameter:  $tagNames MIXED value(s) of tag name, such as STRING of tag name or ARRAY of tag names
	#				$token ARRAY token to be evaluated
	function in_tag($tagNames, $token) {
		if(is_string($tagNames)) $tagNames = array($tagNames);

		if(isset($token["parents"])){
			foreach ($token["parents"] as $parent) {
				if(isset($parent["tagName"])){
					foreach($tagNames as $tagName) {
						if($parent["tagName"] == $tagName) return TRUE;
					}
				}
			}
		}
		return FALSE;
	}


	#   Parameters: $attributeName STRING name of attribute, such as "id" or "class"
	# 				$attributeValue MIXED value(s) of attribute, such as STRING of id Name or ARRAY of Class Names
	#					note: if an ARRAY is passed, method will return TRUE if _any_ of the values match
	#				$token ARRAY token to be evaluated
	function in_attribute($attributeName, $attributeValues, $token) {
		if(is_string($attributeValues)) $attributeValues = array($attributeValues);

		if(isset($token["parents"])){
			foreach ($token["parents"] as $parent) {
				if(isset($parent["attributes"][$attributeName])) {
					if($attributeName == "class" || $attributeName == "CLASS") {
						//because there may be multiple classes
						$classList = preg_split('#\s+#', $parent["attributes"][$attributeName] , -1, PREG_SPLIT_NO_EMPTY);
						foreach($classList as $className) {
							foreach($attributeValues as $attributeValue) {
								if($className == $attributeValue) {
									return TRUE;
								}
							}
						}
					} else {
						foreach($attributeValues as $attributeValue) {
							if($parent["attributes"][$attributeName] == $attributeValue) {
									return TRUE;
								}
						}
					}
				}
			}
		}
		return FALSE;
	}

	#   Parameter:  $idName MIXED - ARRAY or STRING of id Name(s)
	#					note: if an ARRAY is passed, method will return TRUE if _any_ of the values match
	#				$token ARRAY token to be evaluated
	function in_id($idName, $token) {
		return $this->in_attribute("id", $idName, $token);
	}


	#   Parameter:  $className MIXED - ARRAY or STRING of class Name(s)
	#					note: if an ARRAY is passed, method will return TRUE if _any_ of the values match
	#				$token ARRAY token to be evaluated
	function in_class($className, $token) {
		return $this->in_attribute("class", $className, $token);
	}


	#=======================================================================
	#=======================================================================
	#==	MISC. METHODS
	#=======================================================================
	#=======================================================================
	
	
	########################################################################
	#   LOCK / UNLOCK BY TYPE
	#	Action:		locks / unlocks matching tokens
	#   Returns:    TRUE on completion

	#	Params:	STRING type to lock
	function lock_type($type) {
		foreach($this->html as $index => &$token) {
			if($token["type"] == $type)
				$token["locked"] = TRUE;
		}
		return TRUE;		
	}

	#	Params:	STRING type to lock
	function unlock_type($type) {
		foreach($this->html as $index => &$token) {
			if($token["type"] == $type)
				unset($token["locked"]);
		}
		return TRUE;		
	}


	########################################################################
	#   GET METHODS
	#   Returns:	returns matching tokens
	#
	
	#	Params:	STRING type to get
	function get_type($type) {
		$tokens = array();
		foreach($this->html as $index => $token) {
			if($token["type"] == $type)
				$tokens[$index] = $token; 
		}
		return $tokens;		
	}

	#	Params:	STRING type to get
	function get_unlocked_type($type) {
		$tokens = array();
		foreach($this->get_type($type) as $index => $token) {
			if(!(isset($token["locked"])) || !$token["locked"])
				$tokens[$index] = $token; 
		}
		return $tokens;		
	}

	#	Params:	STRING type to get
	function get_locked_type($type) {
		$tokens = array();
		foreach($this->get_type($type) as $index => $token) {
			if($token["locked"])
				$tokens[$index] = $token; 
		}
		return $tokens;		
	}

	#   Params:		STRING beginning index
	#				STRING ending index
	function get_sequential_tokens($begIndex, $endIndex, $tokenType = ALL_TOKENS) {
		$tokens = array();
		$types = array();

		if($tokenType == TEXT_TOKENS) {
			$types = array('text');
		} elseif($tokenType == TAG_TOKENS) {
			$types = array('tag');
		} elseif($tokenType == COMMENT_TOKENS) {
			$types = array('comment');
		} elseif($tokenType == CDATA_TOKENS) {
			$types = array('cdata');
		} elseif($tokenType == TEXT_AND_TAG_TOKENS) {
			$types = array('text','tag');
		} elseif($tokenType == TEXT_AND_COMMENT_TOKENS) {
			$types = array('text','comment');
		} elseif($tokenType == TEXT_AND_CDATA_TOKENS) {
			$types = array('text','cdata');
		} elseif($tokenType == TAG_AND_COMMENT_TOKENS) {
			$types = array('tag','comment');
		} elseif($tokenType == TAG_AND_CDATA_TOKENS) {
			$types = array('tag','cdata');
		} elseif($tokenType == COMMENT_AND_CDATA_TOKENS) {
			$types = array('comment','cdata');
		} elseif($tokenType == TEXT_TAG_AND_COMMENT_TOKENS) {
			$types = array('text','tag','comment');
		} elseif($tokenType == TEXT_TAG_AND_CDATA_TOKENS) {
			$types = array('text','tag','cdata');
		} elseif($tokenType == TEXT_COMMENT_AND_CDATA_TOKENS) {
			$types = array('text','comment','cdata');
		} elseif($tokenType == TAG_COMMENT_AND_CDATA_TOKENS) {
			$types = array('tag','comment','cdata');
		} else {
			$types = array('text','tag','comment','cdata');
		}


		if($begIndex > $endIndex){
			$temp = $begIndex;
			$begIndex = $endIndex;
			$endIndex = $temp;
		}
		for($index = $begIndex; $index<=$endIndex; $index++) {
			if(isset($this->html[$index])) {
				foreach($types as $type) {
					if($type == $this->html[$index]["type"]) {
						$tokens[$index] = $this->html[$index];
						break;
					}
				}
			}
		}
		return $tokens;
	}

} // end class parseHTML