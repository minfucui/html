<?php
/* bill related functions
 * requires db.php to read database
 */

function prices($timestamp) {
    $prices = [];
    $db = searchRowsOrder('prices', ['last_update'=>'DESC']);
    if (!isset($db['error']) && sizeof($db) > 0) {
        foreach ($db as $p)
            if ($p['last_update'] <= $timestamp && !isset($prices[$p['chargename']]))
                $prices[$p['chargename']] = $p['unitprice'];
    }
    if (!isset($prices['CPU'])) $prices['CPU'] = 0;
    if (!isset($prices['内存'])) $prices['内存'] = 0;
    if (!isset($prices['GPU'])) $prices['GPU'] = 0;
    $qs = json_decode(shell_exec('source /etc/profile.d/aip.sh;aip q i -l'), TRUE);
    if ($qs !== FALSE && sizeof($qs) > 0)
        foreach($qs as $q)
            if (!isset($prices['queue-'.$q['Name']]))
                $prices['queue-'.$q['Name']] = 0;
    $apps = searchRows('applications');
    if (!isset($apps['error']) && sizeof($apps) > 0)
        foreach ($apps as $a)
            if (!isset($prices[$a['appname']]))
                $prices[$a['appname']] = 0;

    return $prices;
}

function discounts () {
    $discounts = [];
    $userdb = searchRows('users');
    if (sizeof($userdb) > 0 && !isset($userdb['error'])) {
        foreach ($userdb as $u)
            $discounts[$u['username']] = $u['discount'];
    }
    return $discounts;
}

/* input: Array of
 *    CPU_Hours
 *    Mem_GB_Hours
 *    GPU_Hours
 *    App
 *    User
 *    Queue
 *    JobId
 *    Runtime/runTime
 * output:
 *    CPU_Hours
 *    App_Hours [App: {Hours, UnitPrice, Cost}]
 *    Month
 *    GPU_Hours
 *    Mem_GB_Hours
 *    App_Cost
 *    CPU_Cost
 *    Mem_Cost
 *    GPU_Cost
 *    Total_Cost
 *    Details {EndDate, GPU_Hours, App, JobName, JobId, Queue, Cluster, CPU_Hours,
 *             User, Mem_GB_Hours, runTime, JobDescription, CPU_Cost, GPU_Cost,
 *             Mem_Cost, App_Cost}
 */
function calcCost($jobs, $y = 0, $m = 0)
{
    /* price using the one set in the previous month. The price set in this month will
     * be reflected for the next month */
    if ($y == 0) {
        $shortName = exec('date +%Z');
        $offset = exec('date +%::z');
        $off = explode (":", $offset);
        $offsetSeconds = $off[0][0] . abs($off[0])*3600 + $off[1]*60 + $off[2];
        $longName = timezone_name_from_abbr($shortName, $offsetSeconds);
        date_default_timezone_set($longName);

        $now = localtime(time(), TRUE);
        $y = $now['tm_year'] + 1900;
        $m = $now['tm_mon'] + 1;
    }
    $starttime = sprintf("%4d-%02d-01 00:00:00", $y, $m);

    $output = ['GPU_Hours'=> 0, 'App_Hours'=>[],
               'Month'=>sprintf("%4d-%02d", $y, $m), 'CPU_Hours'=>0,
               'Users'=>[], 'Mem_GB_Hours'=>0, 'App_Cost'=>0,
               'Mem_Cost'=>0, 'GPU_Cost'=>0, 'Total_Cost'=>0, 'CPU_Cost'=>0,
               'Details'=>[]];
    $discounts = discounts();
    $prices = prices($starttime);
    $n = sizeof($jobs);
    for ($i = 0; $i < $n; $i++) {
        $j = $jobs[$i];
        if (isset($discounts[$j['User']]))
            $disc = $discounts[$j['User']];
        else
            $disc = 1;
        $jobs[$i]['GPU_Cost'] = $j['GPU_Hours'] * $disc * $prices['GPU'];
        $jobs[$i]['CPU_Cost'] = $j['CPU_Hours'] * $disc *
                   ($prices['CPU'] +
                   (isset($prices['queue-'.$j['Queue']]) ? $prices['queue-'.$j['Queue']] :0));
        $jobs[$i]['Mem_Cost'] = $j['Mem_GB_Hours'] * $disc * $prices['内存'];
        if ($j['App'] != '') {
            $appprice = isset($prices[$j['App']])? ($prices[$j['App']] * $disc) : 0;
            $jobs[$i]['App_Cost'] = $j['CPU_Hours'] * $appprice;
            if (isset($output['App_Hours'][$j['App']])) {
                 $output['App_Hours'][$j['App']]['Cost'] += $jobs[$i]['App_Cost'];
                 $output['App_Hours'][$j['App']]['Hours'] += $j['CPU_Hours'];
            } 
            else
                 $output['App_Hours'][$j['App']] = ['Hours'=>$j['CPU_Hours'],
                                                    'UnitPrice'=>$appprice,
                                                    'Cost'=>$jobs[$i]['App_Cost']];
        } else
            $jobs[$i]['App_Cost'] = 0;
        $output['GPU_Hours'] += $j['GPU_Hours'];
        $output['CPU_Hours'] += $j['CPU_Hours'];
        $output['Mem_GB_Hours'] += $j['Mem_GB_Hours'];
        $output['CPU_Cost'] += $jobs[$i]['CPU_Cost'];
        $output['GPU_Cost'] += $jobs[$i]['GPU_Cost'];
        $output['Mem_Cost'] += $jobs[$i]['Mem_Cost'];
        $output['App_Cost'] += $jobs[$i]['App_Cost'];
        if (isset($output['Users'][$j['User']]))
            $output['Users'][$j['User']] += $jobs[$i]['CPU_Cost'] + $jobs[$i]['GPU_Cost'] +
                                            $jobs[$i]['Mem_Cost'] + $jobs[$i]['App_Cost'];
        else
            $output['Users'][$j['User']] = $jobs[$i]['CPU_Cost'] + $jobs[$i]['GPU_Cost'] +
                                           $jobs[$i]['Mem_Cost'] + $jobs[$i]['App_Cost'];
    }
    $output['Total_Cost'] = $output['CPU_Cost'] + $output['GPU_Cost'] +
                            $output['Mem_Cost'] + $output['App_Cost'];
    $output['Details'] = $jobs;
    return $output;
}

function previousmonth($y, $m, $prev)
{
    $l = $y * 12 + $m - 1;
    $l -= $prev;
    return [$l / 12, $l % 12 + 1];
}
?>
