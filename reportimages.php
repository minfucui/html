<?php
/*
 * Checks to see if a directory is empty.
 * Args: string containing the directory in question
 * Returns: NULL if directory doesn't exist, otherwise a boolean indicating if the directory is empty or not
 */
function is_dir_empty($dir) {
    if(!is_readable($dir)) return NULL;
    $handle = opendir($dir);
    while(false !== ($entry = readdir($handle))) {
	if($entry != "." && $entry != ".." && filesize("reports/".$entry)!=0) {
	    return FALSE;
	}
    }
    return TRUE;
}

$data = $_POST['data'];
$dir = dirname(realpath(__FILE__)).'/reports/';			// location of the report images

if(!is_null($data) && $data == 'refresh') {			// if new images are being generated, then we need to wait a few minutes while looking for them
    $TIMEOUT = 300;						// timeout of request in seconds
    for($i=0; is_dir_empty($dir) && $i<$TIMEOUT; $i++) {	// while no images can be found, wait
	sleep(1);
    }
    sleep(2);							// once we do find images, we need to wait 2 seconds to ensure that they are fully developed
}
if(is_dir_empty($dir)) echo json_encode(array());		// if no images are found, return an empty JSON object

else {
    $filenameArray = array();
    $handle = opendir($dir);
    $i=1;
    while($file = readdir($handle)) {
	if($file !== '.' && $file !== '..') {
            if (strstr($file, ".out")!=FALSE) {
                if (filesize("reports/".$file)!=0) {
                    $file=$i.".png";
                    if (file_exists("reports/".$file))
                        continue;
                    copy("imgs/nodata.png","reports/".$file);
                    $i++;
                }
		else
                    continue;
            }
	    array_push($filenameArray, 'reports/'.$file);	// fill $filenameArray with the files found (includes all extension types, not just .png/.jpg)
	}
    }
    sort($filenameArray);					// ensure the images always come in the same order
    echo json_encode($filenameArray);
}
?>
