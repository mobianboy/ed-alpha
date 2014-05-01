<input type="text" id="test2" name="test2" value="<?= get_post_meta($post->ID, 'test2', TRUE) ?>" size="25" />

<img src="<?= getImageCdnUrl('http://'.IMAGE_PROC.'/getfile/?src='.get_post_meta($post->ID, 'test2', TRUE).'&w=100&h=100&zc=1') ?>" />

<input type="text" id="test3" name="test3" value="<?= get_post_meta($post->ID, 'test3', TRUE) ?>" size="25" />

<img src="<?= getImageCdnUrl('http://'.IMAGE_PROC.'/getfile/?src='.get_post_meta($post->ID, 'test3', TRUE).'&w=100&h=100&zc=1') ?>" />

<input type="text" id="test4" name="test4" value="<?= get_post_meta($post->ID, 'test4', TRUE) ?>" size="25" />

<img src="<?= getImageCdnUrl('http://'.IMAGE_PROC.'/getfile/?src='.get_post_meta($post->ID, 'test4', TRUE).'&w=100&h=100&zc=1') ?>" />

