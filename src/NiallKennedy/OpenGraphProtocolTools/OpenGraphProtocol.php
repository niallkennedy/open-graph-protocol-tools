<?php
/**
 * Open Graph Protocol Tools
 *
 * @package open-graph-protocol-tools
 * @author Niall Kennedy <niall@niallkennedy.com>
 * @version 2.0
 * @copyright Public Domain
 */

namespace NiallKennedy\OpenGraphProtocolTools;

use NiallKennedy\OpenGraphProtocolTools\Media as OgptMedia;

/**
 * Open Graph Protocol data class. Define and validate OGP values.
 * Validate inputted text against Open Graph Protocol requirements by parameter.
 */
class OpenGraphProtocol
{
    /**
     * Version
     * @var string
     */
    const VERSION = '2.0';

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
     * An array of OgptMedia\Image objects
     *
     * @var array
     * @since 1.0
     */
    protected $image;

    /**
     * An array of OgptMedia\Audio objects
     *
     * @var array
     * @since 1.2
     */
    protected $audio;

    /**
     * An array of OgptMedia\Video objects
     *
     * @var array
     * @since 1.2
     */
    protected $video;

    private static function gettext($text, $domain = '')
    {
        return function_exists('gettext') ? gettext($text, $domain) : $text;
    }

    /**
     * Build Open Graph protocol HTML markup based on an array
     *
     * @param array  $og     associative array of OGP properties and values
     * @param string $prefix optional prefix to prepend to all properties
     */
    public static function buildHTML(array $og, $prefix = self::PREFIX)
    {
        if (empty($og)) {
            return;
        }
        $s = '';
        foreach ($og as $property => $content) {
            if (is_object($content) || is_array($content)) {
                if (is_object($content)) {
                    $content = $content->toArray();
                }
                if (empty($property) || !is_string($property)) {
                    $s .= static::buildHTML($content, $prefix);
                } else {
                    $s .= static::buildHTML($content, $prefix . ':' . $property);
                }
            } elseif (!empty($content)) {
                $s .= '<meta ' . self::META_ATTR . '="' . $prefix;
                if (is_string($property) && !empty($property)) {
                    $s .= ':' . htmlspecialchars( $property );
                }
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
    public static function supportedTypes($flatten = false)
    {
        $types = array(
            self::gettext('Activities') => array(
                'activity'       => self::gettext('Activity'),
                'sport'          => self::gettext('Sport')
            ),
            self::gettext('Businesses') => array(
                'company'        => self::gettext('Company'),
                'bar'            => self::gettext('Bar'),
                'cafe'           => self::gettext('Cafe'),
                'hotel'          => self::gettext('Hotel'),
                'restaurant'     => self::gettext('Restaurant')
            ),
            self::gettext('Groups') => array(
                'cause'          => self::gettext('Cause'),
                'sports_league'  => self::gettext('Sports league'),
                'sports_team'    => self::gettext('Sports team')
            ),
            self::gettext('Organizations') => array(
                'band'           => self::gettext('Band'),
                'government'     => self::gettext('Government'),
                'non_profit'     => self::gettext('Non-profit'),
                'school'         => self::gettext('School'),
                'university'     => self::gettext('University')
            ),
            self::gettext('People') => array(
                'actor'          => self::gettext('Actor or actress'),
                'athlete'        => self::gettext('Athlete'),
                'author'         => self::gettext('Author'),
                'director'       => self::gettext('Director'),
                'musician'       => self::gettext('Musician'),
                'politician'     => self::gettext('Politician'),
                'profile'        => self::gettext('Profile'),
                'public_figure'  => self::gettext('Public Figure')
            ),
            self::gettext('Places') => array(
                'city'           => self::gettext('City or locality'),
                'country'        => self::gettext('Country'),
                'landmark'       => self::gettext('Landmark'),
                'state_province' => self::gettext('State or province')
            ),
            self::gettext('Products and Entertainment') => array(
                'music.album'    => self::gettext('Music Album'),
                'book'           => self::gettext('Book'),
                'drink'          => self::gettext('Drink'),
                'video.episode'  => self::gettext('Video episode'),
                'food'           => self::gettext('Food'),
                'game'           => self::gettext('Game'),
                'video.movie'    => self::gettext('Movie'),
                'music.playlist' => self::gettext('Music playlist'),
                'product'        => self::gettext('Product'),
                'music.radio_station'
                                 => self::gettext('Radio station'),
                'music.song'     => self::gettext('Song'),
                'video.tv_show'  => self::gettext('Television show'),
                'video.other'    => self::gettext('Video')
            ),
            self::gettext('Websites') => array(
                'article'        => self::gettext('Article'),
                'blog'           => self::gettext('Blog'),
                'website'        => self::gettext('Website')
            )
        );
        if ($flatten === true) {
            $types_values = array();
            foreach ($types as $category=>$values) {
                $types_values = array_merge($types_values, array_keys($values));
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
     * @param  bool  $keys_only return only keys
     * @return array associative array of locale code and locale name. locale code is in the format language_TERRITORY where language is an ISO 639-1 alpha-2 code and territory is an ISO 3166-1 alpha-2 code with special regions 'AR' and 'LA' for Arab region and Latin America respectively.
     */
    public static function supportedLocales($keys_only = false)
    {
        $locales = array(
            'af_ZA' => self::gettext('Afrikaans'),
            'ar_AR' => self::gettext('Arabic'),
            'az_AZ' => self::gettext('Azeri'),
            'be_BY' => self::gettext('Belarusian'),
            'bg_BG' => self::gettext('Bulgarian'),
            'bn_IN' => self::gettext('Bengali'),
            'bs_BA' => self::gettext('Bosnian'),
            'ca_ES' => self::gettext('Catalan'),
            'cs_CZ' => self::gettext('Czech'),
            'cy_GB' => self::gettext('Welsh'),
            'da_DK' => self::gettext('Danish'),
            'de_DE' => self::gettext('German'),
            'el_GR' => self::gettext('Greek'),
            'en_GB' => self::gettext('English (UK)'),
            'en_US' => self::gettext('English (US)'),
            'eo_EO' => self::gettext('Esperanto'),
            'es_ES' => self::gettext('Spanish (Spain)'),
            'es_LA' => self::gettext('Spanish (Latin America)'),
            'et_EE' => self::gettext('Estonian'),
            'eu_ES' => self::gettext('Basque'),
            'fa_IR' => self::gettext('Persian'),
            'fi_FI' => self::gettext('Finnish'),
            'fo_FO' => self::gettext('Faroese'),
            'fr_CA' => self::gettext('French (Canada)'),
            'fr_FR' => self::gettext('French (France)'),
            'fy_NL' => self::gettext('Frisian'),
            'ga_IE' => self::gettext('Irish'),
            'gl_ES' => self::gettext('Galician'),
            'he_IL' => self::gettext('Hebrew'),
            'hi_IN' => self::gettext('Hindi'),
            'hr_HR' => self::gettext('Croatian'),
            'hu_HU' => self::gettext('Hungarian'),
            'hy_AM' => self::gettext('Armenian'),
            'id_ID' => self::gettext('Indonesian'),
            'is_IS' => self::gettext('Icelandic'),
            'it_IT' => self::gettext('Italian'),
            'ja_JP' => self::gettext('Japanese'),
            'ka_GE' => self::gettext('Georgian'),
            'ko_KR' => self::gettext('Korean'),
            'ku_TR' => self::gettext('Kurdish'),
            'la_VA' => self::gettext('Latin'),
            'lt_LT' => self::gettext('Lithuanian'),
            'lv_LV' => self::gettext('Latvian'),
            'mk_MK' => self::gettext('Macedonian'),
            'ml_IN' => self::gettext('Malayalam'),
            'ms_MY' => self::gettext('Malay'),
            'nb_NO' => self::gettext('Norwegian (bokmal)'),
            'ne_NP' => self::gettext('Nepali'),
            'nl_NL' => self::gettext('Dutch'),
            'nn_NO' => self::gettext('Norwegian (nynorsk)'),
            'pa_IN' => self::gettext('Punjabi'),
            'pl_PL' => self::gettext('Polish'),
            'ps_AF' => self::gettext('Pashto'),
            'pt_PT' => self::gettext('Portuguese (Brazil)'),
            'ro_RO' => self::gettext('Romanian'),
            'ru_RU' => self::gettext('Russian'),
            'sk_SK' => self::gettext('Slovak'),
            'sl_SI' => self::gettext('Slovenian'),
            'sq_AL' => self::gettext('Albanian'),
            'sr_RS' => self::gettext('Serbian'),
            'sv_SE' => self::gettext('Swedish'),
            'sw_KE' => self::gettext('Swahili'),
            'ta_IN' => self::gettext('Tamil'),
            'te_IN' => self::gettext('Telugu'),
            'th_TH' => self::gettext('Thai'),
            'tl_PH' => self::gettext('Filipino'),
            'tr_TR' => self::gettext('Turkish'),
            'uk_UA' => self::gettext('Ukrainian'),
            'vi_VN' => self::gettext('Vietnamese'),
            'zh_CN' => self::gettext('Simplified Chinese (China)'),
            'zh_HK' => self::gettext('Traditional Chinese (Hong Kong)'),
            'zh_TW' => self::gettext('Traditional Chinese (Taiwan)')
        );
        if ($keys_only === true) {
            return array_keys($locales);
        } else {
            return $locales;
        }
    }

    /**
     * Cleans a URL string, then checks to see if a given URL is addressable, returns a 200 OK response, and matches the accepted Internet media types (if provided).
     *
     * @param  string $url            Publicly addressable URL
     * @param  array  $accepted_mimes Given URL correspond to an accepted Internet media (MIME) type.
     * @return string cleaned URL string, or empty string on failure.
     */
    public static function isValidUrl($url, array $accepted_mimes = array())
    {
        if (!is_string($url) || empty($url)) {
            return '';
        }

        /*
         * Validate URI string by letting PHP break up the string and put it back together again
         * Excludes username:password and port number URI parts
         */
        $url_parts = parse_url($url);
        $url = '';
        if (isset($url_parts['scheme']) && in_array($url_parts['scheme'], array('http', 'https'), true)) {
            $url = "{$url_parts['scheme']}://{$url_parts['host']}{$url_parts['path']}";
            if (empty($url_parts['path'])) {
                $url .= '/';
            }
            if (!empty($url_parts['query'])) {
                $url .= '?' . $url_parts['query'];
            }
            if (!empty($url_parts['fragment'])) {
                $url .= '#' . $url_parts['fragment'];
            }
        }

        if (!empty($url)) {
            // test if URL exists
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
            curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD
            curl_setopt($ch, CURLOPT_USERAGENT, 'Open Graph protocol validator ' . self::VERSION . ' (+http://ogp.me/)');
            if (!empty($accepted_mimes)) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: ' . implode(',', $accepted_mimes )));
            }
            $response = curl_exec($ch);
            if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
                if (!empty($accepted_mimes)) {
                    $content_type = explode(';', curl_getinfo( $ch, CURLINFO_CONTENT_TYPE));
                    if (empty($content_type) || !in_array($content_type[0], $accepted_mimes)) {
                        return '';
                    }
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
    public function toHTML()
    {
        return rtrim(static::buildHTML(get_object_vars($this)), PHP_EOL);
    }

    /**
     * @return String the type slug
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     *
     * @param String type slug
     */
    public function setType($type)
    {
        if (is_string($type) && in_array($type, self::supportedTypes(true), true)) {
            $this->type = $type;
        }

        return $this;
    }

    /**
     * @return String document title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param String $title document title
     */
    public function setTitle($title)
    {
        if (is_string($title)) {
            $title = trim($title);
            if (strlen($title) > 128) {
                $this->title = substr($title, 0, 128);
            } else {
                $this->title = $title;
            }
        }

        return $this;
    }

    /**
     * @return String Site name
     */
    public function getSiteName()
    {
        return $this->site_name;
    }

    /**
     * @param String $site_name Site name
     */
    public function setSiteName($site_name)
    {
        if ( is_string($site_name) && !empty($site_name) ) {
            $site_name = trim($site_name);
            if (strlen($site_name) > 128)
                $this->site_name = substr($site_name, 0, 128);
            else
                $this->site_name = $site_name;
        }

        return $this;
    }

    /**
     * @return String Description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param String $description Document description
     */
    public function setDescription($description)
    {
        if (is_string($description) && !empty($description)) {
            $description = trim($description);
            if (strlen($description) > 255) {
                $this->description = substr($description, 0, 255);
            } else {
                $this->description = $description;
            }
        }

        return $this;
    }

    /**
     * @return String URL
     */
    public function getURL()
    {
        return $this->url;
    }

    /**
     * @param String $url Canonical URL
     */
    public function setURL($url)
    {
        if (is_string($url) && !empty($url) ) {
            $url = trim($url);
            if (self::VERIFY_URLS) {
                $url = self::isValidUrl($url, array( 'text/html', 'application/xhtml+xml' ));
            }
            if (!empty($url)) {
                $this->url = $url;
            }
        }

        return $this;
    }

    /**
     * @return string the determiner
     */
    public function getDeterminer()
    {
        return $this->determiner;
    }

    public function setDeterminer($determiner)
    {
        if (in_array($determiner, array('a','an','auto','the'), true)) {
            $this->determiner = $determiner;
        }

        return $this;
    }

    /**
     * @return string language_TERRITORY
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @var string $locale locale in the format language_TERRITORY
     */
    public function setLocale($locale)
    {
        if (is_string($locale) && in_array($locale, static::supportedLocales(true))) {
            $this->locale = $locale;
        }

        return $this;
    }

    /**
     * @return array OgptMedia\Image array
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Add an image.
     * The first image added is given priority by the Open Graph Protocol spec. Implementors may choose a different image based on size requirements or preferences.
     *
     * @param OgptMedia\Image $image image object to add
     */
    public function addImage(OgptMedia\Image $image)
    {
        $image_url = $image->getURL();
        if (empty($image_url)) {
            return;
        }
        $image->removeURL();
        $value = array($image_url, array($image));
        if (!isset($this->image)) {
            $this->image = array($value);
        } else {
            $this->image[] = $value;
        }

        return $this;
    }

    /**
     * @return array OgptMedia\Audio objects
     */
    public function getAudio()
    {
        return $this->audio;
    }

    /**
     * Add an audio reference
     * The first audio is given priority by the Open Graph protocol spec.
     *
     * @param OgptMedia\Audio $audio audio object to add
     */
    public function addAudio(OgptMedia\Audio $audio)
    {
        $audio_url = $audio->getURL();
        if (empty($audio_url)) {
            return;
        }
        $audio->removeURL();
        $value = array($audio_url, array($audio));
        if (!isset($this->audio)) {
            $this->audio = array($value);
        } else {
            $this->audio[] = $value;
        }

        return $this;
    }

    /**
     * @return array OgptMedia\Video objects
     */
    public function getVideo()
    {
        return $this->video;
    }

    /**
     * Add a video reference
     * The first video is given priority by the Open Graph protocol spec. Implementors may choose a different video based on size requirements or preferences.
     *
     * @param OgptMedia\Video $video video object to add
     */
    public function addVideo(OgptMedia\Video $video)
    {
        $video_url = $video->getURL();
        if (empty($video_url)) {
            return;
        }
        $video->removeURL();
        $value = array($video_url, array($video));
        if (!isset($this->video)) {
            $this->video = array($value);
        } else {
            $this->video[] = $value;
        }

        return $this;
    }
}
