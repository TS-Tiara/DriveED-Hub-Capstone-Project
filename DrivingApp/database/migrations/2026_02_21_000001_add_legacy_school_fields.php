<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('schools')) {
            Schema::table('schools', function (Blueprint $table) {
                if (! Schema::hasColumn('schools', 'subdomain')) {
                    $table->string('subdomain')->nullable();
                }
                if (! Schema::hasColumn('schools', 'email')) {
                    $table->string('email')->nullable();
                }
                if (! Schema::hasColumn('schools', 'contact_number')) {
                    $table->string('contact_number')->nullable();
                }
                if (! Schema::hasColumn('schools', 'address')) {
                    $table->text('address')->nullable();
                }
                if (! Schema::hasColumn('schools', 'status')) {
                    $table->string('status')->default('active');
                }
            });
        }

        if (Schema::hasTable('school_settings')) {
            Schema::table('school_settings', function (Blueprint $table) {
                if (! Schema::hasColumn('school_settings', 'logo_path')) {
                    $table->string('logo_path')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('schools')) {
            Schema::table('schools', function (Blueprint $table) {
                if (Schema::hasColumn('schools', 'subdomain')) {
                    $table->dropColumn('subdomain');
                }
                if (Schema::hasColumn('schools', 'email')) {
                    $table->dropColumn('email');
                }
                if (Schema::hasColumn('schools', 'contact_number')) {
                    $table->dropColumn('contact_number');
                }
                if (Schema::hasColumn('schools', 'address')) {
                    $table->dropColumn('address');
                }
                if (Schema::hasColumn('schools', 'status')) {
                    $table->dropColumn('status');
                }
            });
        }

        if (Schema::hasTable('school_settings')) {
            Schema::table('school_settings', function (Blueprint $table) {
                if (Schema::hasColumn('school_settings', 'logo_path')) {
                    $table->dropColumn('logo_path');
                }
            });
        }
    }
};
