<?php

// Process external links
$pattern = "~(http://[^\s]*)~";
$replace = '<a href="$1" target="_blank">$1</a>';
if(preg_match($pattern, $post->post_content)) {
  $post->post_content = preg_replace($pattern, $replace, $post->post_content);
}

?>
<li class="shout <?= ($hide) ? 'hidden' : '' ?>" data-post-type="shout" data-content="<?= $post->ID ?>">
  <img src="<?= user::get_user_image(50, 50, $post->owner->ID) ?>" alt="<?= $post->owner->display_name ?>" class="userThumb" height="50" width="50" />
  <div class="content">
    <h4>
    <? if($post->post_author == get_current_user_id() || $post->post_parent == get_current_user_id()): ?>
      <a class="template" href="/profile/<?= $post->owner->user_login ?>"><?= $post->owner->display_name ?></a> <?= ($post->post_author != $post->post_parent) ? 'shouted at <a href="/profile/'.user::get_user($post->post_parent)->user_login.'">'.user::get_user($post->post_parent)->display_name.'</a>' : 'shouted' ?> - <?= relative_date($post->post_date) ?>
    <? else: ?>
      <a class="template" href="/profile/<?= $post->owner->user_login ?>"><?= $post->owner->display_name ?></a> <?= ($post->post_author != $post->post_parent) ? 'shouted at <a href="/profile/'.user::get_user($post->post_parent)->user_login.'">'.user::get_user($post->post_parent)->display_name.'</a>' : 'shouted' ?> - <?= relative_date($post->post_date) ?>
    <? endif ?>
    </h4>
    <p><?= $post->post_content ?></p>
    <div class="actions">
      <? include(get_template_directory().'/tpl/dig/single.php') ?>
      <?// <a>Dish Back</a> ?>
    </div>
    <div class="comments" data-content="<?= $post->ID ?>" data-post-type="comment" data-action="template" data-template="archive">
      <?= balls::get_balls_template(array(
        'post_type' => 'comment',
        'template'  => 'archive',
        'content'   => $post->ID,
      )); ?>
    </div>
  </div>
<? if($post->post_author == get_current_user_id() || $post->post_parent == get_current_user_id()): ?>
  <div class="edit hidden">
    <span class="gear"></span>
    <ul>
      <li class="delete">Delete</li>
      <?// li class="edit">Edit</li ?>
      <li class="report locked">Report</li>
    </ul>
  </div>
<? endif ?>
</li>

