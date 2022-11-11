<?php
/**
 *  This file is part of PHP-Typography.
 *
 *  Copyright 2022 Peter Putzer.
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  of the License, or ( at your option ) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License along
 *  with this program; if not, write to the Free Software Foundation, Inc.,
 *  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 *  @package mundschenk-at/php-typography/tests
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace PHP_Typography\Tests\Benchmarks;

/**
 * Processing benchmark.
 *
 * @Iterations(2)
 * @Revs(1000)
 * @OutputTimeUnit("milliseconds", precision=1)
 */
class Detect_Encoding_Bench {

	const SAMPLE = <<<EOT
	Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec mattis erat at dolor fringilla, sed feugiat libero tempus. Curabitur condimentum purus vitä bibendum fringilla. Integer facilisis maximus accumsan. Donec dignissim nec ex ut molestie. Vivamus scelerisque dui vitä dictum laoreet. Duis facilisis fermentum facilisis. Aliquam iaculis sit amet metus a facilisis. Integer sit amet bibendum odio. Quisque purus magna, imperdiet eu vestibulum quis, posuere quis tellus. Sed hendrerit justo id scelerisque elementum. Vestibulum est sem, dictum eget pellentesque finibus, tempor vel nisi. Etiam posuere vehicula magna, vitä fringilla risus efficitur a. Nullam mauris quam, congue vitä dapibus non, interdum ac turpis.

	Morbi vulputate, nibh in fermentum maximus, arcu justo tincidunt enim, bibendum pharetra magna felis sit amet tellus. Cras a urna vel ante luctus rutrum in consequat ante. Aenean lorem mi, congue sit amet porttitor et, scelerisque vitä nulla. Nam nulla magna, hendrerit at ullamcorper at, tincidunt in velit. Mäcenas at nulla vel massa hendrerit vestibulum at vel nibh. Mäcenas lobortis quis nibh id convallis. Pellentesque molestie non nisi eget maximus.

	Quisque nec neque eu magna aliquam interdum eget quis massa. Ut tempus enim non justo auctor, sit amet maximus dolor rhoncus. Curabitur non massa eget felis rhoncus viverra quis eu neque. Integer mollis est nec ligula scelerisque porta. Quisque gravida ultrices tempus. Suspendisse et nunc neque. Donec et mi in felis fermentum ullamcorper quis pretium risus. Cras vel urna quis libero vulputate lacinia. Pellentesque quis tincidunt ipsum, vel posuere risus. Nunc quis ligula augue.

	Aenean vulputate, erat in pellentesque mollis, leo ante placerat libero, sit amet lobortis odio metus sed nulla. Vestibulum at purus non lacus rhoncus pharetra condimentum eu est. Phasellus ut est elementum, pulvinar nisl sed, finibus ligula. Fusce placerat quam et diam lobortis, eu placerat purus tincidunt. Phasellus odio ligula, pharetra ac mauris ac, dapibus placerat risus. Morbi volutpat at metus et tincidunt. In ut nisi iaculis, volutpat nisi et, suscipit odio. In semper tellus sit amet porttitor semper. Fusce tincidunt est non dui egestas sollicitudin. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Sed aliquam, magna sed efficitur viverra, nibh sem mollis ipsum, nec sagittis est ante sed libero. Ut interdum ligula sed lorem consequat, eget dictum tellus scelerisque. Proin eget cursus mauris, sit amet mollis urna. Sed ut euismod orci.

	Quisque blandit, elit a vulputate consequat, ipsum diam volutpat eros, quis venenatis enim nunc ac risus. Quisque a urna facilisis, tincidunt diam ac, pellentesque lorem. Ut iaculis nunc non dui aliquam pulvinar. Morbi faucibus fermentum nunc, at blandit risus consectetur id. Nam ullamcorper pretium purus eget euismod. Duis ut nisi malesuada, porttitor erat ac, porttitor justo. Aenean eget finibus sem. Nam pulvinar faucibus orci vitä vehicula.

