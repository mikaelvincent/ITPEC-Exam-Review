<?php if (!empty($breadcrumbs)): ?>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-5">
            <?php foreach ($breadcrumbs as $index => $breadcrumb): ?>
                <?php if ($index < count($breadcrumbs) - 1): ?>
                    <li class="breadcrumb-item">
                        <a class="text-decoration-none" href="<?= htmlspecialchars($breadcrumb["path"]) ?>">
                            <span>
                                <?= htmlspecialchars($breadcrumb["title"]) ?>
                            </span>
                        </a>
                    </li>
                <?php else: ?>
                    <li class="breadcrumb-item active" aria-current="page">
                        <span>
                            <?= htmlspecialchars($breadcrumb["title"]) ?>
                        </span>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ol>
    </nav>
<?php endif; ?>
