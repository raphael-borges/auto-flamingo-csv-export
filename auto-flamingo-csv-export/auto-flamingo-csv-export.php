<?php
/*
 * Plugin Name: Auto Flamingo message exporter
 * Description: A plugin to export message content to csv and generate a file on the server
 * Version:     0.1
 * Author:      Raphael Borges
 * Author URI:  https://raphaelborges.com.br
 * Text Domain: auto-flamingo-csv-export
*/

// Add page to menu
function add_export_page()
{
    add_menu_page(
        'Exportar Mensagens',
        'Exportar Mensagens',
        'manage_options',
        'export-flamingo',
        'export_flamingo_page'
    );
}
add_action('admin_menu', 'add_export_page');

// export page
function export_flamingo_page()
{
    global $wpdb;

    $taxonomy_query = "SELECT DISTINCT t.name
                       FROM {$wpdb->terms} t
                       INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
                       INNER JOIN {$wpdb->term_relationships} tr ON t.term_id = tr.term_taxonomy_id
                       WHERE tt.taxonomy = 'flamingo_inbound_channel'
                       AND tr.object_id IN (SELECT ID FROM {$wpdb->posts} WHERE post_type = 'flamingo_inbound' AND post_status = 'publish')";
    $taxonomies = $wpdb->get_col($taxonomy_query);

?>
    <div class="wrap">
        <h2>Exportar Mensagens de E-mail</h2>
        <form action="<?php echo admin_url('admin-post.php'); ?>" method="post">

            <?php if (!empty($taxonomies)) : ?>
                <br><br>
                <a href="<?php echo admin_url('admin-post.php?action=export_all_flamingo'); ?>" class="button button-primary">Exportar Todas as Listas</a>
            <?php endif; ?>
        </form>

        <h3>Lista de URLs:</h3>
        <ul>
            <?php foreach ($taxonomies as $taxonomy) : ?>
                <?php
                $taxonomy_name = sanitize_title($taxonomy);
                $upload_dir = wp_upload_dir();
                $export_dir = trailingslashit($upload_dir['baseurl']) . 'export-flamingo';
                $file_url = $export_dir . '/flamingo_export_' . $taxonomy_name . '.csv';
                ?>
                <li><strong><?php echo esc_html($taxonomy); ?>:</strong> <a href="#" onclick="copyToClipboard('<?php echo esc_url($file_url); ?>'); return false;">Clique aqui para copiar a URL - <?php echo esc_url($file_url); ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <?php
    $next_scheduled = wp_next_scheduled('export_all_flamingo_event');
    echo 'Próxima execução agendada para: ' . date('Y-m-d H:i:s', $next_scheduled);
    ?>


    <script>
        function copyToClipboard(text) {
            var tempInput = document.createElement("input");
            tempInput.value = text;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand("copy");
            document.body.removeChild(tempInput);
            alert("URL copiada para a área de transferência: " + text);
        }
    </script>
<?php
}

// Function to export all lists
function export_all_flamingo()
{
    global $wpdb;

    $taxonomy_query = "SELECT DISTINCT t.name
                       FROM {$wpdb->terms} t
                       INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
                       INNER JOIN {$wpdb->term_relationships} tr ON t.term_id = tr.term_taxonomy_id
                       WHERE tt.taxonomy = 'flamingo_inbound_channel'
                       AND tr.object_id IN (SELECT ID FROM {$wpdb->posts} WHERE post_type = 'flamingo_inbound' AND post_status = 'publish')";
    $taxonomies = $wpdb->get_col($taxonomy_query);

    $upload_dir = wp_upload_dir();
    $export_dir = trailingslashit($upload_dir['basedir']) . 'export-flamingo';

    if (!file_exists($export_dir)) {
        wp_mkdir_p($export_dir);
    }

    if (!empty($taxonomies)) {
        foreach ($taxonomies as $taxonomy) {
            $taxonomy_name = sanitize_title($taxonomy);
            $file_path = $export_dir . '/flamingo_export_' . $taxonomy_name . '.csv';

            // Consult posts filtered by taxonomy and published status
            $query = "
                SELECT p.ID, p.post_content, t.name AS taxonomy_value
                FROM {$wpdb->posts} p
                LEFT JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
                LEFT JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                LEFT JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
                WHERE p.post_type = 'flamingo_inbound'
                AND p.post_status = 'publish'
                AND tt.taxonomy = 'flamingo_inbound_channel'
                AND t.name = %s
            ";

            $posts = $wpdb->get_results($wpdb->prepare($query, $taxonomy));

            $output = fopen($file_path, 'w');
            $header = true;

            foreach ($posts as $post) {
                $content_lines = explode("\n", $post->post_content);
                $taxonomy_value = $post->taxonomy_value;

                if ($header) {
                    $header = false;
                }

                $data_row = array_merge($content_lines, [$taxonomy_value]);
                fputcsv($output, $data_row);
            }
            fclose($output);
        }
    }

    wp_redirect(admin_url('admin.php?page=export-flamingo'));
    exit();
}

