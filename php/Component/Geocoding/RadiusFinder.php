<?php namespace Surikat\Component\Geocoding;
use Surikat\Component\Geocoding\Geocoder\Geocoder;
use Surikat\Component\Geocoding\Geocoder\HttpAdapter\CurlHttpAdapter;
use Surikat\Component\Geocoding\Geocoder\Provider\ChainProvider;
use Surikat\Component\Geocoding\Geocoder\Provider\FreeGeoIpProvider;
use Surikat\Component\Geocoding\Geocoder\Provider\HostIpProvider;
use Surikat\Component\Geocoding\Geocoder\Provider\GoogleMapsProvider;
use Surikat\Component\Geocoding\Geocoder\Provider\NominatimProvider;
use Surikat\Component\Geocoding\Geocoder\Provider\OpenStreetMapProvider;

class RadiusFinder{
	static function byAddress($val,&$lat=null,&$lon=null,$debug=false){
		$oLat = $lat;
		$oLon = $lon;
		$geocoder = new Geocoder();
		$adapter  = new CurlHttpAdapter();
		$chain    = new ChainProvider([
			new NominatimProvider($adapter),
			new OpenStreetMapProvider($adapter),
			new GoogleMapsProvider($adapter), //new GoogleMapsProvider($adapter, 'fr_FR', 'France', true),
			new FreeGeoIpProvider($adapter),
			new HostIpProvider($adapter),
			
		]);
		$geocoder->registerProvider($chain);
		try{
			$geocode = $geocoder->geocode($val);
		}
		catch(\Surikat\Component\Geocoding\Geocoder\Exception\ChainNoResultException $e){
			if($debug)
				echo $e->getMessage()."\r\n";
			return 0;
		}
		$bounds = $geocode->getBounds();
		$lon = $geocode->getLongitude();
		$lat = $geocode->getLatitude();
		if(!$bounds){
			$geocode = $geocoder->geocode(self::geocodeToAddr($geocode));
			$bounds = $geocode->getBounds();
			$lon = $geocode->getLongitude();
			$lat = $geocode->getLatitude();
		}
		if($bounds&&$lon&&$lat){
			return self::byBounds($bounds);
		}
		else{
			$lat = $oLat;
			$lon = $oLon;
			return 0;
		}
	}
	static function byBounds($bounds){
		return call_user_func_array(['self','distance'],$bounds)/2.0;
	}
	static function distance($lat1, $lon1, $lat2, $lon2){
		$R = 6371.0; // Radius of the earth in km
		$dLat = ($lat2 - $lat1) * pi() / 180.0;  // deg2rad below
		$dLon = ($lon2 - $lon1) * pi() / 180.0;
		$a = 0.5 - cos($dLat)/2.0 + cos($lat1 * pi() / 180.0) * cos($lat2 * pi() / 180.0) * (1 - cos($dLon))/2;
		return $R * 2 * asin(sqrt($a));
	}
	static function geocodeToAddr($geocode,$keys=[
			'streetNumber',
			'streetName',
			'cityDistrict',
			'city',
			'zipcode',
			'country',
			//'region',
			//'regionCode',
			//'countyCode',
			//'county',
	]){
		$addr = '';
		foreach($keys as $k){
			$m = 'get'.ucfirst($k);
			$t = trim($geocode->$m());
			if($t)
				$addr .= $t.',';
		}
		$addr = rtrim($addr,',');
		return $addr;
	}
}