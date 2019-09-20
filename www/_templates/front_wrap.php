<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>The /-\ pile</title>

    <link rel="stylesheet" type="text/css" href="assets/main.css">
    <link rel="icon" type="image/png" href="/favicon.png">

    <?php if ($selected_doc): ?>
        <meta property="og:title" content="The /-\ Pile: <?= $selected_doc['Title'] ?>"/>
        <meta property="og:url" content="https://pile.sdbs.cz/?item=<?= $selected_doc['ID'] ?>"/>
        <meta property="og:description" content="<?= $selected_doc['Description'] ?>"/>
        <meta property="og:type" content="article"/>
    <?php elseif ($selected_tag): ?>
        <meta property="og:title" content="The /-\ Pile: Documents under '<?= $selected_tag['Name'] ?>'"/>
        <meta property="og:url" content="https://pile.sdbs.cz/?tag=<?= $selected_tag['ID'] ?>"/>
        <meta property="og:description" content="<?= $selected_tag['Description'] ?>"/>
        <meta property="og:type" content="website"/>
    <?php else: ?>
        <meta property="og:title" content="The /-\ Pile"/>
        <meta property="og:type" content="website"/>
        <meta property="og:url" content="https://pile.sdbs.cz/"/>
        <meta property="og:description"
              content="This is where we upload the stuff we consider important to the larger conceptual and thematic landscape of what we do: confronting apathy, inter-subjectivity, the human right to self-determination, counter-culture and such..."/>
    <?php endif; ?>
    <meta property="og:image" content="https://pile.sdbs.cz/favicon.png"/>

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
