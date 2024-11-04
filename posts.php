<?php

// Load Composer's autoload
require_once __DIR__ . '/vendor/autoload.php';

use Suin\RSSWriter\Feed;

// Load the feed
try {
    $feedUrl = 'https://sharpapi.com/feed';
    $xmlContent = file_get_contents($feedUrl);
    if (!$xmlContent) {
        throw new Exception("Failed to load feed from $feedUrl");
    }
    $xml = new SimpleXMLElement($xmlContent);
} catch (Exception $e) {
    echo "Failed to load feed: ", $e->getMessage();
    exit(1);
}

// Initialize the posts list
$posts = '';

// Iterate over each entry in the feed
foreach ($xml->entry as $entry) {
    $title = (string) $entry->title;
    $link = (string) $entry->link['href'];
    $date = date('d/m/Y', strtotime($entry->updated));
    $summary = strip_tags((string) $entry->summary); // Remove any HTML tags for a clean summary

    // Append each entry to the posts list
    //$posts .= sprintf("\n* **[%s]** [%s](%s \"%s\")\n  > %s", $date, $title, $link, $title, $summary);
    $posts .= sprintf("\n* **[%s]** [%s](%s \"%s\")\n > %s\n\n", $date, $title, $link, $title, trim($summary));

}

// Load README.md content
$readmePath = 'README.md';
$readmeContent = file_get_contents($readmePath);

// Replace or append the posts section in README.md
if (strpos($readmeContent, '<!-- posts -->') !== false) {
    $newContent = preg_replace(
        '#<!-- posts -->.*<!-- /posts -->#s',
        sprintf('<!-- posts -->%s<!-- /posts -->', $posts),
        $readmeContent
    );
} else {
    $newContent = $readmeContent . "\n\n<!-- posts -->" . $posts . "<!-- /posts -->";
}

// Write the updated content to README.md
file_put_contents($readmePath, $newContent);

echo "README.md updated successfully with all blog posts.\n";
