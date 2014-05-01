<?php

// Depending on if you are in the loop, call the magic methods or use the global post object
$postID       = ($post) ? $post->ID         : get_the_ID();
$postOwnerID  = ($post) ? $post->owner->ID  : get_the_author_ID();

?>
<button class="<?= (socialnetwork::is_dug($postID)) ? 'undig' : 'dig' ?> <?= ($postOwnerID == get_current_user_id()) ? 'isOwner' : '' ?>" data-dig-count="<?= socialnetwork::count_digs($postID) ?>" data-dig-owner="<?= $postOwnerID ?>">
  <span></span>
  <?= (socialnetwork::is_dug($postID)) ? 'Undig it' : 'Dig it!' ?>
</button>

