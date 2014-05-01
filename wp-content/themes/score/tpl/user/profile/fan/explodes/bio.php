<div class="portlet bio">
	<div class="overlay">
		<div class="caption">
			<span class="number"></span>
			<h3>Bio</h3>
		</div>
	</div>
  <div class="explode">
    <h3>Bio</h3>
    <div class="wrapper">
	    <div class="bio short" <?= ($user->ID == get_current_user_id()) ? 'data-eip="textarea" data-field="fan bio" data-cols="40" data-rows="5" data-content="'.$user->ID.'"' : '' ?>>
	    	<?= (!empty($user->meta['fan bio'][0])) ? $user->meta['fan bio'][0] : '(bio)' ?>
	    </div>
	    <div class="info">
	    	<ul>
	    		<li>
	    			<span class="label">Location</span> 
	    			<span class="text" <?= ($user->ID == get_current_user_id()) ? 'data-eip="text" data-field="City" data-content="'.$user->ID.'"' : '' ?>><?= (!empty($user->meta['City'][0])) ? $user->meta['City'][0] : '(city)' ?></span>
	    			<span class="text" <?= ($user->ID == get_current_user_id()) ? 'data-eip="select" data-field="State" data-select="'.$states.'" data-content="'.$user->ID.'"' : '' ?>><?= (!empty($user->meta['State'][0])) ? $user->meta['State'][0] : '(state)' ?></span>
          </li>
          <li>
            <span class="label">Postal Code</span>
            <span class="text" <?= ($user->ID == get_current_user_id()) ? 'data-eip="text" data-field="postal_code" data-content="'.$user->ID.'"' : '' ?>><?= (!empty($user->meta['postal_code'][0])) ? $user->meta['postal_code'][0] : '(zip)' ?></span>
	    		</li>
	    		<li>
	    			<span class="label">Genre</span>
            <span class="text" <?= ($user->ID == get_current_user_id()) ? 'data-eip="select" data-field="main_genre" data-select="'.$genres.'" data-content="'.$user->ID.'"' : '' ?>><?= (!empty($user->meta['main_genre'][0])) ? $user->meta['main_genre'][0] : '(genre)' ?></span>
	    		</li>
	    		<li>
	    			<span class="label">Website</span>
            <span class="text" <?= ($user->ID == get_current_user_id()) ? 'data-eip="text" data-field="Website" data-content="'.$user->ID.'"' : '' ?>><?= (!empty($user->meta['Website'][0])) ? $user->meta['Website'][0] : '(website)' ?></span>
	    		</li>
	    	</ul>
	    </div>
	  </div>
    <div class="portlet-close"></div>
  </div>
</div>

