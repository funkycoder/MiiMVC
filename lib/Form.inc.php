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

namespace Mii\Lib;

class Form {

// This script defines any functions required by the various forms.
// This script is created in Chapter 4.
// This function generates a form INPUT or TEXTAREA tag.
// It takes three arguments:
// - The name to be given to the element.
// - The type of element (text, password, textarea).
// - An array of errors.
    public static function create_form_input($name, $value, $type, $errors) {

        // Conditional to determine what kind of element to create:
        if (($type == 'text') || ($type == 'password')) { // Create text or password inputs.
            // Start creating the input:
            echo '<input type="' . $type . '" name="' . $name . '" id="' . $name . '"';
            // Add the value to the input. $value already sanitized and converted to htmlentity (in object model)
            // Don't display the password again
            if ($type=='text')
                echo ' value="' . $value . '"';
            // Check for an error:
            if (array_key_exists($name, $errors)) {
                echo 'class="error" /> <span class="error">' . $errors[$name] . '</span>';
            } else {
                echo ' />';
            }
        } elseif ($type == 'textarea') { // Create a TEXTAREA.
            // Display the error first: 
            if (array_key_exists($name, $errors))
                echo ' <span class="error">' . $errors[$name] . '</span>';
            // Start creating the textarea:
            echo '<textarea name="' . $name . '" id="' . $name . '" rows="5" cols="75"';
            // Add the error class, if applicable:
            if (array_key_exists($name, $errors)) {
                echo ' class="error">';
            } else {
                echo '>';
            }
            // Complete the textarea:
            echo '</textarea>';
        } // End of primary IF-ELSE.
    }

// End of the create_form_input() function.

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
            $value = Form::sanitizeData($value);
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
