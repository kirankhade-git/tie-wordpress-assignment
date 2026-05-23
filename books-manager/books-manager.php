<?php
/**
 * Plugin Name: TIE Books Manager
 * Description: Custom Books Management System with restricted access, shortcode listing, pagination, and AJAX filters.
 * Version: 1.0.0
 * Author: Kiran Khade
 */

if (!defined('ABSPATH')) {
    exit;
}

define('TIE_BOOKS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TIE_BOOKS_PLUGIN_PATH', plugin_dir_path(__FILE__));

class TIE_Books_Manager {

    public function __construct() {
        add_action('init', [$this, 'register_books_cpt']);
        add_action('add_meta_boxes', [$this, 'add_book_meta_boxes']);
        add_action('save_post_book', [$this, 'save_book_meta']);
        add_shortcode('books_list', [$this, 'books_list_shortcode']);

        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_filter('single_template', [$this, 'load_single_book_template']);
        add_action('template_redirect', [$this, 'restrict_book_pages']);

        add_action('wp_ajax_filter_books', [$this, 'ajax_filter_books']);
        add_action('wp_ajax_nopriv_filter_books', [$this, 'ajax_filter_books']);
    }

    public function register_books_cpt() {
        $labels = [
            'name' => 'Books',
            'singular_name' => 'Book',
            'add_new' => 'Add New Book',
            'add_new_item' => 'Add New Book',
            'edit_item' => 'Edit Book',
            'new_item' => 'New Book',
            'view_item' => 'View Book',
            'search_items' => 'Search Books',
            'not_found' => 'No books found',
            'menu_name' => 'Books',
        ];

        $args = [
            'labels' => $labels,
            'public' => true,
            'has_archive' => true,
            'rewrite' => ['slug' => 'books'],
            'menu_icon' => 'dashicons-book-alt',
            'supports' => ['title', 'editor', 'thumbnail'],
            'show_in_rest' => true,
        ];

        register_post_type('book', $args);
    }

    public function add_book_meta_boxes() {
        add_meta_box(
            'tie_book_details',
            'Book Details',
            [$this, 'book_meta_box_html'],
            'book',
            'normal',
            'high'
        );
    }

    public function book_meta_box_html($post) {
        wp_nonce_field('tie_save_book_meta', 'tie_book_nonce');

        $author = get_post_meta($post->ID, '_book_author', true);
        $genre = get_post_meta($post->ID, '_book_genre', true);
        $published_date = get_post_meta($post->ID, '_book_published_date', true);
        $description = get_post_meta($post->ID, '_book_description', true);

        $genres = ['Fiction', 'Non-Fiction', 'Sci-Fi', 'Biography', 'Mystery', 'Fantasy'];
        ?>

        <p>
            <label><strong>Author:</strong></label><br>
            <input type="text" name="book_author" value="<?php echo esc_attr($author); ?>" style="width:100%;">
        </p>

        <p>
            <label><strong>Genre:</strong></label><br>
            <select name="book_genre" style="width:100%;">
                <option value="">Select Genre</option>
                <?php foreach ($genres as $g): ?>
                    <option value="<?php echo esc_attr($g); ?>" <?php selected($genre, $g); ?>>
                        <?php echo esc_html($g); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label><strong>Published Date:</strong></label><br>
            <input type="date" name="book_published_date" value="<?php echo esc_attr($published_date); ?>" style="width:100%;">
        </p>

        <p>
            <label><strong>Description:</strong></label><br>
            <textarea name="book_description" rows="5" style="width:100%;"><?php echo esc_textarea($description); ?></textarea>
        </p>

        <?php
    }

