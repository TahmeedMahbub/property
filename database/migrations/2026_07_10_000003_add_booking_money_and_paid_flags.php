<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Mandatory down-payment given to reserve the booking. Counts as a
        // payment toward the total (not an extra charge). Allowed to be 0.
        Schema::table('p_plot_bookings', function (Blueprint $table) {
            $table->decimal('booking_money', 20, 2)->default(0)->after('share_price');
        });

        // Flags payments auto-generated from the booking form's "Paid" checkboxes
        // so they can be re-synced without disturbing manually recorded payments.
        Schema::table('p_plot_booking_payments', function (Blueprint $table) {
            $table->boolean('auto_generated')->default(false)->after('payment_type');
        });
    }

    public function down(): void
    {
        Schema::table('p_plot_bookings', function (Blueprint $table) {
            $table->dropColumn('booking_money');
        });

        Schema::table('p_plot_booking_payments', function (Blueprint $table) {
            $table->dropColumn('auto_generated');
        });
    }
};
