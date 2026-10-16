<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('car_rate_id')->nullable()->after('vehicle_id')->constrained('car_rates')->nullOnDelete();

            // Workflow stage (see App\Support\BookingSop::STAGES)
            $table->string('stage', 30)->default('permohonan')->after('status');
            $table->string('access_token', 64)->nullable()->unique();

            // Quotation (RM, whole ringgit)
            $table->unsignedSmallInteger('rental_days')->nullable();
            $table->unsignedInteger('quoted_list_daily')->nullable();
            $table->unsignedInteger('quoted_daily')->nullable();
            $table->unsignedInteger('quoted_rental_total')->nullable();
            $table->unsignedInteger('deposit_amount')->nullable();
            $table->unsignedInteger('delivery_fee')->nullable();
            $table->string('quote_package', 30)->nullable();
            $table->timestamp('quote_sent_at')->nullable();
            $table->timestamp('customer_agreed_at')->nullable();
            $table->timestamp('verification_link_sent_at')->nullable();

            // Eligibility check (filled by the customer)
            $table->string('ic_number', 30)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->text('home_address')->nullable();
            $table->string('occupation', 120)->nullable();
            $table->string('employer', 150)->nullable();
            $table->string('license_number', 40)->nullable();
            $table->date('license_expiry')->nullable();
            $table->string('emergency_name', 120)->nullable();
            $table->string('emergency_relation', 60)->nullable();
            $table->string('emergency_phone', 30)->nullable();
            $table->string('social_media', 200)->nullable();
            $table->timestamp('verification_submitted_at')->nullable();
            $table->timestamp('terms_accepted_at')->nullable();
            $table->string('terms_signature', 150)->nullable();
            $table->string('terms_ip', 45)->nullable();
            $table->timestamp('verification_approved_at')->nullable();
            $table->text('verification_note')->nullable();

            // Payment
            $table->unsignedInteger('payment_amount')->nullable();
            $table->string('payment_reference', 120)->nullable();
            $table->timestamp('payment_submitted_at')->nullable();
            $table->timestamp('payment_verified_at')->nullable();

            // Handover & return
            $table->timestamp('handover_at')->nullable();
            $table->unsignedInteger('mileage_out')->nullable();
            $table->string('fuel_out', 20)->nullable();
            $table->string('usage_area', 150)->nullable();
            $table->unsignedInteger('mileage_limit')->nullable();
            $table->unsignedInteger('excess_km_rate')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->unsignedInteger('mileage_in')->nullable();
            $table->string('fuel_in', 20)->nullable();
            $table->unsignedInteger('deposit_deduction')->nullable();
            $table->string('deposit_deduction_reason')->nullable();
            $table->timestamp('deposit_refunded_at')->nullable();

            $table->json('sop_checks')->nullable();

            $table->index(['stage']);
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('car_rate_id');
            $table->dropIndex(['stage']);
            $table->dropUnique(['access_token']);
            $table->dropColumn([
                'stage', 'access_token', 'rental_days', 'quoted_list_daily', 'quoted_daily', 'quoted_rental_total',
                'deposit_amount', 'delivery_fee', 'quote_package', 'quote_sent_at', 'customer_agreed_at',
                'verification_link_sent_at', 'ic_number', 'date_of_birth', 'home_address', 'occupation', 'employer',
                'license_number', 'license_expiry', 'emergency_name', 'emergency_relation', 'emergency_phone',
                'social_media', 'verification_submitted_at', 'terms_accepted_at', 'terms_signature', 'terms_ip',
                'verification_approved_at', 'verification_note', 'payment_amount', 'payment_reference',
                'payment_submitted_at', 'payment_verified_at', 'handover_at', 'mileage_out', 'fuel_out',
                'usage_area', 'mileage_limit', 'excess_km_rate', 'returned_at', 'mileage_in', 'fuel_in',
                'deposit_deduction', 'deposit_deduction_reason', 'deposit_refunded_at', 'sop_checks',
            ]);
        });
    }
};
