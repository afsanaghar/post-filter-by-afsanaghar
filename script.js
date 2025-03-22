jQuery(document).ready(function ($) {
    $('#epf-filter-form').on('submit', function (e) {
        e.preventDefault(); // Prevent form submission
        $.ajax({
            url: epf_ajax.ajax_url, // AJAX URL
            type: 'POST',
            data: {
                action: 'epf_filter_posts', // WordPress AJAX action
                search: $('input[name="search"]').val(), // Search term
                category: $('select[name="category"]').val(), // Selected category
            },
            success: function (response) {
                $('#epf-filter-results').html(response); // Display results
            },
        });
    });

    // Reset the form and clear results
    $('#epf-filter-form').on('reset', function () {
        $('#epf-filter-results').html('');
    });
});