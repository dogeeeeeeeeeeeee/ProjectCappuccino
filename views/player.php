<?php
$vidID = $_GET['vidID'];
// We need the item details to get the title
$itemUrl = $CONFIG['jellyfin_url'] . "/Users/" . $CONFIG['user_id'] . "/Items/" . $vidID . "?api_key=" . $CONFIG['api_key'];
$item = json_decode(file_get_contents($itemUrl), true);

// Construct the stream URL for the H.264 video
$streamUrl = $CONFIG['jellyfin_url'] . "/Videos/" . $vidID . "/stream.mp4?api_key=" . $CONFIG['api_key'] . "&Static=true&VideoCodec=h264&AudioCodec=aac";
?>

<div class="player-container">
    <video id="cafePlayer" controls autoplay name="media">
        <source src="<?php echo $streamUrl; ?>" type="video/mp4">
    </video>
    
    <div class="player-info">
        <h1><?php echo $item['Name']; ?></h1>
        <p><?php echo isset($item['Overview']) ? $item['Overview'] : ''; ?></p>
        <a href="javascript:history.back()" class="nav-btn">Back to Library</a>
    </div>
</div>

<script>
// Brutal Reality: The Wii U might need a nudge to start
var v = document.getElementById('cafePlayer');
v.play(); 

// Goofy Wii U hack: Use the GamePad 'B' button to go back
document.addEventListener('keydown', function(e) {
    if (e.keyCode === 27 || e.keyCode === 8) { // Escape or Backspace (Wii U 'B')
        window.history.back();
    }
});
</script>