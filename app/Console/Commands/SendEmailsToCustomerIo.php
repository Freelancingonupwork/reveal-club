<?php

namespace App\Console\Commands;

use App\Services\CustomerIoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Testing\CustomerIoController;

class SendEmailsToCustomerIo extends Command
{
    protected $signature = 'app:send-emails-to-customer-io';
    protected $description = 'Send emails to Customer.io.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
		$controller = new CustomerIoController();
		$controller->sendEmailsToCustomerIo();
    }
}
