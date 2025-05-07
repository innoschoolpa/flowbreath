<?php

namespace App\Controllers;

use App\Core\Request;

class HealthController extends BaseController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function check()
    {
        return $this->json([
            'status' => 'ok',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0.0'
        ]);
    }
} 