<?php
include 'common1.php';

$lang = $_SESSION['lang'];

function updateData($filepath)
{
    global $lang;
    $out = file_get_contents($filepath);
    if ($out === FALSE)
        fail('Cannot read file: '.$filepath);
    $bill = json_decode($out, true);

    $out = [];
    $out['desRows'] = [];
    $out['desRows']['CPU'] = ['title'=>'CPU(核小时)',
                   'value'=>number_format($bill['CPU_Hours'], 4)];
    if (isset($bill['CPU_Cost']) && $bill['CPU_Cost'] > 0) {
        $cpuunit = $bill['CPU_Cost']/$bill['CPU_Hours'];
    } else {
        $cpuunit = 0;
        $bill['CPU_Cost'] = 0;
    }
    $out['desRows']['CPU_Cost'] = ['title'=>'CPU价格',
               'value'=>'每核小时：￥'.number_format($cpuunit, 2).' 小计：￥'.
                        number_format($bill['CPU_Cost'],2)];
    $out['desRows']['MEM'] = ['title'=>'内存(GB小时)',
                   'value'=>number_format($bill['Mem_GB_Hours'], 4)];
    if (isset($bill['Mem_Cost']) && $bill['Mem_Cost'] > 0) {
        $memunit = $bill['Mem_Cost']/$bill['Mem_GB_Hours'];
    } else {
        $memunit = 0;
        $bill['Mem_Cost'] = 0;
    }
    $out['desRows']['Mem_Cost'] = ['title'=>'内存价格',
               'value'=>'每GB小时：￥'.number_format($memunit, 2).' 小计：￥'.
                        number_format($bill['Mem_Cost'],2)];
    $out['desRows']['GPU'] = ['title'=>'GPU(个小时)',
                   'value'=>number_format($bill['GPU_Hours'], 4)];
    if (isset($bill['GPU_Cost']) && $bill['GPU_Cost'] > 0) {
        $gpuunit = $bill['GPU_Cost']/$bill['GPU_Hours'];
    } else {
        $gpuunit = 0;
        $bill['GPU_Cost'] = 0;
    }
    $out['desRows']['GPU_Cost'] = ['title'=>'GPU价格',
               'value'=>'每GPU小时：￥'.number_format($gpuunit, 2).' 小计：￥'.
                        number_format($bill['GPU_Cost'],2)];
    foreach ($bill['App_Hours'] as $key=>$app) {
        if (!isset($app['Hours'])) {
            $out['desRows'][$key] = ['title'=>$lang['APP_NAME'].':'.$key.'(核小时)',
                          'value'=>number_format($app, 4)];
            $out['desRows'][$key.'_Cost'] = ['title'=>$lang['APP_NAME'].'价格',
                          'value'=>'每核小时：￥0.00 小计：￥0.00'];
        } else {
            $out['desRows'][$key] = ['title'=>$lang['APP_NAME'].':'.$key.'(核小>时)',
               'value'=>number_format($app['Hours'], 4)];
            if ($app['UnitPrice'] == 0 && $app['Cost'] > 0)
                $app['UnitPrice'] = $app['Cost'] / $app['Hours'];
            $out['desRows'][$key.'_Cost'] = ['title'=>$lang['APP_NAME'].'价格',
               'value'=>'每核小时：￥'.number_format($app['UnitPrice'], 2).' 小计：￥'.
                        number_format($app['Cost'],2)];
        }
    }
    $out['user'] = $bill['User'];
    $out['desRows']['Total_Cost'] = ['title'=>'月总额',
                       'value'=>'￥'.(isset($bill['Total_Cost'])?
                            number_format($bill['Total_Cost'], 2):0)];

    $out['tdata'] = [];
    
    foreach ($bill['Details'] as $item) {
        $row = [];
        foreach($item as $key=>$value) {
            if ($key == 'JobId')
                $value = '<a href="javascript:jobhist(\''.
                         $value.'\');">'.$value.'</a>';
            $row[$key] = is_numeric($value) ?
                         number_format($value, 4) : $value;
        }
        if (!isset($item['CPU_Cost']))
            $row['App_Cost'] = $row['Total_Cost'] = '￥0.00';
        else {
            $row['App_Cost'] = '￥'.number_format($item['App_Cost'], 2);
            $row['Total_Cost'] = '￥'.number_format($item['App_Cost'] +
               $item['CPU_Cost'] + $item['Mem_Cost'] + $item['GPU_Cost'], 2);
        }
        if (isset($row['Runtime']))
            $row['runTime'] = $row['Runtime'];
        if (isset($row['StartDate']))
            $row['EndDate'] = $row['StartDate'];
        if (!isset($row['App']))
            $row['App'] = '';
        $out['tdata'][] = $row;
    }
    return $out;
}

switch($_POST['action']) {
    case 'load':
        $page = yaml_parse_file('./mbill.yaml');
        if ($page === FALSE)
            fail("Internal error");
        $file = $_GET['file'];
        if ($file == '')
            fail("Wrong parameter");
        $result = updateData($file);
        $month = substr(basename($file), 5);
        $page['rows'][0]['description']['title'] = $lang['BILL'].'：'.
                $lang['USER'].' '.$result['user'].'  '.$lang['MONTH'].':'.$month;
        $page['rows'][0]['description']['rows'] = $result['desRows'];
        $page['rows'][1]['table']['data'] = $result['tdata'];
        $ret['data'] = $page;
        break;

    default:
        $ret['code'] = 500;
        $ret['message'] = 'Wrong request';
        break;
}

echo json_encode($ret);
?>
