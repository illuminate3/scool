<?php

namespace App\Http\Controllers\Tenant\Api\Changelog;

use App\Http\Controllers\Tenant\Controller;
use App\Http\Requests\Incidents\ListChangelog;
use App\Models\Log;


/**
 * Class ChangelogController
 * @package App\Http\Controllers\Tenant\Api\Changelog
 */
class ChangelogController extends Controller
{
    /**
     * @param ListChangelog $request
     * @return mixed
     */
    public function index(ListChangelog $request)
    {
        return map_collection(Log::all());
    }
}