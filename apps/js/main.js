/*
 * Skycloud Software
 * Copyright 2020
 *
 * Basic javascript for constructing menu and common components
 */

var btnstyle = '';

var dict = JSON.parse(localStorage.getItem('dict'));
if (dict == null)
    window.location.href = "index.html";

$('head').append('<title>' + dict.SKYFORM_WEB_INTERFACE + '</title>');
$('#logo').html(dict.SKYFORM_WEB_INTERFACE);

var load = false;
var firstload;
var prevActiveMenuId = '';
var prevActiveSubmenuId = '';
var activeId;
var tableInterval;
var table;
var description;
var descriptionInterval;
var eventInterval;
var chartInterval;
var menu;
var chartData = [];
var datePicker = [];
var viewModalData = {};
var jobsfilter = '';

if (!load) {
  /* load menu */
  $.ajax({
    type: "post",
    url: "php/menu.php",
    contentType: "application/json",
    success: function(data) {
      var d = JSON.parse(data);
      var i, j;
      if (d.code == 0) {
        if (d.data.logout == 'yes')
          window.location.href = "index.html";
        menu = d.data.menu;
        if (menu.side_menu != null && menu.side_menu.length > 0) {
          var sideMenuStr = '';
          for (i = 0; i < menu.side_menu.length; i++) {
            if (menu.side_menu[i].submenu != null) {
              sideMenuStr += '<li class="nav-item has-treeview"><div class="nav-link" id="' +
                            menu.side_menu[i].title + '" style="cursor: pointer;">' +
                            '<i class="nav-icon fas fa-' + menu.side_menu[i].icon + '"></i><p>' +
                            menu.side_menu[i].title + '<i class="right fas fa-angle-left"></i>' +
                            '</p></div><ul class="nav nav-treeview">';
              for (j = 0; j < menu.side_menu[i].submenu.length; j++) {
                sideMenuStr += '<li class="nav-item"><div onclick="openurl(\'php/' +
                             menu.side_menu[i].submenu[j].url + '\',\'' +
                             menu.side_menu[i].title + '\',\'' + menu.side_menu[i].submenu[j].title +
                             '\')" class="nav-link" style="cursor: pointer;" id="' +
                             menu.side_menu[i].submenu[j].title +
                             '"><i class="far fa-circle nav-icon"></i><p>' +
                             menu.side_menu[i].submenu[j].title + '</p></div></li>';
                if (i == 0 && j == 0) {
                  firstload = menu.side_menu[i].submenu[j].url;
                  activeId = menu.side_menu[i].title;
                }
              }
              sideMenuStr += '</ul></li>';
            } else {
              sideMenuStr += '<li class="nav-item"><div onclick="openurl(\'php/' + menu.side_menu[i].url +
                           '\',\'' + menu.side_menu[i].title +
                           '\',\'\')" class="nav-link" id="' + menu.side_menu[i].title +
                           '" style="cursor: pointer;"><i class="nav-icon fas fa-' +
                           menu.side_menu[i].icon + '"></i><p>' + menu.side_menu[i].title + '</p></div></li>';
              if (i == 0) {
                firstload = menu.side_menu[i].url;
                activeId = menu.side_menu[i].title;
              }
            }
          }
          $('#sidemenu').html(sideMenuStr);
        } else
          window.location.href = "index.html";
        $('#account').html(d.data.uname);
        $("#avatar").attr("src", "images/" + d.data.avatar + ".png");
        if (d.data.forum == 'yes')
           $("#forum").html(dict.FORUM);
        openurl('php/' + firstload, activeId);
      } else {
        window.location.href = "index.html";
      }
    },
    error: function() {
      window.location.href = "index.html";
    } 
  });  
  eventInterval = setInterval("eventAjax()", 5000);
  function eventAjax() {
    $.ajax({
      type: "post",
      url: "php/queryEvent.php",
      contentType: "application/json",
      data: JSON.stringify({}),
      dataType: "json",
      success: function(d) {
          if (d.data) {
              d.data.forEach(function(item) {
                 var color;
                 switch(item.status) {
                     case 'WAIT':
                     case 'WSTOP':
                         color = 'secondary';
                         break;
                     case 'RUN':
                         color = 'primary';
                         break;
                     case 'FINISH':
                         color = 'success';
                         break;
                     case 'SYSSTOP':
                     case 'USRSTOP':
                         color = 'warning';
                         break;
                     default:
                         color = 'danger';
                         break;
                 }
                 $(document).Toasts('create', {
                     position: 'bottomRight',
                     class: 'bg-' + color,
                     title: item.entity + ': ' + item.instance + 
                            ' / ' + item.project + ' : ' + dict[item.status] + ' ' + item.time
                     //body: item.time
                 });
              });
          }
      },
      error: function(msg) {
         alert("Back end error!");
         window.location.href = "index.html";
      }
    });
  }
  $("#logout-modal-title").html("<h4>" + dict.CONFIRM_LOGOUT + "</h4>");
  $("#logout-confirm").html(dict.CONFIRM);
  $("#logout-cancel").html(dict.CANCEL);
  $("#logout-label").html(dict.LOGOUT);
  $("#manpages").html(dict.MAN_PAGES);
  loadTopAppMenu();

  load = true;
}

function logoutAction() {
    $.ajax({
        type: "post",
        url: "php/logout.php",
        contentType: "application/json",
        dataType: "json",
        success: function(d) {
            if (d.code == 0) {
                localStorage.removeItem("dict");
            } else {
                failureHandler(d);
            }
        },
        error: function(msg) {
            errorModal("Back end error!");
        }
    });
    window.location.href = "./index.html";
}

function openurl(urlStr, menuid = '', submenuid = '') {
    initialize();
    if (urlStr.includes('php/jobs.php') && (n = urlStr.indexOf('?')) != -1) {
        jobsfilter = urlStr.substring(n + 1);
    }
    if (menuid != '') {
        if (prevActiveMenuId != '')
            $('#' + prevActiveMenuId).removeClass('active');
        if (prevActiveSubmenuId != '')
            $('#' + prevActiveSubmenuId).removeClass('active');
        $('#' + menuid).addClass('active');
        prevActiveMenuId = menuid;
        prevActiveSubmenuId = submenuid;
        if (submenuid != '')
            $('#' + submenuid).addClass('active');
    } 
    $.ajax({
        type: "post",
        url: urlStr,
        data: '{"action":"load"}',
        contentType: "application/json",
        dataType: "json",
        success: function(d) {
            if (d.code == 0) {
                processPage(d.data);
            } else {
                failureHandler(d);
            }
        },
        error: function(msg) {
            alert("Back end error!");
        }
    });   
}

