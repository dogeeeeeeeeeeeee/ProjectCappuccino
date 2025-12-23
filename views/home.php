<?php
// Fetch Latest Movies
$latestMoviesUrl = $CONFIG['jellyfin_url'] . "/Users/" . $CONFIG['user_id'] . "/Items/Latest?IncludeItemTypes=Movie&Limit=6&api_key=" . $CONFIG['api_key'];
$movies = json_decode(file_get_contents($latestMoviesUrl), true);

// Fetch Latest Shows
$latestSeriesUrl = $CONFIG['jellyfin_url'] . "/Users/" . $CONFIG['user_id'] . "/Items/Latest?IncludeItemTypes=Series&Limit=6&api_key=" . $CONFIG['api_key'];
$series = json_decode(file_get_contents($latestSeriesUrl), true);
?>

<div class="home-section">
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