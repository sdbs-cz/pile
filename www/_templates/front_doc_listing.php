<?php if (isset($tag)): ?>
<div class="text tag-text">
    <h1><?= $tag["Name"] ?></h1>
    <p class="tag-desc"><?= $tag["Description"] ?></p>
</div>
<?php endif; ?>

<?php foreach($docs as $doc): ?>
    <div class="text doc-item">
        <?php if (!empty($doc["URL"])): ?>
            <a class="doc-item-link" href="<?= $doc["URL"] ?>">🔗</a>
        <?php endif; ?>
        <a href="?item=<?= $doc["ID"]?>">
            <div class="doc-item-text">
                <h2><?= $doc["Title"]?></h2>
                <h3><?= $doc["Author"] . " " . $doc['date']?></h3>
            </div>
        </a>
    </div>
<?php endforeach; ?>