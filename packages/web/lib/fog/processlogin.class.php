<?php
/**
 * Processes the current login.
 *
 * PHP version 5
 *
 * @category ProcessLogin
 * @package  FOGProject
 * @author   Tom Elliott <tommygunsster@gmail.com>
 * @license  http://opensource.org/licenses/gpl-3.0 GPLv3
 * @link     https://fogproject.org
 */
/**
 * Processes the current login.
 *
 * @category ProcessLogin
 * @package  FOGProject
 * @author   Tom Elliott <tommygunsster@gmail.com>
 * @license  http://opensource.org/licenses/gpl-3.0 GPLv3
 * @link     https://fogproject.org
 */
class ProcessLogin extends FOGPage
{
    private $username;
    private $password;
    private $rangSet;
    private $mobileMenu;
    private $langMenu;
    private $lang;
    private function getLanguages()
    {
        $translang = $this->transLang();
        ob_start();
        foreach ((array)self::$foglang['Language'] as &$lang) {
            printf(
                '<option value="%s"%s>%s</option>',
                $lang,
                ($translang == $lang ? ' selected' : ''),
                $lang
            );
            unset($lang);
        }
        $this->langMenu = ob_get_clean();
    }
    private function defaultLang()
    {
        return self::getSetting('FOG_DEFAULT_LOCALE');
    }
    private function transLang()
    {
        switch ($_SESSION['locale']) {
            case 'de_DE':
                return self::$foglang['Language']['de'];
            case 'en_US':
                return self::$foglang['Language']['en'];
            case 'es_ES':
                return self::$foglang['Language']['es'];
            case 'fr_FR':
                return self::$foglang['Language']['fr'];
            case 'it_IT':
                return self::$foglang['Language']['it'];
            case 'pt_BR':
                return self::$foglang['Language']['pt'];
            case 'zh_CN':
                return self::$foglang['Language']['zh'];
            default:
                return self::$foglang['Language'][$this->defaultLang()];
        }
    }
    private function specLang()
    {
        if (isset($_REQUEST['ulang'])) {
            $_SESSION['locale'] = $_REQUEST['ulang'];
        } else {
            $_SESSION['locale'] = $this->transLang();
        }
        switch ($_SESSION['locale']) {
            case self::$foglang['Language']['de']:
                $_SESSION['locale'] = 'de_DE';
                break;
            case self::$foglang['Language']['en']:
                $_SESSION['locale'] = 'en_US';
                break;
            case self::$foglang['Language']['es']:
                $_SESSION['locale'] = 'es_ES';
                break;
            case self::$foglang['Language']['fr']:
                $_SESSION['locale'] = 'fr_FR';
                break;
            case self::$foglang['Language']['it']:
                $_SESSION['locale'] = 'it_IT';
                break;
            case self::$foglang['Language']['pt']:
                $_SESSION['locale'] = 'pt_BR';
                break;
            case self::$foglang['Language']['zh']:
                $_SESSION['locale'] = 'zh_CN';
                break;
            default:
                $_SESSION['locale'] = $this->transLang();
        }
    }
    public function setLang()
    {
        $langs = array(
            'de_DE' => true,
            'en_US' => true,
            'es_ES' => true,
            'fr_FR' => true,
            'it_IT' => true,
            'pt_BR' => true,
            'zh_CN' => true,
        );
        $this->specLang();
        setlocale(
            LC_MESSAGES,
            sprintf(
                '%s.UTF-8',
                $_SESSION['locale']
            )
        );
        $domain = 'messages';
        bindtextdomain(
            $domain,
            './languages'
        );
        bind_textdomain_codeset(
            $domain,
            'UTF-8'
        );
        textdomain($domain);
    }
    private function setRedirMode()
    {
        foreach ($_GET as $key => &$value) {
            $redirect[$key] = $value;
            unset($value);
        }
        unset($redirect['upass'], $redirect['uname'], $redirect['ulang']);
        if (in_array($redirect['node'], array('login', 'logout'))) {
            unset($redirect['node']);
        }
        foreach ((array)$redirect as $key => &$value) {
            if (!$value) {
                continue;
            }
            $http_query[$key] = $value;
            unset($value);
        }
        if (count($http_query) < 1) {
            unset($_REQUEST['login']);
            $this->redirect('index.php');
        }
        $query = trim(http_build_query($http_query));
        $redir = 'index.php';
        if ($query) {
            $redir .= "?$query";
        }
        $this->redirect($redir);
    }
    public function processMainLogin()
    {
        global $currentUser;
        $this->setLang();
        $this->username = trim($_REQUEST['uname']);
        $this->password = trim($_REQUEST['upass']);
        if (!isset($_REQUEST['login'])) {
            return;
        }
        if (!$this->username) {
            $this->setMessage(self::$foglang['InvalidLogin']);
            $this->redirect('index.php?node=logout');
        }
        self::$HookManager
            ->processEvent(
                'USER_LOGGING_IN',
                array(
                    'username' => $this->username,
                    'password' => $this->password
                )
            );
        if (!self::$FOGUser->isValid()) {
            self::$FOGUser = self::$FOGCore->attemptLogin(
                $this->username,
                $this->password
            );
        }
        if (!self::$FOGUser->isValid()) {
            $this->setRedirMode();
        }
        if (!self::$isMobile) {
            $type = self::$FOGUser->get('type');
            self::$HookManager
                ->processEvent(
                    'USER_TYPE_HOOK',
                    array('type' => &$type)
                );
            if ($type) {
                $this->setMessage(self::$foglang['NotAllowedHere']);
                unset($_REQUEST['login']);
                $this->redirect('index.php?node=logout');
            }
        }
        self::$HookManager
            ->processEvent(
                'LoginSuccess',
                array(
                    'username' => $this->username,
                    'password' => $this->password
                )
            );
        $this->setRedirMode();
    }
    public function mainLoginForm()
    {
        $this->setLang();
        if (in_array($_REQUEST['node'], array('login', 'logout'))) {
            $this->setMessage($_SESSION['FOG_MESSAGES']);
            unset($_REQUEST['login']);
            $this->redirect('index.php');
        }
        if ($_REQUEST['node'] != 'logout') {
            foreach ($_REQUEST as $key => &$value) {
                printf('<input type="hidden" name="%s" value="%s"/>', $key, $value);
                unset($value);
            }
        }
        $this->getLanguages();
        printf('<form method="post" action="%s" id="login-form"><label for="username">%s</label><input type="text" class="input" name="uname" id="username"/><label for="password">%s</label><input type="password" class="input" name="upass" id="password"/><label for="language">%s</label><select name="ulang" id="language">%s</select><label for="login-form-submit"> </label><input type="submit" value="%s" id="login-form-submit" name="login"/></form><div id="login-form-info"><p>%s: <b><i class="icon fa fa-circle-o-notch fa-spin fa-fw"></i></b></p><p>%s: <b><i class="icon fa fa-circle-o-notch fa-spin fa-fw"></i></b></p><p>%s: <b><i class="icon fa fa-circle-o-notch fa-spin fa-fw"></i></b></p><p>%s: <b><i class="icon fa fa-circle-o-notch fa-spin fa-fw"></i></b></p></div>', $this->formAction, self::$foglang['Username'], self::$foglang['Password'], self::$foglang['LanguagePhrase'], $this->langMenu, self::$foglang['Login'], self::$foglang['FOGSites'], self::$foglang['LatestVer'], self::$foglang['LatestDevVer'], self::$foglang['LatestSvnVer']);
    }
    public function mobileLoginForm()
    {
        $this->setLang();
        if (in_array($_REQUEST['node'], array('login', 'logout'))) {
            unset($_REQUEST['login']);
            $this->redirect('index.php');
        }
        $this->getLanguages();
        printf('<div class="c"><p>%s</p><form method="post" action=""><br/><br/><label for="username">%s: </label><input type="text" name="uname" id="username"/><br/><br/><label for="password">%s: </label><input type="password" name="upass" id="password"/><br/><br/><label for="language">%s: </label><select name="ulang" id="language">%s</select><br/><br/><label for="login-form-submit"> </label><input type="submit" value="%s" id="login-form-submit" name="login"/></form></div>', self::$foglang['FOGMobile'], self::$foglang['Username'], self::$foglang['Password'], self::$foglang['LanguagePhrase'], $this->langMenu, self::$foglang['Login']);
    }
}
