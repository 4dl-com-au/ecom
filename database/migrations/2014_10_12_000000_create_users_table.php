<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('username')->unique();
            $table->integer('role')->default(0);
            $table->string('email_token')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->longText('name')->nullable();
            $table->string('verified')->default(0);
            $table->longText('media')->nullable();
            $table->string('package')->default('free');
            $table->dateTime('package_due')->nullable();
            $table->integer('package_trial_done')->default(0);
            $table->string('facebook_id')->nullable();
            $table->string('google_id')->nullable();
            $table->string('domain')->default('main');
            $table->string('address')->nullable();
            $table->longText('gateway')->nullable();
            $table->longText('socials')->nullable();
            $table->longText('extra')->nullable();
            $table->integer('active')->default(1);
            $table->string('last_activity')->nullable();
            $table->string('last_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
