<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>pile ADMIN INTERFACE</title>

    <link rel="stylesheet" type="text/css" href="assets/admin.css">
</head>
<body>

<div id="sidebar">
    <div id="sidebar-head">
        <h1><a href="admin.php">pile admin</a></h1>
    </div>
    <div id="sidebar-taglist">
        <ul id="sidebar-taglist-overview">
            <li id="sidebar-taglist-top"><a href="?tag=*">ALL (<?= $all_count ?>)</a></li>
            <li id="sidebar-taglist-top"><a href="?tag=_">UNTAGGED (<?= $none_count ?>)</a></li>
            <li id="sidebar-taglist-top"><a href="?action=new_tag">ADD TAG</a></li>
        </ul>
        <ul>
            <?
            foreach ($tags as $tag) {
                echo '<li><a href="?tag=' . $tag['name'] . "\">" . $tag['name'] . " (" . $tag['count'] . ")</a></li>";
            }
            ?>
        </ul>
    </div>
</div>

<div id="content">
    <?php echo $content ?>
</div>

<div id="login">
    <form method="get">
        <input type="hidden" name="action" value="logout">
        <button type="submit" id="login-button">log out</button>
    </form>
</div>
</body>
</html>
