<?php
/**
 * Plugin Name: Button PDF
 * Plugin URI:  https://www.linkedin.com/in/jsravelo/
 * Description: Crea botones PDF con enlaces personalizados y genera shortcodes para insertarlos en entradas o páginas.
 * Version:     2.2.1
 * Author:      J. Santiago Ravelo
 * Author URI:  https://www.linkedin.com/in/jsravelo/
 * Text Domain: mi-boton-pdf
 */

// Evitar acceso directo
if ( ! defined('ABSPATH') ) {
    exit;
}

define('MBPDF_VERSION', '2.2.1');

/** Opción: slug de plantilla predeterminada para botones nuevos. */
define('MBPDF_OPTION_DEFAULT_STYLE', 'mbpdf_default_button_style');

/** Meta del botón: plantilla de estilo (slug). */
define('MBPDF_META_BUTTON_STYLE', '_mbpdf_button_style');

/**
 * Cargar traducciones (carpeta /languages del plugin).
 */
function mbpdf_load_textdomain() {
    load_plugin_textdomain(
        'mi-boton-pdf',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages'
    );
}
add_action('plugins_loaded', 'mbpdf_load_textdomain');

/**
 * Plantillas de estilo del botón (slug => datos para UI y CSS).
 *
 * @return array<string, array{label:string, swatch:string}>
 */
function mbpdf_get_style_templates() {
    return array(
        'classic' => array(
            'label'  => __('Clásico (rojo)', 'mi-boton-pdf'),
            'swatch' => '#d9534f',
        ),
        'ocean'   => array(
            'label'  => __('Océano (azul)', 'mi-boton-pdf'),
            'swatch' => '#0d6efd',
        ),
        'forest'  => array(
            'label'  => __('Bosque (verde)', 'mi-boton-pdf'),
            'swatch' => '#198754',
        ),
        'slate'   => array(
            'label'  => __('Pizarra (gris)', 'mi-boton-pdf'),
            'swatch' => '#495057',
        ),
        'sunset'  => array(
            'label'  => __('Atardecer (naranja)', 'mi-boton-pdf'),
            'swatch' => '#fd7e14',
        ),
        'violet'  => array(
            'label'  => __('Violeta', 'mi-boton-pdf'),
            'swatch' => '#6f42c1',
        ),
        'dark'    => array(
            'label'  => __('Contraste oscuro', 'mi-boton-pdf'),
            'swatch' => '#212529',
        ),
        'minimal' => array(
            'label'  => __('Minimal (contorno)', 'mi-boton-pdf'),
            'swatch' => '#6c757d',
        ),
    );
}

/**
 * Slugs de plantilla válidos.
 *
 * @return string[]
 */
function mbpdf_get_valid_style_slugs() {
    return array_keys(mbpdf_get_style_templates());
}

/**
 * Normaliza un slug de plantilla.
 *
 * @param string $slug Valor recibido.
 * @return string Slug válido.
 */
function mbpdf_sanitize_style_slug($slug) {
    $slug = sanitize_key((string) $slug);
    return in_array($slug, mbpdf_get_valid_style_slugs(), true) ? $slug : 'classic';
}

/**
 * Plantilla guardada en el botón o vacío si nunca se definió.
 *
 * @param int $post_id ID del CPT.
 * @return string Slug o cadena vacía.
 */
function mbpdf_get_stored_button_style_raw($post_id) {
    $post_id = (int) $post_id;
    if ( ! $post_id ) {
        return '';
    }
    $saved = get_post_meta($post_id, MBPDF_META_BUTTON_STYLE, true);
    if ($saved === '' || $saved === false) {
        return '';
    }
    $slug = sanitize_key((string) $saved);
    return in_array($slug, mbpdf_get_valid_style_slugs(), true) ? $slug : '';
}

