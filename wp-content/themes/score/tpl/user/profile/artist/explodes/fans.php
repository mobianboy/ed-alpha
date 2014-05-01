<div class="portlet fans">
	<div class="overlay">
		<div class="caption">
			<h3>Fans</h3>
			<div class="number">(<?= count($fans) ?>)</div>
		</div>
	</div>
  <div class="explode">
    <h2>Fans (<a><?= count($fans) ?></a>)</h2>
    <div class="wrapper">
	    <ul>
	  <? if(count($fans)): ?>
	    <? foreach($fans as $fan): ?>
        <? $fan->owner = get_user_by('id', $fan->post_author) ?>
        <? $fan->owner->meta = get_user_meta($fan->post_author) ?>
	    	<li class="user">
	    		<img src="<?= user::get_user_image(167, 167, $fan->post_author) ?>" alt="<?= $fan->owner->display_name ?>" width="167" height="167" />
	    		<div class="overlay">
	    			<a class="template userName" href="/profile/<?= $fan->owner->user_login ?>"><?= $fan->owner->display_name ?></a>
	    			<span class="loc"><?= $fan->owner->meta['City'][0] ?>, <?= $fan->owner->meta['State'][0] ?></span>
	    			<div class="info">
	    				<span class="fanned">Fanned: <span><?= relative_date($fan->post_date) ?></span></span>
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

