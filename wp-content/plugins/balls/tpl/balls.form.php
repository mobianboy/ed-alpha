<?php

$post_types = post_type::get_post_types(TRUE, TRUE);
if(count($post_types)) {
  foreach($post_types as $key => $post_type) {
	  echo $post_type->id;
	  echo $post_type->name;
  }
}

