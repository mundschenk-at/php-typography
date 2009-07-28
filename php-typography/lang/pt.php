<?php
/*
	Project: PHP Typography
	Project URI: http://kingdesk.com/projects/php-typography/
	
	File modified to place pattern and exceptions in arrays that can be understood in php files.
	This file is released under the same copyright as the below referenced original file
	Original unmodified file is available at: http://mirror.unl.edu/ctan/language/hyph-utf8/tex/generic/hyph-utf8/patterns/
	Original file name: hyph-pt.tex
	
//============================================================================================================
	ORIGINAL FILE INFO

		% This file is part of hyph-utf8 package and resulted from
		% semi-manual conversions of hyphenation patterns into UTF-8 in June 2008.
		%
		% Source: pthyph.tex (1994-10-13 - date on CTAN) or (1996-07-21 - date in file) - no idea
		% Author: Pedro J. de Rezende <rezende at dcc.unicamp.br>, J.Joao Dias Almeida <jj at di.uminho.pt>
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
		%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
		% The Portuguese TeX hyphenation table.
		% (C) 1996 by  Pedro J. de Rezende (rezende@dcc.unicamp.br)
		%          and J.Joao Dias Almeida (jj@di.uminho.pt)
		% Version: 1.2 Release date: 21/07/96
		%
		% (C) 1994 by Pedro J. de Rezende (rezende@dcc.unicamp.br)
		% Version: 1.1 Release date: 04/12/94
		%
		% (C) 1987 by Pedro J. de Rezende
		% Version: 1.0 Release date: 02/13/87
		%
		% -----------------------------------------------------------------
		% IMPORTANT NOTICE:
		%
		% This program can be redistributed and/or modified under the terms
		% of the LaTeX Project Public License Distributed from CTAN
		% archives in directory macros/latex/base/lppl.txt; either
		% version 1 of the License, or any later version.
		% -----------------------------------------------------------------
		% Remember! If you *must* change it, then call the resulting file 
		% something  else and attach your name to your *documented* changes.
		% ======================================================================


//============================================================================================================	
	
*/

$patgenLanguage = 'Portuguese';

$patgenExceptions = array(
'hardware'=>'hard-ware',
'software'=>'soft-ware'
);

$patgenMaxSeg = 3;

