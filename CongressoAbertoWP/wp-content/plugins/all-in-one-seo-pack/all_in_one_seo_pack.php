<?php

/*
Plugin Name: All in One SEO Pack
Plugin URI: http://semperfiwebdesign.com
Description: Out-of-the-box SEO for your Wordpress blog. <a href="options-general.php?page=all-in-one-seo-pack/aioseop.class.php">Options configuration panel</a> | <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=mrtorbert%40gmail%2ecom&item_name=All%20In%20One%20SEO%20Pack&item_number=Support%20Open%20Source&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&bn=PP%2dDonationsBF&charset=UTF%2d8">Donate</a> | <a href="http://semperfiwebdesign.com/forum/" >Support</a> |  <a href="https://www.amazon.com/wishlist/1NFQ133FNCOOA/ref=wl_web" target="_blank" title="Amazon Wish List">Amazon Wishlist</a>
Version: 1.6.4.1
Author: Michael Torbert
Author URI: http://michaeltorbert.com
*/

/*
Copyright (C) 2008-2009 Michael Torbert, semperfiwebdesign.com (michael AT semperfiwebdesign DOT com)
Original code by uberdose of uberdose.com

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/*******************************************************************************************************/
register_activation_hook(__FILE__,'aioseop_activate_pl');

$UTF8_TABLES['strtolower'] = array(
	"Ｚ" => "ｚ",	"Ｙ" => "ｙ",	"Ｘ" => "ｘ",
	"Ｗ" => "ｗ",	"Ｖ" => "ｖ",	"Ｕ" => "ｕ",
	"Ｔ" => "ｔ",	"Ｓ" => "ｓ",	"Ｒ" => "ｒ",
	"Ｑ" => "ｑ",	"Ｐ" => "ｐ",	"Ｏ" => "ｏ",
	"Ｎ" => "ｎ",	"Ｍ" => "ｍ",	"Ｌ" => "ｌ",
	"Ｋ" => "ｋ",	"Ｊ" => "ｊ",	"Ｉ" => "ｉ",
	"Ｈ" => "ｈ",	"Ｇ" => "ｇ",	"Ｆ" => "ｆ",
	"Ｅ" => "ｅ",	"Ｄ" => "ｄ",	"Ｃ" => "ｃ",
	"Ｂ" => "ｂ",	"Ａ" => "ａ",	"Å" => "å",
	"K" => "k",	"Ω" => "ω",	"Ώ" => "ώ",
	"Ὼ" => "ὼ",	"Ό" => "ό",	"Ὸ" => "ὸ",
	"Ῥ" => "ῥ",	"Ύ" => "ύ",	"Ὺ" => "ὺ",
	"Ῡ" => "ῡ",	"Ῠ" => "� ",	"Ί" => "ί",
	"Ὶ" => "ὶ",	"Ῑ" => "ῑ",	"Ῐ" => "ῐ",
	"Ή" => "ή",	"Ὴ" => "ὴ",	"Έ" => "έ",
	"Ὲ" => "ὲ",	"Ά" => "ά",	"Ὰ" => "ὰ",
	"Ᾱ" => "ᾱ",	"Ᾰ" => "ᾰ",	"Ὧ" => "ὧ",
	"Ὦ" => "ὦ",	"Ὥ" => "ὥ",	"Ὤ" => "ὤ",
	"Ὣ" => "ὣ",	"Ὢ" => "ὢ",	"Ὡ" => "ὡ",
	"Ὠ" => "� ",	"Ὗ" => "ὗ",	"Ὕ" => "ὕ",
	"Ὓ" => "ὓ",	"Ὑ" => "ὑ",	"Ὅ" => "ὅ",
	"Ὄ" => "ὄ",	"Ὃ" => "ὃ",	"Ὂ" => "ὂ",
	"Ὁ" => "ὁ",	"Ὀ" => "ὀ",	"Ἷ" => "ἷ",
	"Ἶ" => "ἶ",	"Ἵ" => "ἵ",	"Ἴ" => "ἴ",
	"Ἳ" => "ἳ",	"Ἲ" => "ἲ",	"Ἱ" => "ἱ",
	"Ἰ" => "ἰ",	"Ἧ" => "ἧ",	"Ἦ" => "ἦ",
	"Ἥ" => "ἥ",	"Ἤ" => "ἤ",	"Ἣ" => "ἣ",
	"Ἢ" => "ἢ",	"Ἡ" => "ἡ",	"Ἠ" => "� ",
	"Ἕ" => "ἕ",	"Ἔ" => "ἔ",	"Ἓ" => "ἓ",
	"Ἒ" => "ἒ",	"Ἑ" => "ἑ",	"Ἐ" => "ἐ",
	"Ἇ" => "ἇ",	"Ἆ" => "ἆ",	"Ἅ" => "ἅ",
	"Ἄ" => "ἄ",	"Ἃ" => "ἃ",	"Ἂ" => "ἂ",
	"Ἁ" => "ἁ",	"Ἀ" => "ἀ",	"Ỹ" => "ỹ",
	"Ỷ" => "ỷ",	"Ỵ" => "ỵ",	"Ỳ" => "ỳ",
	"Ự" => "ự",	"Ữ" => "ữ",	"Ử" => "ử",
	"Ừ" => "ừ",	"Ứ" => "ứ",	"Ủ" => "ủ",
	"Ụ" => "ụ",	"Ợ" => "ợ",	"� " => "ỡ",
	"Ở" => "ở",	"Ờ" => "ờ",	"Ớ" => "ớ",
	"Ộ" => "ộ",	"Ỗ" => "ỗ",	"Ổ" => "ổ",
	"Ồ" => "ồ",	"Ố" => "ố",	"Ỏ" => "ỏ",
	"Ọ" => "ọ",	"Ị" => "ị",	"Ỉ" => "ỉ",
	"Ệ" => "ệ",	"Ễ" => "ễ",	"Ể" => "ể",
	"Ề" => "ề",	"Ế" => "ế",	"Ẽ" => "ẽ",
	"Ẻ" => "ẻ",	"Ẹ" => "ẹ",	"Ặ" => "ặ",
	"Ẵ" => "ẵ",	"Ẳ" => "ẳ",	"Ằ" => "ằ",
	"Ắ" => "ắ",	"Ậ" => "ậ",	"Ẫ" => "ẫ",
	"Ẩ" => "ẩ",	"Ầ" => "ầ",	"Ấ" => "ấ",
	"Ả" => "ả",	"� " => "ạ",	"Ẕ" => "ẕ",
	"Ẓ" => "ẓ",	"Ẑ" => "ẑ",	"Ẏ" => "ẏ",
	"Ẍ" => "ẍ",	"Ẋ" => "ẋ",	"Ẉ" => "ẉ",
	"Ẇ" => "ẇ",	"Ẅ" => "ẅ",	"Ẃ" => "ẃ",
	"Ẁ" => "ẁ",	"Ṿ" => "ṿ",	"Ṽ" => "ṽ",
	"Ṻ" => "ṻ",	"Ṹ" => "ṹ",	"Ṷ" => "ṷ",
	"Ṵ" => "ṵ",	"Ṳ" => "ṳ",	"Ṱ" => "ṱ",
	"Ṯ" => "ṯ",	"Ṭ" => "ṭ",	"Ṫ" => "ṫ",
	"Ṩ" => "ṩ",	"Ṧ" => "ṧ",	"Ṥ" => "ṥ",
	"Ṣ" => "ṣ",	"� " => "ṡ",	"Ṟ" => "ṟ",
	"Ṝ" => "ṝ",	"Ṛ" => "ṛ",	"Ṙ" => "ṙ",
	"Ṗ" => "ṗ",	"Ṕ" => "ṕ",	"Ṓ" => "ṓ",
	"Ṑ" => "ṑ",	"Ṏ" => "ṏ",	"Ṍ" => "ṍ",
	"Ṋ" => "ṋ",	"Ṉ" => "ṉ",	"Ṇ" => "ṇ",
	"Ṅ" => "ṅ",	"Ṃ" => "ṃ",	"Ṁ" => "ṁ",
	"Ḿ" => "ḿ",	"Ḽ" => "ḽ",	"Ḻ" => "ḻ",
	"Ḹ" => "ḹ",	"Ḷ" => "ḷ",	"Ḵ" => "ḵ",
	"Ḳ" => "ḳ",	"Ḱ" => "ḱ",	"Ḯ" => "ḯ",
	"Ḭ" => "ḭ",	"Ḫ" => "ḫ",	"Ḩ" => "ḩ",
	"Ḧ" => "ḧ",	"Ḥ" => "ḥ",	"Ḣ" => "ḣ",
	"� " => "ḡ",	"Ḟ" => "ḟ",	"Ḝ" => "ḝ",
	"Ḛ" => "ḛ",	"Ḙ" => "ḙ",	"Ḗ" => "ḗ",
	"Ḕ" => "ḕ",	"Ḓ" => "ḓ",	"Ḑ" => "ḑ",
	"Ḏ" => "ḏ",	"Ḍ" => "ḍ",	"Ḋ" => "ḋ",
	"Ḉ" => "ḉ",	"Ḇ" => "ḇ",	"Ḅ" => "ḅ",
	"Ḃ" => "ḃ",	"Ḁ" => "ḁ",	"Ֆ" => "ֆ",
	"Օ" => "օ",	"Ք" => "ք",	"Փ" => "փ",
	"Ւ" => "ւ",	"Ց" => "ց",	"Ր" => "ր",
	"Տ" => "տ",	"Վ" => "վ",	"Ս" => "ս",
	"Ռ" => "ռ",	"Ջ" => "ջ",	"Պ" => "պ",
	"Չ" => "չ",	"Ո" => "ո",	"Շ" => "շ",
	"Ն" => "ն",	"Յ" => "յ",	"Մ" => "մ",
	"Ճ" => "ճ",	"Ղ" => "ղ",	"Ձ" => "ձ",
	"Հ" => "հ",	"Կ" => "կ",	"Ծ" => "ծ",
	"Խ" => "խ",	"Լ" => "լ",	"Ի" => "ի",
	"Ժ" => "ժ",	"Թ" => "թ",	"Ը" => "ը",
	"Է" => "է",	"Զ" => "զ",	"Ե" => "ե",
	"Դ" => "դ",	"Գ" => "գ",	"Բ" => "բ",
	"Ա" => "ա",	"Ԏ" => "ԏ",	"Ԍ" => "ԍ",
	"Ԋ" => "ԋ",	"Ԉ" => "ԉ",	"Ԇ" => "ԇ",
	"Ԅ" => "ԅ",	"Ԃ" => "ԃ",	"Ԁ" => "ԁ",
	"Ӹ" => "ӹ",	"Ӵ" => "ӵ",	"Ӳ" => "ӳ",
	"Ӱ" => "ӱ",	"Ӯ" => "ӯ",	"Ӭ" => "ӭ",
	"Ӫ" => "ӫ",	"Ө" => "ө",	"Ӧ" => "ӧ",
	"Ӥ" => "ӥ",	"Ӣ" => "ӣ",	"� " => "ӡ",
	"Ӟ" => "ӟ",	"Ӝ" => "ӝ",	"Ӛ" => "ӛ",
	"Ә" => "ә",	"Ӗ" => "ӗ",	"Ӕ" => "ӕ",
	"Ӓ" => "ӓ",	"Ӑ" => "ӑ",	"Ӎ" => "ӎ",
	"Ӌ" => "ӌ",	"Ӊ" => "ӊ",	"Ӈ" => "ӈ",
	"Ӆ" => "ӆ",	"Ӄ" => "ӄ",	"Ӂ" => "ӂ",
	"Ҿ" => "ҿ",	"Ҽ" => "ҽ",	"Һ" => "һ",
	"Ҹ" => "ҹ",	"Ҷ" => "ҷ",	"Ҵ" => "ҵ",
	"Ҳ" => "ҳ",	"Ұ" => "ұ",	"Ү" => "ү",
	"Ҭ" => "ҭ",	"Ҫ" => "ҫ",	"Ҩ" => "ҩ",
	"Ҧ" => "ҧ",	"Ҥ" => "ҥ",	"Ң" => "ң",
	"� " => "ҡ",	"Ҟ" => "ҟ",	"Ҝ" => "ҝ",
	"Қ" => "қ",	"Ҙ" => "ҙ",	"Җ" => "җ",
	"Ҕ" => "ҕ",	"Ғ" => "ғ",	"Ґ" => "ґ",
	"Ҏ" => "ҏ",	"Ҍ" => "ҍ",	"Ҋ" => "ҋ",
	"Ҁ" => "ҁ",	"Ѿ" => "ѿ",	"Ѽ" => "ѽ",
	"Ѻ" => "ѻ",	"Ѹ" => "ѹ",	"Ѷ" => "ѷ",
	"Ѵ" => "ѵ",	"Ѳ" => "ѳ",	"Ѱ" => "ѱ",
	"Ѯ" => "ѯ",	"Ѭ" => "ѭ",	"Ѫ" => "ѫ",
	"Ѩ" => "ѩ",	"Ѧ" => "ѧ",	"Ѥ" => "ѥ",
	"Ѣ" => "ѣ",	"� " => "ѡ",	"Я" => "я",
	"Ю" => "ю",	"Э" => "э",	"Ь" => "ь",
	"Ы" => "ы",	"Ъ" => "ъ",	"Щ" => "щ",
	"Ш" => "ш",	"Ч" => "ч",	"Ц" => "ц",
	"Х" => "х",	"Ф" => "ф",	"У" => "у",
	"Т" => "т",	"С" => "с",	"� " => "р",
	"П" => "п",	"О" => "о",	"Н" => "н",
	"М" => "м",	"Л" => "л",	"К" => "к",
	"Й" => "й",	"И" => "и",	"З" => "з",
	"Ж" => "ж",	"Е" => "е",	"Д" => "д",
	"Г" => "г",	"В" => "в",	"Б" => "б",
	"А" => "а",	"Џ" => "џ",	"Ў" => "ў",
	"Ѝ" => "ѝ",	"Ќ" => "ќ",	"Ћ" => "ћ",
	"Њ" => "њ",	"Љ" => "љ",	"Ј" => "ј",
	"Ї" => "ї",	"І" => "і",	"Ѕ" => "ѕ",
	"Є" => "є",	"Ѓ" => "ѓ",	"Ђ" => "ђ",
	"Ё" => "ё",	"Ѐ" => "ѐ",	"ϴ" => "θ",
	"Ϯ" => "ϯ",	"Ϭ" => "ϭ",	"Ϫ" => "ϫ",
	"Ϩ" => "ϩ",	"Ϧ" => "ϧ",	"Ϥ" => "ϥ",
	"Ϣ" => "ϣ",	"� " => "ϡ",	"Ϟ" => "ϟ",
	"Ϝ" => "ϝ",	"Ϛ" => "ϛ",	"Ϙ" => "ϙ",
	"Ϋ" => "ϋ",	"Ϊ" => "ϊ",	"Ω" => "ω",
	"Ψ" => "ψ",	"Χ" => "χ",	"Φ" => "φ",
	"Υ" => "υ",	"Τ" => "τ",	"Σ" => "σ",
	"Ρ" => "ρ",	"� " => "π",	"Ο" => "ο",
	"Ξ" => "ξ",	"Ν" => "ν",	"Μ" => "μ",
	"Λ" => "λ",	"Κ" => "κ",	"Ι" => "ι",
	"Θ" => "θ",	"Η" => "η",	"Ζ" => "ζ",
	"Ε" => "ε",	"Δ" => "δ",	"Γ" => "γ",
	"Β" => "β",	"Α" => "α",	"Ώ" => "ώ",
	"Ύ" => "ύ",	"Ό" => "ό",	"Ί" => "ί",
	"Ή" => "ή",	"Έ" => "έ",	"Ά" => "ά",
	"Ȳ" => "ȳ",	"Ȱ" => "ȱ",	"Ȯ" => "ȯ",
	"Ȭ" => "ȭ",	"Ȫ" => "ȫ",	"Ȩ" => "ȩ",
	"Ȧ" => "ȧ",	"Ȥ" => "ȥ",	"Ȣ" => "ȣ",
	"� " => "ƞ",	"Ȟ" => "ȟ",	"Ȝ" => "ȝ",
	"Ț" => "ț",	"Ș" => "ș",	"Ȗ" => "ȗ",
	"Ȕ" => "ȕ",	"Ȓ" => "ȓ",	"Ȑ" => "ȑ",
	"Ȏ" => "ȏ",	"Ȍ" => "ȍ",	"Ȋ" => "ȋ",
	"Ȉ" => "ȉ",	"Ȇ" => "ȇ",	"Ȅ" => "ȅ",
	"Ȃ" => "ȃ",	"Ȁ" => "ȁ",	"Ǿ" => "ǿ",
	"Ǽ" => "ǽ",	"Ǻ" => "ǻ",	"Ǹ" => "ǹ",
	"Ƿ" => "ƿ",	"Ƕ" => "ƕ",	"Ǵ" => "ǵ",
	"Ǳ" => "ǳ",	"Ǯ" => "ǯ",	"Ǭ" => "ǭ",
	"Ǫ" => "ǫ",	"Ǩ" => "ǩ",	"Ǧ" => "ǧ",
	"Ǥ" => "ǥ",	"Ǣ" => "ǣ",	"� " => "ǡ",
	"Ǟ" => "ǟ",	"Ǜ" => "ǜ",	"Ǚ" => "ǚ",
	"Ǘ" => "ǘ",	"Ǖ" => "ǖ",	"Ǔ" => "ǔ",
	"Ǒ" => "ǒ",	"Ǐ" => "ǐ",	"Ǎ" => "ǎ",
	"Ǌ" => "ǌ",	"Ǉ" => "ǉ",	"Ǆ" => "ǆ",
	"Ƽ" => "ƽ",	"Ƹ" => "ƹ",	"Ʒ" => "ʒ",
	"Ƶ" => "ƶ",	"Ƴ" => "ƴ",	"Ʋ" => "ʋ",
	"Ʊ" => "ʊ",	"Ư" => "ư",	"Ʈ" => "ʈ",
	"Ƭ" => "ƭ",	"Ʃ" => "ʃ",	"Ƨ" => "ƨ",
	"Ʀ" => "ʀ",	"Ƥ" => "ƥ",	"Ƣ" => "ƣ",
	"� " => "ơ",	"Ɵ" => "ɵ",	"Ɲ" => "ɲ",
	"Ɯ" => "ɯ",	"Ƙ" => "ƙ",	"Ɨ" => "ɨ",
	"Ɩ" => "ɩ",	"Ɣ" => "ɣ",	"Ɠ" => "� ",
	"Ƒ" => "ƒ",	"Ɛ" => "ɛ",	"Ə" => "ə",
	"Ǝ" => "ǝ",	"Ƌ" => "ƌ",	"Ɗ" => "ɗ",
	"Ɖ" => "ɖ",	"Ƈ" => "ƈ",	"Ɔ" => "ɔ",
	"Ƅ" => "ƅ",	"Ƃ" => "ƃ",	"Ɓ" => "ɓ",
	"Ž" => "ž",	"Ż" => "ż",	"Ź" => "ź",
	"Ÿ" => "ÿ",	"Ŷ" => "ŷ",	"Ŵ" => "ŵ",
	"Ų" => "ų",	"Ű" => "ű",	"Ů" => "ů",
	"Ŭ" => "ŭ",	"Ū" => "ū",	"Ũ" => "ũ",
	"Ŧ" => "ŧ",	"Ť" => "ť",	"Ţ" => "ţ",
	"� " => "š",	"Ş" => "ş",	"Ŝ" => "ŝ",
	"Ś" => "ś",	"Ř" => "ř",	"Ŗ" => "ŗ",
	"Ŕ" => "ŕ",	"Œ" => "œ",	"Ő" => "ő",
	"Ŏ" => "ŏ",	"Ō" => "ō",	"Ŋ" => "ŋ",
	"Ň" => "ň",	"Ņ" => "ņ",	"Ń" => "ń",
	"Ł" => "ł",	"Ŀ" => "ŀ",	"Ľ" => "ľ",
	"Ļ" => "ļ",	"Ĺ" => "ĺ",	"Ķ" => "ķ",
	"Ĵ" => "ĵ",	"Ĳ" => "ĳ",	"İ" => "i",
	"Į" => "į",	"Ĭ" => "ĭ",	"Ī" => "ī",
	"Ĩ" => "ĩ",	"Ħ" => "ħ",	"Ĥ" => "ĥ",
	"Ģ" => "ģ",	"� " => "ġ",	"Ğ" => "ğ",
	"Ĝ" => "ĝ",	"Ě" => "ě",	"Ę" => "ę",
	"Ė" => "ė",	"Ĕ" => "ĕ",	"Ē" => "ē",
	"Đ" => "đ",	"Ď" => "ď",	"Č" => "č",
	"Ċ" => "ċ",	"Ĉ" => "ĉ",	"Ć" => "ć",
	"Ą" => "ą",	"Ă" => "ă",	"Ā" => "ā",
	"Þ" => "þ",	"Ý" => "ý",	"Ü" => "ü",
	"Û" => "û",	"Ú" => "ú",	"Ù" => "ù",
	"Ø" => "ø",	"Ö" => "ö",	"Õ" => "õ",
	"Ô" => "ô",	"Ó" => "ó",	"Ò" => "ò",
	"Ñ" => "ñ",	"Ð" => "ð",	"Ï" => "ï",
	"Î" => "î",	"Í" => "í",	"Ì" => "ì",
	"Ë" => "ë",	"Ê" => "ê",	"É" => "é",
	"È" => "è",	"Ç" => "ç",	"Æ" => "æ",
	"Å" => "å",	"Ä" => "ä",	"Ã" => "ã",
	"Â" => "â",	"Á" => "á",	"À" => "� ",
	"Z" => "z",		"Y" => "y",		"X" => "x",
	"W" => "w",		"V" => "v",		"U" => "u",
	"T" => "t",		"S" => "s",		"R" => "r",
	"Q" => "q",		"P" => "p",		"O" => "o",
	"N" => "n",		"M" => "m",		"L" => "l",
	"K" => "k",		"J" => "j",		"I" => "i",
	"H" => "h",		"G" => "g",		"F" => "f",
	"E" => "e",		"D" => "d",		"C" => "c",
	"B" => "b",		"A" => "a",
);


