<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"> 
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <meta name="format-detection" content="telephone=no" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0"/>
    </head>
    <body>
        <div style="font-family:arial; font-size:12px;">
            <table width="80%" border="0" style="font-family:Arial, Helvetica, sans-serif; font-size:10px;">
                <tr>
                    <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Pos.</strong></td>
                    <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Datum</strong></td>
                    <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Betrag</strong></td>
                    <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Zahlart</strong></td>
                    <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Kunde</strong></td>
                </tr>
                {foreach from=$data item=order name=order key=id}
                    <tr>
                        <td style="border-bottom:1px solid #cccccc;">{$id+1|fill:4} </td>
                        <td style="border-bottom:1px solid #cccccc;">{$order.orderTime|date_format:"%d.%m.%Y %H:%M:%S"}</td>
                        <td style="border-bottom:1px solid #cccccc;">{$order.invoiceAmount|number_format:2:".":","} &euro;</td>
                        <td style="border-bottom:1px solid #cccccc;">{$order.payment.description}</td>
                        <td style="border-bottom:1px solid #cccccc;">{$order.customer.billing.firstName} {$order.customer.billing.lastName}</td>
                    </tr>
                {/foreach}
            </table>
        </div>
    </body>
</html>