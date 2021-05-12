<?php

namespace App\Model;

class Locale {
	public static function getLocaleSlug($slug){
		$locale = self::locale();
	    if(empty($locale->{$slug})) {
	        return $slug;
	    } else {
	        return $locale->{$slug};
	    }
	}
	public static function locale(){
		$locale = '{
			"AF":"Afrikaans",
			"SQ":"Albanian",
			"AR":"Arabic",
			"HY":"Armenian",
			"EU":"Basque",
			"BN":"Bengali",
			"BG":"Bulgarian",
			"CA":"Catalan",
			"KM":"Cambodian",
			"ZH":"Chinese (Mandarin)",
			"HR":"Croatian",
			"CS":"Czech",
			"DA":"Danish",
			"NL":"Dutch",
			"EN":"English",
			"ET":"Estonian",
			"FJ":"Fiji",
			"FI":"Finnish",
			"FR":"French",
			"KA":"Georgian",
			"DE":"German",
			"EL":"Greek",
			"GU":"Gujarati",
			"HE":"Hebrew",
			"HI":"Hindi",
			"HU":"Hungarian",
			"IS":"Icelandic",
			"ID":"Indonesian",
			"GA":"Irish",
			"IT":"Italian",
			"JA":"Japanese",
			"JW":"Javanese",
			"KO":"Korean",
			"LA":"Latin",
			"LV":"Latvian",
			"LT":"Lithuanian",
			"MK":"Macedonian",
			"MS":"Malay",
			"ML":"Malayalam",
			"MT":"Maltese",
			"MI":"Maori",
			"MR":"Marathi",
			"MN":"Mongolian",
			"NE":"Nepali",
			"NO":"Norwegian",
			"FA":"Persian",
			"PL":"Polish",
			"PT":"Portuguese",
			"PA":"Punjabi",
			"QU":"Quechua",
			"RO":"Romanian",
			"RU":"Russian",
			"SM":"Samoan",
			"SR":"Serbian",
			"SK":"Slovak",
			"SL":"Slovenian",
			"ES":"Spanish",
			"SW":"Swahili",
			"SV":"Swedish ",
			"TA":"Tamil",
			"TT":"Tatar",
			"TE":"Telugu",
			"TH":"Thai",
			"BO":"Tibetan",
			"TO":"Tonga",
			"TR":"Turkish",
			"UK":"Ukrainian",
			"UR":"Urdu",
			"UZ":"Uzbek",
			"VI":"Vietnamese",
			"CY":"Welsh",
			"XH":"Xhosa"
		}';
		return json_decode($locale);
	}
}
