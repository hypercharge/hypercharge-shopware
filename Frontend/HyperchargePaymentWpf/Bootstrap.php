<?php

require_once dirname(__FILE__) . '/vendor/autoload.php';

/**
 * HyperchargePaymentWpf
 *
 * @link http://www.nfxmedia.de
 * @copyright Copyright (c) 2014, nfx:MEDIA
 * @author nf, ma info@nfxmedia.de
 * @package nfxMEDIA
 * @version 2.0.0 / implement Mobile API + select WPF payment method in Shopware + iFrame for WPF / 2014-05-13
 * @version 2.0.1 / fix UTF8 issue + some translations / 2014-06-10
 * @version 2.0.2 / some translations / 2014-06-12
 */
class Shopware_Plugins_Frontend_HyperchargePaymentWpf_Bootstrap extends Shopware_Components_Plugin_Bootstrap {

    /**
     * Performs the necessary installation steps
     * @return boolean
     */
    public function install() {
        if (!$this->assertVersionGreaterThen('4.0.4')) {
            throw new Enlight_Exception('This Plugin needs min shopware 4.0.4');
        }

        $plugins = array('Payment');
        if (!$this->assertRequiredPluginsPresent($plugins)) {
            self::logAction('This plugin requires the plugin payment');
            $this->uninstall();
            throw new Enlight_Exception(
            'This plugin requires the plugin payment');
        }

        $this->createPayments();
        $this->createForm();
        $this->createTranslations();
        $this->createPaymentsTranslations();
        $this->createSnippets();
        $this->createEvents();

        return true;
    }

    /**
     * Updates the plugin
     * @return bool
     */
    public function update($version) {
        $this->createPayments();
        $this->createEvents();
        $this->createForm();
        if($version <= "2.0.0"){
            $this->createSnippets("",true);
        }
        
        /*$available_payments = $this->getAvailablePaymentMethods();

        foreach ($available_payments as $item) {
            $payment = $this->Payments()->findOneBy(
                    array('name' => $item["name"])
            );
            if ($payment) {
                $additionalDescription = "";
                foreach($item["logos"] as $image){
                    $img = base64_encode(file_get_contents(dirname(__FILE__)
                        . "/img/$image"));
                    $additionalDescription .= '<img src="data:image/png;base64,' . $img . '" border="0" style="margin-right:3px;height:30px;"/>';
                }
                $sql = "update s_core_paymentmeans set additionalDescription = ? where name = ?";
                Shopware()->Db()->query($sql, array($additionalDescription, $item["name"]));
            }
        }
         */
    
        return true;
    }

    /**
     * Performs the necessary uninstallation steps
     * @return boolean
     */
    public function uninstall() {
        $this->disable();
        /* $payments = $this->HyperchargePayments();
          foreach($payments as $payment){
          if ($payment)
          $payment->delete();
          } */
        $this->removeSnippets();
        return parent::uninstall();
    }

    /**
     * Enables the plugin
     * @return boolean
     */
    public function enable() {
        $payments = $this->HyperchargePayments();
        foreach ($payments as $payment) {
            $payment->setActive(true);
        }
        return parent::enable();
    }

    /**
     * Disables the plugin
     * @return boolean
     */
    public function disable() {
        $payments = $this->HyperchargePayments();
        foreach ($payments as $payment) {
            $payment->setActive(false);
        }

        return parent::disable();
    }

