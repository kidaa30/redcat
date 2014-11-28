-- http://sig.cenlr.org/creation_bdd/fonctions/no_html_special_char
CREATE OR REPLACE FUNCTION no_html_specials_chars(text)
  RETURNS text AS
$BODY$
DECLARE
	var_texte ALIAS FOR $1;
	var_chaine text;
BEGIN
	var_chaine:=var_texte;
	var_chaine:=REPLACE(var_chaine,'&quot;','"');
	var_chaine:=REPLACE(var_chaine,'&amp;','&');
	var_chaine:=REPLACE(var_chaine,'&euro;','€');
	var_chaine:=REPLACE(var_chaine,'&lt;','<');
	var_chaine:=REPLACE(var_chaine,'&gt;','>');
	var_chaine:=REPLACE(var_chaine,'&oelig;','œ');
	var_chaine:=REPLACE(var_chaine,'','z');
	var_chaine:=REPLACE(var_chaine,'&Yuml;','Y');
	var_chaine:=REPLACE(var_chaine,'&nbsp;','Space');
	var_chaine:=REPLACE(var_chaine,'&iexcl;','¡');
	var_chaine:=REPLACE(var_chaine,'&cent;','¢');
	var_chaine:=REPLACE(var_chaine,'&pound;','£');
	var_chaine:=REPLACE(var_chaine,'&curren;','¤');
	var_chaine:=REPLACE(var_chaine,'&yen','¥');
	var_chaine:=REPLACE(var_chaine,'&brvbar;','¦');
	var_chaine:=REPLACE(var_chaine,'&sect;','§');
	var_chaine:=REPLACE(var_chaine,'&uml;','¨');
	var_chaine:=REPLACE(var_chaine,'&copy;','©');
	var_chaine:=REPLACE(var_chaine,'&ordf;','ª');
	var_chaine:=REPLACE(var_chaine,'&laquo;','«');
	var_chaine:=REPLACE(var_chaine,'&not;','¬');
	var_chaine:=REPLACE(var_chaine,'&shy;','­');
	var_chaine:=REPLACE(var_chaine,'&reg;','®');
	var_chaine:=REPLACE(var_chaine,'&masr;','¯');
	var_chaine:=REPLACE(var_chaine,'&deg;','°');
	var_chaine:=REPLACE(var_chaine,'&plusmn;','±');
	var_chaine:=REPLACE(var_chaine,'&sup2;','²');
	var_chaine:=REPLACE(var_chaine,'&sup3;','³');
	var_chaine:=REPLACE(var_chaine,'&acute;','''');
	var_chaine:=REPLACE(var_chaine,'&micro;','µ');
	var_chaine:=REPLACE(var_chaine,'&para;','¶');
	var_chaine:=REPLACE(var_chaine,'&middot;','·');
	var_chaine:=REPLACE(var_chaine,'&cedil;','¸');
	var_chaine:=REPLACE(var_chaine,'&sup1;','¹');
	var_chaine:=REPLACE(var_chaine,'&ordm;','º');
	var_chaine:=REPLACE(var_chaine,'&raquo;','»');
	var_chaine:=REPLACE(var_chaine,'&frac14;','¼');
	var_chaine:=REPLACE(var_chaine,'&frac12;','½');
	var_chaine:=REPLACE(var_chaine,'&frac34;','¾');
	var_chaine:=REPLACE(var_chaine,'&iquest;','¿');
	var_chaine:=REPLACE(var_chaine,'&Agrave;','À');
	var_chaine:=REPLACE(var_chaine,'&Aacute;','Á');
	var_chaine:=REPLACE(var_chaine,'&Acirc;','Â');
	var_chaine:=REPLACE(var_chaine,'&Atilde;','Ã');
	var_chaine:=REPLACE(var_chaine,'&Auml;','Ä');
	var_chaine:=REPLACE(var_chaine,'&Aring;','Å');
	var_chaine:=REPLACE(var_chaine,'&Aelig','Æ');
	var_chaine:=REPLACE(var_chaine,'&Ccedil;','Ç');
	var_chaine:=REPLACE(var_chaine,'&Egrave;','È');
	var_chaine:=REPLACE(var_chaine,'&Eacute;','É');
	var_chaine:=REPLACE(var_chaine,'&Ecirc;','Ê');
	var_chaine:=REPLACE(var_chaine,'&Euml;','Ë');
	var_chaine:=REPLACE(var_chaine,'&Igrave;','Ì');
	var_chaine:=REPLACE(var_chaine,'&Iacute;','Í');
	var_chaine:=REPLACE(var_chaine,'&Icirc;','Î');
	var_chaine:=REPLACE(var_chaine,'&Iuml;','Ï');
	var_chaine:=REPLACE(var_chaine,'&eth;','Ð');
	var_chaine:=REPLACE(var_chaine,'&Ntilde;','Ñ');
	var_chaine:=REPLACE(var_chaine,'&Ograve;','Ò');
	var_chaine:=REPLACE(var_chaine,'&Oacute;','Ó');
	var_chaine:=REPLACE(var_chaine,'&Ocirc;','Ô');
	var_chaine:=REPLACE(var_chaine,'&Otilde;','Õ');
	var_chaine:=REPLACE(var_chaine,'&Ouml;','Ö');
	var_chaine:=REPLACE(var_chaine,'&times;','×');
	var_chaine:=REPLACE(var_chaine,'&Oslash;','Ø');
	var_chaine:=REPLACE(var_chaine,'&Ugrave;','Ù');
	var_chaine:=REPLACE(var_chaine,'&Uacute;','Ú');
	var_chaine:=REPLACE(var_chaine,'&Ucirc;','Û');
	var_chaine:=REPLACE(var_chaine,'&Uuml;','Ü');
	var_chaine:=REPLACE(var_chaine,'&Yacute;','Ý');
	var_chaine:=REPLACE(var_chaine,'&thorn;','Þ');
	var_chaine:=REPLACE(var_chaine,'&szlig;','ß');
	var_chaine:=REPLACE(var_chaine,'&agrave;','à');
	var_chaine:=REPLACE(var_chaine,'&aacute;','á');
	var_chaine:=REPLACE(var_chaine,'&acirc;','â');
	var_chaine:=REPLACE(var_chaine,'&atilde;','ã');
	var_chaine:=REPLACE(var_chaine,'&auml;','ä');
	var_chaine:=REPLACE(var_chaine,'&aring;','å');
	var_chaine:=REPLACE(var_chaine,'&aelig;','æ');
	var_chaine:=REPLACE(var_chaine,'&ccedil;','ç');
	var_chaine:=REPLACE(var_chaine,'&egrave;','è');
	var_chaine:=REPLACE(var_chaine,'&eacute;','é');
	var_chaine:=REPLACE(var_chaine,'&ecirc;','ê');
	var_chaine:=REPLACE(var_chaine,'&euml;','ë');
	var_chaine:=REPLACE(var_chaine,'&igrave;','ì');
	var_chaine:=REPLACE(var_chaine,'&iacute;','í');
	var_chaine:=REPLACE(var_chaine,'&icirc;','î');
	var_chaine:=REPLACE(var_chaine,'&iuml;','ï');
	var_chaine:=REPLACE(var_chaine,'&eth;','ð');
	var_chaine:=REPLACE(var_chaine,'&ntilde;','ñ');
	var_chaine:=REPLACE(var_chaine,'&ograve;','ò');
	var_chaine:=REPLACE(var_chaine,'&oacute;','ó');
	var_chaine:=REPLACE(var_chaine,'&ocirc;','ô');
	var_chaine:=REPLACE(var_chaine,'&otilde;','õ');
	var_chaine:=REPLACE(var_chaine,'&ouml;','ö');
	var_chaine:=REPLACE(var_chaine,'&divide;','÷');
	var_chaine:=REPLACE(var_chaine,'&oslash;','ø');
	var_chaine:=REPLACE(var_chaine,'&ugrave;','ù');
	var_chaine:=REPLACE(var_chaine,'&uacute;','ú');
	var_chaine:=REPLACE(var_chaine,'&ucirc;','û');
	var_chaine:=REPLACE(var_chaine,'&uuml;','ü');
	var_chaine:=REPLACE(var_chaine,'&yacute;','ý');
	var_chaine:=REPLACE(var_chaine,'&thorn;','þ');
	var_chaine:=REPLACE(var_chaine,'&yuml;','ÿ');
	RETURN var_chaine;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;