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
use phpManufaktur\Contact\Control\Dialog\Simple\TitleList as SimpleTitleList;

class TitleList extends Backend {

    protected $SimpleTitleList = null;

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
        $this->SimpleTitleList = new SimpleTitleList($this->app, array(
            'template' => array(
                'namespace' => '@phpManufaktur/Contact/Template',
                'message' => 'backend/message.twig',
                'list' => 'backend/admin/contact.title.list.twig'
            ),
            'route' => array(
                'create' => '/admin/contact/backend/title/create?usage='.self::$usage,
                'edit' => '/admin/contact/backend/title/edit/id/{title_id}?usage='.self::$usage
            )
        ));
    }

    public function controller(Application $app)
    {
        $this->initialize($app);
        $extra = array(
            'usage' => self::$usage,
            'toolbar' => $this->getToolbar('contact_edit')
        );
        return $this->SimpleTitleList->exec($extra);
    }

}