function processPage(data)
{
    $('#page-title').html(dict[data.page_title] ? dict[data.page_title] : data.page_title);
    var rowWidth = 0;
    var hStr = '';
    data.rows.forEach(function(row, index){
      if (rowWidth == 0) {
          hStr += '<div class="row">';
      }
      hStr += '<div class="col-lg-' + row.width.toString() + '">';
      rowWidth += row.width;
      var box = 0;
      for (var key in row) {
        if (key == 'file') {
            hStr += '<div id="fileexplorer"></div></div>';
            $('#main' + index.toString()).html(hStr);
            fileExplorer(row[key]);
            return;
        }
        if (key == 'buttons') {
            if (box == 0) {
                hStr += '<div class="card"><div class="card-body">';
                box = 1;
            }
            for(i = 0; i < row[key].length; i++) {
                var button = row[key][i];
                hStr += '<button type="button" class="btn btn-' + btnstyle + 
                        button.attr + '" ';
                if (button.modal) {
                    hStr += 'data-toggle="modal" data-target="#' + button.modal + '"';
                }
                if (button.action) {
                    hStr += ' onclick="' + button.action +';"';
                }
                hStr += '>' + (dict[button.label] ? dict[button.label] : button.label) +
                        '</button> ';
                
            }
        }
        if (key == 'table') {
            if (box == 0) {
                hStr += '<div class="card"' +
                    (row.height? ' style="height:' + row.height + ';"' :'') +
                    '><div class="card-body">';
                box = 1;
            } 
            table = row[key];
            if (table.title) {
                hStr += '<div class="card-header"><h5>' +
                        (dict[table.title]?dict[table.title]:table.title) +
                        '</h5></div>';
            }
            hStr += '<div class="card-body"><table id="' + table.id +
                    '" class="table table-sm table-striped">';
            if (table.thead) {
                hStr += '<thead><tr><th>' +
             (table.noselect ? '': '<input type="checkbox" id="all", value="">') + 
                        '</th>';
                for (i = 0; i < table.thead.length; i++)
                    hStr += '<th>' + (dict[table.thead[i]]?dict[table.thead[i]]:table.thead[i]) + '</th>';
                hStr += '</tr></thead>';
            }
            hStr += '<tbody></tbody></table></div>';
            updateTable(table);
            if (table.update && table.update.interval) {
                updateTableAjax(table);
                tableInterval = setInterval("updateTableAjax(table)",
                                table.update.interval*1000);
            }
        }
        if (key == 'form') {
            if (box == 0) {
                hStr += '<div class="card"' +
                (row.height? ' style="height:' + row.height + ';overflow:auto;"' :'') +
                '><div class="card-body">';
                box = 1;
            }
            hStr += formHTML(row[key]);
        }
        if (key == 'description') {
            if (box == 0) {
                hStr += '<div class="card"' +
                    (row.height? ' style="height:' + row.height + ';"' :'') +
                    '>';
                if (row[key].title)
                    hStr += '<div class="card-header"><h3 class="card-title">' +
                            row[key].title + '</h3></div>';
                hStr += '<div class="card-body">';
                box = 1;
            }
            hStr += descriptionHTML(row[key]);
            description = row[key];
            if (row[key].update) {
                updateDescriptionAjax();
                descriptionInterval = setInterval("updateDescriptionAjax()",
                                      description.update.interval * 1000);
            }
        }
        if (key == 'joboutput') {
            if (box == 0) {
                hStr += '<div class="card"' +
                    (row.height? ' style="height:' + row.height + ';overflow:auto;"' :'') +
                    '><div class="card-body">';
                box = 1;
            }
            hStr += '<div class="card-header">' +
                   // '<h4 class="card-title">' + dict.JOB_OUTPUT + '</h4>' +
                    '<span id="guiurl"></span></div><div class="card-body"><pre>' +
                    '<span id="joboutput"></span>' +
                    '</pre></div>';
        }
        if (key == 'donutchart' || key == 'hor_barchart' || key == 'hostlist') {
            if (box == 0) {
                hStr += '<div class="card">';
                box = 1;
            }
            if (row[key].title)
                hStr += '<div class="card-header"><h3 class="card-title">' +
                         (dict[row[key].title]?dict[row[key].title]:row[key].title) +
                         '</h3></div>';
            hStr += '<div class="card-body"><div id="' + row[key].id + '"';
            if (key != 'hostlist')
                hStr += ' style="height:200px;"';
            hStr += '>';
            if (key == 'donutchart')
                donutChart(row[key]);
            if (key == 'hor_barchart')
                horBarchart(row[key]);
            if (key == 'hostlist')
                hStr += hostChart(row[key]);
            hStr += '</div>';
        }
        if (key == 'infobox') {
            if (box == 0) {
                hStr += '<div class="info-box">';
                box = 1;
            }
            hStr += '<span class="info-box-icon bg-' + row[key].attr +
                    ' elevation-1"><i class="fas fa-' + row[key].icon +
                    '"></i></span><div class="info-box-content">' +
                    '<span class="info-box-text">' +
                    (dict[row[key].title]?dict[row[key].title]:row[key].title) +
                    '</span><span class="info-box-number" id="' + row[key].id +
                    '">' + row[key].data + '</span>';
        }
        if (key == 'report') {
            if (box == 0) {
                hStr += '<div class="card"' +
                    (row.height? ' style="height:' + row.height + ';"' :'') +
                    '>';
                if (row[key].title)
                    hStr += '<div class="card-header"><h5>' +
                            row[key].title + '</h5></div>';
                hStr += '<div class="card-body" id="report-image">';
                box = 1;
            }
            if (row[key].data)
                hStr += row[key].data;
        }
      }
      hStr += '</div></div></div>'; /* card-body/info-box-content;
                                     * card/info-box;
                                     * column */
      if (rowWidth >= 12) {
          hStr += '</div>';
          rowWidth = 0;
      }
    });
    $('#main').html(hStr);
    if (data.modals) {
        for (i = 0; i < data.modals.length; i++) {
            switch(data.modals[i].modal) {
            case 'add-modal':
                addModal(data.modals[i]);
                break;
            case 'del-modal':
                batchModal(data.modals[i], 'del');
                break;
            case 'view-modal':
                viewModalData = {};
                viewModalData = jQuery.extend({}, data.modals[i]);
                break;
            case 'b1-modal':
                batchModal(data.modals[i], 'b1');
                break;
            case 'b2-modal':
                batchModal(data.modals[i], 'b2');
                break;
            default:
                break;
            }
        }       
    }
    if (chartData.length > 0)
        drawCharts();
    if (datePicker.length > 0)
        datePickerFunc();
}

function addModal(modal)
{
    $('#add-modal-title').html(dict[modal.title] ? dict[modal.title] : modal.title);
    var addbutton = modal.buttons[0];
    var resetbutton = modal.buttons[1];
    $('#add-modal-confirm').html(dict[addbutton.label] ? dict[addbutton.label] :
                                 addbutton.label);
    $('#add-modal-confirm').addClass('btn-' + btnstyle + addbutton.attr);
    $('#add-modal-cancel').html(dict[resetbutton.label] ? dict[resetbutton.label] :
                                resetbutton.label);
    $('#add-modal-cancel').addClass('btn-' + btnstyle + resetbutton.attr);
    $('#add-modal').on('show.bs.modal', function() {
        var mStr = horizontalForm(modal.body, table.options);
        $('#add-modal-body').html(mStr);
        if (datePicker.length > 0)
            datePickerFunc({timepicker:false,
                            format:'Y-m-d'});
    });
    $('#add-modal-confirm').off('click');
    $('#add-modal-confirm').click(function() {
        var postdata = JSON.parse(addbutton.post);
        var dat = {};
        for (var i = 0; i < modal.body.length; i++) {
            dat[modal.body[i].id] =  $('#' + modal.body[i].id).val();
        }
        postdata.data = dat;
        $.ajax({
            type: "post",
            url: addbutton.url,
            data: JSON.stringify(postdata),
            contentType: "application/json",
            dataType: "json",
            success: function(d) {
                if (d.code == 0) {
                    $('#add-modal').modal('hide');
                    updateTableAjax (table);
                    if ($('#fileexplorer').length)
                        $('#fileexplorer').elfinder('instance').exec('reload');
                } else {
                    warning('add-error-message', d.message);
                }
            },
            error: function(msg) {
                alert("Back end error!");
            }
        });
    })
}

function clearButtonClass(buttonid, classPrefix)
{
    var classList = $('#' + buttonid).attr('class').split(/\s+/);
    classList.forEach(function(item) {
        if (item.startsWith(classPrefix))
            $('#' + buttonid).removeClass(item);
    });
}