	Pellentesque gravida, nisl at aliquet lacinia, eros mi consectetur mauris, quis feugiat purus nunc sed felis. Aliquam sed arcu non arcu porta placerat. Cras sodales semper varius. Fusce leo nulla, condimentum finibus tincidunt id, varius eget magna. Curabitur eu dui non diam molestie dignissim at nec magna. Quisque posuere lorem vel tellus euismod, eu tincidunt tellus tincidunt. Integer ac vestibulum nisi. Curabitur eleifend turpis et pretium interdum. Morbi lobortis consequat nunc, in feugiat elit rutrum vitä. Curabitur vitä dui dolor. Aliquam ultrices augue non auctor convallis. Cras mattis aliquam ipsum. Mauris sollicitudin sit amet mauris et aliquet. Aenean tincidunt est quis turpis venenatis congue.

	Donec at dignissim nulla. Fusce eu semper diam, ultrices congue ante. Ut est enim, faucibus sed nunc id, auctor euismod lectus. Nulla sit amet fermentum augue. Präsent hendrerit, neque nec tincidunt mollis, felis sapien dapibus arcu, euismod porttitor magna ex vitä ligula. Sed erat nibh, elementum non dolor ac, ornare semper elit. Sed suscipit non sapien sed pharetra. Nunc eleifend est nec dolor suscipit, et volutpat elit aliquam. Aliquam eget dapibus arcu.

	Integer vitä urna at nisi consequat euismod id vitä enim. Cras efficitur arcu ex. Pellentesque volutpat nunc et lorem efficitur dignissim. Sed ipsum turpis, ullamcorper non consequat id, sagittis a neque. Nam vehicula non ipsum non blandit. Fusce et dolor elementum, maximus felis pharetra, posuere nibh. Etiam ut nibh venenatis tellus vehicula ultrices. Vivamus mollis et nisl eu commodo. Vivamus ipsum libero, malesuada id malesuada aliquet, bibendum et augue. Etiam sed velit semper, elementum sapien id, ullamcorper nulla. Suspendisse nec egestas ante.

	Donec eu urna et odio euismod faucibus. Duis quis mauris imperdiet, egestas turpis et, varius ipsum. Aenean vitä orci ut erat aliquam aliquam. Sed mattis dolor lacinia justo scelerisque, non sollicitudin mauris varius. Donec eleifend porta mi, nec fringilla nunc rhoncus ac. Nam placerat erat tortor, ut convallis dui efficitur in. Integer id gravida massa. Ut vitä nulla ullamcorper, blandit ante quis, hendrerit lacus. Aliquam blandit ligula eu enim venenatis blandit. Aenean ullamcorper, mauris a tempus vulputate, quam massa pellentesque arcu, pulvinar vestibulum lorem magna quis lectus. Präsent nec ipsum porttitor, sollicitudin lacus interdum, pellentesque metus. Sed tempus hendrerit dolor ac hendrerit. Morbi vel risus ac lorem efficitur dignissim.

	Sed et sollicitudin metus. Suspendisse vel facilisis odio, sit amet dapibus neque. Etiam id mattis dui. Pellentesque eu ex blandit, porta tellus semper, tristique nisi. In nunc erat, ullamcorper ac elementum quis, pretium sit amet turpis. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse pharetra dignissim ex, et fringilla ligula eleifend ut. Vivamus consequat sodales luctus. Donec vestibulum turpis sit amet orci aliquet, id ultrices enim pharetra. In aliquet odio id ipsum aliquet malesuada. Sed maximus scelerisque lorem, at sollicitudin magna maximus ut. Nulla eu dictum metus. Donec in nulla nec nibh fermentum tincidunt.

	Vivamus et nisl purus. Duis quis scelerisque sapien. Ut dapibus laoreet mattis. Vivamus consequat eget risus nec eleifend. Sed non justo et arcu sodales fringilla. Präsent quam est, commodo at felis ut, ullamcorper ultrices tellus. Phasellus vitä metus pellentesque, aliquam nisl sit amet, rutrum nulla.

