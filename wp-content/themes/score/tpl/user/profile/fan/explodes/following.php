<div class="portlet following">
	<div class="overlay">
		<div class="caption">
			<h3>Following</h3>
			<div class="number">(<?= count($following) ?>)</div>
		</div>
	</div>
  <div class="explode">
    <h2>Following (<a><?= count($following) ?></a>)</h2>
    <div class="wrapper">
	    <ul>
	  <? if(count($following)): ?>
	    <? foreach($following as $follow): ?>
        <? $follow->owner = get_user_by('id', $follow->post_content) ?>
        <? $follow->owner->meta = get_user_meta($follow->post_content) ?>
	    	<li class="ueachser">
	    		<img src="<?= user::get_user_image(167, 167, $follow->post_content) ?>" alt="<?= $follow->owner->display_name ?>" width="167" height="167" />
	    		<div class="overlay">
	    			<a class="template userName" href="/profile/<?= $follow->owner->user_login ?>"><?= $follow->owner->display_name ?></a>
	    			<span class="loc"><?= $follow->owner->meta['City'][0] ?>, <?= $follow->owner->meta['State'][0] ?></span>
	    			<div class="info">
	    				<span class="fanned">Followed: <span><?= relative_date($follow->post_date) ?></span></span>
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

