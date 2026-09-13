<?php

namespace App\Console\Commands;

use App\Jobs\ExpireUnpaidBookings;
use Illuminate\Console\Command;

class ExpireUnpaidBookingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sportbook:expire-bookings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire unpaid bookings that exceeded payment window deadline';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking for expired unpaid bookings...');

        $job = new ExpireUnpaidBookings;
        $expiredCount = $job->handle();

        $this->info("Completed. {$expiredCount} bookings expired.");

        return Command::SUCCESS;
    }
}
