<?php

/**
* Интерфейс для подключения к API
* @package src\Integration
*/
interface DataProviderInterface
{
    /**
     * Выполняет GET запрос к API
     * 
     * @param array $request
     * @return mixed
     */
    public function get(array $request);
}
