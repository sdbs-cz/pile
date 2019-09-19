<div class="text">
    <p class="intro"> This site is the sdbs pile, where we upload the stuff we consider important to the larger
        conceptual and thematic landscape of what we do: "confronting apathy", inter-subjectivity, the human right to
        self-determination, counter-culture and such...</p>
    <p class="intro czech">Tohle je hromádka zajímavýho materiálu co něco znamená v kontextu sdbs - budeme sem postupně
        dávat ty nejdůležitější nebo nejzajímavější věci, zatim se o tom ale nikde moc nešiřte.</p>
    <p class="intro sign">/-\</p>
</div>

<div class="text recent-additions">
    <h2>Recent additions</h2>
    <ul>
        <?php foreach (array_slice($recent_docs, 0, 5) as $doc): ?>
            <li>
                <a href="/?item=<?= $doc['ID'] ?>">
                    <?php if (!empty($doc['UploadedTime'])): ?>
                        <em>(<?= date("Y/m/d H:i:s", $doc['UploadedTime']); ?>)</em>
                    <?php endif; ?>
                    <?= $doc['Title'] ?>

                    <div class="recent-additions-desc">
                        <?= $doc['Description'] ?>
                    </div>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>