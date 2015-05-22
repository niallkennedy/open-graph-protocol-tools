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
				if ( empty($property) || !is_string($property) )
					$s .= static::buildHTML( $content, $prefix );
				else
					$s .= static::buildHTML( $content, $prefix . ':' . $property );
			} elseif ( !empty($content) ) {
				$s .= '<meta ' . self::META_ATTR . '="' . $prefix;
				if ( is_string($property) && !empty($property) )
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
				'restaurant' => _('Restaurant')
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
	 * Facebook maps languages to a default territory and only accepts locales in this list. A few popular languages such as English and French support multiple territories.
	 * Map the Facebook list to avoid throwing errors in Facebook parsers that prevent further content indexing
	 *
	 * @link https://www.facebook.com/translations/FacebookLocales.xml Facebook locales
	 * @param bool $keys_only return only keys
	 * @return array associative array of locale code and locale name. locale code is in the format language_TERRITORY where language is an ISO 639-1 alpha-2 code and territory is an ISO 3166-1 alpha-2 code with special regions 'AR' and 'LA' for Arab region and Latin America respectively.
	 */
	public static function supported_locales( $keys_only=false ) {
		$locales = array(
			'af_ZA' => _('Afrikaans'),
			'ak_GH' => _('Akan'),
			'am_ET' => _('Amharic'),
			'ar_AR' => _('Arabic'),
			'as_IN' => _('Assamese'),
			'ay_BO' => _('Aymara'),
			'az_AZ' => _('Azerbaijani'),
			'be_BY' => _('Belarusian'),
			'bg_BG' => _('Bulgarian'),
			'bn_IN' => _('Bengali'),
			'br_FR' => _('Breton'),
			'bs_BA' => _('Bosnian'),
			'ca_ES' => _('Catalan'),
			'cb_IQ' => _('Sorani Kurdish'),
			'ck_US' => _('Cherokee'),
			'co_FR' => _('Corsican'),
			'cs_CZ' => _('Czech'),
			'cx_PH' => _('Cebuano'),
			'cy_GB' => _('Welsh'),
			'da_DK' => _('Danish'),
			'de_DE' => _('German'),
			'el_GR' => _('Greek'),
			'en_GB' => _('English (UK)'),
			'en_IN' => _('English (India)'),
			'en_US' => _('English (US)'),
			'eo_EO' => _('Esperanto'),
			'es_CO' => _('Spanish (Colombia)'),
			'es_ES' => _('Spanish (Spain)'),
			'es_LA' => _('Spanish'),
			'et_EE' => _('Estonian'),
			'eu_ES' => _('Basque'),
			'fa_IR' => _('Persian'),
			'ff_NG' => _('Fulah'),
			'fi_FI' => _('Finnish'),
			'fo_FO' => _('Faroese'),
			'fr_CA' => _('French (Canada)'),
			'fr_FR' => _('French (France)'),
			'fy_NL' => _('Frisian'),
			'ga_IE' => _('Irish'),
			'gl_ES' => _('Galician'),
			'gn_PY' => _('Guarani'),
			'gu_IN' => _('Gujarati'),
			'gx_GR' => _('Classical Greek'),
			'ha_NG' => _('Hausa'),
			'he_IL' => _('Hebrew'),
			'hi_IN' => _('Hindi'),
			'hr_HR' => _('Croatian'),
			'hu_HU' => _('Hungarian'),
			'hy_AM' => _('Armenian'),
			'id_ID' => _('Indonesian'),
			'ig_NG' => _('Igbo'),
			'is_IS' => _('Icelandic'),
			'it_IT' => _('Italian'),
			'ja_JP' => _('Japanese'),
			'ja_KS' => _('Japanese (Kansai)'),
			'jv_ID' => _('Javanese'),
			'ka_GE' => _('Georgian'),
			'kk_KZ' => _('Kazakh'),
			'km_KH' => _('Khmer'),
			'kn_IN' => _('Kannada'),
			'ko_KR' => _('Korean'),
			'ku_TR' => _('Kurdish (Kurmanji)'),
			'la_VA' => _('Latin'),
			'lg_UG' => _('Ganda'),
			'li_NL' => _('Limburgish'),
			'ln_CD' => _('Lingala'),
			'lo_LA' => _('Lao'),
			'lt_LT' => _('Lithuanian'),
			'lv_LV' => _('Latvian'),
			'mg_MG' => _('Malagasy'),
			'mk_MK' => _('Macedonian'),
			'ml_IN' => _('Malayalam'),
			'mn_MN' => _('Mongolian'),
			'mr_IN' => _('Marathi'),
			'ms_MY' => _('Malay'),
			'mt_MT' => _('Maltese'),
			'my_MM' => _('Burmese'),
			'nb_NO' => _('Norwegian (bokmal)'),
			'nd_ZW' => _('Ndebele'),
			'ne_NP' => _('Nepali'),
			'nl_BE' => _('Dutch (België)'),
			'nl_NL' => _('Dutch'),
			'nn_NO' => _('Norwegian (nynorsk)'),
			'ny_MW' => _('Chewa'),
			'or_IN' => _('Oriya'),
			'pa_IN' => _('Punjabi'),
			'pl_PL' => _('Polish'),
			'ps_AF' => _('Pashto'),
			'pt_BR' => _('Portuguese (Brazil)'),
			'pt_PT' => _('Portuguese (Portugal)'),
			'qu_PE' => _('Quechua'),
			'rm_CH' => _('Romansh'),
			'ro_RO' => _('Romanian'),
			'ru_RU' => _('Russian'),
			'rw_RW' => _('Kinyarwanda'),
			'sa_IN' => _('Sanskrit'),
			'sc_IT' => _('Sardinian'),
			'se_NO' => _('Northern Sámi'),
			'si_LK' => _('Sinhala'),
			'sk_SK' => _('Slovak'),
			'sl_SI' => _('Slovenian'),
			'sn_ZW' => _('Shona'),
			'so_SO' => _('Somali'),
			'sq_AL' => _('Albanian'),
			'sr_RS' => _('Serbian'),
			'sv_SE' => _('Swedish'),
			'sw_KE' => _('Swahili'),
			'sy_SY' => _('Syriac'),
			'sz_PL' => _('Silesian'),
			'ta_IN' => _('Tamil'),
			'te_IN' => _('Telugu'),
			'tg_TJ' => _('Tajik'),
			'th_TH' => _('Thai'),
			'tk_TM' => _('Turkmen'),
			'tl_PH' => _('Filipino'),
			'tr_TR' => _('Turkish'),
			'tt_RU' => _('Tatar'),
			'tz_MA' => _('Tamazight'),
			'uk_UA' => _('Ukrainian'),
			'ur_PK' => _('Urdu'),
			'uz_UZ' => _('Uzbek'),
			'vi_VN' => _('Vietnamese'),
			'wo_SN' => _('Wolof'),
			'xh_ZA' => _('Xhosa'),
			'yi_DE' => _('Yiddish'),
			'yo_NG' => _('Yoruba'),
			'zh_CN' => _('Simplified Chinese (China)'),
			'zh_HK' => _('Traditional Chinese (Hong Kong)'),
			'zh_TW' => _('Traditional Chinese (Taiwan)'),
			'zu_ZA' => _('Zulu'),
			'zz_TR' => _('Zazaki')
		);
		if ( $keys_only === true ) {
			return array_keys($locales);
		} else {
			return $locales;
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
		if ( is_string($locale) && in_array($locale, static::supported_locales(true)) )
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
		$image_url = $image->getURL();
		if ( empty($image_url) )
			return;
		$image->removeURL();
		$value = array( $image_url, array($image) );
		if ( ! isset( $this->image ) )
			$this->image = array( $value );
		else
			$this->image[] = $value;
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
		$audio_url = $audio->getURL();
		if ( empty($audio_url) )
			return;
		$audio->removeURL();
		$value = array( $audio_url, array($audio) );
		if ( ! isset($this->audio) )
			$this->audio = array($value);
		else
			$this->audio[] = $value;
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
		$video_url = $video->getURL();
		if ( empty($video_url) )
			return;
		$video->removeURL();
		$value = array( $video_url, array($video) );
		if ( ! isset( $this->video ) )
			$this->video = array( $value );
		else
			$this->video[] = $value;
		return $this;
	}
}

include_once dirname(__FILE__) . '/media.php';  // image, video, audio
include_once dirname(__FILE__) . '/objects.php'; // global objects: profile, article
?>