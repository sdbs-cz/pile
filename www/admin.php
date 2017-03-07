<?php
require '_templates/Template.php';
require '_util/PileDB.php';
require '_util/Uploader.php';


$db = new PileDB();
$uploader = new Uploader();
session_start();

if (isset($_SESSION['ID'])){
    $page = new Template();

    if (isset($_GET["action"])){
        switch ($_GET["action"]){
            case "new_tag":
                $content = $page->render("admin_tag_edit.php");
                break;
            case "edit_tag":
                if (isset($_POST["Name"])){
                    $db->updateTag(
                        $_GET["tag"],
                        $_POST["Name"],
                        $_POST["Description"]
                    );
                }
                if ( !empty($_GET["tag"]) ) {
                    $page->tag = $db->fetchTag($_GET["tag"]);
                }
                $content = $page->render("admin_tag_edit.php");
                break;
            case "new_item":
                $content = $page->render("admin_doc_edit.php");
                break;
            case "edit_item":
                if (isset($_POST["Title"])){
                    if ( !empty($_FILES['upfile']['name']) ){
                        try {
                            $url = "http://pile.sdbs.cz/files/" . rawurlencode($uploader->handle($_FILES, "files/"));
                        } catch (RuntimeException $ex){
                            $page->text = $ex->getMessage();
                            echo $page->render('full_text.php');
                            return;
                        }
                    } else {
                        $url = $_POST["URL"];
                    }

                    $doc_tags = [];
                    foreach (explode(",", $_POST["Tags"]) as $tagName){
                        $tagName = trim($tagName);
                        $tag = $db->findTag($tagName);
                        if (!in_array($tag["ID"], $doc_tags)){
                            array_push($doc_tags, $tag["ID"]);
                        }
                    }

                    $db->updateDoc(
                        $_GET["item"],
                        $_POST["Title"],
                        $_POST["Author"],
                        $_POST["Description"],
                        $_POST["Published"],
                        $url,
                        $doc_tags
                    );
                }

                if ( !empty($_GET["item"]) ) {
                    $page->doc = $db->fetchDoc($_GET["item"]);
                }
                $content = $page->render("admin_doc_edit.php");

                break;
            case "remove":
                if ( ! empty($_GET["confirm"]) && $_GET["confirm"] == "yes"){
                    $db->removeDoc($_GET["item"]);
                    $page->text = "Document deleted.";
                    $page->redirect = $_GET["ret"];
                    echo $page->render("full_text.php");
                    return;
                } else {
                    $page->doc = $db->fetchDoc($_GET["item"]);
                    $content = $page->render("admin_doc_remove.php");
                }
                break;
            case "logout":
                unset($_SESSION["ID"]);
                $page->text = "See you.";
                $page->redirect = "/";
                echo $page->render("full_text.php");
                return;
        }
    } elseif (isset($_GET["tag"])) {
        $doc_list_template = new Template();
        if ($_GET["tag"] == "*"){
            $docs = $db->listDocs();
        } elseif ($_GET["tag"] == "_") {
            $docs = $db->listDocs(-1);
        } else {
            $tag = $db->findTag($_GET["tag"]);
            $docs = $db->listDocs($tag["ID"]);
            $doc_list_template->tag = $db->fetchTag($tag["ID"]);
        }
        $doc_list_template->docs = $docs;
        $content = $doc_list_template->render('admin_doc_listing.php');
    } else {
        $intro_template = new Template();
        $content = $intro_template->render('admin_intro.php');
    }

    $all_count = $db->getDocCount();

    $ret_count = $db->query("SELECT
                            count(ID)
                        FROM
                            Documents d
                        LEFT OUTER JOIN 
                            DocumentstoTags dt ON dt.Document = d.ID
                        WHERE dt.Document IS NULL")->fetchArray(SQLITE3_NUM);
    $none_count = $ret_count[0];

    $tags = $db->getTags();

    $page->all_count = $all_count;
    $page->none_count = $none_count;
    $page->tags = $tags;
    $page->content = $content;
    echo $page->render('admin_wrap.php');
} else {
    $page = new Template();

    if (isset($_POST['username']) && isset($_POST['password'])){
        $ret_id = $db->authenticate($_POST["username"], $_POST["password"]);
        if ($ret_id > 0){
            $_SESSION['ID'] = $ret_id;
            $page->text = "You have logged in successfully.";
            $page->redirect = "admin.php"; 
        } else {
            $page->text = "Username and/or password incorrect.";
            $page->redirect = "/"; 
        }
    } else {
        $page->text = "Please log in before accessing this page.";
        $page->redirect = "/"; 
    }

    echo $page->render('full_text.php');
}

?>