$patgen = array(
'begin'=>array(),
'end'=>array(),
'all'=>array(
'bl'=>'120',
'br'=>'120',
'ba'=>'100',
'be'=>'100',
'bi'=>'100',
'bo'=>'100',
'bu'=>'100',
'bá'=>'100',
'bâ'=>'100',
'bã'=>'100',
'bé'=>'100',
'bí'=>'100',
'bó'=>'100',
'bú'=>'100',
'bê'=>'100',
'bõ'=>'100',
'ch'=>'120',
'cl'=>'120',
'cr'=>'120',
'ca'=>'100',
'ce'=>'100',
'ci'=>'100',
'co'=>'100',
'cu'=>'100',
'cá'=>'100',
'câ'=>'100',
'cã'=>'100',
'cé'=>'100',
'cí'=>'100',
'có'=>'100',
'cú'=>'100',
'cê'=>'100',
'cõ'=>'100',
'ça'=>'100',
'çe'=>'100',
'çi'=>'100',
'ço'=>'100',
'çu'=>'100',
'çá'=>'100',
'çâ'=>'100',
'çã'=>'100',
'çé'=>'100',
'çí'=>'100',
'çó'=>'100',
'çú'=>'100',
'çê'=>'100',
'çõ'=>'100',
'dl'=>'120',
'dr'=>'120',
'da'=>'100',
'de'=>'100',
'di'=>'100',
'do'=>'100',
'du'=>'100',
'dá'=>'100',
'dâ'=>'100',
'dã'=>'100',
'dé'=>'100',
'dí'=>'100',
'dó'=>'100',
'dú'=>'100',
'dê'=>'100',
'dõ'=>'100',
'fl'=>'120',
'fr'=>'120',
'fa'=>'100',
'fe'=>'100',
'fi'=>'100',
'fo'=>'100',
'fu'=>'100',
'fá'=>'100',
'fâ'=>'100',
'fã'=>'100',
'fé'=>'100',
'fí'=>'100',
'fó'=>'100',
'fú'=>'100',
'fê'=>'100',
'fõ'=>'100',
'gl'=>'120',
'gr'=>'120',
'ga'=>'100',
'ge'=>'100',
'gi'=>'100',
'go'=>'100',
'gu'=>'100',
'gua'=>'1040',
'gue'=>'1040',
'gui'=>'1040',
'guo'=>'1040',
'gá'=>'100',
'gâ'=>'100',
'gã'=>'100',
'gé'=>'100',
'gí'=>'100',
'gó'=>'100',
'gú'=>'100',
'gê'=>'100',
'gõ'=>'100',
'ja'=>'100',
'je'=>'100',
'ji'=>'100',
'jo'=>'100',
'ju'=>'100',
'já'=>'100',
'jâ'=>'100',
'jã'=>'100',
'jé'=>'100',
'jí'=>'100',
'jó'=>'100',
'jú'=>'100',
'jê'=>'100',
'jõ'=>'100',
'kl'=>'120',
'kr'=>'120',
'ka'=>'100',
'ke'=>'100',
'ki'=>'100',
'ko'=>'100',
'ku'=>'100',
'ká'=>'100',
'kâ'=>'100',
'kã'=>'100',
'ké'=>'100',
'kí'=>'100',
'kó'=>'100',
'kú'=>'100',
'kê'=>'100',
'kõ'=>'100',
'lh'=>'120',
'la'=>'100',
'le'=>'100',
'li'=>'100',
'lo'=>'100',
'lu'=>'100',
'lá'=>'100',
'lâ'=>'100',
'lã'=>'100',
'lé'=>'100',
'lí'=>'100',
'ló'=>'100',
'lú'=>'100',
'lê'=>'100',
'lõ'=>'100',
'ma'=>'100',
'me'=>'100',
'mi'=>'100',
'mo'=>'100',
'mu'=>'100',
'má'=>'100',
'mâ'=>'100',
'mã'=>'100',
'mé'=>'100',
'mí'=>'100',
'mó'=>'100',
'mú'=>'100',
'mê'=>'100',
'mõ'=>'100',
'nh'=>'120',
'na'=>'100',
'ne'=>'100',
'ni'=>'100',
'no'=>'100',
'nu'=>'100',
'ná'=>'100',
'nâ'=>'100',
'nã'=>'100',
'né'=>'100',
'ní'=>'100',
'nó'=>'100',
'nú'=>'100',
'nê'=>'100',
'nõ'=>'100',
'pl'=>'120',
'pr'=>'120',
'pa'=>'100',
'pe'=>'100',
'pi'=>'100',
'po'=>'100',
'pu'=>'100',
'pá'=>'100',
'pâ'=>'100',
'pã'=>'100',
'pé'=>'100',
'pí'=>'100',
'pó'=>'100',
'pú'=>'100',
'pê'=>'100',
'põ'=>'100',
'qua'=>'1040',
'que'=>'1040',
'qui'=>'1040',
'quo'=>'1040',
'ra'=>'100',
're'=>'100',
'ri'=>'100',
'ro'=>'100',
'ru'=>'100',
'rá'=>'100',
'râ'=>'100',
'rã'=>'100',
'ré'=>'100',
'rí'=>'100',
'ró'=>'100',
'rú'=>'100',
'rê'=>'100',
'rõ'=>'100',
'sa'=>'100',
'se'=>'100',
'si'=>'100',
'so'=>'100',
'su'=>'100',
'sá'=>'100',
'sâ'=>'100',
'sã'=>'100',
'sé'=>'100',
'sí'=>'100',
'só'=>'100',
'sú'=>'100',
'sê'=>'100',
'sõ'=>'100',
'tl'=>'120',
'tr'=>'120',
'ta'=>'100',
'te'=>'100',
'ti'=>'100',
'to'=>'100',
'tu'=>'100',
'tá'=>'100',
'tâ'=>'100',
'tã'=>'100',
'té'=>'100',
'tí'=>'100',
'tó'=>'100',
'tú'=>'100',
'tê'=>'100',
'tõ'=>'100',
'vl'=>'120',
'vr'=>'120',
'va'=>'100',
've'=>'100',
'vi'=>'100',
'vo'=>'100',
'vu'=>'100',
'vá'=>'100',
'vâ'=>'100',
'vã'=>'100',
'vé'=>'100',
'ví'=>'100',
'vó'=>'100',
'vú'=>'100',
'vê'=>'100',
'võ'=>'100',
'wl'=>'120',
'wr'=>'120',
'xa'=>'100',
'xe'=>'100',
'xi'=>'100',
'xo'=>'100',
'xu'=>'100',
'xá'=>'100',
'xâ'=>'100',
'xã'=>'100',
'xé'=>'100',
'xí'=>'100',
'xó'=>'100',
'xú'=>'100',
'xê'=>'100',
'xõ'=>'100',
'za'=>'100',
'ze'=>'100',
'zi'=>'100',
'zo'=>'100',
'zu'=>'100',
'zá'=>'100',
'zâ'=>'100',
'zã'=>'100',
'zé'=>'100',
'zí'=>'100',
'zó'=>'100',
'zú'=>'100',
'zê'=>'100',
'zõ'=>'100',
'aa'=>'030',
'ae'=>'030',
'ao'=>'030',
'cc'=>'030',
'ea'=>'030',
'ee'=>'030',
'eo'=>'030',
'ia'=>'030',
'ie'=>'030',
'ii'=>'030',
'io'=>'030',
'iâ'=>'030',
'iê'=>'030',
'iô'=>'030',
'oa'=>'030',
'oe'=>'030',
'oo'=>'030',
'rr'=>'030',
'ss'=>'030',
'ua'=>'030',
'ue'=>'030',
'uo'=>'030',
'uu'=>'030',
'-'=>'10'
)
);

?>