/**
 * Plantilla efectiva al mostrar el botón (shortcode, bloque con inherit).
 * Sin meta guardada se usa «classic» para no cambiar botones creados antes de 2.2.
 *
 * @param int $post_id ID del CPT.
 * @return string Slug válido.
 */
function mbpdf_get_effective_button_style($post_id) {
    $raw = mbpdf_get_stored_button_style_raw($post_id);
    if ($raw !== '') {
        return $raw;
    }
    return 'classic';
}

/**
 * Plantilla preseleccionada en el metabox (nuevo botón = predeterminado del sitio; sin meta en botón ya existente = clásico).
 *
 * @param \WP_Post $post Post del CPT.
 * @return string Slug válido.
 */
function mbpdf_metabox_selected_template($post) {
    $stored = mbpdf_get_stored_button_style_raw($post->ID);
    if ($stored !== '') {
        return $stored;
    }
    $is_new = ( (int) $post->ID === 0 || $post->post_status === 'auto-draft' );
    return $is_new ? mbpdf_get_default_style_option() : 'classic';
}

/**
 * Opción global: plantilla para botones nuevos y para botones sin meta.
 *
 * @return string Slug válido.
 */
function mbpdf_get_default_style_option() {
    $opt = get_option(MBPDF_OPTION_DEFAULT_STYLE, 'classic');
    return mbpdf_sanitize_style_slug((string) $opt);
}

/**
 * Lista de plantillas para el editor de bloques (JSON-friendly).
 *
 * @return array<int, array{slug:string,label:string}>
 */
function mbpdf_get_style_templates_for_block() {
    $out   = array();
    $tpls  = mbpdf_get_style_templates();
    foreach ($tpls as $slug => $data) {
        $out[] = array(
            'slug'  => $slug,
            'label' => $data['label'],
        );
    }
    return $out;
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
        'supports'           => array('title'),
        'exclude_from_search'=> true,
    );

    register_post_type('mbpdf_button', $args);
}
add_action('init', 'mbpdf_registrar_cpt');

/**
 * Registro del script del visor PDF en capa (modal + iframe).
 */
function mbpdf_register_viewer_script() {
    wp_register_script(
        'mbpdf-viewer',
        plugins_url('js/frontend-viewer.js', __FILE__),
        array(),
        MBPDF_VERSION,
        true
    );
}
add_action('wp_enqueue_scripts', 'mbpdf_register_viewer_script', 0);

/**
 * Encola visor en capa una vez por petición si hace falta.
 */
function mbpdf_enqueue_overlay_viewer_assets() {
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    wp_localize_script(
        'mbpdf-viewer',
        'mbpdfViewer',
        array(
            'closeLabel'      => __('Cerrar', 'mi-boton-pdf'),
            'openNewTabLabel' => __('Abrir en pestaña nueva', 'mi-boton-pdf'),
        )
    );
    wp_enqueue_script('mbpdf-viewer');
}

/**
 * Scripts de administración: metabox (biblioteca de medios).
 */
