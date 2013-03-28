<?php

/**
 * CREATE IMAGE THUMBNAIL
 * 
 * ----------------------------------------------------------------------------------------
 * Source: PHP Solutions Dynamic Design Made Easy 2nd Edition / David Powers/ FriendsofEd(Apress)/ 2010/ Chapter 8
 * Source: PHP for Absolute Beginners / Jason Lengstorf/ Apress/ 2009/ Chapter 8
 * Version : 1.0
 * Date : 14/02/2013
 * Modified date: 22/02/2013
 * @author Nguyen Nhu Quan FunkyCoder <bsquan2009@gmail.com>
 * ------------------------------------------------------------------------------------------
 * PARAMETERS:
 * setDestination($destination)    array : destination to store thumbnail
 * setImageMaxSize($sizeArray)     array : Max image dimensions in array
 * setSuffix($suffix)              string: set $suffix used for thumbnail image
 * getMessages()                   array : return warning messages
 * create()                                create image file at destination
 * -----------------------------------------------------------------------------------------
 * USAGE:
 * $thumb = new Thumbnail($fullPathToOriginalImage)
 * $thumb->setDestination($destination)
 * $thumb->setImageMaxSize(array(800,600))
 * $thumb->setSuffix("thumb");
 * $thumb->create()
 * 
 * -----------------------------------------------------------------------------------------
 * PERSONAL NOTES:
 * 22/02/2013: Finished week 2 peer assigments of Clinical Problem Solving. Whew!
 */

namespace Mii;

require_once 'File.inc.php';

class Mii_Thumbnail {

    //path to original image
    protected $_original = '';
    protected $_destination = '';
    protected $_imageSize = array('originalWidth' => 0, 'originalHeight' => 0, 'thumbWidth' => 0, 'thumbHeight' => 0);
    // Array for max dimensions of images (Default is 350x240) if resize image = true
    protected $_maxSize = array('maxWidth' => 350, 'maxHeight' => 240);
    protected $_canProcess = FALSE;
    protected $_imageType = '';
    //thumbnail name
    protected $_thumbName = '';
    protected $_suffix = '_thumb';
    protected $_messages = array();
    //Store image functions needed for images operation
    protected $_imageFunctions = array();

    //get source image full path
    public function __construct($image) {
        if (is_file($image) && is_readable($image)) {
            $details = getimagesize($image);
            // if getimagesize() return an array,it looks like an image
            if (is_array($details)) {
                $this->_original = $image;
                $this->_imageSize['originalWidth'] = $details[0];
                $this->_imageSize['originalHeight'] = $details[1];
                // if original width or height equals 0 then the getimagesize() function could not determine the size of the image
                if ($this->_imageSize['originalWidth'] == 0) {
                    $this->_messages[] = "Can not determine $image size.";
                } else {
                    //check the image type and set appropiate functions to handle images
                    $this->setImageFunctions($details['mime']);
                    // if imageFunctions is set then this image type is supported for resizing
                    if ($this->_imageFunctions) {
                        $this->_canProcess = TRUE;
                        //Set default _destination = the parent folder
                        $this->_destination = Mii_File::addSlashToPathName(dirname($image));
                    } else {
                        $this->_messages[] = "Resizing " . basename($image) . " is not supported by the system.";
                    }
                }
            } else {
                $this->_messages[] = "$image doesn't appear to be an image.";
            }
        } else {
            $this->_messages[] = "Can not read $image.";
        }
    }

    public function setDestination($destination) {
        if (is_dir($destination) && is_writable($destination)) {
            $this->_destination = Mii_File::addSlashToPathName($destination);
        } else {
            $this->_messages[] = "Can not write to $destination. Current path is used instead.";
        }
    }

    public function setImageMaxSize($sizeArray) {
        if (!is_array($sizeArray)) {
            $this->_messages[] = "Error! Max image size must be an array. Default values 350x240 used.";
        } else {
            $this->_maxSize = $sizeArray;
        }
    }

    public function setSuffix($suffix) {
        //matches a string that contains only alphanumeric characters and underscores
        if (preg_match('/^\w+$/', $suffix)) {
            //if $suffix doesnt contain an underscore, strpos()returns false
            //so the condition needs to use the “not identical” operator (with two equal signs)
            if (strpos($suffix, '_') !== 0) {
                //if the suffix doesnt begin with an underscore, one is added
                $this->_suffix = '_' . $suffix;
            } else {
                $this->_suffix = $suffix;
            }
        } else {
            $this->_suffix = '';
        }
    }

    public function getMessages() {
        return $this->_messages;
    }

