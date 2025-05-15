<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class WeatherService
{
    private const API_KEY = 'ee5a22b858faf241113576b4455300d2'; // Replace with your actual API key
    private const API_URL = 'https://api.openweathermap.org/data/2.5/weather';

    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;

    public function __construct(HttpClientInterface $httpClient, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    /**
     * Get current weather for a city
     *
     * @param string $city The city name
     * @param string $units The units (metric, imperial, standard)
     * @return array|null Weather data or null if not found
     */
    public function getCurrentWeather(string $city, string $units = 'metric'): ?array
    {
        if (empty($city)) {
            return null;
        }

        try {
            $response = $this->httpClient->request('GET', self::API_URL, [
                'query' => [
                    'q' => $city,
                    'appid' => self::API_KEY,
                    'units' => $units
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                $data = $response->toArray();
                
                return [
                    'temperature' => $data['main']['temp'] ?? null,
                    'description' => $data['weather'][0]['description'] ?? null,
                    'icon' => $data['weather'][0]['icon'] ?? null,
                    'humidity' => $data['main']['humidity'] ?? null,
                    'wind_speed' => $data['wind']['speed'] ?? null,
                    'city' => $data['name'] ?? $city,
                    'country' => $data['sys']['country'] ?? null,
                    'icon_url' => 'https://openweathermap.org/img/wn/' . ($data['weather'][0]['icon'] ?? '01d') . '@2x.png',
                ];
            }
        } catch (\Exception $e) {
            $this->logger->error('Weather API error: ' . $e->getMessage());
        }

        return null;
    }
}