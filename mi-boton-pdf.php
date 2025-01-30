<?php
/**
 * Plugin Name: Button PDF
 * Plugin URI:  https://www.linkedin.com/in/jsravelo/
 * Description: Crea botones PDF con enlaces personalizados y genera shortcodes para insertarlos en entradas o páginas.
 * Version:     1.1
 * Author:      J. Santiago Ravelo
 * Author URI:  https://www.linkedin.com/in/jsravelo/
 * Text Domain: mi-boton-pdf
 */

// Evitar acceso directo
if ( ! defined('ABSPATH') ) {
    exit;
}

/**
 * 1. Registrar un Custom Post Type para los botones PDF
 */
function mbpdf_registrar_cpt() {
    $labels = array(
        'name'               => __('Botones PDF', 'mi-boton-pdf'),
        'singular_name'      => __('Botón PDF', 'mi-boton-pdf'),
        'add_new'            => __('Añadir nuevo Botón PDF', 'mi-boton-pdf'),
        'add_new_item'       => __('Añadir nuevo Botón PDF', 'mi-boton-pdf'),
        'edit_item'          => __('Editar Botón PDF', 'mi-boton-pdf'),
        'new_item'           => __('Nuevo Botón PDF', 'mi-boton-pdf'),
        'view_item'          => __('Ver Botón PDF', 'mi-boton-pdf'),
        'search_items'       => __('Buscar Botones PDF', 'mi-boton-pdf'),
        'not_found'          => __('No se encontraron Botones PDF', 'mi-boton-pdf'),
        'not_found_in_trash' => __('No se encontraron Botones PDF en la papelera', 'mi-boton-pdf'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-media-document',
        'supports'           => array('title'), // Solo necesitamos el título
        'exclude_from_search'=> true,
    );

    register_post_type('mbpdf_button', $args);
}
add_action('init', 'mbpdf_registrar_cpt');

/**
 * 2. Crear una Metabox para ingresar el enlace PDF
 */
function mbpdf_agregar_metabox() {
    add_meta_box(
        'mbpdf_link_metabox',
        __('Enlace al PDF', 'mi-boton-pdf'),
        'mbpdf_link_metabox_cb',
        'mbpdf_button',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'mbpdf_agregar_metabox');

/**
 * 3. Contenido de la Metabox (campo de texto para la URL)
 */
function mbpdf_link_metabox_cb($post) {
    wp_nonce_field('mbpdf_link_metabox_nonce', 'mbpdf_link_metabox_nonce_field');

    // Obtener la meta _mbpdf_link si existe
    $pdf_link = get_post_meta($post->ID, '_mbpdf_link', true);
    ?>
    <label for="mbpdf_link_field">
        <?php _e('URL del PDF:', 'mi-boton-pdf'); ?>
    </label><br><br>
    <input
        type="text"
        id="mbpdf_link_field"
        name="mbpdf_link_field"
        style="width: 100%;"
        value="<?php echo esc_attr($pdf_link); ?>"
        placeholder="https://tusitio.com/archivo.pdf"
    />
    <?php
}

/**
 * 4. Guardar el valor de la Metabox
 */
function mbpdf_guardar_metabox($post_id) {
    // Verificar el nonce
    if ( ! isset($_POST['mbpdf_link_metabox_nonce_field']) ||
         ! wp_verify_nonce($_POST['mbpdf_link_metabox_nonce_field'], 'mbpdf_link_metabox_nonce')
    ) {
        return;
    }

    // Evitar autosaves y verificar permisos
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Guardar la URL
    if (isset($_POST['mbpdf_link_field'])) {
        $pdf_url = sanitize_text_field($_POST['mbpdf_link_field']);
        update_post_meta($post_id, '_mbpdf_link', $pdf_url);
    }
}
add_action('save_post', 'mbpdf_guardar_metabox');

/**
 * 5. Shortcode para mostrar el botón PDF
 *    Uso: [mbpdf_boton id="123"]
 */
function mbpdf_boton_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'id' => '', // ID del CPT
        ),
        $atts,
        'mbpdf_boton'
    );

    // Obtener ID
    $post_id = (int) $atts['id'];
    if (!$post_id) {
        return '';
    }

    // Obtener la URL del PDF
    $pdf_link = get_post_meta($post_id, '_mbpdf_link', true);
    if (empty($pdf_link)) {
        return '';
    }

    // Obtener el título del CPT
    $pdf_title = get_the_title($post_id);
    if (!$pdf_title) {
        $pdf_title = __('Ver PDF', 'mi-boton-pdf'); // Por si no existe un título
    }

    // Retornar el código HTML del botón (usando el título del post como texto)
    $html = '
    <div class="pdf-icon-container">
      <a href="'.esc_url($pdf_link).'" target="_blank" title="'.esc_attr($pdf_title).'">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-filetype-pdf" viewBox="0 0 16 16">
          <path fill-rule="evenodd" d="M14 4.5V14a2 2 0 0 1-2 2h-1v-1h1a1 1 0 0 0 1-1V4.5h-2A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v9H2V2a2 2 0 0 1 2-2h5.5zM1.6 11.85H0v3.999h.791v-1.342h.803q.43 0 .732-.173.305-.175.463-.474a1.4 1.4 0 0 0 .161-.677q0-.375-.158-.677a1.2 1.2 0 0 0-.46-.477q-.3-.18-.732-.179m.545 1.333a.8.8 0 0 1-.085.38.57.57 0 0 1-.238.241.8.8 0 0 1-.375.082H.788V12.48h.66q.327 0 .512.181.185.183.185.522m1.217-1.333v3.999h1.46q.602 0 .998-.237a1.45 1.45 0 0 0 .595-.689q.196-.45.196-1.084 0-.63-.196-1.075a1.43 1.43 0 0 0-.589-.68q-.396-.234-1.005-.234zm.791.645h.563q.371 0 .609.152a.9.9 0 0 1 .354.454q.118.302.118.753a2.3 2.3 0 0 1-.068.592 1.1 1.1 0 0 1-.196.422.8.8 0 0 1-.334.252 1.3 1.3 0 0 1-.483.082h-.563zm3.743 1.763v1.591h-.79V11.85h2.548v.653H7.896v1.117h1.606v.638z"/>
        </svg>
        <p>'.esc_html($pdf_title).'</p>
      </a>
    </div>';

    return $html;
}
add_shortcode('mbpdf_boton', 'mbpdf_boton_shortcode');

/**
 * 6. Añadir columna en el listado de 'Botones PDF' con el Shortcode
 */
function mbpdf_agregar_columna_shortcode($columns) {
    $columns['mbpdf_shortcode'] = __('Shortcode', 'mi-boton-pdf');
    return $columns;
}
add_filter('manage_mbpdf_button_posts_columns', 'mbpdf_agregar_columna_shortcode');

function mbpdf_rellenar_columna_shortcode($column, $post_id) {
    if ($column === 'mbpdf_shortcode') {
        echo '[mbpdf_boton id="'.$post_id.'"]';
    }
}
add_action('manage_mbpdf_button_posts_custom_column', 'mbpdf_rellenar_columna_shortcode', 10, 2);

/**
 * 7. Encolar la hoja de estilo para el botón
 */
function mbpdf_enqueue_styles() {
    wp_enqueue_style(
        'mbpdf-style',
        plugin_dir_url(__FILE__) . 'css/style-pdf-button.css',
        array(),
        '1.0',
        'all'
    );
}
add_action('wp_enqueue_scripts', 'mbpdf_enqueue_styles');