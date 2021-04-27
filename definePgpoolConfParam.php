<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Definition of pgpool variable
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
 * @copyright  2003-2020 PgPool Global Development Group
 * @version    CVS: $Id$
 */

$pgpoolConfigParam = array();
$pgpoolConfigBackendParam = array();
$pgpoolConfigWdOtherParam = array();
$pgpoolConfigHbDestinationParam = array();
$pgpoolConfigHealthCheckParam = array();

define('NUM_MAX', 65535);

// regex kinds
$strreg     = '^[0-9a-zA-Z_\-]+$';
$dirreg     = '^\/[0-9a-zA-Z\._\/\-]+$';
$hostreg    = '^[0-9a-zA-Z\._\-]*$';
$sslreg     = '^(|\/[0-9a-zA-Z_\/\.\-]*)$';
$commandreg = '^[0-9a-zA-Z_\/\.\-]*$';
$addressreg = '^([0-9a-zA-Z\._\-]+|[\*]{1})$';
$listreg    = '^[0-9a-zA-Z_,]*$';
$queryreg   = '^[0-9a-zA-Z; ]+$';
$userreg    = "^[0-9a-zA-Z_\.\-]+$";
$permissionreg = '^(0[0-7]{3}|[rwx\-]{9})$';
$anyelse    = '.*';

function selectreg($lists)
{
    return '^['. implode('|', $lists). ']+$';
}

#------------------------------------------------------------------------------
# BACKEND CLUSTERING MODE
#------------------------------------------------------------------------------

$key = 'backend_clustering_mode';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['select'] = array('streaming_replication',
                                           'logical_replication',
                                           'slony',
                                           'native_replication',
                                           'snapshot_isolation',
                                           'raw');
$pgpoolConfigParam[$key]['regexp'] = selectreg($pgpoolConfigParam[$key]['select']);

#------------------------------------------------------------------------------
# CONNECTIONS
#------------------------------------------------------------------------------

# - pgpool Connection Settings -

$key = 'listen_addresses';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = 'localhost';
$pgpoolConfigParam[$key]['regexp'] = $addressreg;
$pgpoolConfigParam[$key]['restart'] = TRUE;

$key = 'port';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = 9999;
$pgpoolConfigParam[$key]['max'] = NUM_MAX;
$pgpoolConfigParam[$key]['min'] = 1024;
$pgpoolConfigParam[$key]['restart'] = TRUE;

$key = 'socket_dir';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '/tmp';
$pgpoolConfigParam[$key]['regexp'] = $dirreg;
$pgpoolConfigParam[$key]['restart'] = TRUE;

$key = 'listen_backlog_multiplier';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = 2;
$pgpoolConfigParam[$key]['max'] = NUM_MAX;
$pgpoolConfigParam[$key]['min'] = 1;

$key = 'serialize_accept';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'off';

$key = 'reserved_connections';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = '0';
$pgpoolConfigParam[$key]['max'] = NUM_MAX;
$pgpoolConfigParam[$key]['min'] = 0;
$pgpoolConfigParam[$key]['restart'] = TRUE;

# - pgpool Communication Manager Connection Settings -

$key = 'pcp_listen_addresses';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = 'localhost';
$pgpoolConfigParam[$key]['regexp'] = $addressreg;
$pgpoolConfigParam[$key]['restart'] = TRUE;

$key = 'pcp_port';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = 9898;
$pgpoolConfigParam[$key]['min'] = 1024;
$pgpoolConfigParam[$key]['max'] = NUM_MAX;
$pgpoolConfigParam[$key]['restart'] = TRUE;

$key = 'pcp_socket_dir';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '/tmp';
$pgpoolConfigParam[$key]['regexp'] = $dirreg;
$pgpoolConfigParam[$key]['restart'] = TRUE;

# - Backend Connection Settings -

$key = 'backend_hostname';
$pgpoolConfigBackendParam[$key]['type'] = 'C';
$pgpoolConfigBackendParam[$key]['default'] = '';
$pgpoolConfigBackendParam[$key]['regexp'] = $hostreg;
$pgpoolConfigBackendParam[$key]['multiple'] = TRUE;

$key = 'backend_port';
$pgpoolConfigBackendParam[$key]['type'] = 'N';
$pgpoolConfigBackendParam[$key]['default'] = 5432;
$pgpoolConfigBackendParam[$key]['min'] = 1024;
$pgpoolConfigBackendParam[$key]['max'] = NUM_MAX;
$pgpoolConfigBackendParam[$key]['multiple'] = TRUE;

$key = 'backend_weight';
$pgpoolConfigBackendParam[$key]['type'] = 'F';
$pgpoolConfigBackendParam[$key]['default'] = 1;
$pgpoolConfigBackendParam[$key]['min'] = 0.0;
$pgpoolConfigBackendParam[$key]['max'] = 100.0;
$pgpoolConfigBackendParam[$key]['multiple'] = TRUE;

$key = 'backend_data_directory';
$pgpoolConfigBackendParam[$key]['type'] = 'C';
$pgpoolConfigBackendParam[$key]['default'] = '';
$pgpoolConfigBackendParam[$key]['regexp'] = $anyelse;
$pgpoolConfigBackendParam[$key]['multiple'] = TRUE;

$key = 'backend_flag';
$pgpoolConfigBackendParam[$key]['type'] = 'C';
$pgpoolConfigBackendParam[$key]['default'] = 'ALLOW_TO_FAILOVER';
if (_PGPOOL2_VERSION >= 4.2) {
    $pgpoolConfigBackendParam[$key]['select'] = array('ALLOW_TO_FAILOVER', 'DISALLOW_TO_FAILOVER', 'ALWAYS_PRIMARY');
} elseif (_PGPOOL2_VERSION >= 3.7) {
    $pgpoolConfigBackendParam[$key]['select'] = array('ALLOW_TO_FAILOVER', 'DISALLOW_TO_FAILOVER', 'ALWAYS_MASTER');
} else {
    $pgpoolConfigBackendParam[$key]['select'] = array('ALLOW_TO_FAILOVER', 'DISALLOW_TO_FAILOVER');
}
$pgpoolConfigBackendParam[$key]['regexp'] = selectreg($pgpoolConfigBackendParam[$key]['select']);
$pgpoolConfigBackendParam[$key]['multiple'] = TRUE;

