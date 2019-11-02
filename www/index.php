<?php
require '_templates/Template.php';
require '_util/PileDB.php';

$db = new PileDB();
session_start();
$page = new Template();

if (isset($_GET["item"])) {
    try {
        $doc = $db->fetchDoc($_GET["item"]);
    } catch (NotFoundException $e) {
        http_response_code(404);
        $page->text = "Document not found.";
        $page->redirect = "/";
        echo $page->render("full_text.php");
        die(0);
    }

    $doc_template = new Template();
    $doc_template->doc = $doc;
    $selected_doc = $doc;
    $content = $doc_template->render('front_doc_overview.php');
} elseif (isset($_GET["tag"])) {
    $doc_list_template = new Template();
    if ($_GET["tag"] == "*") {
        $docs = $db->listDocs();
    } elseif ($_GET["tag"] == "_") {
        $docs = $db->listDocs(-1);
    } else {
        $tag = $db->fetchTag($_GET["tag"]);
        if (!$tag) {
            $tag = $db->findTag($_GET["tag"]);
        }
        $docs = $db->listDocs($tag["ID"]);
        $selected_tag = $tag;
        $doc_list_template->tag = $tag;
    }
    $doc_list_template->docs = $docs;
    $content = $doc_list_template->render('front_doc_listing.php');
} else {
    $intro_template = new Template();
    $intro_template->recent_docs = $db->getRecentDocs();
    $content = $intro_template->render('front_intro.php');
}

$page->doc_count = $db->getDocCount();
$page->none_count = $db->getUntaggedDocCount();
$page->tags = $db->getTags();
$page->content = $content;
$page->logged = isset($_SESSION["ID"]);
$page->selected_doc = $selected_doc;
$page->selected_tag = $selected_tag;
echo $page->render('front_wrap.php');
?>
