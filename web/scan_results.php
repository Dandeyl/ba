<?php 
require dirname(__FILE__).'/../parser/bootstrap.php';
require dirname(__FILE__).'/../scanner/bootstrap.php';

$scan_result = '';
$result_file = dirname(__FILE__).'/../tmp/scanresult.php';

$result   = unserialize(file_get_contents($result_file));

if(isset($result['parseError'])) {
    $scan_result = '<div class="alert alert-error">'.$result['parseError'].'</div>';
}
else {
    $vulnlist = $result["vulnList"];
    foreach($vulnlist->getVulnerabilities() as $vulnerability) {
        $scan_result .= '<pre class="brush: php; first-line: '.$vulnerability->getLine().';toolbar: false;" 
                              title="<button class=\'btn btn-mini\'>Show details</button> <strong>'.$vulnerability->getFile().':</strong> <span class=\'label\'>'.$vulnerability->getType().'</span> on line '.$vulnerability->getLine().'.">
    '.Scanner::printNode($vulnerability->getNode()).'
    </pre><p>&nbsp;</p>';
    }
}

?>
<!doctype html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en-gb"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en-gb"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en-gb"> <![endif]-->
<!--[if gt IE 8]><!--> <html lang="en-gb"> <!--<![endif]-->
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <title>Results - is your software secure?</title>
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

  <!-- You can also compile style.less to use regular css. Your apps will still work. -->
  <style type="text/css">body{padding-top:60px}.hidden{margin:20px;border:5px solid #a24c4c;background-color:red;padding:10px;width:400px;color:white;font-family:helvetica,sans-serif}</style>
  <link rel="stylesheet/less" type="text/css" href="kickstrap.less">
  <script src="Kickstrap/js/less-1.3.0.min.js"></script>
  <script src="Kickstrap/js/syntaxhighlighter/shCore.js"></script>
  <script src="Kickstrap/js/syntaxhighlighter/brushes/shBrushPhp.js"></script>
  <script type="text/javascript">
     SyntaxHighlighter.all()
  </script>
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
                                                <h1><i class="icon-th-list"></i> Scan Results</h1>
                                                
                                            </hgroup>
					</header>

              
              
              
              
              
         
              
              
					<section class="row-fluid">
                                            
                                            
                                            <div class="span12">
                                                    
                                                    <!-- Begin navbar -->
                                                    <div class="navbar">
                                                        <div class="navbar-inner">
                                                            <!--<ul class="nav">
                                                                <li class="active">
                                                                    <a href="#">Home</a>
                                                                </li>
                                                                <li><a href="#">Link</a></li>
                                                                <li><a href="#">Link</a></li>
                                                            </ul>-->
                                                            
                                                            <ul class="nav pull-right">
                                                                <li class="dropdown">
                                                                  <a href="#" class="dropdown-toggle" data-toggle="dropdown">Sort by <b class="caret"></b></a>
                                                                  <ul class="dropdown-menu">
                                                                    <li><a href="#">Action</a></li>
                                                                    <li><a href="#">Another action</a></li>
                                                                    <li><a href="#">Something else here</a></li>
                                                                    <li class="divider"></li>
                                                                    <li><a href="#">Separated link</a></li>
                                                                  </ul>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <!-- End navbar, begin overview -->
                                                    <h2>Scanning overview</h2>
                                                    
                                                    <table class="table table-bordered">
                                                        <tr>
                                                            <th>Attack type</th>
                                                            <td>XSS</td>
                                                            <td>SQL-Injection</td>
                                                            <td>TBA*</td>
                                                            <td>TBA*</td>
                                                            <td>TBA*</td>
                                                            <td>TBA*</td>
                                                            <td>TBA*</td>
                                                            <td>TBA*</td>
                                                            <th>Total</th>
                                                        </tr>
                                                        <tr>
                                                            <th># vulnerabilities</th>
                                                            <td>0</td>
                                                            <td>0</td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <th><?= $vulnlist->getNumVulnerabilities(); ?></th>
                                                        </tr>
                                                    </table>
                                                    <small>*coming soon.</small>
                                                    <p>&nbsp;</p>
                                                    
                                                    <h2>Vulnerable code sequences</h2>
                                                    
                                                    <!-- End overview, begin content -->
                                                    <?php echo $scan_result; ?>
                                                    
                                                    
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
