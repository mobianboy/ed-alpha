<?php

// Include WP Libs
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-config.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-includes/wp-db.php');

// Prepare referrer link for redirect after login
$default_redirect = '/profile/';
$redirect = (strlen($_SERVER['REQUEST_URI']) > 1) ? preg_replace('~\?.*$~', '', $_SERVER['REQUEST_URI']) : $default_redirect;

// Process permalinks for theme routing
balls::balls_permalink();

// Generate random default TTL for testing
$ttl = balls::get_balls_ttl('template');

// Splash page tokenSignup
$is_token_signup = FALSE;

// BALLS Theme Stylesheets
$stylesheets = array(
  'eardish' => array(
    'dep' => FALSE,
    'ver' => '1.0',
    'med' => 'ALL',
  ),
  'global' => array(
    'dep' => FALSE,
    'ver' => '1.0',
    'med' => 'ALL',
  ),
  'userbar' => array(
    'dep' => FALSE,
    'ver' => '1.0',
    'med' => 'ALL',
  ),
  'dialog' => array(
    'dep' => FALSE,
    'ver' => '1.0',
    'med' => 'ALL',
  ),
  'account' => array(
    'dep' => FALSE,
    'ver' => '1.0',
    'med' => 'ALL',
  ),
  $template => array(
    'dep' => FALSE,
    'ver' => '1.0',
    'med' => 'ALL',
  ),
  $post_type => array(
    'dep' => FALSE,
    'ver' => '1.0',
    'med' => 'ALL',
  ),
);

// External Stylesheets
$ext_stylesheets = array(
  'source' => array(
    'url' => 'https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,300,200,600,700,200italic,300italic,700italic,600italic,400italic',
    'dep' => FALSE,
    'ver' => '1.0',
    'med' => 'ALL',
  ),
);

