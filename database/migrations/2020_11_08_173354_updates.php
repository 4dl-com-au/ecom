<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Updates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){
        if (!Schema::hasTable('blog')) {
            Schema::create('blog', function (Blueprint $table) {
                $table->id();
                $table->integer('user');
                $table->string('name')->nullable();
                $table->longText('note')->nullable();
                $table->string('slug')->nullable();
                $table->string('image')->nullable();
                $table->dateTime('start_date')->nullable();
                $table->dateTime('end_date')->nullable();
                $table->longText('extra')->nullable();
                $table->integer('order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('user_pages')) {
            Schema::create('user_pages', function (Blueprint $table) {
                $table->id();
                $table->integer('user');
                $table->string('name')->nullable();
                $table->integer('show_menu')->default(0);
                $table->integer('is_home')->default(0);
                $table->integer('total_views')->default(0);
                $table->string('slug')->nullable();
                $table->string('image')->nullable();
                $table->string('theme')->nullable();
                $table->dateTime('start_date')->nullable();
                $table->dateTime('end_date')->nullable();
                $table->longText('extra')->nullable();
                $table->integer('order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('conversations')) {
            Schema::create('conversations', function (Blueprint $table) {
                $table->id();
                $table->integer('user');
                $table->integer('customer');
                $table->longText('extra')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('messages')) {
            Schema::create('messages', function (Blueprint $table) {
                $table->id();
                $table->integer('conversation_id');
                $table->string('from')->default('store');
                $table->integer('user_id');
                $table->string('type')->default('text');
                $table->longText('data')->nullable();
                $table->longText('extra')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pages_sections')) {
            Schema::create('pages_sections', function (Blueprint $table) {
                $table->id();
                $table->integer('user');
                $table->integer('page_id');
                $table->string('name')->nullable();
                $table->string('theme')->nullable();
                $table->string('block_slug')->nullable();
                $table->longText('data')->nullable();
                $table->longText('extra')->nullable();
                $table->integer('order')->default(0);
                $table->timestamps();
            });
        }
        
        if (!Schema::hasTable('option_values')) {
            Schema::create('option_values', function (Blueprint $table) {
                $table->id();
                $table->integer('user');
                $table->string('label')->nullable();
                $table->integer('option_id')->nullable();
                $table->float('price')->nullable();
                $table->string('price_type')->default('fixed');
                $table->integer('order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('options')) {
            Schema::create('options', function (Blueprint $table) {
                $table->id();
                $table->integer('user');
                $table->string('name')->nullable();
                $table->integer('product')->nullable();
                $table->string('type')->nullable();
                $table->integer('is_required')->default(0);
                $table->integer('is_global')->default(0);
                $table->integer('order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table) {
                $table->id();
                $table->integer('storeuser');
                $table->string('email');
                $table->string('name')->nullable();
                $table->integer('status')->default(1);
                $table->string('password');
                $table->longText('details')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('customers_logs')) {
            Schema::create('customers_logs', function (Blueprint $table) {
                $table->id();
                $table->integer('user');
                $table->string('what');
                $table->string('ip');
                $table->string('os');
                $table->string('browser');
                $table->dateTime('date');
            });
        }

        if (!Schema::hasTable('product_refunds')) {
            Schema::create('product_refunds', function (Blueprint $table) {
                $table->id();
                $table->integer('user');
                $table->integer('customer');
                $table->integer('order_id');
                $table->integer('status')->default(0);
                $table->timestamps();
            });
        }
        
        Schema::table('option_values', function (Blueprint $table) {
            if (!Schema::hasColumn('stock', 'option_values')) {
                $table->integer('stock')->after('price')->nullable();
            }
        });
        
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'avatar')) {
                $table->string('avatar')->after('email')->nullable();
            }
            if (!Schema::hasColumn('customers', 'resetPassword')) {
                $table->string('resetPassword')->after('avatar')->nullable();
            }
            if (!Schema::hasColumn('customers', 'activateCode')) {
                $table->string('activateCode')->after('resetPassword')->nullable();
            }
            if (!Schema::hasColumn('customers', 'active')) {
                $table->integer('active')->after('activateCode')->default(1);
            }
        });
        
        Schema::table('pages_sections', function (Blueprint $table) {
            if (!Schema::hasColumn('pages_sections', 'status')) {
                $table->integer('status')->after('theme')->default(1);
            }
        });
        
        Schema::table('user_pages', function (Blueprint $table) {
            if (!Schema::hasColumn('parent', 'user_pages')) {
                $table->integer('parent')->after('slug')->nullable();
            }
        });

        Schema::table('domains', function (Blueprint $table) {
            if (!Schema::hasColumn('domains', 'user')) {
                $table->integer('user')->after('id')->nullable();
            }
            if (!Schema::hasColumn('domains', 'wildcard')) {
                $table->integer('wildcard')->after('user')->default(0);
            }
        });
        
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'otheruser')) {
                $table->integer('otheruser')->after('user')->nullable();
            }
        });
        
        Schema::table('product_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('product_orders', 'gateway')) {
                $table->string('gateway')->after('currency')->nullable();
            }
            if (!Schema::hasColumn('product_orders', 'customer')) {
                $table->integer('customer')->after('storeuser')->nullable();
            }
            if (!Schema::hasColumn('product_orders', 'send_email')) {
                $table->integer('send_email')->after('customer')->default(0);
            }
            if (!Schema::hasColumn('product_orders', 'order_status')) {
                $table->integer('order_status')->after('send_email')->default(0);
            }
        });
        
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'shipping')) {
                $table->longText('shipping')->after('media')->nullable();
            }
            if (!Schema::hasColumn('users', 'first_welcome_screen')) {
                $table->integer('first_welcome_screen')->after('shipping')->default(0);
            }
            if (!Schema::hasColumn('users', 'enable_welcome_screen')) {
                $table->integer('enable_welcome_screen')->after('first_welcome_screen')->default(0);
            }
        });
        
        Schema::table('packages', function (Blueprint $table) {
            if (!Schema::hasColumn('packages', 'gateways')) {
                $table->longText('gateways')->after('domains')->nullable();
            }
        });
        
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'external_url')) {
                $table->longText('external_url_name')->after('media')->nullable();
                $table->longText('external_url')->after('external_url_name')->nullable();
            }
            if (!Schema::hasColumn('products', 'stock_management')) {
                $table->integer('stock_management')->after('external_url_name')->nullable();
            }

            if (!Schema::hasColumn('products', 'stock_status')) {
                $table->integer('stock_status')->after('stock_management')->nullable();
            }

            if (!Schema::hasColumn('products', 'sku')) {
                $table->string('sku')->after('stock_status')->nullable();
            }

            if (!Schema::hasColumn('products', 'files')) {
                $table->longText('files')->after('sku')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
