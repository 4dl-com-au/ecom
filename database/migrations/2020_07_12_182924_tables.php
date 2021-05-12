<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Tables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->longText('value');
        });

        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('status')->default(1);
            $table->string('price')->nullable();
            $table->longText('settings')->nullable();
            $table->longText('domains')->nullable();
            $table->timestamps();
        });

        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->integer('status')->default(0);
            $table->string('scheme')->nullable();
            $table->string('host')->nullable();
            $table->string('index_url')->nullable();
            $table->longText('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('category')->nullable();
            $table->string('url');
            $table->string('title')->nullable();
            $table->string('status')->default(0);
            $table->string('type')->default('internal');
            $table->string('image')->nullable();
            $table->longText('settings')->nullable();
            $table->integer('order')->default(0);
            $table->integer('total_views')->default(0);
            $table->timestamps();
        });

        Schema::create('pages_categories', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->integer('status')->default(1);
            $table->string('title')->nullable();
            $table->longText('description')->nullable();
            $table->string('icon')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('track', function (Blueprint $table) {
            $table->id();
            $table->integer('user');
            $table->integer('dyid')->nullable();
            $table->string('visitor_id')->nullable();
            $table->string('type');
            $table->string('country')->nullable();
            $table->string('ip');
            $table->string('os');
            $table->string('browser');
            $table->string('referer');
            $table->string('count');
            $table->dateTime('date');
        });

        Schema::create('users_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('user');
            $table->string('what');
            $table->string('ip');
            $table->string('os');
            $table->string('browser');
            $table->dateTime('date');
        });

        Schema::create('pending_payments', function (Blueprint $table) {
            $table->id();
            $table->integer('user');
            $table->integer('status')->default(0);
            $table->string('email')->nullable();
            $table->string('name')->nullable();
            $table->string('bankName')->nullable();
            $table->string('proof')->nullable();
            $table->string('ref')->nullable();
            $table->integer('package')->nullable();
            $table->string('duration')->nullable();
            $table->string('type')->default('bank');
            $table->timestamps();
        });

        Schema::create('track_links', function (Blueprint $table) {
            $table->id();
            $table->integer('user')->nullable();
            $table->string('type')->default('social');
            $table->string('slug')->nullable();
            $table->string('visitor_id')->nullable();
            $table->string('country')->nullable();
            $table->string('ip');
            $table->string('os');
            $table->string('browser');
            $table->string('views');
            $table->timestamps();
        });

        Schema::create('linker', function (Blueprint $table) {
            $table->id();
            $table->string('url')->nullable();
            $table->string('slug')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->integer('user');
            $table->string('name')->nullable();
            $table->string('package_name')->nullable();
            $table->string('email')->nullable();
            $table->string('duration')->nullable();
            $table->string('ref')->nullable();
            $table->string('currency')->nullable();
            $table->float('price')->default(0);
            $table->string('package')->nullable();
            $table->string('gateway')->default(0);
            $table->dateTime('date')->nullable();
        });

        Schema::create('product_orders', function (Blueprint $table) {
            $table->id();
            $table->integer('storeuser');
            $table->longText('products')->nullable();
            $table->longText('details')->nullable();
            $table->string('currency')->nullable();
            $table->string('ref')->nullable();
            $table->float('price')->default(0);
            $table->longText('extra')->nullable();
            $table->integer('delivered')->default(0);
            $table->enum('status', [0, 1, 2])->default(0);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->integer('user');
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->string('price')->nullable();
            $table->integer('status')->default(1);
            $table->string('salePrice')->nullable();
            $table->integer('stock')->nullable();
            $table->integer('product_type')->default(0);
            $table->string('tags')->nullable();
            $table->boolean('featured')->default(false);
            $table->string('product_condition')->default('new');
            $table->longText('media')->nullable();
            $table->longText('categories')->nullable();
            $table->longText('description')->nullable();
            $table->longText('variation')->nullable();
            $table->longText('extra')->nullable();
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->integer('user');
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->integer('status')->default(1);
            $table->longText('description')->nullable();
            $table->longText('media')->nullable();
            $table->longText('extra')->nullable();
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->integer('storeuser');
            $table->integer('product_id')->nullable();
            $table->string('rating')->default(0);
            $table->longText('review')->nullable();
            $table->longText('extra')->nullable();
            $table->timestamps();
        });

        Schema::create('product_wishlists', function (Blueprint $table) {
            $table->id();
            $table->integer('storeuser');
            $table->integer('product');
            $table->integer('user');
            $table->timestamps();
        });

        Schema::create('faq', function (Blueprint $table) {
            $table->id();
            $table->integer('status')->default(1);
            $table->longText('name')->nullable();
            $table->longText('note')->nullable();
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
        Schema::dropIfExists('settings');
        Schema::dropIfExists('packages');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('pages_categories');
        Schema::dropIfExists('track');
        Schema::dropIfExists('users_logs');
    }
}