$key = 'backend_application_name';
$pgpoolConfigBackendParam[$key]['type'] = 'C';
$pgpoolConfigBackendParam[$key]['default'] = '';
$pgpoolConfigBackendParam[$key]['regexp'] = $anyelse;
$pgpoolConfigBackendParam[$key]['multiple'] = TRUE;

# - Authentication -

$key = 'enable_pool_hba';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'off';

$key = 'pool_passwd';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = 'pool_passwd';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;

$key = 'authentication_timeout';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = 60;
$pgpoolConfigParam[$key]['min'] = 0;
$pgpoolConfigParam[$key]['max'] = 10000;

$key = 'allow_clear_text_frontend_auth';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'off';

# - SSL Connections -

$key = 'ssl';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'off';
$pgpoolConfigParam[$key]['restart'] = TRUE;

$key = 'ssl_key';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $sslreg;
$pgpoolConfigParam[$key]['restart'] = TRUE;
$pgpoolConfigParam[$key]['parent'] = array('ssl' => 'on');

$key = 'ssl_cert';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $sslreg;
$pgpoolConfigParam[$key]['restart'] = TRUE;
$pgpoolConfigParam[$key]['parent'] = array('ssl' => 'on');

$key = 'ssl_ca_cert';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $sslreg;
$pgpoolConfigParam[$key]['restart'] = TRUE;
$pgpoolConfigParam[$key]['parent'] = array('ssl' => 'on');

$key = 'ssl_ca_cert_dir';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $sslreg;
$pgpoolConfigParam[$key]['restart'] = TRUE;
$pgpoolConfigParam[$key]['parent'] = array('ssl' => 'on');

$key = 'ssl_crl_file';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $sslreg;
$pgpoolConfigParam[$key]['restart'] = TRUE;
$pgpoolConfigParam[$key]['parent'] = array('ssl' => 'on');

$key = 'ssl_ciphers';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = 'HIGH:MEDIUM:+3DES:!aNULL';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['restart'] = TRUE;
$pgpoolConfigParam[$key]['parent'] = array('ssl' => 'on');

$key = 'ssl_prefer_server_ciphers';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'off';
$pgpoolConfigParam[$key]['restart'] = TRUE;
$pgpoolConfigParam[$key]['parent'] = array('ssl' => 'on');

$key = 'ssl_ecdh_curve';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = 'prime256v1';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['restart'] = TRUE;
$pgpoolConfigParam[$key]['parent'] = array('ssl' => 'on');

$key = 'ssl_dh_params_file';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $sslreg;
$pgpoolConfigParam[$key]['restart'] = TRUE;
$pgpoolConfigParam[$key]['parent'] = array('ssl' => 'on');

$key = 'ssl_passphrase_command';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['restart'] = TRUE;
$pgpoolConfigParam[$key]['parent'] = array('ssl' => 'on');

#------------------------------------------------------------------------------
# POOLS
#------------------------------------------------------------------------------

# - Pool size -

$key = 'num_init_children';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = '32';
$pgpoolConfigParam[$key]['min'] = 1;
$pgpoolConfigParam[$key]['max'] = NUM_MAX;
$pgpoolConfigParam[$key]['restart'] = TRUE;

$key = 'max_pool';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = 4;
$pgpoolConfigParam[$key]['min'] = 1;
$pgpoolConfigParam[$key]['max'] = NUM_MAX;
$pgpoolConfigParam[$key]['restart'] = TRUE;

# - Life time -

$key = 'child_life_time';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = 300;
$pgpoolConfigParam[$key]['min'] = 0;
$pgpoolConfigParam[$key]['max'] = NUM_MAX;

$key = 'child_max_connections';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = 0;
$pgpoolConfigParam[$key]['min'] = 0;
$pgpoolConfigParam[$key]['max'] = NUM_MAX;

$key = 'connection_life_time';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = 0;
$pgpoolConfigParam[$key]['min'] = 0;
$pgpoolConfigParam[$key]['max'] = NUM_MAX;

$key = 'client_idle_limit';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = 0;
$pgpoolConfigParam[$key]['min'] = 0;
$pgpoolConfigParam[$key]['max'] = NUM_MAX;

#------------------------------------------------------------------------------
# LOGS
#------------------------------------------------------------------------------

# - Where to log -

$key = 'log_destination';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = 'stderr';
$pgpoolConfigParam[$key]['select'] = array('stderr', 'syslog');
$pgpoolConfigParam[$key]['regexp'] = selectreg($pgpoolConfigParam[$key]['select']);

$key = 'logging_collector';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'off';
$pgpoolConfigParam[$key]['restart'] = TRUE;

$key = 'log_directory';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '/tmp/pgpool_logs';
$pgpoolConfigParam[$key]['regexp'] = $dirreg;
$pgpoolConfigParam[$key]['restart'] = TRUE;
$pgpoolConfigParam[$key]['parent'] = array('logging_collector' => 'on');

$key = 'log_filename';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = 'pgpool-%Y-%m-%d_%H%M%S.log';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['restart'] = TRUE;
$pgpoolConfigParam[$key]['parent'] = array('logging_collector' => 'on');

$key = 'log_file_mode';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '0600';
$pgpoolConfigParam[$key]['regexp'] = $permissionreg;
$pgpoolConfigParam[$key]['restart'] = TRUE;
$pgpoolConfigParam[$key]['parent'] = array('logging_collector' => 'on');

$key = 'log_rotation_age';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = 1440;
$pgpoolConfigParam[$key]['min'] = 0;
$pgpoolConfigParam[$key]['max'] = NUM_MAX;
$pgpoolConfigParam[$key]['restart'] = TRUE;
$pgpoolConfigParam[$key]['parent'] = array('logging_collector' => 'on');

