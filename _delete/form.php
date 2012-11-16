<?php include("proteus.php"); ?>
<html><head></head>
    
<body>
    <?php Proteus::BeginForm(); ?>
    <form action="<?= $_SERVER["PHP_SELF"]; ?>" method="post">
        E-Mail: <input type="text" name="email" value="" /> <br />
        Text: <textarea name="text"></textarea>
    </form>
    <?php Proteus::EndForm(); ?>
</body>
</html>