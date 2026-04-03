// Your App Class (src/App/User.php)
<?php
namespace App;

class User {
    public function sayHi() {
        echo "Hi from App User!<br>";
    }
}


// Third-Party Library Class (vendor/Stripe/User.php)
<?php
namespace Stripe;

class User {
    public function getBalance() {
        echo "Fetching balance from Stripe...<br>";
    }
}


//  Running them together (index.php)
<?php
require 'src/App/User.php';
require 'vendor/Stripe/User.php';

// Option 1: Using the Full Path
$myUser = new \App\User(); 
$myUser->sayHi();

// Option 2: Using the 'use' keyword (Best Practice)
use Stripe\User as StripeUser; // We give it an Alias to avoid confusion

$paymentUser = new StripeUser();
$paymentUser->getBalance();
