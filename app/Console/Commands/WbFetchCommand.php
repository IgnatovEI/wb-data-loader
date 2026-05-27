<?php

namespace App\Console\Commands;

use App\Services\WbApiService;
use Illuminate\Console\Command;

class WbFetchCommand extends Command
{
    protected $signature = 'wb:fetch 
                            {dateFrom : Дата начала в формате Y-m-d}
                            {dateTo? : Дата окончания (если не указана, равна дате начала)}';

    protected $description = 'Загружает данные из API Wildberries по продажам, заказам, складам и доходам';

    public function handle(): int
    {
        $dateFrom = $this->argument('dateFrom');
        $dateTo   = $this->argument('dateTo') ?? $dateFrom;

        $this->info("Начинаем загрузку данных с {$dateFrom} по {$dateTo}...");
        $service = new WbApiService();

        $sales   = $service->fetch('sales',   $dateFrom, $dateTo);
        $this->info("Продажи: {$sales} записей");

        $orders  = $service->fetch('orders',  $dateFrom, $dateTo);
        $this->info("Заказы: {$orders} записей");

        $incomes = $service->fetch('incomes', $dateFrom, $dateTo);
        $this->info("Доходы: {$incomes} записей");

        $stocks  = $service->fetch('stocks',  $dateFrom);
        $this->info("Склады: {$stocks} записей");

        $this->info('Загрузка завершена.');
        return 0;
    }
}
