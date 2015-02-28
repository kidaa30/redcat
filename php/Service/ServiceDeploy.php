<?php namespace Surikat\Service;
use Surikat\HTTP\HTTP;
use Surikat\Git\GitDeploy\GitDeploy;
use Suriakt\User\Auth;
class ServiceDeploy{
	function __invoke(){
		Auth::lockServer(Auth::RIGHT_MANAGE);
		self::directOutput();
		GitDeploy::factory(SURIKAT_PATH)
			->maintenanceOn()
			->getChild(SURIKAT_SPATH)
				->deploy()
			->getOrigin()
				->autocommit()
				->deploy()
				->maintenanceOff()
		;
	}
	protected static function directOutput(){
		set_time_limit(0);
		HTTP::nocacheHeaders();
		ob_implicit_flush(true);
		@ob_end_flush();
		echo '<pre>';
	}
	static function single(){
		Auth::lockServer(Auth::RIGHT_MANAGE);
		self::directOutput();
		GitDeploy::factory(SURIKAT_PATH)
			->maintenanceOn()
			->autocommit()
			->deploy()
			->maintenanceOff()
		;
	}
	function version(){
		Auth::lockServer(Auth::RIGHT_MANAGE);
		self::directOutput();
		GitDeploy::factory()
			->maintenanceOn()
			->deploy(SURIKAT_PATH)
			->maintenanceOff()
		;
	}
	function shared(){
		Auth::lockServer(Auth::RIGHT_MANAGE);
		self::directOutput();
		GitDeploy::factory(SURIKAT_PATH)
			->maintenanceOn()
			->autocommit()
			->deploy()
			->getParent(SURIKAT_SPATH)
				->deploy()
			->getOrigin()
				->maintenanceOff()
		;
	}
	function surikat(){
		Auth::lockServer(Auth::RIGHT_MANAGE);
		self::directOutput();
		GitDeploy::factory(SURIKAT_SPATH)
			->maintenanceOn()
			->getParent()
				->deploy()
			->getOrigin()
				->maintenanceOff()
		;
	}
}