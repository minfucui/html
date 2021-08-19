<?PHP
session_start();
session_destroy();
session_start();

include 'langfunctions.php';
$_SESSION['language'] = getAcceptLanguage();

header('Location: up/index.php');
?>
