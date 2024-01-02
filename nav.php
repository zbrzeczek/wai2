
    <nav class="navbar">
        <ul>
            <?php if (isset($_SESSION['user'])): ?> 
                <li><a href="logout">Wylogowanie</a></li>
            <?php else: ?>
                <li><a href="log">Logowanie</a></li>
                <li><a href="reg">Rejestracja</a></li>
            <?php endif ?>
        </ul>
    </nav>