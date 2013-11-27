<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/event
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control\Backend;

use Silex\Application;
use phpManufaktur\Contact\Control\Backend\Backend;
use phpManufaktur\Contact\Control\Dialog\Simple\TitleEdit as SimpleTitleEdit;

class TitleEdit extends Backend {

    protected $SimpleTitleEdit = null;

    public function __construct(Application $app=null)
    {
        parent::__construct($app);
        if (!is_null($app)) {
            $this->initialize($app);
        }
    }

    protected function initialize(Application $app)
    {
        parent::initialize($app);
        $this->SimpleTitleEdit = new SimpleTitleEdit($this->app, array(
            'template' => array(
                'namespace' => '@phpManufaktur/Contact/Template',
                'message' => 'backend/message.twig',
                'edit' => 'backend/admin/contact.title.edit.twig'
            ),
            'route' => array(
                'action' => '/admin/contact/backend/title/edit?usage='.self::$usage
            )
        ));
    }

    /**
     * @param number $title_id
     */
    public function setTitleID($title_id)
    {
        $this->SimpleTitleEdit->setTitleID($title_id);
    }

    public function controller(Application $app, $title_id=null)
    {
        $this->initialize($app);
        if (!is_null($title_id)) {
            $this->setTitleID($title_id);
        }
        $extra = array(
            'usage' => self::$usage,
            'toolbar' => $this->getToolbar('contact_edit')
        );
        return $this->SimpleTitleEdit->exec($extra);
    }

}