function mbpdf_admin_enqueue($hook) {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if ( ! $screen || $screen->post_type !== 'mbpdf_button' || ! in_array($hook, array('post.php', 'post-new.php'), true) ) {
        return;
    }

    wp_enqueue_media();
    wp_enqueue_style(
        'mbpdf-frontend-preview',
        plugin_dir_url(__FILE__) . 'css/style-pdf-button.css',
        array(),
        MBPDF_VERSION
    );
    wp_enqueue_script(
        'mbpdf-admin-metabox',
        plugins_url('js/admin-metabox.js', __FILE__),
        array('jquery'),
        MBPDF_VERSION,
        true
    );
    wp_localize_script(
        'mbpdf-admin-metabox',
        'mbpdfAdmin',
        array(
            'frameTitle'  => __('Seleccionar PDF', 'mi-boton-pdf'),
            'frameButton' => __('Usar este archivo', 'mi-boton-pdf'),
        )
    );
}
add_action('admin_enqueue_scripts', 'mbpdf_admin_enqueue');

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

    $pdf_link      = get_post_meta($post->ID, '_mbpdf_link', true);
    $download_mode = mbpdf_get_stored_download_mode($post->ID);
    $templates     = mbpdf_get_style_templates();
    $selected_tpl = mbpdf_metabox_selected_template($post);
    $global_def   = mbpdf_get_default_style_option();
    ?>
    <p>
        <label for="mbpdf_link_field">
            <strong><?php esc_html_e('URL del PDF', 'mi-boton-pdf'); ?></strong>
        </label>
    </p>
    <p>
        <input
            type="url"
            id="mbpdf_link_field"
            name="mbpdf_link_field"
            class="large-text"
            value="<?php echo esc_attr($pdf_link); ?>"
            placeholder="https://ejemplo.com/archivo.pdf"
            autocomplete="off"
        />
    </p>
    <p>
        <button type="button" class="button" id="mbpdf_select_pdf"><?php esc_html_e('Biblioteca de medios…', 'mi-boton-pdf'); ?></button>
    </p>
    <p>
        <label for="mbpdf_download_mode_field">
            <strong><?php esc_html_e('Cómo abrir el PDF', 'mi-boton-pdf'); ?></strong>
        </label>
    </p>
    <p>
        <select id="mbpdf_download_mode_field" name="mbpdf_download_mode_field">
            <option value="overlay" <?php selected($download_mode, 'overlay'); ?>>
                <?php esc_html_e('Visor en la misma página (recomendado en móviles)', 'mi-boton-pdf'); ?>
            </option>
            <option value="no" <?php selected($download_mode, 'no'); ?>>
                <?php esc_html_e('Enlace: abrir en pestaña (sin forzar descarga)', 'mi-boton-pdf'); ?>
            </option>
            <option value="yes" <?php selected($download_mode, 'yes'); ?>>
                <?php esc_html_e('Descargar el archivo', 'mi-boton-pdf'); ?>
            </option>
            <option value="auto" <?php selected($download_mode, 'auto'); ?>>
                <?php esc_html_e('Automático (compatibilidad): igual que enlace sin forzar descarga', 'mi-boton-pdf'); ?>
            </option>
        </select>
    </p>
    <p class="description">
        <?php esc_html_e('En Android, el atributo HTML «descargar» suele guardar el archivo en lugar de mostrarlo. Este plugin solo fuerza la descarga si eliges «Descargar el archivo». «Visor en la misma página» abre el PDF en un cuadro sobre la web (iframe); si el servidor del PDF no lo permite incrustado, usa «Abrir en pestaña nueva» dentro del visor o cambia a enlace directo.', 'mi-boton-pdf'); ?>
    </p>
    <hr style="margin:16px 0;" />
    <p>
        <strong><?php esc_html_e('Plantilla de estilo del botón', 'mi-boton-pdf'); ?></strong>
    </p>
    <p class="description">
        <?php
        printf(
            /* translators: %s: nombre de la plantilla predeterminada del sitio */
            esc_html__('Elige cómo se verá el botón en el sitio. Plantilla predeterminada para botones nuevos: %s.', 'mi-boton-pdf'),
            esc_html($templates[ $global_def ]['label'])
        );
        ?>
    </p>
    <fieldset class="mbpdf-style-fieldset" style="margin:0;padding:0;border:0;">
        <legend class="screen-reader-text"><?php esc_html_e('Plantilla de estilo del botón', 'mi-boton-pdf'); ?></legend>
        <?php foreach ($templates as $slug => $info) : ?>
            <p class="mbpdf-style-choice" style="margin:6px 0;">
                <label style="display:inline-flex;align-items:center;gap:8px;cursor:pointer;">
                    <input
                        type="radio"
                        name="mbpdf_button_style_field"
                        value="<?php echo esc_attr($slug); ?>"
                        <?php checked($selected_tpl, $slug); ?>
                    />
                    <span
                        class="mbpdf-style-swatch"
                        style="<?php echo esc_attr('display:inline-block;width:14px;height:14px;border-radius:3px;background:' . $info['swatch'] . ';flex-shrink:0;'); ?>"
                        aria-hidden="true"
                    ></span>
                    <span><?php echo esc_html($info['label']); ?></span>
                </label>
            </p>
        <?php endforeach; ?>
    </fieldset>
    <p style="margin-top:12px;">
        <label>
            <input type="checkbox" name="mbpdf_set_default_style_for_new" value="1" />
            <?php esc_html_e('Guardar también la plantilla elegida arriba como predeterminada para los próximos botones nuevos', 'mi-boton-pdf'); ?>
        </label>
    </p>
    <p class="description mbpdf-style-preview-note">
        <?php esc_html_e('Vista aproximada (el tamaño en el sitio depende del shortcode o del bloque):', 'mi-boton-pdf'); ?>
    </p>
    <div class="mbpdf-admin-style-previews" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-start;margin-top:8px;">
        <?php foreach ($templates as $slug => $info) : ?>
            <div class="mbpdf-admin-mini-wrap" style="flex:0 0 auto;width:120px;text-align:center;">
                <div
                    class="pdf-icon-container mbpdf-tpl-<?php echo esc_attr($slug); ?>"
                    style="margin:0 auto;transform:scale(0.55);transform-origin:center top;"
                >
                    <a class="pdf-icon-container__link" href="#" onclick="return false;" tabindex="-1" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
                            <path fill-rule="evenodd" d="M14 4.5V14a2 2 0 0 1-2 2h-1v-1h1a1 1 0 0 0 1-1V4.5h-2A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v9H2V2a2 2 0 0 1 2-2h5.5zM1.6 11.85H0v3.999h.791v-1.342h.803q.43 0 .732-.173.305-.175.463-.474a1.4 1.4 0 0 0 .161-.677q0-.375-.158-.677a1.2 1.2 0 0 0-.46-.477q-.3-.18-.732-.179m.545 1.333a.8.8 0 0 1-.085.38.57.57 0 0 1-.238.241.8.8 0 0 1-.375.082H.788V12.48h.66q.327 0 .512.181.185.183.185.522m1.217-1.333v3.999h1.46q.602 0 .998-.237a1.45 1.45 0 0 0 .595-.689q.196-.45.196-1.084 0-.63-.196-1.075a1.43 1.43 0 0 0-.589-.68q-.396-.234-1.005-.234zm.791.645h.563q.371 0 .609.152a.9.9 0 0 1 .354.454q.118.302.118.753a2.3 2.3 0 0 1-.068.592 1.1 1.1 0 0 1-.196.422.8.8 0 0 1-.334.252 1.3 1.3 0 0 1-.483.082h-.563zm3.743 1.763v1.591h-.79V11.85h2.548v.653H7.896v1.117h1.606v.638z"/>
                        </svg>
                        <p class="pdf-icon-container__label" style="font-size:11px;margin-top:4px;">PDF</p>
                    </a>
                </div>
                <span class="screen-reader-text"><?php echo esc_html($info['label']); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    <?php if ($post->ID && $post->post_status !== 'auto-draft') : ?>
        <p class="description">
            <?php esc_html_e('Shortcode para insertar este botón:', 'mi-boton-pdf'); ?>
            <code class="mbpdf-shortcode-preview">[mbpdf_boton id="<?php echo (int) $post->ID; ?>"]</code>
        </p>
    <?php endif; ?>
    <?php
}

