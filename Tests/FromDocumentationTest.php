<?php
/**
 * Open Graph Protocol Tools
 *
 * @package open-graph-protocol-tools
 * @author Niall Kennedy <niall@niallkennedy.com>
 * @version 2.0
 * @copyright Public Domain
 */

namespace NiallKennedy\OpenGraphProtocolTools\Tests;

use DateTime;
use DateTimeZone;
use PHPUnit_Framework_TestCase;

use NiallKennedy\OpenGraphProtocolTools\Media as OgptMedia;
use NiallKennedy\OpenGraphProtocolTools\Objects\Article;
use NiallKennedy\OpenGraphProtocolTools\OpenGraphProtocol;

/**
 * Test class for OGPT behavior taken directly from documentation
 *
 * @author Loren Osborn <loren.osborn@hautelook.com>
 */
class FromDocumentationTest extends PHPUnit_Framework_TestCase
{
    public function createImage()
    {
        /* From docs */
        $image = new OgptMedia\Image();
        $image->setURL('http://example.com/image.jpg');
        $image->setSecureURL('https://example.com/image.jpg');
        $image->setType('image/jpeg');
        $image->setWidth(400);
        $image->setHeight(300);

        return $image;
    }

    public function testCreateImage()
    {
        $image = $this->createImage();
        $this->assertEquals(
            array(
                'url'        => 'http://example.com/image.jpg',
                'secure_url' => 'https://example.com/image.jpg',
                'type'       => 'image/jpeg',
                'width'      => 400,
                'height'     => 300
            ),
            $image->toArray()
        );
    }

    public function createVideo()
    {
        /* From docs */
        $video = new OgptMedia\Video();
        $video->setURL('http://example.com/video.swf' );
        $video->setSecureURL('https://example.com/video.swf' );
        $video->setType(OgptMedia\Video::extensionToMediaType(pathinfo(parse_url($video->getURL(), PHP_URL_PATH), PATHINFO_EXTENSION)));
        $video->setWidth(500);
        $video->setHeight(400);

        return $video;
    }

    public function testCreateVideo()
    {
        $video = $this->createVideo();
        $this->assertEquals(
            array(
                'url'        => 'http://example.com/video.swf',
                'secure_url' => 'https://example.com/video.swf',
                'type'       => 'application/x-shockwave-flash',
                'width'      => 500,
                'height'     => 400
            ),
            $video->toArray()
        );
    }

    public function createAudio()
    {
        /* From docs */
        $audio = new OgptMedia\Audio();
        $audio->setURL('http://example.com/audio.mp3');
        $audio->setSecureURL('https://example.com/audio.mp3');
        $audio->setType('audio/mpeg');

        return $audio;
    }

    public function testCreateAudio()
    {
        $audio = $this->createAudio();
        $this->assertEquals(
            array(
                'url'        => 'http://example.com/audio.mp3',
                'secure_url' => 'https://example.com/audio.mp3',
                'type'       => 'audio/mpeg'
            ),
            $audio->toArray()
        );
    }

    public function createOpenGraphProtocol()
    {
        $image = $this->createImage();
        $audio = $this->createAudio();
        $video = $this->createVideo();
        /* From docs */
        $ogp = new OpenGraphProtocol();
        $ogp->setLocale('en_US');
        $ogp->setSiteName('Happy place');
        $ogp->setTitle('Hello world');
        $ogp->setDescription('We make the world happy.');
        $ogp->setType('website');
        $ogp->setURL('http://example.com/');
        $ogp->setDeterminer('the');
        $ogp->addImage($image);
        $ogp->addAudio($audio);
        $ogp->addVideo($video);

        return $ogp;
    }

