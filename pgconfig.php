<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * View and edit of pgpool.conf
 *
 * PHP versions 4 and 5
 *
 * LICENSE: Permission to use, copy, modify, and distribute this software and
 * its documentation for any purpose and without fee is hereby
 * granted, provided that the above copyright notice appear in all
 * copies and that both that copyright notice and this permission
 * notice appear in supporting documentation, and that the name of the
 * author not be used in advertising or publicity pertaining to
 * distribution of the software without specific, written prior
 * permission. The author makes no representations about the
 * suitability of this software for any purpose.  It is provided "as
 * is" without express or implied warranty.
 *
 * @author     Ryuma Ando <ando@ecomas.co.jp>
 * @copyright  2003-2013 PgPool Global Development Group
 * @version    CVS: $Id$
 */

require_once('common.php');
require('definePgpoolConfParam.php');
$tpl->assign('help', basename( __FILE__, '.php'));

if (!isset($_SESSION[SESSION_LOGIN_USER])) {
    header('Location: login.php');
    exit();
}

$configParam = array();
$error = array();

if (isset($_POST['action'])) {
    $action = $_POST['action'];
} else {
    $action = FALSE;
}

/* --------------------------------------------------------------------- */
/* Add or Cancel                                                         */
/* --------------------------------------------------------------------- */

switch ($action) {
    case 'add':
    case 'add_wd':
    case 'add_heartbeat_destination':
        $configValue = arrangePostData();
        $configValue = doAdd($configValue);

        $tpl->assign('params', $configValue);
        $tpl->assign('isAdd',   ($action == 'add'));
        $tpl->assign('isAddWd', ($action == 'add_wd'));
        $tpl->assign('isAddHeartbeatDestination', ($action == 'add_heartbeat_destination'));
        $tpl->display('pgconfig.tpl');

        return;

    case 'cancel':
    case 'cancel_wd':
    case 'cancel_heartbeat_destination':
        $configValue = arrangePostData();
        $configValue = doCancel($configValue, $action);

        $tpl->assign('params', $configValue);
        $tpl->assign('isAdd',  FALSE);
        $tpl->assign('isAddWd', FALSE);
        $tpl->assign('isAddHeartbeatDestination', FALSE);
        $tpl->display('pgconfig.tpl');

        return;
}

/* --------------------------------------------------------------------- */
/* Update or Delete                                                      */
/* --------------------------------------------------------------------- */

/**
 * check $configFile
 */
$configValue = readConfigParams();
foreach ($pgpoolConfigParam as $key => $value) {
    if (!isset($configValue[$key]) ) {
        $configValue[$key] = $value['default'];
    }
}

switch ($action) {
    case 'update':
        $configValue = arrangePostData();
        $error = doCheck();

        if (! $error) {
            if (is_writable(_PGPOOL2_CONFIG_FILE)) {
                writeConfigFile($configValue, $pgpoolConfigParam);
                $configValue = readConfigParams();
                $tpl->assign('status', 'success');

            } else {
                $errorCode = 'e4003';
                $tpl->assign('errorCode', $errorCode);
                $tpl->display('error.tpl');
                exit();
            }

        } else {
            $tpl->assign('status', 'fail');
        }

        break;

    case 'delete':
    case 'delete_wd':
    case 'delete_heartbeat_destination':
        $num = $_POST['num'];

        switch ($action) {
            case 'delete':
                deleteBackendHost($num, $configValue); break;
            case 'delete_wd':
                deleteWdOther($num, $configValue); break;
            case 'delete_heartbeat_destination':
                deleteHeartbeatDestination($num, $configValue); break;
        }

        if (is_writable(_PGPOOL2_CONFIG_FILE)) {
            writeConfigFile($configValue, $pgpoolConfigParam);
            $configValue = readConfigParams();

        } else {
            $errorCode = 'e4003';
            $tpl->assign('errorCode', $errorCode);
            $tpl->display('error.tpl');
            exit();
        }

        break;

    case 'reset':
    default:
}

