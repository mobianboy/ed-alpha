<? if(count($users)): ?>
  <? foreach($users as $user): ?>
    <li class="user" id="<?= $user->ID ?>"  data-content="<?= $user->ID ?>" data-post-type="user" data-template="single" href="/profile/<?= $user->data->user_login ?>">
      <img src="<?= user::get_user_image(188, 188, $user->ID, TRUE) ?>" width="188" height="188" />
      <div class="metaBox">
        <div class="meta">
          <a class="title template" href="/profile/<?= $user->data->user_login ?>"><?= $user->data->display_name ?></a>
          <div class="loc"><?= user::get_formatted_location($user->ID) ?></div>
          <hr />
          <div class="profileType">
            <span class="label">Profile Type:</span>
            <span><?= ucfirst($user->data->meta['profile_type'][0]) ?></span>
          </div>
          <div class="preferredGenre">
            <span class="label">Preferred Genre:</span>
            <span><?= $user->data->meta['main_genre'][0] ?></span>
          </div>
          <hr />
        <? $song = user::last_song_rated($user->ID) ?>
        <? if($song): ?>
          <div class="lastRated">
            <div class="label">Last Song Rated:</div>
            <div class="wrapper">
              <img class="left" src="<?= song::get_song_image($song->ID, 25, 25) ?>" width="25" height="25" />
              <div class="artist"><?= $song->owner->display_name ?></div>
              <div class="songName"><?= $song->post_title ?></div>
            </div>
          </div>
        <? endif ?>
        </div>
      </div>
    </li>
  <? endforeach ?>
<? endif ?>

