<?php

/**
 * UPLOAD FILES (MULTIPLE FILES SUPPORTED)  
 * 
 * ----------------------------------------------------------------------------------------
 * Source: PHP Solutions Dynamic Design Made Easy 2nd Edition / David Powers/ FriendsofEd(Apress)/ 2010/ Chapter 6
 * Source: PHP for Absolute Beginners / Jason Lengstorf/ Apress/ 2009/ Chapter 8
 * Version : 1.0
 * Date : 14/02/2013
 * Modified date: 14/02/2013, 08/03/2013
 * @author Nguyen Nhu Quan FunkyCoder <bsquan2009@gmail.com>
 * ------------------------------------------------------------------------------------------
 * PARAMETERS:
 * setPermittedTypes($types)       array : replace predefined permitted file type array
 * addPermittedTypes($types)       array : add more allowed file types
 * setMaxFileSize($num)            int   : set max file size in bytes allowed to upload
 * getMessages()                   array : return warning messages
 * getMaxFileSize() 
 * getFileNames()                  array : return succeeded uploaded filenames
 * -----------------------------------------------------------------------------------------
 * USAGE:
 * $upload = new Upload($path)
 * move($overwrite = FALSE, $resize = TRUE)
 * -----------------------------------------------------------------------------------------
 * PERSONAL NOTES:
 * 14/02/2013 :Mung 6 Tet Quy Ty
 * Valentine Day
 * Got stuck with _construct for > 2hrs
 * 22/02/2013: This class no longer automatically resize image
 * which function has reside in the child class ThumbnailUpload
 */
/**
 * PHP configuration settings that affect file uploads
 * max_execution_time 30
 * The maximum number of seconds that a PHP script can run. If
 * the script takes longer, PHP generates a fatal error.
 * max_input_time  60
 * The maximum number of seconds that a PHP script is allowed to
 * parse the $_POST and $_GET arrays and file uploads. Very large
 *  uploads are likely to run out of time.
 * post_max_size  8M
 * The maximum permitted size of all $_POST data, including file
 * uploads. Although the default is 8MB, hosting companies may
 * impose a smaller limit.
 * BE AWARE IF $_POST DATA BIGGER THAN LIMIT, THE BROWSER SIMPLY CANCEL ACTION AND RELOAD WITHOUT WARNING!
 */

namespace Mii;

require_once 'File.inc.php';

class Mii_Upload {

// $_FILES array
    protected $_uploaded = array();
// Path to the upload folder
    protected $_destination;
//Maximum file size
    protected $_maxFileSize = 51200; //in bytes
//Message to report the status of uploads
    protected $_messages = array();
//Permitted file types (basically they are images file)
//Complete MIME file types can be found at http://www.iana.org/assignments/media-types
    protected $_permittedFileTypes = array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/png');
    protected $_extraValidFileTypes = array('image/tiff', 'application/pdf', 'text/plain', 'text/rtf');
// file names uploaded succesfully
    protected $_fileNames = array();

//Constructor
    public function __construct($path) {
// This cause server NOT RESPONDING!!!
//        if (!is_dir($path) || !is_writable($path)) 
        $this->_destination = Mii_File::addSlashToPathName($path);
        $this->_uploaded = $_FILES;
    }

// Return _messages 
    public function getMessages() {
        return $this->_messages;
    }

// Return _maxFileSize in KB
    public function getMaxFileSize() {
        return number_format($this->_maxFileSize / 1024, 1) . ' KB';
    }

// Return file names uploaded succesfully
    public function getFileNames() {
        return $this->_fileNames;
    }

    public function setMaxFileSize($num) {
        if (!is_numeric($num)) {
            throw new \Exception("Maximum size must be a number.");
        }$this->_maxFileSize = (int) $num;
    }

    public function addPermittedTypes($types) {
        $types = (array) $types;
        $this->isValidMime($types);
        $this->_permittedFileTypes = array_merge($this->_permittedFileTypes, $types);
    }

    public function setPermittedTypes($types) {
        $types = (array) $types;
        $this->isValidMime($types);
        $this->_permittedFileTypes = $types;
    }

