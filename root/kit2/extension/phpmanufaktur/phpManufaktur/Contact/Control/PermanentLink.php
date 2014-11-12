<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Contact
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control;

use Silex\Application;
use phpManufaktur\Basic\Control\PermanentLinkBase;

class PermanentLink extends PermanentLinkBase
{

    /**
     * Controller for the permanent link /contact/public/view/id/{contact_id}
     *
     * @param Application $app
     * @param integer $contact_id
     */
    public function ControllerPublicViewID(Application $app, $contact_id=null)
    {
        $this->app = $app;

        if (!filter_var($contact_id, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
            $app['monolog']->addDebug('Access denied, contact_id is no integer value', array(__METHOD__));
            $app->abort(403, 'Access denied');
        }

        if (!$app['contact']->isActive($contact_id) || !$app['contact']->isPublic($contact_id)) {
            $app['monolog']->addDebug("Access denied, contact_id $contact_id is not active or not public.", array(__METHOD__));
            $app->abort(403, 'Access denied');
        }

        if (false === ($target_url = $app['contact']->getCategoryTargetURL($contact_id))) {
            $app['monolog']->addError("Missing the Category Target URL for the Contact ID $contact_id - access denied for the user!",
                array(__METHOD__));
            $app->abort(403, 'Access denied');
        }

        // create the parameter array
        $parameter = array(
            'command' => 'contact',
            'action' => 'view',
            'contact_id' => $contact_id,
            'canonical' => FRAMEWORK_URL."/contact/public/view/id/$contact_id",
        );

        // gather all GET parameter
        $gets = $app['request']->query->all();
        if (is_array($gets)) {
            foreach ($gets as $key => $value) {
                if (!in_array($key, array('pid'))) {
                    $parameter[$key] = $value;
                }
            }
        }

        // create the target URL and set the needed parameters
        $target_url = $target_url.'?'.http_build_query($parameter, '', '&');

        return $this->cURLexec($target_url);
    }
}
