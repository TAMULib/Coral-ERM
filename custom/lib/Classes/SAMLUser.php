<?php
namespace Custom\Lib\Classes;
use OneLogin\Saml2 as OneLogin;

class SAMLUser
{
    private const CONFIG_FILE = "saml.config.ini";
    private const DEFAULT_USERNAME_MAPPING = "netid";

    private $coralConfig;
    private $coralUtil;
    private $coralSession;
    private $settings;

    public function __construct($coralConfig, $coralUtil, $coralSession)
    {
        $this->coralConfig = $coralConfig;
        $this->coralUtil = $coralUtil;
        $this->coralSession = $coralSession;
        $this->loadSettings();
    }

    /**
     * Triggers the SAML login request
     */
    public function initiatelogIn($redirectTo)
    {
        $auth = new OneLogin\Auth($this->settings);
        $auth->login($redirectTo);
    }

    public function processLogIn()
    {
        $auth = new OneLogin\Auth($this->settings);

        if (isset($_SESSION) && isset($_SESSION['AuthNRequestID'])) {
            $requestId = $_SESSION['AuthNRequestID'];
        } else {
            $requestId = null;
        }

        $auth->processResponse($requestId);
        unset($_SESSION['AuthNRequestID']);

        $errors = $auth->getErrors();

        if (!empty($errors)) {
            throw new \RuntimeException("SAML error: " . implode(', ', $errors));
        }

        if (!$auth->isAuthenticated()) {
            throw new \RuntimeException("SAML error: Not authenticated");
        }

        $userNameField = array_key_exists('username', $this->settings['claims']) ? $this->settings['claims']['username'] : self::DEFAULT_USERNAME_MAPPING;

        if (!array_key_exists($userNameField, $auth->getAttributes())) {
            throw new \RuntimeException("SAML error: {$userNameField} claim not present in SAML response");
        }

        $samlUserName = $auth->getAttributes()[$userNameField][0];
        if ($this->processUser($samlUserName)) {
            $auth->redirectTo($_POST['RelayState']);
        } else {
            throw new \RuntimeException("SAML auth: Error processing login");
        }
    }

    /**
     *	Uses the provided username to find/create a matching local user and initiate the session
     *	@param string $userName
     */
    protected function processUser($userName)
    {
        if (empty($userName)) {
            return false;
        }

        $authDbConfig = $this->coralConfig->database;

        $linkID = mysqli_connect($authDbConfig->host, $authDbConfig->username, $authDbConfig->password);
        if (!$linkID) {
            return false;
        }
        if (!mysqli_select_db($linkID, $authDbConfig->name)) {
            return false;
        }

        $query = "SELECT * FROM {$authDbConfig->name}.User where loginID = '" . $userName . "';";
        $rs_users = mysqli_query($linkID, $query);

        if (!$rs_users || mysqli_num_rows($rs_users) == 0) {
            return false;
        }

        /* TAMU Customization note:
        * Session creation logic borrowed from: https://github.com/coral-erm/coral/blob/master/auth/admin/classes/domain/User.php#L139
        */

        //create new session
        $sessionID = $this->coralUtil->randomString(100);

        $session = $this->coralSession;
        $session->sessionID = $sessionID;
        $session->loginID = $userName;
        $session->timestamp = date('Y-m-d H:i:s');

        $session->save();

        //also set the cookie
        $this->coralUtil->setSessionCookie($sessionID, time() + $this->coralConfig->settings->timeout);
        $this->coralUtil->setLoginCookie($userName, time() + $this->coralConfig->settings->timeout);

        //also set session variable
        $_SESSION['loginID'] = $userName;

        return true;
    }

    protected function loadSettings()
    {
        $scriptDir = dirname(__DIR__);

        $config = $this->coralConfig;

        $configFilePath = $scriptDir . "/../../" . $config->tamu->customLibPath . SELF::CONFIG_FILE;
        $defaultSettingsFilePath = $scriptDir . "/../vendor/onelogin/php-saml/settings_example.php";

        if (!is_file($configFilePath)) {
            throw new \RuntimeException("SAML config file not found");
        }

        if (!is_file($defaultSettingsFilePath)) {
            throw new \RuntimeException("SAML Library config missing. Have you run composer in: {$scriptDir}/");
        }

        $samlLocalConfig = parse_ini_file($configFilePath, true);

        require($defaultSettingsFilePath);

        $this->settings = array_replace($settings, $samlLocalConfig);

        if (!$this->checkSettings()) {
            throw new \RuntimeException("Invalid SAML settings. Please check " . $configFilePath);
        }
    }

    protected function checkSettings()
    {
        return (is_array($this->settings)
            && !empty($this->settings['sp']['entityId'])
            && !empty($this->settings['sp']['assertionConsumerService']['url'])
            && !empty($this->settings['sp']['singleLogoutService']['url'])
            && !empty($this->settings['idp']['entityId'])
            && !empty($this->settings['idp']['singleSignOnService']['url'])
            && !empty($this->settings['idp']['singleLogoutService']['url'])
            && !empty($this->settings['idp']['singleLogoutService']['responseUrl'])
            && !empty($this->settings['idp']['x509cert']));
    }
}
