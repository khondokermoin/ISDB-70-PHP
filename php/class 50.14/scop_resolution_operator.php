<?
// use static 
class Counter {
    public static $count = 0;

    public static function increment() {
        self::$count++;
    }
}

Counter::increment();
Counter::increment();

echo Counter::$count;

// use constant
class MyClass {
    const PI = 3.1416;
}

echo MyClass::PI;


