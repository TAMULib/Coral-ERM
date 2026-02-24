<?php
use Custom\Lib\Classes as Classes;
use Custom\Lib\Exceptions\SAMLException;

//Have to load Coral native classes the old way
require_once __DIR__."/../../auth/admin/classes/domain/Session.php";

//autoloader for composer supplied dependencies
$autoLoader = require_once __DIR__."/../vendor/autoload.php";

//autoloader for our TAMU custom classes
spl_autoload_register(function($class) {
    if (str_contains($class, "Custom\\Lib\\" )) {
        $customClassPath = str_replace("Custom\\Lib\\", "custom\\lib\\", $class);
        $file = __DIR__."/../../".str_replace('\\', '/', $customClassPath).'.php';
        if (file_exists($file)) {
            require $file;
        }
    }
});

try {
    if (!isset($config)) {
        throw new SAMLException("Coral config is missing");
    }
    //$service should always be set in the auth index file but just in case
    if (!isset($service)) {
        $service = isset($_GET['service']) ?$_GET['service'] : $util->getCORALURL();
    }
    $samlUser = new Classes\SAMLUser($config, $util, new Session());

    if (!empty($_POST['SAMLResponse'])) {
        if (is_string($_POST['SAMLResponse'])) {
            $samlUser->processLogIn();
            exit;
        }
    } else {
        $samlUser->initiateLogIn($service);
    }
} catch (SAMLException $e) {
    echo 'There was an error with the login process.';
    error_log("SAML Login problem: ".$e->getMessage());
}
?>
