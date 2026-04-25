<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ContactController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q');

        return Contact::where('first_name', 'like', "%{$query}%")
            ->orWhere('last_name', 'like', "%{$query}%")
            ->limit(10)
            ->get([
                'id',
                'first_name',
                'last_name',
                'email',
                'address_1',
                'address_2',
                'country',
                'town_city',
                'postcode'
            ]);
    }

}