/* Set each empty object if null */
if (!isset($configValue['backend_hostname'])) {
    $configValue['backend_hostname'][0] = NULL;
    $configValue['backend_port'][0]     = NULL;
    $configValue['backend_weight'][0]   = NULL;
    $configValue['backend_data_directory'][0] = NULL;
    $configValue['backend_flag'][0]     = NULL;
}

if (!isset($configValue['heartbeat_destination'])) {
    $configValue['heartbeat_destination'][0] = NULL;
    $configValue['heartbeat_destination_port'][0] = NULL;
    $configValue['heartbeat_device'][0]      = NULL;
}

if (!isset($configValue['other_pgpool_hostname'])) {
    $configValue['other_pgpool_hostname'][0] = NULL;
    $configValue['other_pgpool_port'][0]     = NULL;
    $configValue['other_wd_port'][0]         = NULL;
}

$tpl->assign('params', $configValue);
$tpl->assign('error', $error);

$tpl->display('pgconfig.tpl');

/* --------------------------------------------------------------------- */
/* Functions                                                             */
/* --------------------------------------------------------------------- */

/**
 * check POST value
 *
 * @param string $key
 * @param string $value
 * @param array $configParam
 * @param string $error
 */
function check($key, $value, &$configParam ,&$error)
{
    //if (!isset($configParam[$key])) { continue; }
    if (!isset($configParam[$key])) { return; }

    $type = $value['type'];
    $result = FALSE;
    switch ($type) {
        case 'B':
            $result = checkBoolean($configParam[$key]);

            // allow true/false and on/off as input format,
            // but write with only on/off format.
            if ($result) {
                if ($configParam[$key] == 'true') {
                    $configParam[$key] = 'on';
                } elseif ($configParam[$key] == 'false') {
                    $configParam[$key] = 'off';
                }
            }

            break;

        case 'C':
            $result = checkString($configParam[$key], $value);
            break;

        case 'F':
            $result = checkFloat($configParam[$key], $value['min'], $value['max']);
            break;

        case 'N':
            $result = checkInteger($configParam[$key], $value['min'], $value['max']);
            break;
    }
    if (!$result) {
        $error[$key] = TRUE;
    }
}

/**
 * check string value
 *
 * @param string $str
 * @param string $pattern
 * @return bool
 */
function checkString($str, $pattern)
{
    // NULL is OK?
    if (empty($str) && isset($pattern['null_ok']) && $pattern['null_ok'] == TRUE) {
        return TRUE;

    // regex test
    } elseif (preg_match("/{$pattern['regexp']}/", $str)) {
        return TRUE;

    } else {
        return FALSE;
    }
}

/**
 * check integer value
 *
 * @param int $str
 * @param int $min
 * @param int $max
 * @return bool
 */
function checkInteger($str, $min, $max)
{
    if (is_numeric($str)) {
        $minLen = strlen($min);
        $maxLen = strlen($max);

        if ($str < $min || $str > $max) {
            return FALSE;
        } else {
            return TRUE;
        }
    }
    return TRUE;
}

/**
 * check float value
 *
 * @param float $str
 * @param float $min
 * @param float $max
 * @return bool
 */
function checkFloat($str, $min, $max)
{
    if (is_numeric($str)) {
        if ($str < $min || $str > $max) {
            return FALSE;
        }
        return TRUE;
    }
    return TRUE;
}

/**
 * Check Boolean value
 *
 * @param bool $str
 * @return bool
 */
function checkBoolean($str)
{
    if (in_array($str, array('true', 'false', 'on', 'off'))) {
        return TRUE;
    } else {
        return FALSE;
    }
}

/**
 * Check if there is no paradoxes in settings
 */