    protected function checkDirectory($dir) {
        if (is_dir($dir) && is_writable($dir)) {
            return TRUE;
        } else {
            $this->_messages[] = "Destination folder: $dir is NOT writable. No files will be uploaded!";
            return FALSE;
        }
    }

    //Error level    Meaning
    //  0              Upload successful
    //  1              File exceeds maximum upload size specified in php.ini (default 2MB)
    //  2              File exceeds size specified by MAX_FILE_SIZE
    //  3              File only partially uploaded
    //  4              Form submitted with no file specified
    //  6              No temporary folder
    //  7              Cannot write file to disk
    //  8              Upload stopped by an unspecified PHP extension 

    /**
     * Check for upload errors in $_FILES
     * 
     * Source: PHP Solutions Dynamic Design Made Easy 2nd Edition / David Powers/ FriendsofEd(Apress)/ 2010/ Chapter 6
     * Version : 1.0
     * Date : 14/02/2013
     * Modified date: 14/02/2013
     * Modified by : Nguyen Nhu Quan
     * Reason: Use named error constant
     * 
     * @param string $filename  the upload file name
     * @param int $error error code in $_FILES 
     * @return bool Pass error check or not. Warning message store in $this->_messages[] 
     */
    protected function checkError($filename, $error) {
        switch ($error) {
            case UPLOAD_ERR_OK :
                return TRUE;
            case UPLOAD_ERR_INI_SIZE :
            case UPLOAD_ERR_FORM_SIZE:
                $this->_messages[] = "$filename exceeds maximum size: " . $this->getMaxFileSize() . " and was not uploaded";
//Give chance for the code to check for file type too!
                return TRUE;
            case UPLOAD_ERR_PARTIAL:
                $this->_messages[] = "Error while uploading $filename. Please try again.";
                return FALSE;
            case UPLOAD_ERR_NO_FILE :
                $this->_messages[] = "No file selected.";
                return FALSE;
            case UPLOAD_ERR_NO_TMP_DIR :
                $this->_messages[] = "No temporary folder available.";
                return FALSE;
            default :
                $this->_messages[] = "System error uploading $filename. Contact webmaster for help.";
                return FALSE;
        }
    }

