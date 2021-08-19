<?PHP
$connAipConf = FALSE;
function openConn()
{
    if ($GLOBALS['connAipConf'] === FALSE)
        $GLOBALS['connAipConf'] = mysqli_connect($GLOBALS['_SESSION']['dbserver'],
                              $GLOBALS['_SESSION']['dbuser'],
                              $GLOBALS['_SESSION']['dbpassword'],
                              "aipconf");
    return $GLOBALS['connAipConf'];
}
function sqlstr($value)
{
    switch(gettype($value)) {
        case "string":
            return "'".$value."'";
            break;
        case "boolean":
            return ($value? "true":"false");
            break;
        default:
            return strval($value);
            break;
    }
}
function key2where($keys)
{
    $i = 0;
    $ret = "";
    foreach ($keys as $key=>$value) {
        if ($i > 0)
            $ret = $ret." AND ";
        else
            $i++;
        $ret = $ret.$key."=".sqlstr($value);
    }
    return $ret;
}
function insertARow($table, $rec, $ignore = FALSE)
{
    $ret = [];
    if (($conn = openConn()) === FALSE) {
        $ret['error'] = "No connection";
        return $ret;
    }

    if ($ignore)
        $sql = "insert ignore into ".$table."(";
    else
        $sql = "insert into ".$table."(";
    $i = 0;
    foreach ($rec as $key=>$value) {
       if ($i > 0)
           $sql = $sql.",";
       $sql = $sql.$key;
       $i++;
    }
    $sql = $sql.") VALUES(";
    $i = 0;
    foreach ($rec as $key=>$value) {
       if ($i > 0)
           $sql = $sql.",";
       $sql = $sql.sqlstr($value);
       $i++;
    }
    $sql = $sql.")";

    if (!mysqli_query($conn, $sql)) {
        $ret['error'] = mysqli_error($conn);
    }

    return $ret;
}

function deleteARow($table, $key)
{
    $ret = [];
    if (($conn = openConn()) === FALSE) {
        $ret['error'] = "No connection";
        return $ret;
    }

    $sql = "delete from ".$table." where ".key2where($key);
    if (!mysqli_query($conn, $sql))
        $ret['error'] = mysqli_error($conn);
    return $ret;
}

function modifyARow($table, $key, $rec)
{
    $ret = [];
    if (($conn = openConn()) === FALSE) {
        $ret['error'] = "No connection";
        return $ret;
    }

    $sql = "update ".$table." set ";
    $i = 0;
    foreach ($rec as $key1=>$value) {
       if ($i > 0)
           $sql = $sql.",";
       $sql = $sql.$key1."=".sqlstr($value);
       $i++;
    }
    $sql = $sql." where ".key2where($key);
    if (!mysqli_query($conn, $sql))
        $ret['error'] = mysqli_error($conn);

    return $ret;
}

function searchRows($table, $key=NULL)
{
    $ret = [];
    if (($conn = openConn()) === FALSE) {
        $ret['error'] = "No connection";
        return $ret;
    }

    $sql = "select * from ".$table;
    if ($key != NULL) {
        $sql = $sql." where ".key2where($key);
    }
    $res = mysqli_query($conn, $sql);
    if (!$res)
        $ret['error'] = mysqli_error($conn);
    if (mysqli_num_rows($res) > 0) {
        while ($record = mysqli_fetch_assoc($res)) {
            $ret[] = $record;
        }
    }
    return $ret;
}

function searchRowsOrder($table, $order, $skey=NULL)
{
    $ret = [];
    if (($conn = openConn()) === FALSE) {
        $ret['error'] = "No connection";
        return $ret;
    }

    $sql = "select * from ".$table;
    $i = 0;
    if ($skey != NULL)
        $sql = $sql." where ".key2where($skey);
    $sql = $sql.' order by ';
    foreach ($order as $key=>$value) {
        if ($i > 0)
            $sql = $sql.", ";
        else
            $i++;
        $sql = $sql.$key." ".$value;
    }
    $res = mysqli_query($conn, $sql);
    if (!$res) {
        $ret['error'] = mysqli_error($conn);
        error_log($ret['error']);
        return $ret;
    }
    if (mysqli_num_rows($res) > 0) {
        while ($record = mysqli_fetch_assoc($res)) {
            $ret[] = $record;
        }
    }
    return $ret;
}
?>
