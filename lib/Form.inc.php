<?php

/**
 * FORM CLASS
 * 
 * ----------------------------------------------------------------------------------------
 * Version : 1.0
 * Date : 27/02/2013
 * @author Nguyen Nhu Quan FunkyCoder <bsquan2009@gmail.com>
 * ----------------------------------------------------------------------------------------
 * STATIC FUNCTIONS:
 * sanitizeData($data, $allowableTags = NULL, $htmlEntity = FALSE)    
 * checkMissingFields($expected, $required = null)
 */

namespace Mii;

class Mii_Form {

    /**
     * Sanitize input data (recursive function)
     * 
     * Source: PHP for Absolute Beginners / Jason Lengstorf/ Apress/ 2009
     * Version : 1.1
     * Date : 27/02/2013
     * Modified date:
     * Modified by : 
     * Reason:     
     * 
     * @param string or array $data data to be sanitized
     * @param string $allowableTags tags allowed
     * @param boolean $htmlEntity convert sanitized data to htmlEntities
     * @return sanitized data
     */
    public static function sanitizeData($data, $allowableTags = NULL, $htmlEntity = FALSE) {
        if (!is_array($data)) {

            $stripTag = strip_tags(trim($data), $allowableTags);
            return ($htmlEntity) ? htmlentities($stripTag, ENT_COMPAT, 'UTF-8') : $stripTag;
        } else {
            return array_map('sanitizeData', $data);
        }
    }

    /**
     * Check the $_POST variables for required fields that have been left blank
     * MUST CHECK $_POST SET BEFORE USE!
     * Source: PHP Solutions Dynamic Design Made Easy 2nd Edition / David Powers/ FriendsofEd(Apress)/ 2010/ Chapter 5
     * Version : 1.2
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
     * Modified date: 27/02/2013
     * Modified by: Nguyen Nhu Quan
     * Reason:
     *          Store the expected fields in $_SESSION. Move this function to Form class
     * @param string or array $expected expected fields
     * @param string or array $required required fields
     * @return array @missing missing fields
     * -----------------------------------------------------------------------------
     * PERSONAL NOTES
     * 27/02/13: Happy Doctor's Day.
     * -----------------------------------------------------------------------------
     */
    public static function checkMissingFields($expected, $required = null) {
        //start session if havent been done
        if (!isset($_SESSION)) {
            session_start();
        }
        // create empty array for any missing fields
        $missing = array();
        //turn single string to array 
        $expected = (array) $expected;
        // create $required array if not set
        if (!isset($required)) {
            $required = array();
        } else {
            // using casting operator to turn single string to array
            $required = (array) $required;
        }
        foreach ($_POST as $key => $value) {
            //sanitize user input. if $value is an array it will be sanitized thoroughly
            $value = Mii_Form::sanitizeData($value);
            //if empty and required then add to $missing array
            if (empty($value) && in_array($key, $required)) {
                $missing[] = $key;
                //if this $key in $expected then save it to session variable. Others will be omitted (security issue)
            } else if (in_array($key, $expected)) {
                // otherwise, assign to a variable of the same name as $key
                $_SESSION[$key] = $value;
            }
        }
        return $missing;
    }

    function makeUrl($title) {
        $patterns = array(
            '/\s+/',
            '/(?!-)\W+/'
        );
        $replacements = array('-', '');
        return preg_replace($patterns, $replacements, strtolower($title));
    }

}

?>
