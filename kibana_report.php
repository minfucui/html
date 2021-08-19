<?PHP
header("Access-Control-Allow-Origin:*");
include 'header.php';
include 'language.php';
include 'jsonfunc.php';

$uname=$_SESSION['uname'];
$admin=$_SESSION['admin'];
?>

<!DOCTYPE html>
<html>
<?php include('header.html');?>
<head>
<meta http-equiv="Access-Control-Allow-Origin" content="*">
</head>
<body class="hold-transition <?PHP echo $skin;?> sidebar-mini">
<div class="wrapper">
  <!-- Navigation -->
  <?PHP include 'navigation.php';?>

  <div class="content-wrapper">
    <section class="content">
	      <div class="row">
        <div class="col-md-12">
          <div class="box box-solid">
            <div class="box-body">
              <table class="table" id="jobDataTables">
                <thead>
                <tr>
                  <th id="reptitle"><?PHP echo $lang['REPORTLIST'];?></th>
                </tr>
                </thead>
                <tbody id="kibina">
                <?PHP
                     $rawc = file_get_contents('http://'.$_SESSION['kibana'].':5601/api/saved_objects/_find?type=visualization');
                     $list = json_decode($rawc, true);
                     $cont = $list['saved_objects'];
                     foreach ($cont as $item)
                         echo '<tr><td><input type="checkbox" name="checkbox" value="'.$item['id'].'"></input><a id="'.$item['id'].'" href="#" onclick="showReport(this.id);"> '.$item['attributes']['title'].'</font></a></td></tr>';
                ?>
                </tbody>
              </table>
              <div class="modal-footer">
                 <input type="button" class="btn btn-success"id="genreport" value="<?PHP echo $lang['RUNREPORT'];?>"></input> 
              </div>
            </div>
          </div>
        </div>
      </div>
	
    </section>
  </div>
</div>
<?PHP include('js.html');?>

<script type="text/javascript" charset="utf-8">

jQuery(function($){ 
	var rephtml = "";
	var count = 0;
	$("#genreport").click(function(){
	var len = $("input:checkbox:checked").length; 
	$("input[name='checkbox']:checkbox:checked").each(function(){
	   count = count+1;
	  if(len%2==0){
		 rephtml = rephtml + getSoloUrl($(this).val(),50,300);
	  }else{
		   if(len==count){
				rephtml = rephtml + getSoloUrl($(this).val(),100,300);
		   }else{
				rephtml = rephtml + getSoloUrl($(this).val(),50,300);
		   }
		
	  }
	}) 
		$("#genreport").hide();
		$("#kibina").html(rephtml);
		$("#reptitle").html("<?PHP echo $lang['REPORTDETAIL'];?>");
	}) 
}) 



function showReport(id){
	$("#genreport").hide();
	var rephtml = getLinkSoloUrl(id,95,570);
	$("#reptitle").html(id);
	$("#kibina").html(rephtml);
}


function getSoloUrl(id,width,height){
	var soloUrl = "<iframe  height='"+height+"' src=\"http://<?PHP echo $_SESSION['kibana'];?>:5601/app/kibana#/visualize/edit/"+id+"?embed=true\"  frameBorder='0'  scrolling='no'  width='"+width+"%' ></iframe>";
	return soloUrl;
}

function getLinkSoloUrl(id,width,height){
	var soloUrl = "<iframe  height='"+height+"' src=\"http://<?PHP echo $_SESSION['kibana'];?>:5601/app/kibana#/visualize/edit/"+id+"\"  frameBorder='0'  scrolling='no'  width='"+width+"%' ></iframe>";
	return soloUrl;
}

</script>
</body>
</html>