	Aenean vestibulum vehicula purus eget iaculis. Integer massa dui, finibus ac sem vitä, scelerisque mattis quam. Morbi sed iaculis metus. Ut nisl metus, pulvinar ut vestibulum vitä, egestas accumsan felis. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenäos. Nullam tincidunt nunc ut risus pellentesque dictum. Aliquam nec risus est. Sed sed consequat sapien. Cras eget velit dapibus, vestibulum leo eu, bibendum magna. Duis fermentum ligula magna, non aliquam metus consequat non. Cras malesuada massa sit amet ante sodales molestie. Ut hendrerit lacinia convallis. Nullam vel congue nisl, et hendrerit ipsum. Nullam rhoncus consequat iaculis. Nam egestas cursus enim, vel lobortis elit lacinia eget.

	Duis arcu ante, accumsan nec imperdiet quis, rhoncus sit amet libero. Duis mattis, nulla a condimentum varius, risus libero placerat ante, et pretium dui ex et ipsum. Cras lobortis lorem ac metus eleifend, at feugiat ante laoreet. Aenean non cursus metus, quis aliquam ante. Nullam libero ligula, molestie in velit id, pharetra varius nisl. Vivamus purus odio, maximus rutrum volutpat ac, ornare a lectus. Cras venenatis enim varius elit ullamcorper, eget consequat ipsum consequat. Präsent congue, orci vel interdum sagittis, libero felis bibendum arcu, dignissim efficitur ligula diam et mi.

	Interdum et malesuada fames ac ante ipsum primis in faucibus. Donec pulvinar consectetur arcu, in sagittis lacus gravida eget. Mäcenas ut nisi non sem condimentum pretium ullamcorper sed ex. Morbi feugiat volutpat nisl non tempor. Mauris euismod efficitur ligula, in dignissim enim vulputate ut. Mäcenas ante elit, sagittis a massa eu, ornare porttitor velit. Sed cursus nulla accumsan aliquet interdum. Proin elementum diam vel condimentum dapibus. Vivamus tempor, libero non ornare volutpat, tellus lacus pulvinar massa, sit amet dictum dui ante a est. Aliquam in tincidunt erat, et facilisis nunc. Morbi tincidunt pretium erat, non ultrices neque commodo et. Aenean consequat nibh at lectus vehicula luctus.

	Sed venenatis semper consectetur. Pellentesque aliquet aliquet tellus, nec sollicitudin quam ultrices vitä. Vivamus faucibus viverra diam. Aliquam mattis erat eu consectetur imperdiet. Ut at nulla eu tellus rhoncus semper sit amet vel magna. Donec id sem tincidunt, venenatis felis sed, porttitor neque. Sed pellentesque viverra hendrerit.

	Nullam quis sagittis dolor, vel maximus nunc. Ut id suscipit dolor. Suspendisse fermentum a lorem nec cursus. Cras tincidunt varius metus in pellentesque. Sed placerat, ipsum pharetra lobortis maximus, libero lorem suscipit nibh, ut molestie neque nibh vel elit. Nullam et vulputate enim. Cras commodo ante cursus, placerat nulla id, venenatis nisl. Nulla a massa mauris. Aenean nec suscipit quam. Ut ullamcorper a elit tincidunt ullamcorper. Präsent ac laoreet mauris. Präsent semper mi sit amet nunc blandit bibendum.

	Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curä; Nullam orci velit, sollicitudin ut tristique lobortis, facilisis eget erat. Mauris tincidunt tempor turpis. Suspendisse potenti. Nulla quis leo ut dui bibendum tincidunt ac id velit. Pellentesque sodales ut dui sed euismod. Aliquam mollis urna quis nibh dapibus sodales. Sed volutpat suscipit est non hendrerit. Aenean laoreet tincidunt enim sodales commodo. Nullam mattis odio in purus dapibus faucibus.

