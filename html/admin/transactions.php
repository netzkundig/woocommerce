<?php

use UnzerPayments\Controllers\AdminController;

$paymentInstructions = get_post_meta($_GET['post'], \UnzerPayments\Main::ORDER_META_KEY_PAYMENT_INSTRUCTIONS, true);

if ($paymentInstructions) {
    ?>
    <h3>Payment Instructions</h3>
    <div><?php echo $paymentInstructions; ?></div>
    <?php
}
?>
<h3>Totals</h3>
<table id="unzer-sums">
    <tbody id="unzer-sums-body">

    </tbody>
</table>
<h3>Detailed Transactions</h3>
<table id="unzer-transactions" style="width: 100%;">
    <thead style="text-align: left;">
    <tr>
        <th>Time</th>
        <th>Type</th>
        <th>Amount</th>
        <th>Status</th>
    </tr>
    </thead>
    <tbody id="unzer-transactions-body">

    </tbody>
</table>
<div style="margin-top:20px;">
    <a href="#" onclick="document.getElementById('unzer-debug').style.display = 'block'; return false;" class="button">
        Show Debug Information
    </a>
    <pre id="unzer-debug" style="display: none; font-size:10px;">

    </pre>
</div>
<?php
$ajaxUrl = WC()->api_request_url(AdminController::GET_ORDER_TRANSACTIONS_ROUTE_SLUG);
$ajaxUrl .= (strpos($ajaxUrl, '?') === false ? '?' : '&') . 'order_id=' . $_GET['post'];

$chargeUrl = WC()->api_request_url(AdminController::CHARGE_ROUTE_SLUG);
?>
<script>
    const unzerOrderId = <?php echo (int)$_GET['post']; ?>;

    function unzerRefreshData() {
        fetch('<?php echo $ajaxUrl;?>')
            .then(response => response.json())
            .then(data => {

                if (data.transactions) {
                    let tHtml = '';
                    for (const transaction of data.transactions) {
                        let color = transaction.status === 'error' ? '#cc0000' : '#000000';
                        color = transaction.status === 'pending' ? '#bbb' : color;
                        tHtml += `
                    <tr style="color:${color};">
                        <td>${transaction.time}</td>
                        <td>${transaction.type}</td>
                        <td>${transaction.amount}</td>
                        <td>${transaction.status}</td>
                    </tr>
                `;
                    }
                    document.getElementById('unzer-transactions-body').innerHTML = tHtml;
                }

                let captureAction = '';
                if (data.remainingPlain) {
                    captureAction = '<div><input type="number" step="0.01" min="0.01"  max="'+data.remainingPlain+'" value="'+data.remainingPlain+'" id="unzer-capture-amount-input" /></div> ' +
                        '<a href="#" onclick="unzerCaptureOrder(unzerOrderId, document.getElementById(\'unzer-capture-amount-input\').value); return false;" class="button button-small" style="width:100%; text-align: center;">Capture Amount</a>'

                }

                let amountHtml = `
                <tr><th style="text-align: left;">Total amount: </th><td style="text-align: right;">${data.amount}</td></tr>
                <tr><th style="text-align: left;">Charged amount: </th><td style="text-align: right;">${data.charged}</td></tr>
                <tr><th style="text-align: left;">Cancelled amount: </th><td style="text-align: right;">${data.cancelled}</td></tr>
                <tr><th style="text-align: left;">Remaining amount: </th><td style="text-align: right;">${data.remaining}</td></tr>
                <tr><td colspan="2">${captureAction}</td></tr>
            `;

                document.getElementById('unzer-sums-body').innerHTML = amountHtml;
                if (data.raw) {
                    document.getElementById('unzer-debug').innerHTML = data.raw;
                }
                if (data.error) {
                    document.getElementById('unzer-transactions').parentNode.innerHTML = '<b>ERROR:</b> ' + data.error;
                }
                console.log(data);
            });
    }

    unzerRefreshData();

    function unzerCaptureOrder(orderId, amount) {
        const formData = new FormData();
        formData.append('order_id', orderId);
        formData.append('amount', amount);
        fetch('<?php echo $chargeUrl;?>', {
            method: 'POST',
            body: formData
        }).then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                }
                unzerRefreshData();
            })

    }
</script>