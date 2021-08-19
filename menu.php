<?PHP
$uname=$_SESSION['uname'];
$admin=$_SESSION['admin'];
?>

<?PHP
  if ($uname == $admin) {
    echo '<li class="treeview">';
    echo '<a href="dashboard.php"><i class="fa fa-dashboard"></i>';
    echo "\n".'<span>'.$lang['DASHBOARD'].'</span></a>';
    echo '</li>';
    if ($_SESSION['es'] == TRUE){
      echo "<li class=\"treeview\">";
      echo "<a href=\"#\"><i class=\"fa fa-desktop\"></i>";
      echo "\n<span>".$lang['MONITOR']."</span>";
      echo "<span class=\"pull-right-container\">";
      echo "<i class=\"fa fa-angle-left pull-right\"></i>";
      echo "</span>";
      echo "</a>";
      echo "<ul class=\"treeview-menu\">";
      $out = file_get_contents('http://'.$_SESSION['grafana'].':3000/api/search');
      $dbs = json_decode($out, true);
      foreach ($dbs as $db) {
          echo "<li><a href=\"monitor.php?id=".$db['uri']."\">".$db['title']."</a></li>";
      }
      echo "</ul>";
      echo "</li>";
      echo "<li class=\"treeview\">";
      echo "<a href=\"kibana_report.php\"><i class=\"fa fa-area-chart\"></i>";
      echo "\n<span>".$lang['REPORTS']."</span>";
      echo "</a>";
      echo "</li>";
    }
  }
?>


<li class="treeview">
  <a href="applications.php"><i class="fa fa-file-text-o"></i>
    <span><?PHP echo $lang['APPLICATIONS'];?></span>
  </a>
</li>
<li class="treeview">
  <a href="jobs.php"><i class="fa fa-list-alt"></i>
    <span><?PHP echo $lang['JOBS'];?></span>
  </a>
</li>
<li class="treeview">
  <a href="queues.php"><i class="fa fa-th-list"></i>
    <span><?PHP echo $lang['QUEUES'];?></span>
  </a>
</li>
<?PHP
  if ($uname == $admin) { echo'
<li class="treeview">
  <a href="hosts.php"><i class="fa fa-linux"></i>
    <span>'.$lang['HOSTS'].'</span>
  </a>
</li>
<li class="treeview">
  <a href="users.php"><i class="fa fa-user"></i>
    <span>'.$lang['USERS'].'</span>
  </a>
</li>';
}
?>
<li class="treeview">
  <a href="files.php"><i class="fa fa-file"></i>
    <span><?PHP echo $lang['FILES'];?></span>
  </a>
</li>
<li class="treeview">
  <a href="bills.php"><i class="fa fa-calculator"></i>
    <span><?PHP echo $lang['BILLS'];?></span>
  </a>
</li>