    /**
     * Creates the payment method
     * @return void
     */
    protected function createPayments() {
        $img = base64_encode(file_get_contents(dirname(__FILE__)
                        . '/img/logo.png'));
        $available_payments = $this->getAvailablePaymentMethods();

        foreach ($available_payments as $item) {
            $payment = $this->Payments()->findOneBy(
                    array('name' => $item["name"])
            );
            if (!$payment) {
                /* $payment_desc = "Hypercharge WebPaymentForm allows you to use a variety of 
                  payment methods, both online and off-line. Any sensitive
                  information is safely aquired an processed on out platform";
                  if (substr($payment_name, 0, 17) == 'hyperchargemobile') {
                  $payment_desc = "The Mobile API payment methods are PCI compliant –
                  the payment data are transferred directly from customer device
                  to Hypercharge secure servers without the interference of merchant server.";
                  } */
                $additionalDescription = "";
                foreach($item["logos"] as $image){
                    $img = base64_encode(file_get_contents(dirname(__FILE__)
                        . "/img/$image"));
                    $additionalDescription .= '<img src="data:image/png;base64,' . $img . '" border="0" style="margin-right:3px;height:30px;"/>';
                }
                $this->createPayment(array(
                    'name' => $item["name"],
                    'description' => $item["description"]["de"],
                    'action' => 'payment_hyperchargewpf',
                    'active' => 1,
                    'position' => 1/* ,
                      'additionalDescription' =>
                      '<img src="data:image/png;base64,' . $img . '"/><br/><br/>
                      <div id="payment_desc">
                      ' . $payment_desc . '
                      </div>' */,
                    'additionalDescription' => $additionalDescription
                ));
            }
        }
    }

    /**
     * Creates the configuration fields
     * @return void
     */
    public function createForm() {
        $form = $this->Form();

        $form->setElement('boolean', 'hypercharge_test', array(
            'label' => 'Use test mode?', 'value' => true
        ));
        $form->setElement('textarea', 'hypercharge_channels', array(
            'label' => 'Hypercharge channels',
            'description' => 'Hypercharge channels, one per line, with the 
                channel elements in the order 
                channel_currency, channel_login, channel_password, channel_id. 
                Channel elements must be separated by commas',
            'value' => 'eg. USD,76876dfca7a,fa223bcaaa,ab55332299f7a'
        ));
        /*$form->setElement('text', 'hypercharge_ttl', array(
            'label' => 'Transaction TTL',
            'description' => 'Time To Live of the transaction, in minutes',
            'value' => 5
        ));*/
        $form->setElement('select', 'hypercharge_layout', array(
            'label' => 'Page layout for the payment process',
            'required' => true,
            'value' => 'iFrame',
            'store' => array(array('iFrame', 'Integration der Bezahlseite via iFrame'), array('Redirect', 'Weiterleitung zu Hypercharge'))
        ));
        $form->setElement('numberfield', 'iFrameHeight', array(
            'label' => 'iFrame Height',
            'value' => '720'
        ));
        $form->setElement('numberfield', 'iFrameWidth', array(
            'label' => 'iFrame Width',
            'value' => '959'
        ));
        $form->setElement('combo', 'credit_card_types', array(
            'label' => 'Credit Card Types',
            'required' => true,
            'multiSelect' => true,
            'store' => $this->getCardTypes()
        ));
        $form->setElement('checkbox', 'editable_by_user', array(
            'label' => 'Allow the user to edit the billing address',
            'value' => false
        ));
        $form->setElement('checkbox', 'hypercharge_logging', array(
            'label' => 'Enable logging',
            'value' => false
        ));
        $form->save();
    }

    /**
     * Inserts translations for the configuration fields into the db
     * @return void
     */
    public function createTranslations() {
        $form = $this->Form();
        $translations = array(
            'de_DE' => array(
                'hypercharge_test' => 'Verwenden Testmodus?',
                'hypercharge_channels' => 'Hypercharge Kanal',
                'hypercharge_ttl' => 'Transaktion TTL',
                'credit_card_types' => 'Kreditkartentypen',
                'hypercharge_logging' => 'Ausgabe von Logdateien',
                'hypercharge_layout' => 'Seiten-Layout des Zahlungsvorgangs',
                'iFrameHeight' => 'iFrame H&ouml;he',
                'iFrameWidth' => 'iFrame Breite',
                'editable_by_user' => 'Editieren der Rechnungsadresse durch den Nutzer zulassen'
            )
        );
        $shopRepository = Shopware()->Models()
                ->getRepository('\Shopware\Models\Shop\Locale');
        foreach ($translations as $locale => $snippets) {
            $localeModel = $shopRepository->findOneBy(array(
                'locale' => $locale
            ));
            foreach ($snippets as $element => $snippet) {
                if ($localeModel === null)
                    continue;
                $elementModel = $form->getElement($element);
                if ($elementModel === null)
                    continue;
                $translationModel = new \Shopware\Models\Config\ElementTranslation();
                $translationModel->setLabel($snippet);
                $translationModel->setLocale($localeModel);
                $elementModel->addTranslation($translationModel);
            }
        }
    }
    
