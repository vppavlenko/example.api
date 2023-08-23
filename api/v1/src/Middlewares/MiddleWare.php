<?php

namespace nvs\api\v1\Middlewares;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface MiddleWare
{
    public function handle(Request $request, Application $app): ?Response;
}
