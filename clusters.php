<?PHP
if (isset($_POST['cluster'])) {
    foreach($_SESSION['clusters'] as $cluster)
        if ($cluster['cluster'] == $_POST['cluster']) {
            $_SESSION['mycluster'] = $cluster;
            break;
        }
}
?>
