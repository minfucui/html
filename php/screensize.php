<?php
session_start();
if (isset($_GET['screen'])) {
  $_SESSION['geometry'] = $_GET['screen'];
  echo "ok:".$_SESSION['geometry'];
}
else
  echo "ng";
?>
