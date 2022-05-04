<?php

namespace ConnectMalves\MultiTenantDB\Events;

use Illuminate\Queue\SerializesModels;

class CompanyAuthenticated
{
    use SerializesModels;

    /**
     * The settings
     *
     * @var object;
     */
    public $settings;

   /**
    * The constructor
    */
    public function __construct(object $settings)
    {
        $this->settings = $settings;
    }
}