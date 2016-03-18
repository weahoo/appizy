<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Appizy">
    <title>Appizy</title>
    <?php if ($libraries): ?>
        <?php foreach ($libraries as $library): ?>
            <?php print $library; ?>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($style): ?>

        <style type="text/css">
            <?php print $style; ?>
        </style>

    <?php endif; ?>
</head>
<body>
<?php if ($content): ?>
    <?php print $content; ?>
<?php endif; ?>

<?php if ($script) : ?>
    <script src="script.js"></script>
<?php endif; ?>
</body>
</html>