<?php

// Load Composer's autoload
require_once __DIR__ . '/vendor/autoload.php';

try {
    // Load the RSS feed and convert to an array
    $feed = Feed::loadAtom('https://sharpapi.com/feed')->toArray();
} catch (Exception $e) {
    echo "Failed to load feed: ", $e->getMessage();
    exit(1);
}

// Generate the list of blog posts
$posts = '';
if (isset($feed['entry']) && is_array($feed['entry'])) {
    foreach ($feed['entry'] as $post) {
        // Format the date
        $date = isset($post['updated']) ? date('d/m/Y', strtotime($post['updated'])) : 'Unknown Date';
        
        // Ensure title is available
        $title = $post['title'] ?? 'Untitled';

        // Extract the link URL
        if (isset($post['link'])) {
            if (is_array($post['link'])) {
                $link = $post['link']['@attributes']['href'] ?? '#';
            } else {
                $link = $post['link'];
            }
        } else {
            $link = '#';
        }

        // Extract the description or summary
        $description = '';
        if (isset($post['summary'])) {
            $description = is_string($post['summary']) ? strip_tags($post['summary']) : (isset($post['summary'][0]) ? strip_tags($post['summary'][0]) : '');
        }

        // Format each post entry
        $posts .= sprintf(
            "\n* **[%s]** [%s](%s \"%s\")\n  > %s",
            $date,
            $title,
            $link,
            $title,
            $description
        );
    }
}

// Load README.md content
$readmePath = 'README.md';
$readmeContent = file_get_contents($readmePath);

// Check if README.md contains the posts section and replace or append
if (strpos($readmeContent, '<!-- posts -->') !== false) {
    // Replace content between <!-- posts --> and <!-- /posts -->
    $newContent = preg_replace(
        '#<!-- posts -->.*<!-- /posts -->#s',
        sprintf('<!-- posts -->%s<!-- /posts -->', $posts),
        $readmeContent
    );
} else {
    // Append new posts section at the end if placeholders are missing
    $newContent = $readmeContent . "\n\n<!-- posts -->" . $posts . "<!-- /posts -->";
}

// Write the updated content to README.md
file_put_contents($readmePath, $newContent);

echo "README.md updated successfully with all blog posts.\n";