/**
 * 4. Guardar el valor de la Metabox
 */
function mbpdf_guardar_metabox($post_id) {
    if (get_post_type($post_id) !== 'mbpdf_button') {
        return;
    }

    if ( ! isset($_POST['mbpdf_link_metabox_nonce_field']) ||
         ! wp_verify_nonce(wp_unslash($_POST['mbpdf_link_metabox_nonce_field']), 'mbpdf_link_metabox_nonce')
    ) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['mbpdf_link_field'])) {
        $raw = trim(wp_unslash($_POST['mbpdf_link_field']));
        $pdf_url = $raw !== '' ? esc_url_raw($raw) : '';
        if ($pdf_url === '' && $raw !== '' && preg_match('#\A/[^\s#<>"]+\z#', $raw)) {
            $pdf_url = $raw;
        }
        update_post_meta($post_id, '_mbpdf_link', $pdf_url);
    }

    if (isset($_POST['mbpdf_download_mode_field'])) {
        $mode = sanitize_text_field(wp_unslash($_POST['mbpdf_download_mode_field']));
        if (in_array($mode, array('auto', 'yes', 'no', 'overlay'), true)) {
            update_post_meta($post_id, '_mbpdf_download_mode', $mode);
        }
    }

    if (isset($_POST['mbpdf_button_style_field'])) {
        $tpl = mbpdf_sanitize_style_slug(wp_unslash($_POST['mbpdf_button_style_field']));
        update_post_meta($post_id, MBPDF_META_BUTTON_STYLE, $tpl);
    }

    if ( ! empty($_POST['mbpdf_set_default_style_for_new']) ) {
        $tpl = isset($_POST['mbpdf_button_style_field'])
            ? mbpdf_sanitize_style_slug(wp_unslash($_POST['mbpdf_button_style_field']))
            : mbpdf_get_default_style_option();
        update_option(MBPDF_OPTION_DEFAULT_STYLE, $tpl, false);
    }
}
add_action('save_post', 'mbpdf_guardar_metabox');

