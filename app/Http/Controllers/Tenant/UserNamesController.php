<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Requests\GetUser;
use App\Models\User;

/**
 * Class UserNamesController.
 *
 * @package App\Http\Controllers
 */
class UserNamesController extends Controller
{

    protected $repository;

    /**
     * @param GetUser $request
     * @param $tenant
     * @param User $user
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function get(GetUser $request, $tenant, $name)
    {
        return User::where('name',$name)->firstOrFail()->map();
    }


}
