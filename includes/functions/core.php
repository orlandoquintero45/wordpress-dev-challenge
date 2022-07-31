<?php

if ( ! defined('ABSPATH') ) {
    die('Direct access not permitted.');
}
function adding_custom_meta_boxes( $post_type, $post ) {
	add_meta_box( 
		'my-meta-box',
		__( 'Citation' ),
		'render_my_meta_box',
		'post',
		'normal',
		'default'
	);
}
function render_my_meta_box($post) {
	$citacio_citas = get_post_meta( $post->ID, 'citacio_citas', true );
	wp_nonce_field( 'citacio_valida', 'citacio_valida_nonce' ); ?>
	<table width="100%" cellpadding="1" cellspacing="1" border="0">
		<tr>
		  <td width="20%"><strong><?php __('Citas'); ?></strong></td>
		  <td width="80%"><input type="text" name="citacio_citas" value="<?php echo sanitize_text_field($citacio_citas);?>" class="large-text" placeholder="Agregue citas" /></td>
		</tr>
	</table>
  <?php
}
function citacio_save_data($post_id) {
  // Comprobamos si se ha definido el nonce.
  if ( ! isset( $_POST['citacio_valida_nonce'] ) ) {
    return $post_id;
  }
  $nonce = $_POST['citacio_valida_nonce'];

  // Verificamos que el nonce es válido.
  if ( !wp_verify_nonce( $nonce, 'citacio_valida' ) ) {
    return $post_id;
  }

  // Si es un autoguardado nuestro formulario no se enviará, ya que aún no queremos hacer nada.
  if ( defined( 'DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
    return $post_id;
  }
   $old_citacio_citas = get_post_meta( $post_id, 'citacio_citas', true );
   $citacio_citas = sanitize_text_field( $_POST['citacio_citas'] );
   update_post_meta( $post_id, 'citacio_citas', $citacio_citas, $old_citacio_citas );
}
add_action( 'save_post', 'citacio_save_data' );

function shortcode_mostrar_citas($atts) {
	
	$post   = get_post();
	$post_id=$post->ID;
	
		
	$p = shortcode_atts(
		array(
			'post_id' => $post_id
		), $atts, 'mc-citacion' );
	
	$citacio_citas = get_post_meta( $p['post_id'], 'citacio_citas', true );
	
    return '<p>'.$citacio_citas.'</p>';
}