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
* @package src\Integration
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
     * Возвращает ответ от API
     * 
     * @param array $request
     * @throws ProviderException
     * @return array
     */
    public function get(array $input): array
    {
        try {
            $cacheKey  = self::getCacheKey($input);
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
    public static function getCacheKey(array $input)
    {
        return md5(json_encode($input));
    }
}
