<?php

namespace App\Http\Controllers;

use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $sorts = ['transaction_date', 'quantity', 'reference', 'id'];
        $sort = in_array($request->sort, $sorts, true) ? $request->sort : 'transaction_date';
        $dir = $request->dir === 'asc' ? 'asc' : 'desc';

        $transactions = StockTransaction::with(['product', 'user', 'supplier'])
            ->when($request->search, fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('reference', 'like', "%{$s}%")
                ->orWhere('invoice_no', 'like', "%{$s}%")
                ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$s}%")->orWhere('sku', 'like', "%{$s}%"))))
            ->when(in_array($request->type, ['in', 'out'], true), fn ($q) => $q->where('type', $request->type))
            ->when($request->date_from, fn ($q, $d) => $q->whereDate('transaction_date', '>=', $d))
            ->when($request->date_to, fn ($q, $d) => $q->whereDate('transaction_date', '<=', $d))
            ->orderBy($sort, $dir)->orderByDesc('id')
            ->paginate(15)->withQueryString();

        return view('transactions.index', compact('transactions'));
    }
}
