<?php
// Fetch Latest Movies
$latestMoviesUrl = $CONFIG['jellyfin_url'] . "/Users/" . $CONFIG['user_id'] . "/Items/Latest?IncludeItemTypes=Movie&Limit=6&api_key=" . $CONFIG['api_key'];
$movies = json_decode(file_get_contents($latestMoviesUrl), true);

// Fetch Latest Shows
$latestSeriesUrl = $CONFIG['jellyfin_url'] . "/Users/" . $CONFIG['user_id'] . "/Items/Latest?IncludeItemTypes=Series&Limit=6&api_key=" . $CONFIG['api_key'];
$series = json_decode(file_get_contents($latestSeriesUrl), true);
// Set timezone if not in php.ini
date_default_timezone_set('America/Chicago'); 

$hour = date('H');
$month = date('n');
$day = date('j');

if ($month == 12 && $day == 25) {
    $greeting = "Merry Christmas";
} elseif ($hour >= 0 && $hour < 4) {
    $greeting = "dude go to fucking sleep";
} elseif ($hour < 12) {
    $greeting = "good morning!";
} elseif ($hour < 17) {
    $greeting = "afternoon.";
} else {
    $greeting = "Good evening.";
}
$cats = ["Samuel", "Shadow", "Phoebe", "Mochi", "Moe", "Pepperoni", "Pickles"];
$featuredCat = $cats[array_rand($cats)];

$cafeQuotes = [
    "The best (and only) Jellyfin frontend for the Wii U",
    "welcome! the cats REALLY love the stuff here, highly reccommend",
    "ENTRY NUMBER SEVENTEEN
    <br>DARK DARKER YET DARKER
    <br>THE DARKNESS KEEPS GROWING
    <br>THE SHADOWS CUTTING DEEPER
    <br>PHOTON READINGS NEGATIVE
    <br>THIS NEXT EXPERIMENT
    <br>SEEMS
    <br>VERY
    <br>VERY
    <br>INTERESTING
    <br>...
    <br>WHAT DO YOU TWO THINK?",
    "Welcome back to the Cafe.",
    "Today's featured feline is $featuredCat",
    "Welcome to the <a href='?view=cats'>Cat Cafe!</a>"
];
$subquote = $cafeQuotes[array_rand($cafeQuotes)];
?>

<div class="home-section">
    <h1 id="velkommen" style="margin-bottom: 0; color: #4e342e;">
        <?php echo $greeting; ?>!
    </h1>
    <p style="margin-top: 5px; font-style: italic; color: #795548;">
        <?php echo $subquote; ?>
    </p>
    
    <h2 class="section-title">Recently Brewed</h2>
    <div class="horizontal-scroll">
        <?php foreach ($movies as $item): ?>
            <a href="?view=Player&vidID=<?php echo $item['Id']; ?>" class="card poster">
                <img src="<?php echo $CONFIG['jellyfin_url']; ?>/Items/<?php echo $item['Id']; ?>/Images/Primary?width=150&quality=50">
                <div class="title-overlay"><?php echo $item['Name']; ?></div>
            </a>
        <?php endforeach; ?>
        <?php if (empty($movies)): ?>
            <p>No recent movies found. Time to add some fresh content!</p>
        <?php endif; ?>
    </div>
</div>

<div class="home-section">
    <h2 class="section-title">Fresh Grinds</h2>
    <div class="horizontal-scroll">
        <?php foreach ($series as $item): ?>
            <a href="?view=Seasons&id=<?php echo $item['Id']; ?>" class="card poster">
                <img src="<?php echo $CONFIG['jellyfin_url']; ?>/Items/<?php echo $item['Id']; ?>/Images/Primary?width=150&quality=50">
                <div class="title-overlay"><?php echo $item['Name']; ?></div>
            </a>
        <?php endforeach; ?>
        <?php if (empty($series)): ?>
            <p>No recent shows found. Time to add some fresh content!</p>
        <?php endif; ?>
    </div>
</div>

<div class="home-actions">
    <a href="?view=Collections" class="big-button">Full Library Menu</a>
</div>