<?php

namespace ConnectMalves\MultiTenantDB\Models;

use Illuminate\Database\Connection as ConnectionBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\ConnectionResolver;
use MsAlvexx\LaravelPlans\Traits\HasPlans;

class Company extends Model
{
    use HasPlans;

    /**
     * The connection tag.
     *
     * @var string
     */
    protected $connection = "desplanilhe";

    /**
     * Set timestamps off.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The table name.
     *
     * @var string
     */
    protected $table = 'companies';

    /**
     * The attributes.
     *
     * @var array
     */
    protected $fillable = [
        'fullname',
        'phonenumber',
        'company',
        'subdomain',
        'payment_day',
        'amount',
        'active',
        'other_informations'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'other_informations' => 'object',
    ];

    /**
     * Check model is active.
     */
    public function active()
    {
        return $this->active;
    }
}