/**
 * Lista de botones para el bloque de Gutenberg (select).
 *
 * @return array<int, array{id:int,label:string}>
 */
function mbpdf_get_buttons_for_select() {
    $posts = get_posts(
        array(
            'post_type'      => 'mbpdf_button',
            'post_status'    => array('publish', 'draft', 'private', 'future'),
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        )
    );
    $out = array();
    foreach ($posts as $p) {
        $label = $p->post_title !== ''
            ? $p->post_title
            : sprintf(
                /* translators: %d: post ID */
                __('Botón #%d', 'mi-boton-pdf'),
                $p->ID
            );
        $out[] = array(
            'id'    => (int) $p->ID,
            'label' => $label,
        );
    }
    return $out;
}

/**
 * Comprueba si la URL del PDF es del mismo sitio o es una ruta relativa.
 */
function mbpdf_is_same_site_pdf_url($url) {
    if ($url === '') {
        return false;
    }
    $parsed = wp_parse_url($url);
    if ($parsed === false) {
        return false;
    }
    if (empty($parsed['host'])) {
        return isset($parsed['path']) && $parsed['path'] !== '' && strpos($parsed['path'], '/') === 0;
    }
    $home = wp_parse_url(home_url('/'));
    if (empty($home['host'])) {
        return false;
    }
    return isset($parsed['host']) && strtolower($parsed['host']) === strtolower($home['host']);
}

/**
 * Modo de apertura/descarga guardado en el botón (metabox).
 *
 * @param int $post_id ID del CPT mbpdf_button.
 * @return string 'auto'|'yes'|'no'|'overlay'
 */
function mbpdf_get_stored_download_mode($post_id) {
    $post_id = (int) $post_id;
    if ( ! $post_id ) {
        return 'auto';
    }
    $saved = get_post_meta($post_id, '_mbpdf_download_mode', true);
    return in_array($saved, array('auto', 'yes', 'no', 'overlay'), true) ? $saved : 'auto';
}

/**
 * Convierte inherit (o vacío) en el modo efectivo del botón o valida el valor explícito.
 *
 * @param int    $post_id  ID del botón.
 * @param string $download Valor del shortcode/bloque: inherit|auto|yes|no|overlay.
 * @return string 'auto'|'yes'|'no'|'overlay'
 */
