<?php

namespace App\Http\RequestLogging;

use App\Contracts\Http\RequestLogging\Factory;
use \DB;

class RequestLoggingManager implements Factory
{

    /**
     * The application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;
    /**
     * The array of resolved cache stores.
     *
     * @var array
     */
    protected $stores = [];

    /**
     * Create a new  RequestLogging manager instance.
     *
     * @param  \Illuminate\Foundation\Application $app
     */
    function __construct($app)
    {
        $this->app = $app;
    }


    /**
     * Get a RequestLogging store instance by name.
     *
     * @param  string|null $name
     * @return mixed
     */
    public function store($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();
        $this->stores[$name] = $this->get($name);

        return $this->stores[$name];
    }

    /**
     * Get a RequestLogging driver instance.
     *
     * @param  string $driver
     * @return mixed
     */
    public function driver($driver = null)
    {
        return $this->store($driver);
    }

    /**
     * Get the RequestLogging cache driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['request_logging.default'];
    }

    /**
     * Set the default RequestLogging driver name.
     *
     * @param  string $name
     * @return void
     */
    public function setDefaultDriver($name)
    {
        $this->app['config']['request_logging.default'] = $name;
    }

    /**
     * Get the RequestLogging connection configuration.
     *
     * @param  string $name
     * @return array
     */
    protected function getConfig($name)
    {
        return $this->app['config']["request_logging.stores.{$name}"];
    }

    /**
     * Attempt to get the store from the local RequestLogging.
     *
     * @param  string $name
     * @return \Illuminate\Contracts\Cache\Repository
     */
    protected function get($name)
    {
        return isset($this->stores[$name]) ? $this->stores[$name] : $this->resolve($name);
    }

    /**
     * Resolve the given store.
     *
     * @param  string $name
     * @return \App\Contracts\Http\RequestLogging\Repository
     * @throws \InvalidArgumentException
     */
    protected function resolve($name)
    {
        $config = $this->getConfig($name);
        if (is_null($config)) {
            throw new \InvalidArgumentException("RequestLogging store [{$name}] is not defined.");
        }

        return $this->{'create' . ucfirst($config['driver']) . 'Driver'}($config);
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @param  string $method
     * @param  array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->store(), $method], $parameters);
    }

    /**
     * Create an instance of the Mongo RequestLogging driver.
     *
     * @param  array $config
     * @return \App\Http\RequestLogging\MongoStore
     */
    protected function createMongoDriver(array $config)
    {
        try {
            $mongo = DB::connection($config['connection']);

            return new MongoStore($mongo, $config['table']);
        } catch (\Exception $e) {
            \Log::critical($e);
            \App\Helpers\SentryLogger::captureException($e);

            return new MongoStore(null, $config['table']);
        }
    }

    /**
     * Create an instance of the Null RequestLogging driver.
     *
     * @param  array $config
     * @return \App\Http\RequestLogging\NullStore
     */
    protected function createNullDriver(array $config)
    {
        return new NullStore();
    }

    /**
     * Create an instance of the Mysql RequestLogging driver.
     *
     * @param array $config
     * @return \App\Http\RequestLogging\MysqlStore
     */
    protected function createMysqlDriver(array $config)
    {
        $mysql = DB::connection($config['connection']);

        return new MysqlStore($mysql, $config['table']);
    }
}