$key = 'log_rotation_size';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = 0;
$pgpoolConfigParam[$key]['min'] = 0;
$pgpoolConfigParam[$key]['max'] = NUM_MAX;
$pgpoolConfigParam[$key]['restart'] = TRUE;
$pgpoolConfigParam[$key]['parent'] = array('logging_collector' => 'on');

$key = 'log_truncate_on_rotation';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'off';
$pgpoolConfigParam[$key]['restart'] = TRUE;
$pgpoolConfigParam[$key]['parent'] = array('logging_collector' => 'on');

# - What to log -

$key = 'log_line_prefix';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;

$key = 'print_timestamp';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'on';
$pgpoolConfigParam[$key]['restart'] = TRUE;

$key = 'log_connections';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'off';

$key = 'log_disconnections';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'off';

$key = 'log_hostname';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'off';

$key = 'log_statement';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'off';

$key = 'log_per_node_statement';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'off';

$key = 'log_client_messages';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'off';

$key = 'log_standby_delay';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = 'none';
$pgpoolConfigParam[$key]['select'] = array('always', 'if_over_threshold', 'none');
$pgpoolConfigParam[$key]['regexp'] = selectreg($pgpoolConfigParam[$key]['select']);

# - Syslog specific -

$key = 'syslog_facility';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = 'LOCAL0';
$pgpoolConfigParam[$key]['regexp'] = $strreg;
$pgpoolConfigParam[$key]['parent'] = array('log_destination' => 'syslog');

$key = 'syslog_ident';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = 'pgpool';
$pgpoolConfigParam[$key]['regexp'] = $strreg;
$pgpoolConfigParam[$key]['parent'] = array('log_destination' => 'syslog');

# - Debug -

$key = 'debug_level';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = '0';
$pgpoolConfigParam[$key]['min'] = 0;
$pgpoolConfigParam[$key]['max'] = NUM_MAX;

$key = 'log_error_verbosity';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = 'DEFAULT';
$pgpoolConfigParam[$key]['select'] = array('TERSE', 'DEFAULT', 'VERBOSE');
$pgpoolConfigParam[$key]['regexp'] = selectreg($pgpoolConfigParam[$key]['select']);

$key = 'client_min_messages';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = 'notice';
$pgpoolConfigParam[$key]['select'] = array(
    'debug5', 'debug4', 'debug3', 'debug2', 'debug1',
    'log', 'notice', 'warning', 'error'
);
$pgpoolConfigParam[$key]['regexp'] = selectreg($pgpoolConfigParam[$key]['select']);

$key = 'log_min_messages';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = 'warning';
$pgpoolConfigParam[$key]['select'] = array(
    'debug5', 'debug4', 'debug3', 'debug2', 'debug1',
    'info', 'notice', 'warning', 'error', 'log', 'fatal', 'panic'
);
$pgpoolConfigParam[$key]['regexp'] = selectreg($pgpoolConfigParam[$key]['select']);

#------------------------------------------------------------------------------
# FILE LOCATIONS
#------------------------------------------------------------------------------

$key = 'pid_file_name';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '/var/run/pgpool/pgpool.pid';
$pgpoolConfigParam[$key]['regexp'] = $dirreg;
$pgpoolConfigParam[$key]['restart'] = TRUE;

$key = 'logdir';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '/tmp';
$pgpoolConfigParam[$key]['regexp'] = $dirreg;
$pgpoolConfigParam[$key]['restart'] = TRUE;

#------------------------------------------------------------------------------
# CONNECTION POOLING
#------------------------------------------------------------------------------

$key = 'connection_cache';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] =true;
$pgpoolConfigParam[$key]['restart'] = TRUE;

$key = 'reset_query_list';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = 'ABORT; DISCARD ALL';
$pgpoolConfigParam[$key]['regexp'] = $queryreg;

#------------------------------------------------------------------------------
# REPLICATION MODE
#------------------------------------------------------------------------------

$key = 'replication_mode';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'off';
$pgpoolConfigParam[$key]['restart'] = TRUE;

$key = 'replicate_select';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'off';
if (hasBackendClusteringMode()) {
    $pgpoolConfigParam[$key]['parent'] = array('backend_clustering_mode' => 'native_replication');
} else {
    $pgpoolConfigParam[$key]['parent'] = array('replication_mode' => 'on');
}

$key = 'insert_lock';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'off';
if (hasBackendClusteringMode()) {
    $pgpoolConfigParam[$key]['parent'] = array('backend_clustering_mode' => 'native_replication');
} else {
    $pgpoolConfigParam[$key]['parent'] = array('replication_mode' => 'on');
}

$key = 'lobj_lock_table';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
if (hasBackendClusteringMode()) {
    $pgpoolConfigParam[$key]['parent'] = array('backend_clustering_mode' => 'native_replication');
} else {
    $pgpoolConfigParam[$key]['parent'] = array('replication_mode' => 'on');
}

# - Degenerate handling -

$key = 'replication_stop_on_mismatch';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'off';
if (hasBackendClusteringMode()) {
    $pgpoolConfigParam[$key]['parent'] = array('backend_clustering_mode' => 'native_replication');
} else {
    $pgpoolConfigParam[$key]['parent'] = array('replication_mode' => 'on');
}

$key = 'failover_if_affected_tuples_mismatch';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'off';
if (hasBackendClusteringMode()) {
    $pgpoolConfigParam[$key]['parent'] = array('backend_clustering_mode' => 'native_replication');
} else {
    $pgpoolConfigParam[$key]['parent'] = array('replication_mode' => 'on');
}

#------------------------------------------------------------------------------
# LOAD BALANCING MODE
#------------------------------------------------------------------------------

$key = 'load_balance_mode';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'off';
$pgpoolConfigParam[$key]['restart'] = TRUE;

