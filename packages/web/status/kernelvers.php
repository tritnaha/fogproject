<?php
/**
 * Presents the FOG Kernels version that the clients will use.
 *
 * PHP version 5
 *
 * @category KernelVersion
 * @package  FOGProject
 * @author   Tom Elliott <tommygunsster@gmail.com>
 * @license  http://opensource.org/licenses/gpl-3.0 GPLv3
 * @link     https://fogproject.org
 */
/**
 * Presents the FOG Kernels version that the clients will use.
 *
 * @category KernelVersion
 * @package  FOGProject
 * @author   Tom Elliott <tommygunsster@gmail.com>
 * @license  http://opensource.org/licenses/gpl-3.0 GPLv3
 * @link     https://fogproject.org
 */
require '../commons/base.inc.php';
session_write_close();
ignore_user_abort(true);
set_time_limit(0);
header('Content-Type: text/event-stream');
if (isset($_REQUEST['url'])) {
    $url = $_REQUEST['url'];
    $test = $FOGURLRequests->isAvailable($url);
    $test = array_shift($test);
    if (false === $test) {
        echo _('Node is not available!');
        exit;
    }
    $res = $FOGURLRequests
        ->process($url);
    foreach ((array)$res as &$response) {
        echo $response;
        unset($response);
    }
    exit;
} else {
    $kernelvers = function ($kernel) {
        $currpath = sprintf(
            '%s/service/ipxe/%s',
            BASEPATH,
            $kernel
        );
        $reppath = preg_replace(
            '#\\|/#',
            DIRECTORY_SEPARATOR,
            $currpath
        );
        $basepath = escapeshellarg($reppath);
        $findstr = sprintf(
            'strings %s | grep -A1 "%s:" | tail -1 | awk \'{print $1}\'',
            $basepath,
            'Undefined video mode number'
        );
        return shell_exec($findstr);
    };
    printf(
        "%s\n",
        FOG_VERSION
    );
    printf(
        "bzImage Version: %s\n",
        $kernelvers('bzImage')
    );
    printf(
        "bzImage32 Version: %s",
        $kernelvers('bzImage32')
    );
}
