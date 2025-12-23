<?php
$url = $CONFIG['jellyfin_url'] . "/Users/" . $CONFIG['user_id'] . "/Views?api_key=" . $CONFIG['api_key'];
$data = json_decode(file_get_contents($url), true);
?>

<div class="collection-list">
    <?php foreach ($data['Items'] as $folder): ?>
        <a href="?view=Library&parentId=<?php echo $folder['Id']; ?>&type=<?php echo $folder['CollectionType']; ?>" class="folder-card">
            <div class="folder-icon">📂</div>
            <span><?php echo $folder['Name']; ?></span>
        </a>
    <?php endforeach; ?>
</div>