    public function save_book_meta($post_id) {
        if (!isset($_POST['tie_book_nonce']) || !wp_verify_nonce($_POST['tie_book_nonce'], 'tie_save_book_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        update_post_meta($post_id, '_book_author', sanitize_text_field($_POST['book_author'] ?? ''));
        update_post_meta($post_id, '_book_genre', sanitize_text_field($_POST['book_genre'] ?? ''));
        update_post_meta($post_id, '_book_published_date', sanitize_text_field($_POST['book_published_date'] ?? ''));
        update_post_meta($post_id, '_book_description', sanitize_textarea_field($_POST['book_description'] ?? ''));
    }

    public function enqueue_assets() {
        wp_enqueue_style(
            'tie-books-style',
            TIE_BOOKS_PLUGIN_URL . 'assets/css/books-style.css',
            [],
            '1.0.0'
        );

        wp_enqueue_script(
            'tie-books-filter',
            TIE_BOOKS_PLUGIN_URL . 'assets/js/books-filter.js',
            ['jquery'],
            '1.0.0',
            true
        );

        wp_localize_script('tie-books-filter', 'tieBooksAjax', [
            'ajax_url' => admin_url('admin-ajax.php'),
        ]);
    }

    public function restrict_book_pages() {
        if ((is_singular('book') || is_post_type_archive('book')) && !is_user_logged_in()) {
            wp_die(
                '<h2>You must be logged in to view this content.</h2>
                <p>Please log in or register.</p>
                <p><a href="' . esc_url(wp_login_url(get_permalink())) . '">Login Here</a></p>',
                'Restricted Content',
                ['response' => 403]
            );
        }
    }

    public function load_single_book_template($template) {
        if (is_singular('book')) {
            $custom_template = TIE_BOOKS_PLUGIN_PATH . 'templates/single-book-template.php';

            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }

        return $template;
    }

    public function books_list_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '<div class="tie-login-message">
                You must be logged in to view this content. Please log in or register.
                <br><a href="' . esc_url(wp_login_url(get_permalink())) . '">Login Here</a>
            </div>';
        }

        $paged = get_query_var('paged') ? get_query_var('paged') : 1;

        $args = [
            'post_type' => 'book',
            'post_status' => 'publish',
            'posts_per_page' => 5,
            'paged' => $paged,
        ];

        $query = new WP_Query($args);

        ob_start();
        ?>

        <div class="tie-books-wrapper">
            <h2>Books Collection</h2>

            <div class="tie-books-filters">
                <input type="text" id="book-author-filter" placeholder="Search by Author">

                <select id="book-genre-filter">
                    <option value="">All Genres</option>
                    <option value="Fiction">Fiction</option>
                    <option value="Non-Fiction">Non-Fiction</option>
                    <option value="Sci-Fi">Sci-Fi</option>
                    <option value="Biography">Biography</option>
                    <option value="Mystery">Mystery</option>
                    <option value="Fantasy">Fantasy</option>
                </select>

                <button id="filter-books-btn">Filter</button>
            </div>

            <div id="tie-books-results">
                <?php $this->render_books_list($query); ?>
            </div>
        </div>

        <?php
        wp_reset_postdata();

        return ob_get_clean();
    }

    private function render_books_list($query) {
        if ($query->have_posts()) {
            echo '<div class="tie-books-grid">';

            while ($query->have_posts()) {
                $query->the_post();

                $author = get_post_meta(get_the_ID(), '_book_author', true);
                $genre = get_post_meta(get_the_ID(), '_book_genre', true);

                echo '<div class="tie-book-card">';
                echo '<h3><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></h3>';
                echo '<p><strong>Author:</strong> ' . esc_html($author) . '</p>';
                echo '<p><strong>Genre:</strong> ' . esc_html($genre) . '</p>';
                echo '</div>';
            }

            echo '</div>';

            echo '<div class="tie-pagination">';
            echo paginate_links([
                'total' => $query->max_num_pages,
                'current' => max(1, get_query_var('paged')),
            ]);
            echo '</div>';
        } else {
            echo '<p>No books found.</p>';
        }
    }

    public function ajax_filter_books() {
        if (!is_user_logged_in()) {
            wp_send_json_error('Login required');
        }

        $author = sanitize_text_field($_POST['author'] ?? '');
        $genre = sanitize_text_field($_POST['genre'] ?? '');

        $meta_query = ['relation' => 'AND'];

        if (!empty($author)) {
            $meta_query[] = [
                'key' => '_book_author',
                'value' => $author,
                'compare' => 'LIKE',
            ];
        }

        if (!empty($genre)) {
            $meta_query[] = [
                'key' => '_book_genre',
                'value' => $genre,
                'compare' => '=',
            ];
        }

        $args = [
            'post_type' => 'book',
            'post_status' => 'publish',
            'posts_per_page' => 5,
            'meta_query' => $meta_query,
        ];

        $query = new WP_Query($args);

        ob_start();

        $this->render_books_list($query);

        wp_reset_postdata();

        wp_send_json_success(ob_get_clean());
    }
}

new TIE_Books_Manager();