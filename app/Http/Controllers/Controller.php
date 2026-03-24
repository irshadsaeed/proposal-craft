<?php

namespace App\Http\Controllers;

// ─── Add this import ───────────────────────────────────────────────────────
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    // ─── Add AuthorizesRequests here — this is what enables $this->authorize() ──
    use AuthorizesRequests, ValidatesRequests;
}