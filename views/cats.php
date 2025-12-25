<?php
$felines = [
    ["name" => "Samuel", "status" => "active", "desc" => "Fatass"],
    ["name" => "Shadow", "status" => "active", "desc" => "Yo socrates, it's a fucking cat"],
    ["name" => "Phoebe", "status" => "active", "desc" => "Professionally fluffy."],
    ["name" => "Mochi", "status" => "new", "desc" => "Sweet once you know her, but otherwise... no. The yang to Moe's yin."],
    ["name" => "Moe", "status" => "new", "desc" => "The yin to Mochi's yang. Very social."],
    ["name" => "Pepperoni", "status" => "rip", "desc" => "The dev's first tuxedo. Tumor."],
    ["name" => "Pickles", "status" => "rip", "desc" => "Very shaky girl, really missed though. :("]
];
?>

<div class="cats-container">
    <h1>The dev's cats</h1>
    <div class="cats-grid">
        <?php foreach ($felines as $cat): ?>
            <div class="cat-card <?php echo $cat['status']; ?>">
                <div class="cat-avatar">
                    <span><?php echo substr($cat['name'], 0, 1); ?></span>
                </div>
                <h3><?php echo $cat['name']; ?></h3>
                <p><?php echo $cat['desc']; ?></p>
                <?php if ($cat['status'] === 'rip'): ?>
                    <span class="rip-tag">Rest in Peace</span>
                <?php endif; ?>
                <?php if ($cat['status'] === 'new'): ?>
                    <span class="new-tag">This is our newest member!</span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>