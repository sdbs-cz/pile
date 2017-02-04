<?php
require '_templates/Template.php';


$db = new SQLite3("pile.db");

$ret_count = $db->query("SELECT count(ID) FROM Documents")->fetchArray(SQLITE3_NUM);
$count = $ret_count[0];

$tag_query = "SELECT 
                   ID, Name, count(Document)
                FROM
                   Tags t
                LEFT OUTER JOIN 
                   DocumentstoTags d ON t.ID = d.Tag
                GROUP BY Name
                ORDER BY count(Document) DESC, Name";
$tags_ret = $db->query($tag_query);
$tags = [];
while ($row = $tags_ret->fetchArray(SQLITE3_NUM)) {
    array_push($tags, array(
        'href' => $row[0],
        'name' => $row[1],
        'count' => $row[2]
    ));
}

if (isset($_GET["item"])) {
    $stmt_doc = $db->prepare("SELECT * FROM Documents WHERE ID = :id");
    $stmt_doc->bindValue(":id", $_GET["item"], SQLITE3_INTEGER);
    $doc = $stmt_doc->execute()->fetchArray(SQLITE3_ASSOC);

    $stmt_tags = $db->prepare("SELECT t.ID, t.Name FROM Tags t
                                                JOIN DocumentsToTags dt ON t.ID = dt.Tag
                                                JOIN Documents d on t.ID = dt.Document
                                            WHERE d.ID = :id");
    $stmt_tags->bindValue(":id", $_GET["item"], SQLITE3_INTEGER);
    $ret = $stmt_tags->execute();
    $doc_tags = [];
    while ($tag = $ret->fetchArray(SQLITE3_ASSOC)) {
        array_push($doc_tags, $tag);
    }

    $doc_template = new Template();
    $doc_template->doc = $doc;
    $doc_template->tags = $doc_tags;
    $content = $doc_template->render('front_doc_overview.php');
} elseif (isset($_GET["tag"])) {
    if ($_GET["tag"] == "*"){
        $query = "SELECT ID, Title, Author, Published, URL FROM Documents";
        $doc_ret = $db->query($query);
    } else {
        $stmt = $db->prepare("SELECT
                                    ID, Title, Author, Published, URL
                                FROM
                                   Documents d
                                LEFT OUTER JOIN 
                                   DocumentsToTags t ON d.ID = t.Document
                                   WHERE Tag == :tag");
        $stmt->bindValue(":tag", $_GET["tag"], SQLITE3_INTEGER);
        $doc_ret = $stmt->execute();
    }
    $docs = [];
    while ($doc = $doc_ret->fetchArray(SQLITE3_ASSOC)) {
        $doc['date'] = empty($doc["Published"]) ? "" : "(" . $doc["Published"] . ")";
        array_push($docs, $doc);
    }

    $stmt = $db->prepare("SELECT Name, Description FROM Tags WHERE ID == :tag");
    $stmt->bindValue(":tag", $_GET["tag"], SQLITE3_INTEGER);
    
    $doc_list_template = new Template();
    $doc_list_template->tag = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    $doc_list_template->docs = $docs;
    $content = $doc_list_template->render('front_doc_listing.php');
} else {
    $intro_template = new Template();
    $content = $intro_template->render('front_intro.php');
}

$page = new Template();
$page->tag_count = $count;
$page->tags = $tags;
$page->content = $content;
echo $page->render('front_wrap.php');
?>