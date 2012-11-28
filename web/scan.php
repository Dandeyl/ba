<?php 
if(isset($_POST["input_code"])) {
    require("php/scan_validate.php");
} ?>
<!doctype html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en-gb"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en-gb"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en-gb"> <![endif]-->
<!--[if gt IE 8]><!--> <html lang="en-gb"> <!--<![endif]-->
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <title>Scan PHP Code for Vulnerabilities</title>
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

  <!-- You can also compile style.less to use regular css. Your apps will still work. -->
  <style type="text/css">body{padding-top:60px}.hidden{margin:20px;border:5px solid #a24c4c;background-color:red;padding:10px;width:400px;color:white;font-family:helvetica,sans-serif}</style>
  <link rel="stylesheet/less" type="text/css" href="kickstrap.less">
  <script src="Kickstrap/js/less-1.3.0.min.js"></script>
  
  <?php include("CodeMirror/bootstrap.php"); ?>
</head>
<body>
<div id="sf-wrapper"> <!-- Sticky Footer Wrapper -->
   <div class="hidden"><h1>No Stylesheet Loaded</h1><p><strong>Could not load Kickstrap.</strong>There are <a href="http://getkickstrap.com/docs/1.2/troubleshooting/#lessjs-errors">several common reasons for this error.</a></p></div>
  <!-- Prompt IE 6/7 users to install Chrome Frame. Remove this if you support IE 7-.
       chromium.org/developers/how-tos/chrome-frame-getting-started -->
  <!--[if lt IE 8]><p class=chromeframe>Your browser is <em>ancient!</em> <a href="http://browsehappy.com/">Upgrade to a different browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to experience this site.</p><![endif]-->
<!--! END KICKSTRAP HEADER --> 





<?php 
$page = "scan";
require("menu.php"); 
?>
    
    	<div id="qunit"></div>
    
    <div class="container">
    	<div class="row">
    	  <div class="span12">
    	  
					<header class="hero-content">
                                            <hgroup>
                                                <h1><i class="icon-bolt"></i> Scan Your Source Code</h1>
                                                <p class="lead">Let our tool check your source code for security vulnerabilities.</p>
                                               <!--<p>To begin scanning your source code either type in your source directly or upload an archive file to our server and choose which files can be accessed directly.</p>-->
                                            </hgroup>
					</header>

					<section class="row-fluid">
						<div class="span12">
                                                    <div class="tabbable tabs-left" id="scan-tabs">
                                                        <ul class="nav nav-tabs">
                                                            <li><a href="#tab-input" data-toggle="tab">Scan by direct input</a></li>
                                                            <li class="active"><a href="#tab-server" data-toggle="tab">Scan file on server</a></li>
                                                            <li><a href="#tab-upload" data-toggle="tab">Scan by file upload</a></li>
                                                        </ul>
                                                        <div class="tab-content">
                                                            
                                                            <!-- DIRECT INPUT Tab -->
                                                            <div class="tab-pane" id="tab-input">
                                                                <form action="scan.php" method="post" />
                                                                
                                                                <p>
                                                                    <span class="label label-info">Info</span>
                                                                    The starting PHP tag <strong>must</strong> be written, otherwise the input will be interpreted as HTML.
                                                                </p>
                                                                
                                                                <textarea rows="30" cols="40" id="input_code" name="input_code" style="width:97%;"></textarea>
                                                                <div class="form-actions">
                                                                    <div class="btn-group">
                                                                        <button class="btn dropdown-toggle" data-toggle="dropdown">
                                                                            Options
                                                                            <span class="caret"></span>
                                                                        </button>
                                                                        <ul class="dropdown-menu">
                                                                            <li class="disabled"><a href="#">No Options yet</a></li>
                                                                        </ul>

                                                                        <button type="submit" id="btn_scan-direct" class="btn btn-primary">Scan Source</button>
                                                                    </div>


                                                                </div>
                                                                </form>
                                                            </div>
                                                            
                                                            <!-- SERVER Tab -->
                                                            <div class="tab-pane active" id="tab-server">
                                                                <form action="scan.php" method="post" />
                                                                
                                                                <p>Enter the path to the file on the server you want to check.</p>
                                                                
                                                                <div class="input-prepend input-append">
                                                                    <span class="add-on">File path: </span>
                                                                    <input class="input-xxlarge" id="server_file" type="text" value="testfiles/simple.php">
                                                                    <button class="btn dropdown-toggle" data-toggle="dropdown">
                                                                        Options
                                                                        <span class="caret"></span>
                                                                    </button><ul class="dropdown-menu">
                                                                            <li class="disabled"><a href="#">No Options yet</a></li>
                                                                        </ul>
                                                                    <button type="submit" id="btn_scan-server" class="btn btn-primary">Scan File</button>
                                                                </div>

                                                                </form>
                                                            </div>

                                                            

                                                            <!-- FILE UPLOAD Tab -->
                                                            <div class="tab-pane" id="tab-upload">
                                                                File upload coming soon
                                                            </div>
                                                        </div>
                                                        
                                                        
                                                        <script>
      var editor = CodeMirror.fromTextArea(document.getElementById("input_code"), {
        lineNumbers: true,
        matchBrackets: true,
        mode: "application/x-httpd-php",
        indentUnit: 4,
        indentWithTabs: true,
        enterMode: "keep",
        tabMode: "shift"
      });
    </script>
                                                        
                                                        
                                                        
                                                    </div>
                                                </div>
					</section>

					

    	  </div>
    	</div>
    </div>  	


<!----- MODAL BOXES -->
<div id="modal-scanning" class="modal hide fade">
  <div class="modal-header">
    
    <h3>Scanning in progress, please be patient</h3>
  </div>
  <div class="modal-body">
      <div class="row-fluid">
        <div class="span2">
            <img src="images/scanning-in-progress.gif" />
        </div>
        <div class="span5">
            <p class="lead">File being scanned</p>
            
            <div class="file-list">
                <div class="file-list-overlay"></div>
                <ol>
                    <li>index.php
                        <ul>
                            <li>simple.php</li>
                        </ul>
                    </li>
                    <li>index2.php</li>
                    <li>index3.php</li>
                    <li>index4.php</li>
                </ol>
            </div>
        </div>
        <div class="span5">
            <p class="lead">Scan information</p>
            <p>Scanning in progress..</p>
        </div>
      </div>
      
      <div class="row-fluid">
          <div class="alert alert-info">
              You will get redirected to your results as soon as scanning is completed.
          </div>
      </div>
  </div>
</div>







  <!--! KICKSTRAP FOOTER -->
  <div id="push"></div></div> <!-- sf-wrapper -->
  
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
  <script>window.jQuery || document.write('<script src="Kickstrap/js/jquery-1.8.2.min.js"><\/script>');</script>
  <!-- Kickstrap CDN thanks to our friends at netDNA.com -->
  <script id="appList" src="http://netdna.getkickstrap.com/1.2/Kickstrap/js/kickstrap.min.js"></script>
  <script src="Kickstrap/js/scan_validate.js"></script>
  <script>window.consoleLog || document.write('<script id="appList" src="Kickstrap/js/kickstrap.min.js"><\/script>')</script>
  <!--script>
   ks.ready(function() {
      // JavaScript placed here will run only once Kickstrap has loaded successfully.
      $.pnotify({
         title: 'Hello World',
         text: 'To edit this message, find me at the bottom of this HTML file.'
      });
   });
  </script-->
  <!-- Asynchronous Google Analytics snippet. Change UA-XXXXX-X to be your site's ID.
       mathiasbynens.be/notes/async-analytics-snippet -->
  <!--script>
    var _gaq=[['_setAccount','UA-XXXXX-X'],['_trackPageview']];
    (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
    g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
    s.parentNode.insertBefore(g,s)}(document,'script'));
  </script-->
</body>
</html>
