<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->decimal('discount_percent', 5, 2)->default(0)->after('uniform_status');
            $table->string('scholarship_type', 32)->nullable()->after('discount_percent');
            $table->text('scholarship_notes')->nullable()->after('scholarship_type');
        });

        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('name');
            $table->string('category', 32);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->json('size_options')->nullable();
            $table->unsignedInteger('low_stock_threshold')->default(5);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('branch_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(0);
            $table->timestamps();
            $table->unique(['branch_id', 'inventory_item_id']);
        });

        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('to_branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('student_id')->constrained()->nullOnDelete();
            $table->decimal('subtotal', 12, 2)->default(0)->after('amount');
            $table->decimal('discount_amount', 12, 2)->default(0)->after('subtotal');
            $table->decimal('late_fee_amount', 12, 2)->default(0)->after('discount_amount');
            $table->decimal('amount_paid', 12, 2)->default(0)->after('late_fee_amount');
            $table->boolean('is_scholarship_waiver')->default(false)->after('amount_paid');
        });

        Schema::create('invoice_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('fee_type', 64);
            $table->string('description');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('line_total', 12, 2);
            $table->foreignId('inventory_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('size', 32)->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('receipt_number')->unique();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 32)->default('cash');
            $table->timestamp('paid_at');
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        foreach (DB::table('invoices')->orderBy('id')->get() as $inv) {
            DB::table('invoices')->where('id', $inv->id)->update([
                'subtotal' => $inv->amount,
                'amount_paid' => $inv->status === 'paid' ? $inv->amount : 0,
            ]);
            DB::table('invoice_line_items')->insert([
                'invoice_id' => $inv->id,
                'fee_type' => 'legacy',
                'description' => 'Academy fee',
                'quantity' => 1,
                'unit_price' => $inv->amount,
                'line_total' => $inv->amount,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            if ($inv->status === 'paid' && $inv->paid_at) {
                DB::table('payments')->insert([
                    'invoice_id' => $inv->id,
                    'receipt_number' => 'RCP-'.$inv->id.'-LEG',
                    'amount' => $inv->amount,
                    'payment_method' => 'cash',
                    'paid_at' => $inv->paid_at,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        DB::statement("UPDATE invoices i JOIN students s ON s.id = i.student_id SET i.branch_id = s.branch_id WHERE i.branch_id IS NULL");
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_line_items');
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
            $table->dropColumn(['subtotal', 'discount_amount', 'late_fee_amount', 'amount_paid', 'is_scholarship_waiver']);
        });
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('branch_inventories');
        Schema::dropIfExists('inventory_items');
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['discount_percent', 'scholarship_type', 'scholarship_notes']);
        });
    }
};
