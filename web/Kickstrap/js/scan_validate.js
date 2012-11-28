var timer;
var redirect_url = 'scan_results.php'; // redirect to this page after scanning is completed

/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$(document).ready(function() {
    // direct input file
    $('#btn_scan-direct').click(function() {
        initScanning(null, editor.getValue());
        return false;
    });
    
    // File on server getting checked
    $('#btn_scan-server').click(function() {
        initScanning($('#server_file').val(), null);
        return false;
    });
});

/**
 * 1. INIT Scanning
 **/
function initScanning(file, code) {
    var data = {};
    if(file) {
        data["server_file"] = file;
    }
    if(code) {
        data["input_code"] = code;
    }
    
    $.post('php/scan_validate.php', data, startScanning);
}

/**
 * 2. START Scanning
 **/
function startScanning(data) {
    try {
        data = JSON.parse(data);
    }
    catch(Exception) {
        alert("Invalid argument for Json: \n"+data);
        return false;
    }
    
    status = data[0];
    info = data[1];
    
    if(status == "200") {
        // start scanning
        $.post('php/scan_start.php?file='+info, data);
        
        // start grabbing information about scanning process
        $(document).trigger("scanning-in-progress");
    }
    else if(status == "404"){
        alert("Error: The given file does not exist on the server");
    }
    else if(status == "0"){
        alert("Error: The given file is empty");
    }
    else {
        alert("Error: An error occured: "+info);
        alert(data);
    }
    
}




/**
 * Scanning in progress.
 * Check if the scanning process is already finished and update visible scanning information
 **/
$(document).live("scanning-in-progress", function() {
    timer = window.setInterval(checkScanEnd,100);
    
    // open modal dialog
    window.setTimeout(function() {
        $('#modal-scanning').modal({backdrop: 'static'});
    }, 110); 
});

function checkScanEnd() {
    $.ajax({
        url: '../tmp/scaninfo.php',
        success: function(data) {
          document.location.href = redirect_url;
        },
        statusCode: {
            404: function() {
            }
        }
    });

}
