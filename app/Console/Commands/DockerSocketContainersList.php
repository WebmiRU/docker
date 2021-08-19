<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Throwable;

class DockerSocketContainersList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'docker:list';

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
            fwrite($fp, "GET /v1.24/containers/json?all=1&size=1 HTTP/1.0\r\nHost: localhost\r\nAccept: */*\r\n\r\n");
            while (!feof($fp)) {
                try {
                    $dataJson = fgets($fp, pow(1000, 2) * 10);
                    $containers = json_decode($dataJson);

                    Redis::

                    foreach ($containers ?? [] as $container) {
                        dump($container->Names);
                    }

//                    if ($data && $data->status == 'attach') {
//                        $attr = 'com.docker.compose.service';
//                        dump($data->Actor->Attributes->{$attr});
//                        dump('----------');
//                    }


                } catch (Throwable $e) {
                    dump($e->getMessage());
                }
            }

            fclose($fp);
        }

        return 0;
    }
}