	Fusce finibus justo in elit auctor accumsan. Vestibulum consequat, quam non suscipit condimentum, dolor est condimentum ex, non gravida odio ante at arcu. Nullam a turpis est. Morbi ultrices orci a finibus pretium. Mauris faucibus sem sed pharetra maximus. Etiam auctor ex at nisl auctor, a porta sapien dictum. Vestibulum nulla velit, accumsan ac dignissim sit amet, pharetra nec est. Proin feugiat rhoncus lacus vel porta.

	Mäcenas mattis dolor urna, et hendrerit nunc condimentum id. Präsent tortor nibh, tristique semper tristique a, vestibulum luctus ligula. Curabitur sed tortor non magna mattis porttitor. Proin vitä nulla facilisis, sollicitudin nulla sed, dignissim felis. Pellentesque eget lacus hendrerit, feugiat mi quis, feugiat odio. Morbi finibus tempus fringilla. Ut pellentesque purus elit, eget tempus dui maximus vel. Quisque cursus aliquet bibendum. Proin tincidunt, tortor ut ullamcorper hendrerit, tortor magna molestie nulla, eget finibus leo diam et lectus. Donec dictum ultricies justo, quis convallis sem varius at. Phasellus nec luctus tortor.

	Präsent aliquam congue quam sed congue. Duis vitä porttitor leo, ac ullamcorper purus. Mäcenas tempus mollis turpis non faucibus. Mauris placerat lobortis sagittis. Nam dapibus ut quam ac vulputate. Mauris mi urna, consectetur non dapibus id, varius quis odio. Pellentesque sed congue est. Integer convallis cursus orci, vitä imperdiet quam ullamcorper fringilla. Integer ut viverra magna. Phasellus blandit velit velit, non dapibus libero sollicitudin condimentum. Aliquam tristique ornare nisl, eu rhoncus quam semper id. Nulla facilisis mi non imperdiet faucibus. Aenean mollis magna id urna gravida mollis. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curä; Nam et urna viverra, tempus orci sed, euismod ligula.
EOT;

	const SAMPLE_ASCII = <<<EOT
	Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec mattis erat at dolor fringilla, sed feugiat libero tempus. Curabitur condimentum purus vitae bibendum fringilla. Integer facilisis maximus accumsan. Donec dignissim nec ex ut molestie. Vivamus scelerisque dui vitae dictum laoreet. Duis facilisis fermentum facilisis. Aliquam iaculis sit amet metus a facilisis. Integer sit amet bibendum odio. Quisque purus magna, imperdiet eu vestibulum quis, posuere quis tellus. Sed hendrerit justo id scelerisque elementum. Vestibulum est sem, dictum eget pellentesque finibus, tempor vel nisi. Etiam posuere vehicula magna, vitae fringilla risus efficitur a. Nullam mauris quam, congue vitae dapibus non, interdum ac turpis.

	Morbi vulputate, nibh in fermentum maximus, arcu justo tincidunt enim, bibendum pharetra magna felis sit amet tellus. Cras a urna vel ante luctus rutrum in consequat ante. Aenean lorem mi, congue sit amet porttitor et, scelerisque vitae nulla. Nam nulla magna, hendrerit at ullamcorper at, tincidunt in velit. Maecenas at nulla vel massa hendrerit vestibulum at vel nibh. Maecenas lobortis quis nibh id convallis. Pellentesque molestie non nisi eget maximus.

	Quisque nec neque eu magna aliquam interdum eget quis massa. Ut tempus enim non justo auctor, sit amet maximus dolor rhoncus. Curabitur non massa eget felis rhoncus viverra quis eu neque. Integer mollis est nec ligula scelerisque porta. Quisque gravida ultrices tempus. Suspendisse et nunc neque. Donec et mi in felis fermentum ullamcorper quis pretium risus. Cras vel urna quis libero vulputate lacinia. Pellentesque quis tincidunt ipsum, vel posuere risus. Nunc quis ligula augue.

