<?php
  
##
## Note:  This function uses the "header" command, which 
##        MUST be called before anything else is written
##        to the screen.  i.e. make this the first thing
##        you do.
##
##        These functions do not require arguments to be
##        passed to them
##
##        Returns variables $sUin and $sNetid
##

function getCAS($cas_base, $casIP) {
  
  ##
  ## Set CAS server variables
  ##
  
//  $cas_base = $sugar_config['CAS_Server'];
  $cas_login = "cas/login";
  $cas_check = "cas/serviceValidate";    // cas 2.0 method (xml return)
  $cas_logout = "cas/logout";

  $SERVER_NAME = $_SERVER['SERVER_NAME'];
  $REQUEST_URI = $_SERVER['REQUEST_URI'];
  $PHP_SELF = $_SERVER['PHP_SELF'];
  $QUERY_STRING = $_SERVER['QUERY_STRING'];
  
	  if (isset($_SERVER['HTTPS'])) {
		$http = "https";
	  } else {
		$http = "http";
	  }

	$http = "https";

  ##
  ## Validate through CAS
  ##

  ##
  ## Get the CAS ticket
  ##
  if (isset($_GET['ticket'])) {
    $ticket = $_GET['ticket'];
  }  else {
	  $ticket="";
  }

  ##
  ## Separate ticket from other GET variables
  ##
    
	if ( preg_match("/&ticket=/", $QUERY_STRING) || ($QUERY_STRING && !$ticket) ) {
		list ($getVars, $ticket) = explode("&ticket=", $QUERY_STRING);
		
		$getVars = "?" . $getVars;
		$myService = $PHP_SELF . $getVars;
	} else {
		$myService = $PHP_SELF;
	}

	$myService = urlencode($myService);
	
  if ($ticket) {
    $file = @file("https://$cas_base/$cas_check?service=$http://$SERVER_NAME$myService&ticket=$ticket");
	//echo "<br>https://$cas_base/$cas_check?service=$http://$SERVER_NAME$myService&ticket=$ticket<br>";	

	if (!$file) {
		  $file = file("https://$casIP/$cas_check?service=$http://$SERVER_NAME$myService&ticket=$ticket");
		//	echo "<br>2<br>";		  
		}
		
		if (!$file) {  // this still needs work to include the error function and closing string
		  die("The authentication process failed to validate through CAS.");
		}
  } 
  else {
    $action="https://$cas_base/$cas_login?service=$http://$SERVER_NAME$myService";
    header("Location: $action");
  }

  ##
  ## Now parse the xml return
  ##

  // general vars
  $sNetid = "";
  $sUin = "";
  $sFail = "";
  $casNetid = "";
  $casUIN = "";
  $arItems = array();
  $itemCount = 0;  
 
  // parse xml, send to functions
  $xml_parser = xml_parser_create();
  xml_set_element_handler($xml_parser, "startElement", "endElement");
  xml_set_character_data_handler($xml_parser, "characterData");

  // loop through CAS response stream
  if ($file) {
    foreach ($file as $data) {
      if (!xml_parse($xml_parser, $data)) {
        die(sprintf("XML error: %s from CAS server at line %d", xml_error_string(xml_get_error_code($xml_parser)), xml_get_current_line_number($xml_parser)));
      }
    }
  } 
  xml_parser_free($xml_parser);
}

function startElement($parser, $name, $attrs) {
  global $curTag;
  $curTag .= "^$name";
}

function endElement($parser, $name) {
  global $curTag;
  $caret_pos = strrpos($curTag,'^');
  $curTag = substr($curTag,0,$caret_pos);
}
    
// get the xml information
function characterData($parser, $data) {
  global $curTag;
  global $sNetid, $sUin, $sFail, $casNetid, $casUIN;
  
  $netidKey = "^CAS:SERVICERESPONSE^CAS:AUTHENTICATIONSUCCESS^CAS:USER";
  $uinKey = "^CAS:SERVICERESPONSE^CAS:AUTHENTICATIONSUCCESS^CAS:ATTRIBUTES^CAS:TAMUEDUPERSONUIN";

  if ($curTag == $netidKey) {
    $sNetid = $data;
	$_SESSION['sNetid'] = $sNetid;
    $_SESSION['loginID'] = $sNetid; // store the net id data

// This is a hack to ensure the account exists	

	$global_config = parse_ini_file("../common/configuration.ini", TRUE);

	$host = $global_config['database']['host'];
	$username = $global_config['database']['username'];
	$password = $global_config['database']['password'];
	$databaseName = "coral_auth_prod";
	
	$linkID = mysqli_connect($host, $username, $password) or themedDie("error");
	mysqli_select_db($linkID, $databaseName) or themedDie("Error Select (1).");	
	
	$query = "SELECT * FROM coral_auth_prod.User where loginID = '" . $sNetid . "';";
	$rs_users = mysqli_query($linkID, $query) or themedDie("Error Query:  " . mysqli_errno($linkID));	
	
		if (mysqli_num_rows($rs_users) == 0) {
			themedDie("Account does not exist.");
		}
		
//
	
} elseif ($curTag == $uinKey) {  
    $sUin = $data;

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
          <h1>Account Error</h1>
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
          <li><a href="//library.tamu.edu/about/general-information/legal-notices.html">Legal</a></li>
           <li><a href="//library.tamu.edu/about/phone/">979-845-5741</a></li>
        </ul>
      </div>
    </footer>
  </body>
</html><?php
}

?>
