<?php

use App\Models\Company;
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
        Schema::create('incident', function (Blueprint $table) {
            $table->id();
            $table->date('start_date')->nullable();
            $table->time('start_time')->nullable();
            $table->date('close_date')->nullable();
            $table->time('close_time')->nullable();
            $table->enum('type', ['Onsite', 'Remote'])->nullable();
            $table->text('description');
            $table->enum('topic', ['PC', 'Server', 'Network', 'Other'])->default('PC');
            $table->string('assigned_id')->nullable();
            $table->string('pic_name')->nullable();
            $table->foreignId('created_by')->foreignIdFor(User::class);
            $table->foreignId('company_id')->foreignIdFor(Company::class)->nullable();
            $table->enum('status', ['Open', 'Escalated', 'WIP', 'Close'])->default('Open');
            $table->string('service_report')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident');
    }
};