function checkLogical($configValue)
{
    $errors = array();

    // pgpool's mode
    if ($configValue['replication_mode'] == 'on' && $configValue['master_slave_mode'] == 'on') {
        $errors['replication_mode'] = TRUE;
        $errors['master_slave_mode'] = TRUE;
    }

    // syslog
    if ($configValue['log_destination']) {
        if (empty($configValue['syslog_facility'])) { $errors['syslog_facility'] = TRUE; }
        if (empty($configValue['syslog_ident'])) { $errors['syslog_ident'] = TRUE; }
    }

    // health check
    if ($configValue['health_check_period'] > 0) {
        if (empty($configValue['health_check_user'])) { $errors['health_check_user'] = TRUE; }
    }

    // streaming replication
    if ($configValue['master_slave_mode'] == 'on' &&
        $configValue['master_slave_sub_mode'] == 'stream')
    {
        if (empty($configValue['sr_check_user'])) { $errors['sr_check_user'] = TRUE; }
    }

    // watchdog
    if ($configValue['use_watchdog'] == 'on') {
        if (empty($configValue['delegate_IP'])) { $errors['delegate_IP'] = TRUE; }
        if (empty($configValue['wd_hostname'])) { $errors['wd_hostname'] = TRUE; }
    }

    // memqcache
    if ($configValue['memory_cache_enabled'] == 'on') {
        if (empty($configValue['wd_lifecheck_query'])) {
            $errors['wd_lifecheck_query'] = TRUE;
        }

        switch ($configValue['memqcache_method']) {
            case 'shmem':
                if (empty($configValue['memqcache_total_size'])) {
                    $errors['memqcache_total_size'] = TRUE;
                }
                if (empty($configValue['memqcache_max_num_cache'])) {
                    $errors['memqcache_max_num_cache'] = TRUE;
                }
                if (empty($configValue['memqcache_cache_block_size'])) {
                    $errors['memqcache_cache_block_size'] = TRUE;
                }
                break;

            case 'memcached':
                if (empty($configValue['memqcache_memcached_host'])) {
                    $errors['memqcache_memcached_host'] = TRUE;
                }
                if (empty($configValue['memqcache_memcached_port'])) {
                    $errors['memqcache_memcached_port'] = TRUE;
                }
                break;
        }
    }

    return $errors;
}

/* --------------------------------------------------------------------- */

/**
 * Write pgpool.conf
 *
 * @param array $configValue
 * @param array $pgpoolConfigParam
 */
