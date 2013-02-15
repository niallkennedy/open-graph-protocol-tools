<?php
/**
 * Open Graph Protocol Tools
 *
 * @package open-graph-protocol-tools
 * @author Niall Kennedy <niall@niallkennedy.com>
 * @version 2.0
 * @copyright Public Domain
 */

namespace NiallKennedy\OpenGraphProtocolTools\Media;

use NiallKennedy\OpenGraphProtocolTools\OpenGraphProtocol;

/**
 * Describe a media object
 * Structured properties representations of Open Graph protocol media.
 *
 * @link http://ogp.me/#structured Open Graph protocol structured properties
 */
abstract class Media
{
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
    public function toString()
    {
        return $this->url;
    }

    public function toArray()
    {
        return get_object_vars($this);
    }

    /**
     * @return string URL string or null if not set
     */
    public function getURL()
    {
        return $this->url;
    }

    /**
     * Set the media URL
     *
     * @param string $url resource location
     */
    public function setURL($url)
    {
        if (is_string($url) && !empty($url)) {
            $url = trim($url);
            if (OpenGraphProtocol::VERIFY_URLS) {
                $url = OpenGraphProtocol::isValidUrl($url, array('text/html', 'application/xhtml+xml'));
            }
            if (!empty($url)) {
                $this->url = $url;
            }
        }

        return $this;
    }

    /**
     * Remove the URL property.
     * Sets up the maximum compatibility between image and image:url indexers
     */
    public function removeURL()
    {
        if (!empty($this->url)) {
            unset($this->url);
        }
    }

    /**
     * @return string secure URL string or null if not set
     */
    public function getSecureURL()
    {
        return $this->url;
    }

    /**
     * Set the secure URL for display in a HTTPS page
     *
     * @param string $url resource location
     */
    public function setSecureURL($url)
    {
        if (is_string($url) && !empty($url)) {
            $url = trim($url);
            if (OpenGraphProtocol::VERIFY_URLS) {
                if ( parse_url($url,PHP_URL_SCHEME) === 'https' ) {
                    $url = OpenGraphProtocol::isValidUrl($url, array('text/html', 'application/xhtml+xml'));
                } else {
                    $url = '';
                }
            }
            if (!empty($url)) {
                $this->secure_url = $url;
            }
        }

        return $this;
    }

    /**
     * Get the Internet media type of the referenced resource
     *
     * @return string Internet media type or null if none set
     */
    public function getType()
    {
        return $this->type;
    }
}
