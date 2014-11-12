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

class ImageTweak
{
    protected $app = null;

    public function __construct()
    {
        global $app;
        $this->app = $app;
    }

    /**
     * Master routine from imageTweak to create optimized images.
     *
     * @see http://phpmanufaktur.de/image_tweak
     *
     * @param string $filename basename of the image
     * @param string $extension extension of the image (without dot)
     * @param string $file_path complete path to the image
     * @param integer $new_width the new width in pixel
     * @param integer $new_height the new height in pixel
     * @param integer $origin_width the original width in pixel
     * @param integer $origin_height the original height in pixel
     * @param integer $origin_filemtime the FileMTime of the image
     * @param string $new_path the path to the tweaked image
     * @return mixed path to the new file on succes, boolean false on error
     */
    public function tweak($filename, $extension, $file_path, $new_width, $new_height, $origin_width, $origin_height, $origin_filemtime, $new_path) {

        $extension = strtolower($extension);
        switch ($extension) {
            case 'gif':
                $origin_image = imagecreatefromgif($file_path);
                break;
            case 'jpeg':
            case 'jpg':
                $origin_image = imagecreatefromjpeg($file_path);
                break;
            case 'png':
                $origin_image = imagecreatefrompng($file_path);
                break;
            default :
                // unsupported image type
                throw new \Exception($this->app['translator']->trans('The file extension %extension% is not supported!',
                        array('%extension%' => $extension)));
        }

        // create new image of $new_width and $new_height
        $new_image = imagecreatetruecolor($new_width, $new_height);

        // Check if this image is PNG or GIF, then set if Transparent
        if (($extension == 'gif') or ($extension == 'png')) {
            imagealphablending($new_image, false);
            imagesavealpha($new_image, true);
            $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
            imagefilledrectangle($new_image, 0, 0, $new_width, $new_height, $transparent);
        }

        // resample image
        imagecopyresampled($new_image, $origin_image, 0, 0, 0, 0, $new_width, $new_height, $origin_width, $origin_height);

        if (!file_exists($new_path)) {
            try {
                mkdir($new_path, 0755, true);
            } catch (\ErrorException $ex) {
                throw new \Exception($this->app['translator']->trans("Can't create the directory <b>%directory%</b>, message: <em>%message%</em>",
                    array('%directory%' => $new_path, '%message%' => $ex->getMessage())));
            }
        }

        $new_file = $new_path . $filename;
        // Generate the file, and rename it to $newfilename
        switch ($extension) {
            case 'gif':
                imagegif($new_image, $new_file);
                break;
            case 'jpg':
            case 'jpeg':
                // static setting for the JPEG Quality
                imagejpeg($new_image, $new_file, 90);
                break;
            case 'png':
                imagepng($new_image, $new_file);
                break;
            default:
                // unsupported image type
                throw new \Exception($this->app['translator']->trans('The file extension %extension% is not supported!',
                    array('%extension%' => $extension)));
        }

        if (!chmod($new_file, 0644)) {
            throw new \Exception($this->app['translator']->trans("Can't change the access rights for the file <b>%file%</b>!",
                array('%file%' => basename($new_file))));
        }

        if (($origin_filemtime !== false) && (touch($new_file, $origin_filemtime) === false)) {
            throw new \Exception($this->app['translator']->trans("Can't change the last modification time for the file <b>%file%</b>!",
                array('%file%' => basename($new_file))));
        }

        return $new_file;
    } // tweak()

}
