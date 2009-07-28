<?php
/*
	Project: PHP Typography
	Project URI: http://kingdesk.com/projects/php-typography/
	
	File modified to place pattern and exceptions in arrays that can be understood in php files.
	This file is released under the same copyright as the below referenced original file
	Original unmodified file is available at: http://mirror.unl.edu/ctan/language/hyph-utf8/tex/generic/hyph-utf8/patterns/
	Original file name: hyph-_______________.tex
	
//============================================================================================================
	ORIGINAL FILE INFO

		% This file is part of hyph-utf8 package and resulted from
		% semi-manual conversions of hyphenation patterns into UTF-8 in June 2008.
		%
		% Source: fihyph.tex (yyyy-mm-dd)
		% Author: Kauko Saarinen
		%
		% The above mentioned file should become obsolete,
		% and the author of the original file should preferaby modify this file instead.
		%
		% Modificatios were needed in order to support native UTF-8 engines,
		% but functionality (hopefully) didn't change in any way, at least not intentionally.
		% This file is no longer stand-alone; at least for 8-bit engines
		% you probably want to use loadhyph-foo.tex (which will load this file) instead.
		%
		% Modifications were done by Jonathan Kew, Mojca Miklavec & Arthur Reutenauer
		% with help & support from:
		% - Karl Berry, who gave us free hands and all resources
		% - Taco Hoekwater, with useful macros
		% - Hans Hagen, who did the unicodifisation of patterns already long before
		%               and helped with testing, suggestions and bug reports
		% - Norbert Preining, who tested & integrated patterns into TeX Live
		%
		% However, the 'copyright/copyleft' owner of patterns remains the original author.
		%
		% The copyright statement of this file is thus:
		%
		%    Do with this file whatever needs to be done in future for the sake of
		%    'a better world' as long as you respect the copyright of original file.
		%    If you're the original author of patterns or taking over a new revolution,
		%    plese remove all of the TUG comments & credits that we added here -
		%    you are the Queen / the King, we are only the servants.
		%
		% If you want to change this file, rather than uploading directly to CTAN,
		% we would be grateful if you could send it to us (http://tug.org/tex-hyphen)
		% or ask for credentials for SVN repository and commit it yourself;
		% we will then upload the whole 'package' to CTAN.
		%
		% Before a new 'pattern-revolution' starts,
		% please try to follow some guidelines if possible:
		%
		% - \lccode is *forbidden*, and I really mean it
		% - all the patterns should be in UTF-8
		% - the only 'allowed' TeX commands in this file are: \patterns, \hyphenation,
		%   and if you really cannot do without, also \input and \message
		% - in particular, please no \catcode or \lccode changes,
		%   they belong to loadhyph-foo.tex,
		%   and no \lefthyphenmin and \righthyphenmin,
		%   they have no influence here and belong elsewhere
		% - \begingroup and/or \endinput is not needed
		% - feel free to do whatever you want inside comments
		%
		% We know that TeX is extremely powerful, but give a stupid parser
		% at least a chance to read your patterns.
		%
		% For more unformation see
		%
		%    http://tug.org/tex-hyphen
		%
		%------------------------------------------------------------------------------
		%
		% ----->  Finnish hyphenation patterns for MLPCTeX  <------
		% First release January -86 by Kauko Saarinen,
		% Computing Centre, University of Jyvaskyla, Finland
		%
		% Completely rewritten January -88. The new patterns make
		% much less mistakes with foreign and compound words.
		% The article 'Automatic Hyphenation of Finnish'
		% by Professor Fred Karlsson is also referred
		% ---------------------------------------------------------
		%
		% 8th March -89 (vers. 2.2), some vowel triples by Fred Karlsson added.
		% 9th January - 95: added \uccode and \lccode by Thomas Esser
		%
		% *********     Patterns may be freely distributed   **********
		%


//============================================================================================================	
	
*/

$patgenLanguage = 'Finnish';

