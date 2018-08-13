<?php

namespace src\Integration;

/**
* Класс для получение данных через API
* @package src\Integration
*/
class DataProvider implements DataProviderInterface
{
    /** @var string HTTP-хост API */
    private $host;
    /** @var string Имя пользователя */
    private $user;
    /** @var string Пароль */
    private $password;

    /**
     * Constructor
     *
     * @param $host
     * @param $user
     * @param $password
     */
    public function __construct($host, $user, $password)
    {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
    }
    
    /**
     * Выполняет GET запрос к API
     * 
     * @param array $request
     * @return array
     */
    public function get(array $request)
    {
        // returns a response from external service
    }

    /**
     * Возвращает хост API
     * 
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }
}
