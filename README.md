TIE Books Manager
Overview
TIE Books Manager is a custom WordPress plugin developed for the WordPress Developer Assignment.

It allows administrators to manage books using a custom post type and restricts access to logged-in users only.

Features
Custom Post Type: Books
Author Field
Genre Dropdown
Published Date Field
Description Field
Single Book Template
Books Listing Shortcode
Login Restriction
Pagination (5 books per page)
AJAX Filter by Author
AJAX Filter by Genre
Responsive Design
Input Sanitization and Validation
Installation
Upload the plugin folder to:
wp-content/plugins/

Login to WordPress Dashboard

Go to:

Plugins → Installed Plugins

Activate:
TIE Books Manager

Go to:
Settings → Permalinks

Click:
Save Changes

Usage
Add Books
Dashboard → Books → Add New

Fill:

Title
Author
Genre
Published Date
Description
Publish the book.

Display Books
Create a page.

Add shortcode:

[books_list]

Publish the page.

Access Restriction
Only logged-in users can view:

Books Listing Page
Single Book Pages
Logged-out users see a login message.

AJAX Filtering
Users can filter books by:

Author
Genre
Results are displayed without page reload using AJAX.

Security
This plugin uses:

wp_nonce_field()
wp_verify_nonce()
sanitize_text_field()
sanitize_textarea_field()
esc_html()
esc_attr()
esc_url()
current_user_can()
Author
Kiran Khade
