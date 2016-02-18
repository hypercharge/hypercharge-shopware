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
                $sql = "SELECT * FROM hypercharge_orders WHERE sessionId = ? AND status = 0 ORDER BY id DESC LIMIT 1";
                $trn = Shopware()->Db()->fetchRow($sql, array(Shopware()->SessionID()));
                $uniquePaymentId = $trn["uniquePaymentId"];
                $transactionId = $trn["transactionId"];
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
            Shopware()->Db()->insert('hypercharge_orders',
                    array(
                        'sessionId' => Shopware()->SessionID(),
                        'transactionId' => $transactionId,
                        'uniquePaymentId' => $uniquePaymentId
                    ));
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
                'amount' => (int) (string) ($this->getAmount() * 100),
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
            Shopware()->Db()->update("hypercharge_orders", array('uniqueId' => $paymentH->unique_id), "sessionId = '" . Shopware()->SessionID() . "' AND transactionId = '" . $transactionId . "' AND uniquePaymentId = '" . $uniquePaymentId . "'");

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
        $config = $plugin->Config();
        $plugin->logAction("SUCCESS:");
        foreach ($request->getParams() as $key => $value) {
            $plugin->logAction("\t$key => $value");
        }
        $transactionId = $request->getParam('transactionID');
        $uniquePaymentId = $request->getParam('uniquePaymentID');
        $transaction_id_field = "transactionId";
        if($config->transactionId == "uniqueId"){
            $sql = "SELECT uniqueId FROM hypercharge_orders WHERE transactionId = ? AND uniquePaymentId = ?";
            $uniqueId = Shopware()->Db()->fetchOne($sql, array($transactionId, $uniquePaymentId));
            $plugin->logAction(sprintf('The transactionId is changed from %s to %s', $transactionId, $uniqueId));
            $transactionId = $uniqueId;
            $transaction_id_field = "uniqueId";
        }
        $this->saveOrder($transactionId, $uniquePaymentId, null, true);
        Shopware()->Db()->update("hypercharge_orders", array('status' => 1), $transaction_id_field . " = '" . $transactionId . "' AND uniquePaymentId = '" . $uniquePaymentId . "'");
        $this->redirect(array('controller' => 'checkout',
            'action' => 'finish',
            'sUniqueID' => $uniquePaymentId,
            'sAGB' => 1));
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
        //$this->View()->nfxErrorMessage = Shopware()->Session()->nfxErrorMessage;
        //Shopware()->Session()->offsetUnset('nfxErrorMessage');
        $plugin = $this->Plugin();
        $plugin->logAction("FAILED:");
        foreach ($request->getParams() as $key => $value) {
            $plugin->logAction("\t$key => $value");
            if($key == "msg" && !Shopware()->Session()->nfxErrorMessage){
                Shopware()->Session()->nfxErrorMessage = $value;
                //$this->View()->nfxErrorMessage = Shopware()->Session()->nfxErrorMessage;
            }
        }
        Shopware()->Db()->update("hypercharge_orders", array('status' => -1), "sessionId = '" . Shopware()->SessionID() . "' AND status = 0");
        //we want to display the error message above the payment method
        Shopware()->Session()->nfxFailedAction = true;
        $this->redirect(array('controller' => 'checkout', 'action' => 'confirm','forceSecure' => true));
        return;
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
            $plugin->logAction(sprintf('payment status: %s', $paymentH->status));

            // Find the transaction
            $transaction_id_field = "transactionId";
            $trn = explode('---', $paymentH->transaction_id);
            list($transactionId, $paymentId) = explode(' ', $trn[0]);
            if (empty($transactionId) || empty($paymentId)) {
                $plugin->logAction('Incorrect transaction ID');
                throw new Enlight_Controller_Exception('Incorrect transaction id');
                exit();
            }
            //Find unique_id
            $uniqueId = $paymentH->unique_id;

            // Identify notification channel
            $transactionH = $notification->getTransaction();
            $plugin->logAction(sprintf('transaction type: %s', $transactionH->transaction_type));
            
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
                    $newStatus = 52;//35;
                    break;
                case 'pending':
                case 'pending_async':
                case 'pre_arbitrated':
                    $newStatus = 17;
                    break;
                default:
                    break;
            }
            if (null === $newStatus) {
                $plugin->logAction('Undefined transaction status: ' . $paymentH->status);
                exit();
            }
 
            $try = 1;
            //sometimes Hypercharge is faster than SW => we try more times to check the order
            while($try<4){
                //double-check if the order exists
                $plugin->logAction(sprintf('double-check if the order exists (try %s)', $try));
                $sql = '
                            SELECT id FROM s_order
                            WHERE transactionID=? AND temporaryID=?
                            AND status!=-1
                    ';
                $orderId = Shopware()->Db()->fetchOne($sql, array(
                    $transactionId,
                    $paymentId
                ));

                if(!$orderId && $config->transactionId == "uniqueId"){
                    //double-check if the order exists
                    $sql = '
                                SELECT id FROM s_order
                                WHERE transactionID=? AND temporaryID=?
                                AND status!=-1
                        ';
                    $orderId = Shopware()->Db()->fetchOne($sql, array(
                        $uniqueId,
                        $paymentId
                    ));
                    if($orderId){
                        $transaction_id_field = "uniqueId";
                    }
                }
                if($orderId){
                    $try = 10;
                } else{
                    $try++;
                    sleep(2);
                }
            }

            if (!$orderId) {
                if(in_array($newStatus, array(12,18,17))){
                    //check if it is a cancelled order
                    //but only for approved or pending
                    $sql = "SELECT o.id AS orderId 
                            FROM hypercharge_orders AS ho
                            JOIN s_order o ON ho.sessionId = o.temporaryID
                            WHERE ho.transactionId = ?
                                    AND ho.uniquePaymentId = ?
                                    AND ho.uniqueId = ?
                                    AND o.status = -1";
                    $orderId = Shopware()->Db()->fetchOne($sql, array($transactionId, $paymentId, $uniqueId));
                    if($orderId){
                        $plugin->logAction(sprintf('The order having payment id %s , transaction id %s and unique id %s is a cancelled order', $paymentId, $transactionId, $uniqueId));
                        if($config->transactionId == "uniqueId"){
                            $plugin->logAction(sprintf('The transactionId is changed from %s to %s', $transactionId, $uniqueId));
                            $transaction_id_field = "uniqueId";
                        }
                        $orderId = $this->convertOrder($orderId, ($transaction_id_field == "uniqueId")? $uniqueId: $transactionId, $paymentId);
                        if($orderId){
                            Shopware()->Db()->update("hypercharge_orders", array('status' => 1), "transactionId = '" . $transactionId . "' AND uniquePaymentId = '" . $paymentId . "'");
                        }
                    }
                } else {
                    $plugin->logAction(sprintf("The transaction is not approved; we don't check if it is a cancelled order"));
                }
            }
            if (!$orderId) {
                if($config->transactionId == "uniqueId"){
                    $plugin->logAction(sprintf('The order having payment id %s and transaction id %s or %s does not exist', $paymentId, $transactionId, $uniqueId));
                } else {
                    $plugin->logAction(sprintf('The order having payment id %s and transaction id %s does not exist', $paymentId, $transactionId));
                }
                exit();
            }
            //the transactionID was changed to uniqueID
            if($transaction_id_field == "uniqueId"){
                $transactionId = $uniqueId;
            }

            // Update payment status
            $plugin->logAction(sprintf('Updating transaction %s with payment id %s to %s', $transactionId, $paymentId, $newStatus));
            $this->savePaymentStatus($transactionId, $paymentId, $newStatus, false);

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
            Shopware()->Db()->insert('hypercharge_orders',
                    array(
                        'sessionId' => Shopware()->SessionID(),
                        'transactionId' => $transactionId,
                        'uniquePaymentId' => $uniquePaymentId
                    ));
            $hyperchargeTransactionId = $transactionId . ' ' . $uniquePaymentId;
            $hyperchargeTransactionId = \Hypercharge\Helper::appendRandomId($hyperchargeTransactionId);
            $paymentData = array(
                'type' => 'MobilePayment',
                'usage' => 'Mobile Payment transaction',
                'transaction_id' => $hyperchargeTransactionId,
                'amount' => (int) (string) ($this->getAmount() * 100),
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

            Shopware()->Db()->update("hypercharge_orders", array('uniqueId' => $paymentH->unique_id), "sessionId = '" . Shopware()->SessionID() . "' AND transactionId = '" . $transactionId . "' AND uniquePaymentId = '" . $uniquePaymentId . "'");
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
     * convert a cancelled order into a real order
     * @param null $orderId
     * @param type $transactionId
     * @param type $temporaryId
     * @return null
     * @throws Exception
     */
    private function convertOrder($orderId, $transactionId, $temporaryId){
        try{
            $plugin = $this->Plugin();
            Shopware()->Models()->clear();
            // Get user, shipping and billing
           $builder = Shopware()->Models()->createQueryBuilder();
           $builder->select(array('orders', 'customer', 'billing', 'payment', 'shipping'))
               ->from('Shopware\Models\Order\Order', 'orders')
               ->leftJoin('orders.customer', 'customer')
               ->leftJoin('orders.payment', 'payment')
               ->leftJoin('customer.billing', 'billing')
               ->leftJoin('customer.shipping', 'shipping')
               ->where("orders.id = ?1")
               ->setParameter(1, $orderId);

           $result = $builder->getQuery()->getArrayResult();
           // Check requiered fields
            if (empty($result) || $result[0]['customer'] === null || $result[0]['customer']['billing'] === null) {
                throw new Exception('Could not get required customer data');
            }
            // Get ordernumber
            $numberRepository = Shopware()->Models()->getRepository('Shopware\Models\Order\Number');
            $numberModel = $numberRepository->findOneBy(array('name' => 'invoice'));
            if ($numberModel === null) {
                throw new Exception('Could not get ordernumber');
            }
            $newOrderNumber = $numberModel->getNumber() + 1;

            // Set new ordernumber
            $numberModel->setNumber($newOrderNumber);

            // set new ordernumber to the order
            $orderModel = Shopware()->Models()->find('Shopware\Models\Order\Order', $orderId);
            $orderModel->setNumber($newOrderNumber);

            // set new ordernumber to order details
            $orderDetailRepository = Shopware()->Models()->getRepository('Shopware\Models\Order\Detail');
            $orderDetailModel = $orderDetailRepository->findOneBy(array('orderId' => $orderId));
            $orderDetailModel->setNumber($newOrderNumber);

            // If there is no shipping address, set billing address to be the shipping address
            if ($result[0]['customer']['shipping'] === null) {
                $result[0]['customer']['shipping'] = $result[0]['customer']['billing'];
            }

            // Create new entry in s_order_billingaddress
            $billingModel = new Shopware\Models\Order\Billing();
            $billingModel->fromArray($result[0]['customer']['billing']);
            $billingModel->setCountry(Shopware()->Models()->find('Shopware\Models\Country\Country', $result[0]['customer']['billing']['countryId']));
            $billingModel->setCustomer(Shopware()->Models()->find('Shopware\Models\Customer\Customer', $result[0]['customer']['billing']['customerId']));
            $billingModel->setOrder($orderModel);
            Shopware()->Models()->persist($billingModel);

            // Create new entry in s_order_shippingaddress
            $shippingModel = new Shopware\Models\Order\Shipping();
            $shippingModel->fromArray($result[0]['customer']['shipping']);
            $shippingModel->setCountry(Shopware()->Models()->find('Shopware\Models\Country\Country', $result[0]['customer']['shipping']['countryId']));
            $shippingModel->setCustomer(Shopware()->Models()->find('Shopware\Models\Customer\Customer', $result[0]['customer']['shipping']['customerId']));
            $shippingModel->setOrder($orderModel);
            Shopware()->Models()->persist($shippingModel);

            // Finally set the order to be a regular order
            $statusModel = Shopware()->Models()->find('Shopware\Models\Order\Status', 0);
            $orderModel->setOrderStatus($statusModel);
            $orderModel->setTransactionId($transactionId);
            $orderModel->setTemporaryId($temporaryId);

            Shopware()->Models()->flush();
        } catch (Exception $ex) {
            $orderId = null;
            $plugin->logAction("ERROR: " . $ex->getMessage());
        }
        return $orderId;
    }

    /**
     * return an instance to HyperchargePaymentWpf plugin
     * @return type
     */
    private function Plugin() {
        return Shopware()->Plugins()->Frontend()->HyperchargePaymentWpf();
    }

}
