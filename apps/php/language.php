<?PHP
function getLanguages() {
    $languages = array(
        'en'    =>      'English',
        'zh_CN' =>      '中文',
    );
    return $languages;
}

function getAnglicizedLanguages() {
    $languages = array(
        'en'    =>      'English',
        'zh_CN' =>      'Chinese',
    );
    return $languages;
}

function validLang($langCode) {
    $languages = getLanguages();
    return array_key_exists($langCode, $languages);
}

// Picks the best language to display the website in by picking the first language in the Accept-Language header that is supported
// Defaults to English if no other languages are found
function getAcceptLanguage() {
    $languages = getLanguages();

    if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
        return 'en';

    $langarray = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);

    foreach ($langarray as $value) {
        $langCode = array_shift(explode(';', $value));
        $langCode = substr($langCode, 0, 2);
        if ($langCode == 'zh')
            $langCode = 'zh_CN';

        if (array_key_exists($langCode, $languages))
            return $langCode;
        else
            return 'en';
    }
}

// turns the text into a translation-array compatible key by replacing spaces with underscores and CAPITALIZING
function keyify($text) {
    return strtoupper(str_replace(' ', '_', $text));
}
?>
