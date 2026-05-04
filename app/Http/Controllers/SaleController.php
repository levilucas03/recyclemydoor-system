<?php 

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Enums\SaleStatus;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with('contact')
            ->latest()
            ->paginate(20);

        return Inertia::render('Sales/Index', [
            'sales' => $sales
        ]);
    }

    public function create()
    {

        return Inertia::render('Sales/Create', [
            'statusOptions' => SaleStatus::options(),
        ]);

    }

    public function store(Request $request)
    {
        $request->validate([
            'contact' => 'nullable|array',
            'contact_id' => 'nullable|exists:contacts,id',
            'contact.first_name' => 'required_without:contact_id|string',
            'contact.last_name' => 'nullable|string',
            'contact.email' => 'nullable|email',
            'contact.mobile' => 'nullable|string',
            'contact.type' => 'required_without:contact_id|in:general_public,supplier,company',

            'status' => 'string',
            
            'items' => 'required|array|min:1',
            'items.*.title' => 'required|string',
        ]);


        $contactData = $request['contact'] ?? [];

        $contactData['address_1'] = $request['address_1'] ?? null;
        $contactData['address_2'] = $request['address_2'] ?? null;
        $contactData['town_city'] = $request['town_city'] ?? null;
        $contactData['postcode'] = $request['postcode'] ?? null;
        $contactData['invoice_address_1'] = $request['address_1'] ?? null;
        $contactData['invoice_address_2'] = $request['address_2'] ?? null;
        $contactData['invoice_town_city'] = $request['town_city'] ?? null;
        $contactData['invoice_postcode'] = $request['postcode'] ?? null;

        // Contact logic
        if (!empty($request['contact_id'])) {
            $contact = Contact::findOrFail($request['contact_id']);

        } else {
            $contact = Contact::create($contactData);
            $request['contact_id'] = $contact->id;
        }


        DB::transaction(function () use ($request) {

            $sale = Sale::create([
                'contact_id' => $request['contact_id'],
                'user_id' => auth()->id(),
                'status' => $request->status,
                'invoice_date' => $request->invoice_date,
                'notes' => $request->notes,
                'source' => $request->source,
                'total_amount' => 0,
                'total_vat_amount' => 0,
                'deliver_address_1' => $request['address_1'] ?? null,
                'deliver_address_2' => $request['address_2'] ?? null,
                'deliver_town_city' => $request['town_city'] ?? null,
                'deliver_postcode' => $request['postcode'] ?? null,
            ]);

            $total = 0;
            $vatTotal = 0;

            foreach ($request->items as $item) {

                $lineTotal = ($item['price'] * $item['qty']) - ($item['discount'] ?? 0);
                $vat = $item['vat_amount'] ?? 0;

                $sale->items()->create([
                    'type' => $item['type'] ?? 'product',
                    'product_id' => $item['product_id'] ?? null,
                    'title' => $item['title'],
                    'description' => $item['description'] ?? null,
                    'price' => $item['price'],
                    'qty' => $item['qty'],
                    'discount' => $item['discount'] ?? 0,
                    'vat_amount' => $vat,
                    'total' => $lineTotal,
                    'account_code' => $item['account_code'] ?? null,
                    'note' => $item['note'] ?? null,
                ]);

                $total += $lineTotal;
                $vatTotal += $vat;
            }

            $sale->update([
                'total_amount' => $total,
                'total_vat_amount' => $vatTotal,
            ]);
        });

        return redirect()->route('sales.index')
            ->with('success', 'Sale created successfully');
    }

    public function show(Sale $sale)
    {
        $sale->load('items', 'contact');

        return Inertia::render('Sales/Show', [
            'sale' => $sale
        ]);
    }

    public function edit(Sale $sale)
    {
        $sale->load([
            'contact',
            'items'
        ]);

        return Inertia::render('Sales/Edit', [
            'sale' => $sale,
            'statusOptions' => SaleStatus::options(),
        ]);
    }

    public function update(Request $request, Sale $sale)
    {

        $request->validate([
            'contact' => 'nullable|array',
            'contact_id' => 'nullable|exists:contacts,id',
            'contact.first_name' => 'required_without:contact_id|string',
            'contact.last_name' => 'nullable|string',
            'contact.email' => 'nullable|email',
            'contact.mobile' => 'nullable|string',
            'contact.type' => 'required_without:contact_id|in:general_public,supplier,company',

            'status' => 'string',
            
            'items' => 'required|array|min:1',
            'items.*.title' => 'required|string',
        ]);

        DB::transaction(function () use ($request, $sale) {

            // --------------------
            // CONTACT LOGIC
            // --------------------
            $contactId = $request->contact_id;

            if (!$contactId) {

                $contactData = $request->contact ?? [];

                $contactData = array_merge($contactData, [
                    'address_1' => $request->address_1,
                    'address_2' => $request->address_2,
                    'town_city' => $request->town_city,
                    'postcode' => $request->postcode,

                    'invoice_address_1' => $request->address_1,
                    'invoice_address_2' => $request->address_2,
                    'invoice_town_city' => $request->town_city,
                    'invoice_postcode' => $request->postcode,
                ]);

                $contact = Contact::create($contactData);
                $contactId = $contact->id;
            }

            // --------------------
            // UPDATE SALE HEADER
            // --------------------
            $sale->update([
                'contact_id' => $contactId,

                'invoice_date' => $request->invoice_date,
                'notes' => $request->notes,

                'status' => $request->status,

                'deliver_address_1' => $request->address_1,
                'deliver_address_2' => $request->address_2,
                'deliver_town_city' => $request->town_city,
                'deliver_postcode' => $request->postcode,
            ]);

            // --------------------
            // RESET ITEMS
            // --------------------
            $sale->items()->delete();

            $total = 0;
            $vatTotal = 0;

            // --------------------
            // RE-CREATE ITEMS
            // --------------------
            foreach ($request->items as $item) {

                $lineTotal = ($item['price'] * $item['qty']) - ($item['discount'] ?? 0);
                $vat = $item['vat_amount'] ?? 0;

                $sale->items()->create([
                    'type' => $item['type'] ?? 'product',
                    'product_id' => $item['product_id'] ?? null,

                    'title' => $item['title'],
                    'description' => $item['description'] ?? null,

                    'image' => $item['image'] ?? null,
                    'size' => $item['size'] ?? null,

                    'price' => $item['price'],
                    'qty' => $item['qty'],
                    'discount' => $item['discount'] ?? 0,
                    'vat_amount' => $vat,
                    'total' => $lineTotal,

                    'account_code' => $item['account_code'] ?? null,
                    'note' => $item['note'] ?? null,
                ]);

                $total += $lineTotal;
                $vatTotal += $vat;
            }

            // --------------------
            // UPDATE TOTALS
            // --------------------
            $sale->update([
                'total_amount' => $total,
                'total_vat_amount' => $vatTotal,
            ]);
        });

        return redirect()
            ->route('sales.index')
            ->with('success', 'Sale updated successfully');
    }

    public function destroy(Sale $sale)
    {
        $sale->delete();

        return back()->with('success', 'Sale deleted');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:sales,id',
        ]);

        Sale::whereIn('id', $request->ids)->delete();

        return back()->with('success', 'Selected sales deleted');
    }

}