<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Contact;
use App\Services\Xero\XeroPurchaseBuilder;
use App\Services\Xero\XeroSaleBuilder;
use App\DataTransferObjects\Xero\CreatePurchasePayload;

use Webfox\Xero\OauthCredentialManager;
use XeroAPI\XeroPHP\Api\AccountingApi;

use XeroAPI\XeroPHP\Models\Accounting\Invoice;
use XeroAPI\XeroPHP\Models\Accounting\Invoices;
use XeroAPI\XeroPHP\Models\Accounting\LineItem;
use XeroAPI\XeroPHP\Models\Accounting\Contact as XeroContact;

use Carbon\Carbon;

class XeroService
{
    protected AccountingApi $accountingApi;
    protected OauthCredentialManager $oauth;

    public function __construct(AccountingApi $accountingApi, OauthCredentialManager $oauth)
    {
        $this->accountingApi = $accountingApi;
        $this->oauth = $oauth;
    }

    public function getTenantId(): string
    {
        return $this->oauth->getTenantId();
    }

    /**
     * MAIN: Create Purchase in Xero
     */
    public function createPurchase(Purchase $purchase)
    {
        $purchase->load(['products.prices', 'contact']);

        $skuReference = $purchase->products
            ->pluck('sku')        // get all SKUs
            ->filter()            // remove nulls just in case
            ->unique()            // avoid duplicates
            ->implode(', ');      // "SKU1, SKU2"

        // 🔥 Build line items
        $builder = app(XeroPurchaseBuilder::class);
        $lineItems = $builder->buildLineItems($purchase);

        // dd($purchase->contact);

        // 🔥 Ensure contact exists in Xero
        $contact = $this->updateOrCreateXeroContact($purchase->contact);


        $xeroContact = new XeroContact();
        $xeroContact->setContactId($contact->getContactId());

        // 🔥 Create invoice (bill)
        $invoice = new Invoice();


        $invoice
            ->setType('ACCPAY')
            ->setContact($xeroContact)
            ->setLineItems($lineItems)
            ->setStatus('DRAFT')
            ->setLineAmountTypes('NoTax')
            ->setInvoiceNumber($purchase->id . ' - ' . $skuReference);



        if ($purchase->purchase_date) {
            $dueDate = Carbon::parse($purchase->purchase_date)->addDays(14);
            $invoice->setDate($purchase->purchase_date);
            $invoice->setDueDate($dueDate);
        }

        $invoices = new Invoices();
        $invoices->setInvoices([$invoice]);

        $result = $this->accountingApi->createInvoices(
            $this->getTenantId(),
            $invoices
        );

        if ($result->getInvoices()[0]->getHasErrors()) {
            throw new \Exception(
                $result->getInvoices()[0]->getValidationErrors()[0]->getMessage()
            );
        }

        return $result->getInvoices();
    }

    /**
     * Create or fetch Xero contact
     */
    public function updateOrCreateXeroContact(Contact $contact)
    {

   
        // If already linked → return
        if ($contact->hasValidXeroId()) {
             
            $xeroContact = new XeroContact();
            $xeroContact->setContactId($contact->xero_id);
            return $xeroContact;
        }

        $xeroContact = new XeroContact();

        $xeroContact
            ->setName($contact->name ?? ($contact->first_name . ' ' . $contact->last_name))
            ->setFirstName($contact->first_name)
            ->setLastName($contact->last_name);

        if ($contact->email) {
            $xeroContact->setEmailAddress($contact->email);
        }

        

        $addresses[0] = new \XeroAPI\XeroPHP\Models\Accounting\Address();
        $addresses[0]->setAddressType('POBOX')
            ->setAddressLine1($contact->invoice_address_1 ?? '')
            ->setAddressLine2($contact->invoice_address_2 ?? '')
            ->setCity($contact->invoice_town_city ?? '')
            ->setCountry($contact->invoice_country ?? 'GB')
            ->setPostalCode($contact->invoice_postcode ?? '');

        // $addresses[1] = new \XeroAPI\XeroPHP\Models\Accounting\Address();
        // $addresses[1]->setAddressType('STREET')
        //     ->setAddressLine1($contact->deliver_address ?? '')
        //     ->setAddressLine2($contact->deliver_address_extra ?? '')
        //     ->setCity($contact->deliver_town ?? '')
        //     ->setRegion($contact->deliver_state ?? '')
        //     ->setCountry($contact->deliver_country ?? '')
        //     ->setPostalCode($contact->deliver_postcode ?? '');

        $xeroContact->setAddresses($addresses);

        $response = $this->accountingApi->createContacts(
            $this->getTenantId(),
            (new \XeroAPI\XeroPHP\Models\Accounting\Contacts())->setContacts([$xeroContact])
        );

        $created = $response->getContacts()[0];

        // Save Xero ID locally
        $contact->xero_id = $created->getContactId();
        $contact->save();

        return $created;
    }

    public function createSaleInvoice(Sale $sale)
    {
        $builder = app(XeroSaleBuilder::class);

        $lineItems = $builder->buildLineItems($sale);

        $contact = $this->updateOrCreateXeroContact($sale->contact);

        $invoice = new Invoice();

        $dueDate = Carbon::parse($sale->invoice_date)->addDays(14);

        $invoice
            ->setType(Invoice::TYPE_ACCREC)
            ->setContact($contact)
            ->setLineItems($lineItems)
            ->setDate($sale->invoice_date)
            ->setDueDate($dueDate)
            ->setReference($sale->reference ?? '')
            ->setStatus(Invoice::STATUS_AUTHORISED);

        $invoices = new Invoices();
        $invoices->setInvoices([$invoice]);

        $response = $this->accountingApi->createInvoices(
            $this->getTenantId(),
            $invoices
        );

        return $response->getInvoices()[0];
    }
}