function mbpdf_resolve_download_mode($post_id, $download) {
    if ($download === 'inherit' || $download === '') {
        return mbpdf_get_stored_download_mode($post_id);
    }
    if (in_array($download, array('auto', 'yes', 'no', 'overlay'), true)) {
        return $download;
    }
    return 'auto';
}

/**
 * Genera el HTML del botón PDF (shortcode, bloque y vista previa servidor).
 *
 * @param int   $post_id ID del CPT mbpdf_button.
 * @param array $args    class, text, target, download (inherit|auto|yes|no), size, style (inherit|slug).
 */
function mbpdf_get_button_html($post_id, $args = array()) {
    $post_id = (int) $post_id;
    if (!$post_id) {
        return '';
    }

    $post_obj = get_post($post_id);
    if ( ! $post_obj || $post_obj->post_type !== 'mbpdf_button') {
        return '';
    }

    $pdf_link = get_post_meta($post_id, '_mbpdf_link', true);
    if ($pdf_link === '') {
        return '';
    }

    $defaults = array(
        'class'    => '',
        'text'     => '',
        'target'   => '_blank',
        'download' => 'inherit',
        'size'     => 48,
        'style'    => 'inherit',
    );
    $args = wp_parse_args($args, $defaults);

    $style_arg = (string) $args['style'];
    if ($style_arg !== '' && $style_arg !== 'inherit') {
        $tpl_slug = mbpdf_sanitize_style_slug($style_arg);
    } else {
        $tpl_slug = mbpdf_get_effective_button_style($post_id);
    }

    $target = $args['target'] === '_self' ? '_self' : '_blank';
    $download_mode = mbpdf_resolve_download_mode($post_id, (string) $args['download']);

    $size = (int) $args['size'];
    if ($size < 16) {
        $size = 16;
    }
    if ($size > 128) {
        $size = 128;
    }

    $pdf_title = $args['text'] !== '' ? $args['text'] : get_the_title($post_id);
    if ($pdf_title === '') {
        $pdf_title = __('Ver PDF', 'mi-boton-pdf');
    }

    $container_class = 'pdf-icon-container mbpdf-tpl-' . sanitize_html_class($tpl_slug);
    if ($args['class'] !== '') {
        $parts = preg_split('/\s+/', trim($args['class']), -1, PREG_SPLIT_NO_EMPTY);
        foreach ($parts as $part) {
            $san = sanitize_html_class($part);
            if ($san !== '') {
                $container_class .= ' ' . $san;
            }
        }
    }

    $rel = $target === '_blank' ? 'noopener noreferrer' : '';
    $rel_attr = $rel !== '' ? ' rel="' . esc_attr($rel) . '"' : '';

    $download_attr = '';
    // Solo el modo explícito «yes» usa el atributo download. «auto» ya no fuerza descarga en PDFs del mismo sitio (evita descargas en Android).
    if ($download_mode === 'yes') {
        $path = wp_parse_url($pdf_link, PHP_URL_PATH);
        $fname = ($path && $path !== '/') ? basename($path) : '';
        if ($fname === '' || $fname === '/') {
            $fname = 'document.pdf';
        }
        $fname = sanitize_file_name($fname);
        if ($fname === '') {
            $fname = 'document.pdf';
        }
        $download_attr = ' download="' . esc_attr($fname) . '"';
    }

    $link_classes = 'pdf-icon-container__link';
    $extra_link_attrs = '';
    if ($download_mode === 'overlay') {
        mbpdf_enqueue_overlay_viewer_assets();
        $link_classes .= ' mbpdf-viewer-trigger';
        $extra_link_attrs = ' data-mbpdf-src="' . esc_url($pdf_link) . '"';
    }

    $html = sprintf(
        '<div class="%1$s">
      <a class="%9$s" href="%2$s" target="%3$s"%4$s%5$s%10$s title="%6$s">
        <svg xmlns="http://www.w3.org/2000/svg" width="%7$d" height="%7$d" fill="currentColor" class="bi bi-filetype-pdf" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
          <path fill-rule="evenodd" d="M14 4.5V14a2 2 0 0 1-2 2h-1v-1h1a1 1 0 0 0 1-1V4.5h-2A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v9H2V2a2 2 0 0 1 2-2h5.5zM1.6 11.85H0v3.999h.791v-1.342h.803q.43 0 .732-.173.305-.175.463-.474a1.4 1.4 0 0 0 .161-.677q0-.375-.158-.677a1.2 1.2 0 0 0-.46-.477q-.3-.18-.732-.179m.545 1.333a.8.8 0 0 1-.085.38.57.57 0 0 1-.238.241.8.8 0 0 1-.375.082H.788V12.48h.66q.327 0 .512.181.185.183.185.522m1.217-1.333v3.999h1.46q.602 0 .998-.237a1.45 1.45 0 0 0 .595-.689q.196-.45.196-1.084 0-.63-.196-1.075a1.43 1.43 0 0 0-.589-.68q-.396-.234-1.005-.234zm.791.645h.563q.371 0 .609.152a.9.9 0 0 1 .354.454q.118.302.118.753a2.3 2.3 0 0 1-.068.592 1.1 1.1 0 0 1-.196.422.8.8 0 0 1-.334.252 1.3 1.3 0 0 1-.483.082h-.563zm3.743 1.763v1.591h-.79V11.85h2.548v.653H7.896v1.117h1.606v.638z"/>
        </svg>
        <p class="pdf-icon-container__label">%8$s</p>
      </a>
    </div>',
        esc_attr($container_class),
        esc_url($pdf_link),
        esc_attr($target),
        $rel_attr,
        $download_attr,
        esc_attr($pdf_title),
        $size,
        esc_html($pdf_title),
        esc_attr($link_classes),
        $extra_link_attrs
    );

    return $html;
}

