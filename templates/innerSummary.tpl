<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{$message.strNodeStatus|escape}</title>
<link href="screen.css" rel="stylesheet" type="text/css" />
<style type="text/css">
{literal}
td > img {
    vertical-align: middle;
}
{/literal}
</style>
</head>

<body>
<h3>{$message.strPgpoolSummary|escape}</h3>

{if $smarty.const._PGPOOL2_STATUS_REFRESH_TIME > 0}
    <div class="auto_reloading">
    <span><img src="images/refresh.png">
          Refresh info: {$smarty.const._PGPOOL2_STATUS_REFRESH_TIME} seconds
    </span>
    </div>
    <br clear="all">
{/if}

<table>
  <tbody>
    {if hasBackendClusteringMode()}
    <tr><td>{$message.strBackendClusteringMode|escape}</td>
    <td>
      {if $params.backend_clustering_mode == 'streaming_replication'}
        {$message.strStreamingRepMode|escape}
      {elseif $params.backend_clustering_mode == 'logical_replication'}
        {$message.strLogocalRepMode|escape}
      {elseif $params.backend_clustering_mode == 'slony'}
        {$message.strSlonyMode|escape}
      {elseif $params.backend_clustering_mode == 'native_replication'}
        {$message.strNativeRepMode|escape}
      {elseif $params.backend_clustering_mode == 'snapshot_isolation'}
        {$message.strSnapshotIsolationMode|escape}
      {elseif $params.backend_clustering_mode == 'raw'}
        {$message.strRawMode|escape}
      {/if}
      </td></tr>
    {else}
      <tr><td>{$message.strReplicationMode|escape}</td>
      <td>
      {if $params.parallel_mode == 'on'}
      {$message.strInvalidation|escape}
      {elseif $params.replication_mode == 'on'}
      <img src="images/check.png"> {$message.strOn|escape}
      {else}
      <img src="images/no.png"> {$message.strOff|escape}
      {/if}
      </td></tr>

      <tr><td>{$message.strMasterSlaveMode|escape}</td>
      <td>
      {if $params.master_slave_mode == 'on'}
      <img src="images/check.png"> {$message.strOn|escape} / {$params.master_slave_sub_mode|escape}
      {else}
      <img src="images/no.png"> {$message.strOff|escape}
      {/if}
      </td></tr>
    {/if}

    <tr><td>{$message.strParallelMode|escape}</td>
    <td>
    {if $params.parallel_mode == 'on'}
    <img src="images/check.png"> {$message.strOn|escape}
    {else}
    <img src="images/no.png"> {$message.strOff|escape}
    {/if}
    </td></tr>

    <tr><td>{$message.strLoadBalanceMode|escape}</td>
    <td>
    {if $params.parallel_mode == 'on'}
    {$message.strInvalidation|escape}
    {elseif $params.load_balance_mode == 'on'}
    <img src="images/check.png"> {$message.strOn|escape}
    {else}
    <img src="images/no.png"> {$message.strOff|escape}
    {/if}
    </td></tr>
    <tr><td>{$message.strHealthCheck|escape}</td>
    <td>

    {if isHealthCheckValid($params)}
    <img src="images/check.png"> {$message.strOn|escape}
    {else}
    {$message.strInvalidation|escape}
    {/if}
    </td></tr>

    {if hasWatchdog()}
    <tr><td>Watchdog</td>
    <td>
    {if $params.use_watchdog == 'on'}
    <img src="images/check.png"> {$message.strOn|escape} / {$params.wd_lifecheck_method}
    {else}
    <img src="images/no.png"> {$message.strOff|escape}
    {/if}
    </td>
    {/if}

    <tr><td>{$message.strQueryCache|escape}</td>
    <td>
    {if hasMemqcache()}
        {if $params.memory_cache_enabled == 'on'}
        <img src="images/check.png"> {$message.strOn|escape} / {$params.memqcache_method}
        {else}
        <img src="images/no.png"> {$message.strOff|escape}
        {/if}
    {else}
        {if $params.enable_query_cache == 'on'}
        <img src="images/check.png"> {$message.strOn|escape}
        {else}
        <img src="images/no.png"> {$message.strOff|escape}
        {/if}
    {/if}
    </td></tr>

  </tbody>
</table>
</body>
</html>
