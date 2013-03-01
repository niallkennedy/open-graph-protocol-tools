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

/**
 * Open Graph protocol book
 *
 * @link http://ogp.me/ Open Graph protocol
 */
class Book extends Object
{
    /**
     * Property prefix
     * @var string
     */
    const PREFIX = 'book';

    /**
     * prefix namespace
     * @var string
     */
    const NS = 'http://ogp.me/ns/book#';

    /**
     * Book authors as an array of URIs.
     * The target URI is expected to have an Open Graph protocol type of 'profile'
     * @var array
     */
    protected $author;

    /**
     * International Standard Book Number. ISBN-10 and ISBN-13 accepted
     * @link http://en.wikipedia.org/wiki/International_Standard_Book_Number ISBN
     * @var string
     */
    protected $isbn;

    /**
     * The date the book was released, or planned release if in future.
     * Stored as an ISO 8601 date string normalized to UTC for consistency
     * @var string
     */
    protected $release_date;

    /**
     * Tag words describing book content and subjects
     * @var array
     */
    protected $tag;

    public function __construct()
    {
        $this->author = array();
        $this->tag    = array();
    }

    /**
     * Book author URIs
     * @return array author URIs
     */
    public function getAuthors()
    {
        return $this->author;
    }

    /**
     * Add an author URI.
     *
     * @param string $author_uri
     */
    public function addAuthor($author_uri)
    {
        if (static::isValidUrl($author_uri) && !in_array($author_uri, $this->author)) {
            $this->author[] = $author_uri;
        }

        return $this;
    }

    /**
     * International Standard Book Number
     *
     * @return string
     */
    public function getISBN()
    {
        return $this->isbn;
    }

    /**
     * Set an International Standard Book Number
     *
     * @param string $isbn
     */
    public function setISBN($isbn)
    {
        if (is_string($isbn)) {
            $isbn = trim(str_replace('-', '', $isbn));
            if (strlen($isbn) === 10 && is_numeric( substr($isbn, 0 , 9) )) { // published before 2007
                $verifysum = 0;
                $chars = str_split($isbn);
                for ($i=0; $i<9; $i++) {
                    $verifysum += ((int) $chars[$i]) * (10 - $i);
                }
                $check_digit = 11 - ($verifysum % 11);
                if ($check_digit == $chars[9] || ($chars[9] == 'X' && $check_digit == 10)) {
                    $this->isbn = $isbn;
                }
            } elseif (strlen($isbn) === 13 && is_numeric(substr($isbn, 0, 12))) {
                $verifysum = 0;
                $chars = str_split($isbn);
                for ($i=0; $i<12; $i++) {
                    $verifysum += ((int) $chars[$i]) * ((($i%2) === 0) ? 1 : 3);
                }
                $check_digit = 10 - ($verifysum % 10);
                if ($check_digit == $chars[12]) {
                    $this->isbn = $isbn;
                }
            }
        }

        return $this;
    }

    /**
     * Book release date
     *
     * @return string release date in ISO 8601 format
     */
    public function getReleaseDate()
    {
        return $this->release_date;
    }

    /**
     * Set the book release date
     *
     * @param DateTime|string $release_date release date as DateTime or as an ISO 8601 formatted string
     */
    public function setReleaseDate($release_date)
    {
        if ($release_date instanceof DateTime) {
            $this->release_date = static::datetimeToIso8601($release_date);
        } elseif (is_string($release_date) && strlen($release_date) >= 10) { // at least YYYY-MM-DD
            $this->release_date = $release_date;
        }

        return $this;
    }

    /**
     * Book subject tags
     *
     * @return array Topic tags
     */
    public function getTags()
    {
        return $this->tag;
    }

    /**
     * Add a book topic tag
     *
     * @param string $tag topic tag
     */
    public function addTag($tag)
    {
        if (is_string($tag) && !empty($tag) && !in_array($tag, $this->tag)) {
            $this->tag[] = $tag;
        }

        return $this;
    }
}