$key = 'ignore_leading_white_space';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = true;
$pgpoolConfigParam[$key]['parent'] = array('load_balance_mode' => 'on');

$key = 'white_function_list';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['parent'] = array('load_balance_mode' => 'on');

$key = 'read_only_function_list';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['parent'] = array('load_balance_mode' => 'on');

$key = 'black_function_list';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['parent'] = array('load_balance_mode' => 'on');

$key = 'write_function_list';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['parent'] = array('load_balance_mode' => 'on');

$key = 'black_query_pattern_list';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['parent'] = array('load_balance_mode' => 'on');

$key = 'primary_routing_query_pattern_list';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['parent'] = array('load_balance_mode' => 'on');

$key = 'database_redirect_preference_list';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['parent'] = array('load_balance_mode' => 'on');

$key = 'app_name_redirect_preference_list';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['parent'] = array('load_balance_mode' => 'on');

$key = 'allow_sql_comments';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'off';
$pgpoolConfigParam[$key]['parent'] = array('load_balance_mode' => 'on');

$key = 'disable_load_balance_on_write';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = 'transaction';
if (_PGPOOL2_VERSION >= 4.2) {
    $pgpoolConfigParam[$key]['select'] = array('transaction', 'off', 'trans_transaction', 'always', 'dml_adaptive');
} else {
    $pgpoolConfigParam[$key]['select'] = array('transaction', 'off', 'trans_transaction', 'always');
}
$pgpoolConfigParam[$key]['regexp'] = selectreg($pgpoolConfigParam[$key]['select']);
$pgpoolConfigParam[$key]['parent'] = array('load_balance_mode' => 'on');

$key = 'statement_level_load_balance';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'off';
$pgpoolConfigParam[$key]['parent'] = array('load_balance_mode' => 'on');

#------------------------------------------------------------------------------
# MASTER/SLAVE MODE
#------------------------------------------------------------------------------

$key = 'master_slave_mode';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'off';
$pgpoolConfigParam[$key]['restart'] = TRUE;

$key = 'master_slave_sub_mode';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = 'stream';
$pgpoolConfigParam[$key]['select'] = array('stream', 'slony');
$pgpoolConfigParam[$key]['regexp'] = selectreg($pgpoolConfigParam[$key]['select']);
$pgpoolConfigParam[$key]['restart'] = TRUE;
$pgpoolConfigParam[$key]['parent'] = array('master_slave_mode' => 'on');

# - Streaming -

$key = 'sr_check_period';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = 0;
$pgpoolConfigParam[$key]['min'] = 0;
$pgpoolConfigParam[$key]['max'] = NUM_MAX;
if (hasBackendClusteringMode()) {
    $pgpoolConfigParam[$key]['parent'] = array('backend_clustering_mode' => 'streaming_replication');
} else {
    $pgpoolConfigParam[$key]['parent'] = array('master_slave_mode' => 'on', 'master_slave_sub_mode' => 'stream');
}

$key = 'sr_check_user';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $listreg;
if (hasBackendClusteringMode()) {
    $pgpoolConfigParam[$key]['parent'] = array('backend_clustering_mode' => 'streaming_replication');
} else {
    $pgpoolConfigParam[$key]['parent'] = array('master_slave_mode' => 'on', 'master_slave_sub_mode' => 'stream');
}

$key = 'sr_check_password';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $listreg;
if (hasBackendClusteringMode()) {
    $pgpoolConfigParam[$key]['parent'] = array('backend_clustering_mode' => 'streaming_replication');
} else {
    $pgpoolConfigParam[$key]['parent'] = array('master_slave_mode' => 'on', 'master_slave_sub_mode' => 'stream');
}

$key = 'sr_check_database';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $listreg;
if (hasBackendClusteringMode()) {
    $pgpoolConfigParam[$key]['parent'] = array('backend_clustering_mode' => 'streaming_replication');
} else {
    $pgpoolConfigParam[$key]['parent'] = array('master_slave_mode' => 'on', 'master_slave_sub_mode' => 'stream');
}

$key = 'delay_threshold';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = 0;
$pgpoolConfigParam[$key]['min'] = 0;
$pgpoolConfigParam[$key]['max'] = NUM_MAX;
if (hasBackendClusteringMode()) {
    $pgpoolConfigParam[$key]['parent'] = array('backend_clustering_mode' => 'streaming_replication');
} else {
    $pgpoolConfigParam[$key]['parent'] = array('master_slave_mode' => 'on', 'master_slave_sub_mode' => 'stream');
}

# - Special commands -
$key = 'follow_master_command';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['parent'] = array('master_slave_mode' => 'on');

$key = 'follow_primary_command';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['parent'] = array('backend_clustering_mode' => 'streaming_replication');

#------------------------------------------------------------------------------
# PARALLEL MODE AND QUERY CACHE
#------------------------------------------------------------------------------

$key = 'parallel_mode';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'off';
$pgpoolConfigParam[$key]['restart'] = TRUE;

$key = 'enable_query_cache';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'off';
$pgpoolConfigParam[$key]['restart'] = TRUE;
$pgpoolConfigParam[$key]['parent'] = array('parallel_mode' => 'on');

$key = 'pgpool2_hostname';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = 'localhost';
$pgpoolConfigParam[$key]['regexp'] = $hostreg;
$pgpoolConfigParam[$key]['restart'] = TRUE;
$pgpoolConfigParam[$key]['parent'] = array('parallel_mode' => 'on');

# - System DB info -

$key = 'system_db_hostname';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = 'system_db_hostname';
$pgpoolConfigParam[$key]['regexp'] = $hostreg;
$pgpoolConfigParam[$key]['restart'] = TRUE;
$pgpoolConfigParam[$key]['parent'] = array('parallel_mode' => 'on');

$key = 'system_db_port';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = 5432;
$pgpoolConfigParam[$key]['min'] = 1024;
$pgpoolConfigParam[$key]['max'] = NUM_MAX;
$pgpoolConfigParam[$key]['restart'] = TRUE;
$pgpoolConfigParam[$key]['parent'] = array('parallel_mode' => 'on');

