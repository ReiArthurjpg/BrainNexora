<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;

class HealthController
{
    public function check(): void
    {
        Response::success('API online.', ['status' => 'ok']);
    }
}
