<?php

namespace Mii\Upload;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UploadedFile
 *
 * @author Quan Nguyen
 */
abstract class UploadedFile {

    protected $name;
    protected $original_name;
    protected $file_path;
    protected $temp_file;
    protected $type;
    protected $size;
    protected $max_file_size;
    protected $errors = array();

    public function __construct($name, $size, $type, $temp_file, $max_file_size) {
        $this->name = $name;
        $this->original_name = $name;
        $this->size = $size;
        $this->type = $type;
        $this->temp_file = $temp_file;
        $this->max_file_size = $max_file_size;
    }

    protected function directoryOK($dir) {
        if (is_dir($dir) && is_writable($dir)) {
            return TRUE;
        } else {
            $this->errors['Directory'] = "Thư mục $dir không ghi được.";
            return FALSE;
        }
    }

    //Security issue , must check whether the tmp_file is really an uploaded file
    protected function fileUploadedOK() {
        return (\is_uploaded_file($this->temp_file) && \is_readable($this->temp_file));
    }

    protected function fileSizeOK() {
        if ($this->size > $this->max_file_size) {
            $this->error .= 'Kích thước file vượt quá qui định (MAX: ' . \number_format($this->max_file_size / 1024, 1) . ' KB).';
            return FALSE;
        } else {
            return TRUE;
        }
    }

    abstract function fileTypeOK();

    protected function getRandomName() {
        //  string uniqid ([ string $prefix = "" [, bool $more_entropy = false ]] )
        //  With an empty prefix, the returned string will be 13 characters long. 
        //  If more_entropy is TRUE, it will be 23 characters. 
        $this->name = \sha1($this->name . \uniqid('', true));
    }

    /**
     * Check for existed file name, overwrite or change name available
     * 	 
     * Source: PHP Solutions Dynamic Design Made Easy 2nd Edition / David Powers/ FriendsofEd(Apress)/ 2010/ Chapter 6
     * Version : 1.0
     * Date : 14/02/2013
     * 
     * @param type $destination destination folder
     * @param type $overwrite   overwrite existing file?
     */
    protected function getName($destination, $OVERWRITE) {
        //Remove all the space in $name by '_' a security issue
        $nospaces = \str_replace(' ', '_', $this->name);

        if (!$OVERWRITE) {
            //rename the file if it already exists
            //Get all the file names in the directory
            $existing = \scandir($destination);
            //If file name exist
            if (in_array($nospaces, $existing)) {
                //Separate filename to base and extension by dot position
                $dot = \strrpos($nospaces, '.');
                if ($dot) {
                    $base = \substr($nospaces, 0, $dot);
                    $extension = \substr($nospaces, $dot);
                } else {
                    //No dot? then file has no extension
                    $base = $nospaces;
                    $extension = '';
                }
                $i = 1;
                do {
                    $nospaces = $base . '_' . $i++ . $extension;
                } while (\in_array($nospaces, $existing));
            }
        }
        //return the unique name
        $this->name = $nospaces;
    }

    public function isReady() {
        return $this->fileSizeOK() && $this->fileTypeOK() && $this->isUploaded();
    }

    public function move($destination, $RANDOM_NAME = TRUE, $OVERWRITE = TRUE) {
        $SUCCESS = FALSE;
        if ($this->isReady() && $this->directoryOK($destination)) {
            $this->file_path = $this->addSlashToPathName($destination);
            if ($RANDOM_NAME) {
                $this->name = $this->getRandomName();
            } else {
                $this->name = $this->getName($destination, $OVERWRITE);
            }
            $SUCCESS = \move_uploaded_file($this->temp_file, $this->file_path . $this->name);
        }
        if (!$SUCCESS)
            $this->errors['Upload'] = "Upload thất bại ($this->original_name).";
        return $SUCCESS;
    }

    public function deleteTempFile() {
        @\unlink($this->temp_file);
    }

    public function delete() {
        @\unlink($this->file_path . $this->name);
    }

    protected function addSlashToPathName($dir) {
        //get the last character
        $last = \substr($dir, -1);
        //add a trailing slash if missing (second condition using an escapte back slash)
        if ($last == '/' || $last == '\\') {
            return $dir;
        } else {
            return $dir . DIRECTORY_SEPARATOR;
        }
    }

}

?>
