<?php

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('phone_requests')
            ->orderBy('id')
            ->each(function (object $phoneRequest): void {
                try {
                    $phone = Crypt::decryptString($phoneRequest->phone);
                } catch (DecryptException) {
                    return;
                }

                DB::table('phone_requests')
                    ->where('id', $phoneRequest->id)
                    ->update(['phone' => $phone]);
            });
    }

    public function down(): void
    {
        // Plain text cannot be safely distinguished from previously converted values.
    }
};
