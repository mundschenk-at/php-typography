<?php
/*
	Project: PHP Typography
	Project URI: http://kingdesk.com/projects/php-typography/
	
	File modified to place pattern and exceptions in arrays that can be understood in php files.
	This file is released under the same copyright as the below referenced original file
	Original unmodified file is available at: http://mirror.unl.edu/ctan/language/hyph-utf8/tex/generic/hyph-utf8/patterns/
	Original file name: hyph-it.tex
	
//============================================================================================================
	ORIGINAL FILE INFO

		% This file is part of hyph-utf8 package and resulted from
		% semi-manual conversions of hyphenation patterns into UTF-8 in June 2008.
		%
		% Source: ithyph.tex (2008-03-08)
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
		%%%%%%%%%%%%%%%%%%%%%%%%%%%  file ithyph.tex  %%%%%%%%%%%%%%%%%%%%%%%%%%%%%
		%
		% Prepared by Claudio Beccari   e-mail  claudio.beccari@polito.it
		%
		%                                       Dipartimento di Elettronica
		%                                       Politecnico di Torino
		%                                       Corso Duca degli Abruzzi, 24
		%                                       10129 TORINO
		%
		% Copyright  1998, 2008 Claudio Beccari
		%
		% This program is free software; it can be redistributed and/or modified 
		% under the terms of the GNU Lesser General Public Licence,
		% as published by the Free Software Foundation, either version 2.1 of the
		% Licence or (at your option) any later version.
		%
		% \versionnumber{4.8g}   \versiondate{2008/03/08}
		%
		% These hyphenation patterns for the Italian language are supposed to comply
		% with the Recommendation UNI 6461 on hyphenation issued by the Italian
		% Standards Institution (Ente Nazionale di Unificazione UNI).  No guarantee
		% or declaration of fitness to any particular purpose is given and any
		% liability is disclaimed.
		%
		% See comments at the end of the file after the \endinput line

		% %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% Information %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
		% 
		% As the previous versions, this new set  of  patterns does  not  contain  any
		% accented  character  so  that  the hyphenation algorithm behaves properly in
		% both cases, that is with OT1 and T1 encodings.   With  the  former  encoding
		% fonts  do  not contain  accented characters,  while with the latter accented
		% characters are present and sequences such as à map directly to slot 'E0 that
		% contains 'agrave'.
		% 
		% Of course if you use T1 encoded  fonts you get the full power of the hyphen-
		% ation algorithm, while if you use OT1 encoded  fonts you  miss some possible  
		% break  points;  this  is  not a big inconvenience in Italian because:
		% 
		% 1) The Regulation UNI 6015 on  accents  specifies  that  compulsory  accents
		%    appear  only  on the ending vowel of oxitone words (parole tronche); this
		%    means that it is almost  indifferent  to have or to miss  the T1  encoded 
		%    fonts because the only difference consists in how TeX  evaluates  the end 
		%    of the word;  in practice  if you have  these special  facilities you get 
		%    'qua-li-tà', while   if  you miss them, you get 'qua-lità' (assuming that
		%    \righthyphenmin > 1).
		% 
		% 2)  Optional  accents are so rare in Italian, that if you absolutely want to
		%    use  them  in  those  rare  instances,  and  you  miss  the  T1  encoding
		%    facilities, you should also provide  explicit discretionary hyphens as in
		%    'sé\-gui\-to'.
		% 
		% There is no explicit  hyphenation  exception  list  because  these  patterns
		% proved  to  hyphenate correctly a very large set of words suitably chosen in
		% order to test them in the most heavy circumstances; these patterns were used
		% in the preparation of a number of books and no errors were discovered.
		% 
		% Nevertheless if you frequently use  technical terms that you want hyphenated
		% differently  from  what  is  normally  done  (for  example  if  you   prefer
		% etymological  hyphenation  of  prefixed  and/or  suffixed  words) you should
		% insert a specific hyphenation  list  in  the  preamble of your document, for
		% example:
		% 
		% \hyphenation{su-per-in-dut-to-re su-per-in-dut-to-ri}
		% 
		% If you use, as you should, the italan  option of the babel package, then you 
		% have available the active charater '  that allows you to put a discretionary 
		% break at a word boundary of a compound word while maintaning the hyphenation 
		% algorithm on the rest of the word. 
		% 
		% Please, read the babel package documentation.
		% 
		% Should you find any word that gets hyphenated in a wrong way, please, AFTER
		% CHECKING ON A RELIABLE MODERN DICTIONARY, report to the author, preferably
		% by e-mail.
		% 
		% 
		%                        Happy multilingual typesetting!


//============================================================================================================	
	
*/

