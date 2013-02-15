<?php
/**
 * Structured properties representations of Open Graph protocol media: image, video, audio
 *
 * @link http://ogp.me/#structured Open Graph protocol structured properties
 * @package open-graph-protocol-tools
 * @author Niall Kennedy <niall@niallkennedy.com>
 * @version 1.3
 * @copyright Public Domain
 */

 if ( !class_exists('OpenGraphProtocol') )
 	require_once(dirname(__FILE__) . '/open-graph-protocol.php');
 
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

	/**
	 * Treat a string reference just like the base property
	 */
	public function toString() {
		return $this->url;
	}

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
	 * Remove the URL property.
	 * Sets up the maximum compatibility between image and image:url indexers
	 */
	public function removeURL() {
		if ( !empty($this->url) )
			unset($this->url);
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