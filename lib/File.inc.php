<?php

/**
 * FILE CLASS
 * 
 * ----------------------------------------------------------------------------------------
 * Version : 1.0
 * Date : 27/02/2013
 * @author Nguyen Nhu Quan FunkyCoder <bsquan2009@gmail.com>
 * ----------------------------------------------------------------------------------------
 * STATIC FUNCTIONS:
 * buildFileList($dir, $extensions)  
 * readRSS($url, $numberOfItems = null)
 * checkFileName($dir, $name, $overwrite)
 * addSlashToPathName($dir)
 * protocol()
 * replaceBaseNameInURL($name)
 */

namespace Mii;

class Mii_File {

    /**
     * Provide a list of file name with a certain kind of extentions in a specified directory 
     * 
     * Source: PHP Solutions Dynamic Design Made Easy 2nd Edition / David Powers/ FriendsofEd(Apress)/ 2010/ Chapter 7
     * Version : 1.1
     * Date : 18/02/2013
     * Modified date: 18/02/2013
     * Modified by : Nguyen Nhu Quan
     * Reason: Convert to class function.          
     * 
     * @param string $dir Specify a directory
     * @param $extension string[] Specify an array of file extensions
     * @return string[] $filenames 
     * -----------------------------------------------------------------------------
     * USAGE:
     * 
     * $dir = '/home/quan/testUploadFiles';
     * $extensions = array('jpg', 'jpeg', 'png');
     * $OK = new Mii\Files()
     * $images = $OK->buildFileList($dir, $extensions);
     * foreach ($images as $image)
     * option value="<?php echo $image; ?>"><?php echo $image; ?></option>
     * 
     */
    public static function buildFileList($dir, $extensions) {
        //make sure the $dir exists and available
        if (is_dir($dir) && is_readable($dir)) {
            //if $extensions an array then make a prepared string for all values
            if (is_array($extensions)) {
                $extensions = implode('|', $extensions);
            }
            //build the regex
            $pattern = "/\.(?:{$extensions})$/i";

            //get the folder
            $filesInFolder = new \DirectoryIterator($dir);
            //get the files folows the pattern
            $files = new \RegexIterator($filesInFolder, $pattern);
            //initialize an array and fill it with file names
            $filenames = array();
            foreach ($files as $file) {
                $filenames[] = $file->getFilename();
            }
            //sort the array naturally then return it
            \natcasesort($filenames);
            return $filenames;
        } else {
            return FALSE;
        }
    }

    /**
     * Provide a list of items from a RSS feed
     * Must set rss url via setURLFeed();
     * 
     * Source: PHP Solutions Dynamic Design Made Easy 2nd Edition / David Powers/ FriendsofEd(Apress)/ 2010/ Chapter 7
     * Version : 1.1
     * Date : 18/02/2013
     * Modified date: 18/02/2013
     * Modified by : Nguyen Nhu Quan
     * Reason: Convert to class function.
     *         Return the same kind of item list ($feed->channel->item) for 2 situations      
     * 
     * @param int $numberOfItems limit the number of items in the list
     * @return item list 
     * -----------------------------------------------------------------------------
     * USAGE:
     * 
     * $feed = new File();
     * $feed->setUrlFeed('http://rss.cnn.com/rss/edition.rss');
     * $fullFeed= $feed->readRSS;
     * $filteredFeed=$feed->readRSS(5)
     * foreach($fullFeed as $item) {echo $item->title.'<br>';}
     * foreach($filtered as $item) {echo $item->title.'<br>';}
     * 
     */
    public static function readRSS($url, $numberOfItems = null) {

        if (isset($numberOfItems) && is_numeric($numberOfItems)) {
            $feed = simplexml_load_file($url, 'SimpleXMLIterator');
            return new \LimitIterator($feed->channel->item, 0, $numberOfItems);
        } else {
            $feed = simplexml_load_file($url);
            return $feed->channel->item;
        }
    }

    /**
     * Check for existed file name, overwrite or change name available
     * 	 
     * Remove all spaces in file name and replace with _ (security issue)
     * Source: PHP Solutions Dynamic Design Made Easy 2nd Edition / David Powers/ FriendsofEd(Apress)/ 2010/ Chapter 6
     * Version : 1.0
     * Date : 14/02/2013
     * Modified date:
     * Modified by : 
     * Reason: 
     * 
     * @param string $name any file name
     * @param bool $overwrite yes? 
     * @return string $nospaces file name
     */
    public static function checkFileName($dir, $name, $overwrite) {
        //Remove all the space in $name by '_'
        $nospaces = str_replace(' ', '_', $name);

        if (!$overwrite) {
            //rename the file if it already exists
            //Get all the file names in the directory
            $existing = scandir($dir);
            //If file name exist
            if (in_array($nospaces, $existing)) {
                //Separate filename to base and extension by dot position
                $dot = strrpos($nospaces, '.');
                if ($dot) {
                    $base = substr($nospaces, 0, $dot);
                    $extension = substr($nospaces, $dot);
                } else {
                    //No dot? then file has no extension
                    $base = $nospaces;
                    $extension = '';
                }
                $i = 1;
                do {
                    $nospaces = $base . '_' . $i++ . $extension;
                } while (in_array($nospaces, $existing));
            }
        }
        //return the unique name
        return $nospaces;
    }

    public static function addSlashToPathName($dir) {
        //get the last character
        $last = substr($dir, -1);
        //add a trailing slash if missing (second condition using an escapte back slash)
        if ($last == '/' || $last == '\\') {
            return $dir;
        } else {
            return $dir . DIRECTORY_SEPARATOR;
        }
    }

    public static function protocol() {
        //$protocol = strpos($_SERVER['SERVER_SIGNATURE'], '443') ? 'https://' : 'http://';
        //$protocol= strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'?  'https://' : 'http://';
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
        return $protocol;
    }

    public static function replaceBaseNameInURL($name) {
        $filename = basename($_SERVER['SCRIPT_FILENAME']);
        $current = Mii_File::protocol() . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
        $url = str_replace($filename, $name, $current);
        return $url;
    }

    /**
     * Returning to the same point in a navigation system
     * 	 
     * Source: PHP Solutions Dynamic Design Made Easy 2nd Edition / David Powers/ FriendsofEd(Apress)/ 2010/ Chapter 15
     * Version : 1.0
     * Date : 07/03/2013
     * @param string $defaultURL default referer url
     * @return string referer link
     */
    public static function refererURL($defaultURL) {
        // check that browser supports $_SERVER variables 
        if (isset($_SERVER['HTTP_REFERER']) && isset($_SERVER['HTTP_HOST'])) {
            $url = parse_url($_SERVER['HTTP_REFERER']);
            // find if visitor was referred from a different domain 
            if ($url['host'] == $_SERVER['HTTP_HOST']) {
                // if same domain, use referring URL 
                return $_SERVER['HTTP_REFERER'];
            }
        }
        return $defaultURL;
    }

}

?>
