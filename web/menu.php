<div class="navbar navbar-fixed-top">
    <div class="navbar-inner">
      <div class="container">
        <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </a>
        <span class="brand">Scanner</span>
        <div class="nav-collapse">
          <ul class="nav">
            <li <?= ($page=='start')   ? 'class="active"' : '';?>><a href="./">Overview</a></li>
            <li <?= ($page=='scan')    ? 'class="active"' : '';?>><a href="./scan.php">Scan Your Source Code</a></li>
            <li <?= ($page=='about')   ? 'class="active"' : '';?>><a href="./about.php">About</a></li>
            <li <?= ($page=='contact') ? 'class="active"' : '';?>><a href="./contact.php">Contact</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </div>
</div>