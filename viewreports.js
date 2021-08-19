// looks at the query string, turns into array
function getUrlVars() {
    var vars = [], hash;
    var hashes = location.search.substring(1).split('&');
    for(var i=0; i < hashes.length; i++) {
	hash = hashes[i].split('=');
	vars.push(hash[0]);
	vars[hash[0]] = hash[1];
    }
    return vars;
}

// script for loading the report images
$(function() { 
    var data = null;
    if(window.location.href.indexOf('viewreports.php') == -1) {		// means we're on reports.php, not viewreports.php
	var loading = $('#loading-image').hide();			// initially hide the loading gif, in case it takes the page some time to load

	if(getUrlVars()['new'] == 1) {
	    loading.html('<img src="img/loading.gif" style="padding:25px 50px 50px">');
	    $(document).ajaxStart(function() {
		loading.show();						// show it once the AJAX request is sent
	    }).ajaxStop(function() {
		loading.hide();						// hide it again once we get a response
	    });

	    data = 'refresh';						// through the AJAX request tells the PHP code we just refreshed and need to look for new images
	}
    }
    $.ajax({
	type: "POST",
	url: "reportimages.php",
	data: {"data": data},
	datatype: "json",
	success: function(data) {
	    if(data.length > 0) {
		$.each(JSON.parse(data), function(i, filename) {
		    $('#report-images').append('<img src="' + filename + '" width=780px>');
		});
	    }else {}							// if images were not found, do something
	}
    });
});
