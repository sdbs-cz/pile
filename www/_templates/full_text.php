<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <?php if (isset($redirect)): ?>
        <meta http-equiv="refresh" content="1;URL=<?= $redirect ?>"/>
    <?php endif; ?>

    <title>The /-\ pile</title>

    <link rel="stylesheet" type="text/css" href="assets/main.css">
    <link rel="icon" type="image/png" href="/favicon.png">
    <style>
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            display: table
        }

        body p {
            font-size: 3rem;
            text-align: center;
        }

        div {
            display: table-cell;
            text-align: center;
            vertical-align: middle;
        }
    </style>
</head>
<body>
<div>
    <p>
        <?= $text ?>
    </p>
</div>
</body>
</html>