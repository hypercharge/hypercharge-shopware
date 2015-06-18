<?php

/**
 * HyperchargePaymentWpf
 *
 * @link http://www.nfxmedia.de
 * @copyright Copyright (c) 2014, nfx:MEDIA
 * @author nf, ma info@nfxmedia.de
 * @package nfxMEDIA
 */
class Shopware_Controllers_Frontend_PaymentHyperchargeWpf extends Shopware_Controllers_Frontend_Payment {

    /**
     * Frontend index action controller
     * @return void
     */
    public function indexAction() {
        try {
            $initial_encoding = mb_internal_encoding();
            $payment_name = $this->getPaymentShortName();
            $plugin = $this->Plugin();
            $config = $plugin->Config();
            if (substr($payment_name, 0, 11) != 'hypercharge') {
                $plugin->logAction("index - Invalid payment method " . print_r($payment_name, true));
                //$this->redirect(array('controller' => 'checkout'));
                Shopware()->Session()->nfxErrorMessage = "index - Invalid payment method " . print_r($payment_name, true);
                if ($config->hypercharge_layout == "Redirect") {
                    $this->redirect(array(
                        'action' => 'failed',
                        'forceSecure' => true));
                } else {
                    $return_failure_url = $this->Front()->Router()->assemble(
                            array('action' => 'failed', 'forceSecure' => true));
                    $this->redirect(array(
                        'action' => 'return',
                        'nfxTarget' => urlencode($return_failure_url),
                        'forceSecure' => true));
                }
                return;
            }
            if (substr($payment_name, 0, 17) == 'hyperchargemobile') {
                //for Mobile API we already have the transaction, we just need to save the order
                $session = Shopware()->Session();
                $uniquePaymentId = $session->nfxUniquePaymentID;
                $transactionId = $session->nfxTransactionID;

                $session->offsetUnset('nfxUniquePaymentID');
                $session->offsetUnset('nfxTransactionID');
                if (!$uniquePaymentId || !$transactionId) {
                    $plugin->logAction("Invalid session params");
                    //$this->redirect(array('controller' => 'checkout'));
                    Shopware()->Session()->nfxErrorMessage = "Invalid session params";
                    if ($config->hypercharge_layout == "Redirect") {
                        $this->redirect(array(
                            'action' => 'failed',
                            'forceSecure' => true));
                    } else {
                        $return_failure_url = $this->Front()->Router()->assemble(
                                array('action' => 'failed', 'forceSecure' => true));
                        $this->redirect(array(
                            'action' => 'return',
                            'nfxTarget' => urlencode($return_failure_url),
                            'forceSecure' => true));
                    }
                    return;
                }
                $this->redirect(array(
                    'action' => 'success',
                    'forceSecure' => true,
                    'uniquePaymentID' => $uniquePaymentId,
                    'transactionID' => $transactionId));
                return;
            }
            //continue for WPF
            $nfxLastAPICall = 0;
            $session = Shopware()->Session();
            if (isset($session->nfxLastAPICall)) {
                $nfxLastAPICall = $session->nfxLastAPICall;
            }
            $session->nfxLastAPICall = time();
            $diff = $session->nfxLastAPICall - $nfxLastAPICall;
            $plugin->logAction("Last call $diff \n");
            if ($diff <= 5) {
                $message = "Double click.\n";
                $plugin->logAction($message);
                //throw new Enlight_Controller_Exception($message);
                exit();
            }
            //no double click => ok
            // Get currency, to be able to find channel
            $currency = $this->getCurrencyShortName();
            $channel = $this->getChannelByCurrency($currency);

            $plugin->logAction("index - Payment method " . print_r($payment_name, true));
            $plugin->logAction("Channel setup\n" . print_r($channel, true));
            if (empty($channel))
                throw new Enlight_Controller_Exception('No such channel');

            // Set payment parameters
            $user = $this->getUser();
            $router = $this->Front()->Router();
            $pm = $plugin->getAvailablePaymentMethods();
            $paymentMethods = array();
            foreach ($pm as $p) {
                if ($p["name"] == $payment_name) {
                    $paymentMethods[] = $p["hypercharge_trx"];
                    break;
                }
            }
            if (empty($paymentMethods))
                throw new Enlight_Controller_Exception(
                'Payment methods not supplied');

            $uniquePaymentId = $this->createPaymentUniqueId();
            $transactionId = $this->createPaymentUniqueId();
            $return_success_url = $router->assemble(
                            array('action' => 'success', 'forceSecure' => true))
                    . '?uniquePaymentID=' . $uniquePaymentId
                    . '&transactionID=' . $transactionId;
            $return_failure_url = $router->assemble(
                    array('action' => 'failed', 'forceSecure' => true));
            $return_cancel_url = $router->assemble(array('action' => 'cancel',
                'forceSecure' => true));
            if ($config->hypercharge_layout != "Redirect") {
                $return_success_url = $router->assemble(array(
                    'action' => 'return',
                    'nfxTarget' => urlencode($return_success_url),
                    'forceSecure' => true
                ));
                $return_failure_url = $router->assemble(array(
                    'action' => 'return',
                    'nfxTarget' => urlencode($return_failure_url),
                    'forceSecure' => true
                ));
                $return_cancel_url = $router->assemble(array(
                    'action' => 'return',
                    'nfxTarget' => urlencode($return_cancel_url),
                    'forceSecure' => true
                ));
            } else {
                //this is added because the SDK crashes (createElement crashes for &)
                $return_success_url = htmlentities($return_success_url);
            }
            $hyperchargeTransactionId = $transactionId . ' ' . $uniquePaymentId;
            $hyperchargeTransactionId = \Hypercharge\Helper::appendRandomId($hyperchargeTransactionId);
            $paymentData = array(
                'usage' => 'Web Payment Form transaction',
                'description' => 'Web Payment Form transaction',
                'transaction_id' => $hyperchargeTransactionId,
                'amount' => (int) ($this->getAmount() * 100),
                'currency' => $currency,
                'customer_email' => $user['additional']['user']['email'],
                //'customer_phone' => $user['billingaddress']['phone'],
                'notification_url' => $router->assemble(array('action' => 'notify',
                    'forceSecure' => true)),
                'return_success_url' => $return_success_url,
                'return_failure_url' => $return_failure_url,
                'return_cancel_url' => $return_cancel_url,
                'billing_address' => array(
                    'first_name' => $user['billingaddress']['firstname'],
                    'last_name' => $user['billingaddress']['lastname'],
                    'address1' => $user['billingaddress']['street'] . ' ' . $user['billingaddress']['streetnumber'],
                    'city' => $user['billingaddress']['city'],
                    'zip_code' => $user['billingaddress']['zipcode'],
                    'country' => $user['additional']['country']['countryiso']
                )
            );
            if($user['billingaddress']['phone']){
                $paymentData['customer_phone'] = $user['billingaddress']['phone'];
            }
            if (in_array($paymentData['billing_address']['country'], array('US', 'CA')))
                $paymentData['billing_address']['state'] = $user['additional']['state']['shortcode'];

            $paymentData['billing_address'] = array_map('Shopware_Controllers_Frontend_PaymentHyperchargeWpf::normalizeExport', $paymentData['billing_address']);
            $paymentData['transaction_types'] = $paymentMethods;
            if ($payment_name == "hyperchargewpf_pa") {
                //purchase on account
                if ($user['billingaddress']["company"]) {
                    //B2B
                    //$paymentData["company_name"] = array_map('Shopware_Controllers_Frontend_PaymentHyperchargeWpf::normalizeExport', $user['billingaddress']["company"]);
                }
                //shipping address
                $paymentData['shipping_address'] = array(
                    'first_name' => $user['shippingaddress']['firstname'],
                    'last_name' => $user['shippingaddress']['lastname'],
                    'address1' => $user['shippingaddress']['street'] . ' ' . $user['shippingaddress']['streetnumber'],
                    'city' => $user['shippingaddress']['city'],
                    'zip_code' => $user['shippingaddress']['zipcode'],
                    'country' => $user['additional']['countryShipping']['countryiso']
                );
                if (in_array($paymentData['shipping_address']['country'], array('US', 'CA')))
                    $paymentData['shipping_address']['state'] = $user['additional']['stateShipping']['shortcode'];
                $paymentData['shipping_address'] = array_map('Shopware_Controllers_Frontend_PaymentHyperchargeWpf::normalizeExport', $paymentData['shipping_address']);
                //birthday
                /*if ($user['billingaddress']['birthday'] && $user['billingaddress']['birthday'] != "0000-00-00") {
                    $paymentData['risk_params'] = array(
                        'birthday' => $user['billingaddress']['birthday']
                    );
                }*/
            }
            //birthday
            if ($user['billingaddress']['birthday'] && $user['billingaddress']['birthday'] != "0000-00-00") {
                $paymentData['risk_params'] = array(
                    'birthday' => $user['billingaddress']['birthday']
                );
            }

            /* if (ctype_digit($config->hypercharge_ttl) && ($ttl = $config->hypercharge_ttl * 60) >= 300 && $ttl <= 86400)
              $paymentData['ttl'] = $ttl;
             */
            if (!$config->editable_by_user) {
                $paymentData['editable_by_user'] = false;
            }
            $plugin->logAction("$payment_name started. Payment data\n"
                    . print_r($paymentData, true));

            // Configure gateway
            Hypercharge\Config::set(
                    $channel['username']
                    , $channel['password']
                    , $config->hypercharge_test ? Hypercharge\Config::ENV_SANDBOX : Hypercharge\Config::ENV_LIVE
            );
            mb_internal_encoding("ISO-8859-1");
            // Retrieve redirect URL
            // create the WPF payment session
            $paymentH = Hypercharge\Payment::wpf($paymentData);
            if (!$paymentH->shouldRedirect()) {
                if ($paymentH->isPersistentInHypercharge()) {
                    $plugin->logAction("$payment_name error\n"
                            . print_r($paymentH->error, true));
                    $message = "ERROR: " . $paymentH->error->status_code . ": " . $paymentH->error->message . ' - ' . $paymentH->error->technical_message;
                } else {
                    $message = "$payment_name transaction creation failed.";
                    $plugin->logAction("$payment_name error\n"
                            . $message);
                }
                throw new Enlight_Controller_Exception($message);
            }
            $plugin->logAction("$payment_name response received\n"
                    . print_r($paymentH, true));

            mb_internal_encoding($initial_encoding);
            // Redirect for WPF
            if ($config->hypercharge_layout == "Redirect") {
                $this->redirect($paymentH->getRedirectUrl(Shopware()->Locale()->getLanguage()));
            } else {
                $this->View()->nfxWidth = $config->iFrameWidth;
                $this->View()->nfxHeight = $config->iFrameHeight;
                $this->View()->nfxHyperchargeGatewayUrl = $paymentH->getRedirectUrl(Shopware()->Locale()->getLanguage());
            }
            return;
        } catch (Exception $ex) {
            mb_internal_encoding($initial_encoding);
            $plugin->logAction("ERROR\n"
                    . print_r($ex, true));
            if (is_a($ex, 'Hypercharge\Errors\ValidationError')) {
                $message = "ERROR: " . $ex->status_code . ": " . $ex->message . ' - ' . $ex->technical_message;
                foreach ($ex->errors as $error) {
                    $message .= "<br>" . $error["property"] . ": " . $error["message"];
                }
            } else {
                $message = $ex->getMessage();
            }
            Shopware()->Session()->nfxErrorMessage = $message;
            if ($config->hypercharge_layout == "Redirect") {
                $this->redirect(array(
                    'action' => 'failed',
                    'forceSecure' => true));
            } else {
                $return_failure_url = $this->Front()->Router()->assemble(
                        array('action' => 'failed', 'forceSecure' => true));
                $this->redirect(array(
                    'action' => 'return',
                    'nfxTarget' => urlencode($return_failure_url),
                    'forceSecure' => true));
            }
        }
    }

