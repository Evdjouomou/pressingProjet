<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class DasboardAdminController extends BaseController
{
    public function index()
    {
        return view('pages/tableauboard');
    }
}