$UTF8_TABLES['strtoupper'] = array(
	"ｚ" => "Ｚ",	"ｙ" => "Ｙ",	"ｘ" => "Ｘ",
	"ｗ" => "Ｗ",	"ｖ" => "Ｖ",	"ｕ" => "Ｕ",
	"ｔ" => "Ｔ",	"ｓ" => "Ｓ",	"ｒ" => "Ｒ",
	"ｑ" => "Ｑ",	"ｐ" => "Ｐ",	"ｏ" => "Ｏ",
	"ｎ" => "Ｎ",	"ｍ" => "Ｍ",	"ｌ" => "Ｌ",
	"ｋ" => "Ｋ",	"ｊ" => "Ｊ",	"ｉ" => "Ｉ",
	"ｈ" => "Ｈ",	"ｇ" => "Ｇ",	"ｆ" => "Ｆ",
	"ｅ" => "Ｅ",	"ｄ" => "Ｄ",	"ｃ" => "Ｃ",
	"ｂ" => "Ｂ",	"ａ" => "Ａ",	"ῳ" => "ῼ",
	"ῥ" => "Ῥ",	"ῡ" => "Ῡ",	"� " => "Ῠ",
	"ῑ" => "Ῑ",	"ῐ" => "Ῐ",	"ῃ" => "ῌ",
	"ι" => "Ι",	"ᾳ" => "ᾼ",	"ᾱ" => "Ᾱ",
	"ᾰ" => "Ᾰ",	"ᾧ" => "ᾯ",	"ᾦ" => "ᾮ",
	"ᾥ" => "ᾭ",	"ᾤ" => "ᾬ",	"ᾣ" => "ᾫ",
	"ᾢ" => "ᾪ",	"ᾡ" => "ᾩ",	"� " => "ᾨ",
	"ᾗ" => "ᾟ",	"ᾖ" => "ᾞ",	"ᾕ" => "ᾝ",
	"ᾔ" => "ᾜ",	"ᾓ" => "ᾛ",	"ᾒ" => "ᾚ",
	"ᾑ" => "ᾙ",	"ᾐ" => "ᾘ",	"ᾇ" => "ᾏ",
	"ᾆ" => "ᾎ",	"ᾅ" => "ᾍ",	"ᾄ" => "ᾌ",
	"ᾃ" => "ᾋ",	"ᾂ" => "ᾊ",	"ᾁ" => "ᾉ",
	"ᾀ" => "ᾈ",	"ώ" => "Ώ",	"ὼ" => "Ὼ",
	"ύ" => "Ύ",	"ὺ" => "Ὺ",	"ό" => "Ό",
	"ὸ" => "Ὸ",	"ί" => "Ί",	"ὶ" => "Ὶ",
	"ή" => "Ή",	"ὴ" => "Ὴ",	"έ" => "Έ",
	"ὲ" => "Ὲ",	"ά" => "Ά",	"ὰ" => "Ὰ",
	"ὧ" => "Ὧ",	"ὦ" => "Ὦ",	"ὥ" => "Ὥ",
	"ὤ" => "Ὤ",	"ὣ" => "Ὣ",	"ὢ" => "Ὢ",
	"ὡ" => "Ὡ",	"� " => "Ὠ",	"ὗ" => "Ὗ",
	"ὕ" => "Ὕ",	"ὓ" => "Ὓ",	"ὑ" => "Ὑ",
	"ὅ" => "Ὅ",	"ὄ" => "Ὄ",	"ὃ" => "Ὃ",
	"ὂ" => "Ὂ",	"ὁ" => "Ὁ",	"ὀ" => "Ὀ",
	"ἷ" => "Ἷ",	"ἶ" => "Ἶ",	"ἵ" => "Ἵ",
	"ἴ" => "Ἴ",	"ἳ" => "Ἳ",	"ἲ" => "Ἲ",
	"ἱ" => "Ἱ",	"ἰ" => "Ἰ",	"ἧ" => "Ἧ",
	"ἦ" => "Ἦ",	"ἥ" => "Ἥ",	"ἤ" => "Ἤ",
	"ἣ" => "Ἣ",	"ἢ" => "Ἢ",	"ἡ" => "Ἡ",
	"� " => "Ἠ",	"ἕ" => "Ἕ",	"ἔ" => "Ἔ",
	"ἓ" => "Ἓ",	"ἒ" => "Ἒ",	"ἑ" => "Ἑ",
	"ἐ" => "Ἐ",	"ἇ" => "Ἇ",	"ἆ" => "Ἆ",
	"ἅ" => "Ἅ",	"ἄ" => "Ἄ",	"ἃ" => "Ἃ",
	"ἂ" => "Ἂ",	"ἁ" => "Ἁ",	"ἀ" => "Ἀ",
	"ỹ" => "Ỹ",	"ỷ" => "Ỷ",	"ỵ" => "Ỵ",
	"ỳ" => "Ỳ",	"ự" => "Ự",	"ữ" => "Ữ",
	"ử" => "Ử",	"ừ" => "Ừ",	"ứ" => "Ứ",
	"ủ" => "Ủ",	"ụ" => "Ụ",	"ợ" => "Ợ",
	"ỡ" => "� ",	"ở" => "Ở",	"ờ" => "Ờ",
	"ớ" => "Ớ",	"ộ" => "Ộ",	"ỗ" => "Ỗ",
	"ổ" => "Ổ",	"ồ" => "Ồ",	"ố" => "Ố",
	"ỏ" => "Ỏ",	"ọ" => "Ọ",	"ị" => "Ị",
	"ỉ" => "Ỉ",	"ệ" => "Ệ",	"ễ" => "Ễ",
	"ể" => "Ể",	"ề" => "Ề",	"ế" => "Ế",
	"ẽ" => "Ẽ",	"ẻ" => "Ẻ",	"ẹ" => "Ẹ",
	"ặ" => "Ặ",	"ẵ" => "Ẵ",	"ẳ" => "Ẳ",
	"ằ" => "Ằ",	"ắ" => "Ắ",	"ậ" => "Ậ",
	"ẫ" => "Ẫ",	"ẩ" => "Ẩ",	"ầ" => "Ầ",
	"ấ" => "Ấ",	"ả" => "Ả",	"ạ" => "� ",
	"ẛ" => "� ",	"ẕ" => "Ẕ",	"ẓ" => "Ẓ",
	"ẑ" => "Ẑ",	"ẏ" => "Ẏ",	"ẍ" => "Ẍ",
	"ẋ" => "Ẋ",	"ẉ" => "Ẉ",	"ẇ" => "Ẇ",
	"ẅ" => "Ẅ",	"ẃ" => "Ẃ",	"ẁ" => "Ẁ",
	"ṿ" => "Ṿ",	"ṽ" => "Ṽ",	"ṻ" => "Ṻ",
	"ṹ" => "Ṹ",	"ṷ" => "Ṷ",	"ṵ" => "Ṵ",
	"ṳ" => "Ṳ",	"ṱ" => "Ṱ",	"ṯ" => "Ṯ",
	"ṭ" => "Ṭ",	"ṫ" => "Ṫ",	"ṩ" => "Ṩ",
	"ṧ" => "Ṧ",	"ṥ" => "Ṥ",	"ṣ" => "Ṣ",
	"ṡ" => "� ",	"ṟ" => "Ṟ",	"ṝ" => "Ṝ",
	"ṛ" => "Ṛ",	"ṙ" => "Ṙ",	"ṗ" => "Ṗ",
	"ṕ" => "Ṕ",	"ṓ" => "Ṓ",	"ṑ" => "Ṑ",
	"ṏ" => "Ṏ",	"ṍ" => "Ṍ",	"ṋ" => "Ṋ",
	"ṉ" => "Ṉ",	"ṇ" => "Ṇ",	"ṅ" => "Ṅ",
	"ṃ" => "Ṃ",	"ṁ" => "Ṁ",	"ḿ" => "Ḿ",
	"ḽ" => "Ḽ",	"ḻ" => "Ḻ",	"ḹ" => "Ḹ",
	"ḷ" => "Ḷ",	"ḵ" => "Ḵ",	"ḳ" => "Ḳ",
	"ḱ" => "Ḱ",	"ḯ" => "Ḯ",	"ḭ" => "Ḭ",
	"ḫ" => "Ḫ",	"ḩ" => "Ḩ",	"ḧ" => "Ḧ",
	"ḥ" => "Ḥ",	"ḣ" => "Ḣ",	"ḡ" => "� ",
	"ḟ" => "Ḟ",	"ḝ" => "Ḝ",	"ḛ" => "Ḛ",
	"ḙ" => "Ḙ",	"ḗ" => "Ḗ",	"ḕ" => "Ḕ",
	"ḓ" => "Ḓ",	"ḑ" => "Ḑ",	"ḏ" => "Ḏ",
	"ḍ" => "Ḍ",	"ḋ" => "Ḋ",	"ḉ" => "Ḉ",
	"ḇ" => "Ḇ",	"ḅ" => "Ḅ",	"ḃ" => "Ḃ",
	"ḁ" => "Ḁ",	"ֆ" => "Ֆ",	"օ" => "Օ",
	"ք" => "Ք",	"փ" => "Փ",	"ւ" => "Ւ",
	"ց" => "Ց",	"ր" => "Ր",	"տ" => "Տ",
	"վ" => "Վ",	"ս" => "Ս",	"ռ" => "Ռ",
	"ջ" => "Ջ",	"պ" => "Պ",	"չ" => "Չ",
	"ո" => "Ո",	"շ" => "Շ",	"ն" => "Ն",
	"յ" => "Յ",	"մ" => "Մ",	"ճ" => "Ճ",
	"ղ" => "Ղ",	"ձ" => "Ձ",	"հ" => "Հ",
	"կ" => "Կ",	"ծ" => "Ծ",	"խ" => "Խ",
	"լ" => "Լ",	"ի" => "Ի",	"ժ" => "Ժ",
	"թ" => "Թ",	"ը" => "Ը",	"է" => "Է",
	"զ" => "Զ",	"ե" => "Ե",	"դ" => "Դ",
	"գ" => "Գ",	"բ" => "Բ",	"ա" => "Ա",
	"ԏ" => "Ԏ",	"ԍ" => "Ԍ",	"ԋ" => "Ԋ",
	"ԉ" => "Ԉ",	"ԇ" => "Ԇ",	"ԅ" => "Ԅ",
	"ԃ" => "Ԃ",	"ԁ" => "Ԁ",	"ӹ" => "Ӹ",
	"ӵ" => "Ӵ",	"ӳ" => "Ӳ",	"ӱ" => "Ӱ",
	"ӯ" => "Ӯ",	"ӭ" => "Ӭ",	"ӫ" => "Ӫ",
	"ө" => "Ө",	"ӧ" => "Ӧ",	"ӥ" => "Ӥ",
	"ӣ" => "Ӣ",	"ӡ" => "� ",	"ӟ" => "Ӟ",
	"ӝ" => "Ӝ",	"ӛ" => "Ӛ",	"ә" => "Ә",
	"ӗ" => "Ӗ",	"ӕ" => "Ӕ",	"ӓ" => "Ӓ",
	"ӑ" => "Ӑ",	"ӎ" => "Ӎ",	"ӌ" => "Ӌ",
	"ӊ" => "Ӊ",	"ӈ" => "Ӈ",	"ӆ" => "Ӆ",
	"ӄ" => "Ӄ",	"ӂ" => "Ӂ",	"ҿ" => "Ҿ",
	"ҽ" => "Ҽ",	"һ" => "Һ",	"ҹ" => "Ҹ",
	"ҷ" => "Ҷ",	"ҵ" => "Ҵ",	"ҳ" => "Ҳ",
	"ұ" => "Ұ",	"ү" => "Ү",	"ҭ" => "Ҭ",
	"ҫ" => "Ҫ",	"ҩ" => "Ҩ",	"ҧ" => "Ҧ",
	"ҥ" => "Ҥ",	"ң" => "Ң",	"ҡ" => "� ",
	"ҟ" => "Ҟ",	"ҝ" => "Ҝ",	"қ" => "Қ",
	"ҙ" => "Ҙ",	"җ" => "Җ",	"ҕ" => "Ҕ",
	"ғ" => "Ғ",	"ґ" => "Ґ",	"ҏ" => "Ҏ",
	"ҍ" => "Ҍ",	"ҋ" => "Ҋ",	"ҁ" => "Ҁ",
	"ѿ" => "Ѿ",	"ѽ" => "Ѽ",	"ѻ" => "Ѻ",
	"ѹ" => "Ѹ",	"ѷ" => "Ѷ",	"ѵ" => "Ѵ",
	"ѳ" => "Ѳ",	"ѱ" => "Ѱ",	"ѯ" => "Ѯ",
	"ѭ" => "Ѭ",	"ѫ" => "Ѫ",	"ѩ" => "Ѩ",
	"ѧ" => "Ѧ",	"ѥ" => "Ѥ",	"ѣ" => "Ѣ",
	"ѡ" => "� ",	"џ" => "Џ",	"ў" => "Ў",
	"ѝ" => "Ѝ",	"ќ" => "Ќ",	"ћ" => "Ћ",
	"њ" => "Њ",	"љ" => "Љ",	"ј" => "Ј",
	"ї" => "Ї",	"і" => "І",	"ѕ" => "Ѕ",
	"є" => "Є",	"ѓ" => "Ѓ",	"ђ" => "Ђ",
	"ё" => "Ё",	"ѐ" => "Ѐ",	"я" => "Я",
	"ю" => "Ю",	"э" => "Э",	"ь" => "Ь",
	"ы" => "Ы",	"ъ" => "Ъ",	"щ" => "Щ",
	"ш" => "Ш",	"ч" => "Ч",	"ц" => "Ц",
	"х" => "Х",	"ф" => "Ф",	"у" => "У",
	"т" => "Т",	"с" => "С",	"р" => "� ",
	"п" => "П",	"о" => "О",	"н" => "Н",
	"м" => "М",	"л" => "Л",	"к" => "К",
	"й" => "Й",	"и" => "И",	"з" => "З",
	"ж" => "Ж",	"е" => "Е",	"д" => "Д",
	"г" => "Г",	"в" => "В",	"б" => "Б",
	"а" => "А",	"ϵ" => "Ε",	"ϲ" => "Σ",
	"ϱ" => "Ρ",	"ϰ" => "Κ",	"ϯ" => "Ϯ",
	"ϭ" => "Ϭ",	"ϫ" => "Ϫ",	"ϩ" => "Ϩ",
	"ϧ" => "Ϧ",	"ϥ" => "Ϥ",	"ϣ" => "Ϣ",
	"ϡ" => "� ",	"ϟ" => "Ϟ",	"ϝ" => "Ϝ",
	"ϛ" => "Ϛ",	"ϙ" => "Ϙ",	"ϖ" => "� ",
	"ϕ" => "Φ",	"ϑ" => "Θ",	"ϐ" => "Β",
	"ώ" => "Ώ",	"ύ" => "Ύ",	"ό" => "Ό",
	"ϋ" => "Ϋ",	"ϊ" => "Ϊ",	"ω" => "Ω",
	"ψ" => "Ψ",	"χ" => "Χ",	"φ" => "Φ",
	"υ" => "Υ",	"τ" => "Τ",	"σ" => "Σ",
	"ς" => "Σ",	"ρ" => "Ρ",	"π" => "� ",
	"ο" => "Ο",	"ξ" => "Ξ",	"ν" => "Ν",
	"μ" => "Μ",	"λ" => "Λ",	"κ" => "Κ",
	"ι" => "Ι",	"θ" => "Θ",	"η" => "Η",
	"ζ" => "Ζ",	"ε" => "Ε",	"δ" => "Δ",
	"γ" => "Γ",	"β" => "Β",	"α" => "Α",
	"ί" => "Ί",	"ή" => "Ή",	"έ" => "Έ",
	"ά" => "Ά",	"ʒ" => "Ʒ",	"ʋ" => "Ʋ",
	"ʊ" => "Ʊ",	"ʈ" => "Ʈ",	"ʃ" => "Ʃ",
	"ʀ" => "Ʀ",	"ɵ" => "Ɵ",	"ɲ" => "Ɲ",
	"ɯ" => "Ɯ",	"ɩ" => "Ɩ",	"ɨ" => "Ɨ",
	"ɣ" => "Ɣ",	"� " => "Ɠ",	"ɛ" => "Ɛ",
	"ə" => "Ə",	"ɗ" => "Ɗ",	"ɖ" => "Ɖ",
	"ɔ" => "Ɔ",	"ɓ" => "Ɓ",	"ȳ" => "Ȳ",
	"ȱ" => "Ȱ",	"ȯ" => "Ȯ",	"ȭ" => "Ȭ",
	"ȫ" => "Ȫ",	"ȩ" => "Ȩ",	"ȧ" => "Ȧ",
	"ȥ" => "Ȥ",	"ȣ" => "Ȣ",	"ȟ" => "Ȟ",
	"ȝ" => "Ȝ",	"ț" => "Ț",	"ș" => "Ș",
	"ȗ" => "Ȗ",	"ȕ" => "Ȕ",	"ȓ" => "Ȓ",
	"ȑ" => "Ȑ",	"ȏ" => "Ȏ",	"ȍ" => "Ȍ",
	"ȋ" => "Ȋ",	"ȉ" => "Ȉ",	"ȇ" => "Ȇ",
	"ȅ" => "Ȅ",	"ȃ" => "Ȃ",	"ȁ" => "Ȁ",
	"ǿ" => "Ǿ",	"ǽ" => "Ǽ",	"ǻ" => "Ǻ",
	"ǹ" => "Ǹ",	"ǵ" => "Ǵ",	"ǳ" => "ǲ",
	"ǯ" => "Ǯ",	"ǭ" => "Ǭ",	"ǫ" => "Ǫ",
	"ǩ" => "Ǩ",	"ǧ" => "Ǧ",	"ǥ" => "Ǥ",
	"ǣ" => "Ǣ",	"ǡ" => "� ",	"ǟ" => "Ǟ",
	"ǝ" => "Ǝ",	"ǜ" => "Ǜ",	"ǚ" => "Ǚ",
	"ǘ" => "Ǘ",	"ǖ" => "Ǖ",	"ǔ" => "Ǔ",
	"ǒ" => "Ǒ",	"ǐ" => "Ǐ",	"ǎ" => "Ǎ",
	"ǌ" => "ǋ",	"ǉ" => "ǈ",	"ǆ" => "ǅ",
	"ƿ" => "Ƿ",	"ƽ" => "Ƽ",	"ƹ" => "Ƹ",
	"ƶ" => "Ƶ",	"ƴ" => "Ƴ",	"ư" => "Ư",
	"ƭ" => "Ƭ",	"ƨ" => "Ƨ",	"ƥ" => "Ƥ",
	"ƣ" => "Ƣ",	"ơ" => "� ",	"ƞ" => "� ",
	"ƙ" => "Ƙ",	"ƕ" => "Ƕ",	"ƒ" => "Ƒ",
	"ƌ" => "Ƌ",	"ƈ" => "Ƈ",	"ƅ" => "Ƅ",
	"ƃ" => "Ƃ",	"ſ" => "S",	"ž" => "Ž",
	"ż" => "Ż",	"ź" => "Ź",	"ŷ" => "Ŷ",
	"ŵ" => "Ŵ",	"ų" => "Ų",	"ű" => "Ű",
	"ů" => "Ů",	"ŭ" => "Ŭ",	"ū" => "Ū",
	"ũ" => "Ũ",	"ŧ" => "Ŧ",	"ť" => "Ť",
	"ţ" => "Ţ",	"š" => "� ",	"ş" => "Ş",
	"ŝ" => "Ŝ",	"ś" => "Ś",	"ř" => "Ř",
	"ŗ" => "Ŗ",	"ŕ" => "Ŕ",	"œ" => "Œ",
	"ő" => "Ő",	"ŏ" => "Ŏ",	"ō" => "Ō",
	"ŋ" => "Ŋ",	"ň" => "Ň",	"ņ" => "Ņ",
	"ń" => "Ń",	"ł" => "Ł",	"ŀ" => "Ŀ",
	"ľ" => "Ľ",	"ļ" => "Ļ",	"ĺ" => "Ĺ",
	"ķ" => "Ķ",	"ĵ" => "Ĵ",	"ĳ" => "Ĳ",
	"ı" => "I",	"į" => "Į",	"ĭ" => "Ĭ",
	"ī" => "Ī",	"ĩ" => "Ĩ",	"ħ" => "Ħ",
	"ĥ" => "Ĥ",	"ģ" => "Ģ",	"ġ" => "� ",
	"ğ" => "Ğ",	"ĝ" => "Ĝ",	"ě" => "Ě",
	"ę" => "Ę",	"ė" => "Ė",	"ĕ" => "Ĕ",
	"ē" => "Ē",	"đ" => "Đ",	"ď" => "Ď",
	"č" => "Č",	"ċ" => "Ċ",	"ĉ" => "Ĉ",
	"ć" => "Ć",	"ą" => "Ą",	"ă" => "Ă",
	"ā" => "Ā",	"ÿ" => "Ÿ",	"þ" => "Þ",
	"ý" => "Ý",	"ü" => "Ü",	"û" => "Û",
	"ú" => "Ú",	"ù" => "Ù",	"ø" => "Ø",
	"ö" => "Ö",	"õ" => "Õ",	"ô" => "Ô",
	"ó" => "Ó",	"ò" => "Ò",	"ñ" => "Ñ",
	"ð" => "Ð",	"ï" => "Ï",	"î" => "Î",
	"í" => "Í",	"ì" => "Ì",	"ë" => "Ë",
	"ê" => "Ê",	"é" => "É",	"è" => "È",
	"ç" => "Ç",	"æ" => "Æ",	"å" => "Å",
	"ä" => "Ä",	"ã" => "Ã",	"â" => "Â",
	"á" => "Á",	"� " => "À",	"µ" => "Μ",
	"z" => "Z",		"y" => "Y",		"x" => "X",
	"w" => "W",		"v" => "V",		"u" => "U",
	"t" => "T",		"s" => "S",		"r" => "R",
	"q" => "Q",		"p" => "P",		"o" => "O",
	"n" => "N",		"m" => "M",		"l" => "L",
	"k" => "K",		"j" => "J",		"i" => "I",
	"h" => "H",		"g" => "G",		"f" => "F",
	"e" => "E",		"d" => "D",		"c" => "C",
	"b" => "B",		"a" => "A",
);

