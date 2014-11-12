<?php

/**
 * MediaBrowser
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/MediaBrowser
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\MediaBrowser\Control;

use phpManufaktur\MediaBrowser\Control\ImageExtensionFilter;
use phpManufaktur\MediaBrowser\Control\SortedIterator;
use Symfony\Component\Validator\Constraints as Assert;
use phpManufaktur\Basic\Control\Pattern\Alert;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;


class Browser extends Alert
{
    protected $app = null;
    protected static $usage = null;
    protected static $usage_param = null;
    protected static $redirect = null;
    protected static $directory_start = null;
    protected static $directory_mode = null;
    protected static $directory = null;
    protected static $file = null;
    protected static $CKEditorFuncNum = null;

    protected static $allowedExtensions = array('gif','jpg','jpeg','png');
    protected static $icon_width = 150;

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Basic\Control\Pattern\Alert::initialize()
     */
    protected function initialize(Application $app)
    {
        parent::initialize($app);

        $cms = $this->app['request']->get('usage');
        self::$usage = is_null($cms) ? 'framework' : $cms;
        self::$usage_param = (self::$usage != 'framework') ? '?usage='.self::$usage : '';
        self::$redirect = $this->app['request']->get('redirect');
        self::$directory_start = (is_null($this->app['request']->get('start'))) ? '/' : $this->app['request']->get('start');
        self::$directory_mode = (is_null($this->app['request']->get('mode'))) ? 'public' : $this->app['request']->get('mode');
        self::$directory = (is_null($this->app['request']->get('directory'))) ? self::$directory_start : $this->app['request']->get('directory');
        self::$file = $this->app['request']->get('file');
        self::$CKEditorFuncNum = $this->app['request']->get('CKEditorFuncNum');

        // set the locale from the CMS locale
        if (self::$usage !== 'framework') {
            $app['translator']->setLocale($this->app['session']->get('CMS_LOCALE', 'en'));
        }
        else {
            $app['translator']->setLocale($this->app['request']->get('locale', 'en'));
        }
    }

    /**
     * @return the $usage
     */
    public static function getUsage ()
    {
        return Browser::$usage;
    }

      /**
     * @return the $redirect
     */
    public static function getRedirect ()
    {
        return Browser::$redirect;
    }

      /**
     * @return the $directory_start
     */
    public static function getDirectoryStart ()
    {
        return Browser::$directory_start;
    }

      /**
     * @return the $directory_mode
     */
    public static function getDirectoryMode ()
    {
        return Browser::$directory_mode;
    }

    /**
     * Get the actual directory
     *
     * @return Ambigous <string, string>
     */
    public static function getDirectory()
    {
        return Browser::$directory;
    }

    /**
     * @return $file
     */
    public static function getFile() {
        return Browser::$file;
    }

    /**
     * @param Ambigous <string, unknown> $usage
     */
    public static function setUsage ($usage=null)
    {
        self::$usage = is_null($usage) ? 'framework' : $usage;
    }

      /**
     * @param field_type $redirect
     */
    public static function setRedirect ($redirect)
    {
        Browser::$redirect = $redirect;
    }

      /**
     * @param string $directory_start
     */
    public static function setDirectoryStart ($directory_start=null)
    {
        self::$directory_start = (is_null($directory_start)) ? '/' : $directory_start;
    }

      /**
     * @param string $directory_mode
     */
    public static function setDirectoryMode ($directory_mode=null)
    {
        Browser::$directory_mode = (is_null($directory_mode)) ? 'public' : $directory_mode;
    }

    /**
     * Set the actual directory
     *
     * @param string $directory
     */
    public static function setDirectory($directory=null)
    {
        Browser::$directory = (is_null($directory)) ? Browser::$directory_start : $directory;
    }

    /**
     *
     * @param string $file
     */
    public static function setFile($file) {
        Browser::$file = $file;
    }

    /**
     * Set the CK Editor function number
     *
     * @param integer $number
     */
    public static function setCKEditorFuncNum($number)
    {
        Browser::$CKEditorFuncNum = $number;
    }

    /**
     * Get the CK Editor function number
     *
     * @return integer
     */
    public static function getCKEditorFuncNum()
    {
        return Browser::$CKEditorFuncNum;
    }

    /**
     * Check if the User is authenticated and allowed to access the MediaBrowser
     *
     * @return boolean
     */
    protected function checkAuthentication()
    {
        if (!$this->app['account']->isAuthenticated()) {
            $this->setAlert('Your are not authenticated, please login!', array(), Alert::ALERT_TYPE_WARNING);
            return false;
        }

        if ($this->app['account']->isGranted('ROLE_MEDIABROWSER_ADMIN') ||
            $this->app['account']->isGranted('ROLE_MEDIABROWSER_USER')) {
            // the user is allowed to access the MediaBrowser
            return true;
        }

        $this->setAlert('You are not allowed to access this resource!', array(), Alert::ALERT_TYPE_WARNING);
        return false;
    }

    /**
     * Create a new icon
     *
     * @param \Iterator $fileinfo
     * @param integer $iconWidth
     * @param integer $iconHeight
     * @throws \Exception
     * @return string
     */
    protected function createIcon($fileinfo, &$iconWidth, &$iconHeight)
    {
        $source_path = $this->app['utils']->sanitizePath($fileinfo->__toString());
        list($width, $height, $type) = getimagesize($source_path);
        $media_dir = dirname(substr($source_path, (self::$directory_mode == 'public') ? strlen(FRAMEWORK_MEDIA_PATH) : strlen(FRAMEWORK_MEDIA_PROTECTED_PATH)));
        // create Icon
        $icon_path = $this->app['utils']->sanitizePath(FRAMEWORK_TEMP_PATH. '/media_browser/icon'. $media_dir);
        $icon_path = substr($icon_path, strlen($icon_path) - 1, 1) == DIRECTORY_SEPARATOR ? $icon_path : $icon_path.DIRECTORY_SEPARATOR;

        // create icon image
        if ($width > self::$icon_width) {
            // calculate size for icon
            $percent = (int) (self::$icon_width / ($width / 100));
            $iconWidth = self::$icon_width;
            $iconHeight = (int) ($height / (100) * $percent);
        }
        else {
            // use orginal image dimensions
            $iconWidth = $width;
            $iconHeight = $height;
        }
        if (!$this->app['filesystem']->exists($icon_path . $fileinfo->getBasename()) || ($fileinfo->getMTime() != ($mtime = filemtime($icon_path . $fileinfo->getBasename())))) {
            // create a new icon
            if (!$this->app['filesystem']->exists($icon_path)) {
                try {
                    $this->app['filesystem']->mkdir($icon_path);
                } catch (\ErrorException $ex) {
                    throw new \Exception($this->app['translator']->trans("Can't create the directory <b>%directory%</b>, message: <em>%message%</em>",
                        array('%directory%' => $icon_path.$fileinfo->getBasename(),
                            '%message%' => $ex->getMessage()
                    )));
                }
            }
            $this->app['image']->resampleImage(
                $source_path,
                $type,
                $width,
                $height,
                $icon_path.$fileinfo->getBasename(),
                $iconWidth,
                $iconHeight
            );
            return substr($icon_path.$fileinfo->getBasename(), strlen(FRAMEWORK_PATH));
        }
        return substr($icon_path . $fileinfo->getBasename(), strlen(FRAMEWORK_PATH));
    }

    /**
     * Browse a directory within the MediaBrowser
     *
     * @param string $directory
     * @throws \Exception
     * @return array
     */
    protected function browseDirectory($directory)
    {
        if (is_null(self::$redirect)) {
            throw new \Exception('MediaBrowser need the parameter "redirect"!');
        }

        $images = array();
        $directories = array();
        $recursiveIterator = new \RecursiveDirectoryIterator($directory);
        $sortedIterator = new SortedIterator($recursiveIterator);
        $iterator = new ImageExtensionFilter($sortedIterator, self::$allowedExtensions);
        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isFile()) {
                $file_path = $this->app['utils']->sanitizePath($fileinfo->__toString());
                $iconWidth = 0;
                $iconHeight = 0;
                $icon_file = $this->createIcon($fileinfo, $iconWidth, $iconHeight);
                $params = base64_encode(json_encode(array(
                    'redirect' => self::getRedirect(),
                    'file' => substr($file_path, strlen(FRAMEWORK_PATH)),
                    'usage' => self::getUsage(),
                    'mode' => self::getDirectoryMode(),
                    'start' => self::getDirectoryStart(),
                    'directory' => self::getDirectory(),
                    'CKEditorFuncNum' => self::getCKEditorFuncNum(),
                )));
                switch (self::$usage) {
                    case 'CKEditor':
                        $file = FRAMEWORK_URL . substr($file_path, strlen(FRAMEWORK_PATH));
                        $select_link = "javascript:returnCKEFile('$file', '".self::$CKEditorFuncNum."');";
                        break;
                    default:
                        $select_link = FRAMEWORK_URL.'/mediabrowser/select/'.$params;
                }
                list($width, $height, $type) = getimagesize($file_path);
                $images[] = array(
                    'basename' => $fileinfo->getBasename(),
                    'modified' => $fileinfo->getMTime(),
                    'path' => $file_path,
                    'size' => array(
                        'bytes' => $fileinfo->getSize(),
                        'string' => $this->app['utils']->bytes2string($fileinfo->getSize())
                    ),
                    'dimension' => array(
                        'width' => $width,
                        'height' => $height,
                        'string' => sprintf('%d x %d Px', $width, $height)
                    ),
                    'link' => array(
                        'select' => array(
                            'url' => $select_link
                        ),
                        'delete' => array(
                            'url' => FRAMEWORK_URL.'/mediabrowser/delete/'.$params
                        )
                    ),
                    'icon' => array(
                        'url' => FRAMEWORK_URL.$icon_file,
                        'width' => $iconWidth,
                        'height' => $iconHeight
                    )
                );
            }
            elseif ($fileinfo->isDir()) {
                if (($fileinfo->getBasename() == '.') || ($fileinfo->getBasename() == '..')) continue;
                $file_path = $this->app['utils']->sanitizePath($fileinfo->__toString());
                $params = base64_encode(json_encode(array(
                    'redirect' => self::getRedirect(),
                    'directory' => substr($file_path, strlen(FRAMEWORK_PATH)),
                    'usage' => self::getUsage(),
                    'mode' => self::getDirectoryMode(),
                    'start' => self::getDirectoryStart(),
                    'CKEditorFuncNum' => self::getCKEditorFuncNum(),
                )));

                $directories[] = array(
                    'basename' => $fileinfo->getBasename(),
                    'link' => array(
                        'change' => FRAMEWORK_URL.'/mediabrowser/directory/'.$params,
                        'delete' => FRAMEWORK_URL.'/mediabrowser/delete/'.$params
                    )
                );
            }
        }
        if (self::$directory != self::$directory_start) {
            // add a directory up entry
            $params = base64_encode(json_encode(array(
                'redirect' => self::getRedirect(),
                'directory' => substr(substr($directory, 0, strrpos($directory, '/')), strlen(FRAMEWORK_PATH)),
                'usage' => self::getUsage(),
                'mode' => self::getDirectoryMode(),
                'start' => self::getDirectoryStart(),
                'CKEditorFuncNum' => self::getCKEditorFuncNum(),
            )));
            $up_link = array(
                'basename' => '..',
                'link' => array(
                    'change' => FRAMEWORK_URL.'/mediabrowser/directory/'.$params,
                    'delete' => null
                )
            );
            array_unshift($directories, $up_link);
        }
        return array('images' => $images, 'directories' => $directories);
    }

    /**
     * Create a form to upload media files
     *
     */
    protected function createUploadForm()
    {
        $data = array(
            'redirect' => Browser::getRedirect(),
            'usage' => Browser::getUsage(),
            'start' => Browser::getDirectoryStart(),
            'mode' => Browser::getDirectoryMode(),
            'directory' => Browser::getDirectory(),
            'CKEditorFuncNum' => Browser::getCKEditorFuncNum(),
        );

        return $this->app['form.factory']->createBuilder('form', $data)
        ->add('media_file', 'file', array(
            'label' => 'Upload file'
        ))
        ->add('redirect', 'hidden')
        ->add('usage', 'hidden')
        ->add('start', 'hidden')
        ->add('mode', 'hidden')
        ->add('directory', 'hidden')
        ->add('CKEditorFuncNum', 'hidden')
        ->getForm();
    }

    /**
     * Create a form to create a directory
     *
     */
    protected function createDirectoryForm()
    {
        $data = array(
            'redirect' => Browser::getRedirect(),
            'usage' => Browser::getUsage(),
            'start' => Browser::getDirectoryStart(),
            'mode' => Browser::getDirectoryMode(),
            'directory' => Browser::getDirectory(),
            'CKEditorFuncNum' => Browser::getCKEditorFuncNum(),
        );

        return $this->app['form.factory']->createBuilder('form', $data)
        ->add('create_directory', 'text', array(
            'label' => 'Create directory'
        ))
        ->add('redirect', 'hidden')
        ->add('usage', 'hidden')
        ->add('start', 'hidden')
        ->add('mode', 'hidden')
        ->add('directory', 'hidden')
        ->add('CKEditorFuncNum', 'hidden')
        ->getForm();
    }

    /**
     * Show the Browser Dialog
     *
     * @return string MediaBrowser
     */
    public function showBrowser()
    {
        $browse = $this->browseDirectory(FRAMEWORK_PATH.self::$directory);

        // create the form fields for the upload
        $upload = $this->createUploadForm();

        $create_directory = $this->createDirectoryForm();

        switch (self::$usage) {
            case 'CKEditor':
                $msg = $this->app['translator']->trans('No file selected!');
                $exit_link = "javascript:returnCKEMessage('$msg', '".self::$CKEditorFuncNum."');";
                break;
            default:
                $exit_params = base64_encode(json_encode(array(
                    'usage' => self::getUsage(),
                    'redirect' => self::getRedirect(),
                )));
                $exit_link = FRAMEWORK_URL.'/mediabrowser/exit/'.$exit_params;
                break;
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/MediaBrowser/Template',
            'browser.twig'),
            array(
                'actual_directory' => self::$directory,
                'usage' => self::$usage,
                'iframe_add_height' => 35,
                'images' => $browse['images'],
                'directories' => $browse['directories'],
                'alert' => $this->getAlert(),
                'upload' => $upload->createView(),
                'create_directory' => $create_directory->createView(),
                'action' => array(
                    'upload' => FRAMEWORK_URL.'/mediabrowser/upload',
                    'directory' => FRAMEWORK_URL.'/mediabrowser/directory/create',
                    'exit' => $exit_link
                ),
            ));
    }

    /**
     * General controller for the MediaBrowser
     *
     * @param Application $app
     */
    public function ControllerMediaBrowser(Application $app)
    {
        $this->initialize($app);

        if (!$this->checkAuthentication()) {
            return $this->promptAlert();
        }

        return $this->showBrowser();
    }

    public function ControllerEntryPoints(Application $app)
    {
        $subRequest = Request::create('/mediabrowser', 'GET', array(
            'usage' => 'framework',
            'start' => '/',
            'redirect' => '/',
            'mode' => 'public',
            'directory' => '/',
            'locale' => $app['translator']->getLocale()
        ));
        return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * Initialize the MediaBrowser with a base64 encoded parameter string
     *
     * @param Application $app
     * @param string $params
     */
    public function ControllerMediaBrowserInit(Application $app, $params)
    {
        $this->initialize($app);

        if (!$this->checkAuthentication()) {
            return $this->promptAlert();
        }

        $parameter = json_decode(base64_decode($params), true);
        self::$usage = (isset($parameter['usage'])) ? $parameter['usage'] : 'framework';
        self::$directory_start = (isset($parameter['start'])) ? $parameter['start'] : '/';
        self::$redirect = (isset($parameter['redirect'])) ? $parameter['redirect'] : null;
        self::$directory_mode = (isset($parameter['mode'])) ? $parameter['mode'] : 'public';
        self::$directory = (isset($parameter['directory'])) ? $parameter['directory'] : null;

        return $this->showBrowser();
    }

    /**
     * Controller to delete a file or directory
     *
     * @param Application $app
     * @param string $delete base64 encoded JSON Parameter string
     * @throws \Exception
     */
    public function ControllerMediaBrowserDelete(Application $app, $delete)
    {
        $this->initialize($app);

        if (!$this->checkAuthentication()) {
            return $this->promptAlert();
        }

        $parameter = json_decode(base64_decode($delete), true);
        self::$usage = (isset($parameter['usage'])) ? $parameter['usage'] : 'framework';
        self::$directory_start = (isset($parameter['start'])) ? $parameter['start'] : '/';
        self::$redirect = (isset($parameter['redirect'])) ? $parameter['redirect'] : null;
        self::$directory_mode = (isset($parameter['mode'])) ? $parameter['mode'] : 'public';
        self::$directory = (isset($parameter['directory'])) ? $parameter['directory'] : null;
        self::$file = (isset($parameter['file'])) ? $parameter['file'] : null;
        self::$CKEditorFuncNum = isset($parameter['CKEditorFuncNum']) ? $parameter['CKEditorFuncNum'] : null;

        if (!is_null(self::$file)) {
            $delete = FRAMEWORK_PATH.self::$file;
            $mode = 'file';
        }
        elseif (!is_null(self::$directory)) {
            $delete = FRAMEWORK_PATH.self::$directory;
            $mode = 'directory';
            // important: set the directory one level up!
            self::$directory = substr(self::$directory, 0, strrpos(self::$directory, '/'));
        }
        else {
            throw new \Exception('Got no valid parameter to delete a file or directory.');
        }

        if (!$this->app['filesystem']->exists($delete)) {
            throw new \Exception(sprintf('The directory or file %s does not exists!', $delete));
        }

        $this->app['filesystem']->remove($delete);

        if ($mode == 'file') {
            $this->setAlert('The file %file% was successfull deleted.',
                array('%file%' => basename($delete), self::ALERT_TYPE_SUCCESS));
        }
        else {
            $this->setAlert('The directory %directory% was successfull deleted.',
                array('%directory%' => basename($delete), self::ALERT_TYPE_SUCCESS));
        }
        return $this->showBrowser();
    }

    /**
     * Controller to change the directory
     *
     * @param Application $app
     * @param string $change base64 encoded JSON parameter string
     */
    public function ControllerMediaBrowserChangeDirectory(Application $app, $change)
    {
        $this->initialize($app);

        if (!$this->checkAuthentication()) {
            return $this->promptAlert();
        }

        $parameter = json_decode(base64_decode($change), true);
        self::$usage = (isset($parameter['usage'])) ? $parameter['usage'] : 'framework';
        self::$directory_start = (isset($parameter['start'])) ? $parameter['start'] : '/';
        self::$redirect = (isset($parameter['redirect'])) ? $parameter['redirect'] : null;
        self::$directory_mode = (isset($parameter['mode'])) ? $parameter['mode'] : 'public';
        self::$directory = (isset($parameter['directory'])) ? $parameter['directory'] : null;
        self::$CKEditorFuncNum = isset($parameter['CKEditorFuncNum']) ? $parameter['CKEditorFuncNum'] : null;

        return $this->showBrowser();
    }

    /**
     * Controller to create the directory
     *
     * @param Application $app
     * @return string
     */
    public function ControllerMediaBrowserCreateDirectory(Application $app)
    {
        $this->initialize($app);

        if (!$this->checkAuthentication()) {
            return $this->promptAlert();
        }

        // get the form values
        $form = $this->createDirectoryForm();
        $form->bind($this->app['request']);
        $parameter = $form->getData();

        self::$usage = (isset($parameter['usage'])) ? $parameter['usage'] : 'framework';
        self::$directory_start = (isset($parameter['start'])) ? $parameter['start'] : '/';
        self::$redirect = (isset($parameter['redirect'])) ? $parameter['redirect'] : null;
        self::$directory_mode = (isset($parameter['mode'])) ? $parameter['mode'] : 'public';
        self::$directory = (isset($parameter['directory'])) ? $parameter['directory'] : null;
        self::$CKEditorFuncNum = isset($parameter['CKEditorFuncNum']) ? $parameter['CKEditorFuncNum'] : null;

        $create_directory = FRAMEWORK_PATH.self::$directory.$this->app['utils']->sanitizePath($parameter['create_directory']);

        $this->app['filesystem']->mkdir($create_directory);

        $this->setAlert('The directory %directory% was successfull created.',
            array('%directory%' => substr($create_directory, strlen(FRAMEWORK_PATH))), self::ALERT_TYPE_SUCCESS);

        return $this->showBrowser();
    }

    /**
     * Controller to upload a media file
     *
     * @param Application $app
     */
    public function ControllerMediaBrowserUpload(Application $app)
    {
        $this->initialize($app);

        if (!$this->checkAuthentication()) {
            return $this->promptAlert();
        }

        // get the form values
        $form = $this->createUploadForm();
        $form->bind($this->app['request']);
        $parameter = $form->getData();

        self::$usage = (isset($parameter['usage'])) ? $parameter['usage'] : 'framework';
        self::$directory_start = (isset($parameter['start'])) ? $parameter['start'] : '/';
        self::$redirect = (isset($parameter['redirect'])) ? $parameter['redirect'] : null;
        self::$directory_mode = (isset($parameter['mode'])) ? $parameter['mode'] : 'public';
        self::$directory = (isset($parameter['directory'])) ? $parameter['directory'] : null;
        self::$CKEditorFuncNum = isset($parameter['CKEditorFuncNum']) ? $parameter['CKEditorFuncNum'] : null;

        $image = array(
            'File' => $parameter['media_file'],
        );

        $constraint = new Assert\Collection(array(
            'File' => new Assert\File(array(
                'maxSize' => '2048k',
            )),
        ));
        $errors = $this->app['validator']->validateValue($image, $constraint);

        if (count($errors) > 0) {
            // validation failed
            foreach ($errors as $error) {
                $this->setAlert('%path% %error%', array(
                    '%path%' => $error->getPropertyPath(),
                    '%error%' => $error->getMessage()), self::ALERT_TYPE_WARNING);
            }
            return $this->showBrowser();
        }

        if ($form->isValid()) {
            $form['media_file']->getData()->move(
                $this->app['utils']->sanitizePath(FRAMEWORK_PATH.$parameter['directory']),
                $this->app['utils']->sanitizePath($form['media_file']->getData()->getClientOriginalName()));
            $this->setAlert('The file %file% was successfull uploaded.',
                array('%file%' => $form['media_file']->getData()->getClientOriginalName()), self::ALERT_TYPE_SUCCESS);
        }
        else {
            // Ooops, something went wrong ...
            $this->setAlert("Ooops, can't validate the upload form, something went wrong ...", array(), self::ALERT_TYPE_DANGER);
        }
        return $this->ShowBrowser();
    }

    /**
     * Controller to return the selected file to the calling application
     *
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ControllerMediaBrowserSelect(Application $app, $select)
    {
        $this->initialize($app);

        if (!$this->checkAuthentication()) {
            return $this->promptAlert();
        }

        $parameter = json_decode(base64_decode($select), true);
        $subRequest = Request::create($parameter['redirect'], 'GET', array(
            'usage' => (isset($parameter['usage'])) ? $parameter['usage'] : 'framework',
            'file' => (isset($parameter['file'])) ? $parameter['file'] : null
        ));
        return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * Controller to exit the MediaBrowser und redirect to the calling application
     *
     * @param Application $app
     * @param string $usage base64 encoded JSON parameter string
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ControllerMediaBrowserExit(Application $app, $usage)
    {
        $this->initialize($app);

        if (!$this->checkAuthentication()) {
            return $this->promptAlert();
        }

        $parameter = json_decode(base64_decode($usage), true);
        $subRequest = Request::create($parameter['redirect'], 'GET', array(
            'usage' => (isset($parameter['usage'])) ? $parameter['usage'] : 'framework',
        ));
        return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * Controller to execute the MediaBrowser from the CKEditor
     *
     * @param Application $app
     */
    public function ControllerMediaBrowserCKE(Application $app)
    {
        $this->initialize($app);

        if (!$this->checkAuthentication()) {
            return $this->promptAlert();
        }


        self::$usage = 'CKEditor';
        self::$directory = $app['request']->query->get('directory', '/media/public');
        self::$directory_start = $app['request']->query->get('directory_start', '/media/public');
        self::$redirect = '/mediabrowser/cke';
        self::$directory_mode = 'public';
        self::$CKEditorFuncNum = $this->app['request']->get('CKEditorFuncNum');

        return $this->showBrowser();
    }
}
