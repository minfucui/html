<?PHP
   header ("Location: submit.php?app=".$app);
?>

<div class="wrapper">
  <!-- Navigation -->
  <?PHP include 'navigation.php';?>

  <div class="content-wrapper">
    <section class="content-header">
      <h1 class="page-header">
        <?PHP echo $lang['SUBMIT_A_JOB'];?>: SWOpenFoam
      </h1>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="box box-solid">
            <div class="box-body">
              <form role="form" action="applications.php" method="post">
                <div class="col-lg-12">
                <div>
                <div class="col-lg-12">
                  <div class="col-lg-6">
                    <button type="submit" class="btn btn-info">OK</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</div>
    <?PHP include('js.html');?>
