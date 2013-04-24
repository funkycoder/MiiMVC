<!-- END CONTENT -->
<p><br clear="all" /></p>
</div> //<!-- end content div-->

<div class="sidebar">	

    <!-- SIDEBAR -->

    <?php
    // Show the user info or the login form:
    if ($user->logged_in) {

        // Show basic user options:
        // Includes references to some bonus material discussed in Chapter 5!
        echo '<div class="title">
				<h4>Manage Your Account</h4>
			</div>
			<ul>
			<li><a href="renew.php" title="Renew Your Account">Renew Account</a></li>
			<li><a href="change_password.php" title="Change Your Password">Change Password</a></li>
			<li><a href="favorites.php" title="View Your Favorite Pages">Favorites</a></li>
			<li><a href="history.php" title="View Your History">History</a></li>
			<li><a href="recommendations.php" title="View Your Recommendations">Recommendations</a></li>
			<li><a href="logout.php" title="Logout">Logout</a></li>
			</ul>
			';

        // Show admin options, if appropriate:
        if ($user->type=='admin') {
            echo '<div class="title">
					<h4>Administration</h4>
				</div>
				<ul>
				<li><a href="add_page.php" title="Add a Page">Add Page</a></li>
				<li><a href="add_pdf.php" title="Add a PDF">Add PDF</a></li>
				<li><a href="#" title="Blah">Blah</a></li>
				</ul>
				';
        }
    } else { // Show the login form:
        require (__DIR__ . '/login_form.html.php');
    } // End log-in or not logged in menu
    ?>

    <div class="title">
        <h4>Content</h4>
    </div>
    <ul>

<?php
//TODO Enable this later on
//// Dynamically generate the content links:
//$q = 'SELECT * FROM categories ORDER BY category';
//$r = mysqli_query($dbc, $q);
//while (list($id, $category) = mysqli_fetch_array($r, MYSQLI_NUM)) {
//	echo '<li><a href="category.php?id=' . $id . '" title="' . $category . '">' . $category . '</a></li>';
//}
?>
        <li><a href="pdfs.php" title="PDF Guides">PDF Guides</a></li>
    </ul>

</div> <!--end of SIDE BAR -->
<div class="footer">
    <p><a href="site_map.php" title="Site Map">Site Map</a> | <a href="policies.php" title="Site Policies">Policies</a> &nbsp; - &nbsp; &copy; Knowledge is Power &nbsp; - &nbsp; Design by <a href="http://www.spyka.net">spyka webmaster</a></p> 
</div>	
</div> <!-- end page-->
</div> <!--end wrap -->
</body>
</html>