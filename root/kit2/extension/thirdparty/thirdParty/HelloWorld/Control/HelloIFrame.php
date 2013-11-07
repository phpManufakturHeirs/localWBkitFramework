<?php

/**
 * kfHelloWorld
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace thirdParty\HelloWorld\Control;

use phpManufaktur\Basic\Control\kitCommand\Basic as kitCommandBasic;
use thirdParty\HelloWorld\Data\HelloWorld;
use Silex\Application;

class HelloIFrame extends kitCommandBasic {

    /**
     * Show a simple dialog within a iframe
     *
     * @return string rendered dialog
     */
    public function start(Application $app)
    {
        $this->initParameters($app);

        $this->setRedirectRoute('/helloworld/iframe/start');
        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@thirdParty/HelloWorld/Template',
            'hello.iframe.start.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings()
        ));
    }

    /**
     * Define the form for Sample 08 and return the form object
     *
     * @return object
     */
    protected function createForm()
    {
        return $this->app['form.factory']->createBuilder('form')
        ->add('title', 'choice', array(
            'choices' => array('mister' => 'Mister', 'lady' => 'Lady'),
            'expanded' => false,
            'label' => 'Title'
        ))
        ->add('first_name', 'text', array(
            'label' => 'First name',
            'required' => false
        ))
        ->add('last_name', 'text', array(
            'label' => 'Last name',
            'required' => true
        ))
        ->add('email', 'email', array(
            'label' => 'Email',
            'required' => true
        ))
        ->getForm();
    }

    /**
     * Show the contact dialog
     *
     * @return rendered dialog
     */
    public function step02(Application $app)
    {
        $this->initParameters($app);

        $form = $this->createForm();
        $this->setRedirectRoute('/helloworld/iframe/step02');
        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@thirdParty/HelloWorld/Template',
            'hello.iframe.step02.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'form' => $form->createView(),
                'basic' => $this->getBasicSettings()
        ));
    }

    public function step03(Application $app)
    {
        $this->initParameters($app);

        // create the form
        $form = $this->createForm();
        // bind the request to the form
        $form->bind($this->app['request']);
        // check the form
        if (!$form->isValid()) {
            // something went wrong, possible CSFR attack - return to the default form and prompt a message
            $this->setMessage($this->app['translator']->trans('<p>The submitted form is not valid, please try again.</p>'));
            return $this->step02($this->app);
        }
        // get the data from the form
        $form_data = $form->getData();
        // now we want to save the data to the database
        $HelloWorld = new HelloWorld($this->app);
        // ensure that the table is created
  //$HelloWorld->createTable();
        // collect the data
        $record = array(
            'title' => $form_data['title'],
            'first_name' => isset($form_data['first_name']) ? $form_data['first_name'] : '',
            'last_name' => $form_data['last_name'],
            'email' => $form_data['email']
        );
        // insert the record and get the new ID
        $record['id'] = $HelloWorld->insert($record);

        $this->setRedirectRoute('/helloworld/iframe/step04/'.$record['id']);

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@thirdParty/HelloWorld/Template',
            'hello.iframe.step03.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'data' => $record,
                'basic' => $this->getBasicSettings()
            ));
    }

    public function step04(Application $app, $id)
    {
        $this->initParameters($app);

        // select the contact data from the database
        $HelloWorld = new HelloWorld($this->app);
        $data = $HelloWorld->select($id);

        // create the email body
        $body = $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@thirdParty/HelloWorld/Template',
            'hello.iframe.email.twig',
            $this->getPreferredTemplateStyle()),
            array('data' => $data));

        // create the message
        $message = \Swift_Message::newInstance()
        ->setSubject($this->app['translator']->trans('Hello World confirmation'))
        ->setFrom(array(SERVER_EMAIL_ADDRESS))
        ->setTo(array($data['email']))
        ->setBody($body)
        ->setContentType('text/html');
        // send the message
        $this->app['mailer']->send($message);

        $this->setRedirectRoute('/helloworld/iframe/step04/'.$id);

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@thirdParty/HelloWorld/Template',
            'hello.iframe.step04.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'data' => $data,
                'basic' => $this->getBasicSettings()
            ));
    }
}
