<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_organizations', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->unique();
            $table->string('name');
            $table->string('cnpj')->nullable();
            $table->string('industry')->nullable();
            $table->unsignedInteger('employee_count')->nullable();
            $table->string('tier')->nullable();
            $table->json('address')->nullable();
            $table->timestamps();
        });

        Schema::create('crm_deals', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->unique();
            $table->string('title');
            $table->string('pipeline');
            $table->string('stage');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3);
            $table->decimal('probability', 5, 4);
            $table->timestamp('expected_close_date')->nullable();
            $table->json('owner');
            $table->foreignId('organization_id')->constrained('crm_organizations')->cascadeOnDelete();
            $table->string('request_id', 36)->index();
            $table->timestamps();
        });

        Schema::create('crm_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->unique();
            $table->foreignId('organization_id')->nullable()->constrained('crm_organizations')->nullOnDelete();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('name')->nullable();
            $table->string('title')->nullable();
            $table->string('role')->nullable();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('decision_role')->nullable();
            $table->string('preferred_channel')->nullable();
            $table->string('influence_level')->nullable();
            $table->string('sentiment')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('crm_deal_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_id')->constrained('crm_deals')->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained('crm_contacts')->cascadeOnDelete();
            $table->string('relationship');
            $table->timestamps();
            $table->unique(['deal_id', 'contact_id', 'relationship']);
        });

        Schema::create('crm_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_id')->constrained('crm_deals')->cascadeOnDelete();
            $table->string('external_product_id')->nullable();
            $table->string('sku')->nullable();
            $table->string('name');
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('total_price', 15, 2);
            $table->timestamps();
        });

        Schema::create('crm_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_id')->constrained('crm_deals')->cascadeOnDelete();
            $table->string('external_id')->nullable();
            $table->string('type');
            $table->string('subject');
            $table->timestamp('occurred_at')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->json('participants')->nullable();
            $table->text('outcome')->nullable();
            $table->text('content_snippet')->nullable();
            $table->timestamps();
            $table->unique(['deal_id', 'external_id']);
        });

        Schema::create('crm_custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_id')->unique()->constrained('crm_deals')->cascadeOnDelete();
            $table->json('fields');
            $table->timestamps();
        });

        Schema::create('crm_metadata', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_id')->unique()->constrained('crm_deals')->cascadeOnDelete();
            $table->json('data');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_metadata');
        Schema::dropIfExists('crm_custom_fields');
        Schema::dropIfExists('crm_activities');
        Schema::dropIfExists('crm_line_items');
        Schema::dropIfExists('crm_deal_contacts');
        Schema::dropIfExists('crm_contacts');
        Schema::dropIfExists('crm_deals');
        Schema::dropIfExists('crm_organizations');
    }
};
