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
use phpManufaktur\Contact\Control\Pattern\Form\Contact as ContactForm;
use phpManufaktur\Basic\Control\kitCommand\Basic;
use phpManufaktur\Contact\Control\Configuration;
use phpManufaktur\Contact\Data\Contact\CategoryType;
use phpManufaktur\Contact\Data\Contact\TagType;
use Carbon\Carbon;

/**
 * Class ContactRegister
 * @package phpManufaktur\Contact\Control\Command
 */
class ContactRegister extends Basic
{
    protected $app = null;
    protected $ContactForm = null;
    protected static $contact_type = null;
    protected static $category_type_id = null;
    protected static $tags = null;
    protected static $config = null;
    protected static $parameter = null;

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Basic\Control\kitCommand\Basic::initParameters()
     */
    protected function initParameters(Application $app, $parameter_id=-1)
    {
        parent::initParameters($app, $parameter_id);

        self::$parameter = $this->getCommandParameters();

        // get the categoris from the parameter
        if (isset(self::$parameter['categories'])) {
            $CategoryType = new CategoryType($app);
            $cats = strpos(self::$parameter['categories'], ',') ? explode(',', self::$parameter['categories']) : array(self::$parameter['categories']);
            $category = array();
            foreach ($cats as $cat) {
                if ((false !== ($id = filter_var($cat, FILTER_VALIDATE_INT))) &&
                    (false !== ($type = $CategoryType->select($id))) &&
                    ($type['category_type_access'] == 'PUBLIC') &&
                    (!array_key_exists($type['category_type_id'], $category))) {
                    $category[$type['category_type_id']] = $app['utils']->humanize($type['category_type_name']);
                }
                elseif ((false !== ($type = $CategoryType->selectByName(trim($cat)))) &&
                        ($type['category_type_access'] == 'PUBLIC') &&
                        (!array_key_exists($type['category_type_id'], $category))) {
                    $category[$type['category_type_id']] = $app['utils']->humanize($type['category_type_name']);
                }
            }
            self::$parameter['categories'] = $category;
        }

        if (isset(self::$parameter['tags'])) {
            // get the tags from the parameter
            $TagType = new TagType($app);
            $tgs = strpos(self::$parameter['tags'], ',') ? explode(',', self::$parameter['tags']) : array(self::$parameter['tags']);
            $tags = array();
            foreach ($tgs as $tag) {
                if ((false !== ($id = filter_var($tag, FILTER_VALIDATE_INT))) &&
                    (false !== ($type = $TagType->select($id))) &&
                    !array_key_exists($type['tag_type_id'], $tags)) {
                    $tags[$type['tag_name']] = $app['utils']->humanize($type['tag_name']);
                }
                elseif ((false !== ($type = $TagType->selectByName(trim($tag)))) &&
                        !array_key_exists($type['tag_type_id'], $tags)) {
                    $tags[$type['tag_name']] = $app['utils']->humanize($type['tag_name']);
                }
            }
            self::$parameter['tags'] = $tags;
        }


        $request = $this->app['request']->get('form');
        if (isset($request['contact_type'])) {
            self::$contact_type = $request['contact_type'];
        }
        if (isset($request['category_type_id'])) {
            self::$category_type_id = $request['category_type_id'];
        }

        $Config = new Configuration($app);
        self::$config = $Config->getConfiguration();

        $this->ContactForm = new ContactForm($app);
    }

