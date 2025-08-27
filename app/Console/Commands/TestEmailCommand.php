<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Mail\OrderApproved;
use App\Mail\OrderRejected;
use App\Mail\OrderDelivering;
use Illuminate\Support\Facades\Mail;

class TestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {type} {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email functionality (types: approved, rejected, delivering)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->argument('type');
        $email = $this->argument('email');
        
        // Get first order for testing
        $order = Order::with('user')->first();
        
        if (!$order) {
            $this->error('No orders found in database. Please create some test orders first.');
            return;
        }
        
        $this->info("Testing {$type} email to {$email}...");
        $this->info("Using Order ID: #{$order->order_id}");
        
        try {
            switch ($type) {
                case 'approved':
                    Mail::to($email)->send(new OrderApproved($order));
                    $this->info('✅ OrderApproved email sent successfully!');
                    break;
                    
                case 'rejected':
                    Mail::to($email)->send(new OrderRejected($order));
                    $this->info('✅ OrderRejected email sent successfully!');
                    break;
                    
                case 'delivering':
                    Mail::to($email)->send(new OrderDelivering($order));
                    $this->info('✅ OrderDelivering email sent successfully!');
                    break;
                    
                default:
                    $this->error('Invalid type. Use: approved, rejected, or delivering');
                    return;
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to send email: ' . $e->getMessage());
            return;
        }
    }
}
