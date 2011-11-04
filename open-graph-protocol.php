<?php
/**
 * Open Graph Protocol data class. Define and validate OGP values.
 *
 * @package open-graph-protocol-tools
 * @author Niall Kennedy <niall@niallkennedy.com>
 * @version 1.3
 * @copyright Public Domain
 */

/**
 * Open Graph protocol type labels are passed through gettext message interpreters for the current context.
 * Fake the interpreter function alias if not defined
 */
if ( !function_exists('_') ):
function _( $text, $domain='' ) {
	return $text;
}
endif;

/**
 * Validate inputted text against Open Graph Protocol requirements by parameter.
 *
 * @link http://ogp.me/ Open Graph Protocol
 * @version 1.3
 */
class OpenGraphProtocol {
	/**
	 * Version
	 * @var string
	 */
	const VERSION = '1.3';

	/**
	 * Should we remotely request each referenced URL to make sure it exists and returns the expected Internet media type?
	 * @var bool
	 */
	const VERIFY_URLS = false;

	/**
	 * Meta attribute name. Use 'property' if you prefer RDF or 'name' if you prefer HTML validation
	 * @var string
	 */
    const META_ATTR = 'property';

	/**
	 * Property prefix
	 * @var string
	 */
	const PREFIX = 'og';

	/**
	 * prefix namespace
	 * @var string
	 */
	const NS = 'http://ogp.me/ns#';

	/**
	 * Page classification according to a pre-defined set of base types.
	 *
	 * @var string
	 * @since 1.0
	 */
	protected $type;

	/**
	 * The title of your object as it should appear within the graph.
	 *
	 * @var string
	 * @since 1.0
	 */
	protected $title;

	/**
	 * If your object is part of a larger web site, the name which should be displayed for the overall site.
	 *
	 * @var string
	 * @since 1.0
	 */
	protected $site_name;

	/**
	 * A one to two sentence description of your object.
	 *
	 * @var string
	 * @since 1.0
	 */
	protected $description;

	/**
	 * The canonical URL of your object that will be used as its permanent ID in the graph.
	 *
	 * @var string
	 * @since 1.0
	 */
	protected $url;

	/**
	 * The word that appears before this object's title in a sentence
	 *
	 * @var string
	 * @since 1.3
	 */
    protected $determiner;

	/**
	 * Language and optional territory of page content.
	 * @var string
	 * @since 1.3
	 */
	protected $locale;

	/**
	 * An array of OpenGraphProtocolImage objects
	 *
	 * @var array
	 * @since 1.0
	 */
	protected $image;

	/**
	 * An array of OpenGraphProtocolAudio objects
	 *
	 * @var array
	 * @since 1.2
	 */
	protected $audio;

	/**
	 * An array of OpenGraphProtocolVideo objects
	 *
	 * @var array
	 * @since 1.2
	 */
	protected $video;

	/**
	 * Build Open Graph protocol HTML markup based on an array
	 *
	 * @param array $og associative array of OGP properties and values
	 * @param string $prefix optional prefix to prepend to all properties
	 */
	public static function buildHTML( array $og, $prefix=self::PREFIX ) {
		if ( empty($og) )
			return;

		$s = '';
		foreach ( $og as $property => $content ) {
			if ( is_object( $content ) || is_array( $content ) ) {
				if ( is_object( $content ) )
					$content = $content->toArray();
				if ( empty($property) )
					$s .= static::buildHTML( $content, $prefix );
				else
					$s .= static::buildHTML( $content, $prefix . ':' . $property );
			} elseif ( !empty($content) ) {
				$s .= '<meta ' . self::META_ATTR . '="' . $prefix;
				if ( !empty($property) )
					$s .= ':' . htmlspecialchars( $property );
				$s .= '" content="' . htmlspecialchars($content) . '">' . PHP_EOL;
			}
		}
		return $s;
	}

