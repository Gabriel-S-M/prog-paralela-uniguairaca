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

class CrmRelatedDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [5, 30, 120];

    public function __construct(
        private int $dealId,
        private array $deal,
        private string $type,
        private string $requestId,
    ) {}

    public function handle(): void
    {
        Log::withContext(['request_id' => $this->requestId, 'deal_id' => $this->deal['id'] ?? null, 'entity' => $this->type]);
        Log::info('Iniciando persistência da entidade relacionada');

        match ($this->type) {
            'contacts' => $this->saveContacts(),
            'line_items' => $this->saveLineItems(),
            'activities' => $this->saveActivities(),
            'extras' => $this->saveExtras(),
            default => throw new \InvalidArgumentException('Tipo de entidade CRM inválido.'),
        };

        Log::info('Entidade relacionada persistida com sucesso');
    }

    private function saveContacts(): void
    {
        $organizationId = DB::table('crm_organizations')
            ->where('external_id', $this->deal['organization']['id'])->value('id');
        $contacts = array_merge(
            [array_merge($this->deal['primary_contact'], ['_relationship' => 'primary'])],
            array_map(fn (array $contact) => array_merge($contact, ['_relationship' => 'stakeholder']), $this->deal['stakeholders'] ?? []),
        );

        foreach ($contacts as $contact) {
            DB::transaction(function () use ($contact, $organizationId): void {
                DB::table('crm_contacts')->updateOrInsert(
                    ['external_id' => $contact['id']],
                    [
                        'organization_id' => $organizationId,
                        'contact_type' => $contact['_relationship'],
                        'first_name' => $contact['first_name'] ?? null,
                        'last_name' => $contact['last_name'] ?? null,
                        'name' => $contact['name'] ?? null,
                        'title' => $contact['title'] ?? null,
                        'role' => $contact['role'] ?? null,
                        'email' => $contact['email'],
                        'phone' => $contact['phone'] ?? null,
                        'decision_role' => $contact['decision_role'] ?? null,
                        'preferred_channel' => $contact['preferred_channel'] ?? null,
                        'influence_level' => $contact['influence_level'] ?? null,
                        'sentiment' => $contact['sentiment'] ?? null,
                        'notes' => $contact['notes'] ?? null,
                        'updated_at' => now(), 'created_at' => now(),
                    ],
                );
                $contactId = DB::table('crm_contacts')->where('external_id', $contact['id'])->value('id');
                DB::table('crm_deal_contacts')->updateOrInsert(
                    ['deal_id' => $this->dealId, 'contact_id' => $contactId, 'relationship' => $contact['_relationship']],
                    ['updated_at' => now(), 'created_at' => now()],
                );
            });
        }
    }

    private function saveLineItems(): void
    {
        foreach ($this->deal['line_items'] ?? [] as $item) {
            $key = ['deal_id' => $this->dealId, 'external_product_id' => $item['product_id'] ?? null];
            DB::table('crm_line_items')->updateOrInsert($key, [
                'sku' => $item['sku'] ?? null, 'name' => $item['name'],
                'quantity' => $item['quantity'], 'unit_price' => $item['unit_price'],
                'discount_percentage' => $item['discount_percentage'] ?? 0,
                'total_price' => $item['total_price'], 'updated_at' => now(), 'created_at' => now(),
            ]);
        }
    }

    private function saveActivities(): void
    {
        foreach ($this->deal['activities'] ?? [] as $activity) {
            DB::table('crm_activities')->updateOrInsert(
                ['deal_id' => $this->dealId, 'external_id' => $activity['id'] ?? null],
                [
                    'type' => $activity['type'], 'subject' => $activity['subject'],
                    'occurred_at' => isset($activity['timestamp'])
                        ? Carbon::parse($activity['timestamp'])->utc()->format('Y-m-d H:i:s')
                        : null,
                    'duration_minutes' => $activity['duration_minutes'] ?? null,
                    'participants' => json_encode($activity['participants'] ?? []),
                    'outcome' => $activity['outcome'] ?? null,
                    'content_snippet' => $activity['content_snippet'] ?? null,
                    'updated_at' => now(), 'created_at' => now(),
                ],
            );
        }
    }

    private function saveExtras(): void
    {
        foreach ([['crm_custom_fields', 'fields', $this->deal['custom_fields'] ?? []], ['crm_metadata', 'data', $this->deal['metadata'] ?? []]] as [$table, $column, $data]) {
            DB::table($table)->updateOrInsert(
                ['deal_id' => $this->dealId],
                [$column => json_encode($data), 'updated_at' => now(), 'created_at' => now()],
            );
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Falha definitiva ao persistir entidade CRM', [
            'request_id' => $this->requestId, 'deal_id' => $this->deal['id'] ?? null,
            'entity' => $this->type, 'exception' => $exception->getMessage(),
        ]);
    }
}
