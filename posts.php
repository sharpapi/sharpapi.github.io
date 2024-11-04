<?php

// Load Composer's autoload
require_once __DIR__ . '/vendor/autoload.php';

try {
    // Load the RSS feed and convert it to an array
    $feed = Feed::loadAtom('https://sharpapi.com/feed')->toArray();
} catch (Exception $e) {
    echo "Failed to load feed: ", $e->getMessage();
    exit(1);
}

// Initialize an empty string to store the formatted posts
$posts = '';
foreach ($feed['entry'] as $post) {
    // Extract title, link, and summary
    $title = $post['title'] ?? 'No title';
    $link = $post['link'][0]['@attributes']['href'] ?? '#';
    $description = $post['summary'] ?? '';
    $date = date('d/m/Y', strtotime($post['updated']));

    // Append each post's details in the required format
    $posts .= sprintf(
        "\n* **[%s]** [%s](%s \"%s\")\n  > %s\n",
        $date,
        strip_tags($title),
        $link,
        strip_tags($title),
        strip_tags($description)
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