$key = 'system_db_dbname';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = 'pgpool';
$pgpoolConfigParam[$key]['regexp'] = $strreg;
$pgpoolConfigParam[$key]['restart'] = TRUE;
$pgpoolConfigParam[$key]['parent'] = array('parallel_mode' => 'on');

$key = 'system_db_schema';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = 'pgpool_catalog';
$pgpoolConfigParam[$key]['regexp'] = $strreg;
$pgpoolConfigParam[$key]['restart'] = TRUE;
$pgpoolConfigParam[$key]['parent'] = array('parallel_mode' => 'on');

$key = 'system_db_user';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = 'pgpool';
$pgpoolConfigParam[$key]['regexp'] = $userreg;
$pgpoolConfigParam[$key]['restart'] = TRUE;
$pgpoolConfigParam[$key]['parent'] = array('parallel_mode' => 'on');

$key = 'system_db_password';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['restart'] = TRUE;
$pgpoolConfigParam[$key]['parent'] = array('parallel_mode' => 'on');

#------------------------------------------------------------------------------
# HEALTH CHECK GLOBAL PARAMETERS
#------------------------------------------------------------------------------

$key = 'health_check_period';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] =0;
$pgpoolConfigParam[$key]['min'] = 0;
$pgpoolConfigParam[$key]['max'] = NUM_MAX;
$pgpoolConfigParam[$key]['healthcheck'] = TRUE;

$key = 'health_check_timeout';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] =20;
$pgpoolConfigParam[$key]['min'] = 0;
$pgpoolConfigParam[$key]['max'] = NUM_MAX;
$pgpoolConfigParam[$key]['healthcheck'] = TRUE;

$key = 'health_check_user';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = 'noboby';
$pgpoolConfigParam[$key]['regexp'] = $userreg;
$pgpoolConfigParam[$key]['healthcheck'] = TRUE;

$key = 'health_check_password';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['healthcheck'] = TRUE;

$key = 'health_check_database';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['healthcheck'] = TRUE;

$key = 'health_check_max_retries';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = 0;
$pgpoolConfigParam[$key]['min'] = 0;
$pgpoolConfigParam[$key]['max'] = NUM_MAX;
$pgpoolConfigParam[$key]['healthcheck'] = TRUE;

$key = 'health_check_retry_delay';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = 1;
$pgpoolConfigParam[$key]['min'] = 0;
$pgpoolConfigParam[$key]['max'] = NUM_MAX;
$pgpoolConfigParam[$key]['healthcheck'] = TRUE;

$key = 'connect_timeout';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = 10000;
$pgpoolConfigParam[$key]['min'] = 0;
$pgpoolConfigParam[$key]['max'] = NUM_MAX;
$pgpoolConfigParam[$key]['healthcheck'] = TRUE;

#------------------------------------------------------------------------------
# FAILOVER AND FAILBACK
#-----------------------------------------------------------------------------

$key = 'failover_command';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;

$key = 'failback_command';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;

$key = 'fail_over_on_backend_error';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'on';

$key = 'failover_on_backend_error';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'on';

$key = 'detach_false_primary';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'off';

$key = 'search_primary_node_timeout';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = 300;
$pgpoolConfigParam[$key]['min'] = 0;
$pgpoolConfigParam[$key]['max'] = NUM_MAX;

$key = 'auto_failback';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'off';

$key = 'auto_failback_interval';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = 60;
$pgpoolConfigParam[$key]['min'] = 0;
$pgpoolConfigParam[$key]['max'] = NUM_MAX;

#------------------------------------------------------------------------------
# ONLINE RECOVERY
#------------------------------------------------------------------------------

$key = 'recovery_user';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = 'nodoby';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;

$key = 'recovery_password';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;

$key = 'recovery_1st_stage_command';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $commandreg;

$key = 'recovery_2nd_stage_command';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $commandreg;

$key = 'recovery_timeout';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = 90;
$pgpoolConfigParam[$key]['min'] = 0;
$pgpoolConfigParam[$key]['max'] = NUM_MAX;

$key = 'client_idle_limit_in_recovery';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = 0;
$pgpoolConfigParam[$key]['min'] = (3.0 <= _PGPOOL2_VERSION) ? -1 : 0;
$pgpoolConfigParam[$key]['max'] = NUM_MAX;

#------------------------------------------------------------------------------
# WATCHDOG
#------------------------------------------------------------------------------

# Enabling

$key = 'use_watchdog';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'off';

# Watchdog communication

$key = 'wd_hostname';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $hostreg;
$pgpoolConfigParam[$key]['null_ok'] = TRUE;
$pgpoolConfigParam[$key]['parent'] = array('use_watchdog' => 'on');

$key = 'hostname';
$pgpoolConfigWdNodeParam[$key]['type'] = 'C';
$pgpoolConfigWdNodeParam[$key]['default'] = '';
$pgpoolConfigWdNodeParam[$key]['regexp'] = $hostreg;
$pgpoolConfigWdNodeParam[$key]['multiple'] = TRUE;
$pgpoolConfigWdNodeParam[$key]['parent'] = array('use_watchdog' => 'on');

$key = 'wd_port';
if (_PGPOOL2_VERSION >= 4.2) {
    $pgpoolConfigWdNodeParam[$key]['type'] = 'N';
    $pgpoolConfigWdNodeParam[$key]['default'] = 9000;
    $pgpoolConfigWdNodeParam[$key]['max'] = NUM_MAX;
    $pgpoolConfigWdNodeParam[$key]['min'] = 1024;
    $pgpoolConfigWdNodeParam[$key]['multiple'] = TRUE;
    $pgpoolConfigWdNodeParam[$key]['parent'] = array('use_watchdog' => 'on');
} else {
    $pgpoolConfigParam[$key]['type'] = 'N';
    $pgpoolConfigParam[$key]['default'] = 9000;
    $pgpoolConfigParam[$key]['max'] = NUM_MAX;
    $pgpoolConfigParam[$key]['min'] = 1024;
    $pgpoolConfigParam[$key]['parent'] = array('use_watchdog' => 'on');
}

