<?php

class PileDB
{
    private $db;

    function __construct()
    {
        $this->db = new SQLite3("pile.db");
    }

    function prepare($statement)
    {
        return $this->db->prepare($statement);
    }

    function query($statement)
    {
        return $this->db->query($statement);
    }

    public function getDocCount()
    {
        $ret_count = $this->db->query("SELECT count(ID) FROM Documents")->fetchArray(SQLITE3_NUM);
        return $ret_count[0];
    }

    public function getUntaggedDocCount()
    {
        $ret_count = $this->db->query("SELECT
                            count(ID)
                        FROM
                            Documents d
                        LEFT OUTER JOIN 
                            DocumentstoTags dt ON dt.Document = d.ID
                        WHERE dt.Document IS NULL")->fetchArray(SQLITE3_NUM);
        return $ret_count[0];
    }

    public function getTags()
    {
        $tag_query = "SELECT 
                   ID, Name, count(Document)
                FROM
                   Tags t
                LEFT OUTER JOIN 
                   DocumentstoTags d ON t.ID = d.Tag
                GROUP BY Name
                ORDER BY count(Document) DESC, Name";
        $tags_ret = $this->db->query($tag_query);
        $tags = [];
        while ($row = $tags_ret->fetchArray(SQLITE3_NUM)) {
            array_push($tags, array(
                'id' => $row[0],
                'name' => $row[1],
                'count' => $row[2]
            ));
        }
        return $tags;
    }

    public function fetchDoc($id)
    {
        $stmt_doc = $this->db->prepare("SELECT * FROM Documents WHERE ID = :id");
        $stmt_doc->bindValue(":id", $id, SQLITE3_INTEGER);
        $doc = $stmt_doc->execute()->fetchArray(SQLITE3_ASSOC);

        $stmt_tags = $this->db->prepare("SELECT t.ID, t.Name FROM Tags t
                                                JOIN DocumentsToTags dt ON t.ID = dt.Tag
                                                JOIN Documents d on d.ID = dt.Document
                                            WHERE d.ID = :id");
        $stmt_tags->bindValue(":id", $id, SQLITE3_INTEGER);
        $ret = $stmt_tags->execute();
        $doc["tags"] = [];
        while ($tag = $ret->fetchArray(SQLITE3_ASSOC)) {
            array_push($doc["tags"], $tag);
        }
        return $doc;
    }

    public function listDocs()
    {
        if (func_num_args() > 0) {
            $tag = func_get_arg(0);
            if ($tag > 0) {
                $stmt = $this->db->prepare("SELECT
                                        ID, Title, Author, Published, URL
                                    FROM
                                       Documents d
                                    LEFT OUTER JOIN 
                                       DocumentsToTags dt ON d.ID = dt.Document
                                       WHERE Tag == :tag");
                $stmt->bindValue(":tag", $tag, SQLITE3_INTEGER);
            } else {
                $stmt = $this->db->prepare("SELECT
                                        ID, Title, Author, Published, URL
                                    FROM
                                       Documents d
                                    LEFT OUTER JOIN 
                                       DocumentsToTags dt ON d.ID = dt.Document
                                       WHERE dt.Document IS NULL");
            }
            $doc_ret = $stmt->execute();
        } else {
            $query = "SELECT ID, Title, Author, Published, URL FROM Documents";
            $doc_ret = $this->db->query($query);
        }
        $docs = [];
        while ($doc = $doc_ret->fetchArray(SQLITE3_ASSOC)) {
            $doc['date'] = empty($doc["Published"]) ? "" : "(" . $doc["Published"] . ")";
            array_push($docs, $doc);
        }
        return $docs;
    }

    public function getRecentDocs($count = 15)
    {
        $query = $this->db->prepare("SELECT * FROM Documents ORDER BY ID DESC LIMIT :count");
        $query->bindValue("count", $count);

        $query_ret = $query->execute();
        $result = [];
        while ($row = $query_ret->fetchArray(SQLITE3_ASSOC)) {
            array_push($result, $row);
        }
        return $result;
    }

    public function updateDoc($id, $title, $author, $description, $published, $url, $tag_ids)
    {
        if (empty($id)) {
            $stmt = $this->db->prepare("INSERT INTO Documents
			(ID, Title, Author, Description, Published, URL, UploadedTime)
			VALUES
                   	(NULL, :title, :author, :description, :published, :url, :uploadedtime)");
            $stmt->bindValue(":uploadedtime", time(), SQLITE3_INTEGER);
        } else {
            $stmt = $this->db->prepare("UPDATE Documents SET
						Title=:title,
						Author=:author,
						Description=:description,
						Published=:published,
						URL=:url
					    WHERE ID = :id");
            $stmt->bindValue(":id", $id, SQLITE3_INTEGER);
        }
        $stmt->bindValue(":title", $title, SQLITE3_TEXT);
        $stmt->bindValue(":author", $author, SQLITE3_TEXT);
        $stmt->bindValue(":description", $description, SQLITE3_TEXT);
        $stmt->bindValue(":published", $published, SQLITE3_TEXT);
        $stmt->bindValue(":url", $url, SQLITE3_TEXT);
        $stmt->execute();
        if (empty($id)) {
            $id = $this->db->lastInsertRowid();
        }

        if (!empty($id)) {
            $delete_stmt = $this->db->prepare("DELETE FROM DocumentsToTags
                                            WHERE Document = :id");
            $delete_stmt->bindValue(":id", $id, SQLITE3_INTEGER);
            $delete_stmt->execute();
        }

        foreach ($tag_ids as $tag) {
            $tag_stmt = $this->db->prepare("INSERT INTO DocumentsToTags ('Document', 'Tag')
                                                VALUES (:doc, :tag)");
            $tag_stmt->bindValue("doc", $id, SQLITE3_INTEGER);
            $tag_stmt->bindValue("tag", $tag, SQLITE3_INTEGER);
            $tag_stmt->execute();
        }
    }

    public function removeDoc($id)
    {
        $doc_stmt = $this->db->prepare("DELETE FROM Documents
                                            WHERE ID = :id");
        $doc_stmt->bindValue("id", $id, SQLITE3_INTEGER);
        $doc_stmt->execute();

        $tag_stmt = $this->db->prepare("DELETE FROM DocumentsToTags
                                            WHERE Document = :id");
        $tag_stmt->bindValue("id", $id, SQLITE3_INTEGER);
        $tag_stmt->execute();
    }

    public function findTag($name)
    {
        $stmt = $this->db->prepare("SELECT * FROM Tags WHERE Name == :name COLLATE NOCASE");
        $stmt->bindValue(":name", $name, SQLITE3_TEXT);
        return $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    }

    public function fetchTag($tag)
    {
        $stmt = $this->db->prepare("SELECT * FROM Tags WHERE ID == :tag");
        $stmt->bindValue(":tag", $tag, SQLITE3_INTEGER);
        return $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    }

    public function updateTag($id, $name, $description)
    {
        if (empty($id)) {
            $stmt = $this->db->prepare("INSERT INTO Tags
			(ID, Name, Description)
			VALUES
                   	(NULL, :name, :description)");
        } else {
            $stmt = $this->db->prepare("UPDATE Tags SET
						Name=:name,
						Description=:description,
                        Parent=:Parent
					    WHERE ID = :id");
            $stmt->bindValue(":id", $id, SQLITE3_INTEGER);
        }
        $stmt->bindValue(":name", $name, SQLITE3_TEXT);
        $stmt->bindValue(":description", $description, SQLITE3_TEXT);
        return $stmt->execute();
    }

    public function deleteTag($tag)
    {
        $stmt = $this->db->prepare("DELETE FROM Tags WHERE ID == :tag");
        $stmt->bindValue(":tag", $tag, SQLITE3_INTEGER);
        return $stmt->execute();
    }

    public function authenticate($username, $password)
    {
        $stmt = $this->db->prepare("SELECT
                                    *
                                FROM
                                    Users
                                WHERE
                                    Username = :username");
        $stmt->bindValue(":username", $username, SQLITE3_TEXT);
        $auth_ret = $stmt->execute();
        $auth = $auth_ret->fetchArray(SQLITE3_ASSOC);

        if (password_verify($password, $auth["Password"])) {
            return $auth["ID"];
        } else {
            return -1;
        }
    }
}

?>
