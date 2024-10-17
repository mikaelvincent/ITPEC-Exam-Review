<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>
            <?= htmlspecialchars($title ?? "ITPEC Exam Review") ?>
        </title>
        <?= $head ?? "" ?>
	</head>
	<body>
		<header>
			<h1>ITPEC Exam Review</h1>
		</header>
		<main>
            <?= $content ?>
        </main>
		<footer>
			<p>&copy; <?= date("Y") ?> ITPEC Exam Review </p>
		</footer>
        <?= $scripts ?? "" ?>
	</body>
</html>
