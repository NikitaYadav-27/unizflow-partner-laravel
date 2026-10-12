<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as LaravelController;

/**
 * Class BaseController
 */
abstract class BaseController extends LaravelController
{
    use AuthorizesRequests, ValidatesRequests;
}
