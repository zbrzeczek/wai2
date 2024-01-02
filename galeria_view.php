<!DOCTYPE html>
<html>
<head>
    <title>Produkty</title>
    <link rel="stylesheet" href="static/css/styles.css"/>

</head>
<body>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="info">
            <h2><?= $_SESSION['error']?></h2>
        </div>
    <?php endif; ?>
    <header> 
        <h1><b>Zdjęcia sportów</b></h1>
    </header>

    <?php include('partial/nav.php');?>

    <div class="main">
        <?php foreach($photos as $photo): ?>
            <a href="<?= htmlspecialchars($photo['watermark']) ?>" target="_blank">
                <img src="<?= htmlspecialchars($photo['thumbnail']) ?>" class="thumbnail">
            </a>
            <?php
            $cutString = substr($photo['thumbnail'], 0, -4); 
            $opis = get_zdj($cutString);
            ?>
            <p><b>Tytul: <?= $opis['tytul'] ?></b></p>
            <p>Autor: <?= $opis['autor'] ?></p>
        <?php endforeach; ?>
        </br>
        <p>Strony:</p>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>"><?= $i ?></a>
        <?php endfor; ?>

        <a href="img" id="add">nowe zdjecie</a>
    </div>

</body>
</html>
