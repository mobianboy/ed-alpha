<?php 

if(isset($content)) {
  $content = (is_numeric($content)) ? $content : user::get_user_id_by_slug($content);
  $user = get_user_by('id', $content);
}

// Get list of videos for profile
// $photos = user::get_photos($user->ID);

?>
<div class="portlet photos">
  <div class="overlay">
    <div class="caption">
      <h3>Photos</h3>
      <div class="number">(<?//= count($videos) ?>)</div>
    </div>
  </div>
  <div class="explode">
    <h2>Photos</h2>
    <div class="wrapper">
    <? //if(count($photos)): ?>
      <ul>
      <? //foreach($photos as $photos): ?>
      <? for($i=0;$i<21;$i++): ?>
				<li class="photo" id="<?//= $photo->id ?>">
          <img src="<?//= photoSrc ?>" alt="" width="132" height="100" />
        <? if($user->ID == get_current_user_id()): // if is owner ?>
          <div class="delete">X</div>
        <? endif ?>
        </li>
      <? //endforeach ?>
			<? endfor ?>
      </ul>
    <? //else: ?>
      <!--<ul></ul>-->
    <? //endif ?>
    </div>
    <div class="portlet-close"></div>
  </div>
</div>
