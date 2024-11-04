<?php

// Load Composer's autoload
require_once __DIR__ . '/vendor/autoload.php';

try {
    // Load the Atom feed and convert it to an array
    $feed = Feed::loadAtom('https://sharpapi.com/feed')->toArray();
} catch (Exception $e) {
    echo "Failed to load feed: ", $e->getMessage();
    exit(1);
}

// Check if 'entry' key exists and contains data
if (!isset($feed['entry']) || !is_array($feed['entry']) || empty($feed['entry'])) {
    echo "Feed data is missing or 'entry' key is not available.\n";
    exit(1);
}

// Generate the list of all blog posts
$posts = '';
foreach ($feed['entry'] as $post) {
    $date = date('d/m/Y', strtotime($post['updated'] ?? ''));
    $posts .= sprintf(
        "\n* **[%s]** [%s](%s \"%s\")",
        $date,
        $post['title'] ?? 'No title',
        $post['link']['@attributes']['href'] ?? '#',
        $post['title'] ?? 'No title'
    );
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
