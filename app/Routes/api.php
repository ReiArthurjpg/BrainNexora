<?php

declare(strict_types=1);

use App\Controllers\HealthController;
use App\Controllers\SearchController;

$healthController = new HealthController();
$searchController = new SearchController();

return [
    ['GET', '/api/health', [$healthController, 'check']],
    ['GET', '/api/search', [$searchController, 'search']],
];
