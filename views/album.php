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
        <h1 class="wordmark"><?php echo $album['Name']; ?></h1>
        <p style="color: #00adef; font-weight: bold;"><?php echo isset($album['AlbumArtist']) ? $album['AlbumArtist'] : 'Unknown Artist'; ?></p>
    </div>
</div>

<div class="tracklist" id="playlist">
    <?php foreach ($tracksData['Items'] as $index => $track): ?>
        <div class="track-item" 
             data-id="<?php echo $track['Id']; ?>" 
             data-name="<?php echo addslashes($track['Name']); ?>"
             onclick="playTrack(this)">
            <span class="track-number"><?php echo sprintf('%02d', $index + 1); ?></span>
            <span class="track-name"><?php echo $track['Name']; ?></span>
        </div>
    <?php endforeach; ?>
</div>

<div id="music-player-bar">
    <div class="player-container">
        <audio id="audio-element" style="display:none;"></audio>
        <div class="song-info">
            <span id="current-track-name">Select a track...</span>
        </div>
        
        <div class="controls-wrapper">
            <button class="player-btn" id="play-pause">▶</button>
            <div class="progress-area">
                <span class="time">0:00</span>
                <input type="range" id="seek-bar" value="0" max="100">
                <span class="time">0:00</span>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
        // USE VAR ONLY - NO CONST/LET
        var audio = document.getElementById('audio-element');
        var playBtn = document.getElementById('play-pause');
        var trackDisplay = document.getElementById('current-track-name');

        function playTrack(element) {
            var id = element.getAttribute('data-id');
            var name = element.getAttribute('data-name');
            var streamUrl = "<?php echo $CONFIG['jellyfin_url']; ?>/Audio/" + id + "/stream.mp3?api_key=<?php echo $CONFIG['api_key']; ?>";
            
            trackDisplay.innerHTML = name;
            audio.src = streamUrl;
            audio.play();
            playBtn.innerHTML = "PAUSE";

            // Standard FOR loop for Wii U compatibility
            var items = document.getElementsByClassName('track-item');
            for (var i = 0; i < items.length; i++) {
                items[i].style.background = "white";
                items[i].style.color = "#444";
            }

            element.style.background = "#00adef";
            element.style.color = "#ffffff";
        }

        // Toggle Play/Pause
        playBtn.onclick = function() {
            if (audio.paused) {
                audio.play();
                this.innerHTML = "||";
            } else {
                audio.pause();
                this.innerHTML = "▶";
            }
        };

        // Update Seek Bar as music plays
        audio.ontimeupdate = function() {
            var percentage = (audio.currentTime / audio.duration) * 100;
            seekBar.value = percentage;
            
            // Update timestamps (optional)
            // You can add logic here to format minutes:seconds
        };

        // Seek logic
        seekBar.oninput = function() {
            var time = audio.duration * (this.value / 100);
            audio.currentTime = time;
        };

        // Autoplay next track
        audio.onended = function() {
            if (currentTrackElement) {
                var nextTrack = currentTrackElement.nextElementSibling;
                if (nextTrack) playTrack(nextTrack);
            }
        };
</script>