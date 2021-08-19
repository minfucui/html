<?PHP
include '../header.php';
include '../language.php';
  $olv = $_SESSION['version'];
  $uname = $_SESSION['uname'];
  $pword = $_SESSION['password'];
  $setenvdir = 'export CB_ENVDIR='.$_SESSION['mycluster']['env'].';';
  $hdata = shell_exec("source ../env.sh;".$setenvdir."./hinfo");
  $qdata = shell_exec("source ../env.sh;".$setenvdir."./qinfo");
  $rdata = shell_exec("source ../env.sh;".$setenvdir."./hinfo -s");
  $udata = shell_exec("source ../env.sh;".$setenvdir."export OLWD=".
           $pword.";./runas ".$uname." ./uinfo");
  echo '{"hosts":'.$hdata.',"queues":'.$qdata.',"resources":'.$rdata.
       ',"users":'.$udata.'}';
?>
