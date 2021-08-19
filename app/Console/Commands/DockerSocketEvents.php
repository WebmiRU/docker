<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Throwable;

class DockerSocketEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'docker:events';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $fp = stream_socket_client("unix:///var/run/docker.sock", $errno, $errstr, 30);

        if (!$fp) {
            echo "$errstr ($errno)<br />\n";
        } else {
            fwrite($fp, "GET /events HTTP/1.0\r\nHost: localhost\r\nAccept: */*\r\n\r\n");
            while (!feof($fp)) {
                try {
                    $dataJson = fgets($fp, 10000);
                    $data = json_decode($dataJson);

                    if ($data && $data->status == 'attach') {
                        $attr = 'com.docker.compose.service';
                        dump($data->Actor->Attributes->{$attr});
                        dump('----------');
                    }


                } catch (Throwable $e) {

                }
            }

            fclose($fp);
        }

        return 0;
    }
}