    /**
     * Send the activation link to the submitter
     *
     * @param integer $contact_id
     * @return boolean
     */
    protected function sendActivationLink($contact_id)
    {
        $contact = $this->app['contact']->selectOverview($contact_id);
        $allowed_roles = array('ROLE_USER', 'ROLE_CONTACT_EDIT_OWN');
        if (false === ($account = $this->app['account']->getUserData($contact['contact_login']))) {
            // user has no account - create it
            $this->app['account']->createAccount(
                $contact['contact_login'],
                $contact['communication_email'],
                $this->app['utils']->createPassword(),
                implode(',', $allowed_roles),
                $contact['order_name']
            );
        }
        else {
            // check if the new allowed roles are assigned to the user account
            $roles = explode(',', $account['roles']);
            foreach ($allowed_roles as $role) {
                if (!in_array($role, $roles)) {
                    $roles[] = $role;
                }
            }
            $data = array(
                'roles' => implode(',', $roles)
            );
            $this->app['account']->updateUserData($account['username'], $data);
        }

        // create a new GUID
        if (false === ($guid = $this->app['account']->createGUID($contact['communication_email'], false))) {
            $this->setAlert('Can not create GUID, submission aborted, please contact the webmaster.',
                array(), self::ALERT_TYPE_DANGER);
            return false;
        }

        // create the email body
        $body = $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Contact/Template', 'command/mail/user/register.contact.activate.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'contact' => $contact,
                'activation_link' => $this->getCMSpageURL().'?command=contact&action=confirm_register&guid='.$guid
            ));

        // send a email to the contact
        $message = \Swift_Message::newInstance()
        ->setSubject($this->app['translator']->trans('Register a contact'))
        ->setFrom(array(SERVER_EMAIL_ADDRESS => SERVER_EMAIL_NAME))
        ->setTo($contact['communication_email'])
        ->setBody($body)
        ->setContentType('text/html');
        // send the message
        $failedRecipients = null;
        if (!$this->app['mailer']->send($message, $failedRecipients))  {
            $this->setAlert("Can't send mail to %recipients%.", array(
                '%recipients%' => implode(',', $failedRecipients)), self::ALERT_TYPE_DANGER);
            return false;
        }
        return true;
    }

    /**
     * Controller to check the submitted contact data
     *
     * @param Application $app
     */
    public function ControllerContactCheck(Application $app)
    {
        $this->initParameters($app);

        $data = array(
            'contact_type' => self::$contact_type,
            'category_type_id' => self::$category_type_id,
        );
        $field = $this->getFieldArray();
        if (false === ($form = $this->ContactForm->getFormContact($data, $field))) {
            // something went wrong
            return $this->promptAlert();
        }

        if ('POST' == $this->app['request']->getMethod()) {
            // the form was submitted, bind the request
            $form->bind($this->app['request']);
            if ($form->isValid()) {
                // get the data from the form
                $data = $form->getData();

                $contact_id = $data['contact_id'];
                if ($contact_id < 1) {
                    // for new contacts set the status to 'PENDING'
                    $data['contact_status'] = 'PENDING';
                }

                if (false !== ($contact = $this->ContactForm->checkData($data))) {
                    if (($contact_id < 1) && ($contact['contact']['contact_id'] > 0) &&
                        ($contact['contact']['contact_status'] == 'PENDING')) {
                        // restore the current status for this contact!
                        $contact['contact']['contact_status'] = $this->app['contact']->getStatus($contact['contact']['contact_id']);
                    }

                    if (($contact['contact']['contact_id'] > 0) && ($contact['contact']['contact_status'] !== 'ACTIVE')) {
                        // this contact is not ACTIVE - reject the registering!
                        $this->setAlert('There exists already a contact record for you, but the status of this record is <strong>%status%</strong>. Please contact the webmaster to activate the existing record.',
                            array('%status%' => $this->app['translator']->trans($this->app['utils']->humanize($contact['contact']['contact_status']))),
                            self::ALERT_TYPE_WARNING);
                        return $this->promptAlert();
                    }

                    if ($contact['contact']['contact_type'] !== $data['contact_type']) {
                        // problem: the contact type differ!
                        $this->setAlert('There exists already a contact record for you, but this record is assigned to a <strong>%type%</strong> and can not be changed. Please use the same type or contact the webmaster.',
                            array('%type%' => $this->app['translator']->trans($this->app['utils']->humanize($contact['contact']['contact_type']))),
                            self::ALERT_TYPE_WARNING);
                        return $this->promptAlert();
                    }

                    if ($contact['contact']['contact_id'] > 0) {
                        // update existing record
                        $mode = 'UPDATE';
                        if (!$this->app['contact']->update($contact, $contact['contact']['contact_id'])) {
                            return $this->promptAlert();
                        }
                    }
                    else {
                        $mode = 'INSERT';
                        if (!$this->app['contact']->insert($contact, $contact['contact']['contact_id'])) {
                            return $this->promptAlert();
                        }
                        // send an activation link
                        $this->sendActivationLink($contact['contact']['contact_id']);
                    }

                    $data = $this->ContactForm->getData($contact['contact']['contact_id']);
                    return $this->app['twig']->render($this->app['utils']->getTemplateFile(
                        '@phpManufaktur/Contact/Template', 'command/register.contact.submitted.twig',
                        $this->getPreferredTemplateStyle()),
                        array(
                            'basic' => $this->getBasicSettings(),
                            'contact' => $data,
                            'mode' => $mode
                        ));
                }
            }
            else {
                // general error (timeout, CSFR ...)
                $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
            }
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Contact/Template', 'command/register.contact.data.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'form' => $form->createView()
            ));
    }

    /**
     * Check the response of the Category Select Dialog and call the
     * Contact Data Dialog as next step
     *
     * @param Application $app
     * @return string
     */
    public function ControllerCategoryCheck(Application $app)
    {
        $this->initParameters($app);

        //$form = $this->getFormSelectCategory();
        $form = $this->ContactForm->getFormContactCategory(
            self::$parameter['categories'],
            array('contact_type' => self::$contact_type));


        $form->bind($this->app['request']);
        if ($form->isValid()) {
            // get the form data
            $contact = $form->getData();
            self::$contact_type = $contact['contact_type'];
            self::$category_type_id = $contact['category_type_id'];
            // show the dialog for PERSON or COMPANY contacts
            return $this->registerContact();
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
            return $this->registerCategory();
        }
    }

    /**
     * Show the dialog to select a Contact Category.
     * If only one category is defined, redirect to the Contact Data Dialog
     *
     * @return string
     */
    protected function registerCategory()
    {
        if (count(self::$parameter['categories']) == 1) {
            // we have exacly one category - the key contains the ID ...
            reset(self::$parameter['categories']);
            self::$category_type_id = key(self::$parameter['categories']);
            return $this->registerContact();
        }

        $form = $this->ContactForm->getFormContactCategory(
            self::$parameter['categories'],
            array('contact_type' => self::$contact_type));

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Contact/Template', 'command/register.contact.category.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'form' => $form->createView()
            ));
    }

    /**
     * Get the field array for the form
     *
     * @return array
     */
    protected function getFieldArray()
    {
        // get the basic field definition
        $field = self::$config['command']['register']['field'];

        if (isset(self::$parameter['tags']) && is_array(self::$parameter['tags'])) {
            // set the available tags from the parameters
            $field['tags'] = array_keys(self::$parameter['tags']);
        }

        // set the available categories from the parameters
        $field['categories'] = array_keys(self::$parameter['categories']);

        return $field;
    }

    /**
     * Return the form to register a new contact
     *
     * @return \phpManufaktur\Basic\Control\Pattern\rendered
     */
    protected function registerContact()
    {
        $data = array(
            'contact_type' => self::$contact_type,
            'category_type_id' => self::$category_type_id,
        );
        $field = $this->getFieldArray();
        if (false === ($form = $this->ContactForm->getFormContact($data, $field))) {
            // something went wrong
            return $this->promptAlert();
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Contact/Template', 'command/register.contact.data.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'form' => $form->createView()
            ));
    }

    /**
     * Controller to start register a Public Contact, the first step is to
     * select the contact type - PERSON or COMPANY
     *
     * @param Application $app
     * @return string
     */
    public function ControllerType(Application $app)
    {
        $this->initParameters($app);

        if (empty(self::$parameter['categories'])) {
            $this->setAlert('Please use the parameter <em>categories[]</em> to specify at minimum one category with PUBLIC access!',
                array(), self::ALERT_TYPE_DANGER);
            return $this->promptAlert();
        }

        // create the form
        $form = $this->ContactForm->getFormContactType();

        if ('POST' == $this->app['request']->getMethod()) {
            // the form was submitted, bind the request
            $form->bind($this->app['request']);
            if ($form->isValid()) {
                // get the form data
                $contact = $form->getData();
                // show the dialog for PERSON or COMPANY contacts
                self::$contact_type = $contact['contact_type'];
                return $this->registerCategory();
            }
            else {
                // general error (timeout, CSFR ...)
                $this->setAlert('The form is not valid, please check your input and try again!', array(),
                    self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                        'method' => __METHOD__, 'line' => __LINE__));
            }
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Contact/Template', 'command/register.contact.type.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'form' => $form->createView()
            ));
    }

    /**
     * Controller to reject a contact record. The contact record will be deleted
     * and the submitter will receive an email.
     *
     * @param Application $app
     * @param string $guid
     * @return boolean
     */
    public function ControllerRegisterRejectAdmin(Application $app, $guid)
    {
        // don't use initParameters() of this class - we won't check parameters!
        parent::initParameters($app);

        if (false === ($account = $this->app['account']->getUserByGUID($guid))) {
            $this->setAlert('Invalid GUID, can not evaluate the desired account!',
                array(), self::ALERT_TYPE_DANGER);
            return $this->promptAlert();
        }

        if (false === ($contact = $this->app['contact']->selectOverview($account['email']))) {
            $this->setAlert('The GUID was valid but can not get the contact record desired to the account!',
                array(), self::ALERT_TYPE_DANGER);
            return $this->promptAlert();
        }

        $data = array(
            'contact' => array(
                'contact_id' => $contact['contact_id'],
                'contact_status' => 'DELETED'
            )
        );

        if (false === $this->app['contact']->update($data, $contact['contact_id'])) {
            return $this->promptAlert();
        }

        // create the email body
        $body = $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Contact/Template', 'command/mail/user/register.contact.rejected.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'contact' => $contact,
            ));

        // send a email to the contact
        $message = \Swift_Message::newInstance()
        ->setSubject($this->app['translator']->trans('Contact rejected'))
        ->setFrom(array(SERVER_EMAIL_ADDRESS => SERVER_EMAIL_NAME))
        ->setTo($contact['communication_email'])
        ->setBody($body)
        ->setContentType('text/html');
        // send the message
        $failedRecipients = null;
        if (!$this->app['mailer']->send($message, $failedRecipients))  {
            $this->setAlert("Can't send mail to %recipients%.", array(
                '%recipients%' => implode(',', $failedRecipients)), self::ALERT_TYPE_DANGER);
            return $this->promptAlert();
        }

        // clear all alerts from the contact interface
        $this->clearAlert();

        $this->setAlert('The contact was rejected and an email send to the submitter');
        return $this->promptAlert();
    }

    /**
     * Action to perform the publishing of the contact record - update record, send
     * mail to the submitter a.s.o.
     *
     * @param unknown $account
     * @param unknown $contact
     * @param string $published_by
     * @return \phpManufaktur\Basic\Control\Pattern\rendered
     */
    protected function publishContact($account, $contact, $published_by='user')
    {
        // activate the contact
        $data = array(
            'contact' => array(
                'contact_id' => $contact['contact_id'],
                'contact_status' => 'ACTIVE'
             )
        );
        if (!$this->app['contact']->update($data, $contact['contact_id'])) {
            return $this->promptAlert();
        }

        // create the email body
        $body = $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Contact/Template', 'command/mail/user/register.contact.published.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'contact' => $contact,
                'permalink' => FRAMEWORK_URL.'/contact/public/view/id/'.$contact['contact_id']
            ));

        // send a email to the contact
        $message = \Swift_Message::newInstance()
        ->setSubject($this->app['translator']->trans('Contact published'))
        ->setFrom(array(SERVER_EMAIL_ADDRESS => SERVER_EMAIL_NAME))
        ->setTo($contact['communication_email'])
        ->setBody($body)
        ->setContentType('text/html');
        // send the message
        $failedRecipients = null;
        if (!$this->app['mailer']->send($message, $failedRecipients))  {
            $this->setAlert("Can't send mail to %recipients%.", array(
                '%recipients%' => implode(',', $failedRecipients)), self::ALERT_TYPE_DANGER);
            return $this->promptAlert();
        }

        // clear all existing alerts
        $this->clearAlert();

        if ($published_by == 'user') {
            $this->setAlert('Your contact record is now published, we have send you a confirmation mail with further information.',
                array(), self::ALERT_TYPE_SUCCESS);
        }
        else {
            $this->setAlert('The contact record is now published, the submitter has received an email with further information.');
        }
        return $this->promptAlert();
    }

    /**
     * Controller for the Activation of a contact record by the admin
     *
     * @param Application $app
     * @param string $guid
     * @return \phpManufaktur\Basic\Control\Pattern\rendered
     */
    public function ControllerRegisterActivationAdmin(Application $app, $guid)
    {
        // don't use initParameters() of this class - we won't check parameters!
        parent::initParameters($app);

        if (false === ($account = $this->app['account']->getUserByGUID($guid))) {
            $this->setAlert('Invalid GUID, can not evaluate the desired account!',
                array(), self::ALERT_TYPE_DANGER);
            return $this->promptAlert();
        }

        if (false === ($contact = $this->app['contact']->selectOverview($account['email']))) {
            $this->setAlert('The GUID was valid but can not get the contact record desired to the account!',
                array(), self::ALERT_TYPE_DANGER);
            return $this->promptAlert();
        }

        return $this->publishContact($account, $contact, 'admin');
    }

    /**
     * Controller to check the Activation by GUID
     *
     * @param Application $app
     * @param string $guid
     */
    public function ControllerRegisterActivation(Application $app, $guid)
    {
        // don't use initParameters() of this class - we won't check parameters!
        parent::initParameters($app);

        if (false === ($account = $this->app['account']->getUserByGUID($guid))) {
            $this->setAlert('Invalid GUID, can not evaluate the desired account!',
                array(), self::ALERT_TYPE_DANGER);
            return $this->promptAlert();
        }

        if (false === ($contact = $this->app['contact']->selectOverview($account['email']))) {
            $this->setAlert('The GUID was valid but can not get the contact record desired to the account!',
                array(), self::ALERT_TYPE_DANGER);
            return $this->promptAlert();
        }

        $guid_datetime = Carbon::createFromFormat('Y-m-d H:i:s', $account['guid_timestamp']);
        $guid_datetime->addHours(24);
        if ($guid_datetime->lt(Carbon::now())) {
            // the GUID is expired
            $this->setAlert('The GUID was only valid for 24 hours and is expired, please contact the webmaster.',
                array(), self::ALERT_TYPE_DANGER);
            return $this->promptAlert();
        }

        $Config = new Configuration($app);
        self::$config = $Config->getConfiguration();

        if (strtolower(self::$config['command']['register']['publish']['activation']) == 'admin') {
            // the administrator must check and activate the contact record
            $guid = $this->app['account']->createGUID($account['email'], false);

            // create the email body
            $body = $this->app['twig']->render($this->app['utils']->getTemplateFile(
                '@phpManufaktur/Contact/Template', 'command/mail/admin/register.contact.check.twig',
                $this->getPreferredTemplateStyle()),
                array(
                    'basic' => $this->getBasicSettings(),
                    'contact' => $contact,
                    'publish_link' => $this->getCMSpageURL().'?command=contact&action=confirm_publish&guid='.$guid,
                    'reject_link' => $this->getCMSpageURL().'?command=contact&action=confirm_reject&guid='.$guid
                ));

            // send a email to the contact
            $message = \Swift_Message::newInstance()
            ->setSubject($this->app['translator']->trans('Publish a contact'))
            ->setFrom(array(SERVER_EMAIL_ADDRESS => SERVER_EMAIL_NAME))
            ->setTo(SERVER_EMAIL_ADDRESS)
            ->setReplyTo($contact['communication_email'])
            ->setBody($body)
            ->setContentType('text/html');
            // send the message
            $failedRecipients = null;
            if (!$this->app['mailer']->send($message, $failedRecipients))  {
                $this->setAlert("Can't send mail to %recipients%.", array(
                    '%recipients%' => implode(',', $failedRecipients)), self::ALERT_TYPE_DANGER);
                return $this->promptAlert();
            }

            // create the email body
            $body = $this->app['twig']->render($this->app['utils']->getTemplateFile(
                '@phpManufaktur/Contact/Template', 'command/mail/user/register.contact.pending.twig',
                $this->getPreferredTemplateStyle()),
                array(
                    'basic' => $this->getBasicSettings(),
                    'contact' => $contact,
                ));

            // send a email to the contact
            $message = \Swift_Message::newInstance()
            ->setSubject($this->app['translator']->trans('Contact pending'))
            ->setFrom(array(SERVER_EMAIL_ADDRESS => SERVER_EMAIL_NAME))
            ->setTo($contact['communication_email'])
            ->setBody($body)
            ->setContentType('text/html');
            // send the message
            $failedRecipients = null;
            if (!$this->app['mailer']->send($message, $failedRecipients))  {
                $this->setAlert("Can't send mail to %recipients%.", array(
                    '%recipients%' => implode(',', $failedRecipients)), self::ALERT_TYPE_DANGER);
                return $this->promptAlert();
            }

            $this->setAlert('The submitted contact record will be proofed and published as soon as possible, we will send you an email!',
                array(), self::ALERT_TYPE_SUCCESS);
            return $this->promptAlert();
        }
        elseif (strtolower(self::$config['command']['register']['publish']['activation']) == 'user') {
            // the user is allowed to activate the contact record
            return $this->publishContact($account, $contact);
        }
        else {
            // unknown activation value
            $this->setAlert("Don't understand the value %value% for the entry: command->register->publish->activate, please check the configuration!",
                array('%value%' => self::$config['command']['register']['publish']['activate']), self::ALERT_TYPE_DANGER);
            return $this->promptAlert();
        }
    }

}