/**
 * 5. Shortcode: [mbpdf_boton id="123"]
 */
function mbpdf_boton_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'id'       => '',
            'class'    => '',
            'text'     => '',
            'target'   => '_blank',
            'download' => 'inherit',
            'size'     => '48',
            'style'    => 'inherit',
        ),
        $atts,
        'mbpdf_boton'
    );

    $post_id = (int) $atts['id'];
    if (!$post_id) {
        return '';
    }

    return mbpdf_get_button_html(
        $post_id,
        array(
            'class'    => $atts['class'],
            'text'     => $atts['text'],
            'target'   => $atts['target'],
            'download' => $atts['download'],
            'size'     => (int) $atts['size'],
            'style'    => $atts['style'],
        )
    );
}
add_shortcode('mbpdf_boton', 'mbpdf_boton_shortcode');

/**
 * Render del bloque Gutenberg.
 *
 * @param array<string,mixed> $attributes Atributos del bloque.
 */
function mbpdf_render_block_button($attributes) {
    $button_id = isset($attributes['buttonId']) ? (int) $attributes['buttonId'] : 0;
    if (!$button_id) {
        return '';
    }

    return mbpdf_get_button_html(
        $button_id,
        array(
            'class'    => isset($attributes['className']) ? (string) $attributes['className'] : '',
            'text'     => isset($attributes['text']) ? (string) $attributes['text'] : '',
            'target'   => isset($attributes['target']) ? (string) $attributes['target'] : '_blank',
            'download' => isset($attributes['download']) ? (string) $attributes['download'] : 'inherit',
            'size'     => isset($attributes['size']) ? (int) $attributes['size'] : 48,
            'style'    => isset($attributes['buttonStyle']) ? (string) $attributes['buttonStyle'] : 'inherit',
        )
    );
}

