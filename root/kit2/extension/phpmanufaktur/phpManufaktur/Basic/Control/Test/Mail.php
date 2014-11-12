<?php

/**
 * Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\Test;

use Silex\Application;
use phpManufaktur\Basic\Control\Pattern\Alert;

class Mail extends Alert
{
    protected static $usage = null;

    public function Controller(Application $app)
    {
        $this->initialize($app);
        self::$usage = $app['request']->get('usage', 'framework');

        // get the settings for the Swiftmailer
        $settings = $app['utils']->readJSON(FRAMEWORK_PATH.'/config/swift.cms.json');
        $settings['SMTP_PASSWORD'] = '******';

        // get the FRAMEWORK_UID
        $framework_config = $this->app['utils']->readJSON(FRAMEWORK_PATH.'/config/framework.json');

        // create the email body
        $body = $app['twig']->render($app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/mail/test.twig'),
            array(
                'settings' => $settings,
                'server' => array(
                    'email' => array(
                        'name' => SERVER_EMAIL_NAME,
                        'address' => SERVER_EMAIL_ADDRESS
                    )
                ),
                'framework_uid' => $framework_config['FRAMEWORK_UID']
            ));

        // create the message
        $message = \Swift_Message::newInstance()
        ->setSubject($app['translator']->trans('kitFramework email test'))
        ->setFrom(array(SERVER_EMAIL_ADDRESS))
        ->setTo(array(SERVER_EMAIL_ADDRESS))
        ->setBody($body)
        ->setContentType('text/html');

        // send the message
        $failed = array();
        if (!$app['mailer']->send($message, $failed)) {
            $this->setAlert('Failed to send a email with the subject <b>%subject%</b> to the addresses: <b>%failed%</b>.',
                array('%subject%' => $app['translator']->trans('kitFramework email test'),
                    '%failed%' => implode(', ', $failed)), self::ALERT_TYPE_DANGER);
            return false;
        }
        else {
            $this->setAlert('The test mail to %email% was successfull send.',
                array('%email%' => SERVER_EMAIL_ADDRESS), self::ALERT_TYPE_SUCCESS);
        }

        return $app['twig']->render($app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/test/mail.twig'),
            array(
                'settings' => $settings,
                'usage' => self::$usage,
                'alert' => $this->getAlert()
            ));
    }
}
