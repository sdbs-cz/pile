<?php
require '_vendor/autoload.php';

require '_templates/Template.php';
require '_util/PileDB.php';


$db = new PileDB();
try {
    $doc = $db->fetchDoc($_GET["id"]);
} catch (NotFoundException $e) {
    http_response_code(404);
    $page->text = "Document not found.";
    $page->redirect = "/";
    echo $page->render("full_text.php");
    die(0);
}

$front = new Template();
$front->doc = $doc;

$defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
$fontDirs = $defaultConfig['fontDir'];
$defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
$fontData = $defaultFontConfig['fontdata'];
try {
    $mpdf = new \Mpdf\Mpdf([
        'format' => 'A4',
        'fontDir' => array_merge($fontDirs, [
            __DIR__ . '/assets',
        ]),
        'fontdata' => $fontData + [
                'prociono' => [
                    'R' => 'Prociono-Regular.ttf',
                ]
            ],
        'default_font' => 'prociono'
    ]);

    $mpdf->showImageErrors = true;
    $mpdf->WriteHTML($front->render("_templates/label_template.php"));
    try {
        if (preg_match('#^https?://pile.sdbs.cz/.*\.(pdf|PDF)$#', $doc['URL']) === 1) {
            $tempName = tempnam("/tmp/", 'pile-merge');
            file_put_contents($tempName, file_get_contents($doc["URL"]));
            $pageCount = $mpdf->SetSourceFile($tempName);

            for ($page = 1; $page <= $pageCount; $page++) {
                $mpdf->AddPage();
                $template = $mpdf->ImportPage($page);
                $mpdf->UseTemplate($template);
            }
        }
    } catch (Exception $exception) {
        // noop
    } finally {
        unlink($tempName);
    }

    $mpdf->Output();
} catch (Exception $exception) {
    http_response_code(500); ?>
    <h1>Something went wrong generating the label.</h1>
    <pre><?= $exception->getMessage() ?></pre>
<?php } ?>