    /**
     * Upon return from the gateway, the order is saved by this method
     */
    public function successAction() {
        Shopware()->Session()->offsetUnset("nfxLastAPICall");
        Shopware()->Session()->offsetUnset("nfxPayolutionBirthdayDay");
        Shopware()->Session()->offsetUnset("nfxPayolutionBirthdayMonth");
        Shopware()->Session()->offsetUnset("nfxPayolutionBirthdayYear");
        Shopware()->Session()->offsetUnset("nfxPayolutionAgree");
        $request = $this->Request();
        $plugin = $this->Plugin();
        $plugin->logAction("SUCCESS:");
        foreach ($request->getParams() as $key => $value) {
            $plugin->logAction("\t$key => $value");
        }
        $this->saveOrder($request->getParam('transactionID'), $request->getParam('uniquePaymentID'), null, true);
        $this->redirect(array('controller' => 'checkout',
            'action' => 'finish',
            'sUniqueID' => $request->getParam('uniquePaymentID'),
            'sAGB' => 1));
        /*$this->forward("finish", "checkout", 'frontend', array(
            'sUniqueID' => $request->getParam('uniquePaymentID'),
            'sAGB' => 1,
            'appendSession' => true,
            'forceSecure' => true
        ));*/
    }

    /**
     * Controller method executed upon return from the gateway after a 
     * cancellation
     * @return void 
     */
    public function cancelAction() {
        $plugin = $this->Plugin();
        $config = $plugin->Config();
        $plugin->logAction("CANCEL");
        //return $this->redirect(array('controller' => 'checkout'));
        Shopware()->Session()->nfxErrorMessage = Shopware()->Snippets()->getNamespace('HyperchargePaymentWpf/Views/common/frontend/hypercharge/payment_hyperchargewpf/failed')->get("CancelledTransaction", "The transaction was cancelled by the user!");
        if ($config->hypercharge_layout == "Redirect") {
            $this->redirect(array(
                'action' => 'failed',
                'forceSecure' => true));
        } else {
            $return_failure_url = $this->Front()->Router()->assemble(
                    array('action' => 'failed', 'forceSecure' => true));
            $this->redirect(array(
                'action' => 'return',
                'nfxTarget' => urlencode($return_failure_url),
                'forceSecure' => true));
        }
    }

