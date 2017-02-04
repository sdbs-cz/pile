<div class="text document">
    <h1><?= $doc["Title"] ?></h1>
    <h2><?= $doc["Author"] ?></h2>
    <h3>Published: <?= $doc["Published"] ?></h3>
    <h3 class="doc-taglist">Tags:
        <?
        foreach($tags as $tag){
            echo '<li><a href="?tag=' . $tag["ID"] . "\">" . $tag["Name"] . "</a></li>";
        }
        ?>
    </h3>
    <p class="doc-description"><span class="doc-description-intro">Description: </span><?= $doc["Description"] ?></p>
    <div class="doc-link"><span class="doc-link-intro">Access file at: </span><a href="<?= $doc["URL"] ?>"><?= $doc["URL"] ?></a></div>
</div>