<?php

// Process external links
$pattern = "~(http://[^\s]*)~";
$replace = '<a href="$1" target="_blank">$1</a>';
if(preg_match($pattern, $post->post_content)) {
  $post->post_content = preg_replace($pattern, $replace, $post->post_content);
}

?>
<li class="comment" data-post-type="comment" data-content="<?= $post->ID ?>">
  <div class="edit hidden">
    <? if($post->parent->owner->ID == get_current_user_id() || $post->owner->ID == get_current_user_id()): ?>
      <span class="gear"></span>
      <ul>
        <li class="delete">Delete</li>
        <li class="report locked">Report</li>
      </ul>
    <? endif ?>
  </div>
  <img src="<?= user::get_user_image(50, 50, $post->owner->ID) ?>" alt="<?= $post->owner->display_name ?>" class="userThumb" height="50" width="50" />
  <div class="content">
    <h4><a class="userName template" href="/profile/<?= $post->owner->user_login ?>"><?= $post->owner->display_name ?></a> - <?= relative_date($post->post_date) ?></h4>
    <p class="content"><?= $post->post_content ?></p>
    <? include(get_template_directory().'/tpl/dig/single.php') ?>
  </div>
</li>

