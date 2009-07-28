<?php
/*
	Project: PHP Typography
	Project URI: http://kingdesk.com/projects/php-typography/
	
	File modified to place pattern and exceptions in arrays that can be understood in php files.
	This file is released under the same copyright as the below referenced original file
	Original unmodified file is available at: http://mirror.unl.edu/ctan/language/hyph-utf8/tex/generic/hyph-utf8/patterns/
	Original file name: hyph-zh-latn.tex
	
//============================================================================================================
	ORIGINAL FILE INFO

		% This file is part of hyph-utf8 package and resulted from
		% semi-manual conversions of hyphenation patterns into UTF-8 in June 2008.
		%
		% Source: pyhyph.tex (yyyy-mm-dd)
		% Author: Werner Lemberg <wl at gnu.org>
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
		% This is the file pyhyph.tex of the CJK package
		%   for hyphenating Chinese pinyin syllables.
		% 
		% created by Werner Lemberg <wl@gnu.org>
		% 
		% Version 4.8.0 (22-May-2008)
		% 
		% Copyright (C) 1994-2008  Werner Lemberg <wl@gnu.org>
		% 
		% This program is free software; you can redistribute it and/or modify
		% it under the terms of the GNU General Public License as published by
		% the Free Software Foundation; either version 2 of the License, or
		% (at your option) any later version.
		% 
		% This program is distributed in the hope that it will be useful,
		% but WITHOUT ANY WARRANTY; without even the implied warranty of
		% MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
		% GNU General Public License for more details.
		% 
		% You should have received a copy of the GNU General Public License
		% along with this program in doc/COPYING; if not, write to the Free
		% Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston,
		% MA 02110-1301 USA
		% 
		% \message{Hyphenation patterns for unaccented pinyin syllables (CJK 4.7.0)}


//============================================================================================================	
	
*/

$patgenLanguage = 'Chinese pinyin (Latin)';

$patgenExceptions = array();

$patgenMaxSeg = 2;

$patgen = array(
'begin'=>array(),
'end'=>array(),
'all'=>array(
'ab'=>'010',
'ac'=>'010',
'ad'=>'010',
'af'=>'010',
'ag'=>'010',
'ah'=>'010',
'aj'=>'010',
'ak'=>'010',
'al'=>'010',
'am'=>'010',
'ap'=>'010',
'aq'=>'010',
'ar'=>'010',
'as'=>'010',
'at'=>'010',
'aw'=>'010',
'ax'=>'010',
'ay'=>'010',
'az'=>'010',
'eb'=>'010',
'ec'=>'010',
'ed'=>'010',
'ef'=>'010',
'eg'=>'010',
'eh'=>'010',
'ej'=>'010',
'ek'=>'010',
'el'=>'010',
'em'=>'010',
'ep'=>'010',
'eq'=>'010',
'es'=>'010',
'et'=>'010',
'ew'=>'010',
'ex'=>'010',
'ey'=>'010',
'ez'=>'010',
'ga'=>'100',
'gb'=>'010',
'gc'=>'010',
'gd'=>'010',
'ge'=>'100',
'gf'=>'010',
'gg'=>'010',
'gh'=>'010',
'gj'=>'010',
'gk'=>'010',
'gl'=>'010',
'gm'=>'010',
'gn'=>'010',
'go'=>'100',
'gp'=>'010',
'gq'=>'010',
'gr'=>'010',
'gs'=>'010',
'gt'=>'010',
'gu'=>'100',
'gw'=>'010',
'gx'=>'010',
'gy'=>'010',
'gz'=>'010',
'ib'=>'010',
'ic'=>'010',
'id'=>'010',
'if'=>'010',
'ig'=>'010',
'ih'=>'010',
'ij'=>'010',
'ik'=>'010',
'il'=>'010',
'im'=>'010',
'ip'=>'010',
'iq'=>'010',
'ir'=>'010',
'is'=>'010',
'it'=>'010',
'iw'=>'010',
'ix'=>'010',
'iy'=>'010',
'iz'=>'010',
'na'=>'100',
'nb'=>'010',
'nc'=>'010',
'nd'=>'010',
'ne'=>'100',
'nf'=>'010',
'nh'=>'010',
'ni'=>'100',
'nj'=>'010',
'nk'=>'010',
'nl'=>'010',
'nm'=>'010',
'nn'=>'010',
'no'=>'100',
'np'=>'010',
'nq'=>'010',
'nr'=>'010',
'ns'=>'010',
'nt'=>'010',
'nu'=>'100',
'nü'=>'100',
'nw'=>'010',
'nx'=>'010',
'ny'=>'010',
'nz'=>'010',
'ob'=>'010',
'oc'=>'010',
'od'=>'010',
'of'=>'010',
'og'=>'010',
'oh'=>'010',
'oj'=>'010',
'ok'=>'010',
'ol'=>'010',
'om'=>'010',
'op'=>'010',
'oq'=>'010',
'or'=>'010',
'os'=>'010',
'ot'=>'010',
'ow'=>'010',
'ox'=>'010',
'oy'=>'010',
'oz'=>'010',
'ra'=>'100',
'rb'=>'010',
'rc'=>'010',
'rd'=>'010',
're'=>'100',
'rf'=>'010',
'rg'=>'010',
'rh'=>'010',
'ri'=>'100',
'rj'=>'010',
'rk'=>'010',
'rl'=>'010',
'rm'=>'010',
'rn'=>'010',
'ro'=>'100',
'rp'=>'010',
'rq'=>'010',
'rr'=>'010',
'rs'=>'010',
'rt'=>'010',
'ru'=>'100',
'rw'=>'010',
'rx'=>'010',
'ry'=>'010',
'rz'=>'010',
'ub'=>'010',
'uc'=>'010',
'ud'=>'010',
'uf'=>'010',
'ug'=>'010',
'uh'=>'010',
'uj'=>'010',
'uk'=>'010',
'ul'=>'010',
'um'=>'010',
'up'=>'010',
'uq'=>'010',
'ur'=>'010',
'us'=>'010',
'ut'=>'010',
'uw'=>'010',
'ux'=>'010',
'uy'=>'010',
'uz'=>'010',
'üb'=>'010',
'üc'=>'010',
'üd'=>'010',
'üf'=>'010',
'üg'=>'010',
'üh'=>'010',
'üj'=>'010',
'ük'=>'010',
'ül'=>'010',
'üm'=>'010',
'ün'=>'010',
'üp'=>'010',
'üq'=>'010',
'ür'=>'010',
'üs'=>'010',
'üt'=>'010',
'üw'=>'010',
'üx'=>'010',
'üy'=>'010',
'üz'=>'010',
"'a"=>'010',
"'e"=>'010',
"'o"=>'010'
)
);

?>