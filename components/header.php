    <header class="cafe-header">
        <div class="header-content">
            <a href="?view=home" class="logo"></a> <!-- using masks so everything would work with light or dark -->
            <nav class="top-nav">
                <form action="index.php" method="GET" style="display:inline;">
                    <input type="hidden" name="view" value="Search">
                    <input type="text" name="q" placeholder="Search..." class="search-input">
                    <button type="submit" class="nav-icon"><?php
                    if ($is3DS !== true) {
                        echo "<img src='assets/search.svg' alt='search'>";
                    }
                    ?>
                    </button>
                </form>
                <a href="?view=home" class="nav-icon"><?php
                    if ($is3DS !== true) {
                        echo "<img src='assets/home.svg' alt='home'>";
                    }
                    ?>
                </a>
            </nav>
        </div>
    </header>