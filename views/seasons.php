<?php
$seriesId = $_GET['id'];
$apiUrl = $CONFIG['jellyfin_url'] . "/Shows/" . $seriesId . "/Seasons?userId=" . $CONFIG['user_id'] . "&api_key=" . $CONFIG['api_key'];
$data = json_decode(file_get_contents($apiUrl), true);
$imageUrl = $CONFIG['jellyfin_url'] . "/Items/" . $season['Id'] . "/Images?api_key=" . $CONFIG['api_key'];
$images = json_decode(file_get_contents($imageUrl), true);
$primaryImage = isset($item['ImageTags']['Primary']) 
    ? $CONFIG['jellyfin_url'] . "/Items/" . $item['Id'] . "/Images/Primary?width=150&quality=50"
    : "assets/no-poster.png"; // Have a local dummy image ready
?>

<h2>Seasons</h2>
<div class="grid-container">
    <?php foreach ($data['Items'] as $season): ?>
        <a href="?view=Episodes&seriesId=<?php echo $seriesId; ?>&seasonId=<?php echo $season['Id']; ?>" class="card poster">
            <img src="<?php echo $CONFIG['jellyfin_url']; ?>/Items/<?php echo $season['Id']; ?><?php echo $primaryImage; ?>?width=150&quality=50" alt="<?php echo $season['Name']; ?>">
            <p><?php echo $season['Name']; ?></p>
        </a>
    <?php endforeach; ?>
</div>