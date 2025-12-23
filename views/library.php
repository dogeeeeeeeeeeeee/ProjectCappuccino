<?php
$parentId = isset($_GET['parentId']) ? $_GET['parentId'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : ''; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 0;
$limit = 20;
$startIndex = $page * $limit;

// If we have a ParentID, we query its children. 
// If not, we are probably in the wrong view.
$apiUrl = $CONFIG['jellyfin_url'] . "/Users/" . $CONFIG['user_id'] . "/Items?" .
          "ParentId=" . $parentId . 
          "&Recursive=true" . 
          "&StartIndex=" . $startIndex . 
          "&Limit=" . $limit . 
          "&Fields=PrimaryImageAspectRatio,SortName" .
          "&api_key=" . $CONFIG['api_key'];

// If it's a "Music" library, Jellyfin sometimes wants 'MusicAlbum' specifically
if ($type === 'music') {
    $apiUrl .= "&IncludeItemTypes=MusicAlbum";
}

$response = file_get_contents($apiUrl);
$data = json_decode($response, true);

$data = json_decode($response, true);
$totalItems = isset($data['TotalRecordCount']) ? $data['TotalRecordCount'] : 0;
?>

<div class="grid-container">
    <?php if (empty($data['Items'])): ?>
        <p>This folder is empty or the Wii U is being a brat.</p>
    <?php endif; ?>

    <?php foreach ($data['Items'] as $item): ?>
        <?php 
            // Determine the next step based on what the item actually IS
            $itemType = $item['Type'];
            
            if ($itemType === 'Series') {
                $link = "?view=Seasons&id=" . $item['Id'];
            } elseif ($itemType === 'MusicAlbum') {
                $link = "?view=Album&id=" . $item['Id'];
            } elseif ($itemType === 'Movie' || $itemType === 'Episode' || $itemType === 'MusicVideo') {
                $link = "?view=Player&vidID=" . $item['Id'];
            } else {
                // If it's just another folder/collection
                $link = "?view=Library&parentId=" . $item['Id'];
            }
            $primaryImage = isset($item['ImageTags']['Primary']) 
    ? $CONFIG['jellyfin_url'] . "/Items/" . $item['Id'] . "/Images/Primary?width=200&quality=50"
    : "assets/no-poster.png"; // Have a local dummy image ready
        ?>
        <a href="<?php echo $link; ?>" class="card">
            <img src="<?php echo $primaryImage; ?>" alt="<?php echo $item['Name']; ?>">
            <div class="title-overlay"><?php echo $item['Name']; ?></div>
        </a>
    <?php endforeach; ?>
</div>

<div class="pagination">
    <?php if ($page > 0): ?>
        <a href="?view=Library&parentId=<?php echo $parentId; ?>&type=<?php echo $type; ?>&page=<?php echo $page - 1; ?>" class="page-btn">
            &laquo; Back
        </a>
    <?php endif; ?>

    <span class="page-info">
        Page <?php echo ($page + 1); ?> of <?php echo ceil($totalItems / $limit); ?>
    </span>

    <?php if (($startIndex + $limit) < $totalItems): ?>
        <a href="?view=Library&parentId=<?php echo $parentId; ?>&type=<?php echo $type; ?>&page=<?php echo $page + 1; ?>" class="page-btn">
            Next &raquo;
        </a>
    <?php endif; ?>
</div>