function batchModal(modal, prefix)
{
    $('#'+ prefix +'-modal-title').html(
                 dict[modal.title] ? dict[modal.title] : modal.title);
    var delbutton = modal.buttons[0];
    var cancelbutton = modal.buttons[1];
    $('#'+ prefix +'-modal-confirm').html(
                 dict[delbutton.label] ? dict[delbutton.label] : delbutton.label);
    clearButtonClass(prefix +'-modal-confirm', 'btn-');
    $('#'+ prefix +'-modal-confirm').addClass('btn-' + btnstyle + delbutton.attr);
    $('#'+ prefix +'-modal-cancel').html(
                 dict[cancelbutton.label] ? dict[cancelbutton.label] :
                                cancelbutton.label);
    clearButtonClass(prefix +'-modal-cancel', 'btn-');
    $('#' + prefix + '-modal-cancel').addClass('btn-' + btnstyle + cancelbutton.attr);
    $(function() {
        $('#'+ prefix + '-modal').on('show.bs.modal', function() {
            var to_delete = [];
            modal.del = [];
            $.each($("input[name='individual']:checked"), function() {
                itemname = $(this).val();
                item = findTableItem(decodeURI(itemname), delbutton.confirm);
                to_delete.push(item);
                modal.del.push(modal.all ? item : itemname);
            });
            if (to_delete.length == 0) {
                $('#'+ prefix +'-modal-confirm').prop('disabled', true);
            } else {
                $('#'+ prefix +'-modal-confirm').prop('disabled', false);
            }
            /* prepare header */
            var header = [];
            for (var i = 0; i < table.thead.length; i++) {
                for (var j = 0; j < delbutton.confirm.length; j++) {
                    if (delbutton.confirm[j] == table.tbody[i]) {
                        header.push(dict[table.thead[i]] ? dict[table.thead[i]] :
                                    table.thead[i]);
                        break;
                    }
                }
            }
            $('#'+ prefix +'-modal-body').html(simpleTable(header, to_delete) +
                     (prefix == 'del' ? '<p><div class="alert alert-danger">' +
                     dict['CANTUNDO'] + '</div>' : ''));
            $('#'+ prefix +'-error-message').html('');
        });
    });
    $('#'+ prefix +'-modal-confirm').off('click');
    $('#'+ prefix +'-modal-confirm').click(function() {
        var postdata = JSON.parse(delbutton.post);
        postdata.data = modal.del;
        $.ajax({
            type: "post",
            url: delbutton.url,
            data: JSON.stringify(postdata),
            contentType: "application/json",
            dataType: "json",
            success: function(d) {
                if (d.code == 0) {
                    $('#'+ prefix +'-modal').modal('hide');
                    updateTableAjax (table);
                    if ($('#fileexplorer').length)
                        $('#fileexplorer').elfinder('instance').exec('reload');
                } else {
                    warning(prefix +'-error-message', d.message);
                }
            },
            error: function(msg) {
                alert("Back end error!");
            }
        });
    })
}

function findTableItem(name, confirm)
{
    var ret = [];
    var key = (table.idkey ? table.idkey : 'name');
    if (table.idkey == 'none')
        key = table.tbody[0];
    for (var i = 0; i < table.data.length; i++) {
        if (table.data[i][key] == name) {
            for (j = 0; j < confirm.length; j++) {
                ret[j] = table.data[i][confirm[j]];
            }
            break;
        }
    }
    return ret;
}

function viewModal(name)
{
    var modal = {};
    modal = jQuery.extend({}, viewModalData);
    $('#view-modal-title').html(dict[modal.title] ? dict[modal.title] : modal.title);
    var confirmbutton = modal.buttons[0];
    var cancelbutton = modal.buttons[1];
    $('#view-modal-button1').html(dict[confirmbutton.label] ? dict[confirmbutton.label] :
                                 confirmbutton.label);
    $('#view-modal-button1').addClass('btn-' + btnstyle + confirmbutton.attr);
    $('#view-modal-cancel').html(dict[cancelbutton.label] ? dict[cancelbutton.label] :
                                cancelbutton.label);
    $('#view-modal-cancel').addClass('btn-' + btnstyle + cancelbutton.attr);
    item = findTableItem4View(name);
    for (i = 0; i < modal.body.length; i++) {
        modal.body[i].value = item[modal.body[i].id];
    }
    var mStr = horizontalForm(modal.body, table.options, 'view_');
    $('#view-modal-body').html(mStr);
    $('#view-error-message').html('');
    if (datePicker.length > 0)
        datePickerFunc({timepicker:false,
                        format:'Y-m-d'});
    $('#view-modal').modal('show');
    $('#view-modal-button1').off('click');
    $('#view-modal-button1').click(function() {
        var postdata = JSON.parse(confirmbutton.post);
        var dat = {};
        for (var i = 0; i < modal.body.length; i++) {
            dat[modal.body[i].id] =  $('#view_' + modal.body[i].id).val();
            // console.log('id=' + modal.body[i].id + ',val=' + dat[modal.body[i].id]);
        }
        postdata.data = dat;
        if (confirmbutton.url) {
            $.ajax({
                type: "post",
                url: confirmbutton.url,
                data: JSON.stringify(postdata),
                contentType: "application/json",
                dataType: "json",
                success: function(d) {
                    if (d.code == 0) {
                        $('#view-modal').modal('hide');
                        $('#view-modal-button1').removeClass('btn-' + btnstyle + confirmbutton.attr);
                        $('#view-modal-cancel').removeClass('btn-' + btnstyle + cancelbutton.attr);
                        updateTableAjax (table);
                        if ($('#fileexplorer').length)
                            $('#fileexplorer').elfinder('instance').exec('reload');
                    } else {
                        warning('view-error-message', d.message);
                    }
                },
                error: function(msg) {
                    alert("Back end error!");
                }
            });
        } else {
            $('#view-modal').modal('hide');
            $('#view-modal-button1').removeClass('btn-' + btnstyle + confirmbutton.attr);
            $('#view-modal-cancel').removeClass('btn-' + btnstyle + cancelbutton.attr);
        }
    });
}

function findTableItem4View(name)
{
    var key = table.idkey ? table.idkey : 'name';
    for (var i = 0; i < table.data.length; i++) {
        if (table.data[i][key] == name)
            return table.data[i];
    }
    return {};
}

function loadTopAppMenu()
{
    $.ajax({
        type: "post",
        url: "php/appmenu.php",
        data: "{}",
        contentType: "application/json",
        dataType: "json",
        success: function(d) {
            if (d.code == 0) {
                topAppMenu(d.data);
            } else {
                failureHandler(d);
            }
        },
        error: function(msg) {
            alert("Back end error!");
        }
    });
}

function topAppMenu(data)
{
    /* if (data.length == 0) {
        $('#topmenu').html('');
        return;
    } */
    var topMenuStr = '<li class="nav-item d-none d-sm-inline-block">' +
          '<a class="nav-link" href="javascript:terminal();">' +
          '<i class="fas fa-laptop-code"></i> ' + dict.TERMINAL + '</a></li>';
    if (data.length == 0)
        clearInterval(eventInterval);
    data.forEach(function(appcat) {
        topMenuStr += '<li class="nav-item d-none d-sm-inline-block dropdown">' +
            '<a class="nav-link" data-toggle="dropdown" href="#">' +
            appcat.catname + dict.APPLICATIONS +'</a>' +
            '<div class="dropdown-menu">';
        appcat.applications.forEach(function(app) {
            topMenuStr += '<div class="dropdown-item" style="cursor: pointer;"' +
                 ' onclick="openurl(\'php/appform.php?app=' + encodeURI(app.appname) + '\')">' +
                 '<div class="media"><img src="data:' + app.icon + '" height="32"/> ' +
                 '<div class="media-body"><span style="padding-left: 10px;">' +
                 app.appname + '</span></div></div></div>';
        });
        topMenuStr += '</div></li>';
    });
    $('#topmenu').html(topMenuStr);
}