?>
<!DOCTYPE HTML>
<html>
  <head>
    <title>Eardish - Join The Evolution!</title>
    <meta name="branch" content="environment_config">
    <? wp_head() ?>
    <link rel="shortcut icon" href="http://<?= CDN ?>/images/favicon.ico" type="image/x-icon" />
  <? if(preg_match('~win~i', $_SERVER['HTTP_USER_AGENT'])): // if user is on windows, fallback on standard fonts for cleaner display ?>
    <style>
    <!--
      body{
        font-family: 'Source Sans Pro', Helvetica, Arial, "Lucida Grande", sans-serif;
      }
    -->
    </style>
  <? endif ?>
  </head>
  <body>
    <? if(is_user_logged_in()): ?>
      <? if(user::is_user_cpe() && user::is_recent_login()): ?>
        <? $note = notification::set_note_cpe(array(
          'type'      => 'cpe',
          'initiator' => 12,
          'recipient' => get_current_user_id(),
          'title'     => 'Download/License',
        )) ?>
      <? endif ?>
      <? include_once('userbar/remote/remote.php') ?>
      <nav>
        <? include_once('userbar/menu/menu.php') ?>
      </nav>
      <section class="ttl long" id="<?= $permalink ?>" >
        <?= balls::get_balls_template(array(
          'post_type' => $post_type,
          'template'  => $template,
          'content'   => $content,
          'page'      => $page,
          'orderby'   => $orderby,
          'order'     => $order,
          'tax'       => $tax,
          'tag'       => $tag,
          'loc_zip'   => $loc_zip,
          'loc_rad'   => $loc_rad,
          'search'    => $search,
        )) ?>
      </section>
      <div class="ballsFluffer hidden">
        <img src="http://<?= CDN ?>/images/loaderD.gif" alt="loading..." />
      </div>
      <? include_once('tpl/campaign/single.php') ?>
    <? else: // user is not logged in ?>
      <div class="splashOverride">
        <img src="http://<?= CDN ?>/images/eardishLIGHT.jpg" width="100%" height="100%" />
      </div> 
      <div class="credentials">
        <input type="text" name="username" size="20" id="username" placeholder="Email" tabindex="1"/>
        <input type="password" name="password" size="20" id="password" placeholder="Password" tabindex="2"/>
        <div class="button" tabindex="4">&gt; LOG IN</div> 
        <div class="rememberWrapper">
          <input type="checkbox" name="rememberMe" value="true" tabindex="3"/><div class="checkBox"></div>Remember Me
        </div>
				<div class="forgotPass">
					<a>Forgot Password?</a>
					<? include_once('tpl/account/tooltipForgotPass.php') ?>
					<div class="hidden">
						<? include_once('tpl/account/forgotPassword.php') ?>
					</div>
				</div>
      </div>
      <div class="logo">
        <img src="http://<?= CDN ?>/images/eardishLogo_red.svg" width="100%" height="100%" />
      </div>
      <div class="joinTheEvolution">
        Join The Evolution!
      </div>
      <div class="content">
        <ul class="points">
          <li>A vibrant, new Artist Development Community</li>
          <li>Funding for recordings, music videos, tours, and marketing</li>
          <li>No contracts, no costs, and no recoupment</li>
          <li>You retain all masters, publishing and ownership rights with no strings attached!</li>
        </ul>
        <div class="inviteOnly">
          Eardish is now in Alpha release by invitation only. Request your Artist <br/> invitation code by submitting your email below...
        </div>
      </div>
      <div class="submitEmail">
          <div class="getStarted">
            <? if($is_token_signup): ?>
              <img src="http://<?= CDN ?>/images/helloCircle.png" />
            <? else: ?>
              <img src="http://<?= CDN ?>/images/getStarted.png" />
            <? endif ?>
          </div>
          <input class="<?= $is_token_signup ? 'two' : '' ?>" type="text" name="email" size="20" id="email" placeholder="your email address"/>
          <input class="two <?= $is_token_signup ? '' : 'hidden' ?>" type="text" name="token" size="20" id="token" placeholder="your signup token" value="<?= $token ?>"/>
          <div class="button<?= $is_token_signup ? '2' : '' ?>">
            <? if($is_token_signup): ?>
              <img src="http://<?= CDN ?>/images/goButtonB.png" />
            <? else: ?>
              <img src="http://<?= CDN ?>/images/goButtonA.png" />
            <? endif ?>
          </div>
          <? /*<div class="continue">Already have a token? <a class="showTokenField"> -> Continue</a></div>
          <div class="noToken hidden">Oops I don't really have a token... <a class="backToPreReg">&lt;- Go back</a></div>*/ ?>
      </div>
      <div class="bottomBar">
        All Rights Reserved. <a class="tos">Terms and Conditions</a> | <a class="pp">Privacy Policy</a> | <a href="mailto:help@eardish.com">Contact Us</a>
      </div>
      <? include_once('tpl/account/initialize.php') ?>
   <? endif ?>
    <!--<div id="captcha">
      <div id="mc">
        <canvas id="mc-canvas"></canvas>
      </div>
    </div>-->
    <?//* TERMS OF SERVICE ?>
    <div class="tosPopover hidden">
      <? include_once('tpl/account/TOS.php') ?>
      <? include_once('tpl/account/PP.php') ?>
    </div>
    <?//*/ ?>
  </body>
  <script>
    var userSession = {
      id            : <?= get_current_user_id() ?>,
      name          : "<?= $current_user->data->display_name ?>",
      email         : "<?= $current_user->data->user_email ?>",
      profileType   : "<?= get_user_meta(get_current_user_id(), 'profile_type', TRUE) ?>",
      token         : "<?= $token ?>",
      forgotToken  : "<?= $forgot_token ?>",
      initialized   : "<?= get_user_meta(get_current_user_id(), 'initialized', TRUE) ?>",
      mfpServer     : "<?= MFP_HOST ?>",
      edHost        : "<?= $edhost ?>",
      get isLive() {return (/\.com/).test(userSession.edHost);},
			cdn           : "<?= CDN ?>",
      cdna          : "<?= CDNA ?>",
      cdni          : "<?= CDNI ?>",
      cdnv          : "<?= CDNV ?>"
    };
  </script>
  <?/* <script type="text/javascript" src="http://<?= $edhost ?>/wp-content/themes/score/js/lib/modernizr-2.5.3.min.js"></script> */?>
  <script type="text/javascript" data-main="http://<?= $edhost ?>/wp-content/themes/score/js/main.min" src="http://<?= $edhost ?>/wp-content/themes/score/js/lib/require.min.js"></script>
<? if($instance == 'prod'): ?>
  <script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
    ga('create', 'UA-41471578-1', 'eardish.com');
    ga('send', 'pageview');
  </script>
  <script>
    var _prum = [['id', '523bc144abe53d8934000000'],
    ['mark', 'firstbyte', (new Date()).getTime()]];
    (function() {
    var s = document.getElementsByTagName('script')[0]
    , p = document.createElement('script');
    p.async = 'async';
    p.src = '//rum-static.pingdom.net/prum.min.js';
    s.parentNode.insertBefore(p, s);
    })();
  </script>
<? endif ?>
</html>

