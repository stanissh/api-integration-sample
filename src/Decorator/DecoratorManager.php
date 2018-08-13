<?php

namespace src\Decorator;

use DateTime;
use Exception;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use src\Integration\DataProviderInterface;
use src\Exceptions\RunTimeException;

/**
* Класс-декоратор для получение данных посредством оставщика API
* @package src\Decorator
*/
class DecoratorManager
{
    /** @var DataProviderInterface API-провайдер */
    private $provider;
    /** @var LoggerInterface Логгер */
    private $logger;
    /** @var LoggerInterface Кэш */
    private $cache;

    /**
     * Constructor
     *
     * @param DataProviderInterface $provider
     */
    public function __construct(DataProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Устанавливает логгер
     * 
     * @param LoggerInterface $logger
     * @return void
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Устанавливает кэш
     * 
     * @param CacheItemPoolInterface $cache
     * @return void
     */
    public function setCache(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Делает HTTP-запрос к API. Перед запросом проверяется, есть ли данные в кэше.
     * Данные берутся из кэша, если они там есть. Иначе делается запрос к API.
     * Результат запроса записывается в кэш на один день.
     * 
     * @param array $request
     * @throws ProviderException
     * @return array
     */
    public function get(array $input): array
    {
        try {
            $cacheKey  = $this->getCacheKey($input);
            $cacheItem = $this->cache->getItem($cacheKey);

            // Если ответ есть в кэше
            if ($cacheItem->isHit()) {
                // Возвращаем результат из кэша
                return $cacheItem->get();
            }

            $result = $this->provider->get($input);

            $cacheItem
                ->set($result)
                ->expiresAt(
                    (new DateTime())->modify('+1 day')
                );

            return $result;
        } catch (Exception $e) {
            $msg  = $e->getMessage();
            $code = $e->getCode();

            $this->logger->critical($msg, $code);

            throw new RunTimeException($msg, $code);
        }
    }

    /**
     * Возвращает хэш-сумму переданного массива
     * 
     * @param array $input
     * @return string
     */
    public function getCacheKey(array $input)
    {
        // В качестве одно из параметра ключа используем хост API, 
        // что бы разгроничить кэширование по хостам
        return md5($this->provider->getHost() . json_encode($input));
    }
}
