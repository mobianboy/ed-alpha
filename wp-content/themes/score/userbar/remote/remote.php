<?php

// Unread count for notes, msgs 
$unread = array(
  'message'       => message::get_unread(),
  'notification'  => notification::get_unread(),
);

// What type of user is currently in active session
$current_profile_type = get_user_meta(get_current_user_id(), 'profile_type', TRUE);

?>
<div id="bottom" class="drop_zone">
  <div class="sortable"> 
    <div class="defaultState <?= (playlist::count_library()) ? 'hidden' : '' ?>">
      <a class="template" href="/music/">Explore our Music to build up your library.</a> 
    </div>
    <? include_once('player.php') // Player ?>
    <? include_once('demoPlayer.php') // Demo Player ?>
    <div class="widgets">
    	<? include_once('shout.php') // Shout ?>
      <? include_once('message.php') // Messages ?>
			<? include_once('note.php') // Notes ?>
      <? include_once('contribute.php') // Contribute ?>
    </div>
    <div class="buttons">
      <div id="btn_shout" class="btn"></div>
      <div id="btn_message" class="btn"><div class="new"><?= ($unread['message']) ? $unread['message'] : '' ?></div></div>
      <div id="btn_notification" class="btn"><div class="new"><?= ($unread['notification']) ? $unread['notification'] : '' ?></div></div>
      <div id="btn_contribute" class="btn"></div>
    </div>
  </div>
</div>

