<?php
/**
 * Desinstalación: elimina todos los botones PDF (CPT mbpdf_button) y su metadatos.
 *
 * @package Mi_Boton_PDF
 */

if ( ! defined('WP_UNINSTALL_PLUGIN') ) {
    exit;
}

$post_ids = get_posts(
    array(
        'post_type'      => 'mbpdf_button',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    )
);

foreach ($post_ids as $post_id) {
    wp_delete_post((int) $post_id, true);
}