    public function create() {
        if ($this->_canProcess) {
            $this->calculateSize();
            $this->setThumbName();
            $this->createThumbnail();
        }
    }

//
// PROTECTED FUNCTIONS START HERE
//
    /**
     * Create the resize image name and store it to $this->_thumbName
     *      
     * Version : 1.0
     * Date : 22/02/2013
     * Author : Nguyen Nhu Quan
     * Modified date: 
     * Modified by : 
     * Reason: 
     * @return void
     */
    protected function setThumbName() {
        //Prepare possible image file extensions
        $extensions = array('/\.jpg$/i', '/\.jpeg$/i', '/\.png$/i', '/\.gif$/i');
        //Get the original file name without $extensions
        //basename extract file name from a path
        $name = preg_replace($extensions, '', basename($this->_original));
        $fileExtension = '.' . $this->_imageType;
        //Construct the name
        $fullName = $name . $this->_suffix . $fileExtension;
        // Set the _thumbName
        $this->_thumbName = $fullName;
    }

    /**
     * Set new dimensions to resize image
     *      
     * Source: PHP for Absolute Beginners / Jason Lengstorf/ Apress/ 2009/ Chapter 8
     * Version : 1.1
     * Date : 16/01/2013
     * Modified date: 16/01/2013
     * Modified by : Nguyen Nhu Quan
     * Reason: Better logic to set $s (scale factor)
     * Modified date: 14/02/2013
     * Modified by : Nguyen Nhu Quan
     * Reason: If image smaller than max size then no need to resize it. SPEED UP in multiple uploads!
     * Modified date: 22/02/2013
     * Modified by : Nguyen Nhu Quan
     * Reason: Still resize if image smaller then maxsize to reduce complex logic to solve problem during upload
     * New image size is store in $this->_imageSize array
     * @return void
     */
    protected function calculateSize() {
        // Assemble the necessary variables for processing
        list($src_w, $src_h) = array_values($this->_imageSize);
        list($max_w, $max_h) = array_values($this->_maxSize);

        //Scale factor $s
        $s = min($max_w / $src_w, $max_h / $src_h);

        //This image bigger than max dimensions?. Then return new dimension array to resize it
        //Get the new dimensions
        $this->_imageSize['thumbWidth'] = ($s < 1) ? round($src_w * $s) : $src_w;
        $this->_imageSize['thumbHeight'] = ($s < 1) ? round($src_h * $s) : $src_h;
    }

    /**
     * Determines which function to process images
     * Source: PHP for Absolute Beginners / Jason Lengstorf/ Apress/ 2009/ Chapter 8
     * Version : 1.1
     * Date : 14/01/2013
     * Modified date: 14/02/2013
     * Modified by : Nguyen Nhu Quan
     * Reason: Craft the function to fit in Upload class context
     * Uses the MIME type of the provided image to determine
     * what image handling functions should be used. This
     * increases the perfomance of the script versus using
     * imagecreatefromstring().
     * Modified date: 22/02/2013
     * Modified by : Nguyen Nhu Quan
     * Reason: Use name/value pair in array for readability. Image type is also stored in class property this function
     * @param string $mimi the mime file type
     * @return void
     */
    protected function setImageFunctions($mime) {
        switch ($mime) {
            case 'image/jpeg':
            case 'image/pjpeg':
                $this->_imageFunctions = array('createImageResource' => 'imagecreatefromjpeg', 'saveImage' => 'imagejpeg');
                $this->_imageType = 'jpg';
                break;
            case 'image/gif':
                $this->_imageFunctions = array('createImageResource' => 'imagecreatefromgif', 'saveImage' => 'imagegif');
                $this->_imageType = 'gif';
                break;
            case 'image/png':
                $this->_imageFunctions = array('createImageResource' => 'imagecreatefrompng', 'saveImage' => 'imagepng');
                $this->_imageType = 'png';
                break;
        }
    }

    /**
     * Create a new image (with determined size)
     * 
     * Version : 1.0
     * Date : 22/02/2013
     * Author : Nguyen Nhu  Quan
     * Modified date: 
     * Modified by : 
     * Reason: 
     * 
     * $this_messages has the result
     * @return void
     */
    protected function createThumbnail() {
        //Get image size array
        list($src_w, $src_h, $thumb_w, $thumb_h) = array_values($this->_imageSize);
        //create image resource from the original file
        $resource = $this->_imageFunctions['createImageResource']($this->_original);
        //create a dummy thumbnail
        $thumb = imagecreatetruecolor($thumb_w, $thumb_h);
        // now resample $resource to $thumb
        imagecopyresampled($thumb, $resource, 0, 0, 0, 0, $thumb_w, $thumb_h, $src_w, $src_h);

        $success = $this->_imageFunctions['saveImage']($thumb, $this->_destination . $this->_thumbName);

        if ($success) {
            $this->_messages[] = "$this->_thumbName created successfully.";
        } else {
            $this->_messages[] = "Couldn't resize " . basename($this->_original) . " to $this->_thumbName";
        }
        imagedestroy($resource);
        imagedestroy($thumb);
    }

}

?>
