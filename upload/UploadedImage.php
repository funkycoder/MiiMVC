<?php

namespace Mii\Upload;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UploadedImage
 *
 * @author Quan Nguyen
 */
class UploadedImage extends \Mii\Upload\UploadedFile {

    //Complete MIME file types can be found at http://www.iana.org/assignments/media-types
    protected $imageTypes = array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/png');
    protected $imageSize = array('originalWidth' => 0, 'originalHeight' => 0, 'newWidth' => 0, 'newHeight' => 0);
    //Store image functions needed for images operation
    protected $imageFunctions = array();

    protected function fileTypeOK() {
        return $this->isImage($this->temp_file);
    }

    protected function isImage($image_file) {
        if (\is_file($image_file) && \is_readable($image_file)) {
            $imageDetails = \getimagesize($image_file);
            // if getimagesize() return an array,it looks like an image
            if (\is_array($imageDetails)) {
                $this->imageSize['originalWidth'] = $imageDetails[0];
                $this->imageSize['originalHeight'] = $imageDetails[1];
                // if original width or height equals 0 then 
                // the getimagesize() function could not determine the size of the image
                if (!$imageDetails[0])
                    return TRUE;
            }
        }
        return FALSE;
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
    protected function calculateSize($size) {
        // Assemble the necessary variables for processing
        list($src_w, $src_h) = \array_values($this->imageSize);
        list($max_w, $max_h) = \array_values($size);

        //Scale factor $s
        $s = \min($max_w / $src_w, $max_h / $src_h);

        //This image bigger than max dimensions?. Then return new dimension array to resize it
        //Get the new dimensions
        $this->imageSize['newWidth'] = ($s < 1) ? \round($src_w * $s) : $src_w;
        $this->imageSize['newHeight'] = ($s < 1) ? \round($src_h * $s) : $src_h;
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
    protected function setImageFunctions() {
        switch ($this->type) {
            case 'image/jpeg':
            case 'image/pjpeg':
                $this->imageFunctions = array('createImageResource' => 'imagecreatefromjpeg', 'saveImage' => 'imagejpeg');
                break;
            case 'image/gif':
                $this->imageFunctions = array('createImageResource' => 'imagecreatefromgif', 'saveImage' => 'imagegif');
                break;
            case 'image/png':
                $this->imageFunctions = array('createImageResource' => 'imagecreatefrompng', 'saveImage' => 'imagepng');
                break;
        }
    }

    protected function doResize($sourceFile, $destinationFile, $size = array()) {
        $SUCCESS = FALSE;
        if ($this->directoryOK(\pathinfo($destinationFile, \PATHINFO_DIRNAME))) {
            if ($this->isImage($sourceFile)) {
                $this->setImageFunctions();
                if (!empty($this->imageFunctions)) {
                    $this->calculateSize($size);
                    //Get image size array
                    list($src_w, $src_h, $new_w, $new_h) = array_values($this->imageSize);
                    //create image resource from the original file
                    $resource = $this->imageFunctions['createImageResource']($sourceFile);
                    //create a dummy thumbnail
                    $dummy = imagecreatetruecolor($new_w, $new_h);
                    // now resample $resource to $dummy
                    imagecopyresampled($dummy, $resource, 0, 0, 0, 0, $new_w, $new_h, $src_w, $src_h);
                    $SUCCESS = $this->imageFunctions['saveImage']($dummy, $destinationFile);
                    //destroy temp resources
                    \imagedestroy($resource);
                    \imagedestroy($dummy);
                }
            }
        }
        if (!$SUCCESS)
            $this->errors['Resize'] = 'Thay đổi kích thước file ' . basename($sourceFile) . ' thất bại.';
        return $SUCCESS;
    }

    public function resize($size = array()) {
        $sourceFile = $this->file_path . $this->name;
        $this->doResize($sourceFile, $sourceFile, $size);
    }

    protected function getSuffix($suffix) {
        //matches a string that contains only alphanumeric characters and underscores
        if (preg_match('/^\w+$/', $suffix)) {
            //if $suffix doesnt contain an underscore, strpos()returns false
            //so the condition needs to use the “not identical” operator (with two equal signs)
            if (strpos($suffix, '_') !== 0) {
                //if the suffix doesnt begin with an underscore, one is added
                return '_' . $suffix;
            } else {
                return $suffix;
            }
        } else {
            return '';
        }
    }

    public function createThumb($destination, $size = array(120, 120), $suffix = '_thumb') {
        $file_name = \pathinfo($this->name, \PATHINFO_FILENAME);
        $file_extension = \pathinfo($this->name, \PATHINFO_EXTENSION);
        $thumb_name = $file_name . $this->getSuffix($suffix) . $file_extension;
        $thumb_full_path = $this->addSlashToPathName($destination) . $thumb_name;
        $SUCCESS = $this->doResize($this->file_path . $this->name, $thumb_full_path, $size);
        if (!$SUCCESS)
            $this->errors['Thumbnail'] = "Tạo thumbnail cho file : $this->name thất bại.";
        return $SUCCESS;
    }

}

?>
