<?php
#########################################
## REGULAR EXPRESSION
#########################################
//Valid email address
/*
     *  The first section needs to match any hyphens (-) and word characters (symbolized by \w�this 
     *  matches the letters, numbers, and underscore [_]) from the beginning of the string to the at (@) symbol.
     *  However, you should keep in mind that some email addresses use periods (.) as well, as in
     *  john.doe@email.com.By placing the second part of the pattern in parentheses with the star (*) following, 
     *  you can make this section required zero or more times, which effectively makes it optional. 
     * Remember that the period (.) in regular expressions serves as a wildcard and matches nearly 
     * anything. To match only a period, be sure to escape the period with a backslash (\.) 
     * 
     * Domain names cannot contain the underscore character (_), so this part of the pattern cannot use the word 
     * character shortcut (\w). Instead, you have to set up a character set manually that allows for letters, 
     * numbers, and the hyphen (-), and then require that one or more matches exist. As in the first section, 
     * your pattern must accommodate an optional set that matches the period (.) in the event a user�s email address 
     * is from a subdomain, such as johndoe@sales.example.com
     * 
     * Finally, the pattern must match a top-level domain, such as .comor .net. This is as simple as 
     * creating a set of the letters a-z and requiring between two and four total characters that are at the end of
     * the string
     * 
     * Next, wrap this pattern in the required forward slash (/) delimiter, then append the i flag to 
     * make the pattern case-insensitive.
     * */
define('REG_VALID_EMAIL', '/^[\w-]+(\.[\w-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i');
//Suspicous email address
define('REG_SUSPECT_EMAIL_PATTERN','/Content-Type:|Bcc:|Cc:/i');
?>
