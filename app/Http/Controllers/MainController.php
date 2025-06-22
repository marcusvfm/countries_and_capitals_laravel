<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MainController extends Controller
{
    private $appData;

    public function __construct()
    {   
        $this->appData = require(app_path("app_data.php"));
    }

    public function showData() {
        return response()->json($this->appData);
    }
}
