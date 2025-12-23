<?php
$albumId = $_GET['id'];

// 1. Get Album Metadata
$albumUrl = $CONFIG['jellyfin_url'] . "/Users/" . $CONFIG['user_id'] . "/Items/" . $albumId . "?api_key=" . $CONFIG['api_key'];
$album = json_decode(file_get_contents($albumUrl), true);

// 2. Get the Tracks
// Add &Limit=300 to ensure you get the whole album
$tracksUrl = $CONFIG['jellyfin_url'] . "/Users/" . $CONFIG['user_id'] . "/Items?ParentId=" . $albumId . "&SortBy=SortName&Limit=300&api_key=" . $CONFIG['api_key'];
$tracksData = json_decode(file_get_contents($tracksUrl), true);
?>

<div class="album-header">
    <img src="<?php echo $CONFIG['jellyfin_url']; ?>/Items/<?php echo $albumId; ?>/Images/Primary?width=300&quality=60" class="album-art">
    <div class="album-details">
        <h1><?php echo $album['Name']; ?></h1>
        <p><?php echo isset($album['AlbumArtist']) ? $album['AlbumArtist'] : 'Unknown Artist'; ?></p>
    </div>
</div>

<div class="tracklist" id="playlist">
    <?php foreach ($tracksData['Items'] as $index => $track): ?>
        <div class="track-item" 
             data-id="<?php echo $track['Id']; ?>" 
             data-name="<?php echo addslashes($track['Name']); ?>"
             onclick="playTrack(this)">
            <span class="track-number"><?php echo $index + 1; ?></span>
            <span class="track-name"><?php echo $track['Name']; ?></span>
        </div>
    <?php endforeach; ?>
</div>

<div id="music-player-bar">
    <strong id="now-playing">Select a track</strong>
    <audio id="audio-element" controls style="width: 100%;"></audio>
</div>

<script type="text/javascript">
    function playTrack(element) {
        currentTrackElement = element;
        var id = element.getAttribute('data-id');
        var name = element.getAttribute('data-name');
        
        var audio = document.getElementById('audio-element');
        var display = document.getElementById('now-playing');
        
        // Force MP3 for Wii U stability
        var streamUrl = "<?php echo $CONFIG['jellyfin_url']; ?>/Audio/" + id + "/stream.mp3?api_key=<?php echo $CONFIG['api_key']; ?>";
        
        display.innerHTML = "Playing: " + name;
        audio.src = streamUrl;
        audio.play();

        // Visual feedback: Remove active class from others, add to this one
        var trackNumberSpan = element.querySelector('.track-number');
        var items = document.getElementsByClassName('track-item');
        for(var i=0; i<items.length; i++) {
            items[i].style.background = "";
            items[i].style.color = ""; 
            var tn = items[i].querySelector('.track-number');
            if (tn) tn.style.color = "";
        }
        element.style.background = "#0085ff";
        element.style.color = "#fff"; // Bg is blue which is like :active
        element.scrollIntoView({behavior: "smooth", block: "center"});
        // get it's tracknumber span
        trackNumberSpan.style.color = "#fff";
    }

    // The "Magic" Autoplay
    document.getElementById('audio-element').onended = function() {
        if (currentTrackElement) {
            var nextTrack = currentTrackElement.nextElementSibling;
            if (nextTrack && nextTrack.classList.contains('track-item')) {
                playTrack(nextTrack);
            } else {
                document.getElementById('now-playing').innerHTML = "Album Finished";
            }
        }
    };
</script>