function horizontalForm(form, options = null, idpre = '')
{
    var fStr = '';
    for (var i = 0; i < form.length; i++) {
        var item = form[i];
        iid = idpre + item.id;
        fStr += '<div class="form-group row"><label class="col-sm-3 col-form-label">' +
                (dict[item.label] ? dict[item.label] : item.label) + '</label>' +
                '<div class="col-sm-9">';
        switch (item.type) {
        case 'fixed_text':
        case 'text':
        case 'calendar_date':
            fStr += '<input type="text" class="form-control" id="' + iid +
                    (item.value ? ('" value="' + item.value) : '') + '"' +
                    (item.type == 'fixed_text'?' readonly':'') + '>';
            if (item.type == 'calendar_date')
                datePicker.push(iid);
            break;
        case 'fixed_textarea':
        case 'textarea':
            fStr += '<textarea rows="' + item.rows.toString() +
                    '" class="form-control" type="text" id="' + iid + '"' +
                    (item.type == 'fixed_textarea'?' readonly':'') + '>' +
                    (item.value ? item.value : '') + '</textarea>';
            break;
        case 'select':
            if (options != null && options[item.id] != null)
                item.options = options[item.id];
            fStr += '<select class="form-control" id="' + iid + '">';
            for (j = 0; j < item.options.length; j++) {
                fStr += '<option';
                if (item.options[j] == item.value)
                    fStr += ' selected="selected"';
                fStr += '>' + item.options[j] + '</options>';
            }
            fStr += '</select>';
            break;
        default:
            break;
        }
        fStr += '</div></div>';
    }
    return fStr;
}

var tableLanguage = {"sEmptyTable": "无数据",
                     "sInfo": "显示第 _START_ 至 _END_ 项结果，共 _TOTAL_ 项",
                     "sInfoEmpty": "显示第 0 至 0 项结果，共 0 项",
                     "sInfoFiltered": "(由 _MAX_ 项结果过滤)",
                     "sInfoPostFix": "",
                     "sThousands": ",",
                     "sUrl": "",
                     "sLengthMenu": "显示 _MENU_ 项结果",
                     "sLoadingRecords": "载入中...",
                     "sProcessing": "处理中...",
                     "sSearch": "搜索:",
                     "sZeroRecords": "没有匹配结果",
                     "oPaginate": {
                         "sFirst": "首页",
                         "sPrevious": "上页",
                         "sNext": "下页",
                         "sLast": "末页"
                     },
                     "oAria": {
                         "sSortAscending": ": 以升序排列此列",
                         "sSortDescending": ": 以降序排列此列"
                     }
                    };

function updateTable(table)
{
    var columns = [];
    var key = table.idkey ? table.idkey : 'name';
    if (table.idkey == 'none')
        key = table.tbody[0];
    columns[0] = {"orderable": false,
                  "data": function(row, type, val, meta) {
                      if (table.noselect)
                          return '';
                      return '<input class="checkbox" type="checkbox" value="' +
                              encodeURI(row[key]) +
                              '" name="individual">';
                      }
                  };
    if (table.idkey == 'none')
        columns[1] = {"data": table.tbody[0]};
    else
        columns[1] = {"data": function(row, type, val, meta) {
                      if (row[key].includes(' php/')) {
                          v = row[key].split(' ');
                          name = v[0];
                          url = v[1];
                          return '<a href="javascript:openurl(\'' + url +
                              '\');">' + name + '</a>';
                      } else if (row[key].includes('<a href')) {
                          return row[key];
                      } else {
                          return '<a id="' + row[key] +
                            '" href="javascript:viewModal(\'' + row[key] + '\');">' +
                            row[key] + '</a>';
                      }
                      }
                     };

    for (i = 1; i < table.tbody.length && i < table.thead.length; i++) {
        if (table.tbody[i] == 'icon') {
            columns[i + 1 ] = {"data": function(row, type, val, meta) {
                                return '<img src="data:' + row.icon +
                                       '" height="32" />';
                              }};
        } else
            columns[i + 1] = {"data": table.tbody[i]};
    }
    $(function() {
        $('#' + table.id).DataTable({
            "data": table.data,
            "destroy": true,
            "columns": columns,
            "paging": (table.nopaging ? false : true),
            "ordering": true,
            "info": true,
            "searching": true,
            "pagingType": "simple_numbers",
            "order": (table.order ? JSON.parse(table.order) : []),
            "lengthMenu": (table.lengthMenu?
                     JSON.parse(table.lengthMenu):[10, 30, 100]),
            "autoWidth": false,
            "language": tableLanguage
        });
        $('#all').change(function() {
            if (this.checked)
                $('.checkbox').each(function() {
                    this.checked=true;
                });
            else
                $('.checkbox').each(function() {
                    this.checked=false;
                });
        });

        $('.checkbox').click(function() {
            if ($(this).is(":checked")) {
                var isAllChecked = 0;
                $('.checkbox').each(function() {
                    if (!this.checked)
                        isAllChecked = 1;
                });
                if (isAllChecked == 0) {
                    $('#individual').prop("checked", true);
                }
            } else {
                $('#individual').prop("checked", false);
            }
        });
    });
}

function updateTableAjax(table)
{
    var urlStr = table.update.url;
    var dat = JSON.parse(table.update.post);
    if (urlStr == 'php/jobs.php') {
        dat.filter = jobsfilter;
    }
    $.ajax({
        type: "post",
        url: urlStr,
        data: JSON.stringify(dat),
        contentType: "application/json",
        dataType: "json",
        success: function(d) {
            if (d.code == 0) {
                if (d.data != null) {
                    table.data = d.data.table;
                    if (d.data.options != null)
                        table.options = d.data.options;
                }
                updateTable(table);
            } else {
                failureHandler(d);
            }
        },
        error: function(msg) {
            alert("Back end error!");
        }
    });
}

function fileExplorer(file)
{
    $(function() {
        $('#fileexplorer').elfinder({
            url: '/php/connector.minimal.php?path=' + file.path,
            uploadMaxChunkSize: 10737418240,
            height: file.height,
            resizable: false,
            defaultView: 'list',
            lang: 'zh_CN',
            ui: ['toolbar', 'tree', 'path', 'stat'],
            uiOptions: {
              toolbar:  [['back','forward','up'],
                ['mkdir','mkfile','upload'],
                ['downoad'],
                ['undo','redo'],
                ['copy', 'cut', 'paste', 'rm', 'hide'],
                ['duplicate', 'rename', 'edit', 'chmod'],
                ['selectall', 'selectnone', 'selectinvert'],
                ['quicklook', 'info'],
                ['extract', 'archive'],
                ['search'],
                ['view', 'sort']
              ]},
            contextmenu: {
                files: ['quicklook','download','edit','|','mkdirin','|',
                   'copy','cut','duplicate','|','rm','rename','|',
                   'archive','extract','|','selectinvert','|','info']
            },
        });
    });  
}

function initialize()
{
    clearInterval(tableInterval);
    clearInterval(descriptionInterval);
    clearInterval(chartInterval);
    description = {};
    table = {};
    chartData = [];
    datePicker = [];
    viewModalData = {};
    jobsfilter = '';
}

function warning(domid, message)
{
   var mess = dict[message] ? dict[message] : message;
   $('#' + domid).html(mess);
   setTimeout(function(){$('#' + domid).html('');},3000);
}

function simpleTable(header, to_delete)
{
    var hStr = '<table class="table table-sm"><thead><tr>';
    var i;
    for (i = 0; i < header.length; i++)
        hStr += '<th>' + header[i] + '</td>';
    hStr += '</tr></thead><tbody>';
    for (i = 0; i < to_delete.length; i++) {
         hStr += '<tr>';
         to_delete[i].forEach(function(item, index) {
             if (typeof item === 'string' && item.includes(' php/')) {
                 a = item.split(' ');
                 hStr += '<td>' + a[0] + '</td>';
             } else
                 hStr += '<td>' + item + '</td>';
         });
         hStr += '</tr>';
    }
    hStr += '</tbody></table>';
    return hStr;
}

