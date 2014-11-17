<?php namespace surikat;
class dev{
	const NO = 0;
	const CONTROL = 2;
	const VIEW = 4;
	const PRESENT = 8;
	const MODEL = 16;
	const URI = 32;
	const JS = 64;
	const CSS = 128;
	const IMG = 256;
	const STD = 14; //CONTROL+VIEW+PRESENT
	const SERVER = 30; //CONTROL+VIEW+PRESENT+MODEL
	const NAV = 480; //URI+JS+CSS+IMG
	const ALL = 510;
	private static $level;
	static function has($d){
		return $d&self::$level;
	}
	static function on($d){
		return self::$level = $d^self::$level;
	}
	static function off($d){
		return self::$level = $d&self::$level;
	}
	static function level($l=null){
		$oldLevel = self::$level;
		if(isset($l)){
			self::$level = $l;
			self::errorReport(self::$level);
		}
		return $oldLevel;
	}
	static function errorReport($e=true){
		if($e){
			error_reporting(-1);
			ini_set('display_startup_errors',true);
			ini_set('display_errors','stdout');
			ini_set('html_errors',false);
		}
		else{
			error_reporting(0);
			ini_set('display_startup_errors',false);
			ini_set('display_errors',false);
		}
	}
	static function catchException($e){
		echo '<div style="color:#F00;display:block;position:relative;z-index:99999;">! '.$e->getMessage().' <a href="#" onclick="document.getElementById(\''.($id=uniqid('e')).'\').style.visibility=document.getElementById(\''.$id.'\').style.visibility==\'visible\'?\'hidden\':\'visible\';return false;">StackTrace</a></div><pre id="'.$id.'" style="visibility:hidden;display:block;position:relative;z-index:99999;">'.htmlentities($e->getTraceAsString()).'</pre>';
		return false;
	}
}