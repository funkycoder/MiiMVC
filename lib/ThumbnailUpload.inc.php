<?php

/**
 * UPLOAD IMAGES (MULTIPLE FILES SUPPORTED)  
 * THIS CLASS EXTENDS UPLOAD CLASS
 * ----------------------------------------------------------------------------------------
 * Source: PHP Solutions Dynamic Design Made Easy 2nd Edition / David Powers/ FriendsofEd(Apress)/ 2010/ Chapter 8
 * Source: PHP for Absolute Beginners / Jason Lengstorf/ Apress/ 2009/ Chapter 8
 * Version : 1.0
 * Date : 22/02/2013
 * Modified date: 22/02/2013
 * @author Nguyen Nhu Quan FunkyCoder <bsquan2009@gmail.com>
 * ------------------------------------------------------------------------------------------
 * PARAMETERS:
 * setPermittedTypes($types)       array : replace predefined permitted file type array
 * addPermittedTypes($types)       array : add more allowed file types
 * setMaxFileSize($num)            int   : set max file size in bytes allowed to upload
 * getMessages()                   array : return warning messages
 * getMaxFileSize()                int   : return max file size allowed
 * SetImageMaxSize($sizeArray)     array : set max image dimensions to resize
 * setResizeImageSuffix($resizeSuffix) string: set resize image suffix
 * setThumbDestination($destination) string : set destination for thumbnail
 * setThumbSuffix($thumbSuffix)      string : set thumbnail suffix
 * setThumbMaxSize($thumbMaxSize)    numeric : set thumbnail max size (number)
 * -----------------------------------------------------------------------------------------
 * USAGE:
 * $upload = new ThumbnailUpload($path)
 * move($deleteOriginal = FALSE, $resize = FALSE, $createThumb = FALSE, $overwrite = FALSE)
 * -----------------------------------------------------------------------------------------
 * PERSONAL NOTES:
 * 22/02/2013: This class has a lot of tweaks around original code when allow user to create 
 * resize image and or thumbnail at the same time. If $delteOriginal = true 
 * and no resized image and thumbnail will be created then the original image will not be deleted 
 * and no error warning issued.
 */

namespace Mii;

require_once 'Upload.inc.php';
require_once 'Thumbnail.inc.php';

class Mii_ThumbnailUpload extends Mii_Upload {

    //Resize Image parameters

    protected $_maxImageSize = array(350, 240);
    protected $_resizeSuffix = 'resize';
    //Thumbnail parameters

    protected $_thumbDestination = '';
    protected $_thumbSuffix = 'thumb';
    protected $_maxThumbSize = 120;

    //
    // FUNCTIONS DEFINITION FOLLOWS
    //
    public function setImageMaxSize($sizeArray) {
        if (!is_array($sizeArray)) {
            $this->_messages[] = "Error! Max image size must be an array. Default values 350x240 used.";
        } else {
            $this->_maxImageSize = $sizeArray;
        }
    }

    public function setResizeImageSuffix($resizeSuffix) {
        $this->_resizeSuffix = $resizeSuffix;
    }

    public function setThumbDestination($destination) {
        if (is_dir($destination) && is_writable($destination)) {
            $this->_thumbDestination = Mii_File::addSlashToPathName($destination);
        } else {
            $this->_messages[] = "Can not write to $destination. Current path is used instead.";
        }
    }

    public function setThumbSuffix($thumbSuffix) {
        $this->_thumbSuffix = $thumbSuffix;
    }

    public function setThumbMaxSize($thumbMaxSize) {
        if (!is_numeric($thumbMaxSize)) {
            $this->_messages[] = "Error! Max thumbnail size must be a number. Default values 120 pixel used.";
        } else {
            $this->_maxThumbSize = $thumbMaxSize;
        }
    }

    /**
     * Resize (to current dir), create thumbnail (destination dir) and then save them
     *
     * Source: PHP Solutions Dynamic Design Made Easy 2nd Edition / David Powers/ FriendsofEd(Apress)/ 2010/ Chapter 8
     * Version : 1.1
     * Date : 22/02/2013
     * Modified date:22/02/2013
     * Modified by : Nguyen Nhu Quan
     * Reason: Give user options to create a resize image and or thumbnail
     * @return void Warning message stored in $this->_messages[] 
     */
    protected function createThumbnail($image, $resize, $createThumb) {

        if ($resize || $createThumb) {
            $thumb = new Mii_Thumbnail($image);
            if ($resize) {
                $thumb->setImageMaxSize($this->_maxImageSize);
                $thumb->setSuffix($this->_resizeSuffix);
                $thumb->create();
            }
            if ($createThumb) {
                //If destination for thumbnail not set then dont send it to be validated
                if (!empty($this->_thumbDestination)) {
                    $thumb->setDestination($this->_thumbDestination);
                }
                $thumb->setImageMaxSize(array($this->_maxThumbSize, $this->_maxThumbSize));
                $thumb->setSuffix($this->_thumbSuffix);
                $thumb->create();
            }
            $messages = $thumb->getMessages();
            $this->_messages = array_merge($this->_messages, $messages);
        }
    }

