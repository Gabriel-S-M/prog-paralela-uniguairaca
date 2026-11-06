<?php

namespace App\Http\Controllers;

use App\Http\Services\WebhookService;
use App\Http\Requests\CrmWebhookRequest;
use App\Jobs\ProcessLeadData;
use App\Jobs\ProcessCrmWebhookJob;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(private WebhookService $service)
    {}

    public function handle(Request $request)
    {
        Log::info('Informações do webhook recebidas no controller' . json_encode($request->all()));

        ProcessLeadData::dispatch($request->all());

        return response()->json(['message' => 'Tudo certo por aqui!'], 202);
    }

    public function crm(CrmWebhookRequest $request)
    {
        $requestId = (string) Str::uuid();
        $data = $request->validated();

        Log::withContext(['request_id' => $requestId]);
        Log::info('CRM webhook recebido', ['payload' => $data]);

        ProcessCrmWebhookJob::dispatch($data['deal'], $requestId)->onQueue('high');

        Log::info('Processamento do CRM enfileirado', ['deal_id' => $data['deal']['id'] ?? null]);

        return response()->json([
            'message' => 'Webhook recebido e enfileirado para processamento.',
            'request_id' => $requestId,
            'deal_id' => $data['deal']['id'] ?? null,
        ], 202);
    }
}
