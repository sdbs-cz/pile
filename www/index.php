<?php
require '_templates/Template.php';
require '_util/PileDB.php';
require '_vendor/erusev/parsedown/Parsedown.php';

$db = new PileDB();
$pd = new Parsedown();
session_start();

if (isset($_GET["item"])) {
    $doc = $db->fetchDoc($_GET["item"]);
    $doc["Description"] = $pd->text($doc["Description"]);

    $doc_template = new Template();
    $doc_template->doc = $doc;
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
        $tag["Description"] = $pd->text($tag["Description"]);
        $doc_list_template->tag = $tag;
    }
    $doc_list_template->docs = $docs;
    $content = $doc_list_template->render('front_doc_listing.php');
} else {
    $intro_template = new Template();
    $content = $intro_template->render('front_intro.php');
}

$page = new Template();
$page->doc_count = $db->getDocCount();
$page->none_count = $db->getUntaggedDocCount();
$page->tags = $db->getTags();
$page->content = $content;
$page->logged = isset($_SESSION["ID"]);
echo $page->render('front_wrap.php');
?>