function writeConfigFile($configValue, $pgpoolConfigParam)
{
    $configFile = @file(_PGPOOL2_CONFIG_FILE);

    $tmpConfigFile = array();
    for ($i = 0; $i < count($configFile); $i++) {
        $line = $configFile[$i];

        if (preg_match("/^\w/", $line)) {
            list($key, $value) = explode("=", $line);
            $key = trim($key);

            if (!preg_match("/^backend_hostname/",       $key) &&
                !preg_match("/^backend_port/",           $key) &&
                !preg_match("/^backend_weight/",         $key) &&
                !preg_match("/^backend_data_directory/", $key) &&
                !preg_match("/^backend_flag/",           $key) &&
                !preg_match("/^other_pgpool_hostname/",  $key) &&
                !preg_match("/^other_pgpool_port/",      $key) &&
                !preg_match("/^other_wd_port/",          $key) &&
                !preg_match("/^heartbeat_device/",       $key) &&
                !preg_match("/^heartbeat_destination_port/", $key) &&
                !preg_match("/^heartbeat_destination/",  $key)
                )
            {
                $tmpConfigFile[] =  $line;
            }

        } else {
            $tmpConfigFile[] =  $line;
        }
    }
    $configFile = $tmpConfigFile;

    foreach ($pgpoolConfigParam as $key => $value) {
        $isWrite = FALSE;
        for ($j = 0; $j < count($configFile); $j++) {
            $line = $configFile[$j];
            $line = trim($line);

            if (preg_match("/^$key/", $line)) {
                if (strcmp($pgpoolConfigParam[$key]['type'], "C") == 0) {
                    $configFile[$j] = $key . " = '" . $configValue[$key] . "'\n";
                } else {
                    $configFile[$j] = $key . " = " . $configValue[$key]."\n";
                }
                $isWrite = TRUE;
                break;
            }
        }

        if (!$isWrite && paramExists($key)) {
            if (strcmp($pgpoolConfigParam[$key]['type'], "C") == 0) {
                $configFile[] = $key . " = '" . $configValue[$key] . "'\n";
            } else {
                $configFile[] = $key . " = " . $configValue[$key]."\n";
            }
        }
    }

    if (isset($configValue['backend_hostname'])) {
        for ($i = 0; $i < count($configValue['backend_hostname']); $i++) {

            $configFile[] = "backend_hostname$i = '" . $configValue['backend_hostname'][$i] . "'\n";
            $configFile[] = "backend_port$i = " . $configValue['backend_port'][$i] . "\n";
            $configFile[] = "backend_weight$i = " . $configValue['backend_weight'][$i] . "\n";
            $configFile[] = "backend_data_directory$i = '" . $configValue['backend_data_directory'][$i] . "'\n";

            if (paramExists('backend_flag')) {
                $configFile[] = "backend_flag$i= '" . $configValue['backend_flag'][$i] . "'\n";
            }
        }
    }

    if (isset($configValue['other_pgpool_hostname'])) {
        for ($i = 0; $i < count($configValue['other_pgpool_hostname']); $i++) {
            $configFile[] = "other_pgpool_hostname$i = '" . $configValue['other_pgpool_hostname'][$i] . "'\n";
            $configFile[] = "other_pgpool_port$i = " . $configValue['other_pgpool_port'][$i] . "\n";
            $configFile[] = "other_wd_port$i = " . $configValue['other_wd_port'][$i] . "\n";
        }
    }

    if (isset($configValue['heartbeat_destination'])) {
        for ($i = 0; $i < count($configValue['heartbeat_destination']); $i++) {
            $configFile[] = "heartbeat_destination$i = '" . $configValue['heartbeat_destination'][$i] . "'\n";
            $configFile[] = "heartbeat_destination_port$i = " . $configValue['heartbeat_destination_port'][$i] . "\n";
            $configFile[] = "heartbeat_device$i = '" . $configValue['heartbeat_device'][$i] . "'\n";
        }
    }

    $outfp = fopen(_PGPOOL2_CONFIG_FILE, 'w');
    foreach ($configFile as $line) {
        fputs($outfp, $line);
    }
    fclose($outfp);
}

/**
 * Delete backend host
 *
 * @param int $num
 * @param array $configValue
 */
function deleteBackendHost($num, &$configValue)
{
    if (!isset($configValue['backend_hostname'])) { return; }

    unset($configValue['backend_hostname'][$num]);
    $configValue['backend_hostname'] = array_values($configValue['backend_hostname']);

    unset($configValue['backend_port'][$num]);
    $configValue['backend_port'] = array_values($configValue['backend_port']);

    unset($configValue['backend_weight'][$num]);
    $configValue['backend_weight'] = array_values($configValue['backend_weight']);

    unset($configValue['backend_data_directory'][$num]);
    $configValue['backend_data_directory'] = array_values($configValue['backend_data_directory']);

    if (paramExists('backend_flag')) {
        unset($configValue['backend_flag'][$num]);
        $configValue['backend_flag'] = array_values($configValue['backend_flag']);
    }
}

/**
 * Delete an other watchdog
 */
function deleteWdOther($num, &$configValue)
{
    if (!isset($configValue['other_pgpool_hostname'])) { return; }

    unset($configValue['other_pgpool_hostname'][$num]);
    $configValue['other_pgpool_hostname'] = array_values($configValue['other_pgpool_hostname']);

    unset($configValue['other_pgpool_port'][$num]);
    $configValue['other_pgpool_port'] = array_values($configValue['other_pgpool_port']);

    unset($configValue['other_wd_port'][$num]);
    $configValue['other_wd_port'] = array_values($configValue['other_wd_port']);
}