if ( ! defined( 'WP_CONTENT_URL' ) )
    define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
    define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
    define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
    define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );

require_once('aioseop.class.php');

global $aioseop_options;
$aioseop_options = get_option('aioseop_options');



/*
add_option("aiosp_home_description", null, 'All in One SEO Plugin Home Description', 'yes');
add_option("aiosp_home_title", null, 'All in One SEO Plugin Home Title', 'yes');
add_option("aiosp_donate", 0, 'All in One SEO Pack Donate', 'no');
add_option("aiosp_can", 1, 'All in One SEO Pack Canonical URLs', 'yes');
add_option("aiosp_rewrite_titles", 1, 'All in One SEO Plugin Rewrite Titles', 'yes');
add_option("aiosp_use_categories", 0, 'All in One SEO Plugin Use Categories', 'yes');
add_option("aiosp_category_noindex", 1, 'All in One SEO Plugin Noindex for Categories', 'yes');
add_option("aiosp_archive_noindex", 1, 'All in One SEO Plugin Noindex for Archives', 'yes');
add_option("aiosp_tags_noindex", 0, 'All in One SEO Plugin Noindex for Tag Archives', 'yes');
add_option("aiosp_generate_descriptions", 1, 'All in One SEO Plugin Autogenerate Descriptions', 'yes');
add_option("aiosp_post_title_format", '%post_title% | %blog_title%', 'All in One SEO Plugin Post Title Format', 'yes');
add_option("aiosp_page_title_format", '%page_title% | %blog_title%', 'All in One SEO Plugin Page Title Format', 'yes');
add_option("aiosp_dynamic_postspage_keywords", 1, 'All in One SEO Plugin Dynamic Posts Page Keywords', 'yes');
add_option("aiosp_category_title_format", '%category_title% | %blog_title%', 'All in One SEO Plugin Category Title Format', 'yes');
add_option("aiosp_archive_title_format", '%date% | %blog_title%', 'All in One SEO Plugin Archive Title Format', 'yes');
add_option("aiosp_tag_title_format", '%tag% | %blog_title%', 'All in One SEO Plugin Tag Title Format', 'yes');
add_option("aiosp_search_title_format", '%search% | %blog_title%', 'All in One SEO Plugin Search Title Format', 'yes');
add_option("aiosp_description_format", '%description%', 'All in One SEO Plugin Description Format', 'yes');
add_option("aiosp_paged_format", ' - Part %page%', 'All in One SEO Plugin Paged Format', 'yes');
add_option("aiosp_404_title_format", 'Nothing found for %request_words%', 'All in One SEO Plugin 404 Title Format', 'yes');
add_option("aiosp_post_meta_tags", '', 'All in One SEO Plugin Additional Post Meta Tags', 'yes');
add_option("aiosp_page_meta_tags", '', 'All in One SEO Plugin Additional Post Meta Tags', 'yes');
add_option("aiosp_home_meta_tags", '', 'All in One SEO Plugin Additional Home Meta Tags', 'yes');
add_option("aiosp_do_log", null, 'All in One SEO Plugin write log file', 'yes');
*/

