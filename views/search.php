<?php
$query = isset($_GET['q']) ? urlencode($_GET['q']) : '';
$apiUrl = $CONFIG['jellyfin_url'] . "/Users/" . $CONFIG['user_id'] . "/Items?" .
          "SearchTerm=" . $query . 
          "&Recursive=true" .
          "&IncludeItemTypes=Movie,Series,MusicAlbum" .
          "&Limit=40" . // Don't overwhelm the Wii U
          "&api_key=" . $CONFIG['api_key'];

$response = file_get_contents($apiUrl);
$data = json_decode($response, true);
?>

<h2>Search Results for: <?php echo htmlspecialchars($_GET['q']); ?></h2>

<div class="grid-container">
    <?php if (empty($data['Items'])): ?>
        <p>No results found. Maybe try a different keyword?</p>
    <?php else: ?>
        <?php foreach ($data['Items'] as $item): ?>
            <?php 
                // Reuse your existing logic for $link and $imgUrl
                $type = $item['Type'];
                if ($type === 'Series') $link = "?view=Seasons&id=" . $item['Id'];
                elseif ($type === 'MusicAlbum') $link = "?view=Album&id=" . $item['Id'];
                else $link = "?view=Player&vidID=" . $item['Id'];
            ?>
            <a href="<?php echo $link; ?>" class="card">
                <img src="<?php echo $CONFIG['jellyfin_url']; ?>/Items/<?php echo $item['Id']; ?>/Images/Primary?width=150&quality=50">
                <div class="title-overlay"><?php echo $item['Name']; ?></div>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>