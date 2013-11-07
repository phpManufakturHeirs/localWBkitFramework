<?php

/**
 * MediaBrowser
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/propangas24
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\MediaBrowser\Control;

use phpManufaktur\MediaBrowser\Control\ImageExtensionFilter;
use phpManufaktur\MediaBrowser\Control\SortedIterator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Validator\Constraints as Assert;

class Browser
{
    protected $app = null;
    protected static $usage = null;
    protected static $usage_param = null;
    protected static $redirect = null;
    protected static $directory_start = null;
    protected static $directory_mode = null;
    protected static $directory = null;
    protected static $file = null;
    protected static $message = '';
    protected static $CKEditorFuncNum = null;

    protected static $allowedExtensions = array('gif','jpg','jpeg','png');
    protected static $icon_width = 150;

    public function __construct()
    {
        global $app;

        $this->app = $app;
        $cms = $this->app['request']->get('usage');
        self::$usage = is_null($cms) ? 'framework' : $cms;
        self::$usage_param = (self::$usage != 'framework') ? '?usage='.self::$usage : '';
        self::$redirect = $this->app['request']->get('redirect');
        self::$directory_start = (is_null($this->app['request']->get('start'))) ? '/' : $this->app['request']->get('start');
        self::$directory_mode = (is_null($this->app['request']->get('mode'))) ? 'public' : $this->app['request']->get('mode');
        self::$directory = (is_null($this->app['request']->get('directory'))) ? self::$directory_start : $this->app['request']->get('directory');
        self::$file = $this->app['request']->get('file');
        self::$CKEditorFuncNum = $this->app['request']->get('CKEditorFuncNum');
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

    public static function getMessage()
    {
        return Browser::$message;
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

    public static function setCKEditorFuncNum($number)
    {
        Browser::$CKEditorFuncNum = $number;
    }

    public static function getCKEditorFuncNum()
    {
        return Browser::$CKEditorFuncNum;
    }

    public static function setMessage($message)
    {
        Browser::$message .= $message;
    }

    public static function clearMessage()
    {
        Browser::$message = '';
    }

    public static function isMessage()
    {
        return !empty(Browser::$message);
    }

      protected function createIcon($fileinfo, &$iconWidth, &$iconHeight)
    {
        list($width, $height, $type) = getimagesize($fileinfo->__toString());
        $media_dir = dirname(substr($fileinfo->__toString(), (self::$directory_mode == 'public') ? strlen(FRAMEWORK_MEDIA_PATH) : strlen(FRAMEWORK_MEDIA_PROTECTED_PATH)));
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
        if (!file_exists($icon_path . $fileinfo->getBasename()) || ($fileinfo->getMTime() != ($mtime = filemtime($icon_path . $fileinfo->getBasename())))) {
            // create a new icon
            if (!file_exists($icon_path)) {
                try {
                    mkdir($icon_path, 0755, true);
                } catch (\ErrorException $ex) {
                    throw new \Exception($this->app['translator']->trans("Can't create the directory <b>%directory%</b>, message: <em>%message%</em>",
                        array('%directory%' => $icon_path.$fileinfo->getBasename(),
                            '%message%' => $ex->getMessage()
                    )));
                }
            }
            $ImageTweak = new ImageTweak();
            $iconPath = $ImageTweak->tweak($fileinfo->getBasename(), strtolower(substr($fileinfo->getBasename(), strrpos($fileinfo->getBasename(), '.') + 1)), $fileinfo->__toString(), $iconWidth, $iconHeight, $width, $height, $fileinfo->getMTime(), $icon_path);
            return substr($iconPath, strlen(FRAMEWORK_PATH));
        }
        return substr($icon_path . $fileinfo->getBasename(), strlen(FRAMEWORK_PATH));
    }

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
                        $select_link = FRAMEWORK_URL.'/admin/mediabrowser/select/'.$params;
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
                            'url' => FRAMEWORK_URL.'/admin/mediabrowser/delete/'.$params
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
                        'change' => FRAMEWORK_URL.'/admin/mediabrowser/directory/'.$params,
                        'delete' => FRAMEWORK_URL.'/admin/mediabrowser/delete/'.$params
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
                    'change' => FRAMEWORK_URL.'/admin/mediabrowser/directory/'.$params,
                    'delete' => null
                )
            );
            array_unshift($directories, $up_link);
        }
        return array('images' => $images, 'directories' => $directories);
    }

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

    public function exec()
    {

        $browse = $this->browseDirectory(FRAMEWORK_PATH.self::$directory);

        // create the form fields for the upload
        $upload = $this->createUploadForm();

        $create_directory = $this->createDirectoryForm();

        switch (self::$usage) {
            case 'CKEditor':
                $message = $this->app['translator']->trans('No file selected!');
                $exit_link = "javascript:returnCKEMessage('$message', '".self::$CKEditorFuncNum."');";
                break;
            default:
                $exit_params = base64_encode(json_encode(array(
                    'usage' => self::getUsage(),
                    'redirect' => self::getRedirect(),
                )));
                $exit_link = FRAMEWORK_URL.'/admin/mediabrowser/exit/'.$exit_params;
                break;
        }

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/MediaBrowser/Template',
            'browser.twig'),
            array(
                'usage' => self::$usage,
                'iframe_add_height' => 35,
                'images' => $browse['images'],
                'directories' => $browse['directories'],
                'message' => Browser::getMessage(),
                'upload' => $upload->createView(),
                'create_directory' => $create_directory->createView(),
                'action' => array(
                    'upload' => FRAMEWORK_URL.'/admin/mediabrowser/upload',
                    'directory' => FRAMEWORK_URL.'/admin/mediabrowser/directory/create',
                    'exit' => $exit_link
                ),
            ));
    }

    public function delete()
    {
        if (!is_null(Browser::getFile())) {
            $delete = FRAMEWORK_PATH.Browser::getFile();
            $mode = 'file';
        }
        elseif (!is_null(Browser::getDirectory())) {
            $delete = FRAMEWORK_PATH.Browser::getDirectory();
            $mode = 'directory';
            // important: set the directory one level up!
            Browser::setDirectory(substr(Browser::getDirectory(), 0, strrpos(Browser::getDirectory(), '/')));
        }
        else {
            throw new \Exception('Got no valid parameter to delete a file or directory.');
        }

        $Filesystem = new Filesystem();

        if (!$Filesystem->exists($delete)) {
            throw new \Exception(sprintf('The directory or file %s does not exists!', $delete));
        }

        $Filesystem->remove($delete);

        if ($mode == 'file')
            $message = $this->app['translator']->trans('<p>The file <b>%file%</b> was successfull deleted.</p>',
                array('%file%' => basename($delete)));
        else
            $message = $this->app['translator']->trans('<p>The directory <b>%directory%</b> was successfull deleted.</p>',
                array('%directory%' => basename($delete)));
        $this->setMessage($message);
        return $this->exec();
    }

    public function upload()
    {
        // get the form values
        $form = $this->createUploadForm();
        $form->bind($this->app['request']);

        Browser::setDirectory($form['directory']->getData());
        Browser::setRedirect($form['redirect']->getData());
        Browser::setUsage($form['usage']->getData());
        Browser::setDirectoryStart($form['start']->getData());
        Browser::setDirectoryMode($form['mode']->getData());
        Browser::setCKEditorFuncNum($form['CKEditorFuncNum']->getData());

        $image = array(
            'File' => $form['media_file']->getData(),
        );

        $constraint = new Assert\Collection(array(
            'File' => new Assert\File(array(
                'maxSize' => '2048k',
            )),
        ));
        $errors = $this->app['validator']->validateValue($image, $constraint);

        if (count($errors) > 0) {
            // validation failed
            $message = '';
            foreach ($errors as $error) {
                $message .= sprintf('<p>%s %s</p>',
                    $error->getPropertyPath(),
                    $error->getMessage());
            }
            $this->setMessage($message);
            return $this->exec();
        }

        if ($form->isValid()) {
            $form['media_file']->getData()->move(FRAMEWORK_PATH.$form['directory']->getData(), $form['media_file']->getData()->getClientOriginalName());
            $this->setMessage($this->app['translator']->trans('<p>The file <b>%file%</b> was successfull uploaded.</p>',
                array('%file%' => $form['media_file']->getData()->getClientOriginalName())));
        }
        else {
            // Ooops, something went wrong ...
            $this->setMessage($this->app['translator']->trans('<p>Ooops, can\'t validate the upload form, something went wrong ...</p>'));
        }
        return $this->exec();
    }

    public function createDirectory()
    {
        // get the form values
        $form = $this->createDirectoryForm();
        $form->bind($this->app['request']);

        Browser::setDirectory($form['directory']->getData());
        Browser::setRedirect($form['redirect']->getData());
        Browser::setUsage($form['usage']->getData());
        Browser::setDirectoryStart($form['start']->getData());
        Browser::setDirectoryMode($form['mode']->getData());
        Browser::setCKEditorFuncNum($form['CKEditorFuncNum']->getData());

        $create_directory = FRAMEWORK_PATH.$this->getDirectory().$this->app['utils']->sanitizePath($form['create_directory']->getData());

        $Filesystem = new Filesystem();
        $Filesystem->mkdir($create_directory);

        $this->setMessage($this->app['translator']->trans('<p>The directory <b>%directory%</b> was successfull created.</p>',
            array('%directory%' => substr($create_directory, strlen(FRAMEWORK_PATH)))));

        return $this->exec();
    }
}
