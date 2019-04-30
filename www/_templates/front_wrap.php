<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>The /-\ pile</title>

    <link rel="stylesheet" type="text/css" href="assets/main.css">

</head>
<body>

<div id="sidebar">
    <div id="sidebar-head">
        <h1><a href="..">The /-\ pile</a></h1>
    </div>
    <div id="sidebar-taglist">
        <ul>
            <li id="sidebar-taglist-top"><a href="?tag=*">ALL (<?= $doc_count ?>)</a></li>
            <?php if ($none_count > 0): ?>
                <li id="sidebar-taglist-top"><a href="?tag=_">UNTAGGED (<?= $none_count ?>)</a></li>
            <?php endif; ?>
            <?php
            foreach ($tags as $tag) {
                if ($tag['count'] > 0) {
                    echo '<li><a href="?tag=' . $tag['id'] . "\">" . $tag['name'] . " (" . $tag['count'] . ")</a></li>";
                }
            }
            ?>
        </ul>
    </div>
</div>

<div id="content">
    <?php echo $content ?>
</div>

<div id="login">
    <?php if ($logged): ?>
        <form method="get" action="admin.php">
            <button type="submit" id="login-button">enter admin interface</button>
        </form>
    <?php else: ?>
        <form method="post" action="admin.php">
            <input type="text" name="username" id="login-user"></input>
            <input type="password" name="password" id="login-pass"></input>
            <button type="submit" id="login-button">></button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
