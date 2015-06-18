<?php

require_once dirname(__FILE__) . '/vendor/autoload.php';

/**
 * HyperchargePaymentWpf
 *
 * @link http://www.nfxmedia.de
 * @copyright Copyright (c) 2014, nfx:MEDIA
 * @author nf, ma info@nfxmedia.de
 * @package nfxMEDIA
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

        return array('success' => true, 'invalidateCache' => array('backend', 'proxy'));
    }

    /**
     * Updates the plugin
     * @return bool
     */
    public function update($version) {
        $this->createPayments();
        $this->createEvents();
        $this->createForm();
        $this->createTranslations();
        /* if($version <= "2.0.0"){
          $this->createSnippets("",true);
          } */
        /* if($version < "2.0.5"){
          $this->createSnippets();
          } */
        $this->createSnippets();

        /* $available_payments = $this->getAvailablePaymentMethods();

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

        return array('success' => true, 'invalidateCache' => array('backend', 'proxy'));
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
        /* $payments = $this->HyperchargePayments();
          foreach ($payments as $payment) {
          $payment->setActive(true);
          } */
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
            //SW5 does not set the payments inactive
            try{
                Shopware()->Db()->query("UPDATE s_core_paymentmeans SET active = 0 WHERE active = 1 AND name = ?;", array($payment->getName()));
            } catch (Exception $ex) {

            }
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
                foreach ($item["logos"] as $image) {
                    $img = base64_encode(file_get_contents(dirname(__FILE__)
                                    . "/img/$image"));
                    $additionalDescription .= '<img src="data:image/png;base64,' . $img . '" border="0" style="margin-right:3px;height:30px;float:left;"/>';
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
            'label' => 'Verwenden Testmodus?', 'value' => true
        ));
        $form->setElement('textarea', 'hypercharge_channels', array(
            'label' => 'Hypercharge Kanal',
            'description' => 'Hypercharge channels, one per line, with the 
                channel elements in the order 
                channel_currency, channel_login, channel_password, channel_id. 
                Channel elements must be separated by commas',
            'value' => 'eg. USD,76876dfca7a,fa223bcaaa,ab55332299f7a'
        ));
        /* $form->setElement('text', 'hypercharge_ttl', array(
          'label' => 'Transaktion TTL',
          'description' => 'Time To Live of the transaction, in minutes',
          'value' => 5
          )); */
        $form->setElement('select', 'hypercharge_layout', array(
            'label' => 'Seiten-Layout des Zahlungsvorgangs',
            'required' => true,
            'value' => 'Redirect',
            'store' => array(array('iFrame', 'Integration der Bezahlseite via iFrame'), array('Redirect', 'Weiterleitung zu Hypercharge'))
        ));
        $form->setElement('numberfield', 'iFrameHeight', array(
            'label' => 'iFrame H&ouml;he',
            'value' => '720'
        ));
        $form->setElement('numberfield', 'iFrameWidth', array(
            'label' => 'iFrame Breite',
            'value' => '959'
        ));
        $form->setElement('combo', 'credit_card_types', array(
            'label' => 'Kreditkartentypen',
            'required' => true,
            'multiSelect' => true,
            'store' => $this->getCardTypes()
        ));
        $form->setElement('checkbox', 'editable_by_user', array(
            'label' => 'Editieren der Rechnungsadresse durch den Nutzer zulassen',
            'value' => false
        ));
        $form->setElement('combo', 'payolution_countries', array(
            'label' => 'Rechnungskauf f&uuml;r &Ouml;sterreich und Schweiz',
            'required' => false,
            'multiSelect' => true,
            'store' => $this->getCountries()
        ));
        $form->setElement('text', 'agree_link', array(
            'label' => 'Meine Einwilligung Link',
            'value' => ''
        ));
        $form->setElement('checkbox', 'birthday_validation', array(
            'label' => 'Validierung Geburtstag',
            'value' => true
        ));
        $form->setElement('checkbox', 'hypercharge_logging', array(
            'label' => 'Ausgabe von Logdateien',
            'value' => false
        ));
    }

    /**
     * Inserts translations for the configuration fields into the db
     * @return void
     */
    public function createTranslations() {
        $form = $this->Form();

        Shopware()->Db()->query("DELETE FROM s_core_config_element_translations WHERE element_id IN (SELECT id FROM s_core_config_elements WHERE form_id = ?)"
                , array($form->getId()));

        $translations = array(
            'en_GB' => array(
                'hypercharge_test' => 'Use test mode?',
                'hypercharge_channels' => 'Hypercharge channels',
                //'hypercharge_ttl' => 'Transaction TTL',
                'credit_card_types' => 'Credit Card Types',
                'hypercharge_logging' => 'Enable logging',
                'hypercharge_layout' => 'Page layout for the payment process',
                'iFrameHeight' => 'iFrame Height',
                'iFrameWidth' => 'iFrame Width',
                'editable_by_user' => 'Allow the user to edit the billing address',
                'payolution_countries' => 'Allow Purchase On Account for Austria and Switzerland',
                'agree_link' => 'My consent link',
                'birthday_validation' => 'Purchase on Account Birthday Validation'
            )
        );
        $shopRepository = Shopware()->Models()->getRepository('\Shopware\Models\Shop\Locale');

        foreach ($translations as $locale => $snippets) {
            $localeModel = $shopRepository->findOneBy(array('locale' => $locale));

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
    public function createPaymentsTranslations() {
        $result = Shopware()->Db()->fetchRow(
                "
                    SELECT *
                    FROM s_core_translations
                    WHERE objecttype = 'config_payment'
                        AND objectkey = 1
                        AND objectlanguage= 2
                ");
        if ($result) {
            $translations = unserialize($result["objectdata"]);
            $action = "update";
        } else {
            $translations = array();
            $action = "insert";
        }
        $payment_methods = $this->getAvailablePaymentMethods();

        foreach ($payment_methods as $method) {
            $payment = $this->Payments()->findOneBy(
                    array('name' => $method["name"])
            );
            if (!array_key_exists($payment->getId(), $translations)) {
                //add EN translations for the new payment
                $translations[$payment->getId()] = array(
                    "description" => $method["description"]["en"]
                );
            }
        }
        if ($action == "update") {
            Shopware()->Db()->query("
                UPDATE  s_core_translations
                SET objectdata = ?
                WHERE objecttype = 'config_payment'
                            AND objectkey = 1
                            AND objectlanguage= 2
                ", array(serialize($translations)));
        } else {
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
        Shopware()->Plugins()->Frontend()->HyperchargePaymentWpf()->addTemplateDirs();
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
        if ($controller != "checkout" && $request->getModuleName() == 'frontend') {
            Shopware()->Session()->offsetUnset("nfxPayolutionBirthdayDay");
            Shopware()->Session()->offsetUnset("nfxPayolutionBirthdayMonth");
            Shopware()->Session()->offsetUnset("nfxPayolutionBirthdayYear");
            Shopware()->Session()->offsetUnset("nfxPayolutionAgree");
        }
        if (!$request->isDispatched() || $response->isException() || $request->getModuleName() != 'frontend' || !(($controller == "checkout" && $action == "confirm") || ($controller == "account" && $action == "payment") || ($controller == "payment_hyperchargewpf" && $action == "failed") || ($controller == "checkout" && $action == "shippingPayment"))
        ) {
            return;
        }
        if($request->isXmlHttpRequest() && !($controller == "checkout" && $action == "shippingPayment")){
            return;
        }
        $view = $args->getSubject()->View();
        $isResponsive = $this->checkShopwareResponsiveTemplate();
        Shopware()->Plugins()->Frontend()->HyperchargePaymentWpf()->addTemplateDirs();
        $isSameAddress = $this->compareAddresses($view->sUserData);
        $isAllowedCountry = $this->isAllowedCountry($view->sUserData["billingaddress"]["countryID"]);

        if ($controller != "account") {
            if(!$isResponsive){
                $view->extendsTemplate('frontend/index/indexHypercharge.tpl');
            }
        }
        if ($controller == "checkout") {
            if(!$isResponsive){
                $view->extendsTemplate('frontend/payment_hyperchargewpf/mobile.tpl');
            }
            $router = Shopware()->Router();
            $view->shopware_redirect = $router->assemble(array(
                'controller' => 'PaymentHyperchargewpf', 'action' => 'hypercharge_mobile', 'forceSecure' => true
            ));
            $view->shopware_failed_redirect = $router->assemble(array(
                'controller' => 'PaymentHyperchargewpf', 'action' => 'failed', 'forceSecure' => true));
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
            $view->nfxSameAddress = $isSameAddress;
            $view->nfxAllowedCountry = $isAllowedCountry;
            $view->nfxAgreeText = Shopware()->Snippets()->getNamespace('HyperchargePaymentWpf/Views/common/frontend/hypercharge/mobile/hyperchargemobile_gp')->get('AgreeText', 'Mit der Übermittlung der für die Abwicklung des Rechnungskaufes und einer Identitäts- und Bonitätsprüfung erforderlichen Daten an payolution bin ich einverstanden. <a href="" target="_blank">Meine Einwilligung</a> kann ich jederzeit mit Wirkung für die Zukunft widerrufen.');
            $view->nfxAgreeText = str_replace('href=""', 'href="' . $this->Config()->agree_link . '"', $view->nfxAgreeText);
            if (isset(Shopware()->Session()->nfxPayolutionBirthdayDay)) {
                $view->nfxPayolutionBirthdayDay = Shopware()->Session()->nfxPayolutionBirthdayDay;
                $view->nfxPayolutionBirthdayMonth = Shopware()->Session()->nfxPayolutionBirthdayMonth;
                $view->nfxPayolutionBirthdayYear = Shopware()->Session()->nfxPayolutionBirthdayYear;
            } else {
                $birthday = $view->sUserData["billingaddress"]["birthday"];
                if ($birthday) {
                    list($view->nfxPayolutionBirthdayYear, $view->nfxPayolutionBirthdayMonth, $view->nfxPayolutionBirthdayDay) = explode("-", $birthday);
                }
            }
            $view->nfxBirthdayValidation = ($this->Config()->birthday_validation) ? "birthday" : "";
            $view->nfxPayolutionAgree = Shopware()->Session()->nfxPayolutionAgree;
            $view->nfxAGBMsg = Shopware()->Snippets()->getNamespace('frontend/checkout/confirm')->get('ConfirmErrorAGB', 'Bitte bestätigen Sie unsere AGB');
            $view->nfxSepaMandateId = date("Ymdhis", time()) . "a" . rand(0, 32000) * rand(0, 32000);
            $view->nfxSepaMandateSignatureDate = date("Y-m-d");
        }
        if ($controller == "checkout" || $controller == "account") {
            if (!$isAllowedCountry) {
                $paymentsVar = ($controller == "checkout") ? "sPayments" : "sPaymentMeans";
                $payments = $view->Template()->getTemplateVars($paymentsVar);
                $new_payments = array();
                foreach ($payments as $payment) {
                    if ($payment["name"] != "hyperchargemobile_pa" && $payment["name"] != "hyperchargemobile_gp") {
                        $new_payments[] = $payment;
                    }
                }
                $view->assign($paymentsVar, $new_payments);
            }
        }
    }
    
    /**
     * add template dirs based on SW version
     */
    public function addTemplateDirs(){
        $isResponsive = $this->checkShopwareResponsiveTemplate();
        Shopware()->Template()->addTemplateDir($this->Path() . 'Views/common/');
        if($isResponsive){
            Shopware()->Template()->addTemplateDir($this->Path() . 'Views/responsive/');
        } else {
            Shopware()->Template()->addTemplateDir($this->Path() . 'Views/emotion/');
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
                "hypercharge_trx" => "sale",
                "logos" => array("visa.png", "mastercard.png", "jcb.png", "diners.png", "amex.png")
            ),
            array(
                "name" => "hyperchargewpf_cc",
                "description" => array(
                    "en" => "Credit Card WPF",
                    "de" => "Kreditkarte WPF"
                ),
                "hypercharge_trx" => "sale",
                "logos" => array("visa.png", "mastercard.png", "jcb.png", "diners.png", "amex.png")
            ),
            array(
                "name" => "hyperchargemobile_dd",
                "description" => array(
                    "en" => "Direct Debit",
                    "de" => "Lastschrift"
                ),
                "hypercharge_trx" => "debit_sale"
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
                "name" => "hyperchargemobile_pa",
                "description" => array(
                    "en" => "Purchase On Account",
                    "de" => "Rechnungskauf via Payolution"
                ),
                "hypercharge_trx" => "purchase_on_account"
            ),
            array(
                "name" => "hyperchargemobile_gp",
                "description" => array(
                    "en" => "GTD Purchase On Account",
                    "de" => "GTD Purchase On Account"
                ),
                "hypercharge_trx" => "gtd_purchase_on_account"
            ),
            array(
                "name" => "hyperchargemobile_gd",
                "description" => array(
                    "en" => "GTD Sepa Debit Sale",
                    "de" => "GTD Sepa Debit Sale"
                ),
                "hypercharge_trx" => "gtd_sepa_debit_sale"
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
    public function createSnippets($files = "", $bRemove = true) {
        if ($bRemove)
            $this->removeSnippets();
        if (!$files) {
            $files = array("shopware_de_utf8.sql", "shopware_en_utf8.sql");
        }
        foreach ($files as $file) {
            $langFile = dirname(__FILE__) . '/build/' . $file;
            if (file_exists($langFile)) {
                $sql = file_get_contents($langFile);
                Shopware()->Db()->exec($sql);
            }
        }
        //return true;
    }

    /**
     * Remove all the snippets for this plugin
     * @return <type>
     */
    public function removeSnippets() {
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
     * get accepted countries for
     * @return type
     */
    private function getCountries() {
        return array(
            array("AT", (Shopware()->Locale()->getLanguage() == "de") ? "Österreich" : "Austria"),
            array("CH", (Shopware()->Locale()->getLanguage() == "de") ? "Schweiz" : "Switzerland")
        );
    }

    /**
     * compare billing address vs shipping address
     * @param type $userData
     */
    private function compareAddresses($userData) {
        $billing = $userData["billingaddress"];
        $shipping = $userData["shippingaddress"];
        if ($billing["countryID"] == "0") {
            $billing["countryID"] = "";
        }
        if ($shipping["countryID"] == "0") {
            $shipping["countryID"] = "";
        }
        if ($billing["stateID"] == "0") {
            $billing["stateID"] = "";
        }
        if ($shipping["stateID"] == "0") {
            $shipping["stateID"] = "";
        }
        return ($billing["company"] == $shipping["company"] && $billing["department"] == $shipping["department"] &&
                $billing["salutation"] == $shipping["salutation"] && $billing["firstname"] == $shipping["firstname"] &&
                $billing["lastname"] == $shipping["lastname"] && $billing["street"] == $shipping["street"] &&
                $billing["streetnumber"] == $shipping["streetnumber"] && $billing["zipcode"] == $shipping["zipcode"] &&
                $billing["city"] == $shipping["city"] && $billing["countryID"] == $shipping["countryID"] &&
                $billing["stateID"] == $shipping["stateID"]);
    }

    /**
     * check if it is DE (or AT, CH)
     * @param type $countryID
     * @return int
     */
    private function isAllowedCountry($countryID) {
        $sql = "SELECT countryiso FROM s_core_countries WHERE id = ?";
        $country = Shopware()->Db()->fetchOne($sql, array($countryID));
        if ($country == "DE") {
            return 1;
        }
        $allowed_countries = $this->Config()->payolution_countries;
        for ($i = 0; $i < count($allowed_countries); $i++) {
            if ($allowed_countries[$i] == $country) {
                return 1;
            }
        }

        return 0;
    }
    
    /**
     * Returns true if it's Shopware 5
     *
     */
    public function checkShopwareResponsiveTemplate() {
        if ($this->assertMinimumVersion('5') && Shopware()->Shop()->getTemplate()->getVersion() >= 3) {
            return true;
        }
        return false;
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
                        $days = 30;
                        if ($diff > $days) {
                            unlink($filename);
                        }
                    }
                }
                closedir($handle);
            }
            //log the message
            if ($handle = fopen($logfile, 'a+')) {
                $sessionid = "";
                try {
                    $sessionid = Shopware()->SessionID();
                } catch (Exception $ex) {
                    
                }
                fwrite($handle, "[" . date(DATE_RFC822) . "] (" . $sessionid . ") " . $message . "\r\n");
                fclose($handle);
            }
        }
    }
    
    /**
     * Reads Plugins Meta Information
     * @return string
     */
    public function getInfo() {
        $info = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'plugin.json'), true);

        if ($info) {
            $img = base64_encode(file_get_contents(dirname(__FILE__)
                        . '/img/logo.png'));
            $description = str_replace("<%IMG%>", $img, $info['description']);
            
            return array(
                'version' => $info['currentVersion'],
                'author' => $info['author'],
                'copyright' => $info['copyright'],
                'label' => $this->getLabel(),
                'source' => $info['source'],
                'description' => $description,
                'license' => $info['license'],
                'support' => $info['support'],
                'link' => $info['link'],
                'changes' => $info['changelog'],
                'revision' => '1'
            );
        } else {
            throw new Exception('The plugin has an invalid version file.');
        }
    }

    /**
     * Returns the current version of the plugin.
     *
     * @return string|void
     * @throws Exception
     */
    public function getVersion() {
        $info = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'plugin.json'), true);

        if ($info) {
            return $info['currentVersion'];
        } else {
            throw new Exception('The plugin has an invalid version file.');
        }
    }

    /**
     * Get (nice) name for plugin manager list
     *
     * @return string
     */
    public function getLabel() {
        $info = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'plugin.json'), true);

        if ($info) {
            return $info['label']["de"];
        } else {
            throw new Exception('The plugin has an invalid version file.');
        }
    }

}