	Aenean vulputate, erat in pellentesque mollis, leo ante placerat libero, sit amet lobortis odio metus sed nulla. Vestibulum at purus non lacus rhoncus pharetra condimentum eu est. Phasellus ut est elementum, pulvinar nisl sed, finibus ligula. Fusce placerat quam et diam lobortis, eu placerat purus tincidunt. Phasellus odio ligula, pharetra ac mauris ac, dapibus placerat risus. Morbi volutpat at metus et tincidunt. In ut nisi iaculis, volutpat nisi et, suscipit odio. In semper tellus sit amet porttitor semper. Fusce tincidunt est non dui egestas sollicitudin. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Sed aliquam, magna sed efficitur viverra, nibh sem mollis ipsum, nec sagittis est ante sed libero. Ut interdum ligula sed lorem consequat, eget dictum tellus scelerisque. Proin eget cursus mauris, sit amet mollis urna. Sed ut euismod orci.

	Quisque blandit, elit a vulputate consequat, ipsum diam volutpat eros, quis venenatis enim nunc ac risus. Quisque a urna facilisis, tincidunt diam ac, pellentesque lorem. Ut iaculis nunc non dui aliquam pulvinar. Morbi faucibus fermentum nunc, at blandit risus consectetur id. Nam ullamcorper pretium purus eget euismod. Duis ut nisi malesuada, porttitor erat ac, porttitor justo. Aenean eget finibus sem. Nam pulvinar faucibus orci vitae vehicula.

	Pellentesque gravida, nisl at aliquet lacinia, eros mi consectetur mauris, quis feugiat purus nunc sed felis. Aliquam sed arcu non arcu porta placerat. Cras sodales semper varius. Fusce leo nulla, condimentum finibus tincidunt id, varius eget magna. Curabitur eu dui non diam molestie dignissim at nec magna. Quisque posuere lorem vel tellus euismod, eu tincidunt tellus tincidunt. Integer ac vestibulum nisi. Curabitur eleifend turpis et pretium interdum. Morbi lobortis consequat nunc, in feugiat elit rutrum vitae. Curabitur vitae dui dolor. Aliquam ultrices augue non auctor convallis. Cras mattis aliquam ipsum. Mauris sollicitudin sit amet mauris et aliquet. Aenean tincidunt est quis turpis venenatis congue.

	Donec at dignissim nulla. Fusce eu semper diam, ultrices congue ante. Ut est enim, faucibus sed nunc id, auctor euismod lectus. Nulla sit amet fermentum augue. Praesent hendrerit, neque nec tincidunt mollis, felis sapien dapibus arcu, euismod porttitor magna ex vitae ligula. Sed erat nibh, elementum non dolor ac, ornare semper elit. Sed suscipit non sapien sed pharetra. Nunc eleifend est nec dolor suscipit, et volutpat elit aliquam. Aliquam eget dapibus arcu.

	Integer vitae urna at nisi consequat euismod id vitae enim. Cras efficitur arcu ex. Pellentesque volutpat nunc et lorem efficitur dignissim. Sed ipsum turpis, ullamcorper non consequat id, sagittis a neque. Nam vehicula non ipsum non blandit. Fusce et dolor elementum, maximus felis pharetra, posuere nibh. Etiam ut nibh venenatis tellus vehicula ultrices. Vivamus mollis et nisl eu commodo. Vivamus ipsum libero, malesuada id malesuada aliquet, bibendum et augue. Etiam sed velit semper, elementum sapien id, ullamcorper nulla. Suspendisse nec egestas ante.

	Donec eu urna et odio euismod faucibus. Duis quis mauris imperdiet, egestas turpis et, varius ipsum. Aenean vitae orci ut erat aliquam aliquam. Sed mattis dolor lacinia justo scelerisque, non sollicitudin mauris varius. Donec eleifend porta mi, nec fringilla nunc rhoncus ac. Nam placerat erat tortor, ut convallis dui efficitur in. Integer id gravida massa. Ut vitae nulla ullamcorper, blandit ante quis, hendrerit lacus. Aliquam blandit ligula eu enim venenatis blandit. Aenean ullamcorper, mauris a tempus vulputate, quam massa pellentesque arcu, pulvinar vestibulum lorem magna quis lectus. Praesent nec ipsum porttitor, sollicitudin lacus interdum, pellentesque metus. Sed tempus hendrerit dolor ac hendrerit. Morbi vel risus ac lorem efficitur dignissim.

