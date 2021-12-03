<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Agent;
class AgentTimerScreen extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'agent:TimerScreenAcceptJob';

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
     * @return mixed
     */
    public function handle()
    {
        $agent = Agent::find(1);
        $agent->last_name = 'Ba';
        $agent->save();
    }
}
