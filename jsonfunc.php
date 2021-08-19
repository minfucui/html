<?PHP
function jsonisset($string) {
    if (isset($string) && $string != "") return $string;
    return '-';
}
?>
