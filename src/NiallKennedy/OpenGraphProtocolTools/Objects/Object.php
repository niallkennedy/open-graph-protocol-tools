<?php
/**
 * Open Graph Protocol Tools
 *
 * @package open-graph-protocol-tools
 * @author Niall Kennedy <niall@niallkennedy.com>
 * @version 2.0
 * @copyright Public Domain
 */

namespace NiallKennedy\OpenGraphProtocolTools\Objects;

use DateTime;
use DateTimeZone;
use NiallKennedy\OpenGraphProtocolTools\OpenGraphProtocol;

/**
 * Open Graph protocol global types
 *
 * @link http://ogp.me/#types Open Graph protocol global types
 */
abstract class Object
{
    /**
     * Property prefix
     * @var string
     */
    const PREFIX = '';

    /**
     * prefix namespace
     * @var string
     */
    const NS = '';

    /**
     * Output the object as HTML <meta> elements
     * @return string HTML meta element string
     */
    public function toHTML()
    {
        return rtrim(OpenGraphProtocol::buildHTML(get_object_vars($this), static::PREFIX), PHP_EOL);
    }

    /**
     * Convert a DateTime object to GMT and format as an ISO 8601 string.
     * @param  DateTime $date date to convert
     * @return string   ISO 8601 formatted datetime string
     */
    public static function datetimeToIso8601(DateTime $date)
    {
        $date->setTimezone(new DateTimeZone('GMT'));

        return $date->format('c');
    }

    /**
     * Test a URL for validity.
     *
     * @uses OpenGraphProtocol::isValidUrl if OpenGraphProtocol::VERIFY_URLS is true
     * @param  string $url absolute URL addressable from the public web
     * @return bool   true if URL is non-empty string. if VERIFY_URLS set then URL must also properly respond to a HTTP request.
     */
    public static function isValidUrl($url)
    {
        if (is_string($url) && !empty($url)) {
            if (OpenGraphProtocol::VERIFY_URLS) {
                $url = OpenGraphProtocol::isValidUrl($url, array('text/html', 'application/xhtml+xml'));
                if (!empty($url)) {
                    return true;
                }
            } else {
                return true;
            }
        }

        return false;
    }
}
