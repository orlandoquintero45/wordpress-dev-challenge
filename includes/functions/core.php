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
		
		<?php 
		$args = array(  
		'quicktags' => false,  
		'textarea_rows' => 5, 
		'media_buttons' => false, 
		'tinymce'       => array( 
		'toolbar1'    => 'bold,italic,strikethrough,underline,forecolor,charmap,outdent,indent', 
		'toolbar2'    => '', 
		)
		, ); 
		$editor_id = 'citacio_citas';         
		wp_editor( $citacio_citas, $editor_id, $args );
		?>
			
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
   $citacio_citas =  $_POST['citacio_citas'] ;
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
/************************************
************************************
prueba 3.2 **************************
************************************
***********************************/
/**
 * Realiza las acciones necesarias para configurar el plugin cuando se activa
 *
 * @return void
 */
function menu_link_init()
{
    global $wpdb; // Este objeto global nos permite trabajar con la BD de WP
    // Crea la tabla si no existe
    $tabla_proyectos = $wpdb->prefix . 'link_error';
    $charset_collate = $wpdb->get_charset_collate();
    $query = "CREATE TABLE IF NOT EXISTS $tabla_proyectos (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        url varchar(255) NOT NULL,
        estado varchar(100) NOT NULL,
		origen varchar(255) NOT NULL,
        created_at datetime NOT NULL,
		status INT DEFAULT 0 NULL,
        UNIQUE (id)
        ) $charset_collate;";
    // La función dbDelta que nos permite crear tablas de manera segura se
    // define en el fichero upgrade.php que se incluye a continuación
    include_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($query);
}
function menu_link_error()
{
    add_menu_page("Link error", "Link error", "manage_options",
        "menu_link_error", "menu_links_admin", "dashicons-feedback", 75);
	
}
//validar url
function filtroUrl($valor){
	
	$text1='http';
	$text3='https';
	$text2='www';
	if(trim($valor) == ''){
		return 0;
	}else{
		$pos = strpos($valor, $text1);
		if (($pos !== false) AND (strpos($valor, $text3)===false)) {
			//enlace inseguro
			return 1;
		} else {
			$pos = strpos($valor, $text2);
			if ($pos === false) {
				//sin protocolo
				return 2;
			}
			else
			{
				/*if (!filter_var($valor, FILTER_VALIDATE_URL)) {
					//Enlace malformado;
					return 3;
				}
				else
				{*/
					if (!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|](\.)[a-z]{2}/i",$valor)) {
						//Enlace malformado;
						return 3;
					}
					else
					{
						$handle = curl_init(esc_url($valor));
						curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
						$response = curl_exec($handle);
						$httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
						 if ($httpCode >= 200 && $httpCode < 300) {
							return 5;
						} else {
							//Enlace que retorna un Status Code incorrecto
							return 4;
						}
						curl_close($handle);
						/*$headers = @get_headers($valor);
			  
						if($headers && strpos( $headers[0], '200')) {
							return 5;
						}
						else {
							
							return 4;
						}*/
					}
				//}
			}
		}
	}
}
function links_insert($url=null,$estado=null,$origen=null)
{
	 global $wpdb;
	/*
	INSERT INTO `wp_link_error` (`id`, `url`, `estado`, `origen`, `created_at`, `status`) VALUES 
	('1', 'prueba url', 'link con error', 'prueba en entrada 1', '2022-08-01 07:39:47.000000', '0');
	*/
	if($estado==1)
	{
		$estado='enlace inseguro';
	}
	if($estado==2)
	{
		$estado='sin protocolo';
	}
	if($estado==3)
	{
		$estado='Enlace malformado';
	}
	if($estado==4)
	{
		$estado='Enlace que retorna un Status Code incorrecto';
	}
	$tabla_link = $wpdb->prefix . 'link_error';
	$created_at = date('Y-m-d H:i:s');
	$wpdb->insert(
		$tabla_link,
		array(
			'url' => $url,
			'estado' => $estado,
			'created_at' => $created_at,
			'origen'  => $origen,
			'status'  => '0',
		)
	);
}
//funcion verificacion url para llamar desde el cron
function verificar_url()
{
	global $wpdb;
	//verificamos los post ya revisados
	$tabla_proyectos = $wpdb->prefix . 'link_error';
	$links_error = $wpdb->get_results("SELECT * FROM $tabla_proyectos");
	$not_in='';
	foreach ($links_error as $link_error) {
		$not_in=$not_in."'".$link_error->origen."',";
	}
	$not_in = substr($not_in, 0, -1);
	//consultamos los post para saber si el content tiene url y verificar las misma
	$tabla_posts = $wpdb->prefix . 'posts';
	if($not_in=='')
	{
		$posts = $wpdb->get_results("SELECT * FROM $tabla_posts WHERE post_status='publish' ");
	}
	else
	{
		$posts = $wpdb->get_results("SELECT * FROM $tabla_posts WHERE `post_name` not in(".$not_in.") and post_status='publish' ");
	}
	
	
	$text1='href=';
	foreach ($posts as $post_) {
		if(strpos($post_->post_content, $text1)!==false)
		{
			$origen=$post_->post_name;
			$content = apply_filters( 'the_content', $post_->post_content );
			$content = str_replace( ']]>', ']]&gt;', $content );
			$ancla=explode("<a",$content);
			foreach ($ancla as $anc)
			{
				if(strpos($anc, '</a>'))
				{
					$texto=explode("</a>",$anc);
					$texto=explode(">",$texto[0]);
					$primer_texto=$texto[0];
					$url=str_replace('href=','',$primer_texto);
					$url=str_replace('\'','',$url);
					$url=str_replace('"','',$url);
					//llamamos a la funcion que verifica url
					$estado=filtroUrl($url);
					//insertamos url con error
					if($estado!=5)
					{
						links_insert($url,$estado,$origen);
					}
				}
			}
		}
		
	}
}
function menu_links_admin()
{
	global $wpdb;
	$tabla_proyectos = $wpdb->prefix . 'link_error';
	$links_error = $wpdb->get_results("SELECT * FROM $tabla_proyectos");
	/*$urls = array('https://www.google.com/','https://www.php.net/manual/es/filter.filters.validate.php','http://www.website.com', 'https://www.website.com', 'http://website.com', 'https://website.com', 'www.website.com', 'website.com');
	foreach($urls as $url) {
		echo $url.' '.filtroUrl($url).'<br>';
	}*/
	//$tabla_proyectos = $wpdb->prefix . 'link_error';
	//$links_error = $wpdb->get_results("SELECT * FROM $tabla_proyectos");
    echo '<div class="wrap"><h1>Link con error</h1>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th width="30%">URL</th><th width="20%">Estado</th>';
    echo '<th>Origen</th><th>Creado</th>';
    echo '</tr></thead>';
    echo '<tbody id="the-list">';
    foreach ($links_error as $link_error) {
        $url = esc_textarea($link_error->url);
        $estado = esc_textarea($link_error->estado);
        $origen = esc_textarea($link_error->origen);
		$created_at = esc_textarea($link_error->created_at);
		$id = esc_textarea($link_error->id);
		//$id_user = esc_textarea($link_error->id_user);
        echo "<tr><td>$url</td>";
        echo "<td><b style='color:coral;'>$estado</b></td><td>$origen</td>";
        echo "<td>$created_at</td>";
		
		
        echo "</tr>";
    }
    echo '</tbody></table></div>';
}

function cron_personalizado( $schedules ) { 
    $schedules['five_seconds'] = array(
        'interval' => 5,
        'display'  => esc_html__( 'Every Five Seconds' ), );
    return $schedules;
}