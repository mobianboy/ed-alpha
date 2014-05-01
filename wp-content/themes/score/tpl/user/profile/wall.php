<?php
 
// Get shouts for this profile
$shouts = array(
  '3p'  => socialnetwork::get_following_shouts($user->ID),
  '1p'  => socialnetwork::get_shouts($user->ID),
);

?>
<div class="wall">
  <div class="topBar">
  <? if($user->ID == get_current_user_id() && count($shouts['3p'])): ?>
    <div class="followingTab active">Network Shouts</div>
  <? endif ?>
    <div class="activityTab <?= ($user->ID == get_current_user_id() && count($shouts['3p'])) ? '' : 'active' ?>"><?= ($user->ID == get_current_user_id()) ? 'My' : '';?> Shout Wall</div>
    <?// <div class="filter"><input id="userFilter" type="checkbox"><label for="userFilter">Show my posts only</label></div> ?>
  </div>
  <div class="dishPost <?= ($user->ID == get_current_user_id() && count($shouts['3p'])) ? 'hidden' : '' ?>">
    <div class="dishHead">
      <h3>Network Shouts</h3>
      <?/*
      <div class="attach">
      <div class="photo">Add Photo</div>
      <div class="video">Add Video</div>
      </div>
      */?>
    </div>
    <div class="dishInput">
      <textarea class="shout" placeholder="What have you been listening to?"></textarea>
      <input type="button" class="post" value="Post" />
    </div>
  </div>
<? if($user->ID == get_current_user_id() && count($shouts['3p'])): ?>
  <div class="following">
    <ul class="shouts">
  <? if(count($shouts['3p'])): ?>
    <? foreach($shouts['3p'] as $shout): ?>
      <?= balls::get_balls_template(array(
        'post_type' => 'shout',
        'template'  => 'single',
        'content'   => $shout->ID,
      )) ?>
    <? endforeach ?>
  <? endif ?>
    </ul>
  </div>
<? endif ?>
  <div class="activity" <?= ($user->ID == get_current_user_id() && count($shouts['3p'])) ? 'style="display:none;"' : '' ?>>
    <ul class="shouts">
  <? if(count($shouts['1p'])): ?>
    <? foreach($shouts['1p'] as $shout): ?>
      <?= balls::get_balls_template(array(
        'post_type' => 'shout',
        'template'  => 'single',
        'content'   => $shout->ID,
      )) ?>
    <? endforeach ?>
  <? endif ?>
    </ul>
  </div>
</div>