    public function testOpenGraphProtocol()
    {
        $ogp = $this->createOpenGraphProtocol();
        $expectedResult =
            '<meta property="og:type" content="website">' . "\n" .
            '<meta property="og:title" content="Hello world">' . "\n" .
            '<meta property="og:site_name" content="Happy place">' . "\n" .
            '<meta property="og:description" content="We make the world happy.">' . "\n" .
            '<meta property="og:url" content="http://example.com/">' . "\n" .
            '<meta property="og:determiner" content="the">' . "\n" .
            '<meta property="og:locale" content="en_US">' . "\n" .
            '<meta property="og:image" content="http://example.com/image.jpg">' . "\n" .
            '<meta property="og:image:height" content="300">' . "\n" .
            '<meta property="og:image:width" content="400">' . "\n" .
            '<meta property="og:image:secure_url" content="https://example.com/image.jpg">' . "\n" .
            '<meta property="og:image:type" content="image/jpeg">' . "\n" .
            '<meta property="og:audio" content="http://example.com/audio.mp3">' . "\n" .
            '<meta property="og:audio:secure_url" content="https://example.com/audio.mp3">' . "\n" .
            '<meta property="og:audio:type" content="audio/mpeg">' . "\n" .
            '<meta property="og:video" content="http://example.com/video.swf">' . "\n" .
            '<meta property="og:video:height" content="400">' . "\n" .
            '<meta property="og:video:width" content="500">' . "\n" .
            '<meta property="og:video:secure_url" content="https://example.com/video.swf">' . "\n" .
            '<meta property="og:video:type" content="application/x-shockwave-flash">';
        $this->assertEquals($expectedResult, $ogp->toHTML());
    }

    public function createGlobalArticle()
    {
        /* From docs */
        $article = new Article();
        $article->setPublishedTime( '2011-11-03T01:23:45Z' );
        $article->setModifiedTime( new DateTime( '2013-02-15T00:39:06+00:00', new DateTimeZone( 'America/Los_Angeles' ) ) );
        $article->setExpirationTime( '2011-12-31T23:59:59+00:00' );
        $article->setSection( 'Front page' );
        $article->addTag( 'weather' );
        $article->addTag( 'football' );
        $article->addAuthor( 'http://example.com/author.html' );

        return $article;
    }

    public function testGlobalArticle()
    {
        $article = $this->createGlobalArticle();
        $expectedResult =
            '<meta property="article:published_time" content="2011-11-03T01:23:45Z">' . "\n" .
            '<meta property="article:modified_time" content="2013-02-15T00:39:06+00:00">' . "\n" .
            '<meta property="article:expiration_time" content="2011-12-31T23:59:59+00:00">' . "\n" .
            '<meta property="article:author" content="http://example.com/author.html">' . "\n" .
            '<meta property="article:section" content="Front page">' . "\n" .
            '<meta property="article:tag" content="weather">' . "\n" .
            '<meta property="article:tag" content="football">';
        $this->assertEquals($expectedResult, $article->toHTML());
    }

    public function testCombined()
    {
        $ogp = $this->createOpenGraphProtocol();
        $article = new Article();
        /* From docs */
        $ogp_objects = array( $ogp, $article );
        $prefix = '';
        $meta = '';
        foreach ($ogp_objects as $ogp_object) {
            $prefix .= $ogp_object::PREFIX . ': ' . $ogp_object::NS . ' ';
            $meta .= $ogp_object->toHTML() . PHP_EOL;
        }
        $expectedPrefix = 'og: http://ogp.me/ns# article: http://ogp.me/ns/article# ';
        $expectedMeta =
            '<meta property="og:type" content="website">' . "\n" .
            '<meta property="og:title" content="Hello world">' . "\n" .
            '<meta property="og:site_name" content="Happy place">' . "\n" .
            '<meta property="og:description" content="We make the world happy.">' . "\n" .
            '<meta property="og:url" content="http://example.com/">' . "\n" .
            '<meta property="og:determiner" content="the">' . "\n" .
            '<meta property="og:locale" content="en_US">' . "\n" .
            '<meta property="og:image" content="http://example.com/image.jpg">' . "\n" .
            '<meta property="og:image:height" content="300">' . "\n" .
            '<meta property="og:image:width" content="400">' . "\n" .
            '<meta property="og:image:secure_url" content="https://example.com/image.jpg">' . "\n" .
            '<meta property="og:image:type" content="image/jpeg">' . "\n" .
            '<meta property="og:audio" content="http://example.com/audio.mp3">' . "\n" .
            '<meta property="og:audio:secure_url" content="https://example.com/audio.mp3">' . "\n" .
            '<meta property="og:audio:type" content="audio/mpeg">' . "\n" .
            '<meta property="og:video" content="http://example.com/video.swf">' . "\n" .
            '<meta property="og:video:height" content="400">' . "\n" .
            '<meta property="og:video:width" content="500">' . "\n" .
            '<meta property="og:video:secure_url" content="https://example.com/video.swf">' . "\n" .
            '<meta property="og:video:type" content="application/x-shockwave-flash">' . "\n" .
            "\n";
        $this->assertEquals($expectedPrefix, $prefix);
        $this->assertEquals($expectedMeta, $meta);
    }
}
