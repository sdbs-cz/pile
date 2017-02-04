<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>The /-\ pile</title>

        <link rel="stylesheet" type="text/css" href="assets/main.css">

        <!-- Piwik -->
        <script type="text/javascript">
            var _paq = _paq || [];
            _paq.push(['trackPageView']);
            _paq.push(['enableLinkTracking']);
            (function() {
                var u="//www.sdbs.cz/piwik/";
                _paq.push(['setTrackerUrl', u+'piwik.php']);
                _paq.push(['setSiteId', '2']);
                var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
                g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
            })();
        </script>
        <noscript><p><img src="//www.sdbs.cz/piwik/piwik.php?idsite=2" style="border:0;" alt="" /></p></noscript>
        <!-- End Piwik Code -->
    </head>
    <body>

        <div id="sidebar">
            <div id="sidebar-head">
                <h1><a href="..">The /-\ pile</a></h1>
            </div>
            <div id="sidebar-taglist">
                <ul>
                    <li id="sidebar-taglist-top"><a href="?tag=*">ALL (<?= $tag_count ?>)</a></li>
                    <?
                        foreach($tags as $tag){
                            echo '<li><a href="?tag=' . $tag['href'] . "\">" . $tag['name'] . " (" . $tag['count'] . ")</a></li>";
                        }
                    ?>
                </ul>
            </div>
        </div>

        <div id="content">
            <?php echo $content ?>
        </div>

        <div id="login">
            <form method="post">
                <input type="text" name="username" id="login-user"></input>
            <input type="password" name="password" id="login-pass"></input>
        <button type="submit" id="login-button">></button>
        </form>
    </div>
</body>
</html>