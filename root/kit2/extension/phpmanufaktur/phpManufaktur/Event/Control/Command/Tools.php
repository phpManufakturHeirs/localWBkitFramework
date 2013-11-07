<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/event
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Event\Control\Command;

use Silex\Application;
use phpManufaktur\Contact\Data\Contact\Communication;

class Tools
{
    protected $app = null;
    protected $CommunicationData = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->CommunicationData = new Communication($app);
    }

    /**
     * Rewrite the symbolic tags 'contact', 'provider', 'organizer' and 'location'
     * to the real email addresses in context to the given event and contact data
     *
     * @param array $event event record
     * @param array $contact contact record
     * @param array $type_array symbolic tags
     * @return multitype:string NULL unknown Ambigous <unknown>
     */
    public function getEMailArrayFromTypeArray($event, $contact, $type_array)
    {
        $to_array = array();
        foreach ($type_array as $target) {
            switch ($target) {
                case 'provider':
                    $to_array[] = SERVER_EMAIL_ADDRESS; break;
                case 'organizer':
                    if ($event['contact']['organizer']['contact']['contact_type'] == 'COMPANY') {
                        $communication = $this->CommunicationData->select($event['contact']['organizer']['company'][0]['company_primary_email_id']);
                        $to_array[] = $communication['communication_value'];
                    }
                    else {
                        $communication = $this->CommunicationData->select($event['contact']['organizer']['person'][0]['person_primary_email_id']);
                        $to_array[] = $communication['communication_value'];
                    }
                    break;
                case 'location':
                    if ($event['contact']['location']['contact']['contact_type'] == 'COMPANY') {
                        $communication = $this->CommunicationData->select($event['contact']['location']['company'][0]['company_primary_email_id']);
                        $to_array[] = $communication['communication_value'];
                    }
                    else {
                        $communication = $this->CommunicationData->select($event['contact']['location']['person'][0]['person_primary_email_id']);
                        $to_array[] = $communication['communication_value'];
                    }
                    break;
                case 'contact':
                    $to_array[] = $contact['contact']['contact_login']; break;
            }
        }
        return $to_array;
    }


}
