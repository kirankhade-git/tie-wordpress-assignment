jQuery(document).ready(function ($) {
    $('#filter-books-btn').on('click', function () {
        let author = $('#book-author-filter').val();
        let genre = $('#book-genre-filter').val();

        $.ajax({
            url: tieBooksAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'filter_books',
                author: author,
                genre: genre
            },
            beforeSend: function () {
                $('#tie-books-results').html('<p>Loading books...</p>');
            },
            success: function (response) {
                if (response.success) {
                    $('#tie-books-results').html(response.data);
                } else {
                    $('#tie-books-results').html('<p>Please login to view books.</p>');
                }
            }
        });
    });
});