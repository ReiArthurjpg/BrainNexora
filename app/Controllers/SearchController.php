<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class SearchController
{
    /**
     * Directories to skip when scanning for .md files.
     */
    private const IGNORED_DIRS = ['.obsidian', '.git', 'vendor', 'public', 'app'];

    /**
     * GET /api/search?query=...&limit=5
     *
     * Receives a natural-language question, extracts keywords,
     * searches all .md files in the vault and returns the most
     * relevant documents sorted by match count.
     */
    public function search(): void
    {
        $query = trim((string) ($_GET['query'] ?? ''));
        $limit = max(1, min(50, (int) ($_GET['limit'] ?? 5)));

        if ($query === '') {
            Response::error(
                'O parâmetro "query" é obrigatório.',
                ['query' => 'Informe uma pergunta ou termo de busca.'],
                422
            );
            return;
        }

        $keywords = array_values(
            array_filter(
                preg_split('/\s+/', mb_strtolower($query)) ?: []
            )
        );

        if ($keywords === []) {
            Response::success('Nenhum resultado encontrado.', ['results' => []]);
            return;
        }

        $vaultPath = dirname(__DIR__, 2); // project root
        $results = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($vaultPath)
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'md') {
                continue;
            }

            $pathname = $file->getPathname();

            // Skip ignored directories
            foreach (self::IGNORED_DIRS as $ignored) {
                if (str_contains($pathname, DIRECTORY_SEPARATOR . $ignored . DIRECTORY_SEPARATOR)
                    || str_contains($pathname, '/' . $ignored . '/')) {
                    continue 2;
                }
            }

            $content = file_get_contents($pathname);
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
                    'id' => md5($pathname),
                    'title' => $title,
                    'content' => $content,
                    'matchCount' => $matchCount,
                ];
            }
        }

        // Sort by relevance (most matches first)
        usort($results, fn(array $a, array $b) => $b['matchCount'] <=> $a['matchCount']);

        $finalResults = array_slice($results, 0, $limit);

        Response::success('Busca realizada com sucesso.', [
            'query' => $query,
            'total' => count($finalResults),
            'results' => $finalResults,
        ]);
    }
}
