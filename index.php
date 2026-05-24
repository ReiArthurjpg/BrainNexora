<?php

declare(strict_types=1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$query = $_GET['query'] ?? '';
$limit = (int) ($_GET['limit'] ?? 5);

$keywords = array_values(array_filter(preg_split('/\s+/', trim(mb_strtolower($query))) ?: []));

if ($keywords === []) {
    echo json_encode([]);
    exit;
}

$vaultPath = __DIR__;
$results = [];

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($vaultPath));

/** @var SplFileInfo $file */
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'md') {
        if (str_contains($file->getPathname(), '.obsidian') || str_contains($file->getPathname(), '.git')) {
            continue;
        }

        $content = file_get_contents($file->getPathname());
        $title = str_replace('.md', '', $file->getFilename());
        
        $searchableText = mb_strtolower($title . ' ' . $content);
        
        $matchCount = 0;
        foreach ($keywords as $keyword) {
            if (str_contains($searchableText, $keyword)) {
                $matchCount++;
            }
        }

        if ($matchCount > 0) {
            $results[] = [
                'id' => md5($file->getPathname()),
                'title' => $title,
                'content' => $content,
                'matchCount' => $matchCount,
            ];
        }
    }
}

usort($results, function($a, $b) {
    return $b['matchCount'] <=> $a['matchCount'];
});

$finalResults = array_slice($results, 0, $limit);

echo json_encode($finalResults);