    /**
     * Create the translation for payments: name + description
     */
    public function createPaymentsTranslations(){
        $result = Shopware()->Db()->fetchRow(
                "
                    SELECT *
                    FROM s_core_translations
                    WHERE objecttype = 'config_payment'
                        AND objectkey = 1
                        AND objectlanguage= 2
                ");
        if($result){
            $translations = unserialize($result["objectdata"]);
            $action = "update";
        }else{
            $translations = array();
            $action = "insert";
        }
        $payment_methods = $this->getAvailablePaymentMethods();

        foreach ($payment_methods as $method) {
            $payment = $this->Payments()->findOneBy(
                    array('name' => $method["name"])
            );
            if(!array_key_exists($payment->getId(), $translations)){
                //add EN translations for the new payment
                $translations[$payment->getId()] = array(
                    "description" => $method["description"]["en"]
                );
            }
        }
        if($action == "update"){
            Shopware()->Db()->query("
                UPDATE  s_core_translations
                SET objectdata = ?
                WHERE objecttype = 'config_payment'
                            AND objectkey = 1
                            AND objectlanguage= 2
                ", array(serialize($translations)));
        }else{
            Shopware()->Db()->query("
                INSERT INTO  s_core_translations(objecttype, objectdata, objectkey, objectlanguage)
                VALUES('config_payment', ?, 1, 2)
                ", array(serialize($translations)));
        }
    }

    /**
     * creates and subscribes events
     */
    protected function createEvents() {
        $this->subscribeEvent(
                'Enlight_Controller_Dispatcher_ControllerPath_Frontend_PaymentHyperchargewpf', 'onGetControllerPath');
        $this->subscribeEvent(
                'Enlight_Controller_Action_PostDispatch', 'onPostDispatch'
        );
    }

    /**
     * Returns the controller path
     * @param Enlight_Event_EventArgs The iterable array-like arguments object
     * @return string
     */
    public static function onGetControllerPath(Enlight_Event_EventArgs $args) {
        Shopware()->Template()->addTemplateDir(dirname(__FILE__) . '/Views/');
        return dirname(__FILE__) . '/Controllers/frontend/Hyperchargewpf.php';
    }

    /**
     * Triggered on every request, adds the template modifications
     * @param Enlight_Event_EventArgs Arguments received, @see onGetControllerPath
     * @return void
     */
    public function onPostDispatch(Enlight_Event_EventArgs $args) {
        $request = $args->getSubject()->Request();
        $response = $args->getSubject()->Response();

        $controller = $request->getControllerName();
        $action = $request->getActionName();

        if (!$request->isDispatched() || $response->isException() || $request->getModuleName() != 'frontend' || $request->isXmlHttpRequest() || !(($controller == "checkout" && $action == "confirm") || ($controller == "payment_hyperchargewpf" && $action == "failed"))
        ) {
            return;
        }

        $view = $args->getSubject()->View();
        Shopware()->Template()->addTemplateDir($this->Path() . 'Views/');
        $view->extendsTemplate('frontend/index/indexHypercharge.tpl');
        if ($controller == "checkout") {
            $view->extendsTemplate('frontend/payment_hyperchargewpf/mobile.tpl');
            $router = Shopware()->Router();
            $view->shopware_redirect = $router->assemble(array(
                'controller' => 'PaymentHyperchargewpf', 'action' => 'hypercharge_mobile', 'forceSecure' => true
            ));
            $view->shopware_failed_redirect = $router->assemble(array(
                'action' => 'failed', 'forceSecure' => true));
            $credit_card_types = array();
            $all_credit_card_types = $this->getCardTypes();
            $allowed_credit_card_types = $this->Config()->credit_card_types;
            for ($i = 0; $i < count($allowed_credit_card_types); $i++) {
                foreach ($all_credit_card_types as $type) {
                    if ($type[0] == $allowed_credit_card_types[$i]) {
                        $credit_card_types[] = array($type[0], $type[1]);
                    }
                }
            }
            $view->credit_card_types = $credit_card_types;
            $view->nfxLang = Shopware()->Locale()->getLanguage();
        }
    }

    /**
     * Returns the current payment row
     * @return object The current Payment row
     */
    public function HyperchargePayments() {
        $payments = array();
        $payment_methods = $this->getAvailablePaymentMethods();

        foreach ($payment_methods as $method) {
            $payment = $this->Payments()->findOneBy(
                    array('name' => $method["name"])
            );
            if ($payment) {
                $payments[] = $payment;
            }
        }
        return $payments;
    }

    /**
     * get the list of all available payments
     * @return type
     */
    public function getAvailablePaymentMethods() {
        return array(
            array(
                "name" => "hyperchargemobile_cc",
                "description" => array(
                    "en" => "Credit Card",
                    "de" => "Kreditkarte"
                ),
            //"hypercharge_trx" => "sale",
                "logos" => array("visa.png", "mastercard.png", "jcb.png", "diners.png", "amex.png")
            ),
            array(
                "name" => "hyperchargemobile_dd",
                "description" => array(
                    "en" => "Direct Debit",
                    "de" => "Lastschrift"
                ),
            //"hypercharge_trx" => "debit_sale"
            ),
            array(
                "name" => "hyperchargewpf_pp",
                "description" => array(
                    "en" => "PayPal",
                    "de" => "PayPal"
                ),
                "hypercharge_trx" => "pay_pal",
                "logos" => array("paypal.png")
            ),
            array(
                "name" => "hyperchargewpf_id",
                "description" => array(
                    "en" => "iDeal",
                    "de" => "iDeal"
                ),
                "hypercharge_trx" => "ideal_sale",
                "logos" => array("ideal.png")
            ),
            array(
                "name" => "hyperchargewpf_pa",
                "description" => array(
                    "en" => "Purchase on Account",
                    "de" => "Kauf auf Rechnung"
                ),
                "hypercharge_trx" => "purchase_on_account"
            ),
            array(
                "name" => "hyperchargewpf_dp",
                "description" => array(
                    "en" => "Direct Pay24",
                    "de" => "Sofortüberweisung"
                ),
                "hypercharge_trx" => "direct_pay24_sale",
                "logos" => array("sofort.png")
            ),
            array(
                "name" => "hyperchargewpf_gp",
                "description" => array(
                    "en" => "Giro Pay",
                    "de" => "Giro Pay"
                ),
                "hypercharge_trx" => "giro_pay_sale",
                "logos" => array("giro.png")
            ),
            array(
                "name" => "hyperchargewpf_pi",
                "description" => array(
                    "en" => "Pay in Advance",
                    "de" => "Vorkasse"
                ),
                "hypercharge_trx" => "pay_in_advance"
            ),
            array(
                "name" => "hyperchargewpf_ps",
                "description" => array(
                    "en" => "Pay Safe Card",
                    "de" => "Pay Safe Card"
                ),
                "hypercharge_trx" => "pay_safe_card_sale",
                "logos" => array("paysafecard.png")
            ),
            array(
                "name" => "hyperchargewpf_pd",
                "description" => array(
                    "en" => "Payment on Delivery",
                    "de" => "Nachnahme"
                ),
                "hypercharge_trx" => "payment_on_delivery"
            ),
        );
    }

    /**
     * Create snippets: insert into s_core_snippets
     *
     * @param <type> $files
     * @param <type> $bRemove
     * @return <type>
     */
    public function createSnippets($files = "", $bRemove = true){
        if($bRemove)
            $this->removeSnippets();
        if(!$files){
            $files = array("shopware_de_utf8.sql", "shopware_en_utf8.sql");
        }
        foreach($files as $file){
            $langFile = dirname(__FILE__).'/build/'.$file;
            if (file_exists($langFile)) {
                $sql = file_get_contents($langFile);
                Shopware()->Db()->exec($sql);
            }
        }
        return true;
    }
    /**
     * Remove all the snippets for this plugin
     * @return <type>
     */
    public function removeSnippets(){
        $sql = 'DELETE FROM `s_core_snippets` WHERE `namespace` LIKE "HyperchargePaymentWpf/Views/%";';
        Shopware()->Db()->exec($sql);
        return true;
    }
    
    /**
     * get accepted card types
     * @return type
     */
    private function getCardTypes() {
        return array(
            array("AE", "American Express"),
            array("VI", "Visa"),
            array("MC", "MasterCard"),
            array("DI", "Discover"),
            array("JCB", "JCB"),
            array("OT", (Shopware()->Locale()->getLanguage() == "de") ? "Andere" : "Other")
        );
    }

    /**
     * Logger for events
     * @param string The event message
     * @return void
     */
    public static function logAction($message) {
        $folder = realpath(dirname(__FILE__)) . "/Logs";
        $logfile = $folder . "/log" . date('Ymd', strtotime('Last Monday', time())) . ".txt";
        if (Shopware()->Plugins()->Frontend()->HyperchargePaymentWpf()->Config()->hypercharge_logging) {
            //remove old logs
            if ($handle = opendir($folder)) {
                $now = date("Y-m-d");
                while (false !== ($file = readdir($handle))) {
                    if ($file !== '.' && $file !== '..') {
                        $filename = $folder . DIRECTORY_SEPARATOR . $file;

                        $filedate = date('Y-m-d', filemtime($filename));
                        $diff = (strtotime($now) - strtotime($filedate)) / (60 * 60 * 24); //it will count no. of days

                        if ($diff > $days) {
                            unlink($filename);
                        }
                    }
                }
                closedir($handle);
            }
            //log the message
            if ($handle = fopen($logfile, 'a+')) {
                fwrite($handle, "[" . date(DATE_RFC822) . "] " . $message . "\r\n");
                fclose($handle);
            }
        }
    }

    /**
     * Returns information about the payment plugin
     * @return array
     */
    public function getInfo() {
        $img = base64_encode(file_get_contents(dirname(__FILE__)
                        . '/img/logo.png'));
        return array(
            'version' => $this->getVersion(),
            'label' => $this->getLabel(),
            'description' => '<p>
                    <img src="data:image/png;base64,' . $img . '" />
                </p>
                <p style="font-size:12px; font-weight: bold;">
                    Hypercharge WebPaymentForm allows you to use a variety of 
                    payment methods, both online and off-line. Any sensitive 
                    information is safely aquired an processed on out platform
                </p>',
            'autor' => 'nfx:MEDIA',
            'copyright' => 'Copyright (c) 2014, nfx:MEDIA',
            'source' => '',
            'license' => '',
            'support' => 'info@nfxmedia.de',
            'link' => 'http://www.nfxmedia.de',
            'changes' => '',
            'revision' => '4840'
        );
    }

    /**
     * Returns the version
     * @return string
     */
    public function getVersion() {
        return "2.0.2";
    }

    /**
     * Returns the plugins label
     * @return string
     */
    public function getLabel() {
        return "Hypercharge Payment Plugin";
    }

}