$key = 'pgpool_port';
$pgpoolConfigWdNodeParam[$key]['type'] = 'N';
$pgpoolConfigWdNodeParam[$key]['default'] = 9999;
$pgpoolConfigWdNodeParam[$key]['max'] = NUM_MAX;
$pgpoolConfigWdNodeParam[$key]['min'] = 1024;
$pgpoolConfigWdNodeParam[$key]['multiple'] = TRUE;
$pgpoolConfigWdNodeParam[$key]['parent'] = array('use_watchdog' => 'on');

$key = 'wd_priority';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = 1;
$pgpoolConfigParam[$key]['min'] = 0;
$pgpoolConfigParam[$key]['max'] = NUM_MAX;
$pgpoolConfigParam[$key]['parent'] = array('use_watchdog' => 'on');

$key = 'wd_authkey';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['null_ok'] = TRUE;
$pgpoolConfigParam[$key]['parent'] = array('use_watchdog' => 'on');

$key = 'wd_ipc_socket_dir';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '/tmp';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['null_ok'] = TRUE;
$pgpoolConfigParam[$key]['parent'] = array('use_watchdog' => 'on');

# Connection to up stream servers

$key = 'trusted_servers';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['parent'] = array('use_watchdog' => 'on');

$key = 'ping_path';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['parent'] = array('use_watchdog' => 'on');

# Virtual IP control

$key = 'delegate_IP';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $hostreg;
$pgpoolConfigParam[$key]['null_ok'] = TRUE;
$pgpoolConfigParam[$key]['parent'] = array('use_watchdog' => 'on');

$key = 'ifconfig_path';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '/sbin';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['parent'] = array('use_watchdog' => 'on');

$key = 'if_cmd_path';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '/sbin';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['parent'] = array('use_watchdog' => 'on');

$key = 'if_up_cmd';
$pgpoolConfigParam[$key]['type'] = 'C';
if (_PGPOOL2_VERSION >= 4.1) {
    $pgpoolConfigParam[$key]['default'] = '/usr/bin/sudo /sbin/ip addr add $_IP_$/24 dev eth0 label eth0:0';
} else {
    $pgpoolConfigParam[$key]['default'] = 'ip addr add $_IP_$/24 dev eth0 label eth0:0';
}
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['parent'] = array('use_watchdog' => 'on');

$key = 'if_down_cmd';
$pgpoolConfigParam[$key]['type'] = 'C';
if (_PGPOOL2_VERSION >= 4.1) {
    $pgpoolConfigParam[$key]['default'] = '/usr/bin/sudo /sbin/ip addr del $_IP_$/24 dev eth0';
} else {
    $pgpoolConfigParam[$key]['default'] = 'ip addr del $_IP_$/24 dev eth0';
}
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['parent'] = array('use_watchdog' => 'on');

$key = 'arping_path';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '/usr/sbin';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['parent'] = array('use_watchdog' => 'on');

$key = 'arping_cmd';
$pgpoolConfigParam[$key]['type'] = 'C';
if (_PGPOOL2_VERSION >= 4.1) {
    $pgpoolConfigParam[$key]['default'] = '/usr/bin/sudo /usr/sbin/arping -U $_IP_$ -w 1 -I eth0';
} else {
    $pgpoolConfigParam[$key]['default'] = 'arping -U $_IP_$ -w 1 -I eth0';
}
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['parent'] = array('use_watchdog' => 'on');

# Behavior on escalation

$key = 'clear_memqcache_on_escalation';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'on';
$pgpoolConfigParam[$key]['parent'] = array('use_watchdog' => 'on');

$key = 'wd_escalation_command';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['parent'] = array('use_watchdog' => 'on');

$key = 'wd_de_escalation_command';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['parent'] = array('use_watchdog' => 'on');

# Controlling the Failover behavior

$key = 'failover_when_quorum_exists';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'on';
$pgpoolConfigParam[$key]['parent'] = array('use_watchdog' => 'on');

$key = 'failover_require_consensus';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'on';
$pgpoolConfigParam[$key]['parent'] = array('use_watchdog' => 'on');

$key = 'allow_multiple_failover_requests_from_node';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'off';
$pgpoolConfigParam[$key]['parent'] = array('use_watchdog' => 'on');

$key = 'enable_consensus_with_half_votes';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'off';
$pgpoolConfigParam[$key]['parent'] = array('use_watchdog' => 'on');

# Life checking pgpool-II

# (Common)

$key = 'wd_monitoring_interfaces_list';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['parent'] = array('use_watchdog' => 'on');

$key = 'wd_lifecheck_method';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = 'heartbeat';
$pgpoolConfigParam[$key]['select'] = array('heartbeat', 'query');
$pgpoolConfigParam[$key]['regexp'] = selectreg($pgpoolConfigParam[$key]['select']);
$pgpoolConfigParam[$key]['parent'] = array('use_watchdog' => 'on');

$key = 'wd_interval';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = 10;
$pgpoolConfigParam[$key]['max'] = NUM_MAX;
$pgpoolConfigParam[$key]['min'] = 0;
$pgpoolConfigParam[$key]['parent'] = array('use_watchdog' => 'on');

# (Configuration of heartbeat mode)

$key = 'wd_heartbeat_port';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = 9694;
$pgpoolConfigParam[$key]['max'] = NUM_MAX;
$pgpoolConfigParam[$key]['min'] = 0;
$pgpoolConfigParam[$key]['parent'] = array('use_watchdog' => 'on', 'wd_lifecheck_method' => 'heartbeat');

$key = 'wd_heartbeat_keepalive';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = 2;
$pgpoolConfigParam[$key]['max'] = NUM_MAX;
$pgpoolConfigParam[$key]['min'] = 0;
$pgpoolConfigParam[$key]['parent'] = array('use_watchdog' => 'on', 'wd_lifecheck_method' => 'heartbeat');

