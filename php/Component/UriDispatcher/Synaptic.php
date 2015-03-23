<?php namespace Surikat\Component\UriDispatcher;
class Synaptic extends UriDispatcher{
	protected $pathFS;
	function __construct($pathFS=''){
		$this->pathFS = rtrim($pathFS,'/');
		if(!empty($this->pathFS))
			$this->pathFS .= '/';
	}
	function __invoke(){
		list($filename,$file) = func_get_args();
		$this->FileSystem_Synaptic->load($this->pathFS.$file);
	}
}