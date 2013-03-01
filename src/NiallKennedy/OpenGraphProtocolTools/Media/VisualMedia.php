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
 * Describe a media object that can be displayed on a region of the screen
 * Structured properties representations of Open Graph protocol media.
 *
 * @link http://ogp.me/#structured Open Graph protocol structured properties
 */
abstract class VisualMedia extends Media
{
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
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set the object width
     *
     * @param int $width width in pixels
     */
    public function setWidth($width)
    {
        if (is_int($width) && $width >  0) {
            $this->width = $width;
        }

        return $this;
    }

    /**
     * @return int height in pixels
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set the height of the referenced object in pixels
     * @var int height of the referenced object in pixels
     */
    public function setHeight($height)
    {
        if (is_int($height) && $height > 0) {
            $this->height = $height;
        }

        return $this;
    }
}
