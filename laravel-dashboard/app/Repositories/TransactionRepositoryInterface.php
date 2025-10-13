<?php
namespace App\Repositories;

interface TransactionRepositoryInterface
{
    public function create(array $data);
    public function updateStatus($id, $status);
}