$key = 'wd_heartbeat_deadtime';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = 30;
$pgpoolConfigParam[$key]['max'] = NUM_MAX;
$pgpoolConfigParam[$key]['min'] = 0;
$pgpoolConfigParam[$key]['parent'] = array('use_watchdog' => 'on', 'wd_lifecheck_method' => 'heartbeat');

$key = 'heartbeat_destination';
$pgpoolConfigHbDestinationParam[$key]['type'] = 'C';
$pgpoolConfigHbDestinationParam[$key]['default'] = '';
$pgpoolConfigHbDestinationParam[$key]['regexp'] = $hostreg;
$pgpoolConfigHbDestinationParam[$key]['multiple'] = TRUE;
$pgpoolConfigHbDestinationParam[$key]['parent'] = array('use_watchdog' => 'on', 'wd_lifecheck_method' => 'heartbeat');

$key = 'heartbeat_hostname';
$pgpoolConfigWdHbNodeParam[$key]['type'] = 'C';
$pgpoolConfigWdHbNodeParam[$key]['default'] = '';
$pgpoolConfigWdHbNodeParam[$key]['regexp'] = $hostreg;
$pgpoolConfigWdHbNodeParam[$key]['multiple'] = TRUE;
$pgpoolConfigWdHbNodeParam[$key]['parent'] = array('use_watchdog' => 'on', 'wd_lifecheck_method' => 'heartbeat');

$key = 'heartbeat_destination_port';
$pgpoolConfigHbDestinationParam[$key]['type'] = 'N';
$pgpoolConfigHbDestinationParam[$key]['default'] = 9694;
$pgpoolConfigHbDestinationParam[$key]['min'] = 1024;
$pgpoolConfigHbDestinationParam[$key]['max'] = NUM_MAX;
$pgpoolConfigHbDestinationParam[$key]['multiple'] = TRUE;
$pgpoolConfigHbDestinationParam[$key]['parent'] = array('use_watchdog' => 'on', 'wd_lifecheck_method' => 'heartbeat');

$key = 'heartbeat_port';
$pgpoolConfigWdHbNodeParam[$key]['type'] = 'N';
$pgpoolConfigWdHbNodeParam[$key]['default'] = 9694;
$pgpoolConfigWdHbNodeParam[$key]['min'] = 1024;
$pgpoolConfigWdHbNodeParam[$key]['max'] = NUM_MAX;
$pgpoolConfigWdHbNodeParam[$key]['multiple'] = TRUE;
$pgpoolConfigWdHbNodeParam[$key]['parent'] = array('use_watchdog' => 'on', 'wd_lifecheck_method' => 'heartbeat');

$key = 'heartbeat_device';
if(_PGPOOL2_VERSION >= 4.2) {
    $pgpoolConfigWdHbNodeParam[$key]['type'] = 'C';
    $pgpoolConfigWdHbNodeParam[$key]['default'] = '';
    $pgpoolConfigWdHbNodeParam[$key]['regexp'] = $anyelse;
    $pgpoolConfigWdHbNodeParam[$key]['multiple'] = TRUE;
    $pgpoolConfigWdHbNodeParam[$key]['parent'] = array('use_watchdog' => 'on', 'wd_lifecheck_method' => 'heartbeat');
} else {
    $pgpoolConfigHbDestinationParam[$key]['type'] = 'C';
    $pgpoolConfigHbDestinationParam[$key]['default'] = 'eth0';
    $pgpoolConfigHbDestinationParam[$key]['regexp'] = $anyelse;
    $pgpoolConfigHbDestinationParam[$key]['multiple'] = TRUE;
    $pgpoolConfigHbDestinationParam[$key]['parent'] = array('use_watchdog' => 'on', 'wd_lifecheck_method' => 'heartbeat');
}

# (Configuration of query mode)

$key = 'wd_life_point';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = 3;
$pgpoolConfigParam[$key]['max'] = NUM_MAX;
$pgpoolConfigParam[$key]['min'] = 0;
$pgpoolConfigParam[$key]['parent'] = array('use_watchdog' => 'on', 'wd_lifecheck_method' => 'query');

$key = 'wd_lifecheck_query';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = 'SELECT 1';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['parent'] = array('use_watchdog' => 'on', 'wd_lifecheck_method' => 'query');

$key = 'wd_lifecheck_dbname';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = 'template1';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['parent'] = array('use_watchdog' => 'on', 'wd_lifecheck_method' => 'query');

$key = 'wd_lifecheck_user';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = 'nobody';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['parent'] = array('use_watchdog' => 'on', 'wd_lifecheck_method' => 'query');

$key = 'wd_lifecheck_password';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['parent'] = array('use_watchdog' => 'on', 'wd_lifecheck_method' => 'query');

# Servers to monitor

$key = 'other_pgpool_hostname';
$pgpoolConfigWdOtherParam[$key]['type'] = 'C';
$pgpoolConfigWdOtherParam[$key]['default'] = '';
$pgpoolConfigWdOtherParam[$key]['regexp'] = $hostreg;
$pgpoolConfigWdOtherParam[$key]['multiple'] = TRUE;
$pgpoolConfigWdOtherParam[$key]['parent'] = array('use_watchdog' => 'on');

$key = 'other_pgpool_port';
$pgpoolConfigWdOtherParam[$key]['type'] = 'N';
$pgpoolConfigWdOtherParam[$key]['default'] = 9999;
$pgpoolConfigWdOtherParam[$key]['min'] = 1024;
$pgpoolConfigWdOtherParam[$key]['max'] = NUM_MAX;
$pgpoolConfigWdOtherParam[$key]['multiple'] = TRUE;
$pgpoolConfigWdOtherParam[$key]['parent'] = array('use_watchdog' => 'on');

