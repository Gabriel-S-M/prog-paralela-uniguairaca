<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Throwable;

class ProcessCrmWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [5, 30, 120];

    public function __construct(private array $deal, private string $requestId)
    {
        $this->onQueue('high');
    }

    public function handle(): void
    {
        Log::withContext(['request_id' => $this->requestId, 'deal_id' => $this->deal['id'] ?? null]);
        Log::info('Iniciando persistência do negócio e organização');

        $dealId = DB::transaction(function (): int {
            $organization = $this->deal['organization'];
            DB::table('crm_organizations')->updateOrInsert(
                ['external_id' => $organization['id']],
                [
                    'name' => $organization['name'],
                    'cnpj' => $organization['cnpj'] ?? null,
                    'industry' => $organization['industry'] ?? null,
                    'employee_count' => $organization['employee_count'] ?? null,
                    'tier' => $organization['tier'] ?? null,
                    'address' => json_encode($organization['address'] ?? null),
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
            $organizationId = DB::table('crm_organizations')->where('external_id', $organization['id'])->value('id');

            DB::table('crm_deals')->updateOrInsert(
                ['external_id' => $this->deal['id'] ?? $this->requestId],
                [
                    'title' => $this->deal['title'],
                    'pipeline' => $this->deal['pipeline'],
                    'stage' => $this->deal['stage'],
                    'amount' => $this->deal['amount'],
                    'currency' => strtoupper($this->deal['currency']),
                    'probability' => $this->deal['probability'],
                    'expected_close_date' => isset($this->deal['expected_close_date'])
                        ? Carbon::parse($this->deal['expected_close_date'])->utc()->format('Y-m-d H:i:s')
                        : null,
                    'owner' => json_encode($this->deal['owner']),
                    'organization_id' => $organizationId,
                    'request_id' => $this->requestId,
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );

            return (int) DB::table('crm_deals')->where('external_id', $this->deal['id'] ?? $this->requestId)->value('id');
        });

        Log::info('Negócio e organização persistidos', ['database_deal_id' => $dealId]);

        foreach (['contacts', 'line_items', 'activities', 'extras'] as $type) {
            CrmRelatedDataJob::dispatch($dealId, $this->deal, $type, $this->requestId)
                ->onQueue($type === 'contacts' ? 'default' : 'bulk');
        }
        Log::info('Jobs das entidades relacionadas enfileirados', ['database_deal_id' => $dealId]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Falha definitiva ao processar webhook CRM', [
            'request_id' => $this->requestId,
            'deal_id' => $this->deal['id'] ?? null,
            'exception' => $exception->getMessage(),
        ]);
    }
}
