<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Email
 *
 * @author quan
 */


namespace Mii;
require_once 'regex.inc.php';
class Mii_Email {

    // Missing field array
    protected $_missing = array();
    // Required fields array
    protected $_required = array('name', 'email', 'comments');
    // Expected fields array
    protected $_expected = array('name', 'email', 'comments', 'subscribe');
    // Input storage (store all user input in _expected fields
    protected $_storage = array();
    // Email : Email field name
    protected $_email = 'email';
    // Email : Valid email
    protected $_validEmail = "";
    // Email : To
    protected $_to = "bsquan2009@gmail.com";
    // Email : Subject
    protected $_subject = "Feed back.";
    // Email : From
    protected $_from = "Website <feedback@example.com>";
    // Email : Content-Type
    protected $_contentType = "text/plain; charset=utf-8";
    // Email : Body
    protected $_body = "";
    // Email : Header
    protected $_header = "";
    // Email : Redirect location
    protected $_redirectLocation = "";
    // Suspect ?
    protected $_suspectFlag = FALSE;
    // Error fields
    protected $_errors = array();
    //Message to report the status of email
    protected $_messages = array();

    public function setRequiredFields($required) {
        if (!is_array($required)) {
            throw new \Exception("Required fields must be an array.");
        } else {
            $this->_required = $required;
        }
    }

    public function setExpectedFields($expected) {
        if (!is_array($expected)) {
            throw new \Exception("Expected fields must be an array.");
        } else {
            $this->_expected = $expected;
        }
    }

    public function setEmailName($email) {
        $this->_email = $email;
    }

    public function setTo($to) {
        $this->_to = $to;
    }

    public function setFrom($from) {
        $this->_from = $from;
    }

    public function setSubject($subject) {
        $this->_subject = $subject;
    }

    public function setContentType($contentType) {
        $this->_contentType = $contentType;
    }

    public function setRedirectLocation($loc) {
        $this->_redirectLocation = $loc;
    }

    public function getSuspectFlag() {
        return $this->_suspectFlag;
    }

    // Return error array
    public function getErrors() {
        return $this->_errors;
    }

    // Return missing array
    public function getMissing() {
        return $this->_missing;
    }

    // Return _messages 
    public function getMessages() {
        return $this->_messages;
    }

    /**
     * Check valid email address using regular expression
     *
     * Source: PHP for Absolute Beginners / Jason Lengstorf/ Apress/ 2009/ Chapter 8
     * Version : 1.0
     * Date : 14/01/2013
     * Modified date: 05/02/2013
     * Modified by: Nguyen Nhu Quan
     * Reason: Move regular expression to configuration.php file
     * 
     * Modified date: 15/02/2013
     * Modified by: Nguyen Nhu Quan
     * Reason: Set function becomes static!
     * 
     * @param $email input email need to be checked
     * @return bool
     */
    public static function validateEmail($email) {
        // Matches valid email addresses
        //$p= '/^[\w-]+(\.[\w-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i';
        // If a match is found, return TRUE, otherwise return FALSE
        return (preg_match(REG_VALID_EMAIL, $email)) ? TRUE : FALSE;
    }

    /**
     * Check valid email address using specified filter
     * then the valid email will be stored in $this->_validEmail
     * otherwise store email field in errors array and set message
     * Source: PHP Solutions Dynamic Design Made Easy 2nd Edition / David Powers/ FriendsofEd(Apress)/ 2010/ Chapter 5
     * Version : 1.0
     * Date : 06/02/2013
     * Modified date: 06/02/2013
     * Modified by: Nguyen Nhu Quan
     * Reason: Contruct a function with default value
     * 
     * Modified date: 15/02/2013
     * Modified by: Nguyen Nhu Quan
     * Reason: Convert the function to be compatible with Email class
     *
     * @return void
     */
## EVEN ARRAY MUST BE PASS AS REFERENCE IN PHP ! INTERESTING !
    protected function checkEmail() {
        //Check Email only when it is not empty
        if (!empty($_POST[$this->_email])) {
            //Validate value (email address by default)return validEmail otherwise false
            $validEmail = filter_input(INPUT_POST, $_POST[$this->_email], FILTER_VALIDATE_EMAIL);
            if ($validEmail) {
                //Store validEmail for using later
                $this->_validEmail = $validEmail;
            } else {
                //Set error['email]
                $this->_errors[$this->_email] = true;
                //Set message
                $this->_messages[] = "Invalid email address.";
            }
        }
    }