$key = 'other_wd_port';
$pgpoolConfigWdOtherParam[$key]['type'] = 'N';
$pgpoolConfigWdOtherParam[$key]['default'] = 9000;
$pgpoolConfigWdOtherParam[$key]['min'] = 1024;
$pgpoolConfigWdOtherParam[$key]['max'] = NUM_MAX;
$pgpoolConfigWdOtherParam[$key]['multiple'] = TRUE;
$pgpoolConfigWdOtherParam[$key]['parent'] = array('use_watchdog' => 'on');

#------------------------------------------------------------------------------
# IN MEMORY QUERY CACHE
#------------------------------------------------------------------------------

$key = 'memory_cache_enabled';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'off';

$key = 'memqcache_method';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = 'shmem';
$pgpoolConfigParam[$key]['select'] = array('shmem', 'memcached');
$pgpoolConfigParam[$key]['regexp'] = selectreg($pgpoolConfigParam[$key]['select']);
$pgpoolConfigParam[$key]['parent'] = array('memory_cache_enabled' => 'on');

$key = 'memqcache_memcached_host';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = 'localhost';
$pgpoolConfigParam[$key]['regexp'] = $hostreg;
$pgpoolConfigParam[$key]['parent'] = array('memory_cache_enabled' => 'on');

$key = 'memqcache_memcached_port';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = 11211;
$pgpoolConfigParam[$key]['max'] = NUM_MAX;
$pgpoolConfigParam[$key]['min'] = 1024;
$pgpoolConfigParam[$key]['parent'] = array('memory_cache_enabled' => 'on');

$key = 'memqcache_total_size';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = 67108864;
$pgpoolConfigParam[$key]['max'] = PHP_INT_MAX;
$pgpoolConfigParam[$key]['min'] = 0;
$pgpoolConfigParam[$key]['parent'] = array('memory_cache_enabled' => 'on');

$key = 'memqcache_max_num_cache';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = 1000000;
$pgpoolConfigParam[$key]['max'] = PHP_INT_MAX;
$pgpoolConfigParam[$key]['min'] = 0;
$pgpoolConfigParam[$key]['parent'] = array('memory_cache_enabled' => 'on');

$key = 'memqcache_expire';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = 0;
$pgpoolConfigParam[$key]['max'] = NUM_MAX;
$pgpoolConfigParam[$key]['min'] = 0;
$pgpoolConfigParam[$key]['parent'] = array('memory_cache_enabled' => 'on');

$key = 'memqcache_auto_cache_invalidation';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'on';
$pgpoolConfigParam[$key]['parent'] = array('memory_cache_enabled' => 'on');

$key = 'memqcache_maxcache';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = 409600;
$pgpoolConfigParam[$key]['max'] = PHP_INT_MAX;
$pgpoolConfigParam[$key]['min'] = 0;
$pgpoolConfigParam[$key]['parent'] = array('memory_cache_enabled' => 'on');

$key = 'memqcache_cache_block_size';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = 1048576;
$pgpoolConfigParam[$key]['max'] = PHP_INT_MAX;
$pgpoolConfigParam[$key]['min'] = 512;
$pgpoolConfigParam[$key]['parent'] = array('memory_cache_enabled' => 'on');

$key = 'memqcache_oiddir';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '/var/log/pgpool/oiddir';
$pgpoolConfigParam[$key]['regexp'] = $dirreg;
$pgpoolConfigParam[$key]['parent'] = array('memory_cache_enabled' => 'on');

$key = 'white_memqcache_table_list';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['parent'] = array('memory_cache_enabled' => 'on');

$key = 'black_memqcache_table_list';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['parent'] = array('memory_cache_enabled' => 'on');

$key = 'cache_safe_memqcache_table_list';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['parent'] = array('memory_cache_enabled' => 'on');

$key = 'cache_unsafe_memqcache_table_list';
$pgpoolConfigParam[$key]['type'] = 'C';
$pgpoolConfigParam[$key]['default'] = '';
$pgpoolConfigParam[$key]['regexp'] = $anyelse;
$pgpoolConfigParam[$key]['parent'] = array('memory_cache_enabled' => 'on');

#------------------------------------------------------------------------------
# OTHERS
#------------------------------------------------------------------------------

$key = 'relcache_expire';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = 0;
$pgpoolConfigParam[$key]['min'] = 0;
$pgpoolConfigParam[$key]['max'] = NUM_MAX;

$key = 'relcache_size';
$pgpoolConfigParam[$key]['type'] = 'N';
$pgpoolConfigParam[$key]['default'] = 256;
$pgpoolConfigParam[$key]['min'] = 0;
$pgpoolConfigParam[$key]['max'] = NUM_MAX;

$key = 'check_temp_table';
if (_PGPOOL2_VERSION >= 4.1) {
    $pgpoolConfigParam[$key]['type'] = 'C';
    $pgpoolConfigParam[$key]['default'] = 'catalog';
    $pgpoolConfigParam[$key]['regexp'] = $anyelse;
} else {
    $pgpoolConfigParam[$key]['type'] = 'B';
    $pgpoolConfigParam[$key]['default'] = 'on';
}

$key = 'check_unlogged_table';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'on';

$key = 'enable_shared_relcache';
$pgpoolConfigParam[$key]['type'] = 'B';
$pgpoolConfigParam[$key]['default'] = 'on';

$key = 'relcache_query_target';
$pgpoolConfigParam[$key]['type'] = 'C';
if (_PGPOOL2_VERSION >= 4.2) {
    $pgpoolConfigParam[$key]['default'] = 'primary';
} else {
    $pgpoolConfigParam[$key]['default'] = 'master';
}
$pgpoolConfigParam[$key]['regexp'] = $anyelse;

#------------------------------------------------------------------------------
# Deleted
#------------------------------------------------------------------------------

$key = 'backend_socket_dir';
$pgpoolConfigParam[$key]['type'] ='C';
$pgpoolConfigParam[$key]['default'] ='/tmp';
$pgpoolConfigParam[$key]['regexp'] = "$dirreg";

?>
