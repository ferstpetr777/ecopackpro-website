<?php
/**
 * Plugin Name: Contact Page Extra Email
 * Description: Добавляет дополнительный email zakaz1@ecopackpro.ru на странице контактов сразу под существующим адресом.
 * Author: ops
 */

if (!defined('ABSPATH')) {
    exit;
}

add_filter('the_content', function ($content) {
    if (!is_page() || is_admin()) {
        return $content;
    }

    // Проверяем, что это страница контактов по слагу
    $isContact = false;
    $page = get_queried_object();
    if ($page && isset($page->post_name)) {
        $isContact = ($page->post_name === 'contact-us' || $page->post_name === 'kontakty');
    }

    if (!$isContact) {
        return $content;
    }

    $primaryEmail = 'zakaz@plomba-nn.ru';
    $extraEmail   = 'zakaz1@ecopackpro.ru';

    $extraHtml = '<br><a href="mailto:' . esc_attr($extraEmail) . '">' . esc_html($extraEmail) . '</a>';

    // Если основной адрес найден в HTML, вставляем новую строку сразу после него
    if (stripos($content, $primaryEmail) !== false) {
        $content = preg_replace(
            '/(' . preg_quote($primaryEmail, '/') . '\s*<\/a>?)/i',
            '$1' . $extraHtml,
            $content,
            1
        );
        if (stripos($content, $extraEmail) === false) {
            // На случай, если якоря нет вокруг, пробуем по простому тексту
            $content = preg_replace(
                '/' . preg_quote($primaryEmail, '/') . '/i',
                $primaryEmail . $extraHtml,
                $content,
                1
            );
        }
    } else {
        // Если основного адреса нет в контенте, просто добавляем блок в конец
        if (stripos($content, $extraEmail) === false) {
            $content .= '\n<p><a href="mailto:' . esc_attr($extraEmail) . '">' . esc_html($extraEmail) . '</a></p>';
        }
    }

    return $content;
}, 20);

// Дополнительно: подстрахуемся финальной заменой в сгенерированном HTML страницы контактов
add_action('template_redirect', function(){
    if (is_admin() || !is_page()) {
        return;
    }
    $page = get_queried_object();
    if (!$page || !isset($page->post_name)) {
        return;
    }
    if ($page->post_name !== 'contact-us' && $page->post_name !== 'kontakty') {
        return;
    }

    ob_start(function ($html) {
        $primaryAnchorPattern = '#(<div[^>]*class=\"[^\"]*w-post-elm[^\"]*option\|site_mail[^\"]*\"[^>]*>\s*<a\s+href=\"mailto:zakaz@plomba-nn\.ru\"[^>]*>.*?</a>\s*</div>)#is';
        $extraBlock = '<div class="w-post-elm post_custom_field type_text option|site_mail_2 color_link_inherit"><a href="mailto:zakaz1@ecopackpro.ru"><span class="w-post-elm-value">zakaz1@ecopackpro.ru</span></a></div>';
        if (stripos($html, 'zakaz1@ecopackpro.ru') !== false) {
            return $html; // уже вставлено ранее
        }
        $html = preg_replace($primaryAnchorPattern, '$1' . $extraBlock, $html, 1);
        // fallback: если не нашли по блоку, пробуем после первой ссылки mailto на основной email
        if (stripos($html, 'zakaz1@ecopackpro.ru') === false) {
            $html = preg_replace('#(<a\s+href=\"mailto:zakaz@plomba-nn\.ru\"[^>]*>.*?</a>)#is', '$1' . $extraBlock, $html, 1);
        }
        return $html;
    });
});

// Клиентская вставка на случай, если HTML-фильтрация/кэш препятствуют серверной модификации
add_action('wp_footer', function(){
    if (!is_page()) { return; }
    $page = get_queried_object();
    if (!$page || !isset($page->post_name)) { return; }
    if ($page->post_name !== 'contact-us' && $page->post_name !== 'kontakty') { return; }
    ?>
    <script>
    (function(){
      try {
        var primary = document.querySelector('a[href^="mailto:zakaz@plomba-nn.ru"]');
        if (!primary) return;
        if (document.querySelector('a[href^="mailto:zakaz1@ecopackpro.ru"]')) return;
        var wrap = primary.closest('.w-post-elm') || primary.parentElement;
        var div = document.createElement('div');
        div.className = 'w-post-elm post_custom_field type_text option|site_mail_2 color_link_inherit';
        var a = document.createElement('a');
        a.href = 'mailto:zakaz1@ecopackpro.ru';
        a.innerHTML = '<span class="w-post-elm-value">zakaz1@ecopackpro.ru</span>';
        div.appendChild(a);
        if (wrap && wrap.parentNode) {
          wrap.parentNode.insertBefore(div, wrap.nextSibling);
        } else {
          primary.insertAdjacentElement('afterend', div);
        }
      } catch(e) {}
    })();
    </script>
    <?php
});


