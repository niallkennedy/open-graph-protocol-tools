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
 * An image representing page content. Suitable for display alongside a summary of the webpage.
 * Structured properties representations of Open Graph protocol media.
 *
 * @link http://ogp.me/ns#image Open Graph protocol image structured properties
 */
class Image extends VisualMedia
{
    /**
     * Map a file extension to a registered Internet media type
     *
     * @link http://www.iana.org/assignments/media-types/image/index.html IANA image types
     * @param  string $extension file extension
     * @return string Internet media type in the format image/*
     */
    public static function extensionToMediaType( $extension )
    {
        if (empty($extension) || ! is_string($extension)) {
            return;
        }
        if ($extension === 'jpeg' || $extension === 'jpg') {
            return 'image/jpeg';
        } elseif ($extension === 'png') {
            return 'image/png';
        } elseif ($extension === 'gif') {
            return 'image/gif';
        } elseif ($extension === 'svg') {
            return 'image/svg+sml';
        } elseif ($extension === 'ico') {
            return 'image/vnd.microsoft.icon';
        }
    }

    /**
     * Set the Internet media type. Allow only image types.
     *
     * @param string $type Internet media type
     */
    public function setType( $type )
    {
        if (substr_compare( $type, 'image/', 0, 6 ) === 0) {
            $this->type = $type;
        }

        return $this;
    }
}