var fileInputId;
function formHTML(form)
{
    var hStr = '';
    form.forEach(function(section){
        hStr += '<h5>' +
            (dict[section.title] ? dict[section.title] : section.title) +
             '</h5>';
        section.items.forEach(function(item) {
            if (section.format == 'normal')
                hStr += '<div class="form-group"><label>' +
                        (dict[item.label] ? dict[item.label] : item.label) + '</label>';
            else
                hStr += '<div class="form-group row"><label class="col-sm-4 col-form-label">' +
                    (dict[item.label] ? dict[item.label] : item.label) + '</label>' +
                    '<div class="col-sm-8">';
            switch (item.type) {
            case 'fixed_text':
                hStr += '<input type="text" class="form-control" id="' + item.id +
                      '" value="' + item.value + '" readonly>';
                break;
            case 'text':
            case 'calendar':
                hStr += '<input type="text" class="form-control" id="' + item.id +
                      (item.value ? ('" value="' + item.value) : '') + '">';
                if (item.type == 'calendar')
                     datePicker.push(item.id);
                break;
            case 'textarea':
                hStr += '<textarea rows="' + item.rows.toString() +
                      '" class="form-control" type="text" id="' + item.id + '">' +
                      (item.value ? item.value : '') + '</textarea>';
                break;
            case 'select':
                hStr += '<select class="form-control" id="' + item.id + '">';
                for (j = 0; j < item.options.length; j++) {
                    hStr += '<option';
                    if (item.options[j] == item.value)
                        hStr += ' selected="selected"';
                    hStr += '>' +
                       (dict[item.options[j]]?dict[item.options[j]]:item.options[j]) +
                        '</options>';
                }
                hStr += '</select>';
                break;
            case 'file':
            case 'dir':
                hStr += '<input class="btn btn-secondary btn-block" id="' +
                        item.id + '" value="' + item.value +
                        '" onclick="fileDirSelect(\'' + item.value + '\',\'' + 
                        item.type + '\',\'' + item.id + '\')">';
                $('#file-modal-confirm').html(dict.CONFIRM);
                $('#file-modal-reset').html(dict.RESET);
                break;
            default:
                break;
            }
            if (!section.format)
                hStr += '</div>';
            hStr += '</div>';
        })
    })
    localStorage.setItem('form', JSON.stringify(form));
    return hStr;
}

function descriptionHTML(des)
{
    var hStr = '<div class="card-body"><dl class="row" id="' + des.id + '">';
    for (var key in des.rows) {
        hStr += '<dt class="col-sm-4">' + (dict[des.rows[key].title] ?
                   dict[des.rows[key].title] : des.rows[key].title) + '</dt>' +
                '<dd class="col-sm-8" id="' + key + '">' +
                 des.rows[key].value + '</dt>';
    }
    hStr += '</dl></div>';
    return hStr;
}

function updateDescriptionAjax()
{
    var reqdata = {action: "update", id: description.title};
    $.ajax({
        type: "post",
        url: description.update.url,
        data: JSON.stringify(reqdata),
        contentType: "application/json",
        dataType: "json",
        success: function(d) {
            if (d.code == 0) {
                if (d.data.description != null) {
                    for (key in d.data.description)
                        $('#' + key).html(d.data.description[key].value);
                }
                if (d.data.output != null) {
                    $('#joboutput').text(d.data.output);
                }
                if (d.data.guiurl != '' && d.data.description.status.value.includes(dict.RUN))
                    if (d.data.guiurl.length == 32 && !d.data.guiurl.includes('/')) {
                        $('#guiurl').html('<a href="javascript:openvnc(\'' + d.data.description.jobid.value +
                                           '\',\'' + d.data.guiurl + '\');">' +
                             '<button type="button" class="btn btn-block btn-success">' +
                             dict.GUI + '</button>' +
                             '</a>');
                    } else {
                        $('#guiurl').html('<a href="' + d.data.guiurl + '" target="_blank">' +
                             '<button type="button" class="btn btn-block btn-success">' +
                             dict.GUI + '</button>' +
                             '</a>');
                    }
                else
                    $('#guiurl').html('<h4 class="card-title">' + dict.JOB_OUTPUT +
                         '</h4>');
            } else {
                failureHandler(d);
            }
        },
        error: function(msg) {
            alert("Back end error!");
        }
    });
}

function getFormData()
{
    form = JSON.parse(localStorage.getItem('form'));
    for (i = 0; i < form.length; i++) {/* iterate sections */
        for (j = 0; j < form[i].items.length; j++)
            form[i].items[j].value = $('#' + form[i].items[j].id).val();
    }
    return form;
}

function getInstanceFormData()
{
    oform = getFormData();
    var form = {};
    form.appName = oform[0].items[2].value;
    form.cluster_params = {project: oform[0].items[0].value,
                           instance: oform[0].items[1].value};
    oform[1].items.forEach(function(item) {
        form.cluster_params[item.id] = item.value;
    });
    form.uiParamList = {};
    for (i = 2; i < oform.length; i++) {
        oform[i].items.forEach(function(item) {
            form.uiParamList[item.id] = item.value;
        });
    }
    return form;
}

function fileDirSelect(param, type, domid = null)
{
    $('#file-modal-body').html('');
    $('#file-modal-title').html(param);
    $('#file-modal').modal('show');
    $.ajax({
        type: "post",
        url: "php/filelist.php",
        data: JSON.stringify({path: param, type: type}),
        contentType: "application/json",
        dataType: "json",
        success: function(d) {
            if (d.code == 0) {
                updateFileSelectModal(d.data, type);
            } else {
                warning('file-error-message', d.message);
            }
        },
        error: function(msg) {
            alert("Back end error!");
        }
    });
    if (domid != null) {
        fileInputId = domid;
    }
}

function updateFileSelectModal(data, type)
{
    $('#file-modal-title').html(data.path);
    var tStr = '<form role="form"><div class="form-group">' +
               '<table id="filelist" class="table table-sm table-striped">' +
               '<thead><tr><th>' + dict.SELECT + '</th><th>' +
                dict.NAME + '</th><th>' + dict.LAST_MODIFIED +
                '</th><th>' + dict.SIZE + '</th></tr></thead><tbody>';
    tStr += '</tbody></table></div></form>';
    var columns = [];
    columns[0] = {"data": function(row, t, val, meta) {
                   var hStr = '';
                   if ((type == 'dir' && row.type == 'd') ||
                       (type == 'file' && row.type == 'f') || type == 'filedir') {
                       hStr = '<div class="form-check"><input class="form-check-input"' +
                          ' type="radio" name="select" value="' + row.path + '"';
                       if (data.select == row.name)
                           hStr += ' checked';
                       hStr += '></div>';
                   }
                   return hStr;
                  }};
    columns[1] = {"data": function(row, t, val, meta) {
                   if (row.type == 'd')
                      return '<a href="javascript:fileDirSelect(\'' + row.path +
                             '\',\'' + type + '\');">' + row.name + '</a>';
                   else
                      return row.name;
                  }};
    columns[2] = {"data": function(row, t, val, meta) {
                   return row.time;
                 }};
    columns[3] = {"data": function(row, t, val, meta) {
                   return row.size;
                 }};
           
    $('#file-modal-body').html(tStr);
    $(function() {
        $('#filelist').DataTable({
            "data": data.files,
            "columns": columns,
            "destroy": true,
            "paging": true,
            "ordering": true,
            "info": true,
            "searching": true,
            "order": [[1, "asc"]],
            "lengthMenu": [15, 30, 100],
            "autoWidth": false,
            "language": tableLanguage
        });
        $('#file-modal-confirm').click(function() {
            var radioValue = $("input[name='select']:checked").val();
            $('#file-modal').modal('hide');
            $('#' + fileInputId).val(radioValue);
        });
    });     
}

