<div class="text document">
    <h1><?= $doc["Title"] ?></h1>

    <?php if (!empty($doc["Author"])): ?>
        <h2><?= $doc["Author"] ?></h2>
    <?php endif; ?>

    <?php if (!empty($doc["Published"])): ?>
        <h3>Published: <?= $doc["Published"] ?></h3>
    <?php endif; ?>

    <?php if (!empty($doc["tags"])): ?>
        <h3 class="doc-taglist">Tags:
            <?php
            foreach ($doc["tags"] as $tag) {
                echo '<li><a href="?tag=' . $tag["ID"] . "\">" . $tag["Name"] . "</a></li>";
            }
            ?>
        </h3>
    <?php endif; ?>

    <?php if ($doc["HTMLDescription"]): ?>
        <p class="doc-description"><span
                    class="doc-description-intro"><?= empty($doc["URL"]) ? "Content" : "Description" ?>
                : </span><?= $doc["HTMLDescription"] ?></p>
    <?php endif; ?>

    <div class="doc-link"><span class="doc-link-intro">Get (document with) print label: </span>
        <a href="/label.php?id=<?= $doc["ID"] ?>">https://pile.sdbs.cz/label.php?id=<?= $doc["ID"] ?></a></div>

    <?php if (!empty($doc["URL"])): ?>
        <div class="doc-link"><span class="doc-link-intro">Access file at: </span><a
                    href="<?= $doc["URL"] ?>"><?= urldecode($doc["URL"]) ?></a></div>
    <?php endif; ?>
</div>
