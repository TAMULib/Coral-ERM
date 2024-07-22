<?php
namespace Custom;
use Custom\Lib\Classes as Classes;
use Custom\Lib\Exceptions;

//require_once __DIR__.'/Classes/SAMLUser.php';
require_once __DIR__."/../../auth/admin/classes/domain/Session.php";
$autoLoader = require_once __DIR__."/../vendor/autoload.php";

if (!isset($config)) {
    throw new \RuntimeException("SAML auth: Coral Config is missing");
}

if (!array_key_exists('loginID', $_SESSION) || empty($_SESSION['loginID'])) {
    if (isset($_GET['service'])) {
        $service = $_GET['service'];
    } else {
        $service = $util->getCORALURL();
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
}
?>