/**
 * Delete an heartbeat device
 */
function deleteHeartbeatDestination($num, &$configValue)
{
    if (!isset($configValue['heartbeat_destination'])) { return; }

    unset($configValue['heartbeat_destination'][$num]);
    $configValue['heartbeat_destination'] = array_values($configValue['heartbeat_destination']);

    unset($configValue['heartbeat_destination_port'][$num]);
    $configValue['heartbeat_destination_port'] = array_values($configValue['heartbeat_destination_port']);

    unset($configValue['heartbeat_device'][$num]);
    $configValue['heartbeat_device'] = array_values($configValue['heartbeat_device']);
}

/**
 * Arrange post data
 */
function arrangePostData()
{
    global $pgpoolConfigParam;
    global $_POST;

    $configValue = array();
    foreach ($pgpoolConfigParam as $key => $value) {
        if ($pgpoolConfigParam[$key]['type'] == 'B') {
            $configValue[$key] = (isset($_POST[$key])) ? 'on' : 'off';

        } elseif (isset($_POST[$key])) {
            $configValue[$key] = trim($_POST[$key]);
        }
    }

    return $configValue;
}

/**
 * Add action
 */
function doAdd($configValue)
{
    global $_POST;

    // Backend settings
    if (isset($_POST['backend_hostname'])) {
        $configValue['backend_hostname'] = $_POST['backend_hostname'];
    } else {
        $configValue['backend_hostname'][0] = NULL;

    }
    if (isset($_POST['backend_port'])) {
        $configValue['backend_port'] = $_POST['backend_port'];
    } else {
        $configValue['backend_port'][0] = NULL;
    }

    if (isset($_POST['backend_weight'])) {
        $configValue['backend_weight'] = $_POST['backend_weight'];
    } else {
        $configValue['backend_weight'][0] = NULL;
    }

    if (isset($_POST['backend_data_directory'])) {
        $configValue['backend_data_directory'] = $_POST['backend_data_directory'];
    } else {
         $configValue['backend_data_directory'][0] = NULL;
    }

    if (paramExists('backend_flag')) {
        if (isset($_POST['backend_flag'])) {
            $configValue['backend_flag'] = $_POST['backend_flag'];
        } else {
            $configValue['backend_flag'][0] = NULL;
        }
    }

    // watchdog's heartbeat destination settings
    if (isset($_POST['heartbeat_device'])) {
        $configValue['heartbeat_device'] = $_POST['heartbeat_device'];
    } else {
        $configValue['heartbeat_device'][0] = NULL;
    }

    if (isset($_POST['heartbeat_destination_port'])) {
        $configValue['heartbeat_destination_port'] = $_POST['heartbeat_destination_port'];
    } else {
        $configValue['heartbeat_destination_port'][0] = NULL;
    }

    if (isset($_POST['heartbeat_destination'])) {
        $configValue['heartbeat_destination'] = $_POST['heartbeat_destination'];
    } else {
        $configValue['heartbeat_destination'][0] = NULL;
    }

    // watchdog's other pgpool settings
    if (isset($_POST['other_pgpool_hostname'])) {
        $configValue['other_pgpool_hostname'] = $_POST['other_pgpool_hostname'];
    } else {
        $configValue['other_pgpool_hostname'][0] = NULL;
    }

    if (isset($_POST['other_pgpool_port'])) {
        $configValue['other_pgpool_port'] = $_POST['other_pgpool_port'];
    } else {
        $configValue['other_pgpool_port'][0] = NULL;
    }

    if (isset($_POST['other_wd_port'])) {
        $configValue['other_wd_port'] = $_POST['other_wd_port'];
    } else {
        $configValue['other_wd_port'][0] = NULL;
    }

    return $configValue;
}

