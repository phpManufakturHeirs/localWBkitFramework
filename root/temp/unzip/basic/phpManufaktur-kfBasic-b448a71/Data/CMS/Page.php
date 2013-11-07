<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Data\CMS;

use Silex\Application;
use phpManufaktur\Basic\Data\CMS\WebsiteBaker\Page as WebsiteBakerPage;
use phpManufaktur\Basic\Data\CMS\LEPTON\Page as LeptonPage;
use phpManufaktur\Basic\Data\CMS\BlackCat\Page as BlackCatPage;

class Page {

    protected $app = null;
    protected $cms = null;

    /**
     * Constructor
     *
     * @param Application $app
     * @throws \Exception
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        switch (CMS_TYPE) {
            case 'WebsiteBaker':
                $this->cms = new WebsiteBakerPage($app); break;
            case 'LEPTON':
                $this->cms = new LeptonPage($app); break;
            case 'BlackCat':
                $this->cms = new BlackCatPage($app); break;
            default:
                throw new \Exception(sprintf("The CMS TYPE <b>%s</b> is not supported!", CMS_TYPE));
        }
    }

    /**
     * Get the page extension with leading dot, by default '.php'
     *
     * @throws \Exception
     * @return string page extension
     */
    public function getPageExtension()
    {
        return $this->cms->getPageExtension();
    }

    /**
     * Get the page directory with leading slash, by default '/pages' or '/page'
     *
     * @throws \Exception
     * @return string page directory
     */
    public function getPageDirectory()
    {
        $this->cms->getPageDirectory();
    }

    /**
     * Get the URL of the given page ID. If arguments 'topic_id' or 'post_id'
     * the function will return the URL for the given TOPICS or NEWS article
     *
     * @param integer $page_id
     * @param null|array $arguments
     * @throws \Exception
     * @return string URL of the page
     */
    public function getURL($id, $command_parameter=null)
    {
        $parameter = null;
        if (is_array($command_parameter) && (isset($command_parameter['cms']['special']['topic_id']) ||
            isset($command_parameter['cms']['special']['post_id']))) {
            $parameter = array();
            if (isset($command_parameter['cms']['special']['topic_id'])) {
                $parameter['topic_id'] = $command_parameter['cms']['special']['topic_id'];
            }
            if (isset($command_parameter['cms']['special']['post_id'])) {
                $parameter['topic_id'] = $command_parameter['cms']['special']['post_id'];
            }
        }
        else {
            $parameter = $command_parameter;
        }
        return $this->cms->getURL($id, $parameter);
    }

    /**
     * Get the title of the given page ID. If arguments 'topic_id' or 'post_id'
     * the function will return the title for the given TOPICS or NEWS article
     *
     * @param integer $page_id
     * @param null|array $arguments
     * @throws \Exception
     * @return string title of the page
     */
    public function getTitle($page_id, $command_parameter=null)
    {
        $parameter = null;
        if (is_array($command_parameter) && (isset($command_parameter['cms']['special']['topic_id']) ||
            isset($command_parameter['cms']['special']['post_id']))) {
            $parameter = array();
            if (isset($command_parameter['cms']['special']['topic_id'])) {
                $parameter['topic_id'] = $command_parameter['cms']['special']['topic_id'];
            }
            if (isset($command_parameter['cms']['special']['post_id'])) {
                $parameter['topic_id'] = $command_parameter['cms']['special']['post_id'];
            }
        }
        else {
            $parameter = $command_parameter;
        }
        return $this->cms->getTitle($page_id, $parameter);
    }
}
