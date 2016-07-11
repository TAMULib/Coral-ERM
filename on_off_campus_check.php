<?php

if (in_ip_range('165.95.67.128','165.95.67.255')) {
	$on_campus = TRUE;
} elseif (in_ip_range('165.95.68.0','165.95.68.128')) {
	$on_campus = TRUE;
} elseif (in_ip_range('10.0.0.1','10.255.255.254')) {
	$on_campus = TRUE;
} elseif (in_ip_range('172.16.0.1','172.31.255.254')) {
	$on_campus = TRUE;
} elseif (in_ip_range('192.168.0.1','192.168.255.254')) {
	$on_campus = TRUE;
} elseif (in_ip_range('165.95.70.0','165.95.70.128')) {
	$on_campus = TRUE;
} elseif (in_ip_range('165.95.118.0','165.95.118.255')) {
	$on_campus = TRUE;
} elseif (in_ip_range('165.95.82.0','165.95.82.255')) {
	$on_campus = TRUE;
} elseif (in_ip_range('165.95.84.0','165.95.84.255')) {
	$on_campus = TRUE;
} elseif (in_ip_range('165.95.85.0','165.95.85.255')) {
	$on_campus = TRUE;
} elseif (in_ip_range('165.95.204.0','165.95.204.255')) {
	$on_campus = TRUE;
} elseif (in_ip_range('165.95.205.0','165.95.205.255')) {
	$on_campus = TRUE;
} elseif (in_ip_range('165.95.206.0','165.95.206.255')) {
	$on_campus = TRUE;
} elseif (in_ip_range('165.95.207.0','165.95.207.255')) {
	$on_campus = TRUE;
} elseif (in_ip_range('63.72.6.0','63.72.6.255')) {
	$on_campus = TRUE;
} elseif (in_ip_range('128.194.0.0','128.194.77.254')) {
	$on_campus = TRUE;
} elseif (in_ip_range('128.194.79.0','128.194.198.254')) {
	$on_campus = TRUE;	
} elseif (in_ip_range('128.194.200.0','128.194.255.255')) {
	$on_campus = TRUE;		
} elseif (in_ip_range('165.91.0.0','165.91.47.254')) {   
	$on_campus = TRUE;
} elseif (in_ip_range('165.91.50.0','165.91.255.254')) {
	$on_campus = TRUE;	
} elseif (in_ip_range('165.95.40.0','165.95.49.255')) {
	$on_campus = TRUE;
} elseif (in_ip_range('165.95.51.0','165.95.54.255')) {
	$on_campus = TRUE;
} elseif (in_ip_range('165.95.181.0','165.95.184.255')) {
	$on_campus = TRUE;
} elseif (in_ip_range('165.95.254.214')) {
	$on_campus = TRUE;
} elseif (in_ip_range('204.56.160.0','204.56.167.255')) {
	$on_campus = TRUE;
} elseif (in_ip_range('184.174.192.0','184.174.255.255')) {
	$on_campus = TRUE;
} elseif (in_ip_range('192.195.90.230','192.195.90.205')) {
	$on_campus = TRUE;
} elseif (in_ip_range('192.195.90.205')) {
	$on_campus = TRUE;
} elseif (in_ip_range('165.95.232.0','165.95.239.255')) {
	$on_campus = TRUE;
} elseif (in_ip_range('165.91.12.244')) {
	$on_campus = TRUE;	
} else {
	$on_campus = FALSE;
}

if (in_ip_range('192.195.93.249')) {  // Qatar
	$on_campus = TRUE;
}

try {

	if ($on_campus) {
		$on_off_campus = "ON";
	} else {
		$on_off_campus = "OFF";
	}

	
} catch (Exception $e) {
	echo 'Caught exception: ',  $e->getMessage(), "\n";
}


function in_ip_range($ip_one, $ip_two=false){
	$Users_IP_address = VisitorIP();

    if($ip_two===false){
        if($ip_one==$Users_IP_address){
            $ip=true;
        }else{
            $ip=false;
        }
    }else{
        if(ip2long($ip_one)<=ip2long($Users_IP_address) && ip2long($ip_two)>=ip2long($Users_IP_address)){
            $ip=true;
        }else{
            $ip=false;
        }
    }
    return $ip;
}

function VisitorIP()
{ 
	if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		$TheIp=$_SERVER['HTTP_X_FORWARDED_FOR'];
	else $TheIp=$_SERVER['REMOTE_ADDR'];

return trim($TheIp);
}
 
?>