/**
 * Cancel action
 */
function doCancel($configValue, $action)
{
    global $_POST;

    // Backend settings
    if (isset($_POST['backend_hostname'])) {
        $configValue['backend_hostname'] = $_POST['backend_hostname'];
    }
    if (isset($_POST['backend_port'])) {
        $configValue['backend_port'] = $_POST['backend_port'];
    }
    if (isset($_POST['backend_weight'])) {
        $configValue['backend_weight'] = $_POST['backend_weight'];
    }
    if (isset($_POST['backend_data_directory'])) {
        $configValue['backend_data_directory'] = $_POST['backend_data_directory'];
    }
    if (paramExists('backend_flag') && isset($_POST['backend_flag'])) {
        $configValue['backend_flag'] = $_POST['backend_flag'];
    }

    if ($action == 'cancel') {
        array_pop($configValue['backend_hostname']);
        array_pop($configValue['backend_port']);
        array_pop($configValue['backend_weight']);
        array_pop($configValue['backend_data_directory']);
        array_pop($configValue['backend_flag']);
    }

    // watchdog's device settings
    if (isset($_POST['heartbeat_device'])) {
        $configValue['heartbeat_device'] = $_POST['heartbeat_device'];
    }
    if (isset($_POST['heartbeat_destination'])) {
        $configValue['heartbeat_destination'] = $_POST['heartbeat_destination'];
    }
    if (isset($_POST['heartbeat_destination_port'])) {
        $configValue['heartbeat_destination_port'] = $_POST['heartbeat_destination_port'];
    }
    if ($action == 'cancel_heartbeat_destination') {
        array_pop($configValue['heartbeat_destination']);
        array_pop($configValue['heartbeat_destination_port']);
        array_pop($configValue['heartbeat_device']);
    }

    // watchdog's other pgpool settings
    if (isset($_POST['other_pgpool_hostname'])) {
        $configValue['other_pgpool_hostname'] = $_POST['other_pgpool_hostname'];
    }
    if (isset($_POST['other_pgpool_port'])) {
        $configValue['other_pgpool_port'] = $_POST['other_pgpool_port'];
    }
    if (isset($_POST['other_wd_port'])) {
        $configValue['other_wd_port'] = $_POST['other_wd_port'];
    }
    if ($action == 'cancel_wd') {
        array_pop($configValue['other_pgpool_hostname']);
        array_pop($configValue['other_pgpool_port']);
        array_pop($configValue['other_wd_port']);
    }

    return $configValue;
}

/**
 * Check all params
 */
