<?php

/**
 * Contact
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Contact
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Contact\Data\Setup;

use Silex\Application;
use phpManufaktur\Contact\Data\Contact\Address;
use phpManufaktur\Contact\Data\Contact\Communication;
use phpManufaktur\Contact\Data\Contact\Company;
use phpManufaktur\Contact\Data\Contact\Contact;
use phpManufaktur\Contact\Data\Contact\Person;
use phpManufaktur\Contact\Data\Contact\Title;
use phpManufaktur\Contact\Data\Contact\Country;
use phpManufaktur\Contact\Data\Contact\CommunicationType;
use phpManufaktur\Contact\Data\Contact\CommunicationUsage;
use phpManufaktur\Contact\Data\Contact\AddressType;
use phpManufaktur\Contact\Data\Contact\Note;
use phpManufaktur\Contact\Data\Contact\Overview;
use phpManufaktur\Contact\Data\Contact\CategoryType;
use phpManufaktur\Contact\Data\Contact\Category;
use phpManufaktur\Contact\Data\Contact\TagType;
use phpManufaktur\Contact\Data\Contact\Tag;
use phpManufaktur\Contact\Data\Contact\Protocol;
use phpManufaktur\Contact\Data\Contact\ExtraType;
use phpManufaktur\Contact\Data\Contact\ExtraCategory;
use phpManufaktur\Contact\Data\Contact\Extra;
use phpManufaktur\Contact\Data\Contact\Message;
use phpManufaktur\Contact\Control\Configuration;
use phpManufaktur\Basic\Control\CMS\InstallAdminTool;
use phpManufaktur\Contact\Data\Contact\Form;

class Setup
{

    public function exec(Application $app)
    {
        try {
            $Contact = new Contact($app);
            $Contact->createTable();
            $app['monolog']->addInfo('[Contact Setup] Create table `contact_contact`');

            $CommunicationType = new CommunicationType($app);
            $CommunicationType->createTable();
            $app['monolog']->addInfo('[Contact Setup] Create table `contact_communication_type`');
            $CommunicationType->initCommunicationTypeList();
            $app['monolog']->addInfo('[Contact Setup] Import the default data for `contact_communication_type`');

            $CommunicationUsage = new CommunicationUsage($app);
            $CommunicationUsage->createTable();
            $app['monolog']->addInfo('[Contact Setup] Create table `contact_communication_usage`');
            $CommunicationUsage->initCommunicationUsageList();
            $app['monolog']->addInfo('[Contact Setup] Import the default data for `contact_communication_usage`');

            $Communication = new Communication($app);
            $Communication->createTable();
            $app['monolog']->addInfo('[Contact Setup] Create table `contact_communication`');

            $Country = new Country($app);
            $Country->createTable();
            $app['monolog']->addInfo('[Contact Setup] Create table `contact_country`');
            $Country->initCountryList();
            $app['monolog']->addInfo('[Contact Setup] Import the default data for `contact_country`');

            $AddressType = new AddressType($app);
            $AddressType->createTable();
            $app['monolog']->addInfo('[Contact Setup] Create table `contact_address_type`');
            $AddressType->initAddressTypeList();
            $app['monolog']->addInfo('[Contact Setup] Import the default data for `contact_address_type`');

            $Address = new Address($app);
            $Address->createTable();
            $app['monolog']->addInfo('[Contact Setup] Create table `contact_address`');

            $Title = new Title($app);
            $Title->createTable();
            $app['monolog']->addInfo('[Contact Setup] Create table `contact_title`');
            $Title->initTitleList();
            $app['monolog']->addInfo('[Contact Setup] Import the default data for `contact_title`');

            $Note = new Note($app);
            $Note->createTable();
            $app['monolog']->addInfo('[Contact Setup] Create table `contact_note`');

            $Person = new Person($app);
            $Person->createTable();
            $app['monolog']->addInfo('[Contact Setup] Create table `contact_person`');

            $Company = new Company($app);
            $Company->createTable();
            $app['monolog']->addInfo('[Contact Setup] Create table `contact_company`');

            $Overview = new Overview($app);
            $Overview->createTable();
            $app['monolog']->addInfo('[Contact Setup] Create table `contact_overview`');

            $CategoryType = new CategoryType($app);
            $CategoryType->createTable();
            $app['monolog']->addInfo('[Contact Setup] Create table `contact_category_type`');
            $CategoryType->initCategoryTypeList();
            $app['monolog']->addInfo('[Contact Setup] Import the default data for `contact_category_type`');

            $Category = new Category($app);
            $Category->createTable();
            $app['monolog']->addInfo('[Contact Setup] Create table `contact_category`');

            $TagType = new TagType($app);
            $TagType->createTable();
            $app['monolog']->addInfo('[Contact Setup] Create table `contact_tag_type`');

            $Tag = new Tag($app);
            $Tag->createTable();
            $app['monolog']->addInfo('[Contact Setup] Create table `contact_tag`');

            $Protocol = new Protocol($app);
            $Protocol->createTable();
            $app['monolog']->addInfo('[Contact Setup] Create table `contact_protocol`');

            // create the tables for extra fields
            $ExtraType = new ExtraType($app);
            $ExtraType->createTable();
            $app['monolog']->addInfo('[Contact Setup] Create table `contact_extra_type`');

            $ExtraCategory = new ExtraCategory($app);
            $ExtraCategory->createTable();
            $app['monolog']->addInfo('[Contact Setup] Create table `contact_extra_category`');

            $Extra = new Extra($app);
            $Extra->createTable();
            $app['monolog']->addInfo('[Contact Setup] Create table `contact_extra`');

            $Message = new Message($app);
            $Message->createTable();
            $app['monolog']->addInfo('[Contact Setup] Create table `contact_message`');

            $Form = new Form($app);
            $Form->createTable();
            $app['monolog']->addInfo('[Contact Setup] Create table `contact_form`');

            // Create Configuration - only constructor needed
            $Configuration = new Configuration($app);

            // setup kit_framework_contact as Add-on in the CMS
            $admin_tool = new InstallAdminTool($app);
            $admin_tool->exec(MANUFAKTUR_PATH.'/Contact/extension.json', '/contact/cms');

            // success - return message
            $app['monolog']->addInfo('[Contact Setup] The setup process was successfull');
            return $app['translator']->trans('Successfully installed the extension %extension%.',
                array('%extension%' => 'Contact'));
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}
