<?php
define("DEBUG_OUT", FALSE);

require_once('on_off_campus_check.php');

if ($_SERVER["SERVER_NAME"] != 'localhost') {

 $host = "mysql2.l";
//  $host = "mysqldev.l";
	$username = "coral";
	$password = "va7uCUQ2";

 $databaseName = "coral_resources_prod";
	
//  $databaseName = "coral_resources_dev";

  $hostname = "coral.library.tamu.edu";

} else {

	$host = "localhost";
	$username = "root";
	$password = "kap14lah";
	$databaseName = "coral_resources_prod";
	$hostname = "localhost";
	
}	

	$authenticated = FALSE;
	$htmlText = "";
	
	$on_campus=TRUE;
	
		if (!$on_campus) {
			debugout("I am Off Campus.");
		
			if ($_SERVER['HTTP_X_USER']) {
				debugout("I have a header. Set auth true");
				$authenticated = TRUE;
			} else {
				debugout("I do not have a header send to Ezp.");
						header( "Location: http://ezproxy.library.tamu.edu/login?url=http://" . $hostname . $_SERVER[REQUEST_URI] ) ;
						//header( "Location: http://" . $hostname . $_SERVER[REQUEST_URI] ) ;
			}
		} else {
			debugout("I am on campus set auth true");
			$authenticated = TRUE;	
		}	
	
		if (isset($_GET['resource'])) {
		  $request = $_GET['resource'];
		} else {
		  $request = $_POST['resource'];
		}	

  try {
    $request = ( 0 + $request );
  } catch (Exception $e) {
    $request = 0;
  }
		
  if (!is_int($request)) {
    $request = 0;
  }
    
    if ( ($authenticated) && (strlen($request) > 0) ) {
			// Ok I am good so lets get the resource
			
			$linkID = mysql_connect($host, $username, $password) or die("error");
			mysql_select_db($databaseName, $linkID) or die("Error Select (1).");

			$query = "SELECT resourceID, metalibID, titleText, resourceURL, authenticationUserName, authenticationPassword, acquisitionTypeID FROM Resource";
			
				if (stripos($request, "TEX") === false) {
					$query = $query . " Where resourceID = " . $request . "";
				} else {
					$query = $query . " Where metalibID = '" . $request . "'";
				}
				
			$rs = mysql_query($query, $linkID) or die("Error Query:  " . mysql_errno($linkID));
			
				if ($row = mysql_fetch_assoc($rs)) {
				
					$resourceURL = "";
					
					if ($row["resourceURL"]) {
						debugout("I have a url");
						$resourceURL = $row["resourceURL"];
					}
					
					// Check for username and password
					$userText = "";
					
					if (($row["authenticationUserName"]) || ($row["authenticationPassword"])) {					
						debugout("I have a username or password ");
						$userText = "<font color='red'>This resource requires authentication:</font><br><br>";
							if ($row["authenticationUserName"]) {
								$userText = $userText . "<font color='red'>User Name:</font> <label for='User Name'>" . $row["authenticationUserName"] . '</label>';
								$userText = $userText . "<br>";					
							}
							
							if ($row["authenticationPassword"]) {
								$userText = $userText . "<font color='red'>Password:</font> <label for='Password'>" . $row["authenticationPassword"] . '</label>';
								$userText = $userText . "<br>";				
							}	
						
						if ( strlen($resourceURL) > 0 ) {
							$userText = $userText . "After you have recorded the authentication information, <a href='" . $resourceURL . "' class='button'>Connect to database's own interface</a>.";
							$userText = $userText . "<br><br>You are about to leave the Texas A&M University Libraries' website. The site may not comply with accessibility standards.";							
						}
						
						$userText = $userText . "</font>";
						
						
					}
					
					// Check special notes
					$query = "SELECT noteText FROM " .  $databaseName . ".ResourceNote Where resourceID = " . $request . " and noteTypeID = 11";
					$userNote = "";
					
					$rs2 = mysql_query($query, $linkID) or die("Error Query:  " . mysql_errno($linkID));
						if ($row2 = mysql_fetch_assoc($rs2)) {
							debugout("I have a special notes");
							$userNote = "<font>This resource has special access instructions:</font><br><br>";
							$userNote = $userNote . "<font><label for='Special Access Notes'>" . $row2["noteText"] . '</label>';
							$userNote = $userNote . "<br></font>";		
						}

				}	
		

if ($row["acquisitionTypeID"] == 2) {
  $resourceURL = $resourceURL;
} else {
	if ($resource['resourceID'] != 2477) {
		$resourceURL = "http://ezproxy.library.tamu.edu/login?url=" . $resourceURL;
	} else {
		$resourceURL = "http://lib-ezproxy.tamu.edu/login?url=" . $resourceURL;
	}	
}

?>

		<!DOCTYPE html>
		<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
		<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
		<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
		<!--[if gt IE 8]>      <html class="no-js ie"> <![endif]-->
		<!--[if !IE ]><!--> <html class="no-js"> <!--<![endif]-->
	
			<head>
				<link href="ico/favicon.ico" rel="shortcut icon">
				<link rel="stylesheet" type="text/css" href="css/survey.css" />	
				<?php
					if ( (strlen($userText) == 0) && (strlen($userNote) == 0) ) {
				?>
        <meta content="1; URL='<?php echo $resourceURL; ?>'" http-equiv="REFRESH"/>
        <?php
						debugout("Nothing special move along");
						$htmlText = "<font>You are about to leave the Texas A&M University Libraries' website. The site may not comply with accessibility standards.";
						$htmlText = $htmlText . "<br><br>If the page does not redirect to the databases's own interface press the <a href='" . $resourceURL . "' class='button'>Connect to database's own interface</a>.</font>";
					}
				?>
			</head>
			<body>
				<div id="header">
					<div class="navbar">
						<div class="navbar-inner">
							<div class="container-fluid">

								<a class="brand" href="http://library.tamu.edu/"><img src="img/logo.png" alt="Library logo"></a>

							</div>
						</div>
					</div>  
				</div>
				<div class="container well">
					<div class="row">
						<div class="span">

							<p style="text-align:center">
							
								<?php
								echo $userText;
								echo $userNote;
								echo $htmlText;
								?>				
															
							</p>
			
						</div>    
					</div>
				</div>   
				<script>
				  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
				  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
				  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
				  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
				  ga('create', 'UA-54069646-7', 'auto', {'name': 'allSites'});  // All Sites
				  ga('allSites.send', 'pageview'); // Send page view for new tracker.
				  ga('create', 'UA-54069646-3', 'auto');
				  ga('send', 'pageview');
				</script>				
			</body>
		</html>

<?php
			mysql_close($linkID);	
			
		} else {
			debugout("I could not autheticate");
		}

function debugout($info_out)
{ 
	if (DEBUG_OUT) {
		echo $info_out . "<br>";
	}
}

?>
