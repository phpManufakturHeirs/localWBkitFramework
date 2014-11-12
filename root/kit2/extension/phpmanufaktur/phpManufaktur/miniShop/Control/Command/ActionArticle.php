<?php

/**
 * miniShop
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/miniShop
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\miniShop\Control\Command;

use Silex\Application;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;


class ActionArticle extends CommandBasic
{
    protected $Basket = null;

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Basic\Control\kitCommand\Basic::initParameters()
     */
    protected function initParameters(Application $app, $parameter_id=-1)
    {
        parent::initParameters($app, $parameter_id);

        $this->Basket = new Basket($app);

        // check the CMS GET parameters
        $GET = $this->getCMSgetParameters();
        if (isset($GET['command']) && ($GET['command'] == 'minishop')
            && isset($GET['action']) && ($GET['action'] == 'article')) {
            // the command and parameters are set as GET from the CMS
            foreach ($GET as $key => $value) {
                if ($key == 'command') {
                    continue;
                }
                self::$parameter[$key] = $value;
            }
            $this->setCommandParameters(self::$parameter);
        }

        if (isset(self::$parameter['alert'])) {
            $this->setAlertUnformatted(base64_decode(self::$parameter['alert']));
        }
    }

    /**
     * Get the small form to add this article to the basket
     *
     * @param array $data
     */
    protected function getOrderForm($data=array())
    {

        $title_quantity = $this->app['translator']->trans('Quantity to order');

        $form = $this->app['form.factory']->createBuilder('form', null, array('csrf_protection' => false))
        ->add('id', 'hidden', array(
            'data' => isset($data['id']) ? $data['id'] : -1
        ))
        ->add('article_name', 'hidden', array(
            'data' => isset($data['article_name']) ? $data['article_name'] : ''
        ))
        ->add('permanent_link', 'hidden', array(
            'data' => isset($data['permanent_link']) ? $data['permanent_link'] : ''
        ))
        ->add('quantity', 'text', array(
            'data' => isset($data['quantity']) ? $data['quantity'] : 1,
            'label' => 'Quantity',
            'attr' => array(
                'class' => 'form-control input-sm',
                'title' => $title_quantity
            )
        ));

        if (isset($data['article_variant_values']) && !empty($data['article_variant_values']) &&
            isset($data['article_variant_name']) && !empty($data['article_variant_name'])) {

            $variant_values = array();
            $items = explode("\r\n", $data['article_variant_values']);
            foreach ($items as $item) {
                $item = trim($item);
                if (!empty($item)) {
                    $variant_values[] = $item;
                }
            }
            if (!empty($variant_values)) {
                $empty_value = $this->app['translator']->trans($data['article_variant_name']);
                $empty_value = sprintf('- %s -', $empty_value);
                $form->add('article_variant_name', 'hidden', array(
                    'data' => $data['article_variant_name']
                ))
                ->add('article_variant_values', 'choice', array(
                    'choices' => $variant_values,
                    'empty_value' => $empty_value,
                    'attr' => array(
                        'class' => 'form-control input-sm'
                    )
                ));
            }
        }
        else {
            $form->add('article_variant_name', 'hidden')
            ->add('article_variant_values', 'hidden');
        }

        if (isset($data['article_variant_values_2']) && !empty($data['article_variant_values_2']) &&
            isset($data['article_variant_name_2']) && !empty($data['article_variant_name_2'])) {
            $variant_values = array();
            $items = explode("\r\n", $data['article_variant_values_2']);
            foreach ($items as $item) {
                $item = trim($item);
                if (!empty($item)) {
                    $variant_values[] = $item;
                }
            }
            if (!empty($variant_values)) {
                $empty_value = $this->app['translator']->trans($data['article_variant_name_2']);
                $empty_value = sprintf('- %s -', $empty_value);
                $form->add('article_variant_name_2', 'hidden', array(
                    'data' => $data['article_variant_name_2']
                ))
                ->add('article_variant_values_2', 'choice', array(
                    'choices' => $variant_values,
                    'empty_value' => $empty_value,
                    'attr' => array(
                        'class' => 'form-control input-sm'
                    )
                ));
            }
        }
        else {
            $form->add('article_variant_name_2', 'hidden')
            ->add('article_variant_values_2', 'hidden');
        }

        return $form->getForm();
    }

    /**
     * Show the detailed article description
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function showArticle()
    {

        $article = null;
        $base = null;
        $groups = null;
        $shop_url = null;

        if (is_null(self::$parameter['id'])) {
            $this->setAlert('Please submit a article ID!', array(), self::ALERT_TYPE_DANGER);
        }
        elseif (false === ($article = $this->dataArticle->select(self::$parameter['id']))) {
            $this->setAlert('The record with the ID %id% does not exist!',
                array('%id%' => self::$parameter['id']), self::ALERT_TYPE_DANGER);
        }

        if (is_array($article)) {
            $article['folder_images'] = null;
            if ($article['article_image_folder_gallery'] == 1) {
                // loop through the folder and gather the images
                $folder_images = array();
                $main_image = pathinfo($article['article_image'], PATHINFO_BASENAME);
                $directory = pathinfo(FRAMEWORK_PATH.$article['article_image'], PATHINFO_DIRNAME);
                $images = new Finder();
                $images->files()->in($directory)->sortByName();
                foreach (self::$config['images']['extension'] as $extension) {
                    $images->name($extension);
                }
                foreach ($images as $image) {
                    if ($image->getFilename() !== $main_image) {
                        $realpath = $image->getRealPath();
                        if (strpos($realpath, realpath(CMS_MEDIA_PATH)) === 0) {
                            $img = CMS_URL.substr($realpath, strlen(realpath(CMS_PATH)));
                        }
                        else {
                            $img = FRAMEWORK_URL.substr($image->getRealPath(), strlen(realpath(FRAMEWORK_PATH)));
                        }
                        $folder_images[] = str_replace('\\','/', $img);
                    }
                }
                $article['folder_images'] = $folder_images;
            }

            if (false !== ($base = $this->dataBase->select($article['base_id']))) {
                $shop_url = CMS_URL.$base['target_page_link'];
            }
        }

        $form = $this->getOrderForm($article);

        $result = $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/miniShop/Template', 'command/view.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'config' => self::$config,
                'parameter' => self::$parameter,
                'permalink_base_url' => CMS_URL.self::$config['permanentlink']['directory'],
                'article' => $article,
                'groups' => $groups,
                'base' => $base,
                'shop_url' => $shop_url,
                'form' => $form->createView(),
                'basket' => $this->Basket->getBasket()
            ));

        // get the params to autoload jQuery and CSS
        $params = $this->getResponseParameter();

        if (isset($article['id'])) {
            // set the page header and the canonical link
            $params['set_header'] = $article['id'];
            $params['canonical'] = $article['id'];
        }

        return $this->app->json(array(
            'parameter' => $params,
            'response' => $result
        ));
    }

    /**
     * General controller for the article handling
     *
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function Controller(Application $app)
    {
        $this->initParameters($app);
        return $this->showArticle();
    }

    /**
     * add/remove articles to the shopping basket
     *
     * @param Application $app
     * @throws \Exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ControllerBasketAdd(Application $app)
    {
        $this->initParameters($app);

        // we need the route to the article
        $request_form = $this->app['request']->request->get('form');
        if (!isset($request_form['permanent_link']) || empty($request_form['permanent_link'])) {
            throw new \Exception('Invalid form submission, stopped script.');
        }
        $subdirectory = parse_url(CMS_URL, PHP_URL_PATH);
        $article_route = $subdirectory.self::$config['permanentlink']['directory'].'/article/'.$request_form['permanent_link'];

        // now we handle the order form
        $form = $this->getOrderForm();
        $form->bind($this->app['request']);

        if ($form->isValid()) {
            // the form is valid
            $data = $form->getData();
            // update the basket with the article data
            $this->Basket->updateBasket($data);
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
        }

        $subRequest = Request::create($article_route, 'GET', array(
            'command' => 'minishop',
            'action' => 'article',
            'id' => $request_form['id'],
            'robots' => 'noindex,follow',
            'canonical' => CMS_URL.$article_route,
            'alert' => base64_encode($this->getAlert())
        ));
        return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }
}
