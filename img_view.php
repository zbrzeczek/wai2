<!DOCTYPE html>
<html>
<head>
    <title>Edycja</title>
    <link rel="stylesheet" href="static/css/styles.css"/>
</head>
<body>
    <?php if ($_SESSION['error'] != '0'): ?>      
        <div class="info">
            <h2><?= $_SESSION['error'] ?></h2>
        </div>
    <?php endif; ?>
    <div class="main">
        <h2>Image Upload</h2>

        <form method="post" enctype="multipart/form-data">
            <label for="image">Zdjecie</label>
            <input type="file" name="image" accept="image/*" required><br/>
            <label for="tytul">Znak wodny (Obowiązkowe)</label>
            <input type="text" name="watermark" required/><br/>
            <label for="tytul">Tytul</label>
            <input type="text" name="tytul" required/><br/>
            <label for="tytul">Autor</label>
            <input type="text" name="autor" required/><br/>
            <input type="submit" value="zapisz"/>
        </form>
    </div>  
</body>
</html>
