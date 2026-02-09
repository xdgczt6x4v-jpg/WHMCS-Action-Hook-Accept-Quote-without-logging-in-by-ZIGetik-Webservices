<?php
/**
 * WHMCS Action Hook: Accept Quote without logging in by ZIGetik Webservices
 *
 * @package     WHMCS
 * @copyright   ZIGetik Webservices
 * @link        https://zigetik.com
 * @author      ZIGetik Webservices, Email <kontakt@zigetik.com>
 * @version     2.0
 * @compatible  WHMCS 9.0+ | PHP 8.2+
 *
 * Original concept by Katamaze
 * Enhanced and secured by ZIGetik Webservices
 */

use WHMCS\Database\Capsule;

add_hook('EmailPreSend', 1, function($vars) {
    if (!in_array($vars['messagename'], ['Quote Delivery with PDF'])) {
        return;
    }
    
    if (empty($vars['mergefields']['quote_link']) || empty($vars['mergefields']['quote_number'])) {
        return;
    }
    
    $data = Capsule::table('tblquotes as t1')
        ->leftJoin('tblclients as t2', 't1.userid', '=', 't2.id')
        ->where('t1.id', $vars['mergefields']['quote_number'])
        ->select('t1.id', 't2.id as clientid', 't2.email')
        ->first();
    
    if (!$data) {
        return;
    }
    
    $hash = strrev(md5($data->id . $data->clientid . $data->email)) . '-' . $data->id;
    $quote_link = (new SimpleXMLElement($vars['mergefields']['quote_link']))['href'];
    $url = parse_url($quote_link);
    
    $merge_fields['quote_link'] = str_replace(
        $quote_link, 
        $url['scheme'] . '://' . $url['host'] . '/index.php?qhash=' . $hash, 
        $vars['mergefields']['quote_link']
    );
    
    return $merge_fields;
});

add_hook('ClientAreaHeadOutput', 1, function($vars) {
    if (empty($_GET['qhash'])) {
        return;
    }
    
    $qhash = filter_var($_GET['qhash'], FILTER_SANITIZE_STRING);
    $parts = explode('-', $qhash);
    
    if (count($parts) !== 2 || !is_numeric($parts[1])) {
        return;
    }
    
    $data = Capsule::table('tblquotes as t1')
        ->leftJoin('tblclients as t2', 't1.userid', '=', 't2.id')
        ->where('t1.id', $parts[1])
        ->where('t1.stage', '!=', 'Accepted')
        ->select('t1.id', 't1.subject', 't2.id as clientid', 't2.firstname', 't2.email')
        ->first();
    
    if (!$data) {
        return;
    }
    
    $hash = strrev(md5($data->id . $data->clientid . $data->email)) . '-' . $data->id;
    
    if ($hash !== $qhash) {
        return;
    }
    
    try {
        $results = localAPI('AcceptQuote', ['quoteid' => $data->id]);
        
        if (!empty($results['invoiceid'])) {
            localAPI('SendEmail', [
                'messagename' => 'Invoice Created', 
                'id' => $results['invoiceid']
            ]);
        }
    } catch (Exception $e) {
        logActivity('Quote Accept Error (ZIGetik Hook): ' . $e->getMessage());
        return;
    }
    
    $quoteId = (int)$data->id;
    $firstname = htmlspecialchars($data->firstname);
    $subject = htmlspecialchars($data->subject);
    
    return <<<HTML
<script>
setTimeout(function() {
    const modal = document.getElementById('modalAjax');
    if (modal) {
        const titleEl = modal.querySelector('.modal-title');
        const bodyEl = modal.querySelector('.modal-body');
        const loaderEl = modal.querySelector('.loader');
        const submitBtn = modal.querySelector('.modal-submit');
        
        if (titleEl) titleEl.innerHTML = 'Angebot #{$quoteId} akzeptiert';
        if (bodyEl) {
            bodyEl.innerHTML = '<div class="container"><div class="row"><div class="col-md-8"><h4>Hallo {$firstname},</h4><p>vielen Dank für die Annahme des Angebots <strong>#{$quoteId}</strong> ({$subject}). So geht es weiter:<ul><li>Sie erhalten in Kürze die Rechnung per E-Mail</li><li>Nach Zahlungseingang aktivieren wir Ihre Bestellung umgehend</li></ul>Bei Fragen stehen wir Ihnen gerne zur Verfügung: <a href="contact.php"><strong>Kontakt aufnehmen</strong></a></p></div><div class="col-md-4 text-center"><p><a href="cart.php"><i class="fas fa-cart-plus fa-5x"></i></a></p><p><small>Weitere Produkte entdecken</small></p><p><a href="cart.php" class="btn btn-info btn-block">Zum Shop</a></p></div></div></div>';
        }
        if (loaderEl) loaderEl.style.display = 'none';
        if (submitBtn) submitBtn.style.display = 'none';
        
        // Bootstrap 5 kompatibel
        if (typeof bootstrap !== 'undefined') {
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        } else {
            // Fallback für jQuery/Bootstrap 4
            $(modal).modal('show');
        }
    }
}, 250);
</script>
HTML;
});
