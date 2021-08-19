<?php
function progress($value)
{
    $num = number_format(($value * 100), 1);
    $num = $num.'%';
    $percent = number_format(($value * 100), 0);
    $percent = $percent.'%';
    $color = "success";
    if ($value > 0.8)
        $color = "warning";
    if ($value > 0.95)
        $color = "danger";
    
    return '<div class="progress" style="position:relative;"><div class="bar bg-'.$color.
           '" style="width:'.$percent.';"></div>'.
           '<span style="position:absolute;text-align:center;width:100%;z-index:2;">'.$num.'</span></div>';
}
?>
