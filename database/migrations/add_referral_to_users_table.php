<?php

/*
 * This file is part of questocat/laravel-referral package.
 *
 * (c) questocat <zhengchaopu@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('referred_by')->nullable()->index();
            $table->string('affiliate_id')->nullable()->unique();
        });

        // User Models
        config('referral.user_model')::chunkById(200, function ($users) {
            try {
                DB::beginTransaction();

                foreach ($users as $user) {
                    $user->affiliate_id = $user::generateReferral();
                    $user->save();
                }

                DB::commit();
            } catch (\Exception $e) {
                //handle your error (log ...)
                DB::rollBack();
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('affiliate_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('referred_by');
            $table->dropColumn('affiliate_id');
        });
    }
};
