<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Contact
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control\Command;

use Silex\Application;
use phpManufaktur\Basic\Control\kitCommand\Basic;
use phpManufaktur\Contact\Data\Contact\Contact;
use phpManufaktur\Contact\Data\Contact\TagType;
use phpManufaktur\Contact\Data\Contact\CategoryType;

class ContactSearch extends Basic
{
    protected static $list_configuration = null;

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Basic\Control\kitCommand\Basic::initParameters()
     */
    protected function initParameters(Application $app, $parameter_id=-1)
    {
        parent::initParameters($app, $parameter_id);

        // read the list.contact.json
        $config_path = $app['utils']->getTemplateFile('@phpManufaktur/Contact/Template',
            'command/list.contact.json', $this->getPreferredTemplateStyle(), true);
        self::$list_configuration = $app['utils']->readJSON($config_path);
    }

    /**
     * Controller to execute the search for public contacts
     *
     * @param Application $app
     */
    public function ControllerSearch(Application $app)
    {
        $this->initParameters($app);

        $parameter = $this->getCommandParameters();

        $tags = array();
        $use_tags = false;
        if (isset($parameter['tags'])) {
            $use_tags = true;
            if (!empty($parameter['tags'])) {
                $TagTypeData = new TagType($app);
                $tag_array = strpos($parameter['tags'], ',') ? explode(',', $parameter['tags']) : array($parameter['tags']);
                if (!empty($tag_array)) {
                    foreach ($tag_array as $tag) {
                        $tag = strtoupper(trim($tag));
                        if ($TagTypeData->existsTag($tag)) {
                            $tags[] = $tag;
                        }
                    }
                }
            }
        }
        $tag_select = $app['contact']->getTagArrayForTwig();

        $categories = array();
        $use_categories = false;
        $category_select = array();
        if (isset($parameter['categories'])) {
            $use_categories = true;
            if (!empty($parameter['categories'])) {
                $CategoryTypeData = new CategoryType($app);
                $category_array = strpos($parameter['categories'], ',') ? explode(',', $parameter['categories']) : array($parameter['categories']);
                if (!empty($category_array)) {
                    foreach ($category_array as $category) {
                        $category = strtoupper(trim($category));
                        if ($CategoryTypeData->existsCategory($category) &&
                            $CategoryTypeData->isPublic($category)) {
                            $categories[] = $category;
                        }
                    }
                }
            }
        }
        $category_select = $app['contact']->getCategoryArrayForTwig('PUBLIC');

        $contacts = array();

        $cms_parameter = $this->getCMSgetParameters();
        $search = $app['request']->get('search');
        if (is_null($search) && isset($cms_parameter['search'])) {
            $search = $cms_parameter['search'];
        }

        if (!is_null($search)) {
            //$search = trim($app['request']->get('search'));
            $search = trim($search);
            $search_tags = $tags;
            if ($use_tags && empty($search_tags)) {
                if ((null !== ($tag = $app['request']->get('tag'))) && ($tag != -1)) {
                    $search_tags = array($tag);
                }
            }
            $search_categories = $categories;
            if ($use_categories && empty($search_categories)) {
                if ((null !== ($category = $app['request']->get('category'))) && ($category != -1)) {
                    $search_categories = array($category);
                }
            }
            $Contact = new Contact($app);
            if (false !== ($contacts = $Contact->searchPublicContact($search, $search_tags, $search_categories))) {
                // search was successfull
                if (!empty($search)) {
                    $this->setAlert('%hits% hits for the search of <strong>%search%</strong>', array(
                        '%hits%' => count($contacts), '%search%' => $search), self::ALERT_TYPE_SUCCESS);
                }
                else {
                    $this->setAlert('Please use a search term to reduce the hits!', array(), self::ALERT_TYPE_WARNING);
                }
            }
            else {
                // no hits
                $this->setAlert('No hits for the search of %search%', array(
                    '%search%' => $search), self::ALERT_TYPE_INFO);
            }
        }


        // no extra space for the iframe
        $this->setFrameAdd(0);

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Contact/Template', 'command/search.contact.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'use_tags' => $use_tags,
                'tags' => $tags,
                'tag_select' => $tag_select,
                'use_categories' => $use_categories,
                'categories' => $categories,
                'category_select' => $category_select,
                'contacts' => $contacts,
                'columns' => self::$list_configuration['columns'],
                'search' => $search
            ));
    }
}