    /**
     * Void method that simply displays the corresponding view for a failed 
     * payment upon return of the customer from the gateway
     */
    public function failedAction() {
        Shopware()->Session()->offsetUnset("nfxLastAPICall");
        $request = $this->Request();
        $this->View()->nfxErrorMessage = Shopware()->Session()->nfxErrorMessage;
        Shopware()->Session()->offsetUnset('nfxErrorMessage');
        $plugin = $this->Plugin();
        $plugin->logAction("FAILED:");
        foreach ($request->getParams() as $key => $value) {
            $plugin->logAction("\t$key => $value");
        }
    }

    /**
     * Controller method executed when a gateway notification is received
     * @throws Enlight_Controller_Exception
     */
    public function notifyAction() {
        try {
            // Get request variables
            $request = $this->Request();
            $plugin = $this->Plugin();
            $plugin->logAction("Notification received\n"
                    . print_r($request->getPost(), true));
            $config = $plugin->Config();

            $channel = $this->getChannelById($request->getParam("payment_transaction_channel_token"));
            if (empty($channel)) {
                $plugin->logAction('Could not find channel');
                throw new Enlight_Controller_Exception('No such channel');
                exit();
            }
            // Configure gateway
            Hypercharge\Config::set(
                    $channel['username']
                    , $channel['password']
                    , $config->hypercharge_test ? Hypercharge\Config::ENV_SANDBOX : Hypercharge\Config::ENV_LIVE
            );
            $notification = Hypercharge\Payment::notification($request->getPost());

            if (!$notification->isVerified()) {
                $plugin->logAction('Notification is not verified');
                throw new Enlight_Controller_Exception('Notification is not verified');
                exit();
            }
            $paymentH = $notification->getPayment();

            if ($paymentH->isError() && $paymentH->error->status_code) {
                $message = "ERROR: " . $paymentH->error->status_code . ": " . $paymentH->error->message . ' - ' . $paymentH->error->technical_message;
                $plugin->logAction($message);
                exit();
            }
            $plugin->logAction("payment OK");

            // Find the transaction
            $trn = explode('---', $paymentH->transaction_id);
            list($transactionId, $paymentId) = explode(' ', $trn[0]);
            if (empty($transactionId) || empty($paymentId)) {
                $plugin->logAction('Incorrect transaction ID');
                throw new Enlight_Controller_Exception('Incorrect transaction id');
                exit();
            }

            // Identify notification channel
            $transactionH = $notification->getTransaction();

            //double-check if the order exists
            $sql = '
			SELECT id FROM s_order
			WHERE transactionID=? AND temporaryID=?
			AND status!=-1
		';
            $orderId = Shopware()->Db()->fetchOne($sql, array(
                $transactionId,
                $paymentId
            ));

            if (!$orderId) {
                $plugin->logAction(sprintf('The order having transaction %s and payment id %s does not exist', $transactionId, $paymentId));
                exit();
            }

            // Payment status mapping
            $newStatus = null;
            $isAuthorize = $transactionH->transaction_type == 'authorize' || $transactionH->transaction_type == 'authorize3d';
            switch ($paymentH->status) {
                case 'approved':
                case 'chargeback_reversed':
                    $newStatus = 12;
                    if ($isAuthorize)
                        $newStatus = 18;
                    break;
                case 'declined':
                case 'refunded':
                case 'chargebacked':
                case 'voided':
                case 'error':
                case 'rejected':
                    $newStatus = 35;
                case 'pending':
                case 'pending_async':
                case 'pre_arbitrated':
                    $newStatus = 17;
                default:
                    break;
            }
            if (null === $newStatus) {
                $plugin->logAction('Undefined transaction status: ' . $paymentH->status);
                exit();
            }

            // Update payment status
            $plugin->logAction(sprintf('Updating transaction %s with payment id %s to %s', $transactionId, $paymentId, $newStatus));
            $this->savePaymentStatus($transactionId, $paymentId, $newStatus, true);

            $plugin->logAction('Notification finished');

            // Tell hypercharge the notification has been successfully processed
            // and ensure output ends here
            die($notification->ack());
        } catch (Exception $ex) {
            $plugin->logAction("ERROR: " . $ex->getMessage());
        }
        exit();
    }