//$role = get_role('administrator');
//$role->add_cap('Edit AIOSEOP Options');
//$role->add_cap('Edit AIOSEOP on Posts/Pages');

////checking to see if things need to be updated

//if_post('turn-on'){
	
	/*   automattic?
if(!get_option('aioseop_options')){
	aioseop_mrt_fix_meta(); //move this to the if also 
	aioseop_mrt_mkarry();
	}
*/

if($_POST['aioseop_migrate']) aioseop_mrt_fix_meta();
if($_POST['aioseop_migrate_options']) aioseop_mrt_mkarry();
if(!get_option('aiosp_post_title_format') && !get_option('aioseop_options')) aioseop_mrt_mkarry();

//}end _post('turn_on')


////end checking to see if things need to be updated


function aioseop_mrt_fix_meta(){
global $wpdb;
$wpdb->query("UPDATE $wpdb->postmeta SET meta_key = '_aioseop_keywords' WHERE meta_key = 'keywords'");
$wpdb->query("UPDATE $wpdb->postmeta SET meta_key = '_aioseop_title' WHERE meta_key = 'title'");	
$wpdb->query("UPDATE $wpdb->postmeta SET meta_key = '_aioseop_description' WHERE meta_key = 'description'");
$wpdb->query("UPDATE $wpdb->postmeta SET meta_key = '_aioseop_meta' WHERE meta_key = 'aiosp_meta'");
$wpdb->query("UPDATE $wpdb->postmeta SET meta_key = '_aioseop_disable' WHERE meta_key = 'aiosp_disable'");
echo "<div class='updated fade' style='background-color:green;border-color:green;'><p><strong>Updating SEO post meta in database.</strong></p></div";
}

