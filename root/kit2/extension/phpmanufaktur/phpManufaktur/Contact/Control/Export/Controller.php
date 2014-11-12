<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Contact
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control\Export;

use Silex\Application;
use phpManufaktur\Basic\Control\Pattern\Alert;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;

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
     * Get the form to select the export type
     *
     * @return FormFactory
     */
    protected function getFormSelectExport()
    {
        $action = array(
            '/admin/contact/export/type/csv' => 'CSV => *.csv',
            '/admin/contact/export/type/xls' => 'Excel => *.xls',
            '/admin/contact/export/type/xlsx' => 'Excel => *.xlsx'
        );

        return $this->app['form.factory']->createBuilder('form')
        ->add('export', 'choice', array(
            'choices' => $action,
            'empty_value' => false,
            'multiple' => false,
            'expanded' => true,
            'label' => 'Export as',
            'data' => '/admin/contact/export/type/xlsx'
        ))
        ->getForm();
    }

    /**
     * Controller to execute the desired export
     *
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ControllerExecute(Application $app)
    {
        $this->initialize($app);
        $form = $this->getFormSelectExport();

        // get the requested data
        $form->bind($this->app['request']);

        if ($form->isValid()) {
            // the form is valid
            $action = $form->getData();
            $subRequest = Request::create($action['export'], 'POST',  array('usage' => self::$usage));
            return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
            return $this->ControllerStart($app);
        }
    }

    /**
     * Controller to select the type and mode for export
     *
     * @param Application $app
     */
    public function ControllerStart(Application $app)
    {
        $this->initialize($app);
        $form = $this->getFormSelectExport();

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Contact/Template', 'admin/export/start.export.twig'),
            array(
                'alert' => $this->getAlert(),
                'usage' => self::$usage,
                'form' => $form->createView()
            ));

        $this->setAlert('Please select the target file format to export the kitFramework Contact records: <a href="%xlsx%">XLSX (Excel)</a> or <a href="%csv%">CSV (Text)</a>.',
            array('%xlsx%' => FRAMEWORK_URL.'/admin/contact/export/excel', '%csv%' => FRAMEWORK_URL.'/admin/contact/export/csv'),
            self::ALERT_TYPE_INFO);
        return $this->promptAlertFramework();
    }
}
