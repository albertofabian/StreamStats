<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Helpers\TopStreamsHelper;

class SeedTopStreamsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:seedtopstreams';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed Top Streams';

    
    
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
        $kill = false;
        pcntl_async_signals(true);
        pcntl_signal(SIGTERM, function ($signo) {
            $kill = true;
        });

        /*
         * restart every 24 hours aprox: this call be time based for
         * better accuracy
         */
        
        TopStreamsHelper::seedTopStreams();
        //echo "SeedTopStreams...\n";
        return;
        for ($i = 0; $i < 288; $i++) {
            if ($kill) {
                $this->info('gracefully shutdown: signal');
                break;
            }
            /*
             * try catch here?
             * todo: transactions
             */
            
            $logs  = []; //SocialNetworksHelper::setCampaignRecordsToDone();

            if (is_array($logs) && !empty($logs)) {

                foreach ($logs as $log) {
                    $logJson = json_encode($log);
                    if ($log['status'] === 0) {
                        $this->error($logJson);
                    } else {
                        $this->info($logJson);
                    }
                }

            } else {
                /*
                 * removed for now
                 */
//                $this->info('-');
            }

            sleep(300);
        }
    }
}
