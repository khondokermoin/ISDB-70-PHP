<?php

class Database {
    public $name;

    // ১. অবজেক্ট তৈরি হলে এটি চলবে
    public function __construct($n) {
        $this->name = $n;
        echo "--- Connection Opened for: $this->name ---<br>";
    }

    public function query() {
        echo "Running a query...<br>";
    }

    // ২. অবজেক্টের কাজ শেষ হলে বা স্ক্রিপ্ট বন্ধ হলে এটি চলবে
    public function __destruct() {
        echo "--- Connection Closed for: $this->name ---<br>";
    }
}

// অবজেক্ট তৈরি (Triggers __construct)
$db = new Database("MyProject_DB");

// কোনো কাজ করা
$db->query();

// যখন কোড এখানে শেষ হবে, তখন অটোমেটিক __destruct() কল হবে।
?>