function jobsubmit()
{
    var form = getInstanceFormData();
    $.ajax({
        type: "post",
        url: 'php/submit.php',
        data: JSON.stringify(form),
        contentType: "application/json",
        dataType: "json",
        success: function(d) {
            if (d.code == 0) {
                jobid = d.data.jobid;
                openurl('php/job.php?jobid=' + jobid);
            } else {
                failureHandler(d);
            }
        },
        error: function(msg) {
            alert("Back end error!");
        }
    });
}

function saveInstance()
{
    var form = getInstanceFormData();
    $('#gen-modal-title').html(dict.SAVE);
    $('#gen-modal-confirm').html(dict.SAVE);
    clearButtonClass('gen-modal-confirm', 'btn-');
    $('#gen-modal-confirm').addClass('btn-success');
    $('#gen-modal-cancel').html(dict.CANCEL);
    clearButtonClass('gen-modal-cancel', 'btn-');
    $('#gen-modal-cancel').addClass('btn-secondary');
    body = horizontalForm([{label: dict.INSTANCE_NAME,
                            id: 'ins_name',
                            type: 'text',
                            value: form.cluster_params.instance}]);
    
    $('#gen-modal-body').html(body);
    $('#gen-modal').modal('show');
    $('#gen-modal-confirm').off('click');
    $('#gen-modal-confirm').click(function() {
        $.ajax({
            data: JSON.stringify({action: 'save', data: form}),
            type: "post",
            url: 'php/appins.php',
            contentType: "application/json",
            dataType: "json",
            success: function(d) {
                if (d.code == 0) {
                    $('#gen-modal').modal('hide');
                    $('#gen-modal-confirm').removeClass('btn-success');
                    $('#gen-modal-cancel').removeClass('btn-secondary');
                    updateTableAjax(table)
                } else {
                    warning('gen-error-message', d.message);
                }
            },
            error: function(msg) {
                alert("Back end error!");
            }
        });
    });
}

function errorModal(msg)
{
    $('#err-modal-title').html(dict.ERROR);
    $('#err-modal-confirm').html('OK');
    $('#err-modal-body').html(dict[msg]?dict[msg]:msg);
    $('#err-modal-confirm').off('click');
    $('#err-modal-confirm').click(function() {
        $('#err-modal').modal('hide');
    });
    $('#err-modal').modal('show');
}

function actionJob(action)
{
    var jobid = description.title;
    switch (action) {
    case 'kill':
        $('#gen-modal-title').html(dict.KILL);
        $('#gen-modal-confirm').html(dict.KILL);
        clearButtonClass('gen-modal-confirm', 'btn-');
        $('#gen-modal-confirm').addClass('btn-danger');
        $('#gen-modal-cancel').html(dict.CANCEL);
        clearButtonClass('gen-modal-cancel', 'btn-');
        $('#gen-modal-cancel').addClass('btn-success');
        $('#gen-modal-body').html(dict.KILL + ' ' + dict.JOB + ': ' + jobid + '?');
        $('#gen-modal').modal('show');
        $('#gen-modal-confirm').off('click');
        $('#gen-modal-confirm').click(function() {
          $.ajax({
            data: JSON.stringify({action: 'kill', data: [jobid]}),
            type: "post",
            url: 'php/jobs.php',
            contentType: "application/json",
            dataType: "json",
            success: function(d) {
                if (d.code == 0) {
                    $('#gen-modal').modal('hide');
                } else {
                    warning('gen-error-message', d.message);
                }
            },
            error: function(msg) {
                alert("Back end error!");
            }
          });
        });
        break;
    default:
        $.ajax({
            data: JSON.stringify({action: action, data: [jobid]}),
            type: "post",
            url: 'php/jobs.php',
            contentType: "application/json",
            dataType: "json",
            success: function(d) {
                if (d.code == 0) {
                } else {
                    warning('gen-error-message', d.message);
                }
            },
            error: function(msg) {
                alert("Back end error!");
            }
        });
    }
}

function updateaccount()
{
    form = getFormData();
    var udata = {};
    form[0].items.forEach(function(item) {
        udata[item.id] = item.value;
    })
    $.ajax({
        data: JSON.stringify({action: 'modify_user', data: udata}),
        type: "post",
        url: 'php/usermgmt.php',
        contentType: "application/json",
        dataType: "json",
        success: function(d) {
            if (d.code == 0) {
                openurl('php/account.php');
            } else {
                failureHandler(d);
            }
        },
        error: function(msg) {
            alert("Back end error!");
        }
    }); 
}

var donutOptions = {
    series: {
        pie: {
            innerRadius: 0.3,
            show: true,
            /* stroke: {
               color: "#384246",
               width: 2
            },*/
            label: {
                show: true,
                radius: 1,
                formatter: function(label, series) {
                    var percent=Math.round(series.percent);
                    var number = series.data[0][1];
                    return ('<center><font color="black">' +
                           number +
                           '</font></center>');
                }
            }
        }
    },
    grid: {
        hoverable: true
    },
    tooltip: {
        show: true,
        content: "%s:%p.2%",
        shifts: {
            x: 20,
            y: 0
        },
        defaultTheme: false
    },
    legend: {
        show: true,
        position: 'se',
        backgroundOpacity: 0
    }
};

var colorsTemp = {primary:   '#007bff',
                  secondary: '#6c757d',
                  info:      '#17a2b8',
                  success:   '#28a745',
                  warning:   '#ffc107',
                  danger:    '#dc3545'};

var barOptions = {
    series: {
        stack: true,
        bars: {
             show: true,
             barWidth: 0.5,
             horizontal: true,
             align: "center",
             fillColor: {colors: [{opacity: 0.8}, {opacity: 0.8},
                                  {opacity: 0.8}]}
        }
    },
    grid: {
        margin: 5,
        hoverable: true
    },
    yaxis: {},
    xaxis: {
        min:0,
        autoscaleMargin: .02
    },
    tooltip: {
        show: true,
        content: "%s:%y %x",
        shifts: {
            x: 20,
            y: 0
        },
        defaultTheme: false
    },
    legend: {
        position: 'se',
        backgroundOpacity: 0,
        show: true
    }
};

function donutChart(cData)
{
    var colors = [];
    var myoptions = {}, mydata = [];
    myoptions = JSON.parse(JSON.stringify(donutOptions));
    
    cData.data.forEach(function(item) {
        colors.push(colorsTemp[item.color]);
        mydata.push(
                  {label: (dict[item.label]?dict[item.label]:item.label),
                   data: item.data});
    });
    myoptions.colors = colors;
    chartData.push({id: cData.id, data: mydata, options: myoptions});
}

function horBarchart(cData)
{
    var colors = [];
    var myoptions = {}, mydata = [];
    myoptions = JSON.parse(JSON.stringify(barOptions));
    i = 0;
    cData.data.forEach(function(item) {
        colors.push(colorsTemp[item.color]);
        mydata.push({label: (dict[item.label]?dict[item.label]:item.label),
                     data: cData.xdata[i]});
        i++;
    });
    myoptions.colors = colors;
    myoptions.yaxis.ticks = cData.ydata;
    chartData.push({id: cData.id, data: mydata, options: myoptions});
}

function drawCharts()
{
    $(function() {
        chartData.forEach(function(item) {
            $.plot($('#' + item.id), item.data, item.options);
        });
    });
}

function datePickerFunc(format = {})
{
    datePicker.forEach(function(docid) {
        $('#' + docid).datetimepicker(format);
    });
}

