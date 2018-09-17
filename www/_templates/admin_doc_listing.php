<?php if (isset($tag)): ?>
    <div class="text tag-text">
        <h1><?= $tag["Name"] ?></h1>
        <p class="tag-desc"><?= $tag["Description"] ?></p>
        <a class="tag-edit-link" href="?action=edit_tag&tag=<?= $tag["ID"] ?>">[edit tag]</a>
        <a class="tag-edit-link" href="#" onclick="confirmDelete()">[delete tag]</a>
    </div>
<?php endif; ?>

<?php if ($_GET["tag"] != "*" &&
    $_GET["tag"] != "_"): ?>
    <div class="text doc-item doc-new-item">
        <a href="?action=new_item&tag=<?= $tag["Name"] ?>">
            <div class="doc-item-text">
                <h2>Upload a new document</h2>
            </div>
        </a>
    </div>
<?php endif; ?>

<?php foreach ($docs as $doc): ?>
    <div class="text doc-item">
        <a class="doc-item-link" href="?action=remove&item=<?= $doc["ID"] ?>">[X]</a>
        <a href="?action=edit_item&item=<?= $doc["ID"] ?>">
            <div class="doc-item-text">
                <h2><?= $doc["Title"] ?></h2>
                <h3><?= $doc["Author"] . " " . $doc['date'] ?></h3>
            </div>
        </a>
    </div>
<?php endforeach; ?>

<script>
    function confirmDelete() {
        if (window.confirm("Do you really wish to delete this tag?")) {
            window.open("?action=delete_tag&tag=<?= $tag["ID"] ?>")
        }
    }
</script>