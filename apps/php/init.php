<?PHP
session_start();
session_destroy();
session_start();

include 'language.php';

$_SESSION['language'] = getAcceptLanguage();

if (!isset($_SESSION['language']))
    $language = 'en';
else
    $language = $_SESSION['language'];
$lang = parse_ini_file('i18n/'.$language.'.ini');
$_SESSION['lang'] = $lang;

$return = ['code'=>0, 'message'=>'successful','data'=>$lang];
if (file_exists("../config.yaml")) {
    $conf = yaml_parse_file("../config.yaml");
    $return['conf'] = $conf;
} else
    $return['conf'] = [];
echo json_encode($return);
?>