    public function hyperchargeMobileAction() {
        try {
            $plugin = $this->Plugin();
            //check for double-click; avoid sending the data more than once
            $session = Shopware()->Session();
            $session->offsetUnset("nfxErrorMessage");
            $nfxLastAPICall = 0;
            if (isset($session->nfxLastAPICall)) {
                $nfxLastAPICall = $session->nfxLastAPICall;
            }
            $session->nfxLastAPICall = time();
            $diff = $session->nfxLastAPICall - $nfxLastAPICall;
            $plugin->logAction("Last call $diff \n");
            if ($diff <= 5) {
                $message = "Double click.\n";
                $plugin->logAction($message);
                throw new Enlight_Controller_Exception($message);
                exit();
            }
            //no double click => ok
            $initial_encoding = mb_internal_encoding();
            $request = $this->Request();
            $router = $this->Front()->Router();

            $payolution = $request->getParam('payolution');
            if ($payolution) {
                if ($payolution["birthday_day"]) {
                    $session->nfxPayolutionBirthdayDay = $payolution["birthday_day"];
                }
                if ($payolution["birthday_month"]) {
                    $session->nfxPayolutionBirthdayMonth = $payolution["birthday_month"];
                }
                if ($payolution["birthday_year"]) {
                    $session->nfxPayolutionBirthdayYear = $payolution["birthday_year"];
                }
                if ($payolution["agree"]) {
                    $session->nfxPayolutionAgree = $payolution["agree"];
                }
            }
            $payment_name = $this->getPaymentShortName();
            if (substr($payment_name, 0, 17) != 'hyperchargemobile') {
                $plugin->logAction("hyperchargemobile - Invalid payment method " . print_r($payment_name, true));
                throw new Enlight_Controller_Exception('Invalid payment');
                exit();
            }
            // Get currency, to be able to find channel
            $currency = $this->getCurrencyShortName();
            $config = $plugin->Config();
            $channel = $this->getChannelByCurrency($currency);
            $plugin->logAction("hyperchargemobile - Payment method " . print_r($payment_name, true));
            $plugin->logAction("Channel setup\n" . print_r($channel, true));
            if (empty($channel)) {
                throw new Enlight_Controller_Exception('No such channel');
                exit();
            }

            // Configure gateway
            Hypercharge\Config::set(
                    $channel['username']
                    , $channel['password']
                    , $config->hypercharge_test ? Hypercharge\Config::ENV_SANDBOX : Hypercharge\Config::ENV_LIVE
            );

            // Set payment parameters
            $user = $this->getUser();
            $router = $this->Front()->Router();

            $uniquePaymentId = $this->createPaymentUniqueId();
            $transactionId = $this->createPaymentUniqueId();
            $hyperchargeTransactionId = $transactionId . ' ' . $uniquePaymentId;
            $hyperchargeTransactionId = \Hypercharge\Helper::appendRandomId($hyperchargeTransactionId);
            $paymentData = array(
                'type' => 'MobilePayment',
                'usage' => 'Mobile Payment transaction',
                'transaction_id' => $hyperchargeTransactionId,
                'amount' => (int) ($this->getAmount() * 100),
                'currency' => $currency,
                'customer_email' => $user['additional']['user']['email'],
                //'customer_phone' => $user['billingaddress']['phone'],
                'notification_url' => $router->assemble(array('action' => 'notify',
                    'forceSecure' => true)),
                'billing_address' => array(
                    'first_name' => $user['billingaddress']['firstname'],
                    'last_name' => $user['billingaddress']['lastname'],
                    'address1' => $user['billingaddress']['street'] . ' '
                    . $user['billingaddress']['streetnumber'],
                    'city' => $user['billingaddress']['city'],
                    'zip_code' => $user['billingaddress']['zipcode'],
                    'country' => $user['additional']['country']['countryiso']
                )
            );
            if($user['billingaddress']['phone']){
                $paymentData['customer_phone'] = $user['billingaddress']['phone'];
            }
            if (in_array($paymentData['billing_address']['country'], array('US', 'CA')))
                $paymentData['billing_address']['state'] = $user['additional']['state']['shortcode'];
            $paymentData['billing_address'] = array_map('Shopware_Controllers_Frontend_PaymentHyperchargeWpf::normalizeExport', $paymentData['billing_address']);
            if ($payment_name == "hyperchargemobile_pa") {
                if ($user['billingaddress']["company"]) {
                    //B2B
                    //$paymentData["company_name"] = $user['billingaddress']["company"];
                }
            }
            //birthday
            $birthday = "";
            if ($payolution["birthday_day"] && $payolution["birthday_month"] && $payolution["birthday_year"]) {
                $birthday = join("-", array($payolution["birthday_year"], str_pad($payolution["birthday_month"],2,"0",STR_PAD_LEFT), str_pad($payolution["birthday_day"],2,"0",STR_PAD_LEFT)));
            } else {
                $birthday = $user['billingaddress']['birthday'];
            }
            if ($birthday && $birthday != "0000-00-00") {
                $paymentData['risk_params'] = array(
                    'birthday' => $birthday
                );
            }
            $pm = $plugin->getAvailablePaymentMethods();
            $paymentMethods = array();
            foreach ($pm as $p) {
                if ($p["name"] == $payment_name) {
                    $paymentMethods[] = $p["hypercharge_trx"];
                    break;
                }
            }
            if (empty($paymentMethods)) {
                $plugin->logAction("Payment methods not supplied\n");
                throw new Enlight_Controller_Exception(
                'Payment methods not supplied');
                exit();
            }
            $paymentData['transaction_types'] = $paymentMethods;

            $plugin->logAction("$payment_name started. Payment data\n"
                    . print_r($paymentData, true));

            mb_internal_encoding("ISO-8859-1");
            // create the mobile payment session
            $paymentH = Hypercharge\Payment::mobile($paymentData);
            if (!$paymentH->shouldContinueInMobileApp()) {
                $plugin->logAction("$payment_name error\n"
                        . print_r($paymentH, true));
                throw new Enlight_Controller_Exception("$payment_name transaction creation failed.");
                exit();
            }

            $plugin->logAction("$payment_name response received\n"
                    . print_r($paymentH, true));

            Shopware()->Session()->nfxUniquePaymentID = $uniquePaymentId;
            Shopware()->Session()->nfxTransactionID = $transactionId;

            echo json_encode(array(
                "success" => true,
                "redirect_url" => $paymentH->redirect_url,
                'return_success_url' => $router->assemble(
                        array('action' => 'success', 'forceSecure' => true))
                . '?uniquePaymentID=' . $uniquePaymentId
                . '&transactionID=' . $transactionId //this is not used anymore
            ));
        } catch (Exception $ex) {
            $plugin->logAction("ERROR\n"
                    . print_r($ex, true));
            if (is_a($ex, 'Hypercharge\Errors\ValidationError')) {
                $message = "ERROR: " . $ex->status_code . ": " . $ex->message . ' - ' . $ex->technical_message;
                foreach ($ex->errors as $error) {
                    $message .= "<br>" . $error["property"] . ": " . $error["message"];
                }
            } else {
                $message = $ex->getMessage();
            }
            Shopware()->Session()->nfxErrorMessage = $message;
            echo json_encode(array(
                "success" => false
            ));
        }
        mb_internal_encoding($initial_encoding);
        exit();
    }

