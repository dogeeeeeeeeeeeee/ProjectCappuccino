<?php
$vidID = $_GET['vidID'];
// We need the item details to get the title
$itemUrl = $CONFIG['jellyfin_url'] . "/Users/" . $CONFIG['user_id'] . "/Items/" . $vidID . "?api_key=" . $CONFIG['api_key'];
$item = json_decode(file_get_contents($itemUrl), true);
$ITEM_ID = $item['Id'];
?>

<div class="player-container">
    <div id="player-area">
        <div class="wiiu-loading-box">
            <div class="spinner"></div>
            <h2 id="status">dunno</h2>
            <p>This will take a moment. Please don't touch the GamePad.</p>
        </div>
    </div>
    
    <div class="player-info">
        <h1><?php echo $item['Name']; ?></h1>
        <p><?php echo isset($item['Overview']) ? nl2br(htmlspecialchars($item['Overview'])) : ''; ?></p>
        <a href="javascript:history.back()" class="nav-btn">Back to Library</a>
    </div>
</div>

<script>

// Use the GamePad 'B' button to go back
document.addEventListener('keydown', function(e) {
    if (e.keyCode === 27 || e.keyCode === 8) { // Escape or Backspace (Wii U 'B')
        window.history.back();
    }
});

var cats = [
    // ones we have now
    "Samuel",
    "Shadow",
    "Phoebe",
    "Mochi",
    "Moe",
    // :(
    "Pepperoni",
    "Pickles"
]

var messages = [
    "Contacting the café...",
    "Please wait, the video is transcoding...",
    "Petting RandomCatLol457...",
    "Feeding Garfield lasagna...",
];

function updateMessage() {
    // 1. Pick a random message
    var msg = messages[Math.floor(Math.random() * messages.length)];
    
    // 2. If it contains our placeholder, swap it out
    if (msg.indexOf('RandomCatLol457') !== -1) {
        var randomCat = cats[Math.floor(Math.random() * cats.length)];
        msg = msg.replace('RandomCatLol457', randomCat);
    }
    
    document.getElementById('status').innerHTML = msg;
}

function checkStatus() {
    var xhr = new XMLHttpRequest();

    xhr.open(
        'GET',
        'backend/stream_proxy.php?vidID=<?php echo $vidID; ?>&check=true',
        true
    );

    xhr.onreadystatechange = function () {
        if (xhr.readyState !== 4) {
            return; // not done yet, chill
        }

        if (xhr.status === 200) {
            // Success! The file exists now.
            document.getElementById('status').innerHTML = "Ready";
            document.getElementById('player-area').innerHTML =
                '<video controls autoplay style="width:100%">' +
                '<source src="backend/stream_proxy.php?vidID=<?php echo $vidID; ?>" type="video/mp4">' +
                '</video>';
        } else {
            // Still transcoding
            updateMessage(); // Change the text to keep the user entertained
            setTimeout(checkStatus, 5000);
        }
    };

    xhr.send(null);
}

checkStatus();
</script>