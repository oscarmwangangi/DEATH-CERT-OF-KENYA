<?php

session_start();
if ($_SESSION['role'] !== 'main_admin') {
    header("Location: index.php");
    exit();
}

header("Access-Control-Allow-Origin: *");  // Allow all origins
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");  // Allow GET, POST, OPTIONS
header("Access-Control-Allow-Headers: Content-Type");  // Allow Content-Type header
header("Content-Type: application/json");  // Ensure the response is JSON

// Function to fetch data from Wikipedia API
function fetchWikipediaData($searchQuery) {
    $apiUrl = "https://en.wikipedia.org/w/api.php";

    // API parameters for search
    $params = [
        "action" => "query",
        "format" => "json",
        "list" => "search",
        "srsearch" => $searchQuery,
        "utf8" => 1,
        "srlimit" => 5
    ];

    // Build the URL with query parameters
    $url = $apiUrl . "?" . http_build_query($params);

    // Initialize cURL session
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);

    // Check for cURL errors
    if ($response === false) {
        return [
            "status" => "error",
            "message" => "cURL error: " . curl_error($ch)
        ];
    }

    curl_close($ch);
    return json_decode($response, true);
}

// Function to fetch full content of a Wikipedia page
// Function to fetch full content of a Wikipedia page, including images
function fetchWikipediaPageContent($pageTitle) {
    $apiUrl = "https://en.wikipedia.org/w/api.php";

    // API parameters for fetching page content and images
    $params = [
        "action" => "query",
        "format" => "json",
        "prop" => "extracts|images",  // Fetch extracts and image info
        "titles" => $pageTitle,
        "utf8" => 1,
        "exintro" => 1,  // Only fetch the introduction
        "explaintext" => 1  // Fetch plain text, not HTML
    ];

    // Build the URL with query parameters
    $url = $apiUrl . "?" . http_build_query($params);

    // Initialize cURL session
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);

    // Check for cURL errors
    if ($response === false) {
        return [
            "status" => "error",
            "message" => "cURL error: " . curl_error($ch)
        ];
    }

    curl_close($ch);
    return json_decode($response, true);
}

// Handle API request
if (isset($_GET['query'])) {
    $query = htmlspecialchars($_GET['query']);

    // Fetch data from Wikipedia search
    $data = fetchWikipediaData($query);

    if (isset($data['query']['search'])) {
        $results = [];

        foreach ($data['query']['search'] as $item) {
            // Fetch full content and images for each result
            $pageData = fetchWikipediaPageContent($item['title']);
            $pages = $pageData['query']['pages'] ?? [];
            $images = [];

            // Extract images from the page data
            foreach ($pages as $page) {
                $content = $page['extract'] ?? "No content available";

                // Fetch images associated with the page
                if (isset($page['images'])) {
                    foreach ($page['images'] as $image) {
                        // The image title is typically in the format 'File:ImageName.jpg'
                        $imageUrl = "https://en.wikipedia.org/wiki/Special:FilePath/" . urlencode($image['title']);
                        $images[] = $imageUrl;  // Add image URL to the array
                    }
                }
            }

            $results[] = [
                "title" => $item['title'],
                "extract" => $item['snippet'] ?? "No summary available",
                "content" => $content,  // Add full content
                "images" => $images    // Add the images array
            ];
        }

        // Respond with success and results
        echo json_encode([
            "status" => "success",
            "data" => $results
        ]);
    } else {
        // Respond with an error if no results are found
        echo json_encode([
            "status" => "error",
            "message" => "No results found for the query."
        ]);
    }
} else {
    // Respond with an error if no query is provided
    echo json_encode([
        "status" => "error",
        "message" => "Please provide a search query."
    ]);
}