$patgenLanguage = 'Italian';

$patgenExceptions = array();

$patgenMaxSeg = 7;

$patgen = array(
'begin'=>array(
'apn'=>'0320',
'anti'=>'00001',
'antimn'=>'0000320',
'bio'=>'0001',
'caps'=>'00430',
'circum'=>'0000021',
'contro'=>'0000001',
'discine'=>'00230000',
'exeu'=>'02100',
'frank'=>'000023',
'free'=>'00003',
'lipsa'=>'003200',
'narco'=>'000001',
'opto'=>'00001',
'ortop'=>'000032',
'para'=>'00001',
'polip'=>'000032',
'pre'=>'0001',
'ps'=>'020',
'reiscr'=>'0012000',
'share'=>'000203',
'transc'=>'0000230',
'transd'=>'0000230',
'transl'=>'0000230',
'transn'=>'0000230',
'transp'=>'0000230',
'transr'=>'0000230',
'transt'=>'0000230',
'sublu'=>'002300',
'subr'=>'00230',
'wagn'=>'00230',
'welt'=>'00021',
'c'=>'02',
'd'=>'02',
'z'=>'02'
),
'end'=>
array(
'at'=>'200',
'b'=>'20',
'c'=>'20',
'd'=>'20',
'f'=>'20',
'g'=>'20',
'h'=>'20',
'j'=>'20',
'k'=>'20',
'l'=>'20',
'l\''=>'200',
'm'=>'20',
'n'=>'20',
'p'=>'20',
'q'=>'20',
'r'=>'20',
'sh'=>'200',
's'=>'40',
's\''=>'400',
't'=>'20',
't\''=>'200',
'v'=>'20',
'v\''=>'200',
'w'=>'20',
'x'=>'20',
'z'=>'20',
'z\''=>'200'
),
'all'=>array(
'\''=>'22',
'aia'=>'0100',
'aie'=>'0100',
'aio'=>'0100',
'aiu'=>'0100',
'auo'=>'0100',
'aya'=>'0100',
'eiu'=>'0100',
'ew'=>'020',
'oia'=>'0100',
'oie'=>'0100',
'oio'=>'0100',
'oiu'=>'0100',
'b'=>'10',
'bb'=>'200',
'bc'=>'200',
'bd'=>'200',
'bf'=>'200',
'bm'=>'200',
'bn'=>'200',
'bp'=>'200',
'bs'=>'200',
'bt'=>'200',
'bv'=>'200',
'bl'=>'020',
'br'=>'020',
'b\''=>'200',
'c'=>'10',
'cb'=>'200',
'cc'=>'200',
'cd'=>'200',
'cf'=>'200',
'ck'=>'200',
'cm'=>'200',
'cn'=>'200',
'cq'=>'200',
'cs'=>'200',
'ct'=>'200',
'cz'=>'200',
'chh'=>'2000',
'ch'=>'020',
'chb'=>'2000',
'chr'=>'0020',
'chn'=>'2000',
'cl'=>'020',
'cr'=>'020',
'c\''=>'200',
'd'=>'10',
'db'=>'200',
'dd'=>'200',
'dg'=>'200',
'dl'=>'200',
'dm'=>'200',
'dn'=>'200',
'dp'=>'200',
'dr'=>'020',
'ds'=>'200',
'dt'=>'200',
'dv'=>'200',
'dw'=>'200',
'd\''=>'200',
'f'=>'10',
'fb'=>'200',
'fg'=>'200',
'ff'=>'200',
'fn'=>'200',
'fl'=>'020',
'fr'=>'020',
'fs'=>'200',
'ft'=>'200',
'f\''=>'200',
'g'=>'10',
'gb'=>'200',
'gd'=>'200',
'gf'=>'200',
'gg'=>'200',
'gh'=>'020',
'gl'=>'020',
'gm'=>'200',
'gn'=>'020',
'gp'=>'200',
'gr'=>'020',
'gs'=>'200',
'gt'=>'200',
'gv'=>'200',
'gw'=>'200',
'gz'=>'200',
'ght'=>'2020',
'g\''=>'200',
'h'=>'10',
'hb'=>'200',
'hd'=>'200',
'hh'=>'200',
'hipn'=>'00320',
'hl'=>'020',
'hm'=>'200',
'hn'=>'200',
'hr'=>'200',
'hv'=>'200',
'h\''=>'200',
'j'=>'10',
'j\''=>'200',
'k'=>'10',
'kg'=>'200',
'kf'=>'200',
'kh'=>'020',
'kk'=>'200',
'kl'=>'020',
'km'=>'200',
'kr'=>'020',
'ks'=>'200',
'kt'=>'200',
'k\''=>'200',
'l'=>'10',
'lb'=>'200',
'lc'=>'200',
'ld'=>'200',
'lf'=>'232',
'lg'=>'200',
'lh'=>'020',
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
'lw'=>'200',
'lz'=>'200',
'l\'\''=>'2000',
'm'=>'10',
'mb'=>'200',
'mc'=>'200',
'mf'=>'200',
'ml'=>'200',
'mm'=>'200',
'mn'=>'200',
'mp'=>'200',
'mq'=>'200',
'mr'=>'200',
'ms'=>'200',
'mt'=>'200',
'mv'=>'200',
'mw'=>'200',
'm\''=>'200',
'n'=>'10',
'nb'=>'200',
'nc'=>'200',
'nd'=>'200',
'nf'=>'200',
'ng'=>'200',
'nk'=>'200',
'nl'=>'200',
'nm'=>'200',
'nn'=>'200',
'np'=>'200',
'nq'=>'200',
'nr'=>'200',
'ns'=>'200',
'nsfer'=>'023000',
'nt'=>'200',
'nv'=>'200',
'nz'=>'200',
'ngn'=>'0230',
'nheit'=>'200000',
'n\''=>'200',
'p'=>'10',
'pd'=>'200',
'ph'=>'020',
'pl'=>'020',
'pn'=>'200',
'pne'=>'3200',
'pp'=>'200',
'pr'=>'020',
'ps'=>'200',
'psic'=>'32000',
'pt'=>'200',
'pz'=>'200',
'p\''=>'200',
'q'=>'10',
'qq'=>'200',
'q\''=>'200',
'r'=>'10',
'rb'=>'200',
'rc'=>'200',
'rd'=>'200',
'rf'=>'200',
'rh'=>'020',
'rg'=>'200',
'rk'=>'200',
'rl'=>'200',
'rm'=>'200',
'rn'=>'200',
'rp'=>'200',
'rq'=>'200',
'rr'=>'200',
'rs'=>'200',
'rt'=>'200',
'rts'=>'0223',
'rv'=>'200',
'rx'=>'200',
'rw'=>'200',
'rz'=>'200',
'r\''=>'200',
's'=>'12',
'shm'=>'2000',
'sh\''=>'2000',
'ss'=>'230',
'ssm'=>'0430',
'spn'=>'2320',
'stb'=>'2000',
'stc'=>'2000',
'std'=>'2000',
'stf'=>'2000',
'stg'=>'2000',
'stm'=>'2000',
'stn'=>'2000',
'stp'=>'2000',
'sts'=>'2000',
'stt'=>'2000',
'stv'=>'2000',
'sz'=>'200',
's\'\''=>'4000',
't'=>'10',
'tb'=>'200',
'tc'=>'200',
'td'=>'200',
'tf'=>'200',
'tg'=>'200',
'th'=>'020',
'tl'=>'020',
'tm'=>'200',
'tn'=>'200',
'tp'=>'200',
'tr'=>'020',
'ts'=>'020',
'tsch'=>'32000',
'tt'=>'200',
'tts'=>'0230',
'tv'=>'200',
'tw'=>'200',
'tz'=>'020',
'tzk'=>'2000',
'tzs'=>'0020',
't\'\''=>'2000',
'v'=>'10',
'vc'=>'200',
'vl'=>'020',
'vr'=>'020',
'vv'=>'200',
'v\'\''=>'2000',
'w'=>'10',
'wh'=>'020',
'war'=>'0020',
'wy'=>'210',
'w\''=>'200',
'x'=>'10',
'xb'=>'200',
'xc'=>'200',
'xf'=>'200',
'xh'=>'200',
'xm'=>'200',
'xp'=>'200',
'xt'=>'200',
'xw'=>'200',
'x\''=>'200',
'you'=>'0100',
'yi'=>'010',
'z'=>'10',
'zb'=>'200',
'zd'=>'200',
'zl'=>'200',
'zn'=>'200',
'zp'=>'200',
'zt'=>'200',
'zs'=>'200',
'zv'=>'200',
'zz'=>'200',
'z\'\''=>'2000'
)
);

?>