<div class="portlet bio">
	<div class="overlay">
		<div class="caption">
			<h3>Bio</h3>
		</div>
	</div>
    <div class="explode">
    	<h2>Bio</h2>
    	<div class="wrapper">
	    	<div class="bio" <?= ($user->ID == get_current_user_id()) ? 'data-eip="textarea" data-cols="50" data-rows="16" data-field="Biography" data-content="'.$user->ID.'"' : '' ?>>
	    		<?= (!empty($user->meta['Biography'][0])) ? $user->meta['Biography'][0] : '(bio)' ?>
	    	</div>
	    	<div class="info">
	    		<ul>
	    			<li>
	    				<span class="label">Location</span>
              <span class="text" <?= ($user->ID == get_current_user_id()) ? 'data-eip="text" data-field="City" data-content="'.$user->ID.'"' : '' ?>><?= (!empty($user->meta['City'][0])) ? $user->meta['City'][0] : '(city)' ?>, </span>
	    				<span class="text" <?= ($user->ID == get_current_user_id()) ? 'data-eip="select" data-field="State" data-select="'.$states.'" data-content="'.$user->ID.'"' : '' ?>><?= (!empty($user->meta['State'][0])) ? $user->meta['State'][0] : '(state)' ?></span>
            </li>
            <li>
              <span class="label">Postal Code</span>
              <span class="text" <?= ($user->ID == get_current_user_id()) ? 'data-eip="text" data-field="postal_code" data-content="'.$user->ID.'"' : '' ?>><?= (!empty($user->meta['postal_code'][0])) ? $user->meta['postal_code'][0] : '(zip)' ?></span>
            </li>
            <hr>
	    			<li>
	    				<span class="label">Genre</span>
	    				<span class="text"<?= ($user->ID == get_current_user_id()) ? 'data-eip="select" data-field="main_genre" data-select="'.$genres.'" data-content="'.$user->ID.'"' : '' ?>><?= (!empty($user->meta['main_genre'][0])) ? $user->meta['main_genre'][0] : '(genre)' ?></span>
	    			</li>
            <li>
              <span class="label">Influences</span>
	    				<span class="text"<?= ($user->ID == get_current_user_id()) ? 'data-eip="text" data-field="artist_influences" data-content="'.$user->ID.'"' : '' ?>><?= (!empty($user->meta['artist_influences'][0])) ? $user->meta['artist_influences'][0] : '(influences)' ?></span>
            </li>
	    			<hr>
	    			<li>
	    				<span class="label">Website</span>
	    				<span class="text"><a <?= ($user->ID == get_current_user_id()) ? 'data-eip="text" data-field="Website" data-content="'.$user->ID.'"' : '' ?>><?= (!empty($user->meta['Website'][0])) ? $user->meta['Website'][0] : '(Website)' ?></a></span>
	    			</li>
	    		</ul>
	    	</div>
	    </div>
    	<div class="portlet-close"></div>
    </div>
</div>

