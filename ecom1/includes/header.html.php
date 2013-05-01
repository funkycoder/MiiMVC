<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php // Use a default page title if one wasn't provided...
if (isset($page_title)) { 
		echo $page_title; 
} else { 
		echo 'Knowledge is Power: And It Pays to Know'; 
} 
?></title>
<link rel="stylesheet" href="../css/styles.css" type="text/css" />
</head>
<body>
<div id="wrap">
	<div class="header">
		<!-- TITLE -->
		<h1><a href="<?php BASE_URL.'/index.php' ?>">Knowledge is Power</a></h1>
		<h2>and it pays to know</h2>
		<!-- END TITLE -->
	</div>
	<div id="nav">
		<ul>
			<!-- MENU -->
			<?php // Dynamically create header menus...
			
			// Array of labels and pages (without extensions):
			$pages = array (
				'Home' => '/index.php',
				'About' => '#',
				'Contact' => '#',
				'Register' => '/login/index.php?action=register'
			);
				
			// Create each menu item:
			foreach ($pages as $k => $v) {
				
				// Start the item:
				echo '<li';
				
				// Add the class if it's the current page:
				if (isset($_GET['action']) && $_GET['action'] == strtolower($k)) echo ' class="selected"';
				
				// Complete the item:
				echo '><a href="'. BASE_URL. $v . '"><span>' . $k . '</span></a></li>
				';
				
			} // End of FOREACH loop.
			?>
			<!-- END MENU -->
		</ul>
	</div>
	<div class="page">
		<div class="content">
		
			<!-- CONTENT -->
			