add_action('admin_post_export_all_flamingo', 'export_all_flamingo');



//Schedule automatic export every 24 hours
function schedule_export_all_flamingo()
{
    if (!wp_next_scheduled('export_all_flamingo_event')) {
        wp_schedule_event(time(), 'daily', 'export_all_flamingo_event');
    }
}
add_action('admin_init', 'schedule_export_all_flamingo');

// Schedule automatic export every minute (for testing only)
// function schedule_export_all_flamingo()
// {
//     if (!wp_next_scheduled('export_all_flamingo_event')) {
//         wp_schedule_event(time(), 60, 'export_all_flamingo_event');

//     }
//     //wp_clear_scheduled_hook('export_all_flamingo_event');
// }
// add_action('init', 'schedule_export_all_flamingo');


function check_export_all_flamingo_schedule()
{
    $timestamp = wp_next_scheduled('export_all_flamingo_event');
    echo 'Próxima execução agendada para: ' . date('Y-m-d H:i:s', $timestamp);
}


// Function to export all lists automatically
function export_all_flamingo_auto()
{
    global $wpdb;

    $taxonomy_query = "SELECT DISTINCT t.name
                       FROM {$wpdb->terms} t
                       INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
                       INNER JOIN {$wpdb->term_relationships} tr ON t.term_id = tr.term_taxonomy_id
                       WHERE tt.taxonomy = 'flamingo_inbound_channel'
                       AND tr.object_id IN (SELECT ID FROM {$wpdb->posts} WHERE post_type = 'flamingo_inbound' AND post_status = 'publish')";
    $taxonomies = $wpdb->get_col($taxonomy_query);

    $upload_dir = wp_upload_dir();
    $export_dir = trailingslashit($upload_dir['basedir']) . 'export-flamingo';

    if (!file_exists($export_dir)) {
        wp_mkdir_p($export_dir);
    }

    if (!empty($taxonomies)) {
        foreach ($taxonomies as $taxonomy) {
            $taxonomy_name = sanitize_title($taxonomy);
            $file_path = $export_dir . '/flamingo_export_' . $taxonomy_name . '.csv';

            // Consult posts filtered by taxonomy and published status
            $query = "
                SELECT p.ID, p.post_content, t.name AS taxonomy_value
                FROM {$wpdb->posts} p
                LEFT JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
                LEFT JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                LEFT JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
                WHERE p.post_type = 'flamingo_inbound'
                AND p.post_status = 'publish'
                AND tt.taxonomy = 'flamingo_inbound_channel'
                AND t.name = %s
            ";

            $posts = $wpdb->get_results($wpdb->prepare($query, $taxonomy));

            $output = fopen($file_path, 'w');
            $header = true;

            foreach ($posts as $post) {
                $content_lines = explode("\n", $post->post_content);
                $taxonomy_value = $post->taxonomy_value;

                if ($header) {
                    $header = false;
                }

                $data_row = array_merge($content_lines, [$taxonomy_value]);
                fputcsv($output, $data_row);
            }
            fclose($output);
        }
    }
}
add_action('export_all_flamingo_event', 'export_all_flamingo_auto');

// Função para executar a exportação após envio do formulário do Contact Form 7
function run_export_after_cf7_submission($contact_form) {
    export_all_flamingo_auto(); // Chama a função que faz a exportação
}
add_action('wpcf7_mail_sent', 'run_export_after_cf7_submission');
