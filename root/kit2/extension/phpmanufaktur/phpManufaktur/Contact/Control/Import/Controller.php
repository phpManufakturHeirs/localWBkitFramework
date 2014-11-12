<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Contact
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control\Import;

use Silex\Application;
use phpManufaktur\Basic\Control\Pattern\Alert;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactory;

class Controller extends Alert
{

    protected static $usage = null;

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Basic\Control\Pattern\Alert::initialize()
     */
    protected function initialize(Application $app)
    {
       parent::initialize($app);
       self::$usage = $app['request']->get('usage', 'framework');
    }

    /**
     * Get the form to select the import type
     *
     * @return FormFactory
     */
    protected function getFormSelectImport()
    {
        $action = array(
            '/admin/contact/import/type/csv' => 'CSV => *.csv',
            '/admin/contact/import/type/xls' => 'Excel => *.xls',
            '/admin/contact/import/type/xlsx' => 'Excel => *.xlsx',
            '/admin/contact/import/keepintouch' => 'KeepInTouch',
            '/admin/contact/import/type/ods' => 'Open Office/Libre Office => *.ods'
        );

        return $this->app['form.factory']->createBuilder('form')
        ->add('import', 'choice', array(
            'choices' => $action,
            'empty_value' => false,
            'multiple' => false,
            'expanded' => true,
            'label' => 'Import from',
            // set the first entry as default value
            'data' => '/admin/contact/import/type/xlsx'
        ))
        ->getForm();
    }

    /**
     * Controller to select and start an import
     *
     * @param Application $app
     */
    public function Controller(Application $app)
    {
        $this->initialize($app);
        $form = $this->getFormSelectImport();

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Contact/Template', 'admin/import/start.import.twig'),
            array(
                'alert' => $this->getAlert(),
                'usage' => self::$usage,
                'form' => $form->createView()
            ));
    }

    /**
     * Check the selected import type and execute the next step
     *
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ControllerSelect(Application $app)
    {
        $this->initialize($app);
        $form = $this->getFormSelectImport();

        // get the requested data
        $form->bind($this->app['request']);

        if ($form->isValid()) {
            // the form is valid
            $action = $form->getData();
            $subRequest = Request::create($action['import'], 'POST',  array('usage' => self::$usage));
            return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
            return $this->Controller($app);
        }
    }
}
