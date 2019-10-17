<?php /** @noinspection PhpUndefinedVariableInspection */ ?>

<head>
    <title>LABEL FOR <?= $doc["Title"] ?></title>
    <!--suppress CssNoGenericFontName -->
    <style>
        body {
            font-family: prociono;
            font-size: 14px;
        }

        h1, h2, h3 {
            margin: 0;
            padding: 0;
        }

        h1, h2 {
            font-size: 24px;
        }

        .exlibris {
            text-align: center;
            margin-bottom: 10px;
        }

        .label-outer {
            width: 100%;
            border: 5px solid black;
            padding: 10px;
        }

        .label-stamp-outer {
            float: left;
            width: 150px;
        }

        #pile-logo {
            width: 100%;
            margin: 10px 0;
        }

        .label-stamp {
            border: 2px solid black;
            margin-right: 10px;
        }

        .label-stamp-subtitle {
            text-align: center;
            width: 100%;
            margin-bottom: 5px;
            font-size: 20px;

            margin-top: -9px; /* prociono specific */
        }

        .label-text {
            float: right;
        }

        .label-title {
            margin: 0;
            padding: 0;
        }

        .label-otherinfo {
            font-size: 14px;
        }

        .label-description {
            text-align: justify;
            margin-top: 0;
            padding-top: 0;
        }


        .label-footer {
            width: 100%;
            text-align: right;
        }

        .absolute-footer {
            width: 100%;
            position: absolute;
            text-align: center;
            bottom: 20px;
            left: 0;
        }
    </style>
</head>
<body>
<h1 class="exlibris">ex libris /-\ pile</h1>
<div class="label-outer">
    <div class="label-upper">
        <div class="label-stamp-outer">
            <div class="label-column label-stamp">
                <img id="pile-logo" src="assets/pile_300dpi.png" alt="/-\ Pile"/>
                <div class="label-stamp-subtitle">#<?= str_pad($doc["ID"], 4, "0", STR_PAD_LEFT); ?></div>
            </div>
        </div>
        <div class="label-column label-text">
            <h2 class="label-title"><?= $doc["Title"] ?></h2>
            <h3 class="label-otherinfo">
                <?php if ($doc["Author"]): ?>
                    By <?= $doc["Author"] ?>
                <?php endif; ?>
                <?php if ($doc["Published"]): ?>
                    <div class="label-otherinfo-date">(Published: <?= $doc["Published"] ?>)</div>
                <?php endif; ?>
            </h3>
            <p class="label-description"><?= $doc["Description"] ?></p>
        </div>
    </div>
    <div class="label-footer">
        <?php if (count($doc["tags"]) > 0): ?>
            <div class="label-footer-tags">Filed under:
                <?php
                for ($i = 0; $i < count($doc["tags"]); $i++) {
                    echo $doc["tags"][$i]["Name"];
                }
                ?>
            </div>
        <?php endif; ?>
        <div class="label-footer-url">
            Available at: https://pile.sdbs.cz/?item=<?= $doc["ID"] ?>
        </div>
    </div>
</div>
<div class="absolute-footer">
    read or share
</div>
</body>