	/**
	 * A list of allowed page types in the Open Graph Protocol
	 *
	 * @param Bool $flatten true for grouped types one level deep
	 * @link http://ogp.me/#types Open Graph Protocol object types
	 * @return array Array of Open Graph Protocol object types
	 */
	public static function supported_types( $flatten=false ) {
		$types = array(
			_('Activities') => array(
				'activity' => _('Activity'),
				'sport' => _('Sport')
			),
			_('Businesses') => array(
				'company' => _('Company'),
				'bar' => _('Bar'),
				'cafe' => _('Cafe'),
				'hotel' => _('Hotel'),
				'restaurant' => ('Restaurant')
			),
			_('Groups') => array(
				'cause' => _('Cause'),
				'sports_league' => _('Sports league'),
				'sports_team' => _('Sports team')
			),
			_('Organizations') => array(
				'band' => _('Band'),
				'government' => _('Government'),
				'non_profit' => _('Non-profit'),
				'school' => _('School'),
				'university' => _('University')
			),
			_('People') => array(
				'actor' => _('Actor or actress'),
				'athlete' => _('Athlete'), 
				'author' => _('Author'),
				'director' => _('Director'),
				'musician' => _('Musician'),
				'politician' => _('Politician'),
				'profile' => _('Profile'),
				'public_figure' => _('Public Figure')
			),
			_('Places') => array(
				'city' => _('City or locality'),
				'country' => _('Country'),
				'landmark' => _('Landmark'),
				'state_province' => _('State or province')
			),
			_('Products and Entertainment') => array(
				'music.album' => _('Music Album'),
				'book' => _('Book'),
				'drink' => _('Drink'),
				'video.episode' => _('Video episode'),
				'food' => _('Food'),
				'game' => _('Game'),
				'video.movie' => _('Movie'),
				'music.playlist' => _('Music playlist'),
				'product' => _('Product'),
				'music.radio_station' => _('Radio station'),
				'music.song' => _('Song'),
				'video.tv_show' => _('Television show'),
				'video.other' => _('Video')
			),
			_('Websites') => array(
				'article' => _('Article'),
				'blog' => _('Blog'),
				'website' => _('Website')
			)
		);
		if ( $flatten === true ) {
			$types_values = array();
			foreach ( $types as $category=>$values ) {
				$types_values = array_merge( $types_values, array_keys($values) );
			}
			return $types_values;
		} else {
			return $types;
		}
	}

	/**
	 * Cleans a URL string, then checks to see if a given URL is addressable, returns a 200 OK response, and matches the accepted Internet media types (if provided).
	 *
	 * @param string $url Publicly addressable URL
	 * @param array $accepted_mimes Given URL correspond to an accepted Internet media (MIME) type.
	 * @return string cleaned URL string, or empty string on failure.
	 */
	public static function is_valid_url( $url, array $accepted_mimes = array() ) {
		if ( !is_string( $url ) || empty( $url ) )
			return '';

		/*
		 * Validate URI string by letting PHP break up the string and put it back together again
		 * Excludes username:password and port number URI parts
		 */
		$url_parts = parse_url( $url );
		$url = '';
		if ( isset( $url_parts['scheme'] ) && in_array( $url_parts['scheme'], array('http', 'https'), true ) ) {
			$url = "{$url_parts['scheme']}://{$url_parts['host']}{$url_parts['path']}";
			if ( empty( $url_parts['path'] ) )
				$url .= '/';
			if ( !empty( $url_parts['query'] ) )
				$url .= '?' . $url_parts['query'];
			if ( !empty( $url_parts['fragment'] ) )
				$url .= '#' . $url_parts['fragment'];
		}

		if ( !empty( $url ) ) {
			// test if URL exists
			$ch = curl_init( $url );
			curl_setopt( $ch, CURLOPT_TIMEOUT, 5 );
			curl_setopt( $ch, CURLOPT_FORBID_REUSE, true );
			curl_setopt( $ch, CURLOPT_NOBODY, true ); // HEAD
			curl_setopt( $ch, CURLOPT_USERAGENT, 'Open Graph protocol validator ' . self::VERSION . ' (+http://ogp.me/)' );
			if ( !empty($accepted_mimes) )
				curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Accept: ' . implode( ',', $accepted_mimes ) ) );
			$response = curl_exec( $ch );
			if ( curl_getinfo( $ch, CURLINFO_HTTP_CODE ) == 200 ) {
				if ( !empty($accepted_mimes) ) {
					$content_type = explode( ';', curl_getinfo( $ch, CURLINFO_CONTENT_TYPE ) );
					if ( empty( $content_type ) || !in_array( $content_type[0], $accepted_mimes ) )
						return '';
				}
			} else {
				return '';
			}
		}
		return $url;
	}

    /**
	 * Output the OpenGraphProtocol object as HTML elements string
	 *
	 * @return string meta elements
	 */
	public function toHTML() {
		return rtrim( static::buildHTML( get_object_vars($this) ), PHP_EOL );
	}

