<?php
/*
	Project: PHP Typography
	Project URI: http://kingdesk.com/projects/php-typography/
	
	File modified to place pattern and exceptions in arrays that can be understood in php files.
	This file is released under the same copyright as the below referenced original file
	Original unmodified file is available at: http://mirror.unl.edu/ctan/language/hyph-utf8/tex/generic/hyph-utf8/patterns/
	Original file name: hyph-la.tex
	
//============================================================================================================
	ORIGINAL FILE INFO

		% This file is part of hyph-utf8 package and resulted from
		% semi-manual conversions of hyphenation patterns into UTF-8 in June 2008.
		%
		% Source: lahyph.tex (2007-09-03)
		% Author: Claudio Beccari <claudio.beccari at polito.it>
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
		%                  ********** lahyph.tex *************
		%
		% Copyright 1999- 2001 Claudio Beccari
		%                [latin hyphenation patterns]
		%
		% -----------------------------------------------------------------
		% IMPORTANT NOTICE:
		%
		% This program can be redistributed and/or modified under the terms
		% of the LaTeX Project Public License Distributed from CTAN
		% archives in directory macros/latex/base/lppl.txt; either
		% version 1 of the License, or any later version.
		% -----------------------------------------------------------------
		%
		% Patterns for the latin language mainly in modern spelling
		% (u when u is needed and v when v is needed); medieval spelling
		% with the ligatures \ae and \oe  and the (uncial) lowercase `v'
		% written as a `u' is also supported; apparently there is no conflict
		% between the patterns of modern  Latin and those of medieval Latin.
		%
		% Support for font encoding OT1 with 128-character set and
		% for font encoding T1 with a 256-character set.
		%
		% Prepared by  Claudio Beccari
		%              Politecnico di Torino
		%              Torino, Italy
		%              e-mail beccari@polito.it
		%
		% 1999/03/10 Integration of `lahyph7.tex' and `lahyph8.tex' into
		% one file `lahyph.tex' supporting fonts in OT1 and T1 encoding by
		% Bernd Raichle using the macro code from `dehypht.tex' (this code
		% is Copyright 1993,1994,1998,1999 Bernd Raichle/DANTE e.V.).
		%
		%
		% \versionnumber{3.1}   \versiondate{2007/04/16}
		%
		% Information after \endinput.
		%
		%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
		%
		% \message{Latin Hyphenation Patterns `lahyph' Version 3.1 <2007/04/16>}

	%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	% 
	% For documentation see:
	% C. Beccari, 'Computer aided hyphenation for Italian and Modern
	% Latin', TUG vol. 13, n. 1, pp. 23-33 (1992)
	% 
	% see also
	% 
	% C. Beccari, 'Typesetting of ancient languages',
	% TUG vol.15, n.1, pp. 9-16 (1994)
	% 
	% In the former paper the code was described as being contained in file
	% ITALAT.TEX; this is substantially the same code, but the file has been
	% renamed LAHYPH.TEX in accordance with the ISO name for Latin and the
	% convention that all hyphenation pattern file names should be formed by the
	% agglutination of two letter language ISO code and the abbreviation HYPH.
	% 
	% A corresponding file (ITHYPH.TEX) has been extracted in order to eliminate
	% the (few) patterns specific to Latin and leave those specific to Italian;
	% ITHYPH.TEX has been further extended with many new patterns in order to
	% cope with the many neologisms and technical terms with foreign roots.
	% 
	% Should you find any word that gets hyphenated in a wrong way, please, AFTER
	% CHECKING ON A RELIABLE MODERN DICTIONARY, report to the author, preferably
	% by e-mail. Please do not report about wrong break points concerning
	% prefixes and/or suffixes; see at the bottom of this file.
	% 
	% Compared with the previous versions, this file has been extended so as to
	% cope also with the medieval Latin spelling, where the letter `V' played the
	% roles of both `U' and `V', as in the Roman times, save that the Romans used
	% only capitals. In the middle ages the availability of soft writing supports
	% and the necessity of copying books with a reasonable speed, several scripts
	% evolved in (practically) all of which there was a lower case alphabet
	% different from the upper case one, and where the lower case `v' had the
	% rounded shape of our modern lower case `u', and where the Latin diphthongs
	% `AE' and `OE', both in upper and lower case, where written as ligatures,
	% not to mention the habit of substituting them with their sound, that is a
	% simple `E'.
	% 
	% According to Leon Battista Alberti, who in 1466 wrote a book on
	% cryptography where he thoroughly analyzed the hyphenation of the Latin
	% language of his (still medieval) times, the differences from the Tuscan
	% language (the Italian language, as it was named at his time) were very
	% limited, in particular for what concerns the handling of the ascending and
	% descending diphthongs; in Central and Northern Europe, and later on in
	% North America, the Scholars perceived the above diphthongs as made of two
	% distinct vowels; the hyphenation of medieval Latin, therefore, was quite
	% different in the northern countries compared to the southern ones, at least
	% for what concerns these diphthongs. If you need hyphenation patterns for
	% medieval Latin that suite you better according to the habits of Northern
	% Europe you should resort to the hyphenation patterns prepared by Yannis
	% Haralambous (TUGboat, vol.13 n.4 (1992)).
	% 
	% 
	% 
	% PREFIXES AND SUFFIXES
	% 
	% For what concerns prefixes and suffixes, the latter are generally separated
	% according to 'natural' syllabification, while the former are generally
	% divided etimologically. In order to avoid an excessive number of patterns,
	% care has been paid to some prefixes, especially 'ex', 'trans', 'circum',
	% 'prae', but this set of patterns is NOT capable of separating the prefixes
	% in all circumstances.
	% 
	% BABEL SHORTCUTS AND FACILITIES
	% 
	% Read the documentation coming with the discription of the Latin language
	% interface of Babel in order to see the shortcuts and the facilities
	% introduced in order to facilitate the insertion of 'compound word marks'
	% which are very useful for inserting etimological break points.
	% 
	% Happy Latin and multilingual typesetting!


//============================================================================================================	
	
*/

