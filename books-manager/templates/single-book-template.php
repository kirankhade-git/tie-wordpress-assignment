<?php
get_header();

if (!is_user_logged_in()) {
    echo '<div class="tie-login-message">You must be logged in to view this content. Please log in or register.</div>';
    get_footer();
    exit;
}

while (have_posts()) {
    the_post();

    $author = get_post_meta(get_the_ID(), '_book_author', true);
    $genre = get_post_meta(get_the_ID(), '_book_genre', true);
    $published_date = get_post_meta(get_the_ID(), '_book_published_date', true);
    $description = get_post_meta(get_the_ID(), '_book_description', true);
    ?>

    <main class="tie-single-book">
        <div class="tie-single-book-card">
            <h1><?php the_title(); ?></h1>

            <p><strong>Author:</strong> <?php echo esc_html($author); ?></p>
            <p><strong>Genre:</strong> <?php echo esc_html($genre); ?></p>
            <p><strong>Published Date:</strong> <?php echo esc_html($published_date); ?></p>

            <div class="tie-book-description">
                <h3>Description</h3>
                <p><?php echo esc_html($description); ?></p>
            </div>

            <div class="tie-main-content">
                <?php the_content(); ?>
            </div>
        </div>
    </main>

    <?php
}

get_footer();