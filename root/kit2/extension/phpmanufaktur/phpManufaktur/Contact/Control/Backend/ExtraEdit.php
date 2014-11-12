<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Contact
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control\Backend;

use Silex\Application;
use phpManufaktur\Contact\Control\Backend\Backend;
use phpManufaktur\Contact\Control\Dialog\Simple\ExtraFieldEdit as SimpleExtraFieldEdit;

class ExtraEdit extends Backend {

    protected $SimpleExtraFieldEdit = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app=null)
    {
        parent::__construct($app);
        if (!is_null($app)) {
            $this->initialize($app);
        }
    }

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Contact\Control\Backend\Backend::initialize()
     */
    protected function initialize(Application $app)
    {
        parent::initialize($app);
        $this->SimpleExtraFieldEdit = new SimpleExtraFieldEdit($this->app, array(
            'template' => array(
                'namespace' => '@phpManufaktur/Contact/Template',
                'alert' => 'pattern/alert.twig',
                'edit' => 'admin/edit.extra.twig'
            ),
            'route' => array(
                'action' => '/admin/contact/extra/create?usage='.self::$usage,
                'list' => '/admin/contact/extra/list?usage='.self::$usage
            )
        ));
    }

    /**
     * Set the type ID
     *
     * @param number $type_id
     */
    public function setTypeID($type_id)
    {
        $this->SimpleExtraFieldEdit->setTypeID($type_id);
    }

    /**
     * Controller to create and edit extra fields
     *
     * @param Application $app
     * @param integer $type_id
     */
    public function controller(Application $app, $type_id=null)
    {
        $this->initialize($app);
        if (!is_null($type_id)) {
            $this->setTypeID($type_id);
        }
        $extra = array(
            'usage' => self::$usage,
            'toolbar' => $this->getToolbar('extra_fields')
        );
        return $this->SimpleExtraFieldEdit->exec($extra);
    }

}
