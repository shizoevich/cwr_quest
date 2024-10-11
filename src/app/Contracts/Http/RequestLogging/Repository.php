<?php
/**
 * Created by PhpStorm.
 * User: zotov_000
 * Date: 07.07.2015
 * Time: 10:58
 */

namespace App\Contracts\Http\RequestLogging;


interface Repository
{

    /**
     *
     * @param \Response $response
     * @return mixed
     */
    public function putResponse($response);

    /**
     *
     * @param Request $request
     * @return mixed
     */
    public function putRequest($request);

    /**
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function put($key, $value);

    /**
     *
     * @param array $logs
     * @return mixed
     */
    public function putSmartFox($logs);

    /**
     *
     * @return bool
     */
    public function save();
}