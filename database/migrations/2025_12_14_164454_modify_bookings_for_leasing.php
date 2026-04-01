<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check apakah tabel bookings sudah ada
        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                // Check dulu sebelum add untuk safety
                if (!Schema::hasColumn('bookings', 'application_type')) {
                    $table->enum('application_type', ['rental', 'leasing'])->default('rental')->after('status');
                }
                
                if (!Schema::hasColumn('bookings', 'down_payment')) {
                    $table->decimal('down_payment', 10, 2)->nullable()->after('application_type');
                }
                
                if (!Schema::hasColumn('bookings', 'installment_amount')) {
                    $table->decimal('installment_amount', 10, 2)->nullable()->after('down_payment');
                }
                
                if (!Schema::hasColumn('bookings', 'mediator_id')) {
                    $table->integer('mediator_id')->nullable()->after('supplier_id');
                }
                
                if (!Schema::hasColumn('bookings', 'marketing_id')) {
                    $table->integer('marketing_id')->nullable()->after('mediator_id');
                }
                
                if (!Schema::hasColumn('bookings', 'showroom_id')) {
                    $table->integer('showroom_id')->nullable()->after('marketing_id');
                }
                
                if (!Schema::hasColumn('bookings', 'leasing_status')) {
                    $table->enum('leasing_status', ['pending', 'review', 'approved', 'rejected', 'appealed'])->nullable()->after('showroom_id');
                }
                
                if (!Schema::hasColumn('bookings', 'leasing_notes')) {
                    $table->text('leasing_notes')->nullable()->after('leasing_status');
                }
                
                if (!Schema::hasColumn('bookings', 'application_documents')) {
                    $table->json('application_documents')->nullable()->after('leasing_notes');
                }
                
                if (!Schema::hasColumn('bookings', 'pooled_at')) {
                    $table->timestamp('pooled_at')->nullable()->after('application_documents');
                }
                
                if (!Schema::hasColumn('bookings', 'appealed_at')) {
                    $table->timestamp('appealed_at')->nullable()->after('pooled_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                if (Schema::hasColumn('bookings', 'appealed_at')) {
                    $table->dropColumn('appealed_at');
                }
                
                if (Schema::hasColumn('bookings', 'pooled_at')) {
                    $table->dropColumn('pooled_at');
                }
                
                if (Schema::hasColumn('bookings', 'application_documents')) {
                    $table->dropColumn('application_documents');
                }
                
                if (Schema::hasColumn('bookings', 'leasing_notes')) {
                    $table->dropColumn('leasing_notes');
                }
                
                if (Schema::hasColumn('bookings', 'leasing_status')) {
                    $table->dropColumn('leasing_status');
                }
                
                if (Schema::hasColumn('bookings', 'showroom_id')) {
                    $table->dropColumn('showroom_id');
                }
                
                if (Schema::hasColumn('bookings', 'marketing_id')) {
                    $table->dropColumn('marketing_id');
                }
                
                if (Schema::hasColumn('bookings', 'mediator_id')) {
                    $table->dropColumn('mediator_id');
                }
                
                if (Schema::hasColumn('bookings', 'installment_amount')) {
                    $table->dropColumn('installment_amount');
                }
                
                if (Schema::hasColumn('bookings', 'down_payment')) {
                    $table->dropColumn('down_payment');
                }
                
                if (Schema::hasColumn('bookings', 'application_type')) {
                    $table->dropColumn('application_type');
                }
            });
        }
    }
};
