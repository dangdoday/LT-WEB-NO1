<?php
require_once __DIR__ . '/../models/Transaction.php';

class TransactionController
{
    public function index()
    {
        $txs = Transaction::all();
        jsonResponse($txs);
    }
}
