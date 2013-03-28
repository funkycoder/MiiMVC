<?php
/**
 * DYNAMIC ONLINE GALLERY
 * ----------------------------------------------------------------------------------------------------------------
 * Source: PHP Solutions Dynamic Design Made Easy 2nd Edition / David Powers/ FriendsofEd(Apress)/ 2010/ Chapter 12
 * Version : 1.0
 * Date : 03/03/2013         
 * Modified : 04/03/2013
 * Modified by : Nguyen Nhu Quan
 * Reason : Take care of bad input (user may manually input too big/small $curPage or none existing image name)
 * -----------------------------------------------------------------------------
 * USAGE:
 * 
 * COLS = Number of column
 * SHOWMAX = Max number of pics / page
 * 
 */
include './includes/title.inc.php';
require_once './includes/connection.inc.php';

//define number of columns in table
define('COLS', 2);
//set maximum number of records
define('SHOWMAX', 6);
//initialize variables for the horizontal looper
$pos = 0;
$firstRow = TRUE;
//data connection
$conn = dbConnect('read', 'pdo');
//prepare SQL to get total records
$getTotal = 'SELECT COUNT(*) FROM images';
//submit query and store result as $totalPix
$total = $conn->query($getTotal);
$totalPix = $total->fetchColumn();
$total->closeCursor();
//set the current page
$curPage = isset($_GET['curPage']) ? $_GET['curPage'] : 0;
//calculate the first row of the subset
$startRow = $curPage * SHOWMAX;
//check if user enter very big $curPage manually
if ($startRow >= $totalPix) {
    $startRow = 0;
    $curPage = 0;
}
//prepare SQL to retrieve subset of image details
$sql = "SELECT filename,caption FROM images LIMIT $startRow," . SHOWMAX;
//submit the query
$result = $conn->query($sql);
//get any error message
$error = $conn->errorInfo();
if (isset($error[2]))
    die($error[2]);
//extract the first record as an array
$row = $result->fetch();

//Set the main image as the first image of the row
$mainImage = $row['filename'];
//check whether $_GET['image'] exists in the database (in case bad user input)
if (isset($_GET['image'])) {
    $getImage = 'SELECT COUNT(*) FROM images WHERE filename=?';
    $stmt = $conn->prepare($getImage);
    $stmt->execute(array($_GET['image']));
    $ok = $stmt->fetchColumn();
    $stmt->closeCursor();
    if ($ok) {
        //this image exist so it is ok
        $mainImage = $_GET['image'];
    }
}
//get the dimensions of the main image
$imageSize = getimagesize('images/' . $mainImage);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=windows-1258">
        <title>Japan Journey</title>
        <link href="styles/journey.css" rel="stylesheet" type="text/css" media="screen">
    </head>
    <body>
        <div id="header"><h1>Japan Journey</h1></div>
        <div id="wrapper"> 
            <?php include('./includes/menu.inc.php'); ?>
            <div id="maincontent">

                <h2>Images of Japan</h2>
                <p id="picCount">Displaying <?php
                    echo $startRow + 1;
                    if ($startRow + 1 < $totalPix) {
                        echo ' to ';
                        if ($startRow + SHOWMAX < $totalPix) {
                            echo $startRow + SHOWMAX;
                        } else {
                            echo $totalPix;
                        }
                    }
                    echo " of $totalPix";
                    ?>
                </p>
                <div id="gallery">
                    <table id="thumbs">
                        <tr>
                            <!-- This row needs to be repeated -->
                            <?php
                            do {
                                //set caption if thumbnail is same as main image
                                if ($row['filename'] == $mainImage) {
                                    $caption = $row['caption'];
                                }
                                //if remainder is 0 and not first row, close row and start a new one
                                if ($pos++ % COLS === 0 && !$firstRow) {
                                    echo '</tr><tr>';
                                }
                                //once the loop begins, this is no longer true
                                $firstRow = FALSE;
                                ?>
                                <td>
                                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?image=<?php echo $row['filename']; ?>&amp;curPage=<?php echo $curPage; ?>"><img src="images/thumbs/<?php echo $row['filename']; ?>"
                                                                                                                                                                 alt="<?php echo $row['caption']; ?>" width="80" height="54"
                                    </a>
                                </td>
                                <?php
                            } while ($row = $result->fetch());
                            while ($pos++ % COLS) {
                                echo '<td>&nbsp;</td>';
                            }
                            ?>
                        </tr>
                        <!-- Navigation link needs to go here -->
                        <tr>
                            <td>
                                <?php
                                //create back link if current page greater than 0
                                if ($curPage > 0) {
                                    echo '<a href="' . $_SERVER['PHP_SELF'] . '?curPage=' . ($curPage - 1) . '">&lt; Prev</a>';
                                } else {
                                    //otherwise leave the cell empty
                                    echo '&nbsp;';
                                }
                                ?>

                            </td>
                            <?php
                            //pad the final row with empty cells if more than 2 columns
                            if (COLS - 2 > 0) {
                                for ($i = 0; $i < COLS - 2; $i++) {
                                    echo '<td>&nbsp;</td>';
                                }
                            }
                            ?>
                            <td>
                                <?php
                                //create forward link if more records exist
                                if ($startRow + SHOWMAX < $totalPix) {
                                    echo '<a href="' . $_SERVER['PHP_SELF'] . '?curPage=' . ($curPage + 1) . '"> Next &gt;</a>';
                                } else {
                                    echo '&nbsp;';
                                }
                                ?>
                            </td>
                        </tr>

                    </table>
                    <div id ="main_image">
                        <p><img src="images/<?php echo $mainImage; ?>" alt="<?php echo $caption; ?>" <?php echo $imageSize[3]; ?>></p>
                        <p><?php echo $caption; ?></p>
                    </div>
                </div>
            </div>

            <?php include './includes/footer.inc.php'; ?>
        </div>

    </body>
</html>