function hostChart(cData)
{
    var hStr = '<div class="row">';
    cData.data.forEach (function(item) {
        var percent = 0;
        var barcolor = 'success';
        if (item.mxj != '-')
           percent = item.run * 100 / item.mxj;
        switch (item.status) {
        case 'Ok':
            barcolor = 'success';
            break;
        case 'Unavail':
        case 'Unreach':
        case 'Closed-LS':
            barcolor = 'danger';
            percent = 100;
            break;
        case 'Closed-Admin':
            barcolor = 'primary';
            percent = 100;
            break;
        case 'Closed-Excl':
        case 'Closed-Full':
            barcolor = 'info';
            percent = 100;
            break;
        case 'Closed-Busy':
            barcolor = 'warning';
            percent = 100;
            break;
        default:
            barcolor = 'primary';
            percent = 100;
            break;
        }

        tips = dict.STATUS + ': ' +
               (dict[item.status]?dict[item.status]:item.status) + '\nCPU: ' +
               item.ut + '\nMEM: ' + item.mem + '\nNET: ' + item.io +
               '\n' + dict.JOBS + ': ' + item.run;
        hStr += '<div class="col-lg-1" title="' + tips + '">' +
                '<div class="progress-group">' + 
                '<span class="progress-text">' + item.host + '</span>' +
                '<div class="progress progress-sm">' +
                '<div class="progress-bar bg-' + 
                barcolor + '" style="width: '+ percent + '%"></div></div>' + 
                '</div>' +
                '</div>';
    });
    hStr += '</div>';
    return hStr;
}

function runReport()
{
    parameters = getFormData();
    var dd ={};
    parameters[0].items.forEach(function(item) {
        dd[item.id] = item.value;
    });
    $.ajax({
        type: "post",
        url: 'php/reports.php',
        data: JSON.stringify({action: 'run', data: dd}),
        contentType: "application/json",
        dataType: "json",
        success: function(d) {
            if (d.code == 0 && d.data) {
                $('#report-image').html(d.data);
            } else {
                failureHandler(d);
            }
        },
        error: function(msg) {
            alert("Back end error!");
        }
    });
}

function jobhist(jobid)
{
    $.ajax({
        type: "post",
        url: 'php/job.php',
        data: JSON.stringify({action: 'hist', id: jobid}),
        contentType: "application/json",
        dataType: "json",
        success: function(d) {
            if (d.code == 0 && d.data) {
                showJobHist(d.data.description);
            } else {
                failureHandler(d);
            }
        },
        error: function(msg) {
            alert("Back end error!");
        }
    });
}

function showJobHist(jobdata)
{
    $('#gen-modal-title').html(dict.JOB + ': ' + jobdata.rows.jobid.value);
    $('#gen-modal-confirm').html(dict.OK);
    clearButtonClass('gen-modal-confirm', 'btn-');
    $('#gen-modal-confirm').addClass('btn-success');
    $('#gen-modal-cancel').hide();
    $('#gen-modal-body').html(descriptionHTML(jobdata));
    $('#gen-modal').modal('show');
    $('#gen-modal-confirm').off('click');
    $('#gen-modal-confirm').click(function() {
        $('#gen-modal').modal('hide');
        $('#gen-modal-cancel').show();
    });
}

function hostdetail(host)
{
    $.ajax({
        type: "post",
        url: 'php/hosts.php',
        data: JSON.stringify({action: 'detail', name: host}),
        contentType: "application/json",
        dataType: "json",
        success: function(d) {
            if (d.code == 0 && d.data) {
                showHostDetail(d.data.description);
            } else {
                failureHandler(d);
            }
        },
        error: function(msg) {
            alert("Back end error!");
        }
    });
}

function showHostDetail(host)
{
    $('#gen-modal-title').html(dict.HOST + ': ' + host.rows.name.value);
    $('#gen-modal-confirm').html(dict.OK);
    clearButtonClass('gen-modal-confirm', 'btn-');
    $('#gen-modal-confirm').addClass('btn-success');
    $('#gen-modal-cancel').hide();
    $('#gen-modal-body').html(descriptionHTML(host));
    $('#gen-modal').modal('show');
    $('#gen-modal-confirm').off('click');
    $('#gen-modal-confirm').click(function() {
        $('#gen-modal').modal('hide');
        $('#gen-modal-cancel').show();
    });
}

function failureHandler(d)
{
    if (d.code == 404) {
        alert('404: ' + d.message);
        window.location.href = "index.html"; 
    } else
        errorModal(d.message);
}

function terminal()
{
    initialize();
    $('#main').html('');
    $('#page-title').html(dict.TERMINAL);
    $.ajax({
        type: "post",
        url: 'php/terminal.php',
        data: JSON.stringify({}),
        contentType: "application/json",
        dataType: "json",
        success: function(d) {
            if (d.code == 0) {
                $('#main').html('<iframe src="' + d.url + '" frameborder="0" ' +
                  'allowfullscreen style="width:100%;height:800px;"></iframe>');
            } else {
                failureHandler(d);
            }
        },
        error: function(msg) {
            alert("Back end error!");
        }
    });
}

function isPermChecked(text)
{
    items = text.split(' ');
    idstr = items[3].split('"'); 
    id = idstr[1];
    checked = $('#' + id).is(':checked');
    return checked;
}

function savepermissions()
{
    var retd = [];
    table.data.forEach(function(row) {
        var permission = [];
        if (row.scope.search(dict.APP_NAME) == 0)
            idvalue = 'app' + row.scope;
        else
            idvalue = 'side_menu' + row.scope;
        if (isPermChecked(row.admin))
            permission.push(dict.ADMIN);
        if (isPermChecked(row.auditor))
            permission.push(dict.AUDITOR);
        if (isPermChecked(row.confidential_admin))
            permission.push(dict.CONFIDENTIAL_ADMIN);
        if (isPermChecked(row.group_admin))
            permission.push(dict.GROUP_ADMIN);
        if (isPermChecked(row.user))
            permission.push(dict.USER);
        retd.push({idvalue: idvalue,
                   roles_permitted: permission.join(' ')});
    }); 
    $.ajax({
        type: "post",
        url: 'php/roles.php',
        data: JSON.stringify({action:'save', data: retd}),
        contentType: "application/json",
        dataType: "json",
        success: function(d) {
            if (d.code == 0) {
                openurl('php/roles.php');
            } else {
                failureHandler(d);
            }
        },
        error: function(msg) {
            alert("Back end error!");
        }
    });
}

function forum()
{
    initialize();
    $.ajax({
        type: "post",
        url: '../forum/?user-login.htm',
        data: 'email=admin&password=' + $.md5('admin'),
        contentType: "application/x-www-form-urlencoded",
        success: function(d) {
            $('#main').html('<iframe src="../forum/" frameboarder="0" ' +
                    'allowfullscreen style="width:100%;height:800px;"></iframe>');
            $('#page-title').html(dict.FORUM);
        },
        error: function(msg) {
            alert('error');
        }
    });
}

function man()
{
}

function searchjobhist()
{
    parameters = getFormData();
    var dd ={};
    parameters[0].items.forEach(function(item) {
        dd[item.id] = item.value;
    });
    $.ajax({
        type: "post",
        url: 'php/jobhist.php',
        data: JSON.stringify({action: 'search', data: dd}),
        contentType: "application/json",
        dataType: "json",
        success: function(d) {
            if (d.code == 0) {
                table.data = d.data.table;
                updateTable(table);
            } else {
                failureHandler(d);
            }
        },
        error: function(msg) {
            alert("Back end error!");
        }
    });
}

function userInfo(username)
{
    $.ajax({
        type: "post",
        url: 'php/chargeorders.php',
        data: JSON.stringify({action: 'userinfo', username: username}),
        contentType: "application/json",
        dataType: "json",
        success: function(d) {
            if (d.code == 0 && d.data) {
                user = d.data;
                $('#gen-modal-title').html(dict.USERNAME + ': ' + user.description.id);
                $('#gen-modal-confirm').html(dict.OK);
                clearButtonClass('gen-modal-confirm', 'btn-');
                $('#gen-modal-confirm').addClass('btn-success');
                $('#gen-modal-cancel').hide();
                $('#gen-modal-body').html(descriptionHTML(user.description));
                $('#gen-modal').modal('show');
                $('#gen-modal-confirm').off('click');
                $('#gen-modal-confirm').click(function() {
                    $('#gen-modal').modal('hide');
                    $('#gen-modal-cancel').show();
                });
            } else {
                failureHandler(d.message);
            }
        },
        error: function(msg) {
            alert("Back end error!");
        }
    });
}

