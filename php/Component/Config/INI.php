<?php namespace Surikat\Component\Config;
use Surikat\Component\FileSystem\INI as FileINI;
class INI extends Config {
	protected $extension = '.ini';
	protected function getConf($inc){
		return parse_ini_file($inc,true);
	}
	protected function getString(){
		return FileINI::arrayToStr($this->conf);
	}
}