$patgenLanguage = 'Latin';

$patgenExceptions = array();

$patgenMaxSeg = 7;

$patgen = array(
'begin'=>array(
'abl'=>'0230',
'anti'=>'00001',
'antimn'=>'0000320',
'circum'=>'0000021',
'coniun'=>'0021000',
'discine'=>'00230000',
'ex'=>'021',
'ob'=>'023',
'parai'=>'000010',
'parau'=>'000010',
'sublu'=>'002300',
'subr'=>'00230'
),
'end'=>array(
'sque'=>'23000',
'sdem'=>'23000',
'b'=>'20',
'c'=>'20',
'd'=>'20',
'f'=>'20',
'g'=>'20',
'h'=>'20',
'l'=>'20',
'm'=>'20',
'n'=>'20',
'p'=>'20',
'r'=>'20',
's'=>'20',
'st'=>'200',
't'=>'20',
'x'=>'20',
'z'=>'20'
),
'all'=>array(
'\''=>'22',
'psic'=>'32000',
'pneu'=>'32000',
'æ'=>'01',
'œ'=>'01',
'aia'=>'0100',
'aie'=>'0100',
'aio'=>'0100',
'aiu'=>'0100',
'aea'=>'0010',
'aeo'=>'0010',
'aeu'=>'0010',
'eiu'=>'0100',
'ioi'=>'0010',
'oia'=>'0100',
'oie'=>'0100',
'oio'=>'0100',
'oiu'=>'0100',
'uou'=>'0030',
'b'=>'10',
'bb'=>'200',
'bd'=>'200',
'bl'=>'020',
'bm'=>'200',
'bn'=>'200',
'br'=>'020',
'bt'=>'200',
'bs'=>'200',
'c'=>'10',
'cc'=>'200',
'ch'=>'022',
'cl'=>'020',
'cm'=>'200',
'cn'=>'200',
'cq'=>'200',
'cr'=>'020',
'cs'=>'200',
'ct'=>'200',
'cz'=>'200',
'd'=>'10',
'dd'=>'200',
'dg'=>'200',
'dm'=>'200',
'dr'=>'020',
'ds'=>'200',
'dv'=>'200',
'f'=>'10',
'ff'=>'200',
'fl'=>'020',
'fn'=>'200',
'fr'=>'020',
'ft'=>'200',
'g'=>'10',
'gg'=>'200',
'gd'=>'200',
'gf'=>'200',
'gl'=>'020',
'gm'=>'200',
'gn'=>'020',
'gr'=>'020',
'gs'=>'200',
'gv'=>'200',
'h'=>'10',
'hp'=>'200',
'ht'=>'200',
'j'=>'10',
'k'=>'10',
'kk'=>'200',
'kh'=>'022',
'l'=>'10',
'lb'=>'200',
'lc'=>'200',
'ld'=>'200',
'lf'=>'200',
'lft'=>'0320',
'lg'=>'200',
'lk'=>'200',
'll'=>'200',
'lm'=>'200',
'ln'=>'200',
'lp'=>'200',
'lq'=>'200',
'lr'=>'200',
'ls'=>'200',
'lt'=>'200',
'lv'=>'200',
'm'=>'10',
'mm'=>'200',
'mb'=>'200',
'mp'=>'200',
'ml'=>'200',
'mn'=>'200',
'mq'=>'200',
'mr'=>'200',
'mv'=>'200',
'n'=>'10',
'nb'=>'200',
'nc'=>'200',
'nd'=>'200',
'nf'=>'200',
'ng'=>'200',
'nl'=>'200',
'nm'=>'200',
'nn'=>'200',
'np'=>'200',
'nq'=>'200',
'nr'=>'200',
'ns'=>'200',
'nsm'=>'0230',
'nsf'=>'0230',
'nt'=>'200',
'nv'=>'200',
'nx'=>'200',
'p'=>'10',
'ph'=>'020',
'pl'=>'020',
'pn'=>'200',
'pp'=>'200',
'pr'=>'020',
'ps'=>'200',
'pt'=>'200',
'pz'=>'200',
'php'=>'2000',
'pht'=>'2000',
'qu'=>'102',
'r'=>'10',
'rb'=>'200',
'rc'=>'200',
'rd'=>'200',
'rf'=>'200',
'rg'=>'200',
'rh'=>'020',
'rl'=>'200',
'rm'=>'200',
'rn'=>'200',
'rp'=>'200',
'rq'=>'200',
'rr'=>'200',
'rs'=>'200',
'rt'=>'200',
'rv'=>'200',
'rz'=>'200',
's'=>'12',
'sph'=>'2300',
'ss'=>'230',
'stb'=>'2000',
'stc'=>'2000',
'std'=>'2000',
'stf'=>'2000',
'stg'=>'2000',
'stl'=>'2030',
'stm'=>'2000',
'stn'=>'2000',
'stp'=>'2000',
'stq'=>'2000',
'sts'=>'2000',
'stt'=>'2000',
'stv'=>'2000',
't'=>'10',
'tb'=>'200',
'tc'=>'200',
'td'=>'200',
'tf'=>'200',
'tg'=>'200',
'th'=>'020',
'tl'=>'020',
'tr'=>'020',
'tm'=>'200',
'tn'=>'200',
'tp'=>'200',
'tq'=>'200',
'tt'=>'200',
'tv'=>'200',
'v'=>'10',
'vl'=>'020',
'vr'=>'020',
'vv'=>'200',
'x'=>'10',
'xt'=>'200',
'xx'=>'200',
'z'=>'10',
'aua'=>'0100',
'aue'=>'0100',
'aui'=>'0100',
'auo'=>'0100',
'auu'=>'0100',
'eua'=>'0100',
'eue'=>'0100',
'eui'=>'0100',
'euo'=>'0100',
'euu'=>'0100',
'iua'=>'0100',
'iue'=>'0100',
'iui'=>'0100',
'iuo'=>'0100',
'iuu'=>'0100',
'oua'=>'0100',
'oue'=>'0100',
'oui'=>'0100',
'ouo'=>'0100',
'ouu'=>'0100',
'uua'=>'0100',
'uue'=>'0100',
'uui'=>'0100',
'uuo'=>'0100',
'uuu'=>'0100',
'alua'=>'02100',
'alue'=>'02100',
'alui'=>'02100',
'aluo'=>'02100',
'aluu'=>'02100',
'elua'=>'02100',
'elue'=>'02100',
'elui'=>'02100',
'eluo'=>'02100',
'eluu'=>'02100',
'ilua'=>'02100',
'ilue'=>'02100',
'ilui'=>'02100',
'iluo'=>'02100',
'iluu'=>'02100',
'olua'=>'02100',
'olue'=>'02100',
'olui'=>'02100',
'oluo'=>'02100',
'oluu'=>'02100',
'ulua'=>'02100',
'ulue'=>'02100',
'ului'=>'02100',
'uluo'=>'02100',
'uluu'=>'02100',
'amua'=>'02100',
'amue'=>'02100',
'amui'=>'02100',
'amuo'=>'02100',
'amuu'=>'02100',
'emua'=>'02100',
'emue'=>'02100',
'emui'=>'02100',
'emuo'=>'02100',
'emuu'=>'02100',
'imua'=>'02100',
'imue'=>'02100',
'imui'=>'02100',
'imuo'=>'02100',
'imuu'=>'02100',
'omua'=>'02100',
'omue'=>'02100',
'omui'=>'02100',
'omuo'=>'02100',
'omuu'=>'02100',
'umua'=>'02100',
'umue'=>'02100',
'umui'=>'02100',
'umuo'=>'02100',
'umuu'=>'02100',
'anua'=>'02100',
'anue'=>'02100',
'anui'=>'02100',
'anuo'=>'02100',
'anuu'=>'02100',
'enua'=>'02100',
'enue'=>'02100',
'enui'=>'02100',
'enuo'=>'02100',
'enuu'=>'02100',
'inua'=>'02100',
'inue'=>'02100',
'inui'=>'02100',
'inuo'=>'02100',
'inuu'=>'02100',
'onua'=>'02100',
'onue'=>'02100',
'onui'=>'02100',
'onuo'=>'02100',
'onuu'=>'02100',
'unua'=>'02100',
'unue'=>'02100',
'unui'=>'02100',
'unuo'=>'02100',
'unuu'=>'02100',
'arua'=>'02100',
'arue'=>'02100',
'arui'=>'02100',
'aruo'=>'02100',
'aruu'=>'02100',
'erua'=>'02100',
'erue'=>'02100',
'erui'=>'02100',
'eruo'=>'02100',
'eruu'=>'02100',
'irua'=>'02100',
'irue'=>'02100',
'irui'=>'02100',
'iruo'=>'02100',
'iruu'=>'02100',
'orua'=>'02100',
'orue'=>'02100',
'orui'=>'02100',
'oruo'=>'02100',
'oruu'=>'02100',
'urua'=>'02100',
'urue'=>'02100',
'urui'=>'02100',
'uruo'=>'02100',
'uruu'=>'02100'
)
);

?>