function aioseop_get_version(){
	return '1.6.4.1';
}




function aioseop_mrt_mkarry() {
$naioseop_options = array(
"aiosp_can"=>1,
"aiosp_donate"=>0,
"aiosp_home_title"=>null,
"aiosp_home_description"=>'',
"aiosp_home_keywords"=>null,
"aiosp_max_words_excerpt"=>'something',
"aiosp_rewrite_titles"=>1,
"aiosp_post_title_format"=>'%post_title% | %blog_title%',
"aiosp_page_title_format"=>'%page_title% | %blog_title%',
"aiosp_category_title_format"=>'%category_title% | %blog_title%',
"aiosp_archive_title_format"=>'%date% | %blog_title%',
"aiosp_tag_title_format"=>'%tag% | %blog_title%',
"aiosp_search_title_format"=>'%search% | %blog_title%',
"aiosp_description_format"=>'%description%',
"aiosp_404_title_format"=>'Nothing found for %request_words%',
"aiosp_paged_format"=>' - Part %page%',
"aiosp_use_categories"=>0,
"aiosp_dynamic_postspage_keywords"=>1,
"aiosp_category_noindex"=>1,
"aiosp_archive_noindex"=>1,
"aiosp_tags_noindex"=>0,
"aiosp_cap_cats"=>1,
"aiosp_generate_descriptions"=>1,
"aiosp_debug_info"=>null,
"aiosp_post_meta_tags"=>'',
"aiosp_page_meta_tags"=>'',
"aiosp_home_meta_tags"=>'',
"aiosp_enabled" =>0,
"aiosp_do_log"=>null);

if(get_option('aiosp_post_title_format')){
foreach( $naioseop_options as $aioseop_opt_name => $value ) {
		if( $aioseop_oldval = get_option($aioseop_opt_name) ) {
			$naioseop_options[$aioseop_opt_name] = $aioseop_oldval;
			
		}
		if( $aioseop_oldval == ''){
                          $naioseop_options[$aioseop_opt_name] = '';
                      }
        
		delete_option($aioseop_opt_name);
	}
}
add_option('aioseop_options',$naioseop_options);
echo "<div class='updated fade' style='background-color:green;border-color:green;'><p><strong>Updating SEO configuration options in database</strong></p></div";

}
//if( function_exists( 'is_site_admin' ) ) {

