<script type="text/javascript">
	jQuery(document).ready( function(){
			jQuery('#lof_twitter<?php echo $prfSlide.$blockid;?>').lofTwitter({
				username:[<?php echo $username; ?>],
				count: <?php echo $limit_items; ?>,
				itemWidth: <?php echo (($Width == "auto") ? 600 : $Width); ?>,
				itemHeight: <?php echo (($Height == "auto") ? 400 : $Height); ?>,
				space: <?php echo $space; ?>,
				vertical: <?php echo ($layout ? 'true' : 'false');?>,
				hoverPause: <?php echo ($hoverPause ? 'true' : 'false');?>,
				visible: <?php echo $visible; ?>,/*number visible items*/
				auto: <?php echo $auto; ?>,
				speed: <?php echo $speed; ?>,
				showFollowButton: false,
				showMode: "<?php echo $showMode; ?>",
				showTweetFeed: {
					expandHovercards: <?php echo ($expandHovercards ? 'true' : 'false');?>,
					showSource: <?php echo ($showSource ? 'true' : 'false');?>
				}
			})
		});
</script>