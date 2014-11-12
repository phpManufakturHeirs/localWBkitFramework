<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Event
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Event\Control\Command;

use phpManufaktur\Basic\Control\kitCommand\Basic;

class Message extends Basic
{

    /**
     * Return a rendered message dialog for kitEvent
     *
     * @param string $message
     * @param array $message_params
     * @param string $title
     * @param array $title_params
     * @param boolean $log_message if true add a DEBUG message to the logfile
     */
    public function render($message, $message_params=array(), $title='kitCommand ~~ event ~~', $title_params=array(), $log_message=false)
    {
        if ($log_message) {
            // log this message
            $this->app['monolog']->addDebug(strip_tags($this->app['translator']->trans($message, $message_params, 'messages', 'en')));
        }

        // very important - no redirection to avoid a possible recursion!
        $this->setRedirectActive(false);

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template',
            'command/message.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'message' => $this->app['translator']->trans($message, $message_params),
                'title' => $this->app['translator']->trans($title, $title_params)
            ));
    }

}
