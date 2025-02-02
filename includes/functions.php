<?php
// Function to get plugin data by slug
function get_plugin_data_by_slug($plugin_slug) {
    $plugins = get_plugins();
    foreach ($plugins as $plugin_file => $plugin_data) {
        $slug = dirname($plugin_file);
        if ($slug === $plugin_slug) {
            $backup_dir = ABSPATH . 'plugin-library';
            $plugin_version = $plugin_data['Version'];
            $zip_file = $backup_dir . '/' . $plugin_slug . '-' . $plugin_version . '.zip';
            if (file_exists($zip_file)) {
                $plugin_data['zip_url'] = home_url('/plugin-library/' . $plugin_slug . '-' . $plugin_version . '.zip');
            }
            return $plugin_data;
        }
    }
    return null;
}

function create_plugin_zip($plugin_dir, $backup_dir, $plugin_slug, $plugin_version) {
    $zip = new ZipArchive();
    $zip_file = $backup_dir . '/' . $plugin_slug . '-' . $plugin_version . '.zip';

    if ($zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        $root_path = realpath($plugin_dir);
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root_path), RecursiveIteratorIterator::LEAVES_ONLY);

        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $file_path = $file->getRealPath();
                $relative_path = $plugin_slug . '/' . substr($file_path, strlen($root_path) + 1);
                $zip->addFile($file_path, $relative_path);
            }
        }

        $zip->close();
    } else {
        throw new Exception('Failed to create zip file.');
    }
}

add_action('wp_enqueue_scripts', 'plugin_library_enqueue_styles');

// Enqueue FontAwesome
function plugin_library_enqueue_styles() {
    wp_enqueue_style('plugin-library-fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
    wp_enqueue_style('plugin-library-css', plugin_dir_url(__FILE__) . 'assets/css/style.css');
}

?>