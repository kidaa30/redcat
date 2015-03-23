<?php namespace Surikat\Component\Vars;
abstract class STR{
	static function ucfirst($str){
		return self::toupper(mb_substr($str,0,1,mb_detect_encoding($str))).mb_substr($str,1,null,mb_detect_encoding($str));
	}
	static function lcfirst($str){
		return self::tolower(mb_substr($str,0,1,mb_detect_encoding($str))).mb_substr($str,1,null,mb_detect_encoding($str));
	}
	static function toupper($str){
		return mb_strtoupper($str,mb_detect_encoding($str));
	}
	static function tolower($str){
		return mb_strtolower($str,mb_detect_encoding($str));
	}
	// An example string like ÀØėÿᾜὨζὅБю will be translated to AOeyIOzoBY More complete than : strtr((string)$str,"ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ","aaaaaaaaaaaaooooooooooooeeeeeeeecciiiiiiiiuuuuuuuuynn"); @author http://www.evaisse.net/2008/php-translit-remove-accent-unaccent-21001
	static function unaccent($str, $utf8 = true){
		static $transliteration = [
			'Ĳ' => 'I', 'Ö' => 'O', 'Œ' => 'O', 'Ü' => 'U', 'ä' => 'a', 'æ' => 'a',
			'ĳ' => 'i', 'ö' => 'o', 'œ' => 'o', 'ü' => 'u', 'ß' => 's', 'ſ' => 's',
			'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
			'Æ' => 'A', 'Ā' => 'A', 'Ą' => 'A', 'Ă' => 'A', 'Ç' => 'C', 'Ć' => 'C',
			'Č' => 'C', 'Ĉ' => 'C', 'Ċ' => 'C', 'Ď' => 'D', 'Đ' => 'D', 'È' => 'E',
			'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ē' => 'E', 'Ę' => 'E', 'Ě' => 'E',
			'Ĕ' => 'E', 'Ė' => 'E', 'Ĝ' => 'G', 'Ğ' => 'G', 'Ġ' => 'G', 'Ģ' => 'G',
			'Ĥ' => 'H', 'Ħ' => 'H', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
			'Ī' => 'I', 'Ĩ' => 'I', 'Ĭ' => 'I', 'Į' => 'I', 'İ' => 'I', 'Ĵ' => 'J',
			'Ķ' => 'K', 'Ľ' => 'K', 'Ĺ' => 'K', 'Ļ' => 'K', 'Ŀ' => 'K', 'Ł' => 'L',
			'Ñ' => 'N', 'Ń' => 'N', 'Ň' => 'N', 'Ņ' => 'N', 'Ŋ' => 'N', 'Ò' => 'O',
			'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ø' => 'O', 'Ō' => 'O', 'Ő' => 'O',
			'Ŏ' => 'O', 'Ŕ' => 'R', 'Ř' => 'R', 'Ŗ' => 'R', 'Ś' => 'S', 'Ş' => 'S',
			'Ŝ' => 'S', 'Ș' => 'S', 'Š' => 'S', 'Ť' => 'T', 'Ţ' => 'T', 'Ŧ' => 'T',
			'Ț' => 'T', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ū' => 'U', 'Ů' => 'U',
			'Ű' => 'U', 'Ŭ' => 'U', 'Ũ' => 'U', 'Ų' => 'U', 'Ŵ' => 'W', 'Ŷ' => 'Y',
			'Ÿ' => 'Y', 'Ý' => 'Y', 'Ź' => 'Z', 'Ż' => 'Z', 'Ž' => 'Z', 'à' => 'a',
			'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ā' => 'a', 'ą' => 'a', 'ă' => 'a',
			'å' => 'a', 'ç' => 'c', 'ć' => 'c', 'č' => 'c', 'ĉ' => 'c', 'ċ' => 'c',
			'ď' => 'd', 'đ' => 'd', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
			'ē' => 'e', 'ę' => 'e', 'ě' => 'e', 'ĕ' => 'e', 'ė' => 'e', 'ƒ' => 'f',
			'ĝ' => 'g', 'ğ' => 'g', 'ġ' => 'g', 'ģ' => 'g', 'ĥ' => 'h', 'ħ' => 'h',
			'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ī' => 'i', 'ĩ' => 'i',
			'ĭ' => 'i', 'į' => 'i', 'ı' => 'i', 'ĵ' => 'j', 'ķ' => 'k', 'ĸ' => 'k',
			'ł' => 'l', 'ľ' => 'l', 'ĺ' => 'l', 'ļ' => 'l', 'ŀ' => 'l', 'ñ' => 'n',
			'ń' => 'n', 'ň' => 'n', 'ņ' => 'n', 'ŉ' => 'n', 'ŋ' => 'n', 'ò' => 'o',
			'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ø' => 'o', 'ō' => 'o', 'ő' => 'o',
			'ŏ' => 'o', 'ŕ' => 'r', 'ř' => 'r', 'ŗ' => 'r', 'ś' => 's', 'š' => 's',
			'ť' => 't', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ū' => 'u', 'ů' => 'u',
			'ű' => 'u', 'ŭ' => 'u', 'ũ' => 'u', 'ų' => 'u', 'ŵ' => 'w', 'ÿ' => 'y',
			'ý' => 'y', 'ŷ' => 'y', 'ż' => 'z', 'ź' => 'z', 'ž' => 'z', 'Α' => 'A',
			'Ά' => 'A', 'Ἀ' => 'A', 'Ἁ' => 'A', 'Ἂ' => 'A', 'Ἃ' => 'A', 'Ἄ' => 'A',
			'Ἅ' => 'A', 'Ἆ' => 'A', 'Ἇ' => 'A', 'ᾈ' => 'A', 'ᾉ' => 'A', 'ᾊ' => 'A',
			'ᾋ' => 'A', 'ᾌ' => 'A', 'ᾍ' => 'A', 'ᾎ' => 'A', 'ᾏ' => 'A', 'Ᾰ' => 'A',
			'Ᾱ' => 'A', 'Ὰ' => 'A', 'ᾼ' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D',
			'Ε' => 'E', 'Έ' => 'E', 'Ἐ' => 'E', 'Ἑ' => 'E', 'Ἒ' => 'E', 'Ἓ' => 'E',
			'Ἔ' => 'E', 'Ἕ' => 'E', 'Ὲ' => 'E', 'Ζ' => 'Z', 'Η' => 'I', 'Ή' => 'I',
			'Ἠ' => 'I', 'Ἡ' => 'I', 'Ἢ' => 'I', 'Ἣ' => 'I', 'Ἤ' => 'I', 'Ἥ' => 'I',
			'Ἦ' => 'I', 'Ἧ' => 'I', 'ᾘ' => 'I', 'ᾙ' => 'I', 'ᾚ' => 'I', 'ᾛ' => 'I',
			'ᾜ' => 'I', 'ᾝ' => 'I', 'ᾞ' => 'I', 'ᾟ' => 'I', 'Ὴ' => 'I', 'ῌ' => 'I',
			'Θ' => 'T', 'Ι' => 'I', 'Ί' => 'I', 'Ϊ' => 'I', 'Ἰ' => 'I', 'Ἱ' => 'I',
			'Ἲ' => 'I', 'Ἳ' => 'I', 'Ἴ' => 'I', 'Ἵ' => 'I', 'Ἶ' => 'I', 'Ἷ' => 'I',
			'Ῐ' => 'I', 'Ῑ' => 'I', 'Ὶ' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M',
			'Ν' => 'N', 'Ξ' => 'K', 'Ο' => 'O', 'Ό' => 'O', 'Ὀ' => 'O', 'Ὁ' => 'O',
			'Ὂ' => 'O', 'Ὃ' => 'O', 'Ὄ' => 'O', 'Ὅ' => 'O', 'Ὸ' => 'O', 'Π' => 'P',
			'Ρ' => 'R', 'Ῥ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Ύ' => 'Y',
			'Ϋ' => 'Y', 'Ὑ' => 'Y', 'Ὓ' => 'Y', 'Ὕ' => 'Y', 'Ὗ' => 'Y', 'Ῠ' => 'Y',
			'Ῡ' => 'Y', 'Ὺ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'P', 'Ω' => 'O',
			'Ώ' => 'O', 'Ὠ' => 'O', 'Ὡ' => 'O', 'Ὢ' => 'O', 'Ὣ' => 'O', 'Ὤ' => 'O',
			'Ὥ' => 'O', 'Ὦ' => 'O', 'Ὧ' => 'O', 'ᾨ' => 'O', 'ᾩ' => 'O', 'ᾪ' => 'O',
			'ᾫ' => 'O', 'ᾬ' => 'O', 'ᾭ' => 'O', 'ᾮ' => 'O', 'ᾯ' => 'O', 'Ὼ' => 'O',
			'ῼ' => 'O', 'α' => 'a', 'ά' => 'a', 'ἀ' => 'a', 'ἁ' => 'a', 'ἂ' => 'a',
			'ἃ' => 'a', 'ἄ' => 'a', 'ἅ' => 'a', 'ἆ' => 'a', 'ἇ' => 'a', 'ᾀ' => 'a',
			'ᾁ' => 'a', 'ᾂ' => 'a', 'ᾃ' => 'a', 'ᾄ' => 'a', 'ᾅ' => 'a', 'ᾆ' => 'a',
			'ᾇ' => 'a', 'ὰ' => 'a', 'ᾰ' => 'a', 'ᾱ' => 'a', 'ᾲ' => 'a', 'ᾳ' => 'a',
			'ᾴ' => 'a', 'ᾶ' => 'a', 'ᾷ' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd',
			'ε' => 'e', 'έ' => 'e', 'ἐ' => 'e', 'ἑ' => 'e', 'ἒ' => 'e', 'ἓ' => 'e',
			'ἔ' => 'e', 'ἕ' => 'e', 'ὲ' => 'e', 'ζ' => 'z', 'η' => 'i', 'ή' => 'i',
			'ἠ' => 'i', 'ἡ' => 'i', 'ἢ' => 'i', 'ἣ' => 'i', 'ἤ' => 'i', 'ἥ' => 'i',
			'ἦ' => 'i', 'ἧ' => 'i', 'ᾐ' => 'i', 'ᾑ' => 'i', 'ᾒ' => 'i', 'ᾓ' => 'i',
			'ᾔ' => 'i', 'ᾕ' => 'i', 'ᾖ' => 'i', 'ᾗ' => 'i', 'ὴ' => 'i', 'ῂ' => 'i',
			'ῃ' => 'i', 'ῄ' => 'i', 'ῆ' => 'i', 'ῇ' => 'i', 'θ' => 't', 'ι' => 'i',
			'ί' => 'i', 'ϊ' => 'i', 'ΐ' => 'i', 'ἰ' => 'i', 'ἱ' => 'i', 'ἲ' => 'i',
			'ἳ' => 'i', 'ἴ' => 'i', 'ἵ' => 'i', 'ἶ' => 'i', 'ἷ' => 'i', 'ὶ' => 'i',
			'ῐ' => 'i', 'ῑ' => 'i', 'ῒ' => 'i', 'ῖ' => 'i', 'ῗ' => 'i', 'κ' => 'k',
			'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => 'k', 'ο' => 'o', 'ό' => 'o',
			'ὀ' => 'o', 'ὁ' => 'o', 'ὂ' => 'o', 'ὃ' => 'o', 'ὄ' => 'o', 'ὅ' => 'o',
			'ὸ' => 'o', 'π' => 'p', 'ρ' => 'r', 'ῤ' => 'r', 'ῥ' => 'r', 'σ' => 's',
			'ς' => 's', 'τ' => 't', 'υ' => 'y', 'ύ' => 'y', 'ϋ' => 'y', 'ΰ' => 'y',
			'ὐ' => 'y', 'ὑ' => 'y', 'ὒ' => 'y', 'ὓ' => 'y', 'ὔ' => 'y', 'ὕ' => 'y',
			'ὖ' => 'y', 'ὗ' => 'y', 'ὺ' => 'y', 'ῠ' => 'y', 'ῡ' => 'y', 'ῢ' => 'y',
			'ῦ' => 'y', 'ῧ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'p', 'ω' => 'o',
			'ώ' => 'o', 'ὠ' => 'o', 'ὡ' => 'o', 'ὢ' => 'o', 'ὣ' => 'o', 'ὤ' => 'o',
			'ὥ' => 'o', 'ὦ' => 'o', 'ὧ' => 'o', 'ᾠ' => 'o', 'ᾡ' => 'o', 'ᾢ' => 'o',
			'ᾣ' => 'o', 'ᾤ' => 'o', 'ᾥ' => 'o', 'ᾦ' => 'o', 'ᾧ' => 'o', 'ὼ' => 'o',
			'ῲ' => 'o', 'ῳ' => 'o', 'ῴ' => 'o', 'ῶ' => 'o', 'ῷ' => 'o', 'А' => 'A',
			'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'E',
			'Ж' => 'Z', 'З' => 'Z', 'И' => 'I', 'Й' => 'I', 'К' => 'K', 'Л' => 'L',
			'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S',
			'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'K', 'Ц' => 'T', 'Ч' => 'C',
			'Ш' => 'S', 'Щ' => 'S', 'Ы' => 'Y', 'Э' => 'E', 'Ю' => 'Y', 'Я' => 'Y',
			'а' => 'A', 'б' => 'B', 'в' => 'V', 'г' => 'G', 'д' => 'D', 'е' => 'E',
			'ё' => 'E', 'ж' => 'Z', 'з' => 'Z', 'и' => 'I', 'й' => 'I', 'к' => 'K',
			'л' => 'L', 'м' => 'M', 'н' => 'N', 'о' => 'O', 'п' => 'P', 'р' => 'R',
			'с' => 'S', 'т' => 'T', 'у' => 'U', 'ф' => 'F', 'х' => 'K', 'ц' => 'T',
			'ч' => 'C', 'ш' => 'S', 'щ' => 'S', 'ы' => 'Y', 'э' => 'E', 'ю' => 'Y',
			'я' => 'Y', 'ð' => 'd', 'Ð' => 'D', 'þ' => 't', 'Þ' => 'T', 'ა' => 'a',
			'ბ' => 'b', 'გ' => 'g', 'დ' => 'd', 'ე' => 'e', 'ვ' => 'v', 'ზ' => 'z',
			'თ' => 't', 'ი' => 'i', 'კ' => 'k', 'ლ' => 'l', 'მ' => 'm', 'ნ' => 'n',
			'ო' => 'o', 'პ' => 'p', 'ჟ' => 'z', 'რ' => 'r', 'ს' => 's', 'ტ' => 't',
			'უ' => 'u', 'ფ' => 'p', 'ქ' => 'k', 'ღ' => 'g', 'ყ' => 'q', 'შ' => 's',
			'ჩ' => 'c', 'ც' => 't', 'ძ' => 'd', 'წ' => 't', 'ჭ' => 'c', 'ხ' => 'k',
			'ჯ' => 'j', 'ჰ' => 'h',
		];
		$str = (string)$str;
		if(is_null($utf8)){
			if(!function_exists('mb_detect_encoding'))
				$utf8 = (strtolower(mb_detect_encoding($str)) == 'utf-8');
			else{
				$length = strlen($str);
				$utf8 = true;
				for ($i = 0; $i < $length; $i++) {
					$c = ord($str[$i]);
					if ($c < 0x80) $n = 0; // 0bbbbbbb
					elseif (($c & 0xE0) == 0xC0) $n = 1; // 110bbbbb
					elseif (($c & 0xF0) == 0xE0) $n = 2; // 1110bbbb
					elseif (($c & 0xF8) == 0xF0) $n = 3; // 11110bbb
					elseif (($c & 0xFC) == 0xF8) $n = 4; // 111110bb
					elseif (($c & 0xFE) == 0xFC) $n = 5; // 1111110b
					else return false; // Does not match any model
					for ($j = 0; $j < $n; $j++) { // n bytes matching 10bbbbbb follow ?
						if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80)) {
							$utf8 = false;
							break;
						}
					}
				}
			}
		}
		if(!$utf8)
			$str = utf8_encode($str);
		return str_replace(array_keys($transliteration), array_values($transliteration), $str);
	}
	
	static function html_convert_entities($string) {
		return preg_replace_callback('/&([a-zA-Z][a-zA-Z0-9]+);/S',['self','convert_entity'], $string);
	}
	 
	/* Swap HTML named entity with its numeric equivalent. If the entity
	* isn't in the lookup table, this function returns a blank, which
	* destroys the character in the output - this is probably the
	* desired behaviour when producing XML. */
	static function convert_entity($matches) {
		static $table = ['quot' => '&#34;',
			'amp' => '&#38;',
			'lt' => '&#60;',
			'gt' => '&#62;',
			'OElig' => '&#338;',
			'oelig' => '&#339;',
			'Scaron' => '&#352;',
			'scaron' => '&#353;',
			'Yuml' => '&#376;',
			'circ' => '&#710;',
			'tilde' => '&#732;',
			'ensp' => '&#8194;',
			'emsp' => '&#8195;',
			'thinsp' => '&#8201;',
			'zwnj' => '&#8204;',
			'zwj' => '&#8205;',
			'lrm' => '&#8206;',
			'rlm' => '&#8207;',
			'ndash' => '&#8211;',
			'mdash' => '&#8212;',
			'lsquo' => '&#8216;',
			'rsquo' => '&#8217;',
			'sbquo' => '&#8218;',
			'ldquo' => '&#8220;',
			'rdquo' => '&#8221;',
			'bdquo' => '&#8222;',
			'dagger' => '&#8224;',
			'Dagger' => '&#8225;',
			'permil' => '&#8240;',
			'lsaquo' => '&#8249;',
			'rsaquo' => '&#8250;',
			'euro' => '&#8364;',
			'fnof' => '&#402;',
			'Alpha' => '&#913;',
			'Beta' => '&#914;',
			'Gamma' => '&#915;',
			'Delta' => '&#916;',
			'Epsilon' => '&#917;',
			'Zeta' => '&#918;',
			'Eta' => '&#919;',
			'Theta' => '&#920;',
			'Iota' => '&#921;',
			'Kappa' => '&#922;',
			'Lambda' => '&#923;',
			'Mu' => '&#924;',
			'Nu' => '&#925;',
			'Xi' => '&#926;',
			'Omicron' => '&#927;',
			'Pi' => '&#928;',
			'Rho' => '&#929;',
			'Sigma' => '&#931;',
			'Tau' => '&#932;',
			'Upsilon' => '&#933;',
			'Phi' => '&#934;',
			'Chi' => '&#935;',
			'Psi' => '&#936;',
			'Omega' => '&#937;',
			'alpha' => '&#945;',
			'beta' => '&#946;',
			'gamma' => '&#947;',
			'delta' => '&#948;',
			'epsilon' => '&#949;',
			'zeta' => '&#950;',
			'eta' => '&#951;',
			'theta' => '&#952;',
			'iota' => '&#953;',
			'kappa' => '&#954;',
			'lambda' => '&#955;',
			'mu' => '&#956;',
			'nu' => '&#957;',
			'xi' => '&#958;',
			'omicron' => '&#959;',
			'pi' => '&#960;',
			'rho' => '&#961;',
			'sigmaf' => '&#962;',
			'sigma' => '&#963;',
			'tau' => '&#964;',
			'upsilon' => '&#965;',
			'phi' => '&#966;',
			'chi' => '&#967;',
			'psi' => '&#968;',
			'omega' => '&#969;',
			'thetasym' => '&#977;',
			'upsih' => '&#978;',
			'piv' => '&#982;',
			'bull' => '&#8226;',
			'hellip' => '&#8230;',
			'prime' => '&#8242;',
			'Prime' => '&#8243;',
			'oline' => '&#8254;',
			'frasl' => '&#8260;',
			'weierp' => '&#8472;',
			'image' => '&#8465;',
			'real' => '&#8476;',
			'trade' => '&#8482;',
			'alefsym' => '&#8501;',
			'larr' => '&#8592;',
			'uarr' => '&#8593;',
			'rarr' => '&#8594;',
			'darr' => '&#8595;',
			'harr' => '&#8596;',
			'crarr' => '&#8629;',
			'lArr' => '&#8656;',
			'uArr' => '&#8657;',
			'rArr' => '&#8658;',
			'dArr' => '&#8659;',
			'hArr' => '&#8660;',
			'forall' => '&#8704;',
			'part' => '&#8706;',
			'exist' => '&#8707;',
			'empty' => '&#8709;',
			'nabla' => '&#8711;',
			'isin' => '&#8712;',
			'notin' => '&#8713;',
			'ni' => '&#8715;',
			'prod' => '&#8719;',
			'sum' => '&#8721;',
			'minus' => '&#8722;',
			'lowast' => '&#8727;',
			'radic' => '&#8730;',
			'prop' => '&#8733;',
			'infin' => '&#8734;',
			'ang' => '&#8736;',
			'and' => '&#8743;',
			'or' => '&#8744;',
			'cap' => '&#8745;',
			'cup' => '&#8746;',
			'int' => '&#8747;',
			'there4' => '&#8756;',
			'sim' => '&#8764;',
			'cong' => '&#8773;',
			'asymp' => '&#8776;',
			'ne' => '&#8800;',
			'equiv' => '&#8801;',
			'le' => '&#8804;',
			'ge' => '&#8805;',
			'sub' => '&#8834;',
			'sup' => '&#8835;',
			'nsub' => '&#8836;',
			'sube' => '&#8838;',
			'supe' => '&#8839;',
			'oplus' => '&#8853;',
			'otimes' => '&#8855;',
			'perp' => '&#8869;',
			'sdot' => '&#8901;',
			'lceil' => '&#8968;',
			'rceil' => '&#8969;',
			'lfloor' => '&#8970;',
			'rfloor' => '&#8971;',
			'lang' => '&#9001;',
			'rang' => '&#9002;',
			'loz' => '&#9674;',
			'spades' => '&#9824;',
			'clubs' => '&#9827;',
			'hearts' => '&#9829;',
			'diams' => '&#9830;',
			'nbsp' => '&#160;',
			'iexcl' => '&#161;',
			'cent' => '&#162;',
			'pound' => '&#163;',
			'curren' => '&#164;',
			'yen' => '&#165;',
			'brvbar' => '&#166;',
			'sect' => '&#167;',
			'uml' => '&#168;',
			'copy' => '&#169;',
			'ordf' => '&#170;',
			'laquo' => '&#171;',
			'not' => '&#172;',
			'shy' => '&#173;',
			'reg' => '&#174;',
			'macr' => '&#175;',
			'deg' => '&#176;',
			'plusmn' => '&#177;',
			'sup2' => '&#178;',
			'sup3' => '&#179;',
			'acute' => '&#180;',
			'micro' => '&#181;',
			'para' => '&#182;',
			'middot' => '&#183;',
			'cedil' => '&#184;',
			'sup1' => '&#185;',
			'ordm' => '&#186;',
			'raquo' => '&#187;',
			'frac14' => '&#188;',
			'frac12' => '&#189;',
			'frac34' => '&#190;',
			'iquest' => '&#191;',
			'Agrave' => '&#192;',
			'Aacute' => '&#193;',
			'Acirc' => '&#194;',
			'Atilde' => '&#195;',
			'Auml' => '&#196;',
			'Aring' => '&#197;',
			'AElig' => '&#198;',
			'Ccedil' => '&#199;',
			'Egrave' => '&#200;',
			'Eacute' => '&#201;',
			'Ecirc' => '&#202;',
			'Euml' => '&#203;',
			'Igrave' => '&#204;',
			'Iacute' => '&#205;',
			'Icirc' => '&#206;',
			'Iuml' => '&#207;',
			'ETH' => '&#208;',
			'Ntilde' => '&#209;',
			'Ograve' => '&#210;',
			'Oacute' => '&#211;',
			'Ocirc' => '&#212;',
			'Otilde' => '&#213;',
			'Ouml' => '&#214;',
			'times' => '&#215;',
			'Oslash' => '&#216;',
			'Ugrave' => '&#217;',
			'Uacute' => '&#218;',
			'Ucirc' => '&#219;',
			'Uuml' => '&#220;',
			'Yacute' => '&#221;',
			'THORN' => '&#222;',
			'szlig' => '&#223;',
			'agrave' => '&#224;',
			'aacute' => '&#225;',
			'acirc' => '&#226;',
			'atilde' => '&#227;',
			'auml' => '&#228;',
			'aring' => '&#229;',
			'aelig' => '&#230;',
			'ccedil' => '&#231;',
			'egrave' => '&#232;',
			'eacute' => '&#233;',
			'ecirc' => '&#234;',
			'euml' => '&#235;',
			'igrave' => '&#236;',
			'iacute' => '&#237;',
			'icirc' => '&#238;',
			'iuml' => '&#239;',
			'eth' => '&#240;',
			'ntilde' => '&#241;',
			'ograve' => '&#242;',
			'oacute' => '&#243;',
			'ocirc' => '&#244;',
			'otilde' => '&#245;',
			'ouml' => '&#246;',
			'divide' => '&#247;',
			'oslash' => '&#248;',
			'ugrave' => '&#249;',
			'uacute' => '&#250;',
			'ucirc' => '&#251;',
			'uuml' => '&#252;',
			'yacute' => '&#253;',
			'thorn' => '&#254;',
			'yuml' => '&#255;'
		];
		return isset($table[$matches[1]]) ? $table[$matches[1]] : ''; // Entity not found? Destroy it.
	}
	static function cleanXhtml($str){
		return self::html_convert_entities($str);
	}
	

	static function posnth($haystack,$needle,$n,$offset=0){
		$l = strlen($needle);
		for($i=0;$i<=$n;$i++){
			$indx = strpos($haystack, $needle, $offset);
			if($i==$n||$indx===false)
				return $indx;
			else
				$offset = $indx+$l;
		}
		return false;
	}

	static function is_numeric($var){
		for ($i=0;$i<strlen($var);$i++){
			$ascii_code=ord($var[$i]);
			if ($ascii_code >=49 && $ascii_code <=57)
				continue;
			else
				return false;
		}
		return true;
	}
	static function is_integer($var){
		return self::is_numeric($var)&&floor($var)==$var;
	}

}