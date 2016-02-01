<?php

/**
 * HyperchargePaymentWpf
 *
 * @link http://www.nfxmedia.de
 * @copyright Copyright (c) 2016, nfx:MEDIA
 * @author nf, ma info@nfxmedia.de
 * @package nfxMEDIA
 */
class HyperchargeCron {
    const LAST_REPORT_DATE = "/Logs/lastreportdate.txt";
    /**
     * send warning email about the cancelled orders
     * @param type $emails
     */
    public function sendCancelledOrders($emails){
        if (!$this->isReportAlreadyGenerated()) {
            $startDate = date("Y-m-d", strtotime("last Sunday", strtotime("+1 day", strtotime(date("Y-m-d")))));
            $startDate = date("Y-m-d", strtotime("last Sunday", strtotime($startDate)));
            $endDate = date("Y-m-d", strtotime("next Saturday", strtotime($startDate)));
            //get the cancelled orders
            $orders = $this->getOrders($startDate, $endDate);
            if($orders){
                $this->sendEmail($this->Plugin()->getCancelledOrdersEmailTemplate(),$emails,$orders);
            }
            $this->setLastReportDate($startDate);
        }
    }
    
    /**
     * get the cancelled orders in the previous week
     * @param type $startDate
     * @param type $endDate
     * @return type
     */
    private function getOrders($startDate, $endDate){
        $filter = "";
        $payments = $this->Plugin()->HyperchargePayments();
        foreach ($payments as $payment) {
            $filter .= ($filter) ? "," : "";
            $filter .= $payment->getId();
        }
        if (!$filter) {
            return;
        }
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('orders', 'customer', 'billing', 'payment', 'details'))
                ->from('Shopware\Models\Order\Order', 'orders')
                ->leftJoin('orders.details', 'details')
                ->leftJoin('orders.customer', 'customer')
                ->leftJoin('orders.payment', 'payment')
                ->leftJoin('customer.billing', 'billing')
                ->where("orders.status = ?1 AND orders.orderTime >= ?2 AND orders.orderTime < DATE_ADD(?3, 1, 'DAY')")
                ->setParameter(1, -1)
                ->setParameter(2, $startDate)
                ->setParameter(3, $endDate);
        $builder->andWhere('orders.paymentId IN (' . $filter . ')');
        $builder->addOrderBy(array(array('property' => 'orders.orderTime', 'direction'=>'ASC')));
        $query = $builder->getQuery();
        $orders = $query->getArrayResult();
        return $orders;
    }
    
    /**
     * send the email
     * @param type $template
     * @param type $emails
     * @param type $content
     */
    private function sendEmail($template, $emails, $context){
        $_template = clone Shopware()->Template();
        $_template->addTemplateDir($this->Plugin()->Path() . '/Views/');

        $_view = $_template->createData();
        $_view->assign('data', $context);
        $data = $_template->fetch($this->Plugin()->Path() . "/Views/documents/cancelledorders.tpl", $_view);

        $context = array(
            'data' => $data
        );
        $mail = Shopware()->TemplateMail()->createMail($template, $context);
        $mail->clearRecipients();
        foreach($emails as $email){
            $mail->clearRecipients();
            $mail->addTo($email);
            $mail->send();
        }
    }
    
     /**
     * Get last report date
     * If last report date is in previous month, it means that the report was already generated this month
     * so the plugin will not run anymore
     */
    private function isReportAlreadyGenerated() {

        $date = "0000-00-00";
        try {
            if ($fh = @fopen($this->Plugin()->Path() . "/" . self::LAST_REPORT_DATE, "r")) {
                $date = fgets($fh);
                fclose($fh);
            }
        } catch (Exception $ex) {
            
        }
        $new_date = date("Y-m-d", strtotime("last Sunday", strtotime("+1 day", strtotime(date("Y-m-d")))));
        $new_date = date("Y-m-d", strtotime("last Sunday", strtotime($new_date)));

        return ($date >= $new_date);
    }

    /**
     * set last time when the script was called
     * @param type $date
     */
    private function setLastReportDate($date) {
        try {
            if ($fh = @fopen($this->Plugin()->Path() . "/" . self::LAST_REPORT_DATE, "w")) {
                fputs($fh, $date);
                fclose($fh);
            }

            @fclose($fh);
        } catch (Exception $ex) {
            
        }
    }
    
    /**
     * return an instance to HyperchargePaymentWpf plugin
     * @return type
     */
    private function Plugin() {
        return Shopware()->Plugins()->Frontend()->HyperchargePaymentWpf();
    }
}
?>