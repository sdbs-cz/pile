<div class="text document edit-form">
    <form method="post" id="form" action="admin.php?action=edit_item<?= empty($doc) ? "" : "&item=" . $doc["ID"] ?>"
          enctype="multipart/form-data">
        <strong>Title:</strong> <input id="title-input" type="text" name="Title"
                                       value="<?= empty($doc) ? "" : $doc["Title"] ?>"><br>
        <strong>Author:</strong> <input type="text" name="Author" value="<?= empty($doc) ? "" : $doc["Author"] ?>"><br>
        <strong>Date published:</strong> <input type="text" name="Published"
                                                value="<?= empty($doc) ? "" : $doc["Published"] ?>"><br>
        <strong>Description:</strong><br>
        <textarea name="Description" cols="120" rows="20">
<?= empty($doc) ? "" : $doc["Description"] ?>
</textarea><br>
        <strong>File:</strong> <input id="file-input" type="file" name="upfile" onchange="updateTitle()"><br>
        <strong>URL:</strong> <input type="text" name="URL" value="<?= empty($doc) ? "" : $doc["URL"] ?>"><br>
        <strong>Tags:</strong> <input type="text" name="Tags" value="<?php
        if (!empty($doc)) {
            $tags = [];
            foreach ($doc["tags"] as $tag) {
                array_push($tags, $tag["Name"]);
            }
            echo implode(", ", $tags);
        } else if (!empty($_GET["tag"])) {
            echo $tag["Name"];
        }
        ?>"><br>
        <input type="submit">
    </form>
</div>
<script>
    function updateTitle() {
        const titleInput = document.getElementById("title-input");
        if (titleInput.value.length === 0) {
            const filename = document.getElementById("file-input").value;
            titleInput.value = filename.replace(/.*[\/\\]/, '').replace(/\.[\w]{2,}$/, '');
        }
    }
</script>
