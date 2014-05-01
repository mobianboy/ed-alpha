<?php

// Get current session user object
$user = get_user_by('id', get_current_user_id());

// Get user meta data
$user->meta = get_user_meta($user->ID);

// Get user social relationships (follow and/or friend)
$follows = socialnetwork::get_relationships();

// Parse Block list
$blocks = explode(',', $user->meta['block_list'][0]);

?>
<h1><span></span>Account Settings</h1>

<section class="profVisibility">
  <div class="label">Profile Visibility:</div>
  <div class="options">
    <select class="profVisibilitySelect" data-placeholder="Visibility">
      <option value="public" <?= ($user->meta['profile_visibility'][0] == 'public') ? 'selected' : '' ?>>Everyone</option>
      <option value="network" <?= ($user->meta['profile_visibility'][0] == 'network') ? 'selected' : '' ?>>My Network</option>
      <option value="extended" <?= ($user->meta['profile_visibility'][0] == 'extended') ? 'selected' : '' ?>>Extended Network</option>
      <option value="private" <?= ($user->meta['profile_visibility'][0] == 'private') ? 'selected' : '' ?>>Private</option>
    </select>
  </div>
  <div class="help hidden">
    <button class="helpBtn">Help<span></span></button>
    <p>Profile Visibility changes who can see and search for you within the members gallery. It will also affect who can see your profile when they navigate to it using your direct profile link.</p>
    <dl>
      <dt>Everyone</dt>
        <dd>Anyone can view your profile.</dd>
      <dt>My Network</dt>
        <dd>Only people you are already connected to will be able to see your profile.</dd>
      <dt>Extended Network</dt>
        <dd>People you are connected to and the people they are connected to will be able to see your profile</dd>
      <dt>Private</dt>
        <dd>No one will be able to find your profile, true ninja status.</dd>
    </dl>
  </div>
</section>

<section class="blockList">
  <div class="label">Block List:</div>
  <div class="options">
    <p>Block communication from people on&nbsp;<a class="openBlockList">This List</a></p>
  </div>
  <div class="help hidden">
    <button class="helpBtn">Help<span></span></button>
    <p>Aside from Profile Visibility you can block communication from specific users you are already connected too. Anyone on this list will be unable to send you a message or shout. Blocked users will also be unable to comment on your posts.</p>
  </div>
</section>

<section class="allowComments">
  <div class="label">Allow Comments on:</div>
  <div class="options">
    <input  id="comMusicChk"  type="checkbox" name="commentsOn" value="song" <?= ($user->meta['comments_song'][0] == 'true') ? 'checked' : '' ?> />
    <label for="comMusicChk">Music</label>
    <input  id="comImagesChk" type="checkbox" name="commentsOn" value="image" <?= ($user->meta['comments_image'][0] == 'true') ? 'checked' : ''  ?>/>
    <label for="comImagesChk">Images</label>
    <input  id="comVideoChk"  type="checkbox" name="commentsOn" value="video" <?= ($user->meta['comments_video'][0] == 'true') ? 'checked' : '' ?>/>
    <label for="comVideoChk">Video</label>
  </div>
  <div class="help hidden">
    <button class="helpBtn">Help<span></span></button>
    <p>Allow others to comment on your Music, Images, and/or Videos. Unchecking these boxes will disable comments for that particular media type.</p>
  </div>
</section>

<section class="emailNotifications">
  <div class="label">Email Notifications:</div>
  <div class="options">
    <input  id="eNoteNotesChk" type="checkbox" name="emailNotes" value="note" <?= ($user->meta['email_note'][0] == 'true') ? 'checked' : '' ?>/>
    <label for="eNoteNotesChk">Notes</label>
    <input  id="eNoteMesgChk"  type="checkbox" name="emailNotes" value="msg" <?= ($user->meta['email_msg'][0] == 'true') ? 'checked' : '' ?>/>
    <label for="eNoteMesgChk">Messages</label>
  </div>
  <div class="help hidden">
    <button class="helpBtn">Help<span></span></button>
    <p>We will send you email notifications to keep you up to date with what’s happening on your Eardish profile. Don’t like us spamming your inbox?! Then uncheck away!</p>
  </div>
</section>

<section class="changePassword">
  <div class="label">Change Password:</div>
  <div class="options">
		<? include_once('tooltipForgotPass.php') ?>
    <a class="forgotPass">Forgot Password?</a>
    <input type="password" class="currPass" placeholder="Current Password" />
    <input type="password" class="newPass" placeholder="New Password" />
    <input type="password" class="confirmNewPass" placeholder="Confirm New Password" />
    <button class="submitPass btn">Change Password</button>
    <div class="passReq">All passwords are required to have six (6) characters and at least one (1) of each of the following:
      <ul>
        <li>A capital letter, A - Z</li>
        <li>A number, 0 - 9</li>
        <li>A special character, e.g. ! @ # $ & * etc.</li>
      </ul>
    </div>
  </div>
  <div class="help hidden">
    <button class="helpBtn">Help<span></span></button>
    <p>Feel free to change your password! Remember, there are specific requirements to keep your password secure. Be safe! Never give your password to anyone!</p>
  </div>
</section>

<? /* COMING SOON
<section class="changeEmail locked">
  <div class="label">Change Email</div>
  <div class="options">
    <p class="currEmail">Current Email: <span class="email"><?= 'johnsmith@domain.com' // TODO ?></span></p>
    <input type="text" class="newEmail" placeholder="New Email" />
    <input type="text" class="confirmNewEmail" placeholder="Confirm New Email" />
    <button class="submitEmail btn">Change Email</button>
  </div>
  <div class="help hidden">
    <button class="helpBtn">Help<span></span></button>
    <p>Help text. This is where the help text goes. It will tell the person about what the setting is and does. This is more text to fill up this container. Keep writing, is it enough? NO! Nothing is ever enough! Don't stop believin'</p>
  </div>
</section>

<section class="language locked">
  <div class="label">Language:</div>
  <div class="options">
    <select class="languageSelect" data-placeholder="Language">
      <option selected>English</option>
      <option>French</option>
      <option>Latin</option>
      <option>Dothraki</option>
    </select>
  </div>
  <div class="help hidden">
    <button class="helpBtn">Help<span></span></button>
    <p>Help text. This is where the help text goes. It will tell the person about what the setting is and does. This is more text to fill up this container. Keep writing, is it enough? NO! Nothing is ever enough! Don't stop believin'</p>
  </div>
</section>

<section class="explicit locked">
  <div class="label">Explicit Content:</div>
  <div class="options">
    <input  id="explMusicChk"  type="checkbox" name="explicit" value="music" <?= (TRUE) ? 'checked' : '' // TODO ?>/>
    <label for="explMusicChk">Music</label>
    <input  id="explImagesChk" type="checkbox" name="explicit" value="images" <?= (TRUE) ? 'checked' : '' // TODO ?>/>
    <label for="explImagesChk">Images</label>
    <input  id="explVideoChk"  type="checkbox" name="explicit" value="video" <?= (TRUE) ? 'checked' : '' // TODO ?>/>
    <label for="explVideoChk">Video</label>
  </div>
  <div class="help hidden">
    <button class="helpBtn">Help<span></span></button>
    <p>Help text. This is where the help text goes. It will tell the person about what the setting is and does. This is more text to fill up this container. Keep writing, is it enough? NO! Nothing is ever enough! Don't stop believin'</p>
  </div>
</section>
END COMMING SOON */?>
<div class="popups hidden">
	<div class="privacyList hidden">
    <button class="close">Close</button>
    <h2>Custom Privacy List</h2>
    <var><?= $user->meta['block_list'][0] ?></var>
    <select class="blockList" data-placeholder="Forbid Follow of these people" multiple>
    <? if(count($follows)): ?>
      <? foreach($follows as $key => $value): ?>
        <option value="<?= $key ?>" <?= (in_array($key, $blocks)) ? 'selected' : '' ?>><?= $value ?></option>
      <? endforeach ?>
    <? endif ?>
    </select>
    <div class="buttons">
      <button class="save red">Save List</button>
      <button class="cancel">Cancel</button>
		</div>
	</div>
</div>