    /**
     * Core logic to process one upload file. 
     * Check all aspects of the file, change name, resize (to current dir), create thumbnail (destination dir) and then save them
     * THIS FUNCTION OVERRIDES PARENT FUNCTION
     * 
     * Source: PHP Solutions Dynamic Design Made Easy 2nd Edition / David Powers/ FriendsofEd(Apress)/ 2010/ Chapter 8
     * Version : 1.1
     * Date : 14/02/2013
     * Modified date:22/02/2013
     * Modified by : Nguyen Nhu Quan
     * Reason: Give user options to create a resize image and or thumbnail and or delete/keep original file
     * @return void Warning message store in $this->_messages[] 
     */
    protected function processFile($filename, $error, $size, $type, $tempName, $deleteOriginal, $resize, $createThumb, $overwrite) {
        $OK = $this->checkError($filename, $error);
        if ($OK) {
            $sizeOK = $this->checkFileSize($filename, $size);
            $typeOK = $this->checkFileType($filename, $type);
            $directoryOK = $this->checkDirectory($this->_destination);
            if ($sizeOK && $typeOK && $directoryOK) {
                //Prepare the name for the destination file (beaware of $overwrite)
                $name = Mii_File::checkFileName($this->_destination, $filename, $overwrite);
                //Move file from temp folder to _destination
                $success = move_uploaded_file($tempName, $this->_destination . $name);
                if ($success) {
                    // don't add this message if the original image is going to be deleted
                    if (!$deleteOriginal) {

                        $message = $filename . ' uploaded successfully.';
                        if ($name != $filename) {
                            $message.=" (Renamed to $name)";
                        }
                        //Record message to _messages[]
                        $this->_messages[] = $message;
                    }// create a resized image and or thumbnail from the uploaded image
                    if ($resize || $createThumb) {
                        $this->createThumbnail($this->_destination . $name, $resize, $createThumb);
                    }
                    // delete the uploaded image if required
                    if ($deleteOriginal) {
                        if ($resize || $createThumb) {
                            unlink($this->_destination . $name);
                        } else {
                            $this->_messages[] = "$filename uploaded successfully.";
                        }
                    }
                } else {
                    $this->_messages[] = "$filename upload failed.";
                }
            }
        }
    }

    /**
     * Core logic to process multiple upload files in $_FILES
     * Check if multiple files uploaded then proceed accordingly
     * THIS FUNCTION OVERRIDES PARENT FUNCTION!
     *
     * Source: PHP Solutions Dynamic Design Made Easy 2nd Edition / David Powers/ FriendsofEd(Apress)/ 2010/ Chapter 6
     * Source: PHP for Absolute Beginners / Jason Lengstorf/ Apress/ 2009/ Chapter 8
     * Version : 1.1
     * Date : 14/02/2013
     * Modified date: 14/02/2013
     * Modified by : Nguyen Nhu Quan
     * Reason: Use list to improve readability of the code (Jason Lengstoft techniques)
     * Modified date: 21/02/2013
     * Modified by : Nguyen Nhu Quan
     * Reason: override original function in Upload class
     * @return void 
     */
    public function move($deleteOriginal = FALSE, $resize = FALSE, $createThumb = FALSE, $overwrite = FALSE) {

        // current($this->_uploaded) return the current element of the array 
        //Separate array values to corresponding variables.
        list($name, $type, $tempName, $error, $size) = array_values(current($this->_uploaded));
        if (is_array($name)) {
            foreach ($name as $number => $filename) {
                //Reset _renamed to FALSE as everytime processFile() runs it may change this variable
                $this->_renamed = FALSE;
                $this->processFile($filename, $error[$number], $size[$number], $type[$number], $tempName[$number], $deleteOriginal, $resize, $createThumb, $overwrite);
            }
            //Upload one file. Proceed as normal
        } else {
            $this->processFile($name, $error, $size, $type, $tempName, $deleteOriginal, $resize, $createThumb, $overwrite);
        }
    }

}

?>
