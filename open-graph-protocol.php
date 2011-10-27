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
	public static function buildHTML( array $og, $prefix='og' ) {
		if ( empty($og) )
			return;

		$s = '';
		foreach ( $og as $property => $content ) {
			if ( is_object( $content ) || is_array( $content ) ) {
				if ( is_object( $content ) )
					$content = $content->toArray();
				$newprefix = $prefix;
				if ( empty($property) )
					$s .= static::buildHTML( $content, $prefix );
				else
					$s .= static::buildHTML( $content, $prefix . ':' . $property );
			} elseif ( !empty($property) && !empty($content) ) {
				$s .= '<meta ' . self::META_ATTR . '="' . $prefix . ':' . htmlspecialchars( $property ) . '" content="' . htmlspecialchars($content) . '">' . PHP_EOL;
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

/**
 * Describe a media object
 *
 * @version 1.3
 */
abstract class OpenGraphProtocolMedia {

	/**
	 * HTTP scheme URL
	 *
	 * @var string
	 * @since 1.3
	 */
	protected $url;

	/**
	 * HTTPS scheme URL
	 *
	 * @var string
	 * @since 1.3
	 */
	protected $secure_url;

	/**
	 * Internet media type of the linked URLs
	 *
	 * @var string
	 * @since 1.3
	 */
	protected $type;

	public function toArray() {
		return get_object_vars($this);
	}

	/**
	 * @return string URL string or null if not set
	 */
    public function getURL() {
    	return $this->url;
    }

	/**
	 * Set the media URL
	 *
	 * @param string $url resource location
	 */
	public function setURL( $url ) {
		if ( is_string( $url ) && !empty( $url ) ) {
			$url = trim($url);
			if (OpenGraphProtocol::VERIFY_URLS) {
				$url = OpenGraphProtocol::is_valid_url( $url, array( 'text/html', 'application/xhtml+xml' ) );
			}
			if (!empty($url))
				$this->url = $url;
		}
		return $this;
	}

	/**
	 * @return string secure URL string or null if not set
	 */
	public function getSecureURL() {
		return $this->url;
	}

	/**
	 * Set the secure URL for display in a HTTPS page
	 *
	 * @param string $url resource location
	 */
	public function setSecureURL( $url ) {
		if ( is_string( $url ) && !empty( $url ) ) {
			$url = trim($url);
			if (OpenGraphProtocol::VERIFY_URLS) {
				if ( parse_url($url,PHP_URL_SCHEME) === 'https' ) {
					$url = OpenGraphProtocol::is_valid_url( $url, array( 'text/html', 'application/xhtml+xml' ) );
				} else {
					$url = '';
				}
			}
			if (!empty($url))
				$this->secure_url = $url;
		}
		return $this;
	}

	/**
	 * Get the Internet media type of the referenced resource
	 *
	 * @return string Internet media type or null if none set
	 */
	public function getType() {
		return $this->type;
	}
}

abstract class OpenGraphProtocolVisualMedia extends OpenGraphProtocolMedia {
	/**
	 * Height of the media object in pixels
	 *
	 * @var int
	 * @since 1.3
	 */
	protected $height;

	/**
	 * Width of the media object in pixels
	 *
	 * @var int
	 * @since 1.3
	 */
	protected $width;

    	/**
	 * @return int width in pixels
	 */
	public function getWidth() {
		return $this->width;
	}

	/**
	 * Set the object width
	 *
	 * @param int $width width in pixels
	 */
	public function setWidth( $width ) {
		if ( is_int($width) && $width >  0 )
			$this->width = $width;
		return $this;
	}

	/**
	 * @return int height in pixels
	 */
	public function getHeight() {
		return $this->height;
	}

	/**
	 * Set the height of the referenced object in pixels
	 * @var int height of the referenced object in pixels
	 */
	public function setHeight( $height ) {
		if ( is_int($height) && $height > 0 )
			$this->height = $height;
		return $this;
	}
}

/**
 * An image representing page content. Suitable for display alongside a summary of the webpage.
 */
class OpenGraphProtocolImage extends OpenGraphProtocolVisualMedia {
	/**
	 * Map a file extension to a registered Internet media type
	 *
	 * @link http://www.iana.org/assignments/media-types/image/index.html IANA image types
	 * @param string $extension file extension
	 * @return string Internet media type in the format image/* 
	 */
	public static function extension_to_media_type( $extension ) {
		if ( empty($extension) || ! is_string($extension) )
			return;
		if ( $extension === 'jpeg' || $extension === 'jpg' )
			return 'image/jpeg';
		else if ( $extension === 'png' )
			return 'image/png';
		else if ( $extension === 'gif' )
			return 'image/gif';
		else if ( $extension === 'svg' )
			return 'image/svg+sml';
		else if ( $extension === 'ico' )
			return 'image/vnd.microsoft.icon';
	}

	/**
	 * Set the Internet media type. Allow only image types.
	 *
	 * @param string $type Internet media type
	 */
	public function setType( $type ) {
		if ( substr_compare( $type, 'image/', 0, 6 ) === 0 )
			$this->type = $type;
		return $this;
	}
}

/**
 * A video that complements the webpage content
 */
class OpenGraphProtocolVideo extends OpenGraphProtocolVisualMedia {
	/**
	 * Map a file extension to a registered Internet media type
	 * Include Flash as a video type due to its popularity as a wrapper
	 *
	 * @link http://www.iana.org/assignments/media-types/video/index.html IANA video types
	 * @param string $extension file extension
	 * @return string Internet media type in the format video/* or Flash
	 */
	public static function extension_to_media_type( $extension ) {
		if ( empty($extension) || ! is_string($extension) )
			return;
		if ( $extension === 'swf' )
			return 'application/x-shockwave-flash';
		else if ( $extension === 'mp4' )
			return 'video/mp4';
		else if ( $extension === 'ogv' )
			return 'video/ogg';
		else if ( $extension === 'webm' )
			return 'video/webm';
	}

	/**
	 * Set the Internet media type. Allow only video types + Flash wrapper.
	 *
	 * @param string $type Internet media type
	 */
	public function setType( $type ) {
		if ( $type === 'application/x-shockwave-flash' || substr_compare( $type, 'video/', 0, 6 ) === 0 )
			$this->type = $type;
		return $this;
	}
}

/**
 * Audio file suitable for playback alongside the main linked content
 */
class OpenGraphProtocolAudio extends OpenGraphProtocolMedia {
	/**
	 * Map a file extension to a registered Internet media type
	 * Include Flash as a video type due to its popularity as a wrapper
	 *
	 * @link http://www.iana.org/assignments/media-types/audio/index.html IANA video types
	 * @param string $extension file extension
	 * @return string Internet media type in the format audio/* or Flash
	 */
	public static function extension_to_media_type( $extension ) {
		if ( empty($extension) || ! is_string($extension) )
			return;
		if ( $extension === 'swf' )
			return 'application/x-shockwave-flash';
		else if ( $extension === 'mp3' )
			return 'audio/mpeg';
		else if ( $extension === 'm4a' )
			return 'audio/mp4';
		else if ( $extension === 'ogg' || $extension === 'oga' )
			return 'audio/ogg';
	}

	/**
	 * Set the Internet media type. Allow only audio types + Flash wrapper.
	 *
	 * @param string $type Internet media type
	 */
	public function setType( $type ) {
		if ( $type === 'application/x-shockwave-flash' || substr_compare( $type, 'audio/', 0, 6 ) === 0 )
			$this->type = $type;
		return $this;
	}
}
?>