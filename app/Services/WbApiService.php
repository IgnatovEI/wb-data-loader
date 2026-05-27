<?php

namespace App\Services;

use App\Models\Income;
use App\Models\Order;
use App\Models\Sale;
use App\Models\Stock;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WbApiService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.wb.host');
        $this->apiKey  = config('services.wb.key');
    }

    public function fetch(string $entity, string $dateFrom, string $dateTo = null): int
    {
        $map = [
            'sales'   => [Sale::class,   'date'],
            'orders'  => [Order::class,  'date'],
            'stocks'  => [Stock::class,  'date'],
            'incomes' => [Income::class, 'date'],
        ];

        if (!isset($map[$entity])) {
            throw new \InvalidArgumentException("Unknown entity: {$entity}");
        }

        [$modelClass, $dateColumn] = $map[$entity];

        $params = $entity === 'stocks'
            ? ['dateFrom' => $dateFrom]
            : ['dateFrom' => $dateFrom, 'dateTo' => $dateTo ?? $dateFrom];

        $deleteWhere = $entity === 'stocks'
            ? [$dateColumn => $dateFrom]
            : [$dateColumn => [$dateFrom, $dateTo ?? $dateFrom]];

        return $this->fetchAndSave($entity, $modelClass, $params, $deleteWhere);
    }

    private function fetchAndSave(string $endpoint, string $modelClass, array $params, array $deleteWhere): int
    {
        $deleteQuery = $modelClass::query();
        foreach ($deleteWhere as $column => $value) {
            is_array($value)
                ? $deleteQuery->whereBetween($column, $value)
                : $deleteQuery->where($column, $value);
        }
        $deleteQuery->delete();

        $page = 1;
        $limit = 500;
        $totalInserted = 0;

        do {
            $response = Http::get("{$this->baseUrl}/api/{$endpoint}", array_merge($params, [
                'page'  => $page,
                'limit' => $limit,
                'key'   => $this->apiKey,
            ]));

            if ($response->failed()) {
                Log::error("API error: {$endpoint}", ['status' => $response->status()]);
                break;
            }

            $data = $response->json('data');
            if (empty($data)) break;

            $numFields = ['total_price','discount_percent','spp','for_pay',
                          'finished_price','price_with_disc','price','discount'];
            $insertData = array_map(function ($item) use ($numFields) {
                foreach ($numFields as $f) {
                    if (isset($item[$f]) && $item[$f] !== null) {
                        $item[$f] = (float) $item[$f];
                    }
                }
                return $item;
            }, $data);

            $modelClass::insert($insertData);
            $totalInserted += count($insertData);
            $page++;
        } while (count($data) === $limit);

        return $totalInserted;
    }
}
