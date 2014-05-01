<div class="portlet fans">
	<div class="overlay">
		<div class="caption">
			<h3>Friends</h3>
			<div class="number">(<?= count($friends) ?>)</div>
		</div>
	</div>
  <div class="explode">
    <h2>Friends (<?= count($friends) ?>)</h2>
    <div class="wrapper">
	    <ul>
	  <? if(count($friends)): ?>
	    <? foreach($friends as $friend): ?>
        <? $friend->owner = get_user_by('id', $friend->post_author) ?>
        <? $friend->owner->meta = get_user_meta($friend->post_author) ?>
	    	<li class="user">
	    		<img src="<?= user::get_user_image(167, 167, $friend->post_author) ?>" alt="<?= $friend->owner->display_name ?>" width="167" height="167" />
	    		<div class="overlay">
	    			<a class="template userName" href="/profile/<?= $friend->owner->user_login ?>"><?= $friend->owner->display_name ?></a>
	    			<span class="loc"><?= $friend->owner->meta['City'][0] ?>, <?= $friend->owner->meta['State'][0] ?></span>
	    			<div class="info">
	    				<span class="fanned">Friended: <span><?= relative_date($friend->post_date) ?></span></span>
	    			</div>
	    		</div>
	    	</li>
	    <? endforeach ?>
	  <? endif ?>
	    </ul>
    </div>
    <div class="portlet-close"></div>
  </div>
</div>

