<?php
/*
Plugin Name: URL Link Finder
Description: Finds all links to a specified URL in your WordPress content
Version: 1.0
Author: Your Name
*/

// Add menu item to WordPress admin
function url_finder_menu() {
    add_menu_page(
        'URL Link Finder',
        'URL Link Finder',
        'manage_options',
        'url-link-finder',
        'url_finder_page',
        'dashicons-search'
    );
}
add_action('admin_menu', 'url_finder_menu');

// Create the admin page
function url_finder_page() {
    $search_url = isset($_POST['search_url']) ? sanitize_text_field($_POST['search_url']) : '';
    $results = array();
    
    if (!empty($search_url)) {
        // Search in posts and pages
        $args = array(
            'post_type' => array('post', 'page'),
            'posts_per_page' => -1,
        );
        
        $query = new WP_Query($args);
        
        while ($query->have_posts()) {
            $query->the_post();
            $content = get_the_content();
            
            // Extract all URLs from content using regex
            preg_match_all('/(https?:\/\/[^\s<>"\']+)/', $content, $matches);
            
            if (!empty($matches[0])) {
                foreach ($matches[0] as $url) {
                    // Check if URL starts with the search URL
                    if (strpos($url, $search_url) === 0) {
                        $results[] = array(
                            'title' => get_the_title(),
                            'edit_link' => get_edit_post_link(),
                            'view_link' => get_permalink(),
                            'matched_url' => $url
                        );
                        break; // Break after first match in this post
                    }
                }
            }
        }
        wp_reset_postdata();
    }
    ?>
    <div class="wrap">
        <h1>URL Link Finder</h1>
        <form method="post" action="">
            <label for="search_url">Enter URL to search for:</label>
            <input type="url" name="search_url" id="search_url" value="<?php echo esc_attr($search_url); ?>" style="width: 400px;">
            <?php submit_button('Search'); ?>
        </form>
 
        <?php if (!empty($search_url)): ?>
            <h2>Search Results</h2>
            <?php if (!empty($results)): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Page/Post Title</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $result): ?>
                            <tr>
                                <td><?php echo esc_html($result['title']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url($result['edit_link']); ?>">Edit</a> | 
                                    <a href="<?php echo esc_url($result['view_link']); ?>" target="_blank">View</a>
                                    <br>
                                    <small>Matched URL: <?php echo esc_html($result['matched_url']); ?></small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No content found containing the URL: <?php echo esc_html($search_url); ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php
}
