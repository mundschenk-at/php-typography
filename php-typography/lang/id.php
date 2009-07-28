<?php
/*
	Project: PHP Typography
	Project URI: http://kingdesk.com/projects/php-typography/
	
	File modified to place pattern and exceptions in arrays that can be understood in php files.
	This file is released under the same copyright as the below referenced original file
	Original unmodified file is available at: http://mirror.unl.edu/ctan/language/hyph-utf8/tex/generic/hyph-utf8/patterns/
	Original file name: hyph-id.tex
	
//============================================================================================================
	ORIGINAL FILE INFO

		% This file is part of hyph-utf8 package and resulted from
		% semi-manual conversions of hyphenation patterns into UTF-8 in June 2008.
		%
		% Source: inhyph.tex (1997-09-19)
		% Author: Jörg Knappen <knappen@vkpmzd.kph.uni-mainz.de>, Terry Mart <mart@kph.uni-mainz.de>
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
		% inhyph.tex 
		% Version 1.3 19-SEP-1997
		%
		% Hyphenation patterns for bahasa indonesia (probably also usable
		% for bahasa melayu)
		%
		% (c) Copyright 1996, 1997 Jörg Knappen and Terry Mart
		% 
		% This patterns are free software according to the GNU General Public 
		% licence version 2, June 1991.
		%
		% Please read the GNU licence for details. If you don't receive a GNU
		% licence with these patterns, you can obtain it from 
		%
		%                          Free Software Foundation, Inc.
		%                          675 Mass Ave, Cambridge, MA 02139, USA
		%
		% If you make any changes to this file, please rename it so that it
		% cannot be confused with the original one, and change the contact
		% address for bug reports and suggestions.
		%
		% For bug reports, improvements, and suggestions, contact
		%
		% Jörg Knappen
		% jk Unternehmensberatung
		% Barbarossaring 43
		% 55118 Mainz
		%
		% knappen@vkpmzd.kph.uni-mainz.de
		%
		% or:
		% Terry Mart
		%
		% Institut fuer Kernphysik
		% Universitaet Mainz
		% 55099 Mainz
		% Germany
		%
		% phone : +49 6131 395174
		% fax   : +49 6131 395474
		% email : mart@kph.uni-mainz.de
		%
		%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*
		%
		% The patterns are best used with the following parameters
		%
		% \lefthyphenmin=2 \righthyphenmin=2 %
		%
		%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*%*


//============================================================================================================	
	
*/

$patgenLanguage = 'Indonesian';

$patgenExceptions = array(
'berabe'=>'be-ra-be',
'berahi'=>'be-ra-hi',
'berak'=>'be-rak',
'beranda'=>'be-ran-da',
'berandal'=>'be-ran-dal',
'berang'=>'be-rang',
'berangasan'=>'be-ra-ngas-an',
'berangsang'=>'be-rang-sang',
'berangus'=>'be-ra-ngus',
'berani'=>'be-ra-ni',
'berantakan'=>'be-ran-tak-an',
'berantam'=>'be-ran-tam',
'berantas'=>'be-ran-tas',
'berapa'=>'be-ra-pa',
'beras'=>'be-ras',
'berendeng'=>'be-ren-deng',
'berengut'=>'be-re-ngut',
'bererot'=>'be-re-rot',
'beres'=>'be-res',
'berewok'=>'be-re-wok',
'beri'=>'be-ri',
'beringas'=>'be-ri-ngas',
'berisik'=>'be-ri-sik',
'berita'=>'be-ri-ta',
'berok'=>'be-rok',
'berondong'=>'be-ron-dong',
'berontak'=>'be-ron-tak',
'berudu'=>'be-ru-du',
'beruk'=>'be-ruk',
'beruntun'=>'be-run-tun',
'pengekspor'=>'peng-eks-por',
'pengimpor'=>'peng-im-por',
'tera'=>'te-ra',
'terang'=>'te-rang',
'teras'=>'te-ras',
'terasi'=>'te-ra-si',
'teratai'=>'te-ra-tai',
'terawang'=>'te-ra-wang',
'teraweh'=>'te-ra-weh',
'teriak'=>'te-ri-ak',
'terigu'=>'te-ri-gu',
'terik'=>'te-rik',
'terima'=>'te-ri-ma',
'teripang'=>'te-ri-pang',
'terobos'=>'te-ro-bos',
'terobosan'=>'te-ro-bos-an',
'teromol'=>'te-ro-mol',
'terompah'=>'te-rom-pah',
'terompet'=>'te-rom-pet',
'teropong'=>'te-ro-pong',
'terowongan'=>'te-ro-wong-an',
'terubuk'=>'te-ru-buk',
'teruna'=>'te-ru-na',
'terus'=>'te-rus',
'terusi'=>'te-ru-si'
);

