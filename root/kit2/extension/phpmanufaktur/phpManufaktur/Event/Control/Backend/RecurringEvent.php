<?php

/**
 * Event
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/Event
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Event\Control\Backend;

use phpManufaktur\Event\Control\Backend\Backend;
use Silex\Application;
use phpManufaktur\Event\Data\Event\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use phpManufaktur\Event\Data\Event\RecurringEvent as RecurringEventData;
use Carbon\Carbon;
use phpManufaktur\Event\Control\Configuration;

class RecurringEvent extends Backend {

    protected $EventData = null;
    protected $RecurringData = null;
    protected static $config = null;

    protected static $recurring_id = null;
    protected static $event_id = null;
    protected static $parent_event_id = null;
    protected static $recurring_type = null;
    protected static $day_type = null;
    protected static $day_sequence = null;
    protected static $week_sequence = null;
    protected static $week_day = null;
    protected static $month_type = null;
    protected static $month_sequence_day = null;
    protected static $month_sequence_month = null;
    protected static $month_pattern_type = null;
    protected static $month_pattern_day = null;
    protected static $month_pattern_sequence = null;
    protected static $year_type = null;
    protected static $year_repeat = null;
    protected static $year_sequence_day = null;
    protected static $year_sequence_month = null;
    protected static $year_pattern_type = null;
    protected static $year_pattern_day = null;
    protected static $year_pattern_month = null;
    protected static $recurring_date_end = null;
    protected static $exclude_dates = null;

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Event\Control\Backend\Backend::initialize()
     */
    protected function initialize(Application $app)
    {
        parent::initialize($app);

        $this->EventData = new Event($app);
        $this->RecurringData = new RecurringEventData($app);

        $Configuration = new Configuration($app);
        self::$config = $Configuration->getConfiguration();
    }

    /**
     * Get the form fields to select the recurring type a start
     *
     */
    protected function getSelectTypeFormFields()
    {
        return $this->app['form.factory']->createBuilder('form')
        ->add('recurring_id', 'hidden', array(
            'data' => self::$recurring_id
        ))
        ->add('event_id', 'hidden', array(
            'data' => self::$event_id
        ))
        ->add('parent_event_id', 'hidden', array(
            'data' => self::$parent_event_id
        ))
        ->add('recurring_type', 'choice', array(
            'choices' => array(
                'NONE' => $this->app['translator']->trans('No recurring event'),
                'DAY' => $this->app['translator']->trans('Daily recurring'),
                'WEEK' => $this->app['translator']->trans('Weekly recurring'),
                'MONTH' => $this->app['translator']->trans('Monthly recurring'),
                'YEAR' => $this->app['translator']->trans('Yearly recurring')
            ),
            'expanded' => true,
            'required' => true,
            'data' => !is_null(self::$recurring_type) ? self::$recurring_type : 'NONE'
        ));
    }

    /**
     * Get the form fields to select the recurring Day Type
     */
    protected function getSelectDayTypeFormFields()
    {
        return $this->app['form.factory']->createBuilder('form')
        ->add('recurring_id', 'hidden', array(
            'data' => self::$recurring_id
        ))
        ->add('event_id', 'hidden', array(
            'data' => self::$event_id
        ))
        ->add('parent_event_id', 'hidden', array(
            'data' => self::$parent_event_id
        ))
        ->add('recurring_type', 'hidden', array(
            'data' => self::$recurring_type
        ))
        ->add('day_type', 'choice', array(
            'choices' => array(
                'DAILY' => $this->app['translator']->trans('Repeat each x-days'),
                'WORKDAYS' => $this->app['translator']->trans('Repeat at workdays')
            ),
            'expanded' => true,
            'required' => true,
            'data' => !is_null(self::$day_type) ? self::$day_type : 'DAILY'
        ));
    }

    /**
     * Get the form fields to input the day sequence
     */
    protected function getSelectDaySequenceFormFields()
    {
        return $this->app['form.factory']->createBuilder('form')
        ->add('recurring_id', 'hidden', array(
            'data' => self::$recurring_id
        ))
        ->add('event_id', 'hidden', array(
            'data' => self::$event_id
        ))
        ->add('parent_event_id', 'hidden', array(
            'data' => self::$parent_event_id
        ))
        ->add('recurring_type', 'hidden', array(
            'data' => self::$recurring_type
        ))
        ->add('day_type', 'hidden', array(
            'data' => self::$day_type
        ))
        ->add('day_sequence', 'text', array(
            'required' => true,
            'attr' => array(
                'min' => 1,
                'max' => 31
            ),
            'data' => self::$day_sequence
        ));
    }

    /**
     * Get the form fields to input the week sequence
     */
    protected function getSelectWeekSequenceFormFields()
    {

        return $this->app['form.factory']->createBuilder('form')
        ->add('recurring_id', 'hidden', array(
            'data' => self::$recurring_id
        ))
        ->add('event_id', 'hidden', array(
            'data' => self::$event_id
        ))
        ->add('parent_event_id', 'hidden', array(
            'data' => self::$parent_event_id
        ))
        ->add('recurring_type', 'hidden', array(
            'data' => self::$recurring_type
        ))
        ->add('week_sequence', 'integer', array(
            'required' => true,
            'data' => !is_null(self::$week_sequence) ? self::$week_sequence : 1,
            'attr' => array(
                'min' => 1,
                'max' => 52
            )
        ))
        ->add('week_day_view', 'choice', array(
            'choices' => array(
                'MONDAY' => 'Monday',
                'TUESDAY' => 'Tuesday',
                'WEDNESDAY' => 'Wednesday',
                'THURSDAY' => 'Thursday',
                'FRIDAY' => 'Friday',
                'SATURDAY' => 'Saturday',
                'SUNDAY' => 'Sunday'
            ),
            'expanded' => false,
            'multiple' => false,
            'required' => true,
            'data' => !is_null(self::$week_day) ? self::$week_day : 'MONDAY',
            'disabled' => true
        ))
        ->add('week_day', 'hidden', array(
            'data' => !is_null(self::$week_day) ? self::$week_day : 'MONDAY'
        ))
        ;
    }

    /**
     * Get the form fields to select the monthly type
     *
     */
    protected function getSelectMonthTypeFormFields()
    {
        return $this->app['form.factory']->createBuilder('form')
        ->add('recurring_id', 'hidden', array(
            'data' => self::$recurring_id
        ))
        ->add('event_id', 'hidden', array(
            'data' => self::$event_id
        ))
        ->add('parent_event_id', 'hidden', array(
            'data' => self::$parent_event_id
        ))
        ->add('recurring_type', 'hidden', array(
            'data' => self::$recurring_type
        ))
        ->add('month_type', 'choice', array(
            'choices' => array(
                'SEQUENCE' => $this->app['translator']->trans('Repeat sequently at day x of month'),
                'PATTERN' => $this->app['translator']->trans('Repeat by pattern, i.e. at the last tuesday of the month'),
            ),
            'expanded' => true,
            'multiple' => false,
            'required' => true,
            'data' => !is_null(self::$month_type) ? self::$month_type : 'SEQUENCE'
        ))
        ;
    }

    /**
     * Get the form fields to select a monthly sequence
     */
    protected function getSelectMonthSequenceFormFields()
    {
        return $this->app['form.factory']->createBuilder('form')
        ->add('recurring_id', 'hidden', array(
            'data' => self::$recurring_id
        ))
        ->add('event_id', 'hidden', array(
            'data' => self::$event_id
        ))
        ->add('parent_event_id', 'hidden', array(
            'data' => self::$parent_event_id
        ))
        ->add('recurring_type', 'hidden', array(
            'data' => self::$recurring_type
        ))
        ->add('month_type', 'hidden', array(
            'data' => self::$month_type
        ))
        ->add('month_sequence_day', 'hidden', array(
            'data' => !is_null(self::$month_sequence_day) ? self::$month_sequence_day : 1
        ))
        ->add('month_sequence_day_view', 'integer', array(
            'required' => true,
            'data' => !is_null(self::$month_sequence_day) ? self::$month_sequence_day : 1,
            'attr' => array(
                'min' => 1,
                'max' => 31
            ),
            'disabled' => true
        ))
        ->add('month_sequence_month', 'integer', array(
            'required' => true,
            'data' => !is_null(self::$month_sequence_month) ? self::$month_sequence_month : 1,
            'attr' => array(
                'min' => 1,
                'max' => 12
            )
        ))
        ;
    }

    /**
     * Get the form fields to select a monthly PATTERN
     */
    protected function getSelectMonthPatternFormFields()
    {
        return $this->app['form.factory']->createBuilder('form')
        ->add('recurring_id', 'hidden', array(
            'data' => self::$recurring_id
        ))
        ->add('event_id', 'hidden', array(
            'data' => self::$event_id
        ))
        ->add('parent_event_id', 'hidden', array(
            'data' => self::$parent_event_id
        ))
        ->add('recurring_type', 'hidden', array(
            'data' => self::$recurring_type
        ))
        ->add('month_type', 'hidden', array(
            'data' => self::$month_type
        ))
        ->add('month_pattern_type', 'choice', array(
            'choices' => array(
                'FIRST' => $this->app['translator']->trans('At the first'),
                'SECOND' => $this->app['translator']->trans('At the second'),
                'THIRD' => $this->app['translator']->trans('At the third'),
                'FOURTH' => $this->app['translator']->trans('At the fourth'),
                'LAST' => $this->app['translator']->trans('At the last'),
                'FIRST_THIRD' => $this->app['translator']->trans('At the first and third'),
                'SECOND_FOURTH' => $this->app['translator']->trans('At the second and fourth'),
                'SECOND_LAST' => $this->app['translator']->trans('At the second and last')
            ),
            'empty_value' => '- please select -',
            'expanded' => false,
            'multiple' => false,
            'required' => true,
            'data' => !is_null(self::$month_pattern_type) ? self::$month_pattern_type : 'FIRST',
            'required' => true
        ))
        ->add('month_pattern_day', 'hidden', array(
            'data' => !is_null(self::$month_pattern_day) ? self::$month_pattern_day : 'MONDAY'
        ))
        ->add('month_pattern_day_view', 'choice', array(
            'choices' => array(
                'MONDAY' => 'Monday',
                'TUESDAY' => 'Tuesday',
                'WEDNESDAY' => 'Wednesday',
                'THURSDAY' => 'Thursday',
                'FRIDAY' => 'Friday',
                'SATURDAY' => 'Saturday',
                'SUNDAY' => 'Sunday'
            ),
            'empty_value' => '- please select -',
            'expanded' => false,
            'multiple' => false,
            'required' => true,
            'data' => !is_null(self::$month_pattern_day) ? self::$month_pattern_day : 'MONDAY',
            'required' => true,
            'disabled' => true
        ))
        ->add('month_pattern_sequence', 'integer', array(
            'required' => true,
            'data' => !is_null(self::$month_pattern_sequence) ? self::$month_pattern_sequence : 2,
            'attr' => array(
                'min' => 1,
                'max' => 11
            )
        ))
        ;
    }

    /**
     * Get the form fields for the YEAR TYPE
     */
    protected function getSelectYearTypeFormFields()
    {
        return $this->app['form.factory']->createBuilder('form')
        ->add('recurring_id', 'hidden', array(
            'data' => self::$recurring_id
        ))
        ->add('event_id', 'hidden', array(
            'data' => self::$event_id
        ))
        ->add('parent_event_id', 'hidden', array(
            'data' => self::$parent_event_id
        ))
        ->add('recurring_type', 'hidden', array(
            'data' => self::$recurring_type
        ))
        ->add('year_type', 'choice', array(
            'choices' => array(
                'SEQUENCE' => $this->app['translator']->trans('Repeat sequently at day x of month'),
                'PATTERN' => $this->app['translator']->trans('Repeat by pattern, i.e. at the last tuesday of the month'),
            ),
            'expanded' => true,
            'multiple' => false,
            'required' => true,
            'data' => !is_null(self::$year_type) ? self::$year_type : 'SEQUENCE'
        ))
        ;
    }

    /**
     * Get the form fields for the YEAR SEQUENCE
     */
    protected function getSelectYearSequenceFormFields()
    {
        return $this->app['form.factory']->createBuilder('form')
        ->add('recurring_id', 'hidden', array(
            'data' => self::$recurring_id
        ))
        ->add('event_id', 'hidden', array(
            'data' => self::$event_id
        ))
        ->add('parent_event_id', 'hidden', array(
            'data' => self::$parent_event_id
        ))
        ->add('recurring_type', 'hidden', array(
            'data' => self::$recurring_type
        ))
        ->add('year_type', 'hidden', array(
            'data' => self::$year_type
        ))
        ->add('year_repeat', 'integer', array(
            'required' => true,
            'data' => !is_null(self::$year_repeat) ? self::$year_repeat : 1,
            'attr' => array(
                'min' => 1,
                'max' => 10
            )
        ))
        ->add('year_sequence_day', 'hidden', array(
            'data' => !is_null(self::$year_sequence_day) ? self::$year_sequence_day : 1
        ))
        ->add('year_sequence_day_view', 'integer', array(
            'required' => true,
            'data' => !is_null(self::$year_sequence_day) ? self::$year_sequence_day : 1,
            'attr' => array(
                'min' => 1,
                'max' => 31
            ),
            'disabled' => true
        ))
        ->add('year_sequence_month', 'hidden', array(
            'data' => !is_null(self::$year_sequence_month) ? self::$year_sequence_month : 'JANUARY'
        ))
        ->add('year_sequence_month_view', 'choice', array(
            'choices' => array(
                'JANUARY' => 'January',
                'FEBRUARY' => 'February',
                'MARCH' => 'March',
                'APRIL' => 'April',
                'MAY' => 'May',
                'JUNE' => 'June',
                'JULY' => 'July',
                'AUGUST' => 'August',
                'SEPTEMBER' => 'September',
                'OCTOBER' => 'October',
                'NOVEMBER' => 'November',
                'DECEMBER' => 'December'
            ),
            'empty_value' => '- please select -',
            'expanded' => false,
            'multiple' => false,
            'required' => true,
            'data' => !is_null(self::$year_sequence_month) ? self::$year_sequence_month : 'JANUARY',
            'disabled' => true
        ))
        ;
    }

    /**
     * Get the form fields for the YEAR PATTERN
     */
    protected function getSelectYearPatternFormFields()
    {
        return $this->app['form.factory']->createBuilder('form')
        ->add('recurring_id', 'hidden', array(
            'data' => self::$recurring_id
        ))
        ->add('event_id', 'hidden', array(
            'data' => self::$event_id
        ))
        ->add('parent_event_id', 'hidden', array(
            'data' => self::$parent_event_id
        ))
        ->add('recurring_type', 'hidden', array(
            'data' => self::$recurring_type
        ))
        ->add('year_type', 'hidden', array(
            'data' => self::$year_type
        ))
        ->add('year_repeat', 'text', array(
            'required' => true,
            'data' => !is_null(self::$year_repeat) ? self::$year_repeat : 1,
            'attr' => array(
                'min' => 1,
                'max' => 10
            )
        ))
        ->add('year_pattern_type', 'choice', array(
            'choices' => array(
                'FIRST' => $this->app['translator']->trans('At the first'),
                'SECOND' => $this->app['translator']->trans('At the second'),
                'THIRD' => $this->app['translator']->trans('At the third'),
                'FOURTH' => $this->app['translator']->trans('At the fourth'),
                'LAST' => $this->app['translator']->trans('At the last')
            ),
            'empty_value' => '- please select -',
            'expanded' => false,
            'multiple' => false,
            'required' => true,
            'data' => !is_null(self::$year_pattern_type) ? self::$year_pattern_type : 'FIRST',
            'required' => true
        ))
        ->add('year_pattern_day', 'hidden', array(
            'data' => !is_null(self::$year_pattern_day) ? self::$year_pattern_day : 'MONDAY'
        ))
        ->add('year_pattern_day_view', 'choice', array(
            'choices' => array(
                'MONDAY' => 'Monday',
                'TUESDAY' => 'Tuesday',
                'WEDNESDAY' => 'Wednesday',
                'THURSDAY' => 'Thursday',
                'FRIDAY' => 'Friday',
                'SATURDAY' => 'Saturday',
                'SUNDAY' => 'Sunday'
            ),
            'empty_value' => '- please select -',
            'expanded' => false,
            'multiple' => false,
            'required' => true,
            'data' => !is_null(self::$year_pattern_day) ? self::$year_pattern_day : 'MONDAY',
            'required' => true,
            'disabled' => true
        ))
        ->add('year_pattern_month', 'hidden', array(
            'data' => !is_null(self::$year_pattern_month) ? self::$year_pattern_month : 'JANUARY'
        ))
        ->add('year_pattern_month_view', 'choice', array(
            'choices' => array(
                'JANUARY' => 'January',
                'FEBRUARY' => 'February',
                'MARCH' => 'March',
                'APRIL' => 'April',
                'MAY' => 'May',
                'JUNE' => 'June',
                'JULY' => 'July',
                'AUGUST' => 'August',
                'SEPTEMBER' => 'September',
                'OCTOBER' => 'October',
                'NOVEMBER' => 'November',
                'DECEMBER' => 'December'
            ),
            'empty_value' => '- please select -',
            'expanded' => false,
            'multiple' => false,
            'required' => true,
            'data' => !is_null(self::$year_pattern_month) ? self::$year_pattern_month : 'JANUARY',
            'disabled' => true
        ))
        ;
    }

    /**
     * Get the form fields to FINISH the RECURRING
     *
     */
    protected function getSelectRecurringDateEndFormFields()
    {

        return $this->app['form.factory']->createBuilder('form')
        ->add('recurring_id', 'hidden', array(
            'data' => self::$recurring_id
        ))
        ->add('event_id', 'hidden', array(
            'data' => self::$event_id
        ))
        ->add('parent_event_id', 'hidden', array(
            'data' => self::$parent_event_id
        ))
        ->add('recurring_type', 'hidden', array(
            'data' => self::$recurring_type
        ))
        ->add('day_type', 'hidden', array(
            'data' => self::$day_type
        ))
        ->add('day_sequence', 'hidden', array(
            'data' => self::$day_sequence
        ))
        ->add('week_sequence', 'hidden', array(
            'data' => self::$week_sequence
        ))
        ->add('week_day', 'hidden', array(
            'data' => self::$week_day
        ))
        ->add('month_type', 'hidden', array(
            'data' => self::$month_type
        ))
        ->add('month_sequence_day', 'hidden', array(
            'data' => self::$month_sequence_day
        ))
        ->add('month_sequence_month', 'hidden', array(
            'data' => self::$month_sequence_month
        ))
        ->add('month_pattern_type', 'hidden', array(
            'data' => self::$month_pattern_type
        ))
        ->add('month_pattern_day', 'hidden', array(
            'data' => self::$month_pattern_day
        ))
        ->add('month_pattern_sequence', 'hidden', array(
            'data' => self::$month_pattern_sequence
        ))
        ->add('year_repeat', 'hidden', array(
            'data' => self::$year_repeat
        ))
        ->add('year_type', 'hidden', array(
            'data' => self::$year_type
        ))
        ->add('year_sequence_day', 'hidden', array(
            'data' => self::$year_sequence_day
        ))
        ->add('year_sequence_month', 'hidden', array(
            'data' => self::$year_sequence_month
        ))
        ->add('year_pattern_type', 'hidden', array(
            'data' => self::$year_pattern_type
        ))
        ->add('year_pattern_day', 'hidden', array(
            'data' => self::$year_pattern_day
        ))
        ->add('year_pattern_month', 'hidden', array(
            'data' => self::$year_pattern_month
        ))
        ->add('recurring_date_end', 'text', array(
            'label' => 'Recurring date end',
            'required' => true,
            'data' => self::$recurring_date_end
        ))
        ->add('exclude_dates', 'textarea', array(
            'required' => false,
            'data' => self::$exclude_dates,
            'attr' => array(
                'help' => $this->app['translator']->trans(
                    'List single dates in format <b>%format%</b> separated by comma to exclude them from recurring',
                    array('%format%' => $this->app['translator']->trans('DATE_FORMAT')))
            )
        ))
        ;
    }

    /**
     * Select the Day Type
     *
     * @return string
     */
    protected function selectDayType()
    {
        if (false !== ($recurring = $this->RecurringData->select(self::$recurring_id))) {
            self::$day_type = $recurring['day_type'];
        }

        $fields = $this->getSelectDayTypeFormFields();
        $form = $fields->getForm();

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template', 'admin/recurring.select.twig'),
            array(
                'usage' => self::$usage,
                'alert' => $this->getAlert(),
                'toolbar' => $this->getToolbar('event_edit'),
                'form' => $form->createView(),
                'header' => $this->app['translator']->trans('Daily recurring'),
                'action' => FRAMEWORK_URL.'/admin/event/recurring/check/day/type?usage='.self::$usage,
                'abort' => FRAMEWORK_URL.'/admin/event/edit/id/'.self::$event_id.'?usage='.self::$usage
            ));
    }

    /**
     * Select the Day Sequence
     */
    protected function selectDaySequence()
    {
        if (false !== ($recurring = $this->RecurringData->select(self::$recurring_id))) {
            self::$day_sequence = $recurring['day_sequence'];
        }

        $fields = $this->getSelectDaySequenceFormFields();
        $form = $fields->getForm();

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template', 'admin/recurring.select.twig'),
            array(
                'usage' => self::$usage,
                'alert' => $this->getAlert(),
                'toolbar' => $this->getToolbar('event_edit'),
                'form' => $form->createView(),
                'header' => $this->app['translator']->trans('Daily recurring'),
                'action' => FRAMEWORK_URL.'/admin/event/recurring/check/day/sequence?usage='.self::$usage,
                'abort' => FRAMEWORK_URL.'/admin/event/edit/id/'.self::$event_id.'?usage='.self::$usage
            ));
    }

    /**
     * Controller to check the selected Day Type
     *
     * @param Application $app
     * @return string
     */
    public function ControllerCheckDayType(Application $app)
    {
        $this->initialize($app);

        $fields = $this->getSelectDayTypeFormFields();
        $form = $fields->getForm();
        $form->bind($app['request']);

        if ($form->isValid()) {
            $data = $form->getData();
            self::$recurring_id = $data['recurring_id'];
            self::$event_id = $data['event_id'];
            self::$parent_event_id = $data['parent_event_id'];
            self::$recurring_type = $data['recurring_type'];
            self::$day_type = $data['day_type'];

            if (self::$day_type == 'WORKDAYS') {
                // save the recurring event
                return $this->selectRecurringDateEnd();
            }
            else {
                // get the daily sequence
                return $this->selectDaySequence();
            }
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
            return $this->selectDayType($app);
        }

    }

    /**
     * Controller to check the Day Sequence
     *
     * @param Application $app
     * @return string
     */
    public function ControllerCheckDaySequence(Application $app)
    {
        $this->initialize($app);

        $fields = $this->getSelectDaySequenceFormFields();
        $form = $fields->getForm();
        $form->bind($app['request']);

        if ($form->isValid()) {
            $data = $form->getData();
            self::$recurring_id = $data['recurring_id'];
            self::$event_id = $data['event_id'];
            self::$parent_event_id = $data['parent_event_id'];
            self::$recurring_type = $data['recurring_type'];
            self::$day_type = $data['day_type'];
            self::$day_sequence = (int) $data['day_sequence'];
            if (self::$day_sequence < 1) {
                $this->setAlert('The daily sequence must be greater than zero!', array(), self::ALERT_TYPE_WARNING);
                return $this->selectDaySequence();
            }
            // finish the selection
            return $this->selectRecurringDateEnd();
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
            return $this->selectDaySequence();
        }
    }

    /**
     * Select the week sequence
     */
    protected function selectWeekSequence()
    {
        if (false !== ($recurring = $this->RecurringData->select(self::$recurring_id))) {
            self::$week_sequence = $recurring['week_sequence'];
        }

        // get the weekday from the parent event
        $event = $this->EventData->selectEvent(self::$parent_event_id);
        $day = Carbon::createFromFormat('Y-m-d H:i:s', $event['event_date_from']);
        self::$week_day = $this->RecurringData->getDayOfWeekString($day->dayOfWeek);

        $fields = $this->getSelectWeekSequenceFormFields();
        $form = $fields->getForm();

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template', 'admin/recurring.select.twig'),
            array(
                'usage' => self::$usage,
                'alert' => $this->getAlert(),
                'toolbar' => $this->getToolbar('event_edit'),
                'form' => $form->createView(),
                'header' => $this->app['translator']->trans('Weekly recurring'),
                'action' => FRAMEWORK_URL.'/admin/event/recurring/check/week/sequence?usage='.self::$usage,
                'abort' => FRAMEWORK_URL.'/admin/event/edit/id/'.self::$event_id.'?usage='.self::$usage
            ));
    }

    /**
     * Controller to check the Weekly Sequence
     *
     * @param Application $app
     * @return string
     */
    public function ControllerCheckWeekSequence(Application $app)
    {
        $this->initialize($app);

        $fields = $this->getSelectWeekSequenceFormFields();
        $form = $fields->getForm();
        $form->bind($app['request']);

        if ($form->isValid()) {
            $data = $form->getData();
            self::$recurring_id = $data['recurring_id'];
            self::$event_id = $data['event_id'];
            self::$parent_event_id = $data['parent_event_id'];
            self::$recurring_type = $data['recurring_type'];
            self::$week_sequence = (int) $data['week_sequence'];
            self::$week_day = $data['week_day'];
            if (self::$week_sequence < 1) {
                $this->setAlert('The weekly sequence must be greater than zero!', array(), self::ALERT_TYPE_WARNING);
                return $this->selectWeekSequence();
            }
            if (empty(self::$week_day)) {
                $this->setAlert('Please select at least one weekday!', array(), self::ALERT_TYPE_WARNING);
                return $this->selectWeekSequence();
            }
            // save the recurring event
            return $this->selectRecurringDateEnd();
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
            return $this->selectWeekSequence();
        }
    }

    /**
     * Select the month type
     *
     */
    protected function selectMonthType()
    {
        if (false !== ($recurring = $this->RecurringData->select(self::$recurring_id))) {
            self::$month_type = $recurring['month_type'];
        }

        $fields = $this->getSelectMonthTypeFormFields();
        $form = $fields->getForm();

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template', 'admin/recurring.select.twig'),
            array(
                'usage' => self::$usage,
                'alert' => $this->getAlert(),
                'toolbar' => $this->getToolbar('event_edit'),
                'form' => $form->createView(),
                'header' => $this->app['translator']->trans('Monthly recurring'),
                'action' => FRAMEWORK_URL.'/admin/event/recurring/check/month/type?usage='.self::$usage,
                'abort' => FRAMEWORK_URL.'/admin/event/edit/id/'.self::$event_id.'?usage='.self::$usage
            ));
    }

    /**
     * Select the monthly sequence
     */
    protected function selectMonthSequence()
    {
        if (false !== ($recurring = $this->RecurringData->select(self::$recurring_id))) {
            self::$month_sequence_day = $recurring['month_sequence_day'];
        }

        $event = $this->EventData->selectEvent(self::$parent_event_id);
        $day = Carbon::createFromFormat('Y-m-d H:i:s', $event['event_date_from']);
        self::$month_sequence_day = $day->day;

        $fields = $this->getSelectMonthSequenceFormFields();
        $form = $fields->getForm();

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template', 'admin/recurring.select.twig'),
            array(
                'usage' => self::$usage,
                'alert' => $this->getAlert(),
                'toolbar' => $this->getToolbar('event_edit'),
                'form' => $form->createView(),
                'header' => $this->app['translator']->trans('Monthly recurring'),
                'action' => FRAMEWORK_URL.'/admin/event/recurring/check/month/sequence?usage='.self::$usage,
                'abort' => FRAMEWORK_URL.'/admin/event/edit/id/'.self::$event_id.'?usage='.self::$usage
            ));
    }

    /**
     * Select a PATTERN for the month
     */
    protected function selectMonthPattern()
    {
        if (false !== ($recurring = $this->RecurringData->select(self::$recurring_id))) {
            self::$month_pattern_sequence = $recurring['month_pattern_sequence'];
            self::$month_pattern_type = $recurring['month_pattern_type'];
        }

        $event = $this->EventData->selectEvent(self::$parent_event_id);
        $day = Carbon::createFromFormat('Y-m-d H:i:s', $event['event_date_from']);
        self::$month_pattern_day = $this->RecurringData->getDayOfWeekString($day->dayOfWeek);

        $fields = $this->getSelectMonthPatternFormFields();
        $form = $fields->getForm();

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template', 'admin/recurring.select.twig'),
            array(
                'usage' => self::$usage,
                'alert' => $this->getAlert(),
                'toolbar' => $this->getToolbar('event_edit'),
                'form' => $form->createView(),
                'header' => $this->app['translator']->trans('Monthly recurring'),
                'action' => FRAMEWORK_URL.'/admin/event/recurring/check/month/pattern?usage='.self::$usage,
                'abort' => FRAMEWORK_URL.'/admin/event/edit/id/'.self::$event_id.'?usage='.self::$usage
            ));
    }

    /**
     * Controller to check the monthly PATTERN
     *
     * @param Application $app
     * @return string
     */
    public function ControllerCheckMonthPattern(Application $app)
    {
        $this->initialize($app);

        $fields = $this->getSelectMonthPatternFormFields();
        $form = $fields->getForm();
        $form->bind($app['request']);

        if ($form->isValid()) {
            $data = $form->getData();
            self::$recurring_id = $data['recurring_id'];
            self::$event_id = $data['event_id'];
            self::$parent_event_id = $data['parent_event_id'];
            self::$recurring_type = $data['recurring_type'];
            self::$month_type = $data['month_type'];
            self::$month_pattern_type = $data['month_pattern_type'];
            self::$month_pattern_day = $data['month_pattern_day'];
            self::$month_pattern_sequence = (int) $data['month_pattern_sequence'];

            if ((self::$month_pattern_sequence < 1) || (self::$month_pattern_sequence > 12)) {
                $this->setAlert('Repeat x-month must be greater than zero and less then 13.',
                    array(), self::ALERT_TYPE_WARNING);
                return $this->selectMonthPattern();
            }
            // save the recurring event
            return $this->selectRecurringDateEnd();
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
            return $this->selectMonthPattern();
        }
    }

    /**
     * Controller to check the monthly SEQUENCE
     *
     * @param Application $app
     * @return string
     */
    public function ControllerCheckMonthSequence(Application $app)
    {
        $this->initialize($app);

        $fields = $this->getSelectMonthSequenceFormFields();
        $form = $fields->getForm();
        $form->bind($app['request']);

        if ($form->isValid()) {
            $data = $form->getData();
            self::$recurring_id = $data['recurring_id'];
            self::$event_id = $data['event_id'];
            self::$parent_event_id = $data['parent_event_id'];
            self::$recurring_type = $data['recurring_type'];
            self::$month_type = $data['month_type'];
            self::$month_sequence_day = (int) $data['month_sequence_day'];
            self::$month_sequence_month = (int) $data['month_sequence_month'];

            if ((self::$month_sequence_day < 1) || (self::$month_sequence_day > 28)) {
                $this->setAlert('At day x of month must be greater than zero and less than 28.',
                    array(), self::ALERT_TYPE_WARNING);
                return $this->selectMonthSequence();
            }

            if ((self::$month_sequence_month < 1) || (self::$month_sequence_month > 12)) {
                $this->setAlert('Repeat x-month must be greater than zero and less then 13.',
                    array(), self::ALERT_TYPE_WARNING);
                return $this->selectMonthSequence();
            }
            // save the recurring event
            return $this->selectRecurringDateEnd();
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
            return $this->selectMonthSequence();
        }
    }

    /**
     * Controller to check the Month TYPE
     *
     * @param Application $app
     * @return string
     */
    public function ControllerCheckMonthType(Application $app)
    {
        $this->initialize($app);

        $fields = $this->getSelectMonthTypeFormFields();
        $form = $fields->getForm();
        $form->bind($app['request']);

        if ($form->isValid()) {
            $data = $form->getData();
            self::$recurring_id = $data['recurring_id'];
            self::$event_id = $data['event_id'];
            self::$parent_event_id = $data['parent_event_id'];
            self::$recurring_type = $data['recurring_type'];
            self::$month_type = $data['month_type'];

            switch (self::$month_type) {
                case 'SEQUENCE':
                    return $this->selectMonthSequence();
                case 'PATTERN':
                    return $this->selectMonthPattern();
                default:
                    $this->setAlert("Don't know how to handle the month type %type%",
                        array('%type%' => self::$month_type), self::ALERT_TYPE_DANGER);
                    return $this->selectMonthType();
            }
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
            return $this->selectMonthType();
        }
    }

    /**
     * Select the type for the yearly recurring
     *
     */
    protected function selectYearType()
    {
        if (false !== ($recurring = $this->RecurringData->select(self::$recurring_id))) {
            self::$year_type = $recurring['year_type'];
        }

        $fields = $this->getSelectYearTypeFormFields();
        $form = $fields->getForm();

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template', 'admin/recurring.select.twig'),
            array(
                'usage' => self::$usage,
                'alert' => $this->getAlert(),
                'toolbar' => $this->getToolbar('event_edit'),
                'form' => $form->createView(),
                'header' => $this->app['translator']->trans('Yearly recurring'),
                'action' => FRAMEWORK_URL.'/admin/event/recurring/check/year/type?usage='.self::$usage,
                'abort' => FRAMEWORK_URL.'/admin/event/edit/id/'.self::$event_id.'?usage='.self::$usage
            ));
    }

    /**
     * Controller to check the YEAR TYPE
     *
     * @param Application $app
     */
    public function ControllerCheckYearType(Application $app)
    {
        $this->initialize($app);

        $fields = $this->getSelectYearTypeFormFields();
        $form = $fields->getForm();
        $form->bind($app['request']);

        if ($form->isValid()) {
            $data = $form->getData();
            self::$recurring_id = $data['recurring_id'];
            self::$event_id = $data['event_id'];
            self::$parent_event_id = $data['parent_event_id'];
            self::$recurring_type = $data['recurring_type'];
            self::$year_type = $data['year_type'];
            switch (self::$year_type) {
                case 'SEQUENCE':
                    return $this->selectYearSequence();
                case 'PATTERN':
                    return $this->selectYearPattern();
                default:
                    $this->setAlert("Don't know how to handle the year type %type%",
                        array('%type%' => self::$year_type), self::ALERT_TYPE_DANGER);
                    return $this->selectYearType();
            }
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
            return $this->selectYearType();
        }
    }

    /**
     * Select the YEAR SEQUENCE
     *
     * @return string
     */
    protected function selectYearSequence()
    {

        if (false !== ($recurring = $this->RecurringData->select(self::$recurring_id))) {
            self::$year_repeat = $recurring['year_repeat'];
        }

        // select the event data record
        $event = $this->EventData->selectEvent(self::$parent_event_id);
        $day = Carbon::createFromFormat('Y-m-d H:i:s', $event['event_date_from']);
        self::$year_sequence_day = $day->day;
        self::$year_sequence_month = strtoupper($day->format('F'));

        $fields = $this->getSelectYearSequenceFormFields();
        $form = $fields->getForm();

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template', 'admin/recurring.select.twig'),
            array(
                'usage' => self::$usage,
                'alert' => $this->getAlert(),
                'toolbar' => $this->getToolbar('event_edit'),
                'form' => $form->createView(),
                'header' => $this->app['translator']->trans('Yearly recurring'),
                'action' => FRAMEWORK_URL.'/admin/event/recurring/check/year/sequence?usage='.self::$usage,
                'abort' => FRAMEWORK_URL.'/admin/event/edit/id/'.self::$event_id.'?usage='.self::$usage
            ));
    }

    /**
     * Controller to check the YEAR SEQUENCE
     *
     * @param Application $app
     * @return string
     */
    public function ControllerCheckYearSequence(Application $app)
    {
        $this->initialize($app);

        $fields = $this->getSelectYearSequenceFormFields();
        $form = $fields->getForm();
        $form->bind($app['request']);

        if ($form->isValid()) {
            $data = $form->getData();
            self::$recurring_id = $data['recurring_id'];
            self::$event_id = $data['event_id'];
            self::$parent_event_id = $data['parent_event_id'];
            self::$recurring_type = $data['recurring_type'];
            self::$year_type = $data['year_type'];
            self::$year_sequence_day = $data['year_sequence_day'];
            self::$year_sequence_month = $data['year_sequence_month'];
            self::$year_repeat = $data['year_repeat'];

            if ((self::$year_repeat < 1) || (self::$year_repeat > 10)) {
                $this->setAlert('The repeat each x-year sequence must be greater than zero and less than 10!',
                    array(), self::ALERT_TYPE_WARNING);
                return $this->selectYearSequence();
            }

            // save the recurring event
            return $this->selectRecurringDateEnd();
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
            return $this->selectYearSequence();
        }
    }

    /**
     * Select the YEAR PATTERN
     *
     */
    protected function selectYearPattern()
    {
        if (false !== ($recurring = $this->RecurringData->select(self::$recurring_id))) {
            self::$year_repeat = $recurring['year_repeat'];
            self::$year_pattern_type = $recurring['year_pattern_type'];
        }

        // select the event data record
        $event = $this->EventData->selectEvent(self::$parent_event_id);
        $day = Carbon::createFromFormat('Y-m-d H:i:s', $event['event_date_from']);

        self::$year_pattern_day = strtoupper($day->format('l'));
        self::$year_pattern_month = strtoupper($day->format('F'));

        $fields = $this->getSelectYearPatternFormFields();
        $form = $fields->getForm();

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template', 'admin/recurring.select.twig'),
            array(
                'usage' => self::$usage,
                'alert' => $this->getAlert(),
                'toolbar' => $this->getToolbar('event_edit'),
                'form' => $form->createView(),
                'header' => $this->app['translator']->trans('Yearly recurring'),
                'action' => FRAMEWORK_URL.'/admin/event/recurring/check/year/pattern?usage='.self::$usage,
                'abort' => FRAMEWORK_URL.'/admin/event/edit/id/'.self::$event_id.'?usage='.self::$usage
            ));
    }

    /**
     * Controller to check the YEAR PATTERN
     *
     * @param Application $app
     * @return string
     */
    public function ControllerCheckYearPattern(Application $app)
    {
        $this->initialize($app);

        $fields = $this->getSelectYearPatternFormFields();
        $form = $fields->getForm();
        $form->bind($app['request']);

        if ($form->isValid()) {
            $data = $form->getData();
            self::$recurring_id = $data['recurring_id'];
            self::$event_id = $data['event_id'];
            self::$parent_event_id = $data['parent_event_id'];
            self::$recurring_type = $data['recurring_type'];
            self::$year_type = $data['year_type'];
            self::$year_pattern_day = $data['year_pattern_day'];
            self::$year_pattern_month = $data['year_pattern_month'];
            self::$year_pattern_type = $data['year_pattern_type'];
            self::$year_repeat = $data['year_repeat'];

            if ((self::$year_repeat < 1) || (self::$year_repeat > 10)) {
                $this->setAlert('The repeat each x-year sequence must be greater than zero and less than 10!',
                    array(), self::ALERT_TYPE_WARNING);
                return $this->selectYearPattern();
            }

            // save the recurring event
            return $this->selectRecurringDateEnd();
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
            return $this->selectYearPattern();
        }
    }

    /**
     * Get the end date of the recurring event ...
     *
     */
    protected function selectRecurringDateEnd()
    {
        if (false !== ($recurring = $this->RecurringData->select(self::$recurring_id))) {
            $dt = Carbon::createFromFormat('Y-m-d', $recurring['recurring_date_end']);
            self::$recurring_date_end = $dt->format($this->app['translator']->trans('DATE_FORMAT'));
            if (!empty($recurring['exclude_dates'])) {
                $dates = explode(',', $recurring['exclude_dates']);
                $exclude_dates = array();
                foreach ($dates as $date) {
                    $dt = Carbon::createFromFormat('Y-m-d', $date);
                    $exclude_dates[] = $dt->format($this->app['translator']->trans('DATE_FORMAT'));
                }
                self::$exclude_dates = implode(', ', $exclude_dates);
            }
        }

        $fields = $this->getSelectRecurringDateEndFormFields();
        $form = $fields->getForm();

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template', 'admin/recurring.select.twig'),
            array(
                'usage' => self::$usage,
                'alert' => $this->getAlert(),
                'toolbar' => $this->getToolbar('event_edit'),
                'form' => $form->createView(),
                'header' => $this->app['translator']->trans('Recurring date end'),
                'action' => FRAMEWORK_URL.'/admin/event/recurring/check/date/end?usage='.self::$usage,
                'abort' => FRAMEWORK_URL.'/admin/event/edit/id/'.self::$event_id.'?usage='.self::$usage,
                'iframe_add_height' => 100
            ));
    }

    /**
     * This controller finish the recurring definition
     *
     * @param Application $app
     * @return string
     */
    public function ControllerCheckRecurringDateEnd(Application $app)
    {
        $this->initialize($app);

        $fields = $this->getSelectRecurringDateEndFormFields();
        $form = $fields->getForm();
        $form->bind($app['request']);

        if ($form->isValid()) {
            // get all data from the form
            $data = $form->getData();

            self::$event_id = $data['event_id'];
            self::$parent_event_id = $data['parent_event_id'];

            // convert the localized date to standard
            $dt = Carbon::createFromFormat($this->app['translator']->trans('DATE_FORMAT'), $data['recurring_date_end']);
            $data['recurring_date_end'] = $dt->toDateString();

            if (!empty($data['exclude_dates'])) {
                if (strpos($data['exclude_dates'], ',')) {
                    $items = explode(',', $data['exclude_dates']);
                    $dates = array();
                    foreach ($items as $item) {
                        $item = str_replace(' ', '', $item);
                        $dt = Carbon::createFromFormat($this->app['translator']->trans('DATE_FORMAT'), trim($item));
                        if ($dt->year < 2000) {
                            $dt->year($dt->year + 2000);
                        }
                        $dates[] = $dt->toDateString();
                    }
                    sort($dates);
                }
                else {
                    $data['exclude_dates'] = str_replace(' ', '', $data['exclude_dates']);
                    $dt = Carbon::createFromFormat($this->app['translator']->trans('DATE_FORMAT'), trim($data['exclude_dates']));
                    if ($dt->year < 2000) {
                            $dt->year($dt->year + 2000);
                        }
                    $dates = array($dt->toDateString());
                }
                $data['exclude_dates'] = implode(',', $dates);
            }

            if ($data['recurring_id'] < 1) {
                // insert a new record
                self::$recurring_id = $this->RecurringData->insert($data);
                $this->setAlert('Successfull inserted a recurring event');
                // create the recurring events
                $this->createRecurringEvent();
            }
            elseif ($this->RecurringData->hasChanged($data['recurring_id'], $data)) {
                // the existing data has changed - delete the old recurring records
                self::$recurring_id = $data['recurring_id'];
                $this->deleteRecurringEvent();
                self::$recurring_id = $this->RecurringData->insert($data);
                $this->setAlert('Rewrite the the recurring event', array(), self::ALERT_TYPE_INFO);
                $this->createRecurringEvent();
            }
            else {
                $this->setAlert('The recurring event was not changed.', array(), self::ALERT_TYPE_INFO);
            }

            // return to the event editing
            if (self::$event_id != self::$parent_event_id) {
                $this->setAlert('Redirect to the parent event ID!', array(), self::ALERT_TYPE_INFO);
            }
            $subRequest = Request::create('/admin/event/edit/id/'.self::$parent_event_id, 'GET', array('usage' => self::$usage));
            return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
            return $this->selectYearPattern();
        }
    }

    /**
     * Controller to check the recurring event type
     *
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\Response|string
     */
    public function ControllerCheckType(Application $app)
    {

        $this->initialize($app);

        $fields = $this->getSelectTypeFormFields();
        $form = $fields->getForm();
        $form->bind($app['request']);

        if ($form->isValid()) {
            // the form is valid
            $data = $form->getData();
            self::$recurring_id = $data['recurring_id'];
            self::$event_id = $data['event_id'];
            self::$recurring_id = $data['recurring_id'];
            self::$event_id = $data['event_id'];
            self::$parent_event_id = $data['parent_event_id'];
            self::$recurring_type = $data['recurring_type'];
            if (self::$recurring_type == 'NONE') {
                if (self::$recurring_id > 0) {
                    // delete this recurring event!
                    $this->deleteRecurringEvent();
                }
                else {
                    // nothing to do, return to event edit dialog
                    $this->setAlert('No recurring event type selected', array(), self::ALERT_TYPE_SUCCESS);
                }
                if (self::$event_id != self::$parent_event_id) {
                    $this->setAlert('Redirect to the parent event ID!', array(), self::ALERT_TYPE_INFO);
                }
                $subRequest = Request::create('/admin/event/edit/id/'.self::$parent_event_id, 'GET', array('usage' => self::$usage));
                return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
            }
            switch (self::$recurring_type) {
                // select the next step
                case 'DAY':
                    return $this->selectDayType();
                case 'WEEK':
                    return $this->selectWeekSequence();
                case 'MONTH':
                    return  $this->selectMonthType();
                case 'YEAR':
                    return $this->selectYearType();
                default:
                    $this->setAlert('Do not know how to handle the recurring type <b>%type%</b>.',
                        array('%type%' => self::$recurring_type), self::ALERT_TYPE_DANGER);
                    return $this->ControllerStart($app, self::$event_id);
            }
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
            return $this->ControllerStart($app, self::$event_id);
        }
    }

    /**
     * Controller to start handling recurring events
     *
     * @param Application $app
     * @param integer $parent_event_id
     */
    public function ControllerStart(Application $app, $event_id)
    {
        $this->initialize($app);

        if ($event_id < 1) {
            // invalid event ID
            $this->setAlert('Missing a valid Event ID!', array(), self::ALERT_TYPE_DANGER);
            $subRequest = Request::create('/admin/event/list', 'GET', array('usage' => self::$usage));
            return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        }
        self::$event_id = $event_id;

        if (false !== ($recurring = $this->RecurringData->selectByEventID($event_id))) {
            // this is the parent event itself
            self::$parent_event_id = $recurring['parent_event_id'];
            self::$recurring_id = $recurring['recurring_id'];
            self::$recurring_type = $recurring['recurring_type'];
        }
        elseif ((false !== ($event = $this->EventData->selectEvent(self::$event_id))) &&
                (false !== ($recurring = $this->RecurringData->select($event['event_recurring_id'])))) {
            // got the recurring record from the parent
            self::$parent_event_id = $recurring['parent_event_id'];
            self::$recurring_id = $recurring['recurring_id'];
            self::$recurring_type = $recurring['recurring_type'];
        }
        else {
            self::$parent_event_id = self::$event_id;
            self::$recurring_id = -1;
        }

        if (false !== ($status = $this->RecurringData->getReadableCurringEvent($recurring['recurring_id']))) {
            $this->setAlert($status);
        }
        else {
            $this->setAlert('For the event with the ID %event_id% is no recurring defined.',
                array('%event_id%' => self::$parent_event_id));
        }

        $fields = $this->getSelectTypeFormFields();
        $form = $fields->getForm();

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Event/Template', 'admin/recurring.select.twig'),
            array(
                'usage' => self::$usage,
                'alert' => $this->getAlert(),
                'toolbar' => $this->getToolbar('event_edit'),
                'form' => $form->createView(),
                'header' => $this->app['translator']->trans('Recurring event'),
                'action' => FRAMEWORK_URL.'/admin/event/recurring/check/type?usage='.self::$usage,
                'abort' => FRAMEWORK_URL.'/admin/event/edit/id/'.self::$event_id.'?usage='.self::$usage
            ));
    }

    /**
     * Delete the recurring event
     *
     */
    protected function deleteRecurringEvent()
    {
        // delete the depending events and remove the recurring ID from parent event
        $this->EventData->deleteRecurringEventsOfParent(self::$parent_event_id, self::$recurring_id);
        $this->app['monolog']->addDebug('Deleted all recurring events with ID '.self::$recurring_id.' depending on event ID '.self::$parent_event_id);
        // delete the recurring event
        $this->RecurringData->delete(self::$recurring_id);
        $this->setAlert('The recurring events where successfull deleted.');
    }

    /**
     * Create a new Event record from the parent with the given insert date
     *
     * @param array $parent
     * @param Carbon $insert_date
     */
    protected function createEventFromParent($parent, Carbon $insert_date)
    {
        $data = $parent;

        unset($data['event_id']);
        unset($data['event_timestamp']);
        unset($data['participants']);
        unset($data['rating']);

        // get the parent event dates
        $parent_date_from = Carbon::createFromFormat('Y-m-d H:i:s', $parent['event_date_from']);
        $parent_date_to = Carbon::createFromFormat('Y-m-d H:i:s', $parent['event_date_to']);
        $parent_deadline = Carbon::createFromFormat('Y-m-d H:i:s', $parent['event_deadline']);
        // different in minutes between event start and end
        $date_diff_minutes = $parent_date_from->diffInMinutes($parent_date_to);
        // different in minutes between deadline and event start
        $deadline_diff_minutes = $parent_deadline->diffInMinutes($parent_date_from);

        // the new event date FROM
        $date_from = $insert_date->copy();
        $date_from->hour($parent_date_from->hour);
        $date_from->minute($parent_date_from->minute);
        $data['event_date_from'] = $date_from->toDateTimeString();

        // the new event date TO
        $date_to = $date_from->copy();
        $date_to->addMinutes($date_diff_minutes);
        $data['event_date_to'] = $date_to->toDateTimeString();

        // the new publish FROM
        $publish_from = $date_from->copy();
        $publish_from->startOfDay();
        $publish_from->subDays(self::$config['event']['date']['event_publish_from']['subtract_days']);
        $data['event_publish_from'] = $publish_from->toDateTimeString();

        // the new publish TO
        $publish_to = $date_to->copy();
        $publish_to->endOfDay();
        $publish_to->addDays(self::$config['event']['date']['event_publish_to']['add_days']);
        $data['event_publish_to'] = $publish_to->toDateTimeString();

        // the new deadline
        if ($parent['event_deadline'] != '0000-00-00 00:00:00') {
            $deadline = $date_from->copy();
            $deadline->subMinutes($deadline_diff_minutes);
            $data['event_deadline'] = $deadline->toDateTimeString();
        }

        $data['event_recurring_id'] = self::$recurring_id;

        $event_id = -1;
        $this->EventData->insertEvent($data, $event_id);

        $this->setAlert('Create a new recurring event with the ID %event_id%',
            array('%event_id%' => $event_id), self::ALERT_TYPE_SUCCESS);
    }

    /**
     * Create the recurring event
     *
     * @return boolean
     */
    protected function createRecurringEvent()
    {
        if (false === ($parent = $this->EventData->selectEvent(self::$parent_event_id))) {
            $this->setAlert('The record with the ID %id% does not exists!',
                array('%id%' => self::$parent_event_id), self::ALERT_TYPE_DANGER);
            return false;
        }

        if ($parent['event_recurring_id'] != self::$recurring_id) {
            // insert the recurring ID into the parent event record
            $data = array(
                'event_recurring_id' => self::$recurring_id
            );
            $this->EventData->updateEvent($data, self::$parent_event_id);
        }

        if (false === ($recurring = $this->RecurringData->select(self::$recurring_id))) {
            $this->setAlert('The record with the ID %id% does not exists!',
                array('%id%' => self::$parent_event_id), self::ALERT_TYPE_DANGER);
            return false;
        }

        // start date of the recurring
        $start = Carbon::createFromFormat('Y-m-d H:i:s', $parent['event_date_from']);
        // end date of the recurring
        $end = Carbon::createFromFormat('Y-m-d', $recurring['recurring_date_end']);
        $end->endOfDay();

        // actual date
        $now = Carbon::create();

        $exclude = explode(',', $recurring['exclude_dates']);

        // create date is identical with the start date!
        $create = $start->copy();
        // skip the first date because it already exists
        $create_event = false;
        $counter = 1;

        // loop through the recurring dates
        while ($create->lt($end)) {
            if ($create->lt($now) || in_array($create->toDateString(), $exclude)) {
                $create_event = false;
            }
            if ($create_event) {
                // create a new recurring event
                $this->createEventFromParent($parent, $create);
            }
            else {
                // set $create_event again to true!
                $create_event = true;
            }

            // calculate the next recurring date
            switch ($recurring['recurring_type']) {
                case 'DAY':
                    if ($recurring['day_type'] == 'DAILY') {
                        // increase the date by adding the day sequence
                        $create->addDays($recurring['day_sequence']);
                    }
                    else {
                        // increase the date by adding the next weekday
                        $create->addWeekday();
                    }
                    break;
                case 'WEEK':
                    // weekly recurring
                    $create->addWeeks($recurring['week_sequence']);
                    break;
                case 'MONTH':
                    if ($recurring['month_type'] == 'SEQUENCE') {
                        $create->addMonths($recurring['month_sequence_month']);
                    }
                    else {
                        // using a PATTERN
                        if (!in_array($recurring['month_pattern_type'], array('FIRST_THIRD','SECOND_FOURTH','SECOND_LAST')) ||
                            ($counter % 2 == 0)) {
                            // we need two loops for FIRST_THIRD, SECOND_FOURTH and SECOND_LAST!
                            $create->addMonths($recurring['month_sequence_month']);
                        }
                        // get the day of week integer value
                        $dayOfWeek = $this->RecurringData->getDayOfWeekInteger($recurring['month_pattern_day']);
                        // create the recurring date from type
                        switch ($recurring['month_pattern_type']) {
                            case 'FIRST':
                                $create->firstOfMonth($dayOfWeek);
                                break;
                            case 'SECOND':
                                $create->nthOfMonth(2, $dayOfWeek);
                                break;
                            case 'THIRD':
                                $create->nthOfMonth(3, $dayOfWeek);
                                break;
                            case 'FOURTH':
                                $create->nthOfMonth(4, $dayOfWeek);
                                break;
                            case 'LAST':
                                $create->lastOfMonth($dayOfWeek);
                                break;
                            case 'FIRST_THIRD':
                                ($counter % 2 == 0) ? $create->firstOfMonth($dayOfWeek) : $create->nthOfMonth(3, $dayOfWeek);
                                break;
                            case 'SECOND_FOURTH':
                                ($counter % 2 == 0) ? $create->nthOfMonth(2, $dayOfWeek) : $create->nthOfMonth(4, $dayOfWeek);
                                break;
                            case 'SECOND_LAST':
                                ($counter % 2 == 0) ? $create->nthOfMonth(2, $dayOfWeek) : $create->lastOfMonth($dayOfWeek);
                                break;
                        }
                        break;
                    }
                    break;
                case 'YEAR':
                    if ($recurring['year_type'] == 'SEQUENCE') {
                        $create->addYears($recurring['year_repeat']);
                    }
                    else {
                        // using a PATTERN
                        $create->addYears($recurring['year_repeat']);
                        // get the day of week integer value
                        $dayOfWeek = $this->RecurringData->getDayOfWeekInteger($recurring['year_pattern_day']);
                        // create the recurring date from type
                        switch ($recurring['year_pattern_type']) {
                            case 'FIRST':
                                $create->firstOfMonth($dayOfWeek);
                                break;
                            case 'SECOND':
                                $create->nthOfMonth(2, $dayOfWeek);
                                break;
                            case 'THIRD':
                                $create->nthOfMonth(3, $dayOfWeek);
                                break;
                            case 'FOURTH':
                                $create->nthOfMonth(4, $dayOfWeek);
                                break;
                            case 'LAST':
                                $create->lastOfMonth($dayOfWeek);
                                break;
                        }
                    }
                    break;
                default:
                    $this->setAlert("Don't know how to handle the recurring type %type%.",
                            array('%type%' => $recurring['recurring_type']), self::ALERT_TYPE_DANGER);
                    return false;
            }

            // increase the counter
            $counter++;
        } // while

        return true;
    }
}
