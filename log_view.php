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
    <div class="login">
        <h1 id="logmain">Logowanie</h1>
        <form method="post">
            <p id="log">Login</p>
            <input type="text" name="login" id="wpis"><br>
            <p id="log">Hasło</p>
            <input type="password" name="haslo" id="wpis"><br>
            <input type="submit" value="Zaloguj się" id="loginbutton">
        </form>
    </div>  
</body>
</html>
