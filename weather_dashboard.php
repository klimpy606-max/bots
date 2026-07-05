<?php
/**
 * لوحة تحكم الطقس - Weather Dashboard
 * تطبيق لعرض بيانات الطقس من API مفتوح المصدر
 */

// استخدام OpenWeatherMap API المجاني
define('WEATHER_API_KEY', 'YOUR_OPENWEATHERMAP_API_KEY'); // احصل على المفتاح من https://openweathermap.org/api
define('WEATHER_API_URL', 'https://api.openweathermap.org/data/2.5/weather');
define('FORECAST_API_URL', 'https://api.openweathermap.org/data/2.5/forecast');

class WeatherDashboard {
    private $api_key;
    private $api_url;
    private $forecast_url;
    
    public function __construct($api_key, $api_url, $forecast_url) {
        $this->api_key = $api_key;
        $this->api_url = $api_url;
        $this->forecast_url = $forecast_url;
    }
    
    /**
     * الحصول على بيانات الطقس الحالية
     * Get current weather data
     */
    public function getCurrentWeather($city, $lang = 'ar') {
        $params = [
            'q' => $city,
            'appid' => $this->api_key,
            'units' => 'metric',
            'lang' => $lang
        ];
        
        $url = $this->api_url . '?' . http_build_query($params);
        
        $response = @file_get_contents($url);
        if ($response === false) {
            return ['error' => 'فشل الاتصال بـ API - Connection failed'];
        }
        
        $data = json_decode($response, true);
        
        if ($data['cod'] != 200) {
            return ['error' => $data['message'] ?? 'خطأ غير معروف'];
        }
        
        return $this->formatWeatherData($data);
    }
    
    /**
     * الحصول على توقعات الطقس لـ 5 أيام
     * Get 5-day forecast
     */
    public function getForecast($city, $lang = 'ar') {
        $params = [
            'q' => $city,
            'appid' => $this->api_key,
            'units' => 'metric',
            'lang' => $lang
        ];
        
        $url = $this->forecast_url . '?' . http_build_query($params);
        
        $response = @file_get_contents($url);
        if ($response === false) {
            return ['error' => 'فشل الاتصال بـ API'];
        }
        
        $data = json_decode($response, true);
        
        if ($data['cod'] != 200) {
            return ['error' => $data['message'] ?? 'خطأ غير معروف'];
        }
        
        return $this->formatForecastData($data);
    }
    
    /**
     * تنسيق بيانات الطقس الحالية
     */
    private function formatWeatherData($data) {
        return [
            'success' => true,
            'city' => $data['name'] . ', ' . $data['sys']['country'],
            'temperature' => round($data['main']['temp'], 1),
            'feels_like' => round($data['main']['feels_like'], 1),
            'humidity' => $data['main']['humidity'],
            'pressure' => $data['main']['pressure'],
            'description' => $data['weather'][0]['description'],
            'icon' => $data['weather'][0]['icon'],
            'wind_speed' => round($data['wind']['speed'], 1),
            'clouds' => $data['clouds']['all'],
            'visibility' => round($data['visibility'] / 1000, 1),
            'sunrise' => date('H:i', $data['sys']['sunrise']),
            'sunset' => date('H:i', $data['sys']['sunset']),
            'timestamp' => date('Y-m-d H:i:s', $data['dt'])
        ];
    }
    
    /**
     * تنسيق بيانات التوقعات
     */
    private function formatForecastData($data) {
        $forecasts = [];
        foreach ($data['list'] as $forecast) {
            $forecasts[] = [
                'date' => date('Y-m-d', $forecast['dt']),
                'time' => date('H:i', $forecast['dt']),
                'temperature' => round($forecast['main']['temp'], 1),
                'description' => $forecast['weather'][0]['description'],
                'icon' => $forecast['weather'][0]['icon'],
                'humidity' => $forecast['main']['humidity'],
                'wind_speed' => round($forecast['wind']['speed'], 1),
                'rain_chance' => isset($forecast['pop']) ? round($forecast['pop'] * 100) : 0
            ];
        }
        
        return [
            'success' => true,
            'city' => $data['city']['name'] . ', ' . $data['city']['country'],
            'forecasts' => $forecasts
        ];
    }
    
    /**
     * الحصول على أيقونة الطقس المناسبة
     */
    public function getWeatherEmoji($description) {
        $description = strtolower($description);
        
        if (strpos($description, 'rain') !== false) return '🌧️';
        if (strpos($description, 'snow') !== false) return '❄️';
        if (strpos($description, 'cloud') !== false) return '☁️';
        if (strpos($description, 'clear') !== false) return '☀️';
        if (strpos($description, 'sunny') !== false) return '🌞';
        if (strpos($description, 'storm') !== false) return '⛈️';
        if (strpos($description, 'wind') !== false) return '💨';
        if (strpos($description, 'fog') !== false) return '🌫️';
        
        return '🌤️';
    }
}

// معالجة الطلبات
$dashboard = new WeatherDashboard(WEATHER_API_KEY, WEATHER_API_URL, FORECAST_API_URL);

$city = $_GET['city'] ?? 'Cairo';
$action = $_GET['action'] ?? 'current';
$lang = $_GET['lang'] ?? 'ar';

if ($action === 'forecast') {
    $result = $dashboard->getForecast($city, $lang);
} else {
    $result = $dashboard->getCurrentWeather($city, $lang);
}

// إرجاع النتائج بصيغة JSON
header('Content-Type: application/json; charset=utf-8');
echo json_encode($result, JSON_UNESCAPED_UNICODE);
?>