	Sed et sollicitudin metus. Suspendisse vel facilisis odio, sit amet dapibus neque. Etiam id mattis dui. Pellentesque eu ex blandit, porta tellus semper, tristique nisi. In nunc erat, ullamcorper ac elementum quis, pretium sit amet turpis. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse pharetra dignissim ex, et fringilla ligula eleifend ut. Vivamus consequat sodales luctus. Donec vestibulum turpis sit amet orci aliquet, id ultrices enim pharetra. In aliquet odio id ipsum aliquet malesuada. Sed maximus scelerisque lorem, at sollicitudin magna maximus ut. Nulla eu dictum metus. Donec in nulla nec nibh fermentum tincidunt.

	Vivamus et nisl purus. Duis quis scelerisque sapien. Ut dapibus laoreet mattis. Vivamus consequat eget risus nec eleifend. Sed non justo et arcu sodales fringilla. Praesent quam est, commodo at felis ut, ullamcorper ultrices tellus. Phasellus vitae metus pellentesque, aliquam nisl sit amet, rutrum nulla.

	Aenean vestibulum vehicula purus eget iaculis. Integer massa dui, finibus ac sem vitae, scelerisque mattis quam. Morbi sed iaculis metus. Ut nisl metus, pulvinar ut vestibulum vitae, egestas accumsan felis. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Nullam tincidunt nunc ut risus pellentesque dictum. Aliquam nec risus est. Sed sed consequat sapien. Cras eget velit dapibus, vestibulum leo eu, bibendum magna. Duis fermentum ligula magna, non aliquam metus consequat non. Cras malesuada massa sit amet ante sodales molestie. Ut hendrerit lacinia convallis. Nullam vel congue nisl, et hendrerit ipsum. Nullam rhoncus consequat iaculis. Nam egestas cursus enim, vel lobortis elit lacinia eget.

	Duis arcu ante, accumsan nec imperdiet quis, rhoncus sit amet libero. Duis mattis, nulla a condimentum varius, risus libero placerat ante, et pretium dui ex et ipsum. Cras lobortis lorem ac metus eleifend, at feugiat ante laoreet. Aenean non cursus metus, quis aliquam ante. Nullam libero ligula, molestie in velit id, pharetra varius nisl. Vivamus purus odio, maximus rutrum volutpat ac, ornare a lectus. Cras venenatis enim varius elit ullamcorper, eget consequat ipsum consequat. Praesent congue, orci vel interdum sagittis, libero felis bibendum arcu, dignissim efficitur ligula diam et mi.

	Interdum et malesuada fames ac ante ipsum primis in faucibus. Donec pulvinar consectetur arcu, in sagittis lacus gravida eget. Maecenas ut nisi non sem condimentum pretium ullamcorper sed ex. Morbi feugiat volutpat nisl non tempor. Mauris euismod efficitur ligula, in dignissim enim vulputate ut. Maecenas ante elit, sagittis a massa eu, ornare porttitor velit. Sed cursus nulla accumsan aliquet interdum. Proin elementum diam vel condimentum dapibus. Vivamus tempor, libero non ornare volutpat, tellus lacus pulvinar massa, sit amet dictum dui ante a est. Aliquam in tincidunt erat, et facilisis nunc. Morbi tincidunt pretium erat, non ultrices neque commodo et. Aenean consequat nibh at lectus vehicula luctus.

	Sed venenatis semper consectetur. Pellentesque aliquet aliquet tellus, nec sollicitudin quam ultrices vitae. Vivamus faucibus viverra diam. Aliquam mattis erat eu consectetur imperdiet. Ut at nulla eu tellus rhoncus semper sit amet vel magna. Donec id sem tincidunt, venenatis felis sed, porttitor neque. Sed pellentesque viverra hendrerit.

