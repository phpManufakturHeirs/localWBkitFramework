<?php

/**
 * FacebookGallery
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/FacebookGallery
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\FacebookGallery\Control;

use phpManufaktur\Basic\Control\kitCommand\Basic as kitCommand;
use phpManufaktur\Basic\Control\kitCommand\Help;
use Silex\Application;

class Gallery extends kitCommand {

    const USERAGENT = 'kitFramework:FacebookGallery';

    protected static $limit = null;
    protected static $size = null;
    protected static $description = null;

    protected function execFacebookGraph($command, &$result = array(), &$info = array())
    {
        if (false === ($ch = curl_init())) {
            throw new \Exception("Can't create a cURL resource!");
        }

        if (!curl_setopt_array($ch, array(
            CURLOPT_URL => 'https://graph.facebook.com/'.$command,
            CURLOPT_USERAGENT => self::USERAGENT,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false
        ))) {
            throw new \Exception("Can't set the cURL options!");
        }

        // set proxy if needed
        $this->app['utils']->setCURLproxy($ch);

        if (false === ($content = curl_exec($ch))) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception("cURL execution error: $error");
        }
        // get informations about the cURL execution
        $info = curl_getinfo($ch);
        // close the handle
        curl_close($ch);
        // decode the content
        $result = json_decode($content,true);
        return (!isset($info['http_code']) || ($info['http_code'] != '200')) ? false : true;
    }

    protected function getGallery($gallery_id)
    {
        $parameter = $this->app['request']->query->all();

        $limit = (isset($parameter['limit'])) ? $parameter['limit'] : self::$limit;
        $before = (isset($parameter['before'])) ? '&before='.$parameter['before'] : '';
        $after = (isset($parameter['after'])) ? '&after='.$parameter['after'] : '';

        $command = "$gallery_id/photos?limit=$limit$before$after";
        $result = array();
        $info = array();
        $this->execFacebookGraph($command, $result, $info);
        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/FacebookGallery/Template',
            'gallery.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'gallery_id' => $gallery_id,
                'basic' => $this->getBasicSettings(),
                'gallery' => $result,
                'parameter' => array(
                    'limit' => $limit,
                    'size' => self::$size,
                    'description' => self::$description,
                )
            ));
    }

    protected function getList($facebook_id)
    {
        $command = sprintf('%s/albums?fields=id,name,type&limit=1000', $facebook_id);
        $result = array();
        $info = array();
        $this->execFacebookGraph($command, $result, $info);

        if (isset($result['error'])) {
            if ($result['error']['code'] == '102') {
                // OAuth Error - will raise at private Facebook pages
                $error = sprintf('[ %d - %s ] %s', $result['error']['code'], $result['error']['type'], $result['error']['message'])."<br />";
                $error .= $this->app['translator']->trans('Hint: This error occur if you try to access protected Facebook galleries, which belong to private account. The kitFramework FacebookGallery does only support galleries which belong to fanpages and public pages.');
                throw new \Exception($error);
            }
            else {
                // all other errors
                throw new \Exception(sprintf('[ %d - %s ] %s', $result['error']['code'], $result['error']['type'], $result['error']['message']));
            }
        }
        return $this->app['twig']->render($this->app['utils']->getTemplateFile('@phpManufaktur/FacebookGallery/Template', 'list.twig', $this->getPreferredTemplateStyle()),
            array(
                'facebook_id' => $facebook_id,
                'basic' => $this->getBasicSettings(),
                'galleries' => $result
            ));
    }

    public function exec(Application $app)
    {
        $this->initParameters($app);

        try {
            // get the kitCommand parameters
            $parameter = $this->getCommandParameters();

            // exists a settings.json ?
            $style = $this->getPreferredTemplateStyle();
            if (file_exists(MANUFAKTUR_PATH."/FacebookGallery/Template/$style/settings.json")) {
                $settings = $this->app['utils']->readConfiguration(MANUFAKTUR_PATH."/FacebookGallery/Template/$style/settings.json");
                // set default parameter but do not override them!
                foreach ($settings['parameter'] as $key => $value) {
                    if (!isset($parameter[$key])) {
                        $parameter[$key] = $value;
                    }
                }
            }

            self::$limit = (isset($parameter['limit'])) ? (int) $parameter['limit'] : 200;
            self::$size = (isset($parameter['size'])) ? (int) $parameter['size'] : 7;
            self::$description = (isset($parameter['description']) && ((strtolower($parameter['description']) == 'false') || ($parameter['description'] == '0'))) ? false : true;

            if (isset($parameter['id'])) {
                // return the Facebook gallery
                return $this->getGallery($parameter['id']);
            }
            elseif (isset($parameter['account'])) {
                // return the Facebook albums for the given facebook ID
                return $this->getList($parameter['account']);
            }
            else {
                // no parameter set, so get the help function and give a hint for the user
                $help = new Help($this->app);
                return $this->app['twig']->render($this->app['utils']->getTemplateFile('@phpManufaktur/FacebookGallery/Template', 'help.twig', $this->getPreferredTemplateStyle()),
                    array(
                        'basic' => $this->getBasicSettings(),
                        'help' => $help->getContent(MANUFAKTUR_PATH.'/FacebookGallery/command.facebookgallery.json')
                    ));
            }
        } catch (\Exception $e) {
            return $this->app['twig']->render($this->app['utils']->getTemplateFile('@phpManufaktur/FacebookGallery/Template', 'error.twig', $this->getPreferredTemplateStyle()),
                array(
                    'basic' => $this->getBasicSettings(),
                    'error' => array(
                        'file' => substr($e->getFile(), strlen(MANUFAKTUR_PATH)+1),
                        'line' => $e->getLine(),
                        'message' => $e->getMessage()
                    )
                ));
        }
    }
}
