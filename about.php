<?php
  // Include important files
  require_once(dirname(__FILE__) . "/config.php");
  // Include page specific functions
?>
<?php include "header.php"; ?>

	<div class="row">
		<div class="span12">
            <h3>About <?php echo $APPLICATION_NAME; ?></h3>
            <p><?php echo $APPLICATION_NAME; ?> was coded to address a simple need.  Reuse my existing ReadyNAS Pro box to handle recording OTA TV with a HDHomeRun box and not have to have another system running.  So now one little NAS box can host my blog, serve media files and handle recording OTA TV.</p>
            <p>While I was at it I used HTML5 and added video play back to support remote playback of recorded video on the local network</p>
			<p>I hope that this script, using simple php 5.2 compliant code can be reused in other NAS boxes that typically run Debian Etch.  Please feel free to copy and distribute this code.</p>
        </div>
    </div>

<?php include "footer.php"; ?>
