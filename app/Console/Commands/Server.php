<?php

namespace App\Console\Commands;

use App\Services\HttpService;
use Illuminate\Console\Command;

class Server extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'server:http 
                            {--host=0.0.0.0 : Http host} 
                            {--port=80 : Http port}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'http server start';

    private $httpService;

    /**
     * Create a new command instance.
     *
     * @param HttpService $httpService
     */
    public function __construct(HttpService $httpService)
    {
        parent::__construct();
        $this->httpService = $httpService;

    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->option('host') && $this->httpService->setHost($this->option('host'));
        $this->option('port') && $this->httpService->setPort($this->option('port'));
        $this->httpService->init()->start();
        $this->info('Http running!!!');
    }
}