$patgenExceptions = array();

$patgenMaxSeg = 7;

$patgen = array(
'begin'=>array(
'ä'=>'02',
'ydin'=>'00021',
'suura'=>'000212'
),
'end'=>array(
'sidea'=>'212000'
),
'all'=>array(
'ba'=>'100',
'be'=>'100',
'bi'=>'100',
'bo'=>'100',
'bu'=>'100',
'by'=>'100',
'da'=>'100',
'de'=>'100',
'di'=>'100',
'do'=>'100',
'du'=>'100',
'dy'=>'100',
'dä'=>'100',
'dö'=>'100',
'fa'=>'100',
'fe'=>'100',
'fi'=>'100',
'fo'=>'100',
'fu'=>'100',
'fy'=>'100',
'ga'=>'100',
'ge'=>'100',
'gi'=>'100',
'go'=>'100',
'gu'=>'100',
'gy'=>'100',
'gä'=>'100',
'gö'=>'100',
'ha'=>'100',
'he'=>'100',
'hi'=>'100',
'ho'=>'100',
'hu'=>'100',
'hy'=>'100',
'hä'=>'100',
'hö'=>'100',
'ja'=>'100',
'je'=>'100',
'ji'=>'100',
'jo'=>'100',
'ju'=>'100',
'jy'=>'100',
'jä'=>'100',
'jö'=>'100',
'ka'=>'100',
'ke'=>'100',
'ki'=>'100',
'ko'=>'100',
'ku'=>'100',
'ky'=>'100',
'kä'=>'100',
'kö'=>'100',
'la'=>'100',
'le'=>'100',
'li'=>'100',
'lo'=>'100',
'lu'=>'100',
'ly'=>'100',
'lä'=>'100',
'lö'=>'100',
'ma'=>'100',
'me'=>'100',
'mi'=>'100',
'mo'=>'100',
'mu'=>'100',
'my'=>'100',
'mä'=>'100',
'mö'=>'100',
'na'=>'100',
'ne'=>'100',
'ni'=>'100',
'no'=>'100',
'nu'=>'100',
'ny'=>'100',
'nä'=>'100',
'nö'=>'100',
'pa'=>'100',
'pe'=>'100',
'pi'=>'100',
'po'=>'100',
'pu'=>'100',
'py'=>'100',
'pä'=>'100',
'pö'=>'100',
'ra'=>'100',
're'=>'100',
'ri'=>'100',
'ro'=>'100',
'ru'=>'100',
'ry'=>'100',
'rä'=>'100',
'rö'=>'100',
'sa'=>'100',
'se'=>'100',
'si'=>'100',
'so'=>'100',
'su'=>'100',
'sy'=>'100',
'sä'=>'100',
'sö'=>'100',
'ta'=>'100',
'te'=>'100',
'ti'=>'100',
'to'=>'100',
'tu'=>'100',
'ty'=>'100',
'tä'=>'100',
'tö'=>'100',
'va'=>'100',
've'=>'100',
'vi'=>'100',
'vo'=>'100',
'vu'=>'100',
'vy'=>'100',
'vä'=>'100',
'vö'=>'100',
'str'=>'1020',
'äy'=>'020',
'ya'=>'012',
'yo'=>'012',
'oy'=>'010',
'öy'=>'020',
'uy'=>'012',
'yu'=>'012',
'öa'=>'032',
'öo'=>'032',
'äa'=>'032',
'äo'=>'032',
'äu'=>'012',
'öu'=>'012',
'aä'=>'010',
'aö'=>'010',
'oä'=>'010',
'oö'=>'010',
'uä'=>'012',
'uö'=>'012',
'ää'=>'020',
'öö'=>'020',
'äö'=>'020',
'öä'=>'020',
'aai'=>'0012',
'aae'=>'0012',
'aao'=>'0012',
'aau'=>'0012',
'eea'=>'0012',
'eei'=>'0012',
'eeu'=>'0012',
'eey'=>'0012',
'iia'=>'0012',
'iie'=>'0012',
'iio'=>'0012',
'uua'=>'0012',
'uue'=>'0012',
'uuo'=>'0012',
'uui'=>'0012',
'eaa'=>'0100',
'iaa'=>'0100',
'oaa'=>'0100',
'uaa'=>'0100',
'uee'=>'0100',
'auu'=>'0100',
'iuu'=>'0100',
'euu'=>'0100',
'ouu'=>'0100',
'ääi'=>'0010',
'ääe'=>'0010',
'ääy'=>'0030',
'iää'=>'0100',
'eää'=>'0100',
'yää'=>'0100',
'iöö'=>'0100',
'aei'=>'0100',
'aoi'=>'0100',
'eai'=>'0100',
'iau'=>'0100',
'yei'=>'0100',
'aia'=>'0010',
'aie'=>'0010',
'aio'=>'0010',
'aiu'=>'0010',
'aua'=>'0010',
'aue'=>'0010',
'eua'=>'0010',
'iea'=>'0010',
'ieo'=>'0010',
'iey'=>'0010',
'ioa'=>'0012',
'ioe'=>'0012',
'iua'=>'0010',
'iue'=>'0010',
'iuo'=>'0010',
'oia'=>'0010',
'oie'=>'0010',
'oio'=>'0010',
'oiu'=>'0010',
'oui'=>'0100',
'oue'=>'0010',
'ouo'=>'0010',
'uea'=>'0010',
'uie'=>'0010',
'uoa'=>'0010',
'uou'=>'0010',
'eö'=>'012',
'öe'=>'012',
'us'=>'020',
'yliop'=>'000120',
'aliav'=>'000120',
'spli'=>'10200',
'alous'=>'000001',
'keus'=>'00001',
'rtaus'=>'000001',
'sohje'=>'210000',
'sasia'=>'212000',
'asian'=>'120000',
'asiat'=>'120000',
'asioi'=>'120000',
'ras'=>'0200',
'las'=>'0200',
'sopisk'=>'2120000',
'nopet'=>'212000',
'saloi'=>'212000',
'nopist'=>'2120000',
'sopist'=>'2120000',
'sosa'=>'21200',
'nosa'=>'21200',
'alkeis'=>'0000021',
'perus'=>'000001',
'sidean'=>'2120000',
'sesity'=>'2120000',
'nedus'=>'212000',
'sajatu'=>'2100000',
'sase'=>'21000',
'sapu'=>'21000',
'syrit'=>'212000',
'syhti'=>'212000',
'notto'=>'210000',
'noton'=>'210000',
'nanto'=>'210000',
'nanno'=>'210000',
'najan'=>'212000',
'naika'=>'210000',
'nomai'=>'212000',
'nylit'=>'212000',
'salen'=>'212000',
'nalen'=>'212000',
'asiakas'=>'12000021',
'ulos'=>'00021',
'najo'=>'21200',
'sajo'=>'21200',
'bl'=>'020',
'blo'=>'1200',
'bibli'=>'000300',
'br'=>'020',
'bri'=>'1200',
'bro'=>'1200',
'bru'=>'1200',
'dr'=>'020',
'dra'=>'1200',
'fl'=>'020',
'fla'=>'1200',
'fr'=>'020',
'fra'=>'1200',
'fre'=>'1200',
'gl'=>'020',
'glo'=>'1200',
'gr'=>'020',
'gra'=>'1200',
'kl'=>'020',
'kra'=>'1200',
'kre'=>'1200',
'kri'=>'1200',
'kv'=>'120',
'kva'=>'1200',
'pl'=>'020',
'pr'=>'020',
'pro'=>'1200',
'cl'=>'020',
'qv'=>'020',
'qvi'=>'1200',
'sch'=>'0020',
'tsh'=>'0020',
'chr'=>'0020'
)
);

?>