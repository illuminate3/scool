<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Specialty.
 *
 * @package App\Models
 */
class Specialty extends Model
{
    protected $guarded = [];

    /**
     * Find by code.
     *
     * @param $code
     * @return mixed
     */
    public static function findByCode($code)
    {
        return static::where('code','=',$code)->first();

    }
}
