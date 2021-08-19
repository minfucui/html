<div class="wrapper">
  <!-- Navigation -->
  <?PHP include 'navigation.php';?>

  <div class="content-wrapper">
    <section class="content-header">
      <h1 class="page-header">
        <?PHP echo $lang['SUBMIT_A_JOB'].': '.$app;?>
      </h1>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="box box-solid">
            <div class="box-body">
              <form role="form" action="submit.php?app=<?PHP echo $app;?>" method="post">
                <div class="col-lg-12">
                  <div class="col-lg-6">
                    <table class="jtab">
                      <tr><td><h3><?PHP echo $lang['ADDITIONAL_FLUENT_OPTIONS'];?></h3></td><td></td></tr>
                      <tr>
                        <td class="cell"><?PHP echo $lang['DIMENSION'];?></td>
                        <td><div class="radio">
                          <label>
                            <input type="radio" name="dimension" value="2d" checked>2D
                          </label>
                          </div><div class="radio">
                          <label>
                            <input type="radio" name="dimension" value="3d">3D
                          </label>
                          </div>
                        </td>
                      </tr>
                      <tr>
                        <td class="cell"><?PHP echo $lang['DISPLAY_OPTIONS'];?></td>
                        <td><div class="checkbox">
                          <label>
                            <input type="checkbox" name="display_options" value="checked">
                            <?PHP echo $lang['DISPLAY_MESH_AFTER_READING'];?>
                          </label>
                          </div>
                        </td>
                      </tr>
                      <tr>
                        <td class="cell"><?PHP echo $lang['OPTIONS'];?></td>
                        <td><div class="checkbox">
                          <label>
                            <input type="checkbox" name="double_precision" value="checked">
                            <?PHP echo $lang['DOUBLE_PRECISION'];?>
                          </label></div>
                        </td>
                      </tr>
                      <tr>
                        <td class="cell"></td>
                        <td><div class="checkbox">
                          <label>
                            <input type="checkbox" name="meshing" value="checked">
                            <?PHP echo $lang['MESHING_MODE'];?>
                          </label></div>
                        </td>
                      </tr>
                      <tr>
                        <td class="cell"></td>
                        <td><div class="checkbox">
                          <label>
                            <input type="checkbox" name="post" value=checked">
                            <?PHP echo $lang['POST_ONLY'];?>
                          </label></div>
                        </td>
                      </td>
                    </table>
                  </div>
                 </div>
                 <div class="col-lg-12">
                   <div class="col-lg-6">
                     <table class="jtab">
                      <tr><td><h3><?PHP echo $lang['CLUSTER_PARAMETERS'];?></h3></td><td></td></tr>
                      <tr><td class="cell"><?PHP echo $lang['CPUS'];?></td>
                        <td><select class="form-control" name="nmin">
                          <option>1</option>
                          <option>2</option>
                          <option>4</option>
                          <option>6</option>
                          <option>8</option>
                          <option>12</option>
                          <option>16</option>
                          <!-- <option>24</option>
                          <option>32</option> -->
                          </select>
                        </td>
                      </tr>
                    </table>
                  </div>
                </div>
              <div class="col-lg-12">
                <div class="col-lg-10">
                  <table class="jtab">
                    <tr><td><h3><?PHP echo $lang['DIRECTORY'];?></h3></td><td></td></tr>
                      <tr><td class="cell"><?PHP echo $lang['WORKING_DIR'];?></td>
                        <td>
                          <input class="form-control" size=64 name="cwd" id="cwd" value="<?PHP echo $olpath;?>" required>
                          <button type="button" class="btn btn-info" data-toggle="modal"
                            data-target="#working_dir" ><?PHP echo $lang['SELECT_SERVER_DIR'];?></button>
                        </td>
                      </tr>
                    </table>
                  </div>
                </div>
                <div class="col-lg-12">
                  <div class="col-lg-6">
                    <button type="submit" class="btn btn-info"><?PHP echo $lang['SUBMIT'];?></button>
                    <button type="reset" class="btn btn-warning"><?PHP echo $lang['RESET'];?></button>
                  </div>
                </div>
              </form>
              <div class="modal" id="working_dir" role="dialog">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal">&times;</button>
                      <h4 class="modal-title"><div id="deck_title"></div></h4>
                    </div>
                    <form role="form">
                      <div class="modal-body">
                        <div id="deck_explore"></div>
                      </div>
                      <div class="modal-footer">
                        <button type="reset" class="btn btn-warning"><?PHP echo $lang['RESET'];?></button>
                        <button type="button" id="deck_selected" class="btn btn-success" data-dismiss="modal"><?PHP echo $lang['OK'];?></button>
                      </div>
                    </form>
                  </div>
                </div>
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
        function basename(str) {
           var base = new String(str).substring(str.lastIndexOf('/')+1);
           return base;
        };

        $(document).ready(function() {
                $("#deck_explore").load("php/fileexplore.php?dir="+"<?php echo $olpath;?>"+"&title=deck&op=dir");
        });

        $(document).ready(function() {
                $("#deck_selected").click(function() {
		  sf=$('input[name=deck]:checked').val();
                  //sf=$(':checkbox:checked').val();
		  if (sf)
                     a=sf;
		  else
		     a='';
                  $("#cwd").val(a);
                });
        });

    </script>
