<?php

namespace Kanagama\CsvToSeeder\Tests\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @author k-nagama <k-nagama@se-ec.co.jp>
 */
class User extends Model
{
    /**
     * @var array
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
    ];
}
