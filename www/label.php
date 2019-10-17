<?php
require '_vendor/autoload.php';
require '_vendor/erusev/parsedown/Parsedown.php';

require '_templates/Template.php';
require '_util/PileDB.php';


use Mpdf\Mpdf;

$db = new PileDB();
$doc = $db->fetchDoc($_GET["id"]);

$pd = new Parsedown();
$doc["Description"] = $pd->text($doc["Description"]);

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
    $mpdf->Output();
} catch (\Mpdf\MpdfException $exception) {
    http_response_code(500); ?>
    <h1>Something went wrong generating the label.</h1>
    <pre><?= $exception->getMessage() ?></pre>
<?php } ?>