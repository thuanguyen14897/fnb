<?php

namespace App\Jobs;

use App\Models\Clients;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $clients;
    public function __construct($clients)
    {
        $this->clients = $clients;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->clients as $client) {
            \Log::info("Đã gửi thông báo đến: " . $client->fullname);
        }
    }
}
