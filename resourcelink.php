<?php
define("DEBUG_OUT", FALSE);

require_once('on_off_campus_check.php');

  $global_config = parse_ini_file("common/configuration.ini", TRUE);
  $resources_config = parse_ini_file("resources/admin/configuration.ini", TRUE);

  $hostname = isset($_SERVER['SERVER_NAME']) && strlen($_SERVER['SERVER_NAME']) > 0 ? $_SERVER['SERVER_NAME'] : 'localhost';
  $host = $global_config['database']['host'];
  $username = $global_config['database']['username'];
  $password = $global_config['database']['password'];
  $databaseName = $resources_config['settings']['resourcesDatabaseName'];

	$authenticated = FALSE;
	
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
			
			$linkID = mysql_connect($host, $username, $password) or themedDie("error");
			mysql_select_db($databaseName, $linkID) or themedDie("Error Select (1).");

			$query = "SELECT r.resourceID, r.metalibID, r.titleText, r.resourceURL, ra.authenticationUserName, ra.authenticationPassword, ra.acquisitionTypeID FROM Resource r";
      $query .= " INNER JOIN ResourceAcquisition ra ON r.resourceID = ra.resourceID";
			
				if (stripos($request, "TEX") === false) {
					$query = $query . " Where r.resourceID = " . $request . "";
				} else {
					$query = $query . " Where metalibID = '" . $request . "'";
				}
				
			$rs = mysql_query($query, $linkID) or themedDie("Error Query:  " . mysql_errno($linkID));
			
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
						$userText = "<div class=\"authentication-required attention\">This resource requires authentication:</div>";
							if ($row["authenticationUserName"]) {
								$userText .= "<div class=\"authentication-name\"><div class=\"authentication-label attention\">User Name:</div><div title='User Name' class=\"authentication-value\">" . $row["authenticationUserName"] . '</div></div>';
							}
							
							if ($row["authenticationPassword"]) {
								$userText .= "<div class=\"authentication-password\"><div class=\"authentication-label attention\">Password:</div><div title='Password' class=\"authentication-value\">" . $row["authenticationPassword"] . '</div></div>';
							}	
						
						if ( strlen($resourceURL) > 0 ) {
							$userText .= "<div class=\"notes-user-authentication\">After you have recorded the authentication information, <a href='" . $resourceURL . "' class='button'>Connect to database's own interface</a>.</div>";
							$userText .= "<div class=\"notes-user-leave\">You are about to leave the Texas A&M University Libraries' website. The site may not comply with accessibility standards.</div>";
						}
						
						$userText .= "</span>";
						
						
					}
					
					// Check special notes
					$query = "SELECT noteText FROM " .  $databaseName . ".ResourceNote Where entityID = " . $request . " and noteTypeID = 11";
					$userNote = "";
					
					$rs2 = mysql_query($query, $linkID) or themedDie("Error Query:  " . mysql_errno($linkID));
						if ($row2 = mysql_fetch_assoc($rs2)) {
							debugout("I have a special notes");
							$userNote = "<div class=\"notes-note-special_instructions\">This resource has special access instructions:</div>";
							$userNote .= "<div class=\"notes-note-special_notes\" title='Special Access Notes'>" . $row2["noteText"] . '</div>';
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

  $redirect = (strlen($userText) == 0) && (strlen($userNote) == 0) ? $resourceURL : FALSE;

  printHeader($redirect);
  if (is_string($redirect)) {
      print('<div class="redirect">');
      print("<div class=\"redirect-leave\">You are about to leave the Texas A&M University Libraries' website. The site may not comply with accessibility standards.</div>");
      print("<div class=\"redirect-address\">If the page does not redirect to the databases's own interface press the <a href='" . $resourceURL . "' class='button'>Connect to database's own interface</a>.</div>");
      print('</div>');
  }
  else {
    print('<div class="notes">');
    print('<div class="notes-user">' . $userText . '</div>');
    print('<div class="notes-note">' . $userNote . '</div>');
    print('</div>');
  }
  printFooter();

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

function themedDie($message) {
  printHeader();
  print($message);
  printFooter();
  exit();
}

function printHeader($redirect = FALSE) {
  $basePath = NULL;
  $shortPath = NULL;
  $longPath = NULL;
  $canonicalPath = NULL;
  $helpdeskPath = 'https://helpdesk.library.tamu.edu';
  if (isset($_SERVER['SERVER_NAME']) && strlen($_SERVER['SERVER_NAME']) > 0 && isset($_SERVER['REQUEST_SCHEME']) && strlen($_SERVER['REQUEST_SCHEME']) > 0) {
    if (isset($_SERVER['SCRIPT_NAME']) && strlen($_SERVER['SCRIPT_NAME']) > 0) {
      $shortPath = $_SERVER['SCRIPT_NAME'];
      $basePath = dirname($shortPath);
    }

    $canonicalPath = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . $shortPath;
    $longPath = '//' . $_SERVER['SERVER_NAME'] . $basePath;
  } ?><!DOCTYPE html>
<html lang="en" dir="ltr" class="no-js">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">
    <?php if (is_string($redirect)) { ?>
      <meta content="1; URL='<?php print($redirect); ?>'" http-equiv="REFRESH"/><?php
      debugout("Nothing special move along");
    } ?>

    <base href="<?php print($basePath); ?>/">
    <?php if (isset($canonicalPath)) { ?>
      <link rel="canonical" href="<?php print($canonicalPath); ?>">
    <?php } ?>

    <link rel="shortcut icon" href="<?php print($helpdeskPath); ?>/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="<?php print($helpdeskPath); ?>/css/bootstrap.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="<?php print($helpdeskPath); ?>/css/tamu.css" media="all">
    <!--<link rel="stylesheet" type="text/css" href="css/survey.css">	-->
    <style type="text/css" media="all">
        #wrap {
          color: #ffffff;
        }

        #doContent {
          margin-top: 20px;
        }

        #doContent .breadcrumb a,
        #doContent .breadcrumb a:visited {
          color: #2f6fa7;
        }

        #doContent .content {
          text-align: center;
        }

        #doContent .content .attention {
          color: #cc0000;
        }

        #doContent .content .authentication-required,
        #doContent .content .authentication-name,
        #doContent .content .authentication-password {
          font-weight: bold;
        }

        #doContent .content .notes .notes-note .notes-note-special_instructions {
          font-weight: bold;
        }

        #doContent .content .notes .notes-user .button {
          color: #285f8f;
        }

        #doContent .content .notes .authentication-name,
        #doContent .content .notes .authentication-password,
        #doContent .content .notes .notes-note,
        #doContent .content .notes .notes-user .notes-user-authentication,
        #doContent .content .notes .notes-user .notes-user-leave,
        #doContent .content .notes .notes-note .notes-note-special_notes {
          margin-top: 12px;
        }
    </style>
    <style type="text/css" media="print">
        .no-print {
          display: none;
        }

        #banner {
          height: auto;
        }

        #footer {
          display: none;
        }
    </style>

    <script type="text/javascript" src="<?php print($helpdeskPath); ?>/js/jquery-2.1.4.min.js"></script>
    <script type="text/javascript" src="<?php print($helpdeskPath); ?>/js/bootstrap.min.js"></script>
  </head>
  <body>
    <header id="wrap">
      <nav class="navbar navbar-default no-print">
        <div class="container-fluid">
          <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
              <span class="sr-only">Toggle navigation</span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
            </button>
          </div>
          <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <span class="navbar-brand tamu-header-brand tamu-header-display">
              <a class="top-nav-link" href="<?php print($helpdeskPath); ?>"><img src="<?php print($helpdeskPath); ?>/images/tamu-logo-with-bar.png" alt="Texas A&amp;M University Libraries"></a>
              <a class="top-nav-link" href="<?php print($helpdeskPath); ?>"><span class="tamu-header-brand-text hidden-xs">Texas A&amp;M University Libraries</span></a>
            </span>
            <ul class="nav navbar-nav navbar-right">
              <li><a class="nav-link" href="//askus.library.tamu.edu/">Help</a></a></li>
            </ul>
          </div>
        </div>
      </nav>
      <div id="banner">
        <div class="container" role="heading">
          <h1>Resource Link</h1>
        </div>
      </div>
    </header>
    <section id="doContent" class="container well">
      <div class="row">
        <div class="span">
          <div class="content"><?php
}

function printFooter() { ?></div>
        </div>
      </div>
    </section>
    <footer id="footer">
      <div class="container text-center">
        <ul class="list-inline" role="navigation">
          <li><a href="//library.tamu.edu/giving/">Giving to the Libraries</a></li>
          <li><a href="//www.tamu.edu/">Texas A&amp;M University</a></li>
          <li><a href="//library.tamu.edu/about/employment/index.html">Employment</a></li>
          <li><a href="//library.tamu.edu/services/forms/contact-info.html">Webmaster</a></li>
          <li><a href="//library.tamu.edu/about/general-information/legal-notices.html">Legal</a></li>
          <li><a href="//askus.library.tamu.edu/">Comments</a></li>
          <li><a href="//library.tamu.edu/about/phone/">979-845-5741</a></li>
          <li><a href="//sugar.library.tamu.edu/">Staff Login</a></li>
        </ul>
      </div>
    </footer>
  </body>
</html><?php
} ?>
