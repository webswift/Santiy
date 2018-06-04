<?php

namespace App\Jobs;

use App\Http\Controllers\CommonController;
use App\Jobs\Job;
use App\Models\Setting;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use PayPal\Api\Agreement;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

class SendApprovedTransaction extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $transaction;

    /**
     * Create a new job instance.
     *
     * @param Transaction $transaction
     *
     * @return void
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        #1. get agreement status
        #2. if it is pending then push try again
        #3. else save transaction into database and send invoice to user
        $apiContext = new ApiContext(new OAuthTokenCredential(Setting::get('paypalID'), Setting::get('apiSignature')));

        $agreementID = $this->transaction->id;
        $agreement = Agreement::get($agreementID, $apiContext);
        $status = $agreement->getState();

        if(strtolower($status) == 'pending') {
            // set job to queue again
            $this->release(600);
            return;
        }

        $this->transaction->state = $status;
        $this->transaction->save();

        // search for transaction
        $params = array('start_date' => date('Y-m-d', strtotime('-2 days')), 'end_date' => date('Y-m-d', strtotime('+2 days')));
        $paypalTransactions = \PayPal\Api\Agreement::searchTransactions($agreementID, $params, $apiContext)->getAgreementTransactionList();

        if(count($paypalTransactions) > 0) {
            foreach($paypalTransactions as $paypalTransaction) {
                $transactionID = $paypalTransaction->getTransactionId();

                $isTransaction = Transaction::find($transactionID);

                // If transaction is there then continue, else save this transaction in database and then continue
                if($transactionID == $agreementID) {
                    continue;
                }
                elseif($isTransaction) {
                    continue;
                }
                else {
                    $nextBillingDate = $agreement->getAgreementDetails()->next_billing_date;

                    $amount = $paypalTransaction->getAmount();
                    $status = $paypalTransaction->getStatus();

                    if(strtolower($status) == 'unclaimed' || strtolower($status) == 'complete' || strtolower($status) == 'completed' || strtolower($status) == 'claimed') {
                        $trans = new Transaction();
                        $trans->id = $transactionID;
                        $trans->type = 'Renew';
                        $trans->time = Carbon::parse($paypalTransaction->getTimeStamp(), $paypalTransaction->getTimeZone());
                        $trans->purchaser = $this->transaction->purchaser;
                        $trans->amount = $amount->getValue();
                        $trans->licenseType = $this->transaction->licenseType;
                        $trans->plan_id = $this->transaction->plan_id;
                        $trans->payer_id = $this->transaction->payer_id;
                        $trans->state = $status;
                        $trans->currency = $amount->getCurrency();
                        $trans->nextBillingAmount = $amount->getValue();
                        $trans->nextBillingDate = Carbon::parse($nextBillingDate)->format('Y-m-d H:i:s');

                        $trans->save();

                        // send invoice to user
                        CommonController::sendInvoiceEmailToUser($trans);

                        //$this->delete();
                    }
                }
            }
        }

        $this->delete();
    }
}