function aioseop_activation_notice(){
	global $aioseop_options;
				echo '<div class="error fade" style="background-color:red;"><p><strong>All in One SEO Pack must be configured. Go to <a href="' . admin_url( 'options-general.php?page=all-in-one-seo-pack/aioseop.class.php' ) . '">the admin page</a> to enable and configure the plugin.</strong></p></div>';

}

//add_action('after_plugin_row_all-in-one-seo-pack/all_in_one_seo_pack.php', 'add_plugin_row', 10, 2);

function add_plugin_row($links, $file) {

echo '<td colspan="5" style="background-color:yellow;">';
echo  wp_remote_fopen('http://aioseop.semperfiwebdesign.com/');
echo '</td>';

}
function aioseop_activate_pl(){
	if(get_option('aioseop_options')){
		$aioseop_options = get_option('aioseop_options');
		$aioseop_options['aiosp_enabled'] = "0";
		$aioseop_options['aiosp_donate'] = "0";
		update_option('aioseop_options',$aioseop_options);
	}
}


if($aioseop_options['aiosp_enabled']){
	add_action('wp_list_pages', 'aioseop_list_pages');

}
$aiosp = new All_in_One_SEO_Pack();	
add_action('edit_post', array($aiosp, 'post_meta_tags'));
add_action('publish_post', array($aiosp, 'post_meta_tags'));
add_action('save_post', array($aiosp, 'post_meta_tags'));
add_action('edit_page_form', array($aiosp, 'post_meta_tags'));
add_action('init', array($aiosp, 'init'));
add_action('wp_head', array($aiosp, 'wp_head'));
add_action('template_redirect', array($aiosp, 'template_redirect'));
//add_action('admin_head',array($aiosp, 'seo_mrt_admin_head');
add_action('admin_menu', array($aiosp, 'admin_menu'));
add_action('admin_menu', 'aiosp_meta_box_add');



