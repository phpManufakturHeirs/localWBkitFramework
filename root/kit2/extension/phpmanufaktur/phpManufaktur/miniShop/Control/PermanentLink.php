<?php

/**
 * miniShop
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/miniShop
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\miniShop\Control;

use Silex\Application;
use phpManufaktur\miniShop\Control\Configuration;
use phpManufaktur\miniShop\Data\Shop\Article as DataArticle;
use phpManufaktur\miniShop\Data\Shop\Base as DataBase;
use Carbon\Carbon;
use phpManufaktur\Basic\Data\CMS\Page as DataPage;
use phpManufaktur\miniShop\Control\Command\Basket;
use phpManufaktur\Basic\Control\Pattern\Alert;
use phpManufaktur\miniShop\Data\Shop\Order as DataOrder;

class PermanentLink extends Alert
{
    protected $dataArticle = null;
    protected $dataBase = null;
    protected $dataPage = null;
    protected $Basket = null;
    protected $dataOrder = null;

    protected static $config = null;

    protected static $ignore_parameters = array('searchresult','sstring','pid');

    /**
     * Initialize the class
     *
     * @param Application $app
     */
    protected function initialize(Application $app)
    {
        parent::initialize($app);

        $Config = new Configuration($app);
        self::$config = $Config->getConfiguration();

        $this->dataArticle = new DataArticle($app);
        $this->dataBase = new DataBase($app);
        $this->dataPage = new DataPage($app);
        $this->Basket = new Basket($app);
        $this->dataOrder = new DataOrder($app);
    }

    /**
     * Execute cURL to catch the CMS content into the permanent link
     *
     * @param string $url
     * @return mixed
     */
    protected function cURLexec($url, $page_id=-1)
    {
        // init cURL
        $ch = curl_init();
        $this->app['monolog']->addDebug(
            sprintf('calling URL [%s]',$url),
            array(__METHOD__, __LINE__));

        if (is_null($this->app['session']->get('MINISHOP_COOKIE_FILE')) ||
            !$this->app['filesystem']->exists($this->app['session']->get('MINISHOP_COOKIE_FILE'))) {
            // this is the first call of this cURL session, create a cookie file
            $this->app['session']->set('MINISHOP_COOKIE_FILE', FRAMEWORK_TEMP_PATH.'/session/'.uniqid('minishop_'));
        }

        // set the general cURL options
        $options = array(
            CURLOPT_HEADER => false,
            //CURLOPT_FOLLOWLOCATION => false, // follow redirects!
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'kitFramework::miniShop',
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_COOKIESESSION => true,
            CURLOPT_COOKIEJAR => $this->app['session']->get('MINISHOP_COOKIE_FILE'),
            CURLOPT_COOKIEFILE => $this->app['session']->get('MINISHOP_COOKIE_FILE')
        );

        if ($page_id > 0) {
            // get the visibility of the target page
            $visibility = $this->dataPage->getPageVisibilityByPageID($page_id);
            if (in_array($visibility, array('none', 'registered', 'private'))) {
                // page can not be shown!
                $error = 'The visibility of the requested page is "none", can not show the content!';
                $this->app['monolog']->addError($error, array(__METHOD__, __LINE__));
                return $this->app['twig']->render($this->app['utils']->getTemplateFile(
                    '@phpManufaktur/Basic/Template', 'kitcommand/bootstrap/noframe/alert.twig'),
                    array(
                        'content' => $this->app['translator']->trans($error),
                        'type' => 'alert-danger'));
            }
        }

        // add the URL to the options
        $options[CURLOPT_URL] = $url;

        curl_setopt_array($ch, $options);

        // set proxy if needed
        $this->app['utils']->setCURLproxy($ch);

        if (false === ($result = curl_exec($ch))) {
            // write debug info
            $this->app['monolog']->addDebug(var_export(curl_getinfo($ch),1), array(__METHOD__, __LINE__));
            // cURL error
            $error = 'cURL error: '.curl_error($ch);
            $this->app['monolog']->addError($error, array(__METHOD__, __LINE__));
            return $this->app['twig']->render($this->app['utils']->getTemplateFile(
                '@phpManufaktur/Basic/Template', 'kitcommand/bootstrap/noframe/alert.twig'),
                array(
                    'content' => $error,
                    'type' => 'alert-danger'));
        }

        curl_close($ch);
        return $result;
    }

    public function ControllerArticle(Application $app, $name)
    {
        $this->initialize($app);

        if (false === ($article = $this->dataArticle->selectByPermanentLink($name))) {
            $message = str_ireplace(array('%directory%','%action%','%name%'),
                array(self::$config['permanentlink']['directory'], 'article', $name),
                'The permanent link <strong>%directory%/%action%/%name%</strong> does not exist!');
            $this->app['monolog']->addDebug(strip_tags($message), array(__METHOD__, __LINE__));
            $app->abort(404, $message);
        }
        elseif ($article['status'] === 'LOCKED') {
            $message = str_ireplace(array('%directory%','%action%','%name%'),
                array(self::$config['permanentlink']['directory'], 'article', $name),
                'The permanent link <strong>%directory%/%action%/%name%</strong> is temporary not available!');
            $this->app['monolog']->addDebug(strip_tags($message), array(__METHOD__, __LINE__));
            $app->abort(423, $message);
        }
        elseif ($article['status'] === 'DELETED') {
            $message = str_ireplace(array('%directory%','%action%','%name%'),
                array(self::$config['permanentlink']['directory'], 'article', $name),
                'The permanent link <strong>%directory%/%action%/%name%</strong> is no longer available!');
            $this->app['monolog']->addDebug(strip_tags($message), array(__METHOD__, __LINE__));
            $app->abort(410, $message);
        }

        $publish_date = Carbon::createFromFormat('Y-m-d', $article['publish_date']);
        $now = Carbon::create();
        if ($now->lt($publish_date)) {
            // the article is not published yet!
            $message = str_ireplace(array('%directory%','%action%','%name%'),
                array(self::$config['permanentlink']['directory'], 'article', $name),
                'The permanent link <strong>%directory%/%action%/%name%</strong> is temporary not available!');
            $this->app['monolog']->addDebug(strip_tags($message), array(__METHOD__, __LINE__));
            $app->abort(423, $message);
        }

        // get the base configuration
        $base = $this->dataBase->select($article['base_id']);

        $link = substr($base['target_page_link'], strlen($this->dataPage->getPageDirectory()), (strlen($this->dataPage->getPageExtension()) * -1));

        if (false === ($page_id = $this->dataPage->getPageIDbyPageLink($link))) {
            // the CMS page does not exist!
            $message = str_ireplace('%link%', $base['target_page_link'], 'The CMS page <strong>%link%</strong> does not exist!');
            $this->app['monolog']->addError(strip_tags($message), array(__METHOD__, __LINE__));
            $app->abort(404, $message);
        }

        if (!$this->dataPage->existsCommandAtPageID('minishop', $page_id)) {
            // missing the kitCommand at the target URL
            $message = str_ireplace('%link%', $base['target_page_link'], 'The CMS page <strong>%link%</strong> does not contain the needed kitCommand!');
            $this->app['monolog']->addError(strip_tags($message), array(__METHOD__, __LINE__));
            $app->abort(404, $message);
        }

        $parameter = array(
            'command' => 'minishop',
            'action' => 'article',
            'id' => $article['id'],
            'robots' => 'index,follow',
            'canonical' => CMS_URL.self::$config['permanentlink']['directory'].'/article/'.$name
        );

        $gets = $this->app['request']->query->all();
        foreach ($gets as $key => $value) {
            if (!key_exists($key, $parameter) && !in_array($key, self::$ignore_parameters)) {
                // pass all other parameters to the target page
                $parameter[$key] = $value;
            }
        }

        // create the target URL and set the needed parameters
        $target_url = CMS_URL.$base['target_page_link'].'?'.http_build_query($parameter, '', '&');

        return $this->cURLexec($target_url, $page_id);
    }

    /**
     * Controller for the shopping basket
     *
     */
    public function ControllerBasket(Application $app)
    {
        $this->initialize($app);

        $basket = $this->Basket->getBasket();

        if (empty($basket)) {
            // the basket is empty - select a base configuration
            if (false === ($bases = $this->dataBase->selectAllActive())) {
                $app->abort(423, 'No active shop available!');
            }
            $base = $bases[0];
        }
        elseif (is_array($basket)) {
            if (null === ($basket_article = array_shift($basket))) {
                throw new \Exception('Invalid basket, can not read the content!');
            }
            if (false === ($article = $this->dataArticle->select($basket_article['id']))) {
                throw new \Exception('Invalid article in basket!');
            }
            if (false === ($base = $this->dataBase->select($article['base_id']))) {
                throw new \Exception('Can not get the base configuration from a basket article!');
            }
        }
        else {
            throw new \Exception('Invalid basket, can not read the content!');
        }

        $link = substr($base['target_page_link'], strlen($this->dataPage->getPageDirectory()), (strlen($this->dataPage->getPageExtension()) * -1));

        if (false === ($page_id = $this->dataPage->getPageIDbyPageLink($link))) {
            // the CMS page does not exist!
            $message = str_ireplace('%link%', $base['target_page_link'], 'The CMS page <strong>%link%</strong> does not exist!');
            $this->app['monolog']->addError(strip_tags($message), array(__METHOD__, __LINE__));
            $app->abort(404, $message);
        }

        $parameter = array(
            'command' => 'minishop',
            'action' => 'basket',
            'robots' => 'noindex,follow',
            'basket' => $app['request']->request->get('form', null)
        );

        $queries = $this->app['request']->query->all();
        foreach ($queries as $key => $value) {
            if (!key_exists($key, $parameter) && !in_array($key, self::$ignore_parameters)) {
                // pass all other parameters to the target page
                $parameter[$key] = $value;
            }
        }

        // create the target URL and set the needed parameters
        $target_url = CMS_URL.$base['target_page_link'].'?'.http_build_query($parameter, '', '&');
        return $this->cURLexec($target_url, $page_id);
    }

    /**
     * Controller to order the articles from the basket
     *
     * @param Application $app
     * @throws \Exception
     */
    public function ControllerOrder(Application $app)
    {
        $this->initialize($app);

        $basket = $this->Basket->getBasket();

        if (empty($basket)) {
            // there exists no basket!
            if (false !== ($bases = $this->dataBase->selectAllActive())) {
                // try to redirect to shop article list
                $link = substr($bases[0]['target_page_link'], strlen($this->dataPage->getPageDirectory()), (strlen($this->dataPage->getPageExtension()) * -1));
                if (false !== ($page_id = $this->dataPage->getPageIDbyPageLink($link))) {
                    $this->setAlert('Your shopping basket is empty.', array(), self::ALERT_TYPE_WARNING);
                    $parameter = array(
                        'command' => 'minishop',
                        'action' => 'list',
                        'alert' => base64_encode($this->getAlert())
                    );
                    $queries = $this->app['request']->query->all();
                    foreach ($queries as $key => $value) {
                        if (!key_exists($key, $parameter) && !in_array($key, self::$ignore_parameters)) {
                            // pass all other parameters to the target page
                            $parameter[$key] = $value;
                        }
                    }
                    // create the target URL and set the needed parameters
                    $target_url = CMS_URL.$bases[0]['target_page_link'].'?'.http_build_query($parameter, '', '&');
                    return $this->cURLexec($target_url, $page_id);
                }
            }
            // not possible to redirect to a shop link:
            $app->abort('423', 'No shopping basket available!');
        }
        elseif (is_array($basket)) {
            if (null === ($basket_article = array_shift($basket))) {
                throw new \Exception('Invalid basket, can not read the content!');
            }
            if (false === ($article = $this->dataArticle->select($basket_article['id']))) {
                throw new \Exception('Invalid article in basket!');
            }
            if (false === ($base = $this->dataBase->select($article['base_id']))) {
                throw new \Exception('Can not get the base configuration from a basket article!');
            }
        }
        else {
            throw new \Exception('Invalid basket, can not read the content!');
        }

        $link = substr($base['target_page_link'], strlen($this->dataPage->getPageDirectory()), (strlen($this->dataPage->getPageExtension()) * -1));

        if (false === ($page_id = $this->dataPage->getPageIDbyPageLink($link))) {
            // the CMS page does not exist!
            $message = str_ireplace('%link%', $base['target_page_link'], 'The CMS page <strong>%link%</strong> does not exist!');
            $this->app['monolog']->addError(strip_tags($message), array(__METHOD__, __LINE__));
            $app->abort(404, $message);
        }

        $parameter = array(
            'command' => 'minishop',
            'action' => 'order',
            'robots' => 'noindex,follow',
            'order' => $app['request']->request->get('form', null)
        );

        $queries = $this->app['request']->query->all();
        foreach ($queries as $key => $value) {
            if (!key_exists($key, $parameter) && !in_array($key, self::$ignore_parameters)) {
                // pass all other parameters to the target page
                $parameter[$key] = $value;
            }
        }

        // create the target URL and set the needed parameters
        $target_url = CMS_URL.$base['target_page_link'].'?'.http_build_query($parameter, '', '&');
        return $this->cURLexec($target_url, $page_id);
    }

    /**
     * Controller to send again the activation mail for a order
     *
     * @param Application $app
     * @param integer $id
     * @return string
     */
    public function ControllerSendActivation(Application $app, $order_id)
    {
        $this->initialize($app);

        if (false === ($order = $this->dataOrder->select($order_id))) {
            $app->abort(404, 'The submitted order ID does not exist.');
        }

        if ($order['status'] !== 'PENDING') {
            $app->abort(410, 'This order was already handled and can not activated again.');
        }

        $data = unserialize($order['data']);

        $link = substr($data['base']['target_page_link'], strlen($this->dataPage->getPageDirectory()), (strlen($this->dataPage->getPageExtension()) * -1));

        if (false === ($page_id = $this->dataPage->getPageIDbyPageLink($link))) {
            // the CMS page does not exist!
            $message = str_ireplace('%link%', $data['base']['target_page_link'], 'The CMS page <strong>%link%</strong> does not exist!');
            $this->app['monolog']->addError(strip_tags($message), array(__METHOD__, __LINE__));
            $app->abort(404, $message);
        }

        $parameter = array(
            'command' => 'minishop',
            'action' => 'send-guid',
            'robots' => 'noindex,follow',
            'order_id' => $order_id
        );

        $queries = $this->app['request']->query->all();
        foreach ($queries as $key => $value) {
            if (!key_exists($key, $parameter) && !in_array($key, self::$ignore_parameters)) {
                // pass all other parameters to the target page
                $parameter[$key] = $value;
            }
        }

        // create the target URL and set the needed parameters
        $target_url = CMS_URL.$data['base']['target_page_link'].'?'.http_build_query($parameter, '', '&');
        return $this->cURLexec($target_url, $page_id);
    }

    /**
     * Double Opt-in - check the GUID
     *
     * @param Application $app
     * @param string $guid
     */
    public function ControllerDoubleOptIn(Application $app, $guid)
    {
        $this->initialize($app);

        if (false === ($order = $this->dataOrder->selectByGUID($guid))) {
            $app->abort(404, 'The submitted GUID does not exist.');
        }

        if ($order['status'] !== 'PENDING') {
            $app->abort(410, 'The submitted GUID is no longer valid.');
        }

        $data = unserialize($order['data']);

        $link = substr($data['base']['target_page_link'], strlen($this->dataPage->getPageDirectory()), (strlen($this->dataPage->getPageExtension()) * -1));

        if (false === ($page_id = $this->dataPage->getPageIDbyPageLink($link))) {
            // the CMS page does not exist!
            $message = str_ireplace('%link%', $data['base']['target_page_link'], 'The CMS page <strong>%link%</strong> does not exist!');
            $this->app['monolog']->addError(strip_tags($message), array(__METHOD__, __LINE__));
            $app->abort(404, $message);
        }

        // update order
        $orderdata = array(
            'status' => 'CONFIRMED',
            'confirmation_timestamp' => date('Y-m-d H:i:s')
        );
        $this->dataOrder->update($order['id'], $orderdata);

        $parameter = array(
            'command' => 'minishop',
            'action' => 'guid',
            'sub_action' => 'success',
            'robots' => 'noindex,follow',
            'guid' => $guid
        );

        $queries = $this->app['request']->query->all();
        foreach ($queries as $key => $value) {
            if (!key_exists($key, $parameter) && !in_array($key, self::$ignore_parameters)) {
                // pass all other parameters to the target page
                $parameter[$key] = $value;
            }
        }

        // create the target URL and set the needed parameters
        $target_url = CMS_URL.$data['base']['target_page_link'].'?'.http_build_query($parameter, '', '&');
        return $this->cURLexec($target_url, $page_id);
    }

    public function ControllerPayPalCancel(Application $app, $order_id)
    {
        $this->initialize($app);

        if (false === ($order = $this->dataOrder->select($order_id))) {
            $app->abort(404, 'The submitted Order ID does not exist.');
        }

        if ($order['status'] !== 'PENDING') {
            $app->abort(410, 'The submitted Order ID is no longer valid.');
        }

        $data = unserialize($order['data']);

        $link = substr($data['base']['target_page_link'], strlen($this->dataPage->getPageDirectory()), (strlen($this->dataPage->getPageExtension()) * -1));

        if (false === ($page_id = $this->dataPage->getPageIDbyPageLink($link))) {
            // the CMS page does not exist!
            $message = str_ireplace('%link%', $data['base']['target_page_link'], 'The CMS page <strong>%link%</strong> does not exist!');
            $this->app['monolog']->addError(strip_tags($message), array(__METHOD__, __LINE__));
            $app->abort(404, $message);
        }

        $parameter = array(
            'command' => 'minishop',
            'action' => 'paypal',
            'sub_action' => 'cancel',
            'robots' => 'noindex,follow',
            'order_id' => $order_id
        );

        $queries = $this->app['request']->query->all();
        foreach ($queries as $key => $value) {
            if (!key_exists($key, $parameter) && !in_array($key, self::$ignore_parameters)) {
                // pass all other parameters to the target page
                $parameter[$key] = $value;
            }
        }

        // create the target URL and set the needed parameters
        $target_url = CMS_URL.$data['base']['target_page_link'].'?'.http_build_query($parameter, '', '&');
        return $this->cURLexec($target_url, $page_id);
    }

    public function ControllerPayPalSuccess(Application $app, $order_id)
    {
        $this->initialize($app);

        if (false === ($order = $this->dataOrder->select($order_id))) {
            $app->abort(404, 'The submitted Order ID does not exist.');
        }

        if ($order['status'] !== 'PENDING' && $order['status'] !== 'CONFIRMED') {
            $app->abort(410, 'The submitted Order ID is no longer valid.');
        }

        $data = unserialize($order['data']);

        $link = substr($data['base']['target_page_link'], strlen($this->dataPage->getPageDirectory()), (strlen($this->dataPage->getPageExtension()) * -1));

        if (false === ($page_id = $this->dataPage->getPageIDbyPageLink($link))) {
            // the CMS page does not exist!
            $message = str_ireplace('%link%', $data['base']['target_page_link'], 'The CMS page <strong>%link%</strong> does not exist!');
            $this->app['monolog']->addError(strip_tags($message), array(__METHOD__, __LINE__));
            $app->abort(404, $message);
        }

        $parameter = array(
            'command' => 'minishop',
            'action' => 'paypal',
            'sub_action' => 'success',
            'robots' => 'noindex,follow',
            'order_id' => $order_id
        );

        $queries = $this->app['request']->query->all();
        foreach ($queries as $key => $value) {
            if (!key_exists($key, $parameter) && !in_array($key, self::$ignore_parameters)) {
                // pass all other parameters to the target page
                $parameter[$key] = $value;
            }
        }

        // create the target URL and set the needed parameters
        $target_url = CMS_URL.$data['base']['target_page_link'].'?'.http_build_query($parameter, '', '&');
        return $this->cURLexec($target_url, $page_id);
    }
}

