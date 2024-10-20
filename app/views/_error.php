<?php
// Set the dynamic title based on the error title
$title = htmlspecialchars($errorTitle) . " | ITPEC Exam Review"; ?>
<div class="row mb-5">
    <div class="col-md-8 col-xl-6 text-center mx-auto">
        <h1 class="fw-bold"><?= htmlspecialchars($errorTitle) ?></h1>
        <?php if ($code === 404): ?>
            <p class="lead">The page you are looking for could not be found. Please check the URL or return to the <a href="/">home page</a>.</p>
        <?php elseif ($code === 500): ?>
            <p class="lead">An internal server error has occurred. Please try again later or contact support if the problem persists.</p>
        <?php elseif ($code === 401): ?>
            <p class="lead">Unauthorized access. Please log in to access this page.</p>
        <?php else: ?>
            <p class="lead">An unexpected error has occurred. Please try again later.</p>
        <?php endif; ?>
    </div>
</div>