function openvnc(j, p)
{
    var url = "/php/vnc.php?jobid=" + j + "&pw=" + p;
    $.ajax ({
        type: 'GET',
        url: url,
        success: function (d) {
            window.open(window.location.origin + d, "_blank");
        }
    });
}

function new_file_share()
{
    var modalnew = {modal: 'add-modal',
                 title: 'NEW_SHARE',
                 body: [ {label: 'FILE',
                          type: 'fixed_text',
                          id: 'share_path'},
                         {label: 'DESCRIPTION',
                          type: 'textarea',
                          id: 'description',
                          rows: 3},
                         {label: 'SHAREWITH',
                          type: 'textarea',
                          id: 'targets',
                          rows: 5}],
                 buttons: [{label: 'SHARE',
                            attr: 'primary',
                            url: 'php/dshare.php',
                            post: '{"action":"new_share"}' },
                           {label: 'RESET',
                            attr: 'warning'}]};
    addModal(modalnew);
    $('#file-modal-confirm').html(dict.CONFIRM);
    $('#file-modal-reset').html(dict.RESET);
    fileDirSelect('选择文件', 'filedir', 'share_path');
    $('#add-modal').modal('show');
}

function extract_file(shareid)
{
    var modalext = {modal: 'add-modal',
                    title: 'EXTRACT',
                    body: [{label: 'ID',
                            type: 'fixed_text',
                            id: 'id',
                            value: shareid},
                           {label: '到目录',
                            type: 'fixed_text',
                            id: 'todir'}],
                    buttons: [{label: 'EXTRACT',
                            attr: 'primary',
                            url: 'php/dshare.php',
                            post: '{"action":"extract"}' },
                           {label: 'RESET',
                            attr: 'warning'}]};
    addModal(modalext);
    $('#file-modal-confirm').html(dict.CONFIRM);
    $('#file-modal-reset').html(dict.RESET);
    fileDirSelect('选择文件', 'dir', 'todir');
    $('#add-modal').modal('show');
}

function new_file_share(jobid, share)
{
    var modals = {modal: 'add-modal',
                  title: 'APPLICATION_SHARES',
                  body: [{label: 'JOB_ID',
                          id: 'jobid',
                          type: 'fixed_text',
                          value: jobid},
                         {label: 'ID',
                          type: 'fixed_text',
                          id: 'share',
                          value: share},
                         {label: 'DESCRIPTION',
                          type: 'textarea',
                          id: 'description',
                          rows: 3},
                         {label: 'SHAREWITH',
                          type: 'textarea',
                          id: 'targets',
                          rows: 5}],
                  buttons: [{label: 'SHARE',
                            attr: 'primary',
                            url: 'php/ashare.php',
                            post: '{"action":"new_share"}' },
                           {label: 'RESET',
                            attr: 'warning'}]};
    addModal(modals);
    $('#file-modal-confirm').html(dict.CONFIRM);
    $('#file-modal-reset').html(dict.RESET);
    $('#add-modal').modal('show');
}

function access_app_share(shareid)
{
    $.ajax({
        type: "post",
        url: 'php/ashare.php',
        data: JSON.stringify({action: 'getshare', data: shareid}),
        contentType: "application/json",
        async: false,
        dataType: "json",
        success: function(d) {
            if (d.code == 0) {
                open_shared_vnc(d.data); 
            } else {
                failureHandler(d);
            }
        },
        error: function(msg) {
            alert("Back end error!");
        }
    });

}

function open_shared_vnc(data)
{
    var url = "php/vnc.php?jobid=" + data.jobid + "&pw=" + data.pword +
               "&host=" + data.host + "&sid=" + data.sid;
    $.ajax ({
        type: 'GET',
        url: url,
        success: function (d) {
            window.open(window.location.origin + d, "_blank");
        }
    });
}

function setDiscount(user)
{
    /* get user discount */   
    $.ajax({
        type: "get",
        url: 'php/getDiscount.php?user=' + user,
        contentType: "application/json",
        dataType: "json",
        success: function(d) {
            if (d.code == 0) {
                editDiscount(d.data);
            } else {
                failureHandler(d);
            }
        },
        error: function(msg) {
            alert("Back end error!");
        }
    });
} 

function editDiscount(d)
{
    $('#gen-modal-title').html(dict.DISCOUNT + ': ' + d.user);
    $('#gen-modal-confirm').html(dict.CONFIRM);
    clearButtonClass('gen-modal-confirm', 'btn-');
    $('#gen-modal-confirm').addClass('btn-success');
    $('#gen-modal-body').html(renderDiscount(d));
    $('#gen-modal').modal('show');
    $('#gen-modal-confirm').off('click');
    $('#gen-modal-confirm').click(function() {
        d.cpu = parseFloat($('#dis_cpu').val());
        if (d.cpu < 0) d.cpu = 0;
        d.mem = parseFloat($('#dis_mem').val());
        if (d.mem < 0) d.mem = 0;
        d.gpu = parseFloat($('#dis_gpu').val());
        if (d.gpu < 0) d.gpu = 0;
        for (i = 0; i < d.apps.length; i++) {
            d.apps[i].rate = parseFloat($('#dis_' + d.apps[i].app.replace('+', '_')).val());
            if (d.apps[i].rate < 0) d.apps[i].rate = 0;
        }
        $.ajax({
            type: "post",
            url: 'php/setDiscount.php',
            data: JSON.stringify(d),
            contentType: "application/json",
            dataType: "json",
            success: function(r) {
                if (r.code == 0)
                    $('#gen-modal').modal('hide');
                else
                    failureHandler(r);
            },
            error: function(msg) {
                alert("Back end error!");
            }
        });
    });
}

function renderDiscount(d)
{
    labelhtml = '<div class="form-group row"><label class="col-sm-6 col-form-label">'
    inputhtml = '<div class="col-sm-6"><input type="text" class=form-control" ';
    st = '<div style="height:600px;overflow:auto;overflow-x:hidden">' +
         labelhtml + 'CPU</label>' +
         inputhtml + ' id="dis_cpu" value="' + d.cpu + '"></div></div>' +
         labelhtml + '内存</label>' +
         inputhtml + ' id="dis_mem" value="' + d.mem + '"></div></div>' +
         labelhtml + 'GPU</label>' +
         inputhtml + ' id="dis_gpu" value="' + d.gpu + '"></div></div>';
    for (i = 0; i < d.apps.length; i++)
         st += labelhtml + d.apps[i].app + '</label>' +
               inputhtml + ' id="dis_' + d.apps[i].app.replace('+', '_') + '" value="' +
                       d.apps[i].rate + '"></div></div>';

    st += '</div>';
    return st;
}

function messageModal(message, title)
{
    $('#gen-modal-title').html(title);
    $('#gen-modal-confirm').html(dict.OK);
    clearButtonClass('gen-modal-confirm', 'btn-');
    $('#gen-modal-confirm').addClass('btn-primary');
    $('#gen-modal-body').html(message);
    $('#gen-modal').modal('show');
    $('#gen-modal-confirm').off('click');
    $('#gen-modal-confirm').click(function() {
        $('#gen-modal').modal('hide');
    });
}

function apply_price()
{
    $.ajax({
        type: "get",
        url: 'php/applyPrice.php',
        contentType: "application/json",
        dataType: "json",
        success: function(r) {
            if (r.code == 0)
                messageModal('更新的价格和用户折扣开始生效<p>' + r.data, '成功'); 
            else
                failureHandler(r);
        },
        error: function(msg) {
            alert("Back end error!");
        }
    });
}
