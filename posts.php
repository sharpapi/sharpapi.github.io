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

// Limit to the latest 5 posts (adjust if needed)
$posts = '';
foreach (array_slice($feed['item'], 0, 5) as $post) {
    $date = date('d/m/Y', strtotime($post['pubDate']));
    $posts .= sprintf("\n* **[%s]** [%s](%s \"%s\")", $date, $post['title'], $post['link'], $post['title']);
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

echo "README.md updated successfully with the latest blog posts.\n";