    /**
     * go to return page in order to redirect the parent to faield or success
     */
    public function returnAction() {
        $config = $this->Plugin()->Config();
        $this->View()->nfxWidth = $config->iFrameWidth;
        $this->View()->nfxHeight = $config->iFrameHeight;
        $this->View()->nfxRedirectURL = $this->Request()->nfxTarget;
    }

    /**
     * Returns a channel configuration by currency name
     * @param string $currency
     * @return array
     */
    protected function getChannelByCurrency($currency) {
        return $this->getChannelByKeyValue($currency);
    }

    /**
     * Returns the channel configuration by channel ID
     * @param string $channelId
     * @return array
     */
    protected function getChannelById($channelId) {
        return $this->getChannelByKeyValue($channelId, 3);
    }

    /**
     * Returns the channel configuration array by specifying the key
     * @param type $value The value of the specified key
     * @param type $key The key
     * @return array The channel array
     * @throws Enlight_Controller_Exception
     */
    protected function getChannelByKeyValue($value, $key = 0) {
        $plugin = $this->Plugin();
        $config = $plugin->Config();
        $channelLines = explode("\n", $config->hypercharge_channels);
        if (!is_array($channelLines) || empty($channelLines))
            throw new Enlight_Controller_Exception('Incorrect channels config');
        $channel = array();
        foreach ($channelLines as $l) {
            $c = explode(',', $l);
            if (count($c) != 4 || $c[$key] != $value)
                continue;
            $channel['channel'] = $c[3];
            $channel['username'] = $c[1];
            $channel['password'] = $c[2];
            break;
        }

        return $channel;
    }

    /**
     * encode data which will be sent to cUrl
     * @param type $val
     * @return type
     */
    public static function normalizeExport($val) {
        if (mb_detect_encoding($val, "UTF-8")) {
            return $val;
        }
        return iconv(mb_internal_encoding(), 'utf-8', $val);
    }

    /**
     * return an instance to HyperchargePaymentWpf plugin
     * @return type
     */
    private function Plugin() {
        return Shopware()->Plugins()->Frontend()->HyperchargePaymentWpf();
    }

}