if( ($_POST['aiosp_enabled'] == null && $aioseop_options['aiosp_enabled']!='1') || $_POST['aiosp_enabled']=='0'){
add_action( 'admin_notices', 'aioseop_activation_notice');
}


// The following two functions copied entirely and modified slightly from Sarah G's Page Menu Editor, http://wordpress.org/extend/plugins/page-menu-editor/
function aioseop_list_pages($content){
		$url = preg_replace(array('/\//', '/\./', '/\-/'), array('\/', '\.', '\-'), get_option('siteurl'));
		$pattern = '/<li class="page_item page-item-(\d+)([^\"]*)"><a href=\"([^\"]+)" title="([^\"]+)">([^<]+)<\/a>/i';
		return preg_replace_callback($pattern, "aioseop_filter_callback", $content);
	}

function aioseop_filter_callback($matches) {
	global $wpdb;
	if ($matches[1] && !empty($matches[1])) $postID = $matches[1];
	if (empty($postID)) $postID = get_option("page_on_front");
	$title_attrib = stripslashes(get_post_meta($postID, '_aioseop_titleatr', true));
	$menulabel = stripslashes(get_post_meta($postID, '_aioseop_menulabel', true));
	if (empty($menulabel)) $menulabel = $matches[4];
	if (!empty($title_attrib)) :
		$filtered = '<li class="page_item page-item-'.$postID.$matches[2].'"><a href="'.$matches[3].'" title="'.$title_attrib.'">'.$menulabel.'</a>';
	else :
		$filtered = '<li class="page_item page-item-'.$postID.$matches[2].'"><a href="'.$matches[3].'">'.$menulabel.'</a>';
	endif;
	return $filtered;
}

