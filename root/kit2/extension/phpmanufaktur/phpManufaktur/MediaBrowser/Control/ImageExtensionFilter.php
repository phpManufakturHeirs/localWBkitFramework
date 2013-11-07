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

/**
 * FilterIterator for parsing images in the /MEDIA directory
 *
 * @author phpManufaktur, ralf.hertsch@phpmanufaktur.de
 */
class ImageExtensionFilter extends \FilterIterator
{

    private $extensions;

    /**
     * Constructor imageExtensionFilter
     * Specify the allowed file extensions in the array $allowed_extensions
     *
     * @param iterator $iterator
     * @param array $allowed_extensions
     */
    public function __construct($iterator, $allowed_extensions = array())
    {
        $this->setExtensions(implode('|', $allowed_extensions));
        parent::__construct($iterator);
    } // __construct()

    /**
     *
     * @return the $extensions
     */
    protected function getExtensions()
    {
        return $this->extensions;
    }

    /**
     *
     * @param field_type $extensions
     */
    protected function setExtensions($extensions)
    {
        $this->extensions = $extensions;
    }

    /**
     * Set the accepted filter to the desired extension array and to directories
     * to enable recursing through the /MEDIA directory.
     *
     * @see FilterIterator::accept()
     */
    public function accept()
    {
        $filter = sprintf('%%.(%s)%%si', $this->getExtensions());
        return (($this->current()->isFile() && preg_match($filter, $this->current()->getBasename()) || $this->current()->isDir()));
    } // accept()

} // class ImageExtensionFilter
