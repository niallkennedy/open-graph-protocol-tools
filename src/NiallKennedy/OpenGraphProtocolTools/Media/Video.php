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

/**
 * A video that complements the webpage content.
 * Structured properties representations of Open Graph protocol media.
 *
 * @link http://ogp.me/ns#video Open Graph protocol audio structured properties
 */
class Video extends VisualMedia
{
    /**
     * Map a file extension to a registered Internet media type
     * Include Flash as a video type due to its popularity as a wrapper
     *
     * @link http://www.iana.org/assignments/media-types/video/index.html IANA video types
     * @param  string $extension file extension
     * @return string Internet media type in the format video/* or Flash
     */
    public static function extensionToMediaType($extension)
    {
        if (empty($extension) || ! is_string($extension)) {
            return;
        }
        if ($extension === 'swf') {
            return 'application/x-shockwave-flash';
        } elseif ($extension === 'mp4') {
            return 'video/mp4';
        } elseif ($extension === 'ogv') {
            return 'video/ogg';
        } elseif ($extension === 'webm') {
            return 'video/webm';
        }
    }

    /**
     * Set the Internet media type. Allow only video types + Flash wrapper.
     *
     * @param string $type Internet media type
     */
    public function setType($type)
    {
        if ($type === 'application/x-shockwave-flash' || substr_compare( $type, 'video/', 0, 6 ) === 0) {
            $this->type = $type;
        }

        return $this;
    }
}
