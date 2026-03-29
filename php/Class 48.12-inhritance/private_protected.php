<?php
class BankAccount
{
    private $balance;
    protected $accountNumber;

    public function __construct($accountNumber, $balance)
    {
        $this->accountNumber = $accountNumber;
        $this->balance = $balance;
    }

    public function deposit($amount)
    {
        $this->balance += $amount;
    }

    public function withdraw($amount)
    {
        if ($amount <= $this->balance) {
            $this->balance -= $amount;
        } else {
            echo "Insufficient balance<br>";
        }
    }

    public function getBalance()
    {
        return $this->balance;
    }
}

$acc = new BankAccount("12345", 1000);

$acc->deposit(500);
$acc->withdraw(200);

echo "Balance: " . $acc->getBalance();