if (substr($aiosp->wp_version, 0, 3) < '2.5') {
        add_action('dbx_post_advanced', array($aiosp, 'add_meta_tags_textinput'));
        add_action('dbx_page_advanced', array($aiosp, 'add_meta_tags_textinput'));
}




function aiosp_meta_box_add() {
	if ( function_exists('add_meta_box') ) {
		add_meta_box('aiosp',__('All in One SEO Pack', 'all_in_one_seo_pack'),'aiosp_meta','post');
		add_meta_box('aiosp',__('All in One SEO Pack', 'all_in_one_seo_pack'),'aiosp_meta','page');
	} else {
		add_action('dbx_post_advanced', array($aiosp, 'add_meta_tags_textinput'));
		add_action('dbx_page_advanced', array($aiosp, 'add_meta_tags_textinput'));
	}
}

function aiosp_meta() {

	global $post;
	
	$post_id = $post;
	if (is_object($post_id)){
		$post_id = $post_id->ID;
	}
 	$keywords = htmlspecialchars(stripcslashes(get_post_meta($post_id, '_aioseop_keywords', true)));
    $title = htmlspecialchars(stripcslashes(get_post_meta($post_id, '_aioseop_title', true)));
	$description = htmlspecialchars(stripcslashes(get_post_meta($post_id, '_aioseop_description', true)));
    $aiosp_meta = htmlspecialchars(stripcslashes(get_post_meta($post_id, '_aiosp_meta', true)));
    $aiosp_disable = htmlspecialchars(stripcslashes(get_post_meta($post_id, '_aiosp_disable', true)));
    $aiosp_titleatr = htmlspecialchars(stripcslashes(get_post_meta($post_id, '_aioseop_titleatr', true)));
    $aiosp_menulabel = htmlspecialchars(stripcslashes(get_post_meta($post_id, '_aioseop_menulabel', true)));	
	?>
		<SCRIPT LANGUAGE="JavaScript">
		<!-- Begin
		function countChars(field,cntfield) {
		cntfield.value = field.value.length;
		}
		//  End -->
		</script>
		<input value="aiosp_edit" type="hidden" name="aiosp_edit" />
		
		<a target="__blank" href="http://semperfiwebdesign.com/portfolio/wordpress/wordpress-plugins/all-in-one-seo-pack/"><?php _e('Click here for Support', 'all_in_one_seo_pack') ?></a>
		<table style="margin-bottom:40px">
		<tr>
		<th style="text-align:left;" colspan="2">
		</th>
		</tr>
		<tr>
		<th scope="row" style="text-align:right;"><?php _e('Title:', 'all_in_one_seo_pack') ?></th>
		<td><input value="<?php echo $title ?>" type="text" name="aiosp_title" size="62"/></td>
		</tr>
		<tr>
		<th scope="row" style="text-align:right;"><?php _e('Description:', 'all_in_one_seo_pack') ?></th>
		<td><textarea name="aiosp_description" rows="3" cols="60"
		onKeyDown="countChars(document.post.aiosp_description,document.post.length1)"
		onKeyUp="countChars(document.post.aiosp_description,document.post.length1)"><?php echo $description ?></textarea><br />
		<input readonly type="text" name="length1" size="3" maxlength="3" value="<?php echo strlen($description);?>" />
		<?php _e(' characters. Most search engines use a maximum of 160 chars for the description.', 'all_in_one_seo_pack') ?>
		</td>
		</tr>
		<tr>
		<th scope="row" style="text-align:right;"><?php _e('Keywords (comma separated):', 'all_in_one_seo_pack') ?></th>
		<td><input value="<?php echo $keywords ?>" type="text" name="aiosp_keywords" size="62"/></td>
		</tr>
		<input type="hidden" name="nonce-aioseop-edit" value="<?php echo wp_create_nonce('edit-aioseop-nonce') ?>" />
<?php if($post->post_type=='page'){ ?>
		<tr>
		<th scope="row" style="text-align:right;"><?php _e('Title Attribute:', 'all_in_one_seo_pack') ?></th>
		<td><input value="<?php echo $aiosp_titleatr ?>" type="text" name="aiosp_titleatr" size="62"/></td>
		</tr>
		
		<tr>
		<th scope="row" style="text-align:right;"><?php _e('Menu Label:', 'all_in_one_seo_pack') ?></th>
		<td><input value="<?php echo $aiosp_menulabel ?>" type="text" name="aiosp_menulabel" size="62"/></td>
		</tr>
<?php } ?>
		<tr>
		<th scope="row" style="text-align:right; vertical-align:top;">
		<?php _e('Disable on this page/post:', 'all_in_one_seo_pack')?>
		</th>
		<td>
		<input type="checkbox" name="aiosp_disable" <?php if ($aiosp_disable) echo "checked=\"1\""; ?>/>
		</td>
		</tr>


		</table>
	<?php
}
?>
