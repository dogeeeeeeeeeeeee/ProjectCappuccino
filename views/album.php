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
    <?php 
    // Ensure $items is actually the array from Jellyfin
    // It usually looks like $data['Items'] or just $items depending on your fetch
    $items = isset($tracksData['Items']) ? $tracksData['Items'] : []; 

    if (empty($items)) {
        echo "<div class='error'>No tracks found. Bummer.</div>";
    } else {
        for ($i = 0; $i < count($items); $i++): 
            $current = $items[$i];
            $nextId = isset($items[$i+1]) ? $items[$i+1]['Id'] : 'null';
            $duration = isset($current['RunTimeTicks']) ? floor($current['RunTimeTicks'] / 10000000) : 0;
    ?>
        <div class="track-item" 
            id="track-<?= $current['Id'] ?>"
            onclick="loadTrack('<?= $current['Id'] ?>', '<?= addslashes($current['Name']) ?>', <?= $duration ?>, '<?= $nextId ?>')">
            <?= $current['IndexNumber'] ?? ($i + 1) ?>. <?= htmlspecialchars($current['Name']) ?>
        </div>
    <?php 
        endfor; 
    } 
    ?>
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
    var audio = document.getElementById('audio-element');
    var playBtn = document.getElementById('play-pause');
    var trackDisplay = document.getElementById('current-track-name');
    var seekBar = document.getElementById('seek-bar');
    var poller = null;
    var currentTrackElement = null; // CRITICAL: Fix the undefined error
    var globalNextTrackId = null;

    function playTrack(audID) {
        var player = audio;
        var status = trackDisplay;
        
        status.innerText = "Transcoding track...";
        
        // 1. Ping the proxy to start the transcode
        var checkUrl = "backend/audio_proxy.php?audID=" + audID + "&check=true";
        
        var poller = setInterval(function() {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', checkUrl, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    clearInterval(poller);
                    status.innerText = "Playing!";
                    // 2. Point player to the real file
                    player.src = "backend/audio_proxy.php?audID=" + audID;
                    player.play();
                }
            };
            xhr.send();
        }, 3000);
    }

    function loadTrack(audID, name, dur, nextId) {
        // Reset state
        if (poller) clearInterval(poller);
        audio.pause();
        trackDisplay.innerText = "Transcoding " + name + "...";
        playBtn.innerText = "...";

        var checkUrl = "backend/audio_proxy.php?audID=" + audID + "&check=true";
        
        poller = setInterval(function() {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', checkUrl, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        clearInterval(poller);
                        trackDisplay.innerText = name;
                        playBtn.innerText = "⏸";
                        audio.src = "backend/audio_proxy.php?audID=" + audID;
                        audio.play();
                    } else if (xhr.status == 202) {
                    } else if (xhr.status !== 425) {
                        // Something actually went wrong
                        trackDisplay.innerText = "Error brewing track.";
                        clearInterval(poller);
                    }
                }
            };
            xhr.send();
        }, 2000);

        // Save the next ID for the gapless trigger
        globalNextTrackId = (nextId !== 'null') ? nextId : null;

        // Visual feedback: highlight the active track
        document.querySelectorAll('.track-item').forEach(el => el.classList.remove('active'));
        document.getElementById('track-' + audID).classList.add('active');
    }

    // Play/Pause Toggle
    playBtn.addEventListener('click', function() {
        if (audio.paused) {
            audio.play();
            this.innerText = "⏸";
        } else {
            audio.pause();
            this.innerText = "▶";
        }
    });

    audio.ontimeupdate = function() {
        var dur = audio.duration && isFinite(audio.duration) ? audio.duration : audio.dataset.targetDuration;
        
        // Update progress bar
        if (dur > 0) {
            var progress = (audio.currentTime / dur) * 100;
            document.getElementById('seek-bar').value = progress;
            document.querySelectorAll('.time')[0].innerText = formatTime(audio.currentTime);

            // GAPLESS TRIGGER: 15 seconds before the end
            if (dur - audio.currentTime < 15 && globalNextTrackId !== null) {
                preheatNextTrack(globalNextTrackId);
                // CRITICAL: Set it to null so we don't start 50 FFmpeg processes 
                // for the same song while we wait for the timer to tick down.
                globalNextTrackId = null; 
            }
        }
    };

    function preheatNextTrack(audID) {
        // Just a "fire and forget" ping to the proxy
        // This starts the FFmpeg process so the file is ready when the song ends
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'backend/audio_proxy.php?audID=' + audID + '&check=true', true);
        xhr.send();
    }

    function formatTime(s) {
        var mins = Math.floor(s / 60);
        var secs = Math.floor(s % 60);
        return mins + ":" + (secs < 10 ? "0" : "") + secs;
    }

    audio.onended = function() {
        // Find the current active track and click the next sibling
        var current = document.querySelector('.track-item.active');
        var next = current ? current.nextElementSibling : null;
        
        if (next) {
            next.click(); // This calls your loadTrack() function
        }
    };
</script>