/**
 * Registro del bloque y script de editor.
 */
function mbpdf_register_block() {
    wp_register_script(
        'mbpdf-block-editor',
        plugins_url('js/block-editor.js', __FILE__),
        array(
            'wp-blocks',
            'wp-element',
            'wp-components',
            'wp-block-editor',
            'wp-i18n',
            'wp-server-side-render',
        ),
        MBPDF_VERSION,
        true
    );

    wp_localize_script(
        'mbpdf-block-editor',
        'mbpdfBlock',
        array(
            'buttons'        => mbpdf_get_buttons_for_select(),
            'styleTemplates' => mbpdf_get_style_templates_for_block(),
        )
    );

    register_block_type(
        'mi-boton-pdf/boton',
        array(
            'api_version'     => 2,
            'supports'        => array(
                'html'      => false,
                'align'     => true,
                'className' => true,
            ),
            'attributes'      => array(
                'buttonId'  => array(
                    'type'    => 'integer',
                    'default' => 0,
                ),
                'text'      => array(
                    'type'    => 'string',
                    'default' => '',
                ),
                'target'    => array(
                    'type'    => 'string',
                    'default' => '_blank',
                ),
                'download'  => array(
                    'type'    => 'string',
                    'default' => 'inherit',
                ),
                'size'      => array(
                    'type'    => 'integer',
                    'default' => 48,
                ),
                'className' => array(
                    'type'    => 'string',
                    'default' => '',
                ),
                'buttonStyle' => array(
                    'type'    => 'string',
                    'default' => 'inherit',
                ),
            ),
            'editor_script'   => 'mbpdf-block-editor',
            'render_callback' => 'mbpdf_render_block_button',
        )
    );
}
add_action('init', 'mbpdf_register_block', 20);

/**
 * 6. Columnas en el listado de Botones PDF
 */
function mbpdf_agregar_columnas($columns) {
    $new = array();
    foreach ($columns as $key => $label) {
        $new[ $key ] = $label;
        if ($key === 'title') {
            $new['mbpdf_url'] = __('URL del PDF', 'mi-boton-pdf');
        }
    }
    $new['mbpdf_shortcode'] = __('Shortcode', 'mi-boton-pdf');
    return $new;
}
add_filter('manage_mbpdf_button_posts_columns', 'mbpdf_agregar_columnas');

function mbpdf_rellenar_columnas($column, $post_id) {
    if ($column === 'mbpdf_url') {
        $url = get_post_meta($post_id, '_mbpdf_link', true);
        if ($url !== '') {
            $display = $url;
            if (function_exists('mb_strlen') && mb_strlen($display) > 48) {
                $display = mb_substr($display, 0, 48) . '…';
            } elseif (strlen($display) > 48) {
                $display = substr($display, 0, 48) . '…';
            }
            printf(
                '<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
                esc_url($url),
                esc_html($display)
            );
        } else {
            echo '—';
        }
        return;
    }

    if ($column === 'mbpdf_shortcode') {
        $code = '[mbpdf_boton id="' . (int) $post_id . '"]';
        printf(
            '<input type="text" class="widefat code mbpdf-shortcode-copy" readonly value="%s" onclick="this.select();" aria-label="%s" />',
            esc_attr($code),
            esc_attr__('Seleccionar shortcode', 'mi-boton-pdf')
        );
    }
}
add_action('manage_mbpdf_button_posts_custom_column', 'mbpdf_rellenar_columnas', 10, 2);

/**
 * 7. Encolar la hoja de estilo para el botón
 */
function mbpdf_enqueue_styles() {
    wp_enqueue_style(
        'mbpdf-style',
        plugin_dir_url(__FILE__) . 'css/style-pdf-button.css',
        array(),
        MBPDF_VERSION,
        'all'
    );
}
add_action('wp_enqueue_scripts', 'mbpdf_enqueue_styles');
