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
        Schema::table('users', function (Blueprint $table) {
            // Personal Information
            $table->string('title')->nullable()->after('profile_photo');
            $table->string('first_name')->nullable()->after('title');
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('last_name')->nullable()->after('middle_name');
            $table->string('name_extension')->nullable()->after('last_name');
            $table->string('gender')->nullable()->after('birthday');
            $table->string('civil_status')->nullable()->after('gender');
            $table->string('place_of_birth')->nullable()->after('civil_status');
            $table->string('blood_type')->nullable()->after('place_of_birth');
            $table->string('citizenship')->nullable()->after('blood_type');
            $table->string('religion')->nullable()->after('citizenship');

            // Employment Information
            $table->string('employment_type')->nullable()->after('position');
            $table->string('classification')->nullable()->after('employment_type');
            $table->string('tax_code')->nullable()->after('classification');
            $table->string('pay_type')->nullable()->after('tax_code');
            $table->foreignId('report_to')->nullable()->after('pay_type')->constrained('users')->nullOnDelete();

            // Account Information
            $table->string('bank')->nullable()->after('pagibig_number');
            $table->string('account_no')->nullable()->after('bank');
            $table->string('tin')->nullable()->after('account_no');

            // Contact Details
            $table->string('mobile_no_1')->nullable()->after('tin');
            $table->string('mobile_no_2')->nullable()->after('mobile_no_1');
            $table->string('tel_no_1')->nullable()->after('mobile_no_2');
            $table->string('tel_no_2')->nullable()->after('tel_no_1');
            $table->string('facebook')->nullable()->after('tel_no_2');
            $table->string('twitter')->nullable()->after('facebook');
            $table->string('instagram')->nullable()->after('twitter');

            // Address
            $table->text('permanent_address')->nullable()->after('instagram');
            $table->string('permanent_province')->nullable()->after('permanent_address');
            $table->text('present_address')->nullable()->after('permanent_province');
            $table->string('present_province')->nullable()->after('present_address');
            
            // Other Information
            $table->text('other_info')->nullable()->after('present_province');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['report_to']);
            $table->dropColumn([
                'title', 'first_name', 'middle_name', 'last_name', 'name_extension',
                'gender', 'civil_status', 'place_of_birth', 'blood_type', 'citizenship', 'religion',
                'employment_type', 'classification', 'tax_code', 'pay_type', 'report_to',
                'bank', 'account_no', 'tin',
                'mobile_no_1', 'mobile_no_2', 'tel_no_1', 'tel_no_2',
                'facebook', 'twitter', 'instagram',
                'permanent_address', 'permanent_province', 'present_address', 'present_province',
                'other_info'
            ]);
        });
    }
};
