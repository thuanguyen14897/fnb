<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => url('/').'/cron/getCancelSystemTransactionDriver',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_HTTPHEADER => array(
                'Cookie: XSRF-TOKEN=eyJpdiI6IkIvTmNDMXVISmtHR0hKbjlMYjlPdUE9PSIsInZhbHVlIjoiaGE3NDgzWVVES3BoTzQ4RStKdnZVWlJ4VlBLa0dxSCszcnh6dUoxb0xHbUNqUFFNU0FLQ3psVG9QaG5uNCt4QlFkaTR2VHNSdnU1b1U4S2pHRnBRRHZnb0hTaDFhL2F6Q3JZS2lKdUVwTkU2V2FHUlg5YTR4L2FuNkl1aFpEVDAiLCJtYWMiOiI1MWY2ODMzNjhlYWMxNDNkMTk1OTYzNjY2MmJhYzY3MDk3ZDJlOTc0NjVlM2E2ZjZkMTJiNDFhZDRmYzNiZGUzIiwidGFnIjoiIn0%3D; laravel_session=eyJpdiI6IjFYT0JUakFNRVVjTjdyNGlROXFMRlE9PSIsInZhbHVlIjoiTER6L2puZmFidDEzUnUwdmlXME1tTDNzbEFMMW1IWjhGZE1JOXp2aFNPcW83VTBKWUJNQUQva1hpMTI1RVlHNFJQZE1xdTYxK1djbTA3dldlcDc1YXJ6VUNwb2lBNTcydnFlS1FVRzZaOE9sdC9kZTBDSU9xbzdMT1BQRDB3NGIiLCJtYWMiOiJhZTU1MDg4NDA4NTE2ZDkwYjAzMDY1ZmJkM2QxMmNlOTAwZGEzY2Y3N2Q5ZjdjMjg5MWZmODc1OGIwYWNhODRjIiwidGFnIjoiIn0%3D'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $response = json_encode($response);
        echo $response;
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