	/**
	 * @return String the type slug
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 *
	 * @param String type slug
	 */
	public function setType( $type ) {
		if ( is_string($type) && in_array( $type, self::supported_types(true), true ) )
			$this->type = $type;
		return $this;
	}

	/**
	 * @return String document title
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @param String $title document title
	 */
	public function setTitle( $title ) {
		if ( is_string($title) ) {
			$title = trim( $title );
			if ( strlen( $title ) > 128 )
				$this->title = substr( $title, 0, 128 );
			else
				$this->title = $title;
		}
		return $this;
	}

	/**
	 * @return String Site name
	 */
	public function getSiteName() {
		return $this->site_name;
	}

	/**
	 * @param String $site_name Site name
	 */
	public function setSiteName( $site_name ) {
		if ( is_string($site_name) && !empty($site_name) ) {
			$site_name = trim( $site_name );
			if ( strlen( $site_name ) > 128 )
				$this->site_name = substr( $site_name, 0, 128 );
			else
				$this->site_name = $site_name;
		}
		return $this;
	}

	/**
	 * @return String Description
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @param String $description Document description
	 */
	public function setDescription( $description ) {
		if ( is_string($description) && !empty($description) ) {
			$description = trim( $description );
			if ( strlen( $description ) > 255 )
				$this->description = substr( $description, 0, 255 );
			else
				$this->description = $description;
		}
		return $this;
	}

	/**
	 * @return String URL
	 */
	public function getURL() {
		return $this->url;
	}

	/**
	 * @param String $url Canonical URL
	 */
	public function setURL( $url ) {
		if ( is_string( $url ) && !empty( $url ) ) {
			$url = trim($url);
			if (self::VERIFY_URLS) {
				$url = self::is_valid_url( $url, array( 'text/html', 'application/xhtml+xml' ) );
			}
			if ( !empty( $url ) )
				$this->url = $url;
		}
		return $this;
	}

	/**
	 * @return string the determiner
	 */
	public function getDeterminer() {
		return $this->determiner;
	}

	public function setDeterminer( $determiner ) {
		if ( in_array($determiner, array('a','an','auto','the'), true) )
			$this->determiner = $determiner;
		return $this;
	}

	/**
	 * @return string language_TERRITORY
	 */
	public function getLocale() {
		return $this->locale;
	}

	/**
	 * @var string $locale locale in the format language_TERRITORY
	 */
	public function setLocale( $locale ) {
		if ( is_string($locale) )
			$this->locale = $locale;
		return $this;
	}

	/**
	 * @return array OpenGraphProtocolImage array
	 */
	public function getImage() {
		return $this->image;
	}

	/**
	 * Add an image.
	 * The first image added is given priority by the Open Graph Protocol spec. Implementors may choose a different image based on size requirements or preferences.
	 *
	 * @param OpenGraphProtocolImage $image image object to add
	 */
	public function addImage( OpenGraphProtocolImage $image ) {
		if ( ! isset( $this->image ) )
			$this->image = array( $image );
		else
			$this->image[] = $image;
		return $this;
	}

	/**
	 * @return array OpenGraphProtocolAudio objects
	 */
	public function getAudio() {
		return $this->audio;
	}

	/**
	 * Add an audio reference
	 * The first audio is given priority by the Open Graph protocol spec.
	 *
	 * @param OpenGraphProtocolAudio $audio audio object to add
	 */
	public function addAudio( OpenGraphProtocolAudio $audio ) {
		if ( ! isset($this->audio) )
			$this->audio = array($audio);
		else
			$this->audio[] = $audio;
		return $this;
	}

	/**
	 * @return array OpenGraphProtocolVideo objects
	 */
	public function getVideo() {
		return $this->video;
	}

	/**
	 * Add a video reference
	 * The first video is given priority by the Open Graph protocol spec. Implementors may choose a different video based on size requirements or preferences.
	 *
	 * @param OpenGraphProtocolVideo $video video object to add
	 */
	public function addVideo( OpenGraphProtocolVideo $video ) {
		if ( ! isset( $this->video ) )
			$this->video = array( $video );
		else
			$this->video[] = $video;
		return $this;
	}
}

include_once dirname(__FILE__) . '/media.php';  // image, video, audio
include_once dirname(__FILE__) . '/objects.php'; // global objects: profile, article
?>