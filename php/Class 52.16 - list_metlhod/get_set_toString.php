<?php

class UserProfile {
    // এখানে আমরা ডাইনামিক ডাটা রাখার জন্য একটি অ্যারে ব্যবহার করছি
    private $data = [];

    // ১. __set: যখন কোনো প্রপার্টি ক্লাসে ডিফাইন করা নেই কিন্তু ডাটা সেট করা হয়
    public function __set($name, $value) {
        echo "সেটিং: '$name' এর মান দেওয়া হলো '$value'<br>";
        $this->data[$name] = $value;
    }

    // ২. __get: যখন কোনো প্রপার্টি ক্লাসে নেই কিন্তু তা পড়ার চেষ্টা করা হয়
    public function __get($name) {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        return "দুঃখিত! '$name' নামে কোনো তথ্য পাওয়া যায়নি।<br>";
    }

    // ৩. __toString: যখন সরাসরি অবজেক্টকে echo করা হয়
    public function __toString() {
        $name = $this->data['name'] ?? 'Unknown';
        $city = $this->data['city'] ?? 'Unknown';
        return "<b>প্রোফাইল সামারি:</b> নাম: $name, শহর: $city <br>";
    }
}

// অবজেক্ট তৈরি
$user = new UserProfile();

// ১. এটি __set() মেথডকে ট্রিগার করবে
$user->name = "রাহিম"; 
$user->city = "ঢাকা";
$user->age  = 25;

echo "<br>";

// ২. এটি __get() মেথডকে ট্রিগার করবে
echo "ব্যবহারকারীর নাম: " . $user->name . "<br>";
echo "ব্যবহারকারীর বয়স: " . $user->age . "<br>";
echo $user->address; // এটি নেই, তাই error মেসেজ দিবে

echo "<br>";

// ৩. এটি __toString() মেথডকে ট্রিগার করবে
echo $user; 

?>
