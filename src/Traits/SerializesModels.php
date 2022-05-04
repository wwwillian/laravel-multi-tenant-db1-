<?php

namespace ConnectMalves\MultiTenantDB\Traits;

use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;
use ReflectionClass;
use ReflectionProperty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\ConnectionResolver;

trait SerializesModels
{
    use SerializesAndRestoresModelIdentifiers;

    /**
     * Prepare the instance for serialization.
     *
     * @return array
     */
    public function __sleep()
    {
        $properties = (new ReflectionClass($this))->getProperties();

        foreach ($properties as $property) {
            $property->setValue($this, $this->getSerializedPropertyValue(
                $this->getPropertyValue($property)
            ));
        }

        return array_values(array_filter(array_map(function ($p) {
            return $p->isStatic() ? null : $p->getName();
        }, $properties)));
    }

    /**
     * Configure tentant environment before starts unserialization.
     *
     * @return void
     */
    public function configureTenantEnvironment()
    {
        $property = ((new ReflectionClass($this))->getProperty('company'));
        $company = $this->getRestoredPropertyValue($this->getPropertyValue($property));
        app()->instance('Company', $company);

        config(['database.connections.clients.database' => $company->subdomain]);

        $resolver = Model::getConnectionResolver();
        $resolver->setDefaultConnection($company->subdomain);
        Model::setConnectionResolver($resolver);

        DB::setDefaultConnection($company->subdomain);
    }


    /**
     * Restore the model after serialization.
     *
     * @return void
     */
    public function __wakeup()
    {
        $this->configureTenantEnvironment();

        foreach ((new ReflectionClass($this))->getProperties() as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $property->setValue($this, $this->getRestoredPropertyValue(
                $this->getPropertyValue($property)
            ));
        }
    }

    /**
     * Get the property value for the given property.
     *
     * @param  \ReflectionProperty  $property
     * @return mixed
     */
    protected function getPropertyValue(ReflectionProperty $property)
    {
        $property->setAccessible(true);

        return $property->getValue($this);
    }
}
