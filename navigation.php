<header class="main-header">
  <!-- Logo -->
  <!-- mini logo for sidebar mini 50x50 pixels -->
  <a href="#" class="logo">
  <span class="logo-mini"><b>S</b></span>
  <!-- logo for regular state and mobile devices -->
  <span class="logo-lg"><b>神工仿真云</b></span>
  <!-- Header Navbar: style can be found in header.less -->
  </a>
  <nav class="navbar navbar-static-top">
    <!-- Sidebar toggle button-->
    <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
      <span class="sr-only"><?PHP echo $lang['TOGGLE_NAVIGATION'];?></span>
    </a>
    <div class="navbar-custom-menu">
      <ul class="nav navbar-nav">
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <span class="hidden-xs"><b><?PHP echo $lang['CLUSTER'].': <font color="red">'.$_SESSION['mycluster']['cluster'];?></font></b></span>
          </a>
          <?PHP
          if (sizeof($_SESSION['clusters']) > 1) {
              echo '<ul class="dropdown-menu">';
              foreach ($_SESSION['clusters'] as $cluster) {
                $clustername = $cluster['cluster'];
                if ($clustername != $_SESSION['mycluster']['cluster'])
                   echo '<li> <a href="#" class="cluster" id="'.$clustername.'"><b><font color="red">'.$clustername.'</font></b></a></li>';
              }
              echo '</ul>';
          }
          ?>
        </li>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <span class="hidden-xs"><?PHP $languages=getLanguages();echo $languages[$language];?></span>
          </a>
          <ul class="dropdown-menu">
            <?PHP
              foreach ($languages as $key => $value)
                if ($key != $language)
                  echo '<li> <a href="#" class="language" id="'.$key.'">'.$value.'</a> </li>';
            ?> 
          </ul>
        </li>
        <!-- <li>
          <a href="man.php"><?PHP echo $lang['MAN_PAGES'];?></a>
        </li> -->
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <span class="hidden-xs"><?PHP echo $uname;?></span>
          </a>
          <ul class="dropdown-menu">
            <li> <a href="index.php"><i class="fa fa-sign-out fa-fw"></i><?PHP echo $lang['LOG_OUT'];?></a> </li>
          </ul> 
        </li>
      </ul>
    </div>
  </nav>
</header>
<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">
  <section class="sidebar">
    <!-- sidebar menu: : style can be found in sidebar.less -->
    <ul class="sidebar-menu">
      <?PHP include('menu.php');?>
    </ul>
  </section> 
</aside>
<!-- JQuery -->
<script src='plugins/jQuery/jquery-2.2.3.min.js'></script>

<!-- change language script -->
<script>
$(function(){
    $('.language').on('click', function() {
	$.ajax({
	    type: 'POST',
	    data: {'language': $(this).attr('id')},
	    success: function() {
		location.reload();
	    }
	});
    });
});

$(function(){
    $('.cluster').on('click', function() {
        $.ajax({
            type: 'POST',
            data: {'cluster': $(this).attr('id')},
            success: function() {
                location.reload();
            }
        });
    });
});
</script>
