<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Contact
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Data\Contact;

use Silex\Application;

class ContactFilter
{
    protected $app = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Contact filter
     *
     * @param array $filter
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function filter($filter)
    {
        try {
            // base SQL - access only PUBLIC and ACTIVE contacts
            $SQL = "SELECT * FROM `".FRAMEWORK_TABLE_PREFIX."contact_overview` WHERE `category_access`='PUBLIC' AND ".
                "`contact_status`='ACTIVE'";

            // filter for the category ID
            if (isset($filter['category']) && is_array($filter['category']) && !empty($filter['category'])) {
                $SQL .= " AND `category_id` IN (".implode(',', $filter['category']).")";
            }

            // filter the contact type 'PERSON' or 'COMPANY'
            if (isset($filter['contact_type']) && is_array($filter['contact_type']) && !empty($filter['contact_type'])) {
                $SQL .= " AND `contact_type` IN (".implode(',', $filter['contact_type']).")";
            }

            // filter the tag's
            if (isset($filter['tag']) && is_array($filter['tag']) && !empty($filter['tag'])) {
                $SQL .= " AND (";
                $start = true;
                foreach ($filter['tag'] as $tag) {
                    if (!$start) {
                        $SQL .= " OR ";
                    }
                    $SQL .= "((`tags`='$tag') OR (`tags` LIKE '$tag,%') OR (`tags` LIKE '%,$tag,%') OR (`tags` LIKE '%,$tag'))";
                    $start = false;
                }
                $SQL .= ")";
            }

            // filter the zip's
            if (isset($filter['zip']) && is_array($filter['zip']) && !empty($filter['zip'])) {
                $SQL .= " AND (";
                $start = true;
                foreach ($filter['zip'] as $zip) {
                    if (!$start) {
                        $SQL .= " OR ";
                    }
                    $SQL .= "(`address_zip` LIKE '$zip%')";
                    $start = false;
                }
                $SQL .= ")";
            }

            // filter for the city
            if (isset($filter['city']) && is_array($filter['city']) && !empty($filter['city'])) {
                $SQL .= " AND (";
                $start = true;
                foreach ($filter['city'] as $city) {
                    if (!$start) {
                        $SQL .= " OR ";
                    }
                    $SQL .= "(`address_city` LIKE '%$city%')";
                    $start = false;
                }
                $SQL .= ")";
            }

            // filter for states
            if (isset($filter['state']) && is_array($filter['state']) && !empty($filter['state'])) {
                $SQL .= " AND (";
                $start = true;
                foreach ($filter['state'] as $state) {
                    if (!$start) {
                        $SQL .= " OR ";
                    }
                    $SQL .= "(`address_state` LIKE '%$state%')";
                    $start = false;
                }
                $SQL .= ")";
            }

            // filter for country codes
            if (isset($filter['country']) && is_array($filter['country']) && !empty($filter['country'])) {
                $SQL .= " AND `address_country_code` IN (".implode(',', $filter['country']).")";
            }

            // ... and the tail of the SQL query:
            if (isset($filter['order_by']) && is_array($filter['order_by']) && !empty($filter['order_by'])) {
                $SQL .= " ORDER BY ".implode(', ',$filter['order_by']);
                $SQL .= isset($filter['order_direction']) ? " ".$filter['order_direction'] : " ASC";
            }
            if (isset($filter['limit'])) {
                $SQL .= " LIMIT ".$filter['limit'];
            }

            $results = $this->app['db']->fetchAll($SQL);

            $contacts = array();

            if (is_array($results)) {
                foreach ($results as $result) {
                    $contact = array();
                    foreach ($result as $key => $value) {
                        $contact[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                    }
                    $contacts[] = $contact;
                }
            }

            // return the contacts
            return (!empty($contacts)) ? $contacts : false;

        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }
}
