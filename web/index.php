<!doctype html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en-gb"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en-gb"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en-gb"> <![endif]-->
<!--[if gt IE 8]><!--> <html lang="en-gb"> <!--<![endif]-->
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <title>Online PHP Security Vulnerability Source Code Scanner</title>
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

  <!-- You can also compile style.less to use regular css. Your apps will still work. -->
  <style type="text/css">body{padding-top:60px}.hidden{margin:20px;border:5px solid #a24c4c;background-color:red;padding:10px;width:400px;color:white;font-family:helvetica,sans-serif}</style>
  <link rel="stylesheet/less" type="text/css" href="kickstrap.less">
  <script src="Kickstrap/js/less-1.3.0.min.js"></script>
</head>
<body>
<div id="sf-wrapper"> <!-- Sticky Footer Wrapper -->
   <div class="hidden"><h1>No Stylesheet Loaded</h1><p><strong>Could not load Kickstrap.</strong>There are <a href="http://getkickstrap.com/docs/1.2/troubleshooting/#lessjs-errors">several common reasons for this error.</a></p></div>
  <!-- Prompt IE 6/7 users to install Chrome Frame. Remove this if you support IE 7-.
       chromium.org/developers/how-tos/chrome-frame-getting-started -->
  <!--[if lt IE 8]><p class=chromeframe>Your browser is <em>ancient!</em> <a href="http://browsehappy.com/">Upgrade to a different browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to experience this site.</p><![endif]-->
<!--! END KICKSTRAP HEADER --> 











   <?php
   $page = 'start';
   require("menu.php");
   ?>
    
    	<div id="qunit"></div>
    
    <div class="container">
    	<div class="row">
    	  <div class="span12">
    	  
					<header class="hero-unit">
						<hgroup>
							<h1>PHP Source Code Scanner</h1>
							<h2>Analyse your PHP source.<br>
							  Find security vulnerabilities.</h2></hgroup>
					</header>

					<section class="row-fluid">
						<div class="span8">
							<article>
								<header>
									<hgroup>
                                                                            <h2><i class="icon-bolt"></i> Dynamic Source Code Analysing</h2>
									</hgroup>
								</header>

								<p class="lead">Find more flaws. Get less false results.</p>
								
                                                                <p>With its dynamic algorithm our scanner is able to detect code parts which are never reached and therefore unable to cause security weaknesses. Therefore you will most likely only get real security vulnerabilities, not all lines which possibly contain security vulnerabilities like you would get with static analysing tools. This makes checking your source code much more enjoyable.</p>
								
							</article>


							<article>
								<header>
									<hgroup>
                                                                            <h2><i class="icon-eye-open"></i> Detects most common security flaws</h2>
									</hgroup>
								</header>

								<p class="lead">Heading text bla</p>
								<p>Some additional text.</p>
                                                                <p>Some additional text.</p>
                                                                <p>Some additional text.</p>
                                                                <div class="alert alert-error">SQL Injection possible!</div>
							</article>


						</div>
                                                <aside class="span4">
                                                    <aside class="well bs-docs-sidebar"> 
                                                        <h2>What this tool is for?</h2>
                                                        <p>You have written the probably most awesome piece of software ever created and now you want to use or even publish it.</p>
                                                        <p>However, if your software has security flaws you didn't think of, your or your customers website can easily become a target of hackers. The damage done then often isn't trivial. Getting everything working and secure again can cost you hours or even days of work.</p>
                                                        <p>To prevent this scenario you can scan your source code for security vulnerabilities with our tool.</p>

                                                    </aside>

                                                    <aside class="well bs-docs-sidebar"> 
                                                        <h2>Which vulnerabilities get detected?</h2>
                                                        <p>So far our scanner detects the following security vulnerabilities:</p>

                                                            <ul class="nav nav-list bs-docs-sidenav affic">
                                                                    <li><a href="xss.php">Cross Site Scripting (XSS)</a></li>
                                                                    <li><a href="sqlinjection.php">SQL-Injection</a></li>
                                                            </ul>
                                                    </aside>
                                                </aside>
					</section>

					

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
