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
    <div class="login" id="lin">
        <h1 id="logmain">Rejestracja</h1>
        <form method="post">
            <p id="log">Login</p>
            <input type="text" name="login" id="wpis" required><br>
            <p id="log">Email</p>
            <input type="text" name="email" id="wpis" required><br>
            <p id="log">Hasło</p>
            <input type="password" name="haslo" id="wpis" required><br>
            <p id="log">Powtórz hasło</p>
            <input type="password" name="haslo2" id="wpis" required><br>
            <input type="submit" value="Rejestracja" id="loginbutton">
        </form>
    </div>
</body>
</html>
