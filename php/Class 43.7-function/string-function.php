<?php
// 1. quotemeta() - Quote regex meta characters
$str = "Hello. (PHP)";
echo "quotemeta: " . quotemeta($str) . "<br>";

// 2. rtrim() - Strip whitespace from the end
$str = "Hello World   ";
echo "rtrim: '" . rtrim($str) . "'<br>";

// 3. setlocale() - Set locale information
setlocale(LC_ALL, "en_US");
echo "setlocale: " . setlocale(LC_ALL, 0) . "<br>";

// 4. sha1() - Calculate SHA1 hash
echo "sha1: " . sha1("hello") . "<br>";

// 5. sha1_file() - SHA1 hash of a file (make sure test.txt exists)
if (file_exists("test.txt")) {
    echo "sha1_file: " . sha1_file("test.txt") . "<br>";
} else {
    echo "sha1_file: test.txt not found<br>";
}

// 6. similar_text() - Calculate similarity
similar_text("hello", "hallo", $percent);
echo "similar_text: $percent%<br>";

// 7. soundex() - Calculate soundex key
echo "soundex: " . soundex("Robert") . "<br>";

// 8. sprintf() - Return formatted string
$name = "Moin";
echo "sprintf: " . sprintf("Hello %s!", $name) . "<br>";

// 9. sscanf() - Parse input from string
$str = "10 20";
sscanf($str, "%d %d", $a, $b);
echo "sscanf: " . ($a + $b) . "<br>";

// 10. str_contains() - Check if substring exists
echo "str_contains: " . (str_contains("Hello World", "World") ? "true" : "false") . "<br>";

// 11. str_ends_with() - Check ending substring
echo "str_ends_with: " . (str_ends_with("Hello.php", ".php") ? "true" : "false") . "<br>";

// 12. str_getcsv() - Parse CSV string into array
$csv = "apple,banana,orange";
print_r(str_getcsv($csv));
echo "<br>";

// 13. str_ireplace() - Case-insensitive replace
echo "str_ireplace: " . str_ireplace("hello", "hi", "Hello World") . "<br>";

// 14. str_pad() - Pad string to certain length
echo "str_pad: '" . str_pad("PHP", 10, "*") . "'<br>";

// 15. str_repeat() - Repeat string
echo "str_repeat: " . str_repeat("Hi ", 3) . "<br>";

// 16. str_replace() - Replace string
echo "str_replace: " . str_replace("World", "PHP", "Hello World") . "<br>";

// 17. str_rot13() - ROT13 transform
echo "str_rot13: " . str_rot13("hello") . "<br>";

// 18. str_shuffle() - Shuffle string
echo "str_shuffle: " . str_shuffle("abcdef") . "<br>";

// 19. str_split() - Convert string to array
print_r(str_split("Hello"));
echo "<br>";

// 20. str_starts_with() - Check starting substring
echo "str_starts_with: " . (str_starts_with("Hello World", "Hello") ? "true" : "false") . "<br>";

// 21. str_word_count() - Count words
echo "str_word_count: " . str_word_count("Hello world from PHP") . "<br>";

// 22. strcasecmp() - Case-insensitive string comparison
echo "strcasecmp: " . strcasecmp("HELLO", "hello") . "<br>";

// 23. strcmp() - Binary-safe string comparison
echo "strcmp: " . strcmp("abc", "abc") . "<br>";

// 24. strip_tags() - Remove HTML/PHP tags
echo "strip_tags: " . strip_tags("<h1>Hello</h1>") . "<br>";

// 25. stripos() - Find position (case-insensitive)
echo "stripos: " . stripos("Hello World", "world") . "<br>";

// 26. strlen() - String length
echo "strlen: " . strlen("Hello") . "<br>";

// 27. strpos() - Position of first occurrence
echo "strpos: " . strpos("Hello World", "World") . "<br>";

// 28. strrev() - Reverse string
echo "strrev: " . strrev("Hello") . "<br>";

// 29. strtolower() - Lowercase
echo "strtolower: " . strtolower("HELLO") . "<br>";

// 30. strtoupper() - Uppercase
echo "strtoupper: " . strtoupper("hello") . "<br>";

// 31. substr() - Part of string
echo "substr: " . substr("Hello World", 0, 5) . "<br>";

// 32. substr_count() - Count substring occurrences
echo "substr_count: " . substr_count("Hello Hello", "Hello") . "<br>";

// 33. trim() - Remove whitespace from both ends
$str = "  Hello  ";
echo "trim: '" . trim($str) . "'<br>";
// 34. ucfirst() - Uppercase first character of a string
$str = "hello world";
echo "ucfirst: " . ucfirst($str) . "<br>";
// Output: "Hello world"

// 35. strtolower() - Convert string to lowercase
$str = "HELLO WORLD";
echo "strtolower: " . strtolower($str) . "<br>";
// Output: "hello world"

// 36. str_replace() - Replace all occurrences of a search string
$str = "I love PHP";
echo "str_replace: " . str_replace("PHP", "Python", $str) . "<br>";
// Output: "I love Python"

// 37. ucwords() - Uppercase the first character of each word
$str = "hello world from php";
echo "ucwords: " . ucwords($str) . "<br>";
// Output: "Hello World From Php"

// 38. str_word_count() - Count number of words in a string
$str = "Hello world from PHP";
echo "str_word_count: " . str_word_count($str) . "<br>";
// Output: 4

// 39. strrev() - Reverse a string
$str = "Hello PHP";
echo "strrev: " . strrev($str) . "<br>";
// Output: "PHP olleH"