function doCheck()
{
    global $pgpoolConfigParam;
    global $configValue;
    global $pgpoolConfigBackendParam;
    global $pgpoolConfigHbDestinationParam;
    global $pgpoolConfigWdOtherParam;
    global $_POST;

    $error = array();

    /*
     * Confirmations of value except backend host
     */
    foreach ($pgpoolConfigParam as $key => $value) {
        check($key, $value, $configValue, $error);
    }

    /*
     * copy backend value from POST data to $configValue
     */
    foreach ($pgpoolConfigBackendParam as $key => $value) {
        if (isset($_POST[$key])) {
            $configValue[$key] = $_POST[$key];
        }
    }

    /*
     * check backend value
     */
    if (isset($configValue['backend_hostname'])) {
        for ($i = 0; $i < count($configValue['backend_hostname']); $i++) {
            $result = FALSE;

            // backend_hostname
            $result = checkString($configValue['backend_hostname'][$i],
                                  $pgpoolConfigBackendParam['backend_hostname']['regexp']);
            if (!$result) {
                $error['backend_hostname'][$i] = TRUE;
            }

            // backend_port
            $result = checkInteger($configValue['backend_port'][$i],
                                   $pgpoolConfigBackendParam['backend_port']['min'],
                                   $pgpoolConfigBackendParam['backend_port']['max']);
            if (!$result) {
                $error['backend_port'][$i] = TRUE;
            }

            // backend_weight
            $result = checkFloat($configValue['backend_weight'][$i],
                                 $pgpoolConfigBackendParam['backend_weight']['min'],
                                 $pgpoolConfigBackendParam['backend_weight']['max']);
            if (!$result) {
                $error['backend_weight'][$i] = TRUE;
            }

            // backend_data_directory
            $result = checkString($configValue['backend_data_directory'][$i],
                                  $pgpoolConfigBackendParam['backend_data_directory']['regexp']);
            if (!$result) {
                $error['backend_data_directory'][$i] = TRUE;
            }

            // backend_flag
            if (paramExists('backend_flag')) {
                $result = checkString($configValue['backend_flag'][$i],
                                      $pgpoolConfigBackendParam['backend_flag']['regexp']);
                if (!$result) {
                    $error['backend_flag'][$i] = TRUE;
                }
            }
        }
    }

    /*
     * check watchdog heartbeat destination value
     */
    foreach ($pgpoolConfigHbDestinationParam as $key => $value) {
        if (isset($_POST[$key])) {
            $configValue[$key] = $_POST[$key];
        }
    }
    if (isset($configValue['heartbeat_destination'])) {
        for ($i = 0; $i < count($configValue['heartbeat_destination']); $i++) {
            $result = FALSE;

            // heartbeat_destination
            $result = checkString($configValue['heartbeat_destination'][$i],
                                  $pgpoolConfigHbDestinationParam['heartbeat_destination']['regexp']);
            if (!$result) {
                $error['heartbeat_destination'][$i] = TRUE;
            }

            // heartbeat_destination_port
            $result = checkInteger($configValue['heartbeat_destination_port'][$i],
                                   $pgpoolConfigHbDestinationParam['heartbeat_destination_port']['min'],
                                   $pgpoolConfigHbDestinationParam['heartbeat_destination_port']['max']);
            if (!$result) {
                $error['heartbeat_destination_port'][$i] = TRUE;
            }

            // heartbeat_device
            $result = checkString($configValue['heartbeat_device'][$i],
                                  $pgpoolConfigHbDestinationParam['heartbeat_device']['regexp']);
            if (!$result) {
                $error['heartbeat_device'][$i] = TRUE;
            }
        }
    }

    /*
     * check watchdog's other pgpool value
     */
    foreach ($pgpoolConfigWdOtherParam as $key => $value) {
        if (isset($_POST[$key])) {
            $configValue[$key] = $_POST[$key];
        }
    }
    if (isset($configValue['other_pgpool_hostname'])) {
        for ($i = 0; $i < count($configValue['other_pgpool_hostname']); $i++) {
            $result = FALSE;

            // other_pgpool_hostname
            $result = checkString($configValue['other_pgpool_hostname'][$i],
                                  $pgpoolConfigWdOtherParam['other_pgpool_hostname']['regexp']);
            if (!$result) {
                $error['other_pgpool_hostname'][$i] = TRUE;
            }

            // other_pgpool_port
            $result = checkInteger($configValue['other_pgpool_port'][$i],
                                   $pgpoolConfigWdOtherParam['other_pgpool_port']['min'],
                                   $pgpoolConfigWdOtherParam['other_pgpool_port']['max']);
            if (!$result) {
                $error['other_pgpool_port'][$i] = TRUE;
            }

            // other_wd_port
            $result = checkInteger($configValue['other_wd_port'][$i],
                                   $pgpoolConfigWdOtherParam['other_wd_port']['min'],
                                   $pgpoolConfigWdOtherParam['other_wd_port']['max']);
            if (!$result) {
                $error['other_wd_port'][$i] = TRUE;
            }
        }
    }

    /*
     * check logically
     */
    $logical_errors = checkLogical($configValue);
    if ($logical_errors) {
        $isError = TRUE;
        $error += $logical_errors;
    }

    /**
     * return params which has errors
     */
    return $error;
}
