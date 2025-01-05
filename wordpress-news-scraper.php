<?php
/**
 * Plugin Name: Bing News Scraper
 * Description: Busca noticias de Bing News relacionadas con palabras clave y extrae su contenido.
 * Version: 1.8
 * Author: Alexis Olivero
 * Author URI: https://oliverodev.com.com
 */

if (!defined('ABSPATH')) {
    exit;
}

// Verificar y cargar Simple HTML DOM
function ns_load_simple_html_dom() {
    $simple_html_dom_path = plugin_dir_path(__FILE__) . 'simple_html_dom.php';
    if (!file_exists($simple_html_dom_path)) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>Error: El archivo simple_html_dom.php no se encuentra.</p></div>';
        });
        return false;
    }
    require_once($simple_html_dom_path);
    return true;
}

// Registrar el menú de administración
add_action('admin_menu', 'ns_add_admin_menu');
function ns_add_admin_menu() {
    add_menu_page('Google News Scraper', 'Google News Scraper', 'manage_options', 'news-scraper', 'ns_admin_page');
}

// Página de administración
function ns_admin_page() {
    if (!ns_load_simple_html_dom()) {
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['keyword'])) {
        $keyword = sanitize_text_field($_POST['keyword']);
        $results = ns_fetch_news($keyword);

        if (is_wp_error($results)) {
            echo '<div class="notice notice-error"><p>Error: ' . esc_html($results->get_error_message()) . '</p></div>';
        } elseif ($results === true) {
            echo '<div class="notice notice-success"><p>¡Noticias importadas como borradores! Revisa tus publicaciones para editarlas.</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>No se encontraron noticias para esta palabra clave. Intenta con otra búsqueda.</p></div>';
        }
    }
    ?>
    <div class="wrap">
        <h1>Bing News Scraper</h1>
        <form method="post" action="">
            <label for="keyword">Palabra clave:</label>
            <input type="text" id="keyword" name="keyword" required>
            <button type="submit" class="button button-primary">Buscar Noticias</button>
        </form>
    </div>
    <?php
}

// Función para extraer el contenido principal de una URL
function ns_extract_article_content($url) {
    if (!class_exists('simple_html_dom')) {
        return '';
    }

    $response = wp_remote_get($url, array(
        'timeout' => 60, // Aumentado el timeout
        'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/91.0.4472.124'
    ));

    if (is_wp_error($response)) {
        return '';
    }

    $html = str_get_html(wp_remote_retrieve_body($response));
    if (!$html) {
        return '';
    }

    // Selectores mejorados para encontrar el contenido principal
    $main_selectors = array(
        'article',
        '.article-body',
        '.story-body',
        '.entry-content',
        '.post-content',
        '.content-body',
        '#article-body',
        '.news-body',
        '.article__body',
        '.article-text',
        '.article-content',
        '[itemprop="articleBody"]',
        '.body-content',
        'main p',
        '.main-content'
    );

    $content = '';
    
    // Buscar contenido usando múltiples métodos
    foreach ($main_selectors as $selector) {
        if ($selector === 'main p') {
            // Recolectar todos los párrafos dentro de main
            $paragraphs = $html->find('main p');
            foreach ($paragraphs as $p) {
                $content .= $p->plaintext . "\n\n";
            }
        } else {
            $element = $html->find($selector, 0);
            if ($element) {
                // Limpiar elementos no deseados
                foreach($element->find('script, style, iframe, .ad, .advertisement, .social-share, .related-articles, .newsletter, nav, header, footer, aside') as $item) {
                    $item->outertext = '';
                }
                
                // Preservar párrafos y saltos de línea
                $text = $element->innertext;
                $text = strip_tags($text, '<p><br><h1><h2><h3><h4><h5><h6>');
                $content = $text;
                break;
            }
        }
    }

    // Limpieza final del contenido
    $content = preg_replace('/\s+/', ' ', $content); // Eliminar espacios múltiples
    $content = str_replace(array("\r", "\n"), '', $content); // Eliminar saltos de línea extras
    $content = preg_replace('/<\/?p>/', "\n\n", $content); // Convertir etiquetas <p> en saltos de línea
    $content = preg_replace('/<br\s*\/?>/i', "\n", $content); // Convertir <br> en saltos de línea
    $content = strip_tags($content); // Eliminar cualquier etiqueta HTML restante
    $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8'); // Decodificar entidades HTML
    $content = trim($content); // Eliminar espacios al inicio y final

    // Eliminar líneas vacías múltiples
    $content = preg_replace("/[\r\n]+/", "\n\n", $content);
    
    return $content;
}

// Función para buscar noticias
function ns_fetch_news($keyword) {
    if (empty($keyword)) {
        return new WP_Error('empty_keyword', 'La palabra clave no puede estar vacía.');
    }

    // Construir URL de Bing News
    $bing_news_url = 'https://www.bing.com/news/search?q=' . urlencode($keyword) . '&format=rss';
    
    $response = wp_remote_get($bing_news_url, array(
        'timeout' => 30,
        'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/91.0.4472.124'
    ));

    if (is_wp_error($response)) {
        error_log("Bing News Scraper Error: " . $response->get_error_message());
        return $response;
    }

    $rss_content = wp_remote_retrieve_body($response);
    if (empty($rss_content)) {
        return new WP_Error('empty_content', 'No se pudo obtener contenido del feed.');
    }

    libxml_use_internal_errors(true);
    $rss = simplexml_load_string($rss_content);
    
    if (!$rss) {
        return new WP_Error('invalid_xml', 'Feed RSS inválido');
    }

    $articles = array();
    $items = $rss->channel->item ?? array();
    
    foreach ($items as $item) {
        if (count($articles) >= 3) break; // Limitado a 3 artículos

        $url = (string)($item->link ?? $item->guid);
        $content = ns_extract_article_content($url);

        $articles[] = array(
            'title' => (string)$item->title,
            'description' => (string)($item->description ?? ''),
            'content' => $content,
            'url' => $url,
            'date' => (string)($item->pubDate ?? date('r'))
        );
    }

    if (empty($articles)) {
        return false;
    }

    foreach ($articles as $article) {
        $post_content = sprintf(
            "<strong>Fecha:</strong> %s\n\n<strong>Extracto:</strong>\n%s\n\n<strong>Contenido:</strong>\n%s\n\n<hr>\n\n<p>Fuente original: <a href=\"%s\" target=\"_blank\">Leer más</a></p>",
            esc_html($article['date']),
            wp_kses_post($article['description']),
            wp_kses_post($article['content']),
            esc_url($article['url'])
        );

        $post_data = array(
            'post_title'   => sanitize_text_field($article['title']),
            'post_content' => $post_content,
            'post_status'  => 'draft',
            'post_author'  => get_current_user_id(),
            'post_type'    => 'post',
        );

        $post_id = wp_insert_post($post_data);

        if (is_wp_error($post_id)) {
            error_log('Bing News Scraper: Error al insertar post - ' . $post_id->get_error_message());
        } else {
            error_log('Bing News Scraper: Post creado exitosamente - ID: ' . $post_id);
        }
    }

    return true;
}
?>
