<?php
$title = "Q" . htmlspecialchars($question_number) . " | ITPEC Exam Review"; ?>

<h1>Question <?= htmlspecialchars($question_number) ?></h1>
<p>This is the page for Question <?= htmlspecialchars($question_number) ?> in the <?= htmlspecialchars($examset_name) ?> exam set of <?= htmlspecialchars($exam_name) ?>.</p>
