<div class="text">
    <p>Confirm deletion of <strong>"<?= $doc["Title"] ?>"</strong>:</p>
    <a href="admin.php?action=remove&confirm=yes&item=<?= $doc["ID"] ?>&ret=<?= $_SERVER['HTTP_REFERER']; ?>" class="button">Remove from database</a>
</div>
