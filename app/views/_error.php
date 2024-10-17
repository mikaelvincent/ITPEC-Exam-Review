<?php
// Set the dynamic title based on the error title
$title = htmlspecialchars($errorTitle) . " | ITPEC Exam Review";
?>

<h1>
    <?= htmlspecialchars($errorTitle) ?>
</h1>
<p>
    <?= htmlspecialchars($message) ?>
</p>
