<?PHP
function extLink($jinfo_data, $uname, $pword, $lang) {
  $setenvdir = 'export CB_ENVDIR='.$_SESSION['mycluster']['env'].';';
  $cmdPrefix = 'export OLWD='.$pword.';source ./env.sh;'.$setenvdir.'cmd/runas '.$uname;
  $cmdExt = $_SESSION['ext'];
  if ($jinfo_data['JOB_NAME'] == 'cubevnc' || $jinfo_data['JOB_NAME'] == 'dcv' || $jinfo_data['JOB_NAME'] == 'jupyter'
      || strpos($jinfo_data['JOB_NAME'],'GUI') !== false ) {
    if ($jinfo_data['JOB_NAME'] == 'jupyter')
      $label = 'Notebook';
    else
      $label = $lang['DESKTOP'];
    if ($jinfo_data['JOB_STATUS'] == 'Running') {
      $cmd=$cmdPrefix." cread".$cmdExt." ".$jinfo_data['JOB_ID']." | grep = | /bin/awk '{print $6}'";
      exec($cmd, $output, $exit_code);
      if (isset($output[0]) && $output[0] != ''  && (strpos($output[0], "vnc.html") ||
          strpos($output[0], ":8443") || strpos($output[0], "token=")))
        echo '<a href="'.$output[0].'" id="extlink" target="_blank" class="btn btn-info">'.$label.'</a>';
      else
        echo '<a href="#" id="extlink" target="_blank" class="btn btn-default disabled">'.$label.'</a>';
    }
    else
      echo '<a href="#" id="extlink" target="_blank" class="btn btn-default disabled">'.$label.'</a>';
    return TRUE;
  }
  else
    return FALSE;
}

function jobCmd($jinfo_data) {
    if ($jinfo_data['JOB_NAME'] == "cubevnc" ||
        $jinfo_data['JOB_NAME'] == "dcv" || 
        $jinfo_data['JOB_NAME'] == 'jupyter')
       return $jinfo_data['JOB_NAME'];
    else
       return $jinfo_data['JOB_COMMAND'];
}
