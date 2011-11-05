<?php
/**
 * Open Graph protocol global types
 *
 * @link http://ogp.me/#types Open Graph protocol global types
 * @package open-graph-protocol-tools
 * @author Niall Kennedy <niall@niallkennedy.com>
 * @version 1.3
 * @copyright Public Domain
 */
if ( !class_exists('OpenGraphProtocol') )
	require_once dirname(__FILE__) . '/open-graph-protocol.php';

abstract class OpenGraphProtocolObject {
	const PREFIX ='';
	const NS='';

	/**
	 * Output the object as HTML <meta> elements
	 * @return string HTML meta element string
	 */
	public function toHTML() {
		return rtrim( OpenGraphProtocol::buildHTML( get_object_vars($this), static::PREFIX ), PHP_EOL );
	}
}

class OpenGraphProtocolArticle extends OpenGraphProtocolObject {
	/**
	 * Property prefix
	 * @var string
	 */
	const PREFIX = 'article';

	/**
	 * prefix namespace
	 * @var string
	 */
	const NS = 'http://ogp.me/ns/article#';

	/**
	 * When the article was first published.
	 * ISO 8601 formatted string.
	 * @var string
	 */
	protected $published_time;

	/**
	 * When the article was last changed
	 * ISO 8601 formatted string.
	 * @var string
	 */
	protected $modified_time;

	/**
	 * When the article is considered out-of-date
	 * ISO 8601 formatted string.
	 * @var string
	 */
	protected $expiration_time;

	/**
	 * Writers of the article.
	 * Array of author URIs
	 * @var array
	 */
	protected $author;

	/**
	 * High-level section or category
	 * @var string
	 */
	protected $section;

	/**
	 * Content tag
	 * Array of tag strings
	 * @var array
	 */
	protected $tag;

	/**
	 * Initialize arrays
	 */
	public function __construct() {
		$this->author = array();
		$this->tag = array();
	}

	/**
	 * Convert a DateTime object to GMT and format as an ISO 8601 string.
	 * @param DateTime $date date to convert
	 * @return string ISO 8601 formatted datetime string
	 */
	public static function datetime_to_iso_8601( DateTime $date ) {
		$date->setTimezone(new DateTimeZone('GMT'));
		return $date->format('c');
	}

	/**
	 * When the article was first published
	 * @return string ISO 8601 formatted publication date and optional time
	 */
	public function getPublishedTime() {
		return $this->published_time;
	}

	/**
	 * Set when the article was first published
	 * @param DateTime|string $pubdate ISO 8601 formatted datetime string or DateTime object for conversion
	 */
	public function setPublishedTime( $pubdate ) {
		if ( $pubdate instanceof DateTime )
			$this->published_time = static::datetime_to_iso_8601($pubdate);
		else if ( is_string($pubdate) && strlen($pubdate) >= 10 ) // at least YYYY-MM-DD
			$this->published_time = $pubdate;
		return $this;
	}

	/**
	 * When article was last changed
	 * @return string ISO 8601 formatted modified date and optional time
	 */
	public function getModifiedTime() {
		return $this->modified_time;
	}

	/**
	 * Set when the article was last changed
	 * @param DateTime|string $updated ISO 8601 formatted datetime string or DateTime object for conversion
	 */
	public function setModifiedTime( $updated ) {
		if ( $updated instanceof DateTime )
			$this->modified_time = static::datetime_to_iso_8601($updated);
		else if ( is_string($updated) && strlen($updated) >= 10 ) // at least YYYY-MM-DD
			$this->modified_time = $updated;
		return $this;
	}

	/**
	 * Time the article content expires
	 * @return string ISO 8601 formatted expiration date and optional time
	 */
	public function getExpirationTime() {
		return $this->expiration_time;
	}

	/**
	 * Set when the article content expires
	 * @param DateTime|string $expires ISO formatted datetime string or DateTime object for conversion
	 */
	public function setExpirationTime( $expires ) {
		if ( $expires instanceof DateTime )
			$this->expiration_time = static::datetime_to_iso_8601($expires);
		else if ( is_string($expires) && strlen($expires) >= 10 )
			$this->expiration_time = $expires;
		return $this;
	}

	/**
	 * Article author URIs
	 * @return array Article author URIs
	 */
	public function getAuthors() {
		return $this->author;
	}

	/**
	 * Add an author URI
	 * @param string $author_uri Author URI
	 */
	public function addAuthor( $author_uri ) {
		if ( is_string($author_uri) && !in_array($author_uri, $this->author))
			$this->author[] = $author_uri;
		return $this;
	}

	/**
	 * High-level section name
	 */
	public function getSection() {
		return $this->section();
	}

	/**
	 * Set the top-level content section
	 * @param string $section
	 */
	public function setSection( $section ) {
		if ( is_string($section) && !empty($section) )
			$this->section = $section;
		return $this;
	}

	/**
	 * Content tags
	 * @return array content tags
	 */
	public function getTags() {
		return $this->tag;
	}

	/**
	 * Add a content tag
	 * @param string $tag content tag
	 */
	public function addTag( $tag ) {
		if ( is_string($tag) && !empty($tag) )
			$this->tag[] = $tag;
		return $this;
	}
}

class OpenGraphProtocolProfile extends OpenGraphProtocolObject {
	/**
	 * Property prefix
	 * @var string
	 */
	const PREFIX = 'profile';

	/**
	 * prefix namespace
	 * @var string
	 */
	const NS = 'http://ogp.me/ns/profile#';

	/**
	 * A person's given name
	 * @var string
	 */
	protected $first_name;

	/**
	 * A person's last name
	 * @var string
	 */
	protected $last_name;

	/**
	 * The profile's unique username
	 * @var string
	 */
	protected $username;

	/**
	 * Gender: male or female
	 */
	protected $gender;

	/**
	 * Get the person's given name
	 * @return string given name
	 */
	public function getFirstName() {
		return $this->first_name;
	}

	/**
	 * Set the person's given name
	 * @param string $first_name given name
	 */
	public function setFirstName( $first_name ) {
		if ( is_string($first_name) && !empty($first_name) )
			$this->first_name = $first_name;
		return $this;
	}

	/**
	 * The person's family name
	 * @return string famil name
	 */
	public function getLastName() {
		return $this->last_name;
	}

	/**
	 * Set the person's family name
	 * @param string $last_name family name
	 */
	public function setLastName( $last_name ) {
		if ( is_string($last_name) && !empty($last_name) )
			$this->last_name = $last_name;
		return $this;
	}

	/**
	 * Person's username on your site
	 * @return string account username
	 */
	public function getUsername() {
		return $this->username;
	}

	/**
	 * Set the account username
	 * @param string $username username
	 */
	public function setUsername( $username ) {
		if ( is_string($username) && !empty($username) )
			$this->username = $username;
		return $this;
	}

	/**
	 * The person's gender. male|female
	 * @return string male|female
	 */
	public function getGender() {
		return $this->gender;
	}

	/**
	 * Set the person's gender
	 * @param string $gender male|female
	 */
	public function setGender( $gender ) {
		if ( is_string($gender) && ( $gender === 'male' || $gender === 'female' ) )
			$this->gender = $gender;
		return $this;
	}
}
?>