	Nullam quis sagittis dolor, vel maximus nunc. Ut id suscipit dolor. Suspendisse fermentum a lorem nec cursus. Cras tincidunt varius metus in pellentesque. Sed placerat, ipsum pharetra lobortis maximus, libero lorem suscipit nibh, ut molestie neque nibh vel elit. Nullam et vulputate enim. Cras commodo ante cursus, placerat nulla id, venenatis nisl. Nulla a massa mauris. Aenean nec suscipit quam. Ut ullamcorper a elit tincidunt ullamcorper. Praesent ac laoreet mauris. Praesent semper mi sit amet nunc blandit bibendum.

	Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Nullam orci velit, sollicitudin ut tristique lobortis, facilisis eget erat. Mauris tincidunt tempor turpis. Suspendisse potenti. Nulla quis leo ut dui bibendum tincidunt ac id velit. Pellentesque sodales ut dui sed euismod. Aliquam mollis urna quis nibh dapibus sodales. Sed volutpat suscipit est non hendrerit. Aenean laoreet tincidunt enim sodales commodo. Nullam mattis odio in purus dapibus faucibus.

	Fusce finibus justo in elit auctor accumsan. Vestibulum consequat, quam non suscipit condimentum, dolor est condimentum ex, non gravida odio ante at arcu. Nullam a turpis est. Morbi ultrices orci a finibus pretium. Mauris faucibus sem sed pharetra maximus. Etiam auctor ex at nisl auctor, a porta sapien dictum. Vestibulum nulla velit, accumsan ac dignissim sit amet, pharetra nec est. Proin feugiat rhoncus lacus vel porta.

	Maecenas mattis dolor urna, et hendrerit nunc condimentum id. Praesent tortor nibh, tristique semper tristique a, vestibulum luctus ligula. Curabitur sed tortor non magna mattis porttitor. Proin vitae nulla facilisis, sollicitudin nulla sed, dignissim felis. Pellentesque eget lacus hendrerit, feugiat mi quis, feugiat odio. Morbi finibus tempus fringilla. Ut pellentesque purus elit, eget tempus dui maximus vel. Quisque cursus aliquet bibendum. Proin tincidunt, tortor ut ullamcorper hendrerit, tortor magna molestie nulla, eget finibus leo diam et lectus. Donec dictum ultricies justo, quis convallis sem varius at. Phasellus nec luctus tortor.

	Praesent aliquam congue quam sed congue. Duis vitae porttitor leo, ac ullamcorper purus. Maecenas tempus mollis turpis non faucibus. Mauris placerat lobortis sagittis. Nam dapibus ut quam ac vulputate. Mauris mi urna, consectetur non dapibus id, varius quis odio. Pellentesque sed congue est. Integer convallis cursus orci, vitae imperdiet quam ullamcorper fringilla. Integer ut viverra magna. Phasellus blandit velit velit, non dapibus libero sollicitudin condimentum. Aliquam tristique ornare nisl, eu rhoncus quam semper id. Nulla facilisis mi non imperdiet faucibus. Aenean mollis magna id urna gravida mollis. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Nam et urna viverra, tempus orci sed, euismod ligula.
EOT;

	const PARAGRAPH_UTF8 = <<<EOT
	Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec mattis erat at dolor fringilla, sed feugiat libero tempus. Curabitur condimentum purus vitä bibendum fringilla. Integer facilisis maximus accumsan. Donec dignissim nec ex ut molestie. Vivamus scelerisque dui vitä dictum laoreet. Duis facilisis fermentum facilisis. Aliquam iaculis sit amet metus a facilisis. Integer sit amet bibendum odio. Quisque purus magna, imperdiet eu vestibulum quis, posuere quis tellus. Sed hendrerit justo id scelerisque elementum. Vestibulum est sem, dictum eget pellentesque finibus, tempor vel nisi. Etiam posuere vehicula magna, vitä fringilla risus efficitur a. Nullam mauris quam, congue vitä dapibus non, interdum ac turpis.
EOT;

