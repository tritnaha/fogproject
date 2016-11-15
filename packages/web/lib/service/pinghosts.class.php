<?php
/**
 * Gets the current ping code of each host and
 * updates the hosts related to them.
 *
 * PHP version 5
 *
 * @category PingHosts
 * @package  FOGProject
 * @author   Tom Elliott <tommygunsster@gmail.com>
 * @license  http://opensource.org/licenses/gpl-3.0 GPLv3
 * @link     https://fogproject.org
 */
/**
 * Gets the current ping code of each host and
 * updates the hosts related to them.
 *
 * @category PingHosts
 * @package  FOGProject
 * @author   Tom Elliott <tommygunsster@gmail.com>
 * @license  http://opensource.org/licenses/gpl-3.0 GPLv3
 * @link     https://fogproject.org
 */
class PingHosts extends FOGService
{
    /**
     * Is the host lookup/ping enabled
     *
     * @var int
     */
    private static $_pingOn = 0;
    /**
     * The fog web host
     *
     * @var string
     */
    private static $_fogWeb = '';
    /**
     * Where to get the services sleeptime
     *
     * @var string
     */
    public static $sleeptime = 'PINGHOSTSLEEPTIME';
    /**
     * Initializes the PingHost Class
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        list(
            self::$_pingOn,
            self::$_fogWeb,
            $dev,
            $log,
            $zzz
        ) = self::getSubObjectIDs(
            'Service',
            array(
                'name' => array(
                    'FOG_HOST_LOOKUP',
                    'FOG_WEB_HOST',
                    'PINGHOSTDEVICEOUTPUT',
                    'PINGHOSTLOGFILENAME',
                    self::$sleeptime
                )
            ),
            'value',
            false,
            'AND',
            'name',
            false,
            ''
        );
        static::$log = sprintf(
            '%s%s',
            (
                self::$logpath ?
                self::$logpath :
                '/opt/fog/log/'
            ),
            (
                $log ?
                $log :
                'pinghost.log'
            )
        );
        if (file_exists(static::$log)) {
            unlink(static::$log);
        }
        static::$dev = (
            $dev ?
            $dev :
            '/dev/tty3'
        );
        static::$zzz = (
            $zzz ?
            $zzz :
            300
        );
    }
    /**
     * This is what almost all services have available
     * but is specific to this service
     *
     * @throws Exception
     * @return void
     */
    private function _commonOutput()
    {
        try {
            $this->waitDbReady();
            if (self::$_pingOn < 1) {
                throw new Exception(_(' * Host Ping is not enabled'));
            }
            $webServerIP = self::$FOGCore->resolveHostName(
                self::$_fogWeb
            );
            self::outall(
                sprintf(' * FOG Web Host IP: %s', $webServerIP)
            );
            self::getIPAddress();
            if (!in_array($webServerIP, self::$ips)) {
                throw new Exception(
                    _('I am not the fog web server')
                );
            }
            foreach ((array)self::$ips as $index => &$ip) {
                if ($index === 0) {
                    self::outall(
                        sprintf(
                            ' * %s',
                            _('This servers ip(s)')
                        )
                    );
                }
                self::outall(" |\t$ip");
                unset($ip, $index);
            }
            $hostCount = self::getClass('HostManager')->count();
            self::outall(
                sprintf(
                    ' * %s %s %s',
                    _('Attempting to ping'),
                    $hostCount,
                    (
                        $hostcount != 1 ?
                        _('hosts') :
                        _('host')
                    )
                )
            );
            $hostids = self::getsubObjectIDs('Host');
            $hostnames = self::getSubObjectIDs(
                'Host',
                array('id' => $hostids),
                'name'
            );
            $hostips = self::getSubObjectIDs(
                'Host',
                array(
                    'id' => $hostids,
                    'name' => $hostnames
                ),
                'ip',
                false,
                'AND',
                'name',
                false,
                ''
            );
            foreach ((array)$hostids as $index => &$hostid) {
                $ip = $hostips[$index];
                if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
                    $ip = self::$FOGCore->resolveHostname($hostnames[$index]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
                    $ip = $hostnames[$index];
                }
                unset($hostnames[$index], $hostips[$index]);
                $ping = self::getClass('Ping', $ip)
                    ->execute();
                self::getClass('HostManager')
                    ->update(
                        array('id' => $hostid),
                        '',
                        array('pingstatus' => $ping)
                    );
                unset($hostid, $index, $hostids[$index]);
            }
            self::outall(' * All hosts updated');
        } catch (Exception $e) {
            self::outall($e->getMessage());
        }
    }
    /**
     * This is what essentially "runs" the service
     *
     * @return void
     */
    public function serviceRun()
    {
        self::out(
            ' ',
            static::$dev
        );
        $str = str_pad('+', 75, '-');
        self::out($str, static::$dev);
        $this->_commonOutput();
        self::out($str, static::$dev);
        parent::serviceRun();
    }
}