$patgenMaxSeg = 6;

$patgen = array(
'begin'=>array(
'ber'=>'0023',
'ter'=>'0023',
'meng'=>'00203',
'per'=>'0023',
'atau'=>'02020',
'tangan'=>'0030400',
'lengan'=>'0030400',
'jangan'=>'0030400',
'mangan'=>'0030400',
'pangan'=>'0030400',
'ringan'=>'0030400',
'dengan'=>'0030400'
),
'end'=>array(
'ng'=>'200',
'ny'=>'200',
'ban'=>'2100',
'can'=>'2100',
'dan'=>'2100',
'fan'=>'2100',
'gan'=>'2100',
'han'=>'2100',
'jan'=>'2100',
'kan'=>'2100',
'lan'=>'2100',
'man'=>'2100',
'ngan'=>'20100',
'nan'=>'2100',
'pan'=>'2100',
'ran'=>'2100',
'san'=>'2100',
'tan'=>'2100',
'van'=>'2100',
'zan'=>'2100',
'an'=>'300'
),
'all'=>array(
'ck'=>'210',
'cn'=>'210',
'dk'=>'210',
'dn'=>'210',
'dp'=>'210',
'fd'=>'210',
'fk'=>'210',
'fn'=>'210',
'ft'=>'210',
'gg'=>'210',
'gk'=>'210',
'gn'=>'210',
'hk'=>'210',
'hl'=>'210',
'hm'=>'210',
'hn'=>'210',
'hw'=>'210',
'jk'=>'210',
'jn'=>'210',
'kb'=>'210',
'kk'=>'210',
'km'=>'210',
'kn'=>'210',
'kr'=>'210',
'ks'=>'210',
'kt'=>'210',
'lb'=>'210',
'lf'=>'210',
'lg'=>'210',
'lh'=>'210',
'lk'=>'210',
'lm'=>'210',
'ln'=>'210',
'ls'=>'210',
'lt'=>'210',
'lq'=>'210',
'mb'=>'210',
'mk'=>'210',
'ml'=>'210',
'mm'=>'210',
'mn'=>'210',
'mp'=>'210',
'mr'=>'210',
'ms'=>'210',
'nc'=>'210',
'nd'=>'210',
'nf'=>'210',
'nj'=>'210',
'nk'=>'210',
'nn'=>'210',
'np'=>'210',
'ns'=>'210',
'nt'=>'210',
'nv'=>'210',
'pk'=>'210',
'pn'=>'210',
'pp'=>'210',
'pr'=>'210',
'pt'=>'210',
'rb'=>'210',
'rc'=>'210',
'rf'=>'210',
'rg'=>'210',
'rh'=>'210',
'rj'=>'210',
'rk'=>'210',
'rl'=>'210',
'rm'=>'210',
'rn'=>'210',
'rp'=>'210',
'rr'=>'210',
'rs'=>'210',
'rt'=>'210',
'rw'=>'210',
'ry'=>'210',
'sb'=>'210',
'sk'=>'210',
'sl'=>'210',
'sm'=>'210',
'sn'=>'210',
'sp'=>'210',
'sr'=>'210',
'ss'=>'210',
'st'=>'210',
'sw'=>'210',
'tk'=>'210',
'tl'=>'210',
'tn'=>'210',
'tt'=>'210',
'wt'=>'210',
'ngg'=>'2010',
'ngh'=>'2010',
'ngk'=>'2010',
'ngn'=>'2010',
'ngs'=>'2010',
'nst'=>'2320',
'ion'=>'0210',
'air'=>'0200',
'bagai'=>'101020'
)
);

?>