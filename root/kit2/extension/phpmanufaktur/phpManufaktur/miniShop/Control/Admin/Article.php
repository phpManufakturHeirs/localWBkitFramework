<?php

/**
 * miniShop
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/miniShop
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\miniShop\Control\Admin;

use Silex\Application;
use phpManufaktur\miniShop\Data\Shop\Article as DataArticle;
use phpManufaktur\miniShop\Data\Shop\Base as DataBase;
use phpManufaktur\miniShop\Data\Shop\Group as DataGroup;
use Carbon\Carbon;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Article extends Admin
{
    protected $dataArticle = null;
    protected $dataBase = null;
    protected $dataGroup = null;

    protected function initialize(Application $app)
    {
        parent::initialize($app);

        $this->dataArticle = new DataArticle($app);
        $this->dataBase = new DataBase($app);
        $this->dataGroup = new DataGroup($app);
    }

    /**
     * Show the article list of the miniShop
     *
     * @return string rendered dialog
     */
    public function Controller(Application $app)
    {
        $this->initialize($app);

        if (false === ($articles = $this->dataArticle->selectAll())) {
            if ($this->dataBase->count() < 1) {
                $this->setAlert('Please create a base configuration to start with your miniShop!', array(), self::ALERT_TYPE_INFO);
            }
            elseif ($this->dataGroup->count() < 1) {
                $this->setAlert('Please create a article group to start with your miniShop!', array(), self::ALERT_TYPE_INFO);
            }
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/miniShop/Template', 'admin/list.article.twig'),
            array(
                'usage' => self::$usage,
                'usage_param' => self::$usage_param,
                'toolbar' => $this->getToolbar('article'),
                'alert' => $this->getAlert(),
                'config' => self::$config,
                'articles' => $articles
            ));
    }

    /**
     * Get the form to create and edit an article
     *
     * @param array $data
     */
    protected function getArticleForm($data = array())
    {
        $status_array = array();
        $types = $this->dataArticle->getStatusTypes();
        foreach ($types as $type) {
            $type_name = $this->app['utils']->humanize($type);
            $status_array[$type] = $this->app['translator']->trans($type_name);
        }

        $group_array = array();
        $groups = $this->dataGroup->selectAll();
        foreach ($groups as $group) {
            $group_name = $this->app['utils']->humanize($group['name']);
            $group_array[$group['id']] = $this->app['translator']->trans($group_name);
        }

        $publish_date = (isset($data['publish_date']) && ($data['publish_date'] != '0000-00-00')) ? date($this->app['translator']->trans('DATE_FORMAT'), strtotime($data['publish_date'])) : date($this->app['translator']->trans('DATE_FORMAT'));
        $available_date = (isset($data['available_date']) && ($data['available_date'] != '0000-00-00')) ? date($this->app['translator']->trans('DATE_FORMAT'), strtotime($data['available_date'])) : date($this->app['translator']->trans('DATE_FORMAT'));

        $decimal_separator = $this->app['translator']->trans('DECIMAL_SEPARATOR');
        $thousand_separator = $this->app['translator']->trans('THOUSAND_SEPARATOR');

        $article_price = isset($data['article_price']) ? $data['article_price'] : 0;
        $shipping_cost = isset($data['shipping_cost']) ? $data['shipping_cost'] : 0;

        $ck_config_short = $this->app['utils']->getTemplateFile('@phpManufaktur/miniShop/Template', 'admin/ckeditor.config.short.js', '', true);
        $ck_config_short = str_replace(MANUFAKTUR_PATH, MANUFAKTUR_URL, $ck_config_short);

        $ck_config_long = $this->app['utils']->getTemplateFile('@phpManufaktur/miniShop/Template', 'admin/ckeditor.config.long.js', '', true);
        $ck_config_long = str_replace(MANUFAKTUR_PATH, MANUFAKTUR_URL, $ck_config_long);



        $form = $this->app['form.factory']->createBuilder('form')
        ->add('id', 'hidden', array(
            'data' => isset($data['id']) ? $data['id'] : -1
        ))
        ->add('article_name', 'text', array(
            'data' => isset($data['article_name']) ? $data['article_name'] : ''
        ))
        ->add('order_number', 'text', array(
            'data' => isset($data['order_number']) ? $data['order_number'] : '',
            'required' => false
        ))
        ->add('article_group', 'choice', array(
            'choices' => $group_array,
            'empty_value' => '- please select -',
            'data' => isset($data['group_id']) ? $data['group_id'] : null
        ))
        ->add('publish_date', 'text', array(
            'data' => $publish_date,
            'attr' => array('class' => 'datepicker')
        ))
        ->add('available_date', 'text', array(
            'data' => $available_date,
            'attr' => array('class' => 'datepicker')
        ))
        ->add('status', 'choice', array(
            'choices' => $status_array,
            'empty_value' => false,
            'data' => isset($data['status']) ? $data['status'] : 'LOCKED'
        ));

        $permanent_link = isset($data['permanent_link']) ? $data['permanent_link'] : '';
        $form->add('permanent_link', 'text', array(
            'data' => $permanent_link,
            'attr' => array(
                'help' => CMS_URL.self::$config['permanentlink']['directory'].'/article/'.$permanent_link
            )
        ))
        ->add('article_image', 'hidden', array(
            'data' => isset($data['article_image']) ? $data['article_image'] : ''
        ))
        ->add('article_price', 'text', array(
            'data' => number_format($article_price, 2, $decimal_separator, $thousand_separator)
        ))
        /*
        ->add('article_limit', 'text', array(
            'data' => isset($data['article_limit']) ? $data['article_limit'] : -1
        ))
        */
        ->add('shipping_cost', 'text', array(
            'data' => number_format($shipping_cost, 2, $decimal_separator, $thousand_separator)
        ))
        ->add('seo_title', 'text', array(
            'data' => isset($data['seo_title']) ? $data['seo_title'] : '',
            'required' => false
        ))
        ->add('seo_description', 'textarea', array(
            'data' => isset($data['seo_description']) ? $data['seo_description'] : '',
            'required' => false
        ))
        ->add('seo_keywords', 'text', array(
            'data' => isset($data['seo_keywords']) ? $data['seo_keywords'] : '',
            'required' => false
        ))
        ->add('description_short', 'textarea', array(
            'data' => isset($data['description_short']) ? $data['description_short'] : '',
            'attr' => array(
                'class' => 'description_short',
                'type' => 'html',
                'config' => $ck_config_short
            ),
            'label' => 'Teaser'
        ))
        ->add('article_variant_name', 'text', array(
            'data' => isset($data['article_variant_name']) ? $data['article_variant_name'] : '',
            'required' => false
        ));
        $help = $this->app['translator']->trans('Each value in a separate line, use <key>SHIFT</key>+<key>ENTER</key>');
        $form->add('article_variant_values', 'textarea', array(
            'data' => isset($data['article_variant_values']) ? $data['article_variant_values'] : null,
            'required' => false,
            'attr' => array(
                'help' => $help
            )
        ))
        ->add('article_variant_name_2', 'text', array(
            'data' => isset($data['article_variant_name_2']) ? $data['article_variant_name_2'] : '',
            'required' => false
        ))
        ->add('article_variant_values_2', 'textarea', array(
            'data' => isset($data['article_variant_values_2']) ? $data['article_variant_values_2'] : null,
            'required' => false,
            'attr' => array(
                'help' => $help
            )
        ))
        ->add('description_long', 'textarea', array(
            'data' => isset($data['description_long']) ? $data['description_long'] : '',
            'attr' => array(
                'class' => 'description_long',
                'type' => 'html',
                'config' => $ck_config_long,
                'height' => '250px',
                'widget_column' => 'col-sm-12',
                'label_column' => 'col-sm-12'
            ),
            'required' => false
        ))
        ;

        if (isset($data['article_image']) && !empty($data['article_image'])) {
            $form->add('article_image_folder_gallery', 'checkbox', array(
                'data' => isset($data['article_image_folder_gallery']) ? (bool) $data['article_image_folder_gallery'] : null,
                'required' => false
            ));
        }
        else {
            $form->add('article_image_folder_gallery', 'hidden');
        }

        if (isset($data['id']) && ($data['id'] > 0)) {
            $form->add('article_delete_checkbox', 'checkbox', array(
                'required' => false
            ));
        }
        else {
            $form->add('article_delete_checkbox', 'hidden');
        }

        return $form->getForm();
    }

    /**
     * Get the rendered Article dialog
     *
     * @param FormFactory $form
     */
    protected function getArticleDialog($form)
    {
        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/miniShop/Template', 'admin/edit.article.twig'),
            array(
                'usage' => self::$usage,
                'usage_param' => self::$usage_param,
                'toolbar' => $this->getToolbar('article'),
                'alert' => $this->getAlert(),
                'form' => $form->createView()
            ));
    }

    /**
     * Controller for the Article Dialog
     *
     * @param Application $app
     * @param integer $article_id
     */
    public function ControllerEdit(Application $app, $article_id)
    {
        $this->initialize($app);

        $data = array();
        if ($article_id > 0) {
            if (false === ($data = $this->dataArticle->select($article_id))) {
                $this->setAlert('The record with the ID %id% does not exist!',
                    array('%id%' => $article_id), self::ALERT_TYPE_DANGER);
            }
        }
        $form = $this->getArticleForm($data);
        return $this->getArticleDialog($form);
    }

    /**
     *
     * @param unknown $data
     * @return string|boolean
     */
    protected function checkArticleForm(&$data=array())
    {
        $form = $this->getArticleForm();
        $form->bind($this->app['request']);

        if ($form->isValid()) {
            // the form is valid
            $data = $form->getData();

            if ($data['article_delete_checkbox']) {
                // delete the article
                $this->dataArticle->delete($data['id']);
                $this->setAlert('The article with the ID %id% has successfull deleted',
                    array('%id%' => $data['id']), self::ALERT_TYPE_SUCCESS);
                // show the article list and prompt the alert
                return $this->Controller($this->app);
            }
            else {
                // delete this item to avoid conflicts with the data table
                unset($data['article_delete_checkbox']);
            }

            // convert all dates and float values
            if (empty($data['publish_date'])) {
                $dt = Carbon::create();
                $data['publish_date'] = $dt->toDateString();
            }
            else {
                $dt = Carbon::createFromFormat($this->app['translator']->trans('DATE_FORMAT'), $data['publish_date']);
                $data['publish_date'] = $dt->toDateString();
            }
            if (empty($data['available_date'])) {
                $dt = Carbon::create();
                $data['available_date'] = $dt->toDateString();
            }
            else {
                $dt = Carbon::createFromFormat($this->app['translator']->trans('DATE_FORMAT'), $data['available_date']);
                $data['available_date'] = $dt->toDateString();
            }

            $data['article_price'] = $this->app['utils']->str2float($data['article_price']);
            $data['shipping_cost'] = $this->app['utils']->str2float($data['shipping_cost']);

            $permanent_link = isset($data['permanent_link']) ? $data['permanent_link'] : null;
            if (is_null($permanent_link)) {
                $this->setAlert('Please define a permanent link for this article!', array(), self::ALERT_TYPE_WARNING);
                return false;
            }
            $permanent_link = trim($permanent_link, '\\');
            $permanent_link = trim($permanent_link, '/');
            $permanent_link = trim($permanent_link);
            $data['permanent_link'] = $this->app['utils']->sanitizeLink(strtolower($permanent_link));

            $data['group_id'] = $data['article_group'];
            unset($data['article_group']);
            $group = $this->dataGroup->select($data['group_id']);
            $data['group_name'] = $group['name'];
            $data['base_id'] = $group['base_id'];
            $data['base_name'] = $group['base_name'];

            $data['description_short'] = trim($data['description_short']);
            $data['description_long'] = trim($data['description_long']);
            $data['article_name'] = trim($data['article_name']);
            $data['seo_title'] = trim($data['seo_title']);
            $data['seo_description'] = trim($data['seo_description']);
            $data['seo_keywords'] = trim($data['seo_keywords']);

            $checks = array('order_number', 'article_variant_name', 'article_variant_values', 'article_variant_name_2', 'article_variant_values_2');
            foreach ($checks as $check) {
                if (is_null($data[$check])) {
                    $data[$check] = '';
                }
                $data[$check] = trim($data[$check]);
            }

            if (empty($data['description_short'])) {
                $this->setAlert('The short description can not be empty!', array(), self::ALERT_TYPE_WARNING);
                return false;
            }

            if (empty($data['description_long'])) {
                $data['description_long'] = $data['description_short'];
            }

            if (empty($data['seo_title'])) {
                $data['seo_title'] = $data['article_name'];
            }
            if (empty($data['seo_description'])) {
                $data['seo_description'] = strip_tags($data['description_short']);
            }

            if (!empty($data['seo_keywords'])) {
                $explode = explode(',', utf8_decode($data['seo_keywords']));
                $keywords = array();
                foreach ($explode as $item) {
                    $keyword = strtolower(trim($item));
                    if (!empty($keyword)) {
                        $keywords[] = $keyword;
                    }
                }
                $data['seo_keywords'] = utf8_encode(implode(', ', $keywords));
            }

            if (is_null($data['article_image'])) {
                $data['article_image'] = '';
            }
            $data['article_image_folder_gallery'] = intval($data['article_image_folder_gallery']);

            if (empty($data['permanent_link'])) {
                $this->setAlert('Please define a permanent link for this article!', array(), self::ALERT_TYPE_WARNING);
                return false;
            }

            if ($data['id'] < 1) {
                // this is a new record
                if (false !== ($check = $this->dataArticle->existsPermanentLink($data['permanent_link']))) {
                    // this permanent link is already in use
                    $this->setAlert('The permanent link <strong>/%link%</strong> is already in use by another article, please select an alternate one.',
                        array('%link%' => $data['permanent_link'])) ;
                    return false;
                }
                unset($data['id']);
                $data['id'] = $this->dataArticle->insert($data);
                $this->setAlert('Successful inserted a new article.', array(), self::ALERT_TYPE_SUCCESS);
            }
            else {
                // check existing record
                $id = $this->dataArticle->selectArticleIDbyPermaLink($data['permanent_link']);
                if (($id !== false) && ($id != $data['id'])) {
                    // this permanent link is already in use
                    $this->setAlert('The permanent link <strong>/%link%</strong> is already in use by another article, please select an alternate one.',
                        array('%link%' => $data['permanent_link'])) ;
                    return false;
                }
                $old = $this->dataArticle->select($data['id']);

                $do_update = false;
                foreach ($data as $key => $value) {
                    if ($value != $old[$key]) {
                        $do_update = true;
                        break;
                    }
                }
                if ($do_update) {
                    $this->dataArticle->update($data['id'], $data);
                    $this->setAlert('Successful updated the article.', array(), self::ALERT_TYPE_SUCCESS);
                }
                else {
                    $this->setAlert('The article has not changed.', array(), self::ALERT_TYPE_INFO);
                }
            }
            return true;
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
            return false;
        }
    }

    /**
     * Controller to check the article dialog and insert or update a record
     *
     * @param Application $app
     */
    public function ControllerEditCheck(Application $app)
    {
        $this->initialize($app);

        $data = array();
        $this->checkArticleForm($data);
        $form = $this->getArticleForm($data);
        return $this->getArticleDialog($form);
    }

    /**
     * Controller to execute the Mediabrowser to select an article image
     *
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ControllerImageSelect(Application $app)
    {
        $this->initialize($app);

        $data = array();
        if (!$this->checkArticleForm($data)) {
            $form = $this->getArticleForm($data);
            return $this->getArticleDialog($form);
        }

        // grant that the directory exists
        $app['filesystem']->mkdir(FRAMEWORK_PATH.self::$config['images']['directory']['select']);

        // exec the MediaBrowser
        $subRequest = Request::create('/mediabrowser', 'GET', array(
            'usage' => self::$usage,
            'start' => self::$config['images']['directory']['start'],
            'redirect' => '/admin/minishop/article/image/check/id/'.$data['id'],
            'mode' => 'public',
            'directory' => self::$config['images']['directory']['select']
        ));
        return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * Check the response of the MediaBrowser, update the article record and
     * show the article dialog
     *
     * @param Application $app
     * @param integer $article_id
     */
    public function ControllerImageCheck(Application $app, $article_id)
    {
        $this->initialize($app);

        if (null == ($image = $app['request']->get('file'))) {
            $this->setAlert('There was no image selected.', array(), self::ALERT_TYPE_INFO);
        }
        else {
            // update the article record
            $update = array(
                'article_image' => $image
            );
            $this->dataArticle->update($article_id, $update);
            $this->setAlert('The image %image% was successfull inserted.',
                array('%image%' => basename($image)), self::ALERT_TYPE_SUCCESS);
        }

        if (false === ($data = $this->dataArticle->select($article_id))) {
            $this->setAlert('The record with the ID %id% does not exist!',
                array('%id%' => $article_id), self::ALERT_TYPE_DANGER);
        }
        $form = $this->getArticleForm($data);
        return $this->getArticleDialog($form);
    }

    /**
     * Controller to remove the current image
     *
     * @param Application $app
     * @param integer $article_id
     */
    public function ControllerImageRemove(Application $app, $article_id)
    {
        $this->initialize($app);

        $update = array(
            'article_image' => ''
        );
        $this->dataArticle->update($article_id, $update);
        $this->setAlert('The image was successfull removed.', array(), self::ALERT_TYPE_SUCCESS);

        if (false === ($data = $this->dataArticle->select($article_id))) {
            $this->setAlert('The record with the ID %id% does not exist!',
                array('%id%' => $article_id), self::ALERT_TYPE_DANGER);
        }
        $form = $this->getArticleForm($data);
        return $this->getArticleDialog($form);
    }
}
