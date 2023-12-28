<?php

namespace Src\Controller;

use Src\Gateway\OrchardPaymentGateway;
use Src\Controller\VoucherPurchase;

class PaymentController
{
    private $voucher;

    public function __construct()
    {
        $this->voucher = new VoucherPurchase();
    }

    public function vendorPaymentProcess($data)
    {
        $trans_id = time();
        if (!$trans_id) return array("success" => false, "message" => "Transaction ID generation failed!");
        return $this->voucher->SaveFormPurchaseData($data, $trans_id);
    }

    public function verifyTransactionStatusFromOrchard(int $transaction_id)
    {
        $response = json_decode($this->getTransactionStatusFromOrchard($transaction_id));
        if (empty($response)) return array("success" => false, "message" => "Invalid transaction ID! Code: -2");

        if (isset($response->trans_status)) {
            $status_code = substr($response->trans_status, 0, 3);
            if ($status_code == '000') return array("success" => true, "message" => "COMPLETED");
            if ($status_code == '001') return array("success" => true, "message" => "FAILED");
        } elseif (isset($response->resp_code)) {
            if ($response->resp_code == '084') return array("success" => true, "message" => "PENDING");
            if ($response->resp_code == '067') return array("success" => false, "message" => "NO RECORD");
        }
        return array("success" => false, "message" => "Bad request: this transaction process failed!");
    }

    /**
     * @param int $transaction_id
     * @return mixed
     */
    public function getTransactionStatusFromOrchard(int $transaction_id)
    {
        $payload = json_encode(array(
            "exttrid" => $transaction_id,
            "trans_type" => "TSC",
            "service_id" => getenv('ORCHARD_SERVID')
        ));
        $endpointUrl = "https://orchard-api.anmgw.com/checkTransaction";
        return $this->setOrchardPaymentGatewayParams($payload, $endpointUrl);
    }

    private function setOrchardPaymentGatewayParams($payload, $endpointUrl)
    {
        $client_id = getenv('ORCHARD_CLIENT');
        $client_secret = getenv('ORCHARD_SECRET');
        $signature = hash_hmac("sha256", $payload, $client_secret);

        $secretKey = $client_id . ":" . $signature;
        try {
            $pay = new OrchardPaymentGateway($secretKey, $endpointUrl, $payload);
            return $pay->initiatePayment();
        } catch (\Exception $e) {
            throw $e;
            return "Error: " . $e;
        }
    }
}
