<h3>{$message.strNodeInfo|escape}</h3>

{if $smarty.const._PGPOOL2_STATUS_REFRESH_TIME > 0}
    <div class="auto_reloading">
    <span><img src="images/refresh.png">
          Refresh info: {$smarty.const._PGPOOL2_STATUS_REFRESH_TIME} seconds
    </span>
    </div>
    <br clear="all">
{/if}

{if $nodeCount > 0}
    {if $has_not_loaded_node}<p>*) {$message.msgHasNotLoadedNode}</p>{/if}

    <table>
      <thead>
      <tr>
        <th></th>
        <th><label>{$message.strIPaddress|escape}</label></th>
        <th><label>{$message.strPort|escape}</label></th>
        <th><label>{$message.strStatus|escape}</label></th>
        {if $parallelMode == false}
        <th><label>{$message.strWeight|escape}</label></th>
        {/if}
        <th colspan="2"><label>{$message.strHealthCheck|escape}</label></th>
        <th></th>
        <th></th>
      </tr>
      </thead>

    <tbody>
    {$i = 0}
    {foreach from=$nodeInfo key=node_num item=v}
        {$i = $i + 1}
        <tr class="{if $i % 2 == 0}even{else}odd{/if}">

        {* ---------------------------------------------------------------------- *}
        {* node info (IP address, port)                                           *}
        {* ---------------------------------------------------------------------- *}

        <td>node {$node_num}</td>
        <td>{$nodeInfo.$node_num.hostname|escape}</td>
        <td>{$nodeInfo.$node_num.port|escape}</td>

        {* ---------------------------------------------------------------------- *}
        {* status                                                                 *}
        {* ---------------------------------------------------------------------- *}

        {if $nodeInfo.$node_num.status != $smarty.const.NODE_NOT_LOADED}
            <td>
            {if $nodeInfo.$node_num.status == $smarty.const.NODE_ACTIVE_NO_CONNECT}
                {$message.strNodeStatus1|escape}
            {elseif $nodeInfo.$node_num.status == $smarty.const.NODE_ACTIVE_CONNECTED}
                {$message.strNodeStatus2|escape}
            {elseif $nodeInfo.$node_num.status == $smarty.const.NODE_DOWN}
                {$message.strNodeStatus3|escape}
            {/if}

            {if $nodeInfo.$node_num.is_standby == 1}
                {$message.strStandbyRunning|escape}
            {elseif $nodeInfo.$node_num.is_standby === 0}
                {$message.strPrimaryRunning|escape}
            {/if}
            </br>
            postgres:
            {if $nodeInfo.$node_num.is_active}{$message.strUp|escape}
            {else}{$message.strDown|escape}
            {/if}
            </td>
        {else}
            <td align="center">-</td>
        {/if}

        {* ---------------------------------------------------------------------- *}
        {* weight                                                                 *}
        {* ---------------------------------------------------------------------- *}

        {if $parallelMode == false}
            {if $nodeInfo.$node_num.status != $smarty.const.NODE_NOT_LOADED}
                <td>{$nodeInfo.$node_num.weight|escape}</td>
            {else}
                <td align="center">-</td>
            {/if}
        {/if}

        {* ---------------------------------------------------------------------- *}
        {* health check                                                           *}
        {* ---------------------------------------------------------------------- *}
        <td>
        {$perHC = getPerNodeHealthCheck($node_num)}
        database: {$perHC.health_check_database|escape}</br>
        user: {$perHC.health_check_user|escape}</br>
        period: {$perHC.health_check_period|escape}</br>
        timeout: {$perHC.health_check_timeout|escape}
        </td>
        <td>
        max retries: {$perHC.health_check_max_retries|escape}</br>
        retry delay: {$perHC.health_check_retry_delay|escape}</br>
        connect timeout: {$perHC.connect_timeout|escape}
        </td>

        {* ---------------------------------------------------------------------- *}
        {* buttons (attch, recovery, etc.)                                        *}
        {* ---------------------------------------------------------------------- *}

        <td nowrap>
        {if $nodeInfo.$node_num.disconnect}
          <input type="button" name="command"
                 onclick="sendCommand('detach', {$node_num|escape},
                                      '{$message.msgDetachConfirm|escape}')"
                  value="{$message.strDisconnect|escape}" />

        {elseif $nodeInfo.$node_num.return}
          <input type="button" name="command"
                 onclick="sendCommand('return', {$node_num|escape},
                                      '{$message.msgReturnConfirm|escape}')"
                 value="{$message.strReturn|escape}" />

        {elseif $nodeInfo.$node_num.recovery}
          {if $params.recovery_1st_stage_command != NULL}
              <input type="button" name="command"
                     onclick="sendCommand('recovery', {$node_num|escape},
                                          '{$message.msgRecoveryConfirm|escape}')"
                     value="{$message.strRecovery|escape}" />
          {else}
              <input type="button" name="command"
                     title="Please set recovery_1st_stage_command." disabled
                     value="{$message.strRecovery|escape}" />
          {/if}
        {/if}

        {if $nodeInfo.$node_num.promote && $nodeInfo.$node_num.is_standby == 1}
          {if hasBackendClusteringMode()}
            <input type="button" name="command"
                   onclick="sendCommand('promote', {$node_num|escape},
                                      '{$message.msgRPromoteConfirm|escape}')"
                   value="{$message.strPromotePrimary|escape}" />
          {else}
            <input type="button" name="command"
                   onclick="sendCommand('promote', {$node_num|escape},
                                      '{$message.msgRPromoteConfirm|escape}')"
                   value="{$message.strPromote|escape}" />
          {/if}
        {/if}
        </td>

        {* ---------------------------------------------------------------------- *}
        {* buttons (start, stop, etc.)                                            *}
        {* ---------------------------------------------------------------------- *}

        <td nowrap>{include file="elements/status_pgsql_buttons.tpl"}</td>
        </tr>
    {/foreach}
    </tbody>

      <tfoot>
      <tr><th colspan="9" align="right">
          <input type="button" id="button_add_backend" onClick="addBackendButtonHandler()"
                 value="{$message.strAddBackend|escape}" />
          </th>
      </tr>
      </tfoot>

    </table>

{else}
    {$message.strNoNode|escape}
{/if}


<p>[ mode ]
{if hasBackendClusteringMode()}
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
{else}
    {if $params.replication_mode == 'on'}
        {$message.strReplicationMode|escape}
    {elseif $params.master_slave_mode == 'on'}
        {$message.strMasterSlaveMode|escape}
    {/if}
{/if}
{if $params.load_balance_mode == 'on'} / {$message.strLoadBalanceMode|escape}{/if}
{if $params.memory_cache_enabled == 'on'} / {$message.strQueryCache|escape} {$message.strOn|escape}{/if}
{if $params.use_watchdog == 'on'} / Watchdog {$message.strOn|escape}{/if}
</p>
