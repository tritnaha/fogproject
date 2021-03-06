<?php
/**
 * LDAPPluginHook enables our checks as required
 *
 * PHP version 5
 *
 * @category LDAPPluginHook
 * @package  FOGProject
 * @author   Fernando Gietz <nah@nah.com>
 * @author   george1421 <nah@nah.com>
 * @author   Tom Elliott <tommygunsster@gmail.com>
 * @license  http://opensource.org/licenses/gpl-3.0 GPLv3
 * @link     https://fogproject.org
 */
/**
 * LDAPPluginHook enables our checks as required
 *
 * @category LDAPPluginHook
 * @package  FOGProject
 * @author   Fernando Gietz <nah@nah.com>
 * @author   george1421 <nah@nah.com>
 * @author   Tom Elliott <tommygunsster@gmail.com>
 * @license  http://opensource.org/licenses/gpl-3.0 GPLv3
 * @link     https://fogproject.org
 */
class LDAPPluginHook extends Hook
{
    /**
     * The name of this hook.
     *
     * @var string
     */
    public $name = 'LDAPPluginHook';
    /**
     * The description of this hook.
     *
     * @var string
     */
    public $description = 'LDAP Hook';
    /**
     * The active flag.
     *
     * @var bool
     */
    public $active = true;
    /**
     * The node to enact upon.
     *
     * @var string
     */
    public $node = 'ldap';
    /**
     * Checks and creates users if they're valid
     *
     * @param mixed $arguments the item to adjust
     *
     * @throws Exception
     * @return void
     */
    public function checkAddUser($arguments)
    {
        if (!in_array($this->node, (array)$_SESSION['PluginsInstalled'])) {
            return;
        }
        $user = trim($arguments['username']);
        $pass = trim($arguments['password']);
        self::$FOGUser = self::$FOGCore->attemptLogin(
            $user,
            $pass
        );
        $ldapTypes = array(990, 991);
        /**
         * Check the user and validate the type is not
         * our ldap inserted items. If not return as the
         * user is already allowed.
         */
        if (self::$FOGUser->isValid()) {
            $ldapType = self::$FOGUser->get('type');
            if (!in_array($ldapType, $ldapTypes)) {
                return;
            }
        }
        /**
         * Collects the user ids with our ldap entries.
         */
        $userIDs = self::getSubObjectIDs(
            'User',
            array('type' => $ldapTypes)
        );
        /**
         * Count the user ids and if any are there,
         * remove all the entries.
         */
        if (count($userIDs) > 0) {
            self::getClass('UserManager')
                ->destroy(array('id' => $userIDs));
        }
        $ldaps = self::getClass('LDAPManager')->find();
        /**
         * Create our new user (initially at least
         */
        self::$FOGUser = self::getClass('User')
            ->set('name', $user);
        foreach ((array)$ldaps as &$ldap) {
            if (!$ldap->isValid()) {
                continue;
            }
            $access = $ldap->authLDAP($user, $pass);
            unset($ldap);
            switch ($access) {
            case false:
                // Reset user object and Skip this
                self::$FOGUser = self::getClass('User');
                continue 2;
            case 2:
                // This is an admin account, break the loop
                self::$FOGUser
                    ->set('password', $pass)
                    ->set('type', 990)
                    ->save();
                break 2;
            case 1:
                // This is an unprivileged user account.
                self::$FOGUser
                    ->set('password', $pass)
                    ->set('type', 991)
                    ->save();
                break;
            }
        }
        unset($ldaps);
    }
    /**
     * Sets our ldap types
     *
     * @param mixed $arguments the item to adjust
     *
     * @return void
     */
    public function setLdapType($arguments)
    {
        if (!in_array($this->node, (array)$_SESSION['PluginsInstalled'])) {
            return;
        }
        $type = (int)$arguments['type'];
        if ($type === 990) {
            $arguments['type'] = 0;
        } elseif ($type === 991) {
            $arguments['type'] = 1;
        }
    }
    /**
     * Sets our user type to filter from user list
     *
     * @param mixed $arguments the item to adjust
     *
     * @return void
     */
    public function setTypeFilter($arguments)
    {
        if (!in_array($this->node, (array)$_SESSION['PluginsInstalled'])) {
            return;
        }
        $arguments['types'] = array(990, 991);
    }
}
$LDAPPluginHook = new LDAPPluginHook();
$HookManager
    ->register(
        'USER_LOGGING_IN',
        array(
            $LDAPPluginHook,
            'checkAddUser'
        )
    );
$HookManager
    ->register(
        'USER_TYPE_HOOK',
        array(
            $LDAPPluginHook,
            'setLdapType'
        )
    );
$HookManager
    ->register(
        'USER_TYPES_FILTER',
        array(
            $LDAPPluginHook,
            'setTypeFilter'
        )
    );
