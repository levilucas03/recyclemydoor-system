<?php 

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use App\Models\Contact;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\ProductPrice;
use Illuminate\Support\Str;


class PurchaseDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Create 5 contacts
        $contacts = Contact::factory()->count(5)->create();

        foreach ($contacts as $contact) {

            // Create 2 purchases per contact
            for ($i = 0; $i < 2; $i++) {

                $purchase = Purchase::create([
                    'contact_id' => $contact->id,
                    'user_id' => 1,
                    'status_id' => 'draft',
                    'purchase_date' => now(),

                    'notes' => 'Seeded purchase',
                    'total_amount' => 0,

                    // Copy address from contact
                    'collection_address_1' => $contact->address,
                    'collection_address_2' => $contact->address_extra,
                    'collection_town_city' => $contact->town_city,
                    'collection_postal_code' => $contact->postcode,
                ]);

                // Create 2–4 products per purchase
                $productCount = rand(2, 4);
                $total = 0;

                for ($p = 0; $p < $productCount; $p++) {

                $purchasePrice = rand(50, 200);

                // Create product (NO price here now)
                $product = Product::create([
                    'purchase_id' => $purchase->id,
                    'title' => 'Door ' . Str::upper(Str::random(4)),
                    'user_id' => 1,
                ]);


                // Add prices
                ProductPrice::insert([
                    [
                        'product_id' => $product->id,
                        'type' => 'purchase',
                        'price' => $purchasePrice,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'product_id' => $product->id,
                        'type' => 'website',
                        'price' => $purchasePrice * 2,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'product_id' => $product->id,
                        'type' => 'ebay',
                        'price' => $purchasePrice * 2.2,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]);

                $total += $purchasePrice;
            }


                // Update total
            $purchase->update([
                'total_amount' => $total
            ]);
            }
        }
    }
}