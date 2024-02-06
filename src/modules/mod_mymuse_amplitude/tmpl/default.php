<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 

use Joomla\CMS\Uri\Uri;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;

?>

<section>
 <div id="mymuse-player-container">
    <div class="my-grid">
      <div id="player-left" class="player-box-1 player-left">
        <div class="cover-art-url">
          <img data-amplitude-song-info="cover_art_url"/>
        </div>
        <div id="meta-container" class="song-info">
          <span data-amplitude-song-info="name" class="song-name"></span>
          <div class="song-artist-album">
            <span data-amplitude-song-info="artist"></span>
            <span data-amplitude-song-info="album"></span>
          </div>
        </div>

      </div>
      <div id="player-middle" class="player-box-2 player-middle">
        <div id="control-container">

          <div id="shuffle-container">
            <div class="amplitude-shuffle amplitude-shuffle-off" id="shuffle"></div>
          </div>

          <div id="prev-container">
            <div class="amplitude-prev" id="previous"></div>
          </div>

          <div id="play-pause-container">
            <div class="amplitude-play-pause" id="play-pause"></div>
          </div>

          <div id="next-container">
            <div class="amplitude-next" id="next"></div>
          </div>

          <div id="repeat-container">
            <div class="amplitude-repeat" id="repeat"></div>
          </div>
        </div>

        <div id="time-container">
          <span class="amplitude-current-time time-container"></span>
          <span class="amplitude-duration-time time-container"></span>
        </div>
        <div id="player-progress-bar-container">
          <progress id="song-played-progress" class="amplitude-song-played-progress"></progress>
          <progress id="song-buffered-progress" class="amplitude-buffered-progress" value="0"></progress>
        </div>
      </div>

      <div id="player-right" class="player-box-3 player-right">
        <div id="volume-container">
          <img src="<?php echo Uri::Root(); ?>modules/mod_mymuse_amplitude/assets/img/volume.svg"/><input type="range" class="amplitude-volume-slider" step=".1"/>
        </div>

      </div>
    </div>
</div>

<script src="<?php echo $js_path; ?>"></script>
<script src="<?php echo $playlist; ?>"></script>
<script>
/*  window.onkeydown = function(e) {
    return !(e.keyCode == 32);
};
*/
/*
  Handles a click on the song played progress bar.
*/
document.getElementById('song-played-progress').addEventListener('click', function( e ){
  var offset = this.getBoundingClientRect();
  var x = e.pageX - offset.left;

  Amplitude.setSongPlayedPercentage( ( parseFloat( x ) / parseFloat( this.offsetWidth) ) * 100 );
});
</script>
<section>
