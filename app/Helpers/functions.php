<?php

use Illuminate\Support\Facades\Auth;

if (! function_exists('success_response')) {
    function success_response($statusCode = 200) {
        return response()->json(['success' => true], $statusCode);
    }
}

if (! function_exists('random_number')) {
    function random_number($digit = 10) {
        return rand(pow(10, $digit-1), pow(10, $digit)-1);
    }
}
