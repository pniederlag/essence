<?php

/**
 *	@author Félix Girault <felix.girault@gmail.com>
 *	@license FreeBSD License (http://opensource.org/licenses/BSD-2-Clause)
 */

namespace Essence;



/**
 *	Gathers embed informations from URLs.
 *
 *	@package Essence
 */

class Essence {

	/**
	 *	A collection of providers to query.	
	 *
	 *	@var \Essence\ProviderCollection
	 */

	protected $_ProviderCollection = null;



	/**
	 *	Constructor.
	 */

	protected function __construct( ) {

		$this->_ProviderCollection = new ProviderCollection( );
	}



	/**
	 *	Returns a singleton instance of Essence.
	 *	
	 *	@return \Embed\Essence Singleton instance.
	 */

	protected static function _instance( ) {

		static $Instance = null;

		if ( $Instance === null ) {
			$Instance = new self( );
		}

		return $Instance;
	}



	/**
	 *	Configures the Essence to query the given providers.
	 *	Throws an exception if a Provider couldn't be found.
	 *
	 *	@see \Essence\ProviderCollection::load( )
	 *	@param array $providers An array of provider class names, relative to
	 *		the 'Provider' folder.
	 *	@throws \Essence\Exception 
	 */

	public static function configure( array $providers ) {

		$_this = self::_instance( );
		$_this->_ProviderCollection->load( $providers );
	}



	/**
	 *	If the url can be parser directly by one of the registered providers,
	 *	it is returned as is. Otherwise, the page is parsed to find such urls.
	 *
	 *	@param string $url The Url to extract.
	 */

	public function extract( $url ) {

		$_this = self::_instance( );

		// if a provider can directly handle the url, there is no more work to do.

		if ( $_this->_ProviderCollection->hasProvider( $url )) {
			return array( $url );
		}

		// fetching the page

		try {
			$html = Http::get( $url );
		} catch ( Exception $e ) {
			return array( );
		}

		// extraction of possible urls

		$result = preg_match_all(
			'#<(a|iframe|embed)[^>]+(href|src)=("|\')(?P<source>[^"\']+)#i',
			$html,
			$matches
		);

		$urls = array( );

		foreach ( $matches['source'] as $source ) {
			if ( $this->_ProviderCollection->hasProvider( $source )) {
				$urls[] = $source;
			}
		}

		return array_values( array_unique( $urls )); // array_values reindexes the array
	}



	/**
	 *	Fetches embed informations from the given URL.
	 *
	 *	@param string $url URL to fetch informations from.
	 *	@return \Essence\Embed Embed informations.
	 */

	public function fetch( $url ) {

		$_this = self::_instance( );

		$index = $_this->_ProviderCollection->providerIndex( $url );
		$Embed = null;

		while ( $index !== false ) {
			$Provider = $_this->_ProviderCollection->provider( $index );

			try {
				$Embed = $Provider->fetch( $url );
			} catch ( Exception $e ) {

			}

			$index = ( $Embed === null )
				? $_this->_ProviderCollection->providerIndex( $url, $index )
				: false;
		}

		return $Embed;
	}



	/**
	 *	Fetches embed informations from the given URLs.
	 *
	 *	@param array $urls An array of URLs to fetch informations from.
	 *	@return array An array of embed informations, indexed by URL.
	 */

	public function fetchAll( array $urls ) {
		
		$infos = array( );

		foreach ( $urls as $url ) {
			$data = $this->fetch( $url );

			if ( $data ) {
				$infos[ $url ] = $data;
			}
		}

		return $infos;
	}
}