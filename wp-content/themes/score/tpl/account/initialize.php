<?php

/* Account set up on first login */

?>
<div class="popupWrap hidden">
  <div class="accInitPopup">
    <span class="close">Close</span>
    <h2>Initialize Account</h2>
    <p>
      Username must be minimum 5 characters, and can only have the special symbols: '_', '-' and '.'<br />
      Password must be 6 characters, contain one number, one uppercase letter, and a non-alphanumeric sybmol.<br />
      Fan: You have an appreciation for music and maybe even play an instrument.<br />
      Artist: You're so core you need to upload new music to share it with your fans.<br />
    </p>

    <label>UserName:</label>
    <input type="text" class="username" />
    <label>Type your Password:</label>
    <input type="password" class="password" />
    <label>Confirm Password:</label>
    <input type="password" class="passwordConfirm" />

    <p>I am a:</p>
    <input  id="fanRadio" type="radio" name="accType" value="fan" checked />
    <label for="fanRadio" class="radio">Fan</label>

    <input  id="artistRadio" type="radio" name="accType" value="artist" />
    <label for="artistRadio" class="radio">Artist</label>

    <button class="submit">Submit</button>
  </div>
</div>

