# TIE Books Manager

## Overview

TIE Books Manager is a custom WordPress plugin developed for the WordPress Developer Assignment.

It allows administrators to manage books using a custom post type and restricts access to logged-in users only.

## Features

- Custom Post Type: Books
- Author Field
- Genre Dropdown
- Published Date Field
- Description Field
- Single Book Template
- Books Listing Shortcode
- Login Restriction
- Pagination (5 books per page)
- AJAX Filter by Author
- AJAX Filter by Genre
- Responsive Design
- Input Sanitization and Validation

## Installation

1. Upload the plugin folder to `wp-content/plugins/`
2. Activate the plugin
3. Go to **Settings → Permalinks**
4. Click **Save Changes**

## Usage

### Add Books

Dashboard → Books → Add New

### Display Books

Use shortcode:

`[books_list]`

## Access Restriction

Only logged-in users can view:

- Books Listing Page
- Single Book Pages

## AJAX Filtering

- Filter by Author
- Filter by Genre
- Results update without page refresh

## Security

- wp_nonce_field()
- wp_verify_nonce()
- sanitize_text_field()
- sanitize_textarea_field()
- esc_html()
- esc_attr()
- esc_url()
- current_user_can()

## Author

Kiran Khade
