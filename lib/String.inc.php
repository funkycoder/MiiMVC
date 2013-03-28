<?php

/**
 * STRING CLASS
 * 
 * ----------------------------------------------------------------------------------------
 * Version : 1.0
 * Date : 27/02/2013
 * @author Nguyen Nhu Quan FunkyCoder <bsquan2009@gmail.com>
 * ----------------------------------------------------------------------------------------
 */

namespace Mii;

class Mii_String {

    /**
     * Provide a specified number of first words in a string
     * 
     * Source: PHP Solutions Dynamic Design Made Easy 2nd Edition / David Powers/ FriendsofEd(Apress)/ 2010/ Chapter 7
     * Version : 1.0
     * Date : 18/02/2013
     * Modified date: 18/02/2013
     * Modified by : Nguyen Nhu Quan
     * Reason: Convert to class function.          
     * 
     * @param string $string Specify a string
     * @param $number Set a number of words to extract
     * @return string String result
     */
    public static function getFirstWords($string, $number) {
        // split the string into an array of words 
        $words = explode(' ', $string);
        // extract the first number elements of the array 
        $first = array_slice($words, 0, $number);
        // join the first $number elements and display 
        return implode(' ', $first);
    }

    /**
     * Get first paragraph in a textblock
     * 
     * Source: PHP Solutions Dynamic Design Made Easy 2nd Edition / David Powers/ FriendsofEd(Apress)/ 2010/ Chapter 13
     * Version : 1.0
     * Date : 05/03/2013
     * @param $text the text block to extract
     * @return string the first paragraph of the text
     */
    public static function getFirstParagraph($text) {
        // find the first occurence of PHP_EOL (cross platform EOL character)
        //substring it from the beginning to that character

        return substr($text, 0, strpos($text, PHP_EOL));
    }

    /**
     * Get some number of first paragraphs in a textblock
     * 
     * Version : 1.0
     * Date : 05/03/2013
     * Author: Nguyen Nhu Quan      
     * 
     * @param $text the text block to extract
     * @param $number the number of paragraphs to extract
     * @param $formatted boolean convert to paragraphs with <p> 
     * @param $class the CSS class of <p>
     * @return string the first paragraph of the text
     */
    public static function getFirstParagraphs($text, $number = 1, $formatted = TRUE, $class = '') {
        // split the text into an array of paragraphs 
        $paragraphs = explode(PHP_EOL, $text);
        // extract the first number elements of the array 
        $first = array_slice($paragraphs, 0, $number);
        // join the first $number elements and display 
        $rawParagraphs = implode(PHP_EOL, $first);
        //Need formatted?
        if ($formatted) {
            return Mii_String::convertToParagraphs($rawParagraphs, $class);
        } else {
            return $rawParagraphs;
        }
    }

    /**
     * Get some first sentences in a text
     * 
     * Source: PHP Solutions Dynamic Design Made Easy 2nd Edition / David Powers/ FriendsofEd(Apress)/ 2010/ Chapter 14
     * Version : 1.0
     * Date : 05/03/2013
     * Modified date: 05/03/2013
     * Modified by : Nguyen Nhu Quan
     * Reason: Modify the expression add [A-Z] looking for capitalized letter after a period.
     * @param $text the text block to extract
     * @param $number number of sentences
     * @return array first element is the sentences, second element is the remainder
     */
    public static function getFirstSentences($text, $number = 2) {
        // use regex to split into sentences 
        //regex to identify the end of each sentence
        //—a period, question mark, or exclamation point, optionally followed by a double or single quotation mark and a space or capitalized letter
        //The second argument is the target text. The third argument determines the maximum number of chunks to split the text into.
        // the elements of the $sentences array consist alternately of the text of a sentence followed by the punctuation and space.
        $sentences = preg_split('/([.?!]["\']?[\sA-Z])/', $text, $number + 1, PREG_SPLIT_DELIM_CAPTURE);
        //The conditional statement uses count()to ascertain the number of elements in the $sentences array 
        //and compares the result with $numbermultiplied by 2 (because the array contains two elements for each sentence)
        if (count($sentences) > $number * 2) {
            //array_pop()removes the last element of the $sentencesarray and assigns it to $remainder
            $remainder = array_pop($sentences);
        } else {
            $remainder = '';
        }
        $result = array();
        $result['sentences'] = implode('', $sentences);
        $result['remainder'] = $remainder;
        return $result;
    }

    /**
     * Convert all return carriage / new line character to paragraph tag <p> for displaying purposes
     * 
     * Source: PHP Solutions Dynamic Design Made Easy 2nd Edition / David Powers/ FriendsofEd(Apress)/ 2010/ Chapter 14
     * Version : 1.0
     * Date : 05/03/2013
     * Modified date: 05/03/2013
     * Modified by : Nguyen Nhu Quan
     * Reason: Add class support for <p>
     * @param $text the text to convert
     * @param $class the CSS class of <p>
     * @return string return formatted paragraph
     */
    public static function convertToParagraphs($text, $class = '') {
        $text = trim($text);
        //prepare opening <p> tag
        $p = '<p';
        //append the class attribute if not empty
        if (!empty($class)) {
            $class = ' class="' . $class . '"';
        }
        $p = $p . $class . '>';
        //The regular expression used as the first argument matches one or more 
        //carriage returns and/or newline characters
        return $p . preg_replace('/[\r\n]+/', '</p>' . $p, $text) . '</p>';
    }

}

?>