    /**
     * Check the $val for suspect phrases when sending mail with pattern : '/Content-Type:|Bcc:|Cc:/i'
     * raise $this->_suspect if caught something
     * Because of the recursive nature of the function, we can't use local suspect value 
     * Source: PHP Solutions Dynamic Design Made Easy 2nd Edition / David Powers/ FriendsofEd(Apress)/ 2010/ Chapter 5
     * Version : 1.0
     * Date : 05/02/2013
     * Modified date: 05/02/2013
     * Modified by: Nguyen Nhu Quan
     * Reason: 
     *          Move pattern to regex.inc.php
     * Modified date: 15/02/2013
     * Modified by: Nguyen Nhu Quan
     * Reason: 
     *          Convert the function to be compatible with Email class
     * @param $val value to check ̣default is $_POST
     * @return void
     */
    protected function checkSuspect($val = NULL) {
        //if no $val provided then use $_POST as default value
        $val = isset($val) ? $val : $_POST;
        //if the variable is an array , loop through each element
        //and pass it recursively back to the same function
        if (is_array($val)) {
            foreach ($val as $item) {
                // if already suspected then no reason to continue
                if ($this->_suspectFlag) {
                    break;
                } else {
                    isSuspect($item);
                }
            }
        } else {
            //if one of the suspect phrase is found then return true
            $this->_suspectFlag = preg_match(REG_SUSPECT_EMAIL_PATTERN, $val);
        }
    }

    /**
     * Check the $_POST variables for required fields that have been left blank
     * Store input value in $this->_storage array to save user to reenter info
     * Source: PHP Solutions Dynamic Design Made Easy 2nd Edition / David Powers/ FriendsofEd(Apress)/ 2010/ Chapter 5
     * Version : 1.0
     * Date : 04/02/2013
     * Modified date: 04/02/2013
     * Modified by: Nguyen Nhu Quan
     * Reason: 
     *          Move code statements to function
     *          ${$key} is not available for caller
     * Modified date: 07/02/2013
     * Modified by: Nguyen Nhu Quan
     * Reason:
     *          Store $_POST (key,value) in $this->_storage array
     * Modified date: 15/02/2013
     * Modified by: Nguyen Nhu Quan
     * Reason:
     *          Convert the function to be compatible with Email class
     * @return void
     */
    protected function checkMissingFields() {

        foreach ($_POST as $key => $value) {
            //assign to temporary variable and strip whitespace if not an array
            $temp = is_array($value) ? $value : trim($value);
            //if empty and required then add to $missing array
            if (empty($temp) && in_array($key, $this->_required)) {
                $this->_missing[] = $key;
                $this->_messages[] = "$key is required";
            }
            /*
              else if (in_array($key, $expected)) {
              otherwise, assign to variable of the same name as $key
              ${$key} = $temp; */

            //Store those value in expected storage array
            if (in_array($key, $this->_expected)) {
                $this->_storage[$key] = $temp;
            }
        }
    }

    /**
     * Prepare the message body from array ($this->_storage array)
     * and store in to $this->_body
     * 
     * Source: PHP Solutions Dynamic Design Made Easy 2nd Edition / David Powers/ FriendsofEd(Apress)/ 2010/ Chapter 5
     * Version : 1.1
     * Date : 04/02/2013
     * Modified date: 04/02/2013
     * Modified by: Nguyen Nhu Quan
     * Reason: 
     *          Uncompatible with the contact.php structure
     *          Code almost completely rewritten
     * Modified date: 15/02/2013
     * Modified by: Nguyen Nhu Quan
     * Reason: 
     *          Convert the function to be compatible with Email class
     * @return void
     */
    protected function prepareMessage() {
        //Initialize $message
        $message = '';
        //loop through $this->_storage array
        foreach ($this->_storage as $key => $value) {
            // if it has no value, assign 'Not provided' 
            $value = (empty($value)) ? 'Not provided' : $value;
            // if an array, expand as comma-separated string 
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            // replace underscores and hyphens in the label with spaces 
            $key = str_replace(array('_', '-'), ' ', $key);
            // add label and value to the message body. Uppercase first letter
            $message .=ucfirst($key) . ": $value\r\n\r\n";
        }
        // limit line length to 70 characters 
        $this->_body = wordwrap($message, 70);
    }

    protected function prepareHeader() {
        $this->_headers = "From: $this->_from\r\n";
        $this->_headers .= "Content-Type: $this->_contentType";
        $this->_headers .="\r\nReply-To: $this->_validEmail";
    }

    public function sendMail() {
        $mailSent = FALSE;
        $this->checkSuspect();
        if (!$this->_suspectFlag) {
            $this->checkMissingFields();
            $this->checkEmail();
            if ((!$this->_missing) && (!$this->_errors)) {
                $this->prepareMessage();
                $this->prepareHeader();
                $mailSent = mail($this->_to, $this->_subject, $this->_body, $this->_header);
                if ($mailSent) {
                    header('Location: ' . $this->_redirectLocation);
                } else {
                    $this->_messages[] = "Error! Please contact Administrator or try again later";
                }
            }
        } else {
            $this->_messages[] = "Your mail was not sent. Please try again later";
        }
        return $mailSent;
    }

}

?>
