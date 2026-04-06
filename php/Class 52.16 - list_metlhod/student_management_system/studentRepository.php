
<?php
// student.php file include করা হচ্ছে, যাতে Student class ব্যবহার করা যায়
require_once __DIR__ . '/student.php';

// StudentRepository class → student data file এ save ও read করার কাজ করবে
class StudentRepository
{
    // file path store করার জন্য private property
    private string $file;

    // Constructor → object তৈরি হলে run হবে
    // optional file name নেওয়া যায়
    public function __construct(string $file = null)
    {
        // যদি constructor এ file path দেওয়া হয় → সেটি use হবে
        // না দিলে → current folder এর data.txt file use হবে
        // ?? → null coalescing operator
        $this->file = $file ?? (__DIR__ . '/data.txt');
    }

    // save() method → Student object file এ save করবে
    // Student $student → parameter হিসেবে Student object নিতে হবে
    // : void → এই function কিছু return করবে না
    public function save(Student $student): void
    {
        // file_put_contents() → file এ data write করে
        // $this->file → কোন file এ write করবে
        // $student->toCSV() → Student object কে CSV string বানায়
        // FILE_APPEND → file এর শেষে data add করবে
        // LOCK_EX → file lock করে write করবে (data corruption prevent)
        file_put_contents($this->file, $student->toCSV(), FILE_APPEND | LOCK_EX);
    }

    // getAll() method → file থেকে সব student data read করবে
    // : array → এই function array return করবে
    public function getAll(): array
    {
        // empty array তৈরি করা হচ্ছে
        // এখানে Student object গুলো রাখা হবে
        $students = [];

        // যদি file না থাকে → empty array return করবে
        if (!file_exists($this->file)) {
            return $students;
        }

        // file() → file line by line array আকারে পড়ে
        // FILE_IGNORE_NEW_LINES → newline remove করবে
        // FILE_SKIP_EMPTY_LINES → empty line skip করবে
        $lines = file($this->file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // প্রতিটি line loop করা হচ্ছে
        foreach ($lines as $line) {

            // CSV line থেকে Student object তৈরি করা হচ্ছে
            $student = Student::fromCSV($line);

            // যদি object valid হয় (null না হয়)
            if ($student !== null) {

                // students array তে object add করা হচ্ছে
                $students[] = $student;
            }
        }

        // সব Student object এর array return করা হচ্ছে
        return $students;
    }
}