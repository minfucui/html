<?php
# The function requires
# - file access function
#
# return: ['code'=>code, 'message'=>message, 'url'=>url
#

function vncurl ($jobid, $password, $uname, $session=[])
{
    global $_SERVER;
    $ret = ['code'=>0, 'message'=>'success', 'url'=>''];
    // get user's home
    exec('getent passwd '.$uname.' | cut -d: -f6', $r, $errno);
    if ($errno != 0) {
        $ret['code'] = 200;
        $ret['message'] = 'cannot find user home'; 
        return $ret;
    }
    $home = $r[0];

    // get session file
    if (sizeof($session) == 0) {
        $s = myFile_Get_Contents($home.'/.vnc/session.'.$jobid);

        if ($s === FALSE) {
            $ret['code'] = 0;
            $ret['message'] = "can't find session file";
            $ret['url'] = 'url wait';
            return $ret;
        }

        $s1 = explode(' ', $s);
        $host = $s1[0];
        $sid = $s1[1];
    } else {
        $sid = $session['sid'];
        $host = $session['host'];
    }

    $p = hexdec(substr(hash('md5', $uname.$jobid), 0, 4));
    if ($p < 16000)
       $p += 16000;
    $r = [];
    $vncport = strval(5900 + intval($sid));
    $lport = $p;
    exec('source /var/www/html/env.sh;echo $CB_ENVDIR', $r, $errno);
    $conff = $r[0].'/vncsub.yaml';
    $hostsFile = $r[0].'/hosts';

    $pp = '';
    $ssl = 0;
    $novnc = 'vnc.html';
    $httpport = '80';
    $httpsport = '443';
    exec('hostname', $hs, $errno);
    $publicaddr = $_SERVER['SERVER_NAME'];
    $myhost = $hs[0]; //$_SERVER['SERVER_NAME'];

    if (file_exists($hostsFile) && ip2long($host) === FALSE) {
        /* convert $host to IP */
        exec('grep '.$host.' '.$hostsFile.' | cut -d" " -f1', $ipout, $errno);
        if (sizeof($ipout) > 0)
            $host = $ipout[0];
    }
    
    error_reporting(E_ERROR);
    if (file_exists($conff) && ($conf = yaml_parse_file($conff)) !== FALSE) {
        if (isset($conf['pp']) && is_writable($conf['pp']) &&
            is_dir($conf['pp']))
            $pp = $conf['pp'];
        if (isset($conf['novnc']))
            $novnc = strpos($conf['novnc'], 'lite') !== FALSE ?
                     'vnc_lite.html' : 'vnc.html';
        if (isset($conf['ssl']))
            $ssl = 1;
        if (isset($conf['http_port']))
            if ($ssl == 0)
                $httpport = strval($conf['http_port']);
            else
                $httpsport = strval($conf['http_port']);
        if (isset($conf['pubweb_ip']))
            $publicaddr = $conf['pubweb_ip'];
    }
    if ($pp != '') {
        if ($ssl == 0) {
            $http = 'http://';
            $entryPoints = ['web'];
        } else {
            $http = 'https://';
            $entryPoints = ['websecure'];
            $httpport = $httpsport;
        }
        $conffile = $pp.'/web';
    }
    $cmd = $r[0].'/skyformvnc/novnc/utils/websockify/websockify.py '.
           '-D --run-once --timeout=30 ';
    if ($pp != '') {
        if ($ssl == 0) {
            $http = 'http://';
            $entryPoints = ['web'];
        } else {
            $http = 'https://';
            $entryPoints = ['websecure'];
            $httpport = $httpsport;
        }
        $conffile = $pp.'/'.$uname.$jobid.'.yaml';
        $url = '/portal/novnc/'.$novnc.'?autoconnect=true&host='.$publicaddr.
               '&port='.$httpport.'&password='.$password.
               '&resize=remote&quality=9&path=web'.$lport;
        $tconf = ['http'=>['routers'=>[
                             'to-novnc'.$lport=>[
                               'entryPoints'=>$entryPoints,
                               'rule'=>'PathPrefix(`/web'.$lport.'`)',
                               'service'=>'novnc'.$lport
                               #, 'tls'=>['passthrough'=>'true']
                               ]],
                           'services'=>[
                             'novnc'.$lport=>[
                               'loadBalancer'=>[
                                 'servers'=>[['url'=>$http.$myhost.':'.$lport.'/']]
                            ]]]
                  ]];
        if ($ssl != 0)
            $tconf['http']['routers']['to-novnc'.$lport]['tls'] =
               ['passthrough'=>'true'];
               #['certificates'=>[['certFile'=>'/etc/pki/tls/certs/ca.crt',
               #                   'keyFile'=>'/etc/pki/tls/private/ca.key']]];
        if (!file_exists($conffile)) {
            yaml_emit_file($conffile, $tconf);
            sleep(1);
        }
    } else {
        $url = '/novnc/'.$novnc.'?autoconnect=true&host='.$publicaddr.
               '&port='.$lport.'&password='.$password.
               '&resize=remote&quality=9';
    }
    if ($ssl == 0) {
        $cmd2exe = $cmd.$myhost.':'.$lport.' '.$host.':'.$vncport." 2>&1";
    } else {
        $cmd2exe = $cmd.'--cert=/etc/pki/tls/certs/ca.crt '.
                   '--key=/etc/pki/tls/private/ca.key --ssl-only '.
                   $myhost.':'.$lport.' '.$host.':'.$vncport." 2>&1"; 
    }
    $out = shell_exec($cmd2exe);
    $ret['url'] = $url;
    return $ret;
}

function confClean($jobs)
{
    exec('source /var/www/html/env.sh;echo $CB_ENVDIR', $r, $errno);
    $conff = $r[0].'/vncsub.yaml';
    $pp = '';

    if (file_exists($conff) && ($conf = yaml_parse_file($conff)) !== FALSE) {
        if (isset($conf['pp']) && is_writable($conf['pp']) &&
            is_dir($conf['pp']))
            $pp = $conf['pp'];
    }
    if ($pp == '' || ($files = scandir($pp)) === FALSE)
        return;
    $goodNames = [];
    foreach ($jobs as $j) {
        if (isset($j['STATUS']) && ($j['STATUS'] == 'Running' ||
                  $j['STATUS'] == 'Stopped by the scheduler' ||
                  $j['STATUS'] == 'Suspended while running'))
             $goodNames[] = $j['USER'].$j['JOB_ID'].'.yaml';
        if (isset($j['StatusString']) && ($j['StatusString'] == 'RUN' ||
                  $j['StatusString'] == 'SYSSTOP' ||
                  $j['StatusString'] == 'USRSTOP'))
             $goodNames[] = $j['User'].$j['JobID']['JobID'].'.yaml';
        if (isset($j['statusString']) && ($j['statusString'] == 'RUN' ||
                  $j['statusString'] == 'SYSSTOP' ||
                  $j['statusString'] == 'USRSTOP'))
             $goodNames[] = $j['user'].$j['jobID']['jobID'].'.yaml';
    }
    foreach ($files as $f) {
        if ($f[0] == '.')
            continue;
        if ($f == 'portal.yaml' || strpos($f, 'web') === 0)
            continue;
        if (!in_array($f, $goodNames))
            unlink($pp.'/'.$f);
    }
}
?>
