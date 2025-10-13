<?php
namespace App\Repositories;

use App\Models\Transaction;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function create(array $data)
    {
        return Transaction::create($data);
    }

    public function updateStatus($id, $status)
    {
        $tx = Transaction::findOrFail($id);
        $tx->status = $status;
        $tx->save();
        return $tx;
    }
}