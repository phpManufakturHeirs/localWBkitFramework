<?php

/**
 * TemplateTools
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/TemplateTools
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\TemplateTools\Control\cmsFunctions;

use Silex\Application;
use Symfony\Component\Validator\Constraints as Assert;
use phpManufaktur\flexContent\Data\Content\Content as flexContentData;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Finder\Finder;

class PageImage
{
    protected $app = null;
    protected static $options = array(
        'fallback_image' => array(
           'active' => true,
           'url' => null
        ),
        'minimum_size' => array(
           'width' => 200,
           'height' => 150
        )
    );

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        self::$options['fallback_image']['url'] = TEMPLATE_URL.'/image/page_image.*';
    }

    /**
     * Check the $options and set self::$options
     *
     * @param array $options
     */
    protected function checkOptions($options)
    {
        if (isset($options['fallback_image']['active']) && is_bool($options['fallback_image']['active'])) {
            self::$options['fallback_image']['active'] = $options['fallback_image']['active'];
        }
        if (isset($options['fallback_image']['url'])) {
            // validate the URL
            if ('localhost' != strtolower(parse_url($options['fallback_image']['url'], PHP_URL_HOST))) {
                $error = $this->app['validator']->validateValue($options['fallback_image']['url'], new Assert\Url());
                if (count($error) < 1) {
                    self::$options['fallback_image']['url'] = $options['fallback_image']['url'];
                }
            }
            else {
                // no checks at localhost
                self::$options['fallback_image']['url'] = $options['fallback_image']['url'];
            }
        }
        if (isset($options['minimum_size']['width']) && is_numeric($options['minimum_size']['width']) && ($options['minimum_size']['width'] > 0)) {
            self::$options['minimum_size']['width'] = intval($options['minimum_size']['width']);
        }
        if (isset($options['minimum_size']['height']) && is_numeric($options['minimum_size']['height']) && ($options['minimum_size']['height'] > 0)) {
            self::$options['minimum_size']['height'] = intval($options['minimum_size']['height']);
        }
    }

    /**
     * Validate the image in the given path (exists, type, width, height)
     *
     * @param string $image_path
     * @return boolean
     */
    protected function validateImage($image_path)
    {
        if ($this->app['filesystem']->exists($image_path)) {
            $errors = $this->app['validator']->validateValue($image_path, new Assert\Image(array(
                'minWidth' => self::$options['minimum_size']['width'],
                'minHeight' => self::$options['minimum_size']['height']
            )));
            if (count($errors) < 1) {
                // this image is valid
                return true;
            }
        }
        return false;
    }

    /**
     * Grab the URL of the first valid image from the content
     *
     * @param string $content of flexContent, News, Topics, WYSIWYG
     * @return string|boolean URL or FALSE
     */
    protected function getImage($content)
    {
        // filter the content with the DOM Crawler
        $crawler = new Crawler($content);
        $images = $crawler->filter('img');

        foreach ($images as $image) {
            // loop through the images
            $source = $image->getAttribute('src');
            if (false !== strpos($source, CMS_URL)) {
                // accept only local images!
                $image_path = CMS_PATH.substr($source, strlen(CMS_URL));
                if ($this->validateImage($image_path)) {
                    return $source;
                }
            }
        }
        return false;
    }

    /**
     * Get the first content image from any WYSIWYG, NEWS, TOPICS or flexContent article.
     * Try alternate to get a teaser image (TOPICS, flexContent)
     *
     * @param integer $page_id
     * @param array $options
     * @return string return the URL of the image or an empty string
     */
    public function page_image($page_id=PAGE_ID, $options=array())
    {
        // first check the options
        $this->checkOptions($options);

        $image = false;

        if (isset($_GET['command']) && ($_GET['command'] == 'flexcontent') &&
            isset($_GET['action']) && ($_GET['action'] == 'view') &&
            isset($_GET['content_id']) && is_numeric($_GET['content_id'])) {
            // this is a flexContent Article...
            $flexContentData = new flexContentData($this->app);
            if (false !== ($content = $flexContentData->select($_GET['content_id']))) {
                if (false === ($image = $this->getImage($content['content']))) {
                    // no image in the article, check teaser image
                    if (!empty($content['teaser_image']) &&
                        $this->validateImage(FRAMEWORK_PATH.$content['teaser_image'])) {
                        // use the teaser image
                        return FRAMEWORK_URL.$content['teaser_image'];
                    }
                }
                else {
                    // return the article image
                    return $image;
                }
            }
        }
        elseif (defined('EXTRA_POST_ID') && (EXTRA_POST_ID > 0)) {
            // this is a NEWS Article
            $SQL = "SELECT `content_long` FROM `".CMS_TABLE_PREFIX."mod_news_posts` WHERE `post_id`=".EXTRA_POST_ID;
            $content = $this->app['db']->fetchColumn($SQL);
            $content = $this->app['tools']->unsanitizeText($content);
            if (false !== ($image = $this->getImage($content))) {
                // use a image from the NEWS article
                return $image;
            }
        }
        elseif (defined('EXTRA_TOPIC_ID') && (EXTRA_TOPIC_ID > 0)) {
            // this is a TOPICS Article
            $SQL = "SELECT `content_long` FROM `".CMS_TABLE_PREFIX."mod_topics` WHERE `topic_id`=".EXTRA_TOPIC_ID;
            $content = $this->app['db']->fetchColumn($SQL);
            $content = $this->app['tools']->unsanitizeText($content);
            if (false !== ($image = $this->getImage($content))) {
                // use a image from the TOPICS arcticle
                return $image;
            }
            // no hit, check if a teaser image is available
            $SQL = "SELECT `picture` FROM `".CMS_TABLE_PREFIX."mod_topics` WHERE `topic_id`=".EXTRA_TOPIC_ID;
            $picture = $this->app['db']->fetchColumn($SQL);
            if (!empty($picture)) {
                $SQL = "SELECT `picture_dir` FROM `".CMS_TABLE_PREFIX."mod_topics_settings` WHERE `page_id`=".PAGE_ID;
                $directory = $this->app['db']->fetchColumn($SQL);
                if ($this->validateImage(CMS_PATH.$directory.'/'.$picture)) {
                    return CMS_URL.$directory.'/'.$picture;
                }
            }
        }
        elseif (PAGE_ID > 0) {
            // this is a regular page
            $sections = CMS_TABLE_PREFIX.'sections';
            $wysiwyg = CMS_TABLE_PREFIX.'mod_wysiwyg';
            $SQL = "SELECT `content` FROM `$sections`, `$wysiwyg` WHERE $wysiwyg.page_id=$sections.page_id AND ".
                "$wysiwyg.page_id=".PAGE_ID." AND $sections.module='wysiwyg'  ORDER BY `position` ASC";
            $sections = $this->app['db']->fetchAll($SQL);
            foreach ($sections as $section) {
                $content = $this->app['tools']->unsanitizeText($section['content']);
                // replace {SYSVAR:MEDIA_REL} with the real CMS_MEDIA_URL
                $content = str_replace('{SYSVAR:MEDIA_REL}', CMS_MEDIA_URL, $content);
                if (false !== ($image = $this->getImage($content))) {
                    // use a image from this WYSIWYG section
                    return $image;
                }
            }
        }
        else {
            // invalid page, perhaps search results?
            return '';
        }

        if ((false === $image) && self::$options['fallback_image']['active'] &&
            (false !== strpos(self::$options['fallback_image']['url'], CMS_URL))) {
            // no image detected, try to load the fallback image
            $image_path = CMS_PATH.substr(self::$options['fallback_image']['url'], strlen(CMS_URL));
            $path_info = pathinfo($image_path);
            if ($this->app['filesystem']->exists($path_info['dirname'])) {
                $images = new Finder();
                $images->name($path_info['basename'])->in($path_info['dirname']);
                $images->depth('== 0');
                foreach ($images as $image) {
                    if ($this->validateImage($image->getRealpath())) {
                        return str_replace('\\', '/', CMS_URL.substr($image->getRealpath(), strlen(CMS_PATH)));
                    }
                }
            }
        }

        // no hit, return empty string
        return '';
    }
}