	const PARAGRAPH_ASCII = <<<EOT
	Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec mattis erat at dolor fringilla, sed feugiat libero tempus. Curabitur condimentum purus vitae bibendum fringilla. Integer facilisis maximus accumsan. Donec dignissim nec ex ut molestie. Vivamus scelerisque dui vitae dictum laoreet. Duis facilisis fermentum facilisis. Aliquam iaculis sit amet metus a facilisis. Integer sit amet bibendum odio. Quisque purus magna, imperdiet eu vestibulum quis, posuere quis tellus. Sed hendrerit justo id scelerisque elementum. Vestibulum est sem, dictum eget pellentesque finibus, tempor vel nisi. Etiam posuere vehicula magna, vitae fringilla risus efficitur a. Nullam mauris quam, congue vitae dapibus non, interdum ac turpis.
EOT;

	/**
	 * Retrieves the sampe text.
	 *
	 * @param  array $params Required.
	 *
	 * @return string
	 */
	protected static function get_html( array $params ) {
		if ( ! empty( $params['long'] ) ) {
			if ( 'UTF-8' === $params['long'] ) {
				$html = self::SAMPLE;
			} else {
				$html = self::SAMPLE_ASCII;
			}
		} elseif ( ! empty( $params['medium'] ) ) {
			if ( 'UTF-8' === $params['medium'] ) {
				$html = self::PARAGRAPH_UTF8;
			} else {
				$html = self::PARAGRAPH_ASCII;
			}
		} else {
			$html = $params['html'];
		}

		return $html;
	}

	/**
	 * Provide parameters for process_bench.
	 *
	 * @return array
	 */
	public function provide_process_filenames() {
		return [
			[
				'long' => 'ASCII',
			],
			[
				'long' => 'UTF-8',
			],
			[
				'medium' => 'ASCII',
			],
			[
				'medium' => 'UTF-8',
			],
			[
				'html' => 'aaabbbcccdddeeefffggghhh',
			],
			[
				'html' => 'äääbbbcccdddeeefffggghhh',
			],
		];
	}

	/**
	 * Benchmark mb_strlen method.
	 *
	 * @ParamProviders({"provide_process_filenames"})
	 *
	 * @param  array $params The parameters.
	 */
	public function bench_mb_detect_encoding_utf8_first( $params ) {
		$html = self::get_html( $params );

		for ( $i = 0; $i < 100; ++$i ) {
			\mb_detect_encoding( $html, [ 'UTF-8', 'ASCII' ], true );
		}
	}

	/**
	 * Benchmark mb_strlen method.
	 *
	 * @ParamProviders({"provide_process_filenames"})
	 *
	 * @param  array $params The parameters.
	 */
	public function bench_mb_detect_encoding_ascii_first( $params ) {
		$html = self::get_html( $params );

		for ( $i = 0; $i < 100; ++$i ) {
			\mb_detect_encoding( $html, [ 'ASCII', 'UTF-8' ], true );
		}
	}

	/**
	 * Benchmark strlen/mb_strlen method.
	 *
	 * @ParamProviders({"provide_process_filenames"})
	 *
	 * @param  array $params The parameters.
	 */
	public function bench_looped_mb_check_encoding_utf8_first( $params ) {
		$html = self::get_html( $params );

		for ( $i = 0; $i < 100; ++$i ) {
			if ( \mb_check_encoding( $html, 'UTF-8' ) ) {
				return 'UTF-8';
			} elseif ( \mb_check_encoding( $html, 'ASCII' ) ) {
				return 'ASCII';
			}
		}
	}

	/**
	 * Benchmark strlen/mb_strlen method.
	 *
	 * @ParamProviders({"provide_process_filenames"})
	 *
	 * @param  array $params The parameters.
	 */
	public function bench_looped_mb_check_encoding_ascii_first( $params ) {
		$html = self::get_html( $params );

		for ( $i = 0; $i < 100; ++$i ) {
			if ( \mb_check_encoding( $html, 'ASCII' ) ) {
				return 'ASCII';
			} elseif ( \mb_check_encoding( $html, 'UTF-8' ) ) {
				return 'UTF-8';
			}
		}
	}
}
