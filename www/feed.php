<?php
require '_util/PileDB.php';
$db = new PileDB();
$recent_docs = $db->getRecentDocs();

header('Content-Type: application/rss+xml');
?>
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">

    <channel>
        <title>/-\ pile</title>
        <link>https://pile.sdbs.cz</link>
        <atom:link href="https://pile.sdbs.cz/feed.php" rel="self" type="application/rss+xml" />
        <description>A pile of interesting documents.</description>

        <?php foreach ($recent_docs as $doc): ?>
        <item>
            <title><?= $doc['Title'] ?></title>
            <guid>https://pile.sdbs.cz/?item=<?= $doc['ID'] ?></guid>
            <link>https://pile.sdbs.cz/?item=<?= $doc['ID'] ?></link>
            <description><?= $doc['Description'] ?></description>
        </item>
        <?php endforeach; ?>
    </channel>
</rss>
