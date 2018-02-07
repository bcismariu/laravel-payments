<?php

namespace Bcismariu\Laravel\Payments\Commands;

use Illuminate\Console\Command;
use Bcismariu\Laravel\Payments\Processors\Processor;
use Bcismariu\Laravel\Payments\OrderItem;
use Bcismariu\Laravel\Payments\Subscription;
use Bcismariu\Commons\Descendable\Descendable;
use Carbon\Carbon;

class SyncCommand extends Command
{

    /**
     * how many results per page should be returned
     * @var integer
     */
    protected $results_per_page = 50;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:sync {days=3}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'It syncs the subscriptions';


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
        $processor = $this->getProcessor();

        $processor->setOptions([
            'startDate' => Carbon::now()->subDays($this->argument('days'))->format('m/d/Y'),
            'endDate' => Carbon::now()->format('m/d/Y'),
            'dateRangeType' => 'dateUpdated',
            'resultsPerPage' => $this->results_per_page,
        ]);

        $current_page = 0;

        do {
            $current_page ++;

            $processor->setOptions(['page' => $current_page]);
            $response = $processor->sync();
            $this->response = $this->parseResponse($response);

            $this->processResponse();

        } while (! $this->isLastPage());
    }

    protected function processResponse()
    {
        foreach ($this->response->get('message.data', []) as $order) {
            $this->processOrder(new Descendable($order));
        }
    }

    protected function processOrder(Descendable $order)
    {
        foreach ($order->get('items', []) as $raw) {
            $item = $this->buildItem($order, new Descendable($raw));
            $this->handleItem($item);
        }
    }

    protected function handleItem(OrderItem $item)
    {
        if (! $item->isValid()) {
            return false;
        }

        $subscription = Subscription::where([
            'customer_id'   => $item->customer_id,
            'product_id'    => $item->product_id,
        ])->first();

        if (! $subscription) {
            return false;
        }

        // adding 3 before expiring to have a buffer for unexpected scenarios
        $subscription->ends_at = Carbon::createFromFormat('Y-m-d', $item->next_bill_date)->addDays(3);
        $subscription->status = 'active';

        $subscription->save();

        $this->info("Subscription $subscription->id was updated");
    }

    protected function buildItem(Descendable $order, Descendable $raw)
    {
        $item = new OrderItem();
        $item->order_id = $order->get('orderId');
        $item->customer_id = $order->get('customerId');
        $item->date_created = $order->get('dateCreated');
        $item->date_updated = $order->get('dateUpdated');
        $item->order_type = $order->get('orderType');
        $item->order_status = $order->get('orderStatus');

        $item->product_id = $raw->get('productId');
        $item->purchase_status = $raw->get('purchaseStatus');
        $item->next_bill_date = $raw->get('nextBillDate');

        return $item;
    }

    protected function isLastPage()
    {
        $message = new Descendable($this->response->get('message', []));
        return $message->get('totalResults', 0) <= 
            $message->get('page', 0) * $message->get('resultsPerPage', 0);
    }

    protected function parseResponse($response) {
        return new Descendable(json_decode($response));
    }

    /**
     * Returns the configured Payment Processor
     * @return Processor
     */
    protected function getProcessor()
    {
        $processor = config('payments.processor');
        $settings = config("payments.integrations.$processor");

        if (!$settings) {
            throw new \Exception("Processor $processor not found!");
        }

        $settings = array_merge($settings);

        return Processor::make($settings);
    }
}