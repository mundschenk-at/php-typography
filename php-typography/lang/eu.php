<?php
/*
	Project: PHP Typography
	Project URI: http://kingdesk.com/projects/php-typography/
	
	File modified to place pattern and exceptions in arrays that can be understood in php files.
	This file is released under the same copyright as the below referenced original file
	Original unmodified file is available at: http://mirror.unl.edu/ctan/language/hyph-utf8/tex/generic/hyph-utf8/patterns/
	Original file name: hyph-eu.tex
	
//============================================================================================================
	ORIGINAL FILE INFO

		% Hyphenation patterns for Basque.
		%
		% This file has first been written by Juan M. Aguirregabiria
		% (juanmari.aguirregabiria@ehu.es) on February 1997 based on the
		% shyphen.sh script that generates the Spanish patterns as compiled
		% by Julio Sanchez (jsanchez@gmv.es) on September 1991.
		%
		% In June 2008 the generating script has been rewritten into ruby and
		% adapted for native UTF-8 TeX engines. Patterns became part of hyph-utf8
		% package and were renamed from bahyph.tex into hyph-eu.tex.
		% Functionality should not change apart from adding ñ by default.
		%
		% The original Copyright followed and applied also to precessor of this file
		% whose last version will be always available by anonymous ftp
		% from tp.lc.ehu.es or by poynting your Web browser to
		%     http://tp.lc.ehu.es/jma/basque.html
		%
		% For more information about the new UTF-8 hyphenation patterns and
		% links to this file see
		%     http://www.tug.org/tex-hyphen/
		%
		%          COPYRIGHT NOTICE
		%
		% These patterns and the generating script are Copyright (c) JMA 1997, 2008
		% These patterns are made public in the hope that they will benefit others.
		% You can use this software for any purpose.
		% However, this is given for free and WITHOUT ANY WARRANTY.
		%
		% You are kindly requested to send any changes to the author.
		% If you change the generating script, you must include code
		% in it such that any output is clearly labeled as generated
		% by a modified script.
		%
		%               END OF COPYRIGHT NOTICE
		%
		% Open vowels: a e o
		% Closed vowels: i u
		% Consonants: b c d f g j k l m n ñ p q r s t v w x y z
		%
		% Some of the patterns below represent combinations that never
		% happen in Basque. Would they happen, they would be hyphenated
		% according to the rules.
		% 


//============================================================================================================	
	
*/

$patgenLanguage = 'Basque';

$patgenExceptions = array();

$patgenMaxSeg = 4;

$patgen = array(
'begin'=>array(),
'end'=>array(),
'all'=>array(
'ba'=>'100',
'be'=>'100',
'bo'=>'100',
'bi'=>'100',
'bu'=>'100',
'ca'=>'100',
'ce'=>'100',
'co'=>'100',
'ci'=>'100',
'cu'=>'100',
'da'=>'100',
'de'=>'100',
'do'=>'100',
'di'=>'100',
'du'=>'100',
'fa'=>'100',
'fe'=>'100',
'fo'=>'100',
'fi'=>'100',
'fu'=>'100',
'ga'=>'100',
'ge'=>'100',
'go'=>'100',
'gi'=>'100',
'gu'=>'100',
'ja'=>'100',
'je'=>'100',
'jo'=>'100',
'ji'=>'100',
'ju'=>'100',
'ka'=>'100',
'ke'=>'100',
'ko'=>'100',
'ki'=>'100',
'ku'=>'100',
'la'=>'100',
'le'=>'100',
'lo'=>'100',
'li'=>'100',
'lu'=>'100',
'ma'=>'100',
'me'=>'100',
'mo'=>'100',
'mi'=>'100',
'mu'=>'100',
'na'=>'100',
'ne'=>'100',
'no'=>'100',
'ni'=>'100',
'nu'=>'100',
'ña'=>'100',
'ñe'=>'100',
'ño'=>'100',
'ñi'=>'100',
'ñu'=>'100',
'pa'=>'100',
'pe'=>'100',
'po'=>'100',
'pi'=>'100',
'pu'=>'100',
'qa'=>'100',
'qe'=>'100',
'qo'=>'100',
'qi'=>'100',
'qu'=>'100',
'ra'=>'100',
're'=>'100',
'ro'=>'100',
'ri'=>'100',
'ru'=>'100',
'sa'=>'100',
'se'=>'100',
'so'=>'100',
'si'=>'100',
'su'=>'100',
'ta'=>'100',
'te'=>'100',
'to'=>'100',
'ti'=>'100',
'tu'=>'100',
'va'=>'100',
've'=>'100',
'vo'=>'100',
'vi'=>'100',
'vu'=>'100',
'wa'=>'100',
'we'=>'100',
'wo'=>'100',
'wi'=>'100',
'wu'=>'100',
'xa'=>'100',
'xe'=>'100',
'xo'=>'100',
'xi'=>'100',
'xu'=>'100',
'ya'=>'100',
'ye'=>'100',
'yo'=>'100',
'yi'=>'100',
'yu'=>'100',
'za'=>'100',
'ze'=>'100',
'zo'=>'100',
'zi'=>'100',
'zu'=>'100',
'lla'=>'1200',
'lle'=>'1200',
'llo'=>'1200',
'lli'=>'1200',
'llu'=>'1200',
'rra'=>'1200',
'rre'=>'1200',
'rro'=>'1200',
'rri'=>'1200',
'rru'=>'1200',
'tsa'=>'1200',
'tse'=>'1200',
'tso'=>'1200',
'tsi'=>'1200',
'tsu'=>'1200',
'txa'=>'1200',
'txe'=>'1200',
'txo'=>'1200',
'txi'=>'1200',
'txu'=>'1200',
'tza'=>'1200',
'tze'=>'1200',
'tzo'=>'1200',
'tzi'=>'1200',
'tzu'=>'1200',
'bla'=>'1200',
'ble'=>'1200',
'blo'=>'1200',
'bli'=>'1200',
'blu'=>'1200',
'bra'=>'1200',
'bre'=>'1200',
'bro'=>'1200',
'bri'=>'1200',
'bru'=>'1200',
'dra'=>'1200',
'dre'=>'1200',
'dro'=>'1200',
'dri'=>'1200',
'dru'=>'1200',
'fla'=>'1200',
'fle'=>'1200',
'flo'=>'1200',
'fli'=>'1200',
'flu'=>'1200',
'fra'=>'1200',
'fre'=>'1200',
'fro'=>'1200',
'fri'=>'1200',
'fru'=>'1200',
'gla'=>'1200',
'gle'=>'1200',
'glo'=>'1200',
'gli'=>'1200',
'glu'=>'1200',
'gra'=>'1200',
'gre'=>'1200',
'gro'=>'1200',
'gri'=>'1200',
'gru'=>'1200',
'kla'=>'1200',
'kle'=>'1200',
'klo'=>'1200',
'kli'=>'1200',
'klu'=>'1200',
'kra'=>'1200',
'kre'=>'1200',
'kro'=>'1200',
'kri'=>'1200',
'kru'=>'1200',
'pla'=>'1200',
'ple'=>'1200',
'plo'=>'1200',
'pli'=>'1200',
'plu'=>'1200',
'pra'=>'1200',
'pre'=>'1200',
'pro'=>'1200',
'pri'=>'1200',
'pru'=>'1200',
'tra'=>'1200',
'tre'=>'1200',
'tro'=>'1200',
'tri'=>'1200',
'tru'=>'1200',
'subr'=>'00220',
'subl'=>'00220'
)
);

?>