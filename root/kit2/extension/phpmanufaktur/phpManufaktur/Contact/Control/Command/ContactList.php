<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Contact
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Control\Command;

use Silex\Application;
use phpManufaktur\Basic\Control\kitCommand\Basic;
use phpManufaktur\Contact\Data\Contact\ContactFilter;
use phpManufaktur\Contact\Data\Contact\CategoryType;

class ContactList extends Basic
{
    protected static $parameter = null;
    protected static $filter = null;
    protected static $config = null;

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Basic\Control\kitCommand\Basic::initParameters()
     */
    protected function initParameters(Application $app, $parameter_id=-1)
    {
        parent::initParameters($app, $parameter_id);

        self::$parameter = $this->getCommandParameters();

        // check the CMS GET parameters
        $GET = $this->getCMSgetParameters();
        if (isset($GET['command']) && ($GET['command'] == 'contact') &&
            isset($GET['action']) && ($GET['action'] == 'list')) {
            foreach ($GET as $key => $value) {
                if ($key == 'command') continue;
                self::$parameter[$key] = $value;
            }
            $this->setCommandParameters(self::$parameter);
        }

        // grant that the 'action' value is set and is a lower string
        self::$parameter['action'] = isset(self::$parameter['action']) ? strtolower(self::$parameter['action']) : 'none';

        $CategoryTypeData = new CategoryType($app);

        $filter = isset(self::$parameter['filter']) ? self::$parameter['filter'] : '';

        $filter_array = (strpos($filter, '|')) ? explode('|', $filter) : array($filter);

        self::$filter = array();

        if (!empty($filter_array)) {
            foreach ($filter_array as $filter_item) {
                if (strpos($filter_item, '=')) {
                    list($key, $value) = explode('=', $filter_item);
                    $key = strtolower(trim($key));
                    switch ($key) {
                        case 'category':
                            // extract the categories as integer values
                            $cats = (strpos($value, ',')) ? explode(',', $value) : array($value);
                            $categories = array();
                            foreach ($cats as $cat) {
                                $cat = trim($cat);
                                if (is_numeric($cat) && ($cat > 0)) {
                                    $categories[] = intval($cat);
                                }
                                elseif (false !== ($cat_type = $CategoryTypeData->selectByName($cat))) {
                                    // got the category ID from the category type name
                                    $categories[] = $cat_type['category_type_id'];
                                }
                            }
                            self::$filter[$key] = $categories;
                            break;
                        case 'city':
                            $cities = (strpos($value, ',')) ? explode(',', $value) : array($value);
                            $city_array = array();
                            foreach ($cities as $city) {
                                // IMPORTANT! special chars are probably encoded, set explicit UTF-8!
                                $city_array[] = mb_convert_encoding(html_entity_decode(trim($city)), 'UTF-8');
                            }
                            self::$filter[$key] = $city_array;
                            break;
                        case 'contact_type':
                            $types = (strpos($value, ',')) ? explode(',', $value) : array($value);
                            $contact_types = array();
                            foreach ($types as $type) {
                                $type = strtoupper(trim($type));
                                if (in_array($type, array('PERSON','COMPANY'))) {
                                    $contact_types[] = "'".strtoupper($type)."'";
                                }
                            }
                            self::$filter[$key] = $contact_types;
                            break;
                        case 'country':
                            $countries = (strpos($value, ',')) ? explode(',', $value) : array($value);
                            $country_array = array();
                            foreach ($countries as $country) {
                                $country_array[] = strtoupper("'$country'");
                            }
                            self::$filter[$key] = $country_array;
                            break;
                        case 'order_by':
                            $bys = (strpos($value, ',')) ? explode(',', $value) : array($value);
                            $order_by = array();
                            foreach ($bys as $by) {
                                $order_by[] = "`".trim($by)."`";
                            }
                            self::$filter[$key] = $order_by;
                            break;
                        case 'order_direction':
                            self::$filter[$key] = (strtoupper($value) == 'DESC') ? 'DESC' : 'ASC';
                            break;
                        case 'limit':
                            self::$filter[$key] = intval($value);
                            break;
                        case 'state':
                            $states = (strpos($value, ',')) ? explode(',', $value) : array($value);
                            $state_array = array();
                            foreach ($states as $state) {
                                // IMPORTANT! special chars are probably encoded, set explicit UTF-8!
                                $state_array[] = mb_convert_encoding(html_entity_decode(trim($state)), 'UTF-8');;
                            }
                            self::$filter[$key] = $state_array;
                            break;
                        case 'tag':
                            // extract the tags as integer values
                            $tags = (strpos($value, ',')) ? explode(',', $value) : array($value);
                            $tag_array = array();
                            foreach ($tags as $tag) {
                                $tag_array[] = trim($tag);
                            }
                            self::$filter[$key] = $tag_array;
                            break;
                        case 'zip':
                            $zips = (strpos($value, ',')) ? explode(',', $value) : array($value);
                            $zip_array = array();
                            foreach ($zips as $zip) {
                                $zip = trim($zip);
                                if (strtolower($zip) == 'null') {
                                    $zip_array[] = 0;
                                }
                                else {
                                    $zip_array[] = $zip;
                                }
                            }
                            self::$filter[$key] = $zip_array;
                            break;
                        default:
                            // uh? unknown filter!
                            $this->setAlert('The filter %filter% is unknown, please check the kitCommand!',
                                array('%filter%' => $key), self::ALERT_TYPE_WARNING);
                            break;
                    }
                }
            }
        }

        // grant the filter keys
        self::$filter['category'] = isset(self::$filter['category']) ? self::$filter['category'] : null;
        self::$filter['city'] = isset(self::$filter['city']) ? self::$filter['city'] : null;
        self::$filter['contact_type'] = isset(self::$filter['contact_type']) ? self::$filter['contact_type'] : array("'PERSON'","'COMPANY'");
        self::$filter['country'] = isset(self::$filter['country']) ? self::$filter['country'] : null;
        self::$filter['limit'] = isset(self::$filter['limit']) ? self::$filter['limit'] : 100;
        self::$filter['order_by'] = isset(self::$filter['order_by']) ? self::$filter['order_by'] : array('order_name');
        self::$filter['order_direction'] = isset(self::$filter['order_direction']) ? self::$filter['order_direction'] : 'ASC';
        self::$filter['state'] = isset(self::$filter['state']) ? self::$filter['state'] : null;
        self::$filter['tag'] = isset(self::$filter['tag']) ? self::$filter['tag'] : null;
        self::$filter['zip'] = isset(self::$filter['zip']) ? self::$filter['zip'] : null;

        // read the list.contact.json
        $config_path = $app['utils']->getTemplateFile('@phpManufaktur/Contact/Template',
            'command/list.contact.json', $this->getPreferredTemplateStyle(), true);
        self::$config = $app['utils']->readJSON($config_path);
    }



    public function ControllerList(Application $app)
    {
        $this->initParameters($app);

        $Filter = new ContactFilter($app);
        $contacts = $Filter->filter(self::$filter);

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Contact/Template', 'command/list.contact.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'contacts' => $contacts,
                'columns' => self::$config['columns']
            ));
    }
}
