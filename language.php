<?PHP
include 'langfunctions.php';

if (!isset($_SESSION['language'])) header('Location: index.php');

// change language
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['language']) && validLang($_POST['language'])) {
	$_SESSION['language'] = $_POST['language'];
    }
}

if (!isset($_SESSION['language']))
    $language = 'en';
else
    $language = $_SESSION['language'];
$lang = parse_ini_file('i18n/'.$language.'.ini');

?>