    /**
     * Check for exceeding permitted file size
     * 
     * Client site could be control by <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $max; ?>">
     * Source: PHP Solutions Dynamic Design Made Easy 2nd Edition / David Powers/ FriendsofEd(Apress)/ 2010/ Chapter 6
     * Version : 1.0
     * Date : 14/02/2013
     * Modified date:
     * Modified by : 
     * Reason: 
     * 
     * @param string $filename  the upload file name
     * @param int $size size in bytes of the file in $_FILES 
     * @return bool Pass file size check or not. Warning message store in $this->_messages[] 
     */
    protected function checkFileSize($filename, $size) {
        if ($size == 0) {
            return FALSE;
        } elseif ($size > $this->_maxFileSize) {
            $this->_messages[] = "$filename exceeds maximum size: " . $this->getMaxFileSize() . " and was not uploaded";
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * Check for permitted file type
     * 	 
     * Source: PHP Solutions Dynamic Design Made Easy 2nd Edition / David Powers/ FriendsofEd(Apress)/ 2010/ Chapter 6
     * Version : 1.0
     * Date : 14/02/2013
     * Modified date:
     * Modified by : 
     * Reason: 
     * 
     * @param string $filename  the upload file name
     * @param int $type file type in $_FILES 
     * @return bool Pass file type check or not. Warning message store in $this->_messages[] 
     */
    protected function checkFileType($filename, $type) {
        //MAX_FILE_SIZE hidden input field prevent the $_FILES to be loaded
        //then it may cause message "$filename is not a permitted type of file." displayed.
        if (empty($type)) {
            return FALSE;
        } elseif (!in_array($type, $this->_permittedFileTypes)) {
            $this->_messages[] = "$filename is not a permitted type of file and was not uploaded.";
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * Check for valid MIME file type
     * 	 
     * Source: PHP Solutions Dynamic Design Made Easy 2nd Edition / David Powers/ FriendsofEd(Apress)/ 2010/ Chapter 6
     * Version : 1.0
     * Date : 14/02/2013
     * Modified date:
     * Modified by : 
     * Reason: 
     * 
     * @param int $type requesting file type 
     * @return void throw exception if not valid MIME types
     * Complete MIME file types can be found at http://www.iana.org/assignments/media-types
     */
    protected function isValidMime($types) {
        //All possible valid MIME file types defined
        $valid = array_merge($this->_permittedFileTypes, $this->_extraValidFileTypes);
        //types is already casted to array by caller
        foreach ($types as $types) {
            if (!in_array($types, $valid))
                throw new \Exception("$types is not permitted MIME type and can not be used.");
        }
    }

    /**
     * Core logic to process one upload file. 
     * Check all aspects of the file then move them to $path
     *
     * Source: PHP Solutions Dynamic Design Made Easy 2nd Edition / David Powers/ FriendsofEd(Apress)/ 2010/ Chapter 6
     * Version : 1.0
     * Date : 14/02/2013
     * Modified date:
     * Modified by : 
     * Reason: 
     * @return void Warning message store in $this->_messages[] 
     */
    protected function processFile($filename, $error, $size, $type, $tempName, $overwrite) {
        $OK = $this->checkError($filename, $error);
        if ($OK) {
            $sizeOK = $this->checkFileSize($filename, $size);
            $typeOK = $this->checkFileType($filename, $type);
            $directoryOK = $this->checkDirectory($this->_destination);
            if ($sizeOK && $typeOK && $directoryOK) {
                $name = Mii_File::checkFileName($this->_destination, $filename, $overwrite);
                //Move file from temp folder to _destination
                $success = move_uploaded_file($tempName, $this->_destination . $name);
                if ($success) {
                    //add the amended file name to array of filenames
                    $this->_fileNames[] = $name;
                    $message = $filename . ' uploaded successfully.';
                    if ($name != $filename) {
                        $message.=" (Renamed to $name)";
                    }
                    //Record message to _messages[]
                    $this->_messages[] = $message;
                } else {
                    $this->_messages[] = "$filename upload failed.";
                }
            }
        }
    }

    /**
     * Core logic to process multiple upload files in $_FILES
     * Check if multiple files uploaded then proceed accordingly
     *
     * Source: PHP Solutions Dynamic Design Made Easy 2nd Edition / David Powers/ FriendsofEd(Apress)/ 2010/ Chapter 6
     * Source: PHP for Absolute Beginners / Jason Lengstorf/ Apress/ 2009/ Chapter 8
     * Version : 1.1
     * Date : 14/02/2013
     * Modified date: 14/02/2013
     * Modified by : Nguyen Nhu Quan
     * Reason: Use list to improve readability of the code (Jason Lengstoft techniques)
     * @return void 
     */
    public function move($overwrite = FALSE) {
//Return the current element of the array which happens to be the first element of $_FILES
//Instead of using $_FILES['image']['name'], now we can use $field['name'] regardless of
//the name attribute of the file input field
//        $field = current($this->_uploaded);
//        //Upload multiple files?
//        if (is_array($field['name'])) {
//            foreach ($field['name'] as $number => $filename) {
//                //Reset _renamed to FALSE as everytime processFile() runs it may change this variable
//                $this->_renamed = FALSE;
//                $this->processFile($filename, $field['error'][$number], $field['size'][$number], $field['type'][$number], $field['tmp_name'][$number], $overwrite);
//            }
//            //Upload one file. Proceed as normal
//        } else {
//            $this->processFile($field['name'], $field['error'], $field['size'], $field['type'], $field['tmp_name'], $overwrite);
//        }
        // current($this->_uploaded) return the current element of the array 
        //Separate array values to corresponding variables.
        list($name, $type, $tempName, $error, $size) = array_values(current($this->_uploaded));
        if (is_array($name)) {
            foreach ($name as $number => $filename) {
                //Reset _renamed to FALSE as everytime processFile() runs it may change this variable
                $this->_renamed = FALSE;
                $this->processFile($filename, $error[$number], $size[$number], $type[$number], $tempName[$number], $overwrite);
            }
            //Upload one file. Proceed as normal
        } else {
            $this->processFile($name, $error, $size, $type, $tempName, $overwrite);
        }
    }

}

// End class here
?>
