<?php

// Get list of conversations involving the viewing user
$threads = message::get_conversations();

// Get relationships for message contact list
$follows = socialnetwork::get_relationships();

?>
<section class="message widget">
  <div class="wrapper" id="the_message">
    <h2>Messages</h2>
    <div class="context">
	<div class="threads">
	      <ul>
	        <li class="compose"><span>New Message</span></li>
      <? if(count($threads)): ?>
        <? foreach($threads as $key => $thread): ?>
          <? include('convo.php') ?>
	      <? endforeach ?>
	    <? endif ?>
	      </ul>
	    </div>
	    <div class="conversation">
        <div class="userSearch">
          <span>To:</span>
          <select class="searchRel" name="searchRel" id="searchRel" data-placeholder="---Select---" multiple>
          <? if(count($follows)): ?>
            <? foreach($follows as $key => $value): ?>
              <option value="<?= $key ?>"><?= $value ?></option>
            <? endforeach ?>
          <? endif ?>
          </select>
          <div class="relDisplay">
            <?/*
            <!-- Start with following, then followed by -->
            <!-- Break -->
            <!-- Closest suggested not following/followed -->
            */?>
          </div>
          <div class="close">Close</div>
        </div>
        <div class="default">No conversation selected</div>
	      <div class="reply">
	      	<input type="text" placeholder="" />
	      	<div class="button">Send</div>
	      </div>
	    </div>
    </div>
    <div class="widgetClose"></div>
  </div>
</section>

