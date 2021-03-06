<?php
/**
 * Method for dispalying children
 */
?>

<?php
	
	function displayChildEntities($title, $childEntities) {

		global $post;
		global $entity;
			
		if (sizeof($childEntities) > 0) {
			?> <h2><?php echo htmlspecialchars($title) ?></h2>
			<div class="archive-blog-wrapper blog-posts clear"> <?php
				
				$originalPost = $post;
				$originalEntity = $entity;
				$GLOBALS['displayingChild'] = true;
				foreach( $childEntities as $child ) {
					$post = $child->post;
					get_template_part( 'template-parts/content' );
				}
				
				$post = $originalPost;
				$entity = $originalEntity;
				$GLOBALS['displayingChild'] = false;
				
			?></div><?php
		}
	}
?>