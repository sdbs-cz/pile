<div class="text document edit-form">
    <form method="post" id="form" action="admin.php?action=edit_tag<?= empty($tag) ? "" : "&tag=" . $tag["ID"] ?>">
        <strong>Name:</strong> <input type="text" name="Name" value="<?= empty($tag) ? "" : $tag["Name"] ?>"><br>
        <strong>Description:</strong><br>
        <textarea name="Description" cols="120" rows="20">
<?= empty($tag) ? "" : $tag["Description"] ?>
</textarea><br>
        <input type="submit">
    </form>
</div>
