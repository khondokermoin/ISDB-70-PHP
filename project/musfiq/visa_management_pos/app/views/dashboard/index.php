<?php
session_start();

// ডাটাবেস কানেকশন (dashboard থেকে ২ ধাপ পেছনে গিয়ে config ফোল্ডার)
require_once '../../config/database.php';

// ইউজার লগিন করা না থাকলে লগিন পেজে পাঠিয়ে দিবে
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// ডাটাবেস থেকে ক্যান্ডিডেটদের লিস্ট নিয়ে আসা
$stmt = $conn->prepare("SELECT * FROM candidates ORDER BY id DESC");
$stmt->execute();
$candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

// পেজের টাইটেল সেট করা
$pageTitle = "Dashboard - Visa Management POS";

// Header যুক্ত করা
require_once '../layouts/header.php';
?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/yadcf/0.9.4/jquery.dataTables.yadcf.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/yadcf/0.9.4/jquery.dataTables.yadcf.js"></script>

<style>
    /* Table Full Width Override */
    table.dataTable {
        width: 100% !important;
    }

    /* Yadcf & Select2 Customization */
    .yadcf-filter-wrapper {
        width: 100%;
        display: block;
        margin-top: 5px;
    }

    .yadcf-filter {
        width: 100% !important;
        font-size: 0.75rem !important;
        padding: 4px !important;
        border-radius: 4px;
        border: 1px solid #cbd5e1;
    }

    .yadcf-filter-reset-button {
        display: none;
    }

    .select2-container--default .select2-selection--multiple {
        border: 1px solid #cbd5e1;
        border-radius: 0.375rem;
        min-height: 28px;
        padding-bottom: 2px;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1d4ed8;
        border-radius: 4px;
        padding: 0 4px;
        margin-top: 2px;
        font-size: 0.75rem;
    }
</style>

<div class="flex justify-between items-center mb-4 w-full">
    <h2 class="text-2xl font-bold text-gray-700">Candidate List (Visa Processing)</h2>
    <button onclick="openModal()" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded shadow transition duration-200">
        + Add New Candidate
    </button>
</div>

<div class="bg-white p-4 rounded-lg shadow-lg w-full overflow-x-auto">
    <table id="candidateTable" class="w-full whitespace-nowrap text-sm text-left text-slate-600 display">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
            <tr>
                <th>Int. No</th>
                <th>Full Name</th>
                <th>Passport No</th>
                <th>Date of Birth</th>
                <th>Age</th>
                <th>PP Exp Date</th>
                <th>District</th>
                <th>Phone</th>
                <th>Trade (Job)</th>
                <th>Reference</th>
                <th>Medical Status</th>
                <th>PC Status</th>
                <th>Photo Status</th>
                <th>Apply Date</th>
                <th>Status</th>
                <th class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($candidates as $row): ?>
                <tr class="border-b hover:bg-slate-50 transition-colors">
                    <td class="py-3 px-4"><?php echo htmlspecialchars($row['interview_no'] ?? ''); ?></td>
                    <td class="py-3 px-4 font-bold text-slate-900"><?php echo htmlspecialchars($row['full_name'] ?? ''); ?></td>
                    <td class="py-3 px-4 font-mono"><?php echo htmlspecialchars($row['passport_no'] ?? ''); ?></td>
                    <td class="py-3 px-4"><?php echo htmlspecialchars($row['dob'] ?? '-'); ?></td>
                    <td class="py-3 px-4"><?php echo htmlspecialchars($row['age'] ?? '-'); ?></td>
                    <td class="py-3 px-4"><?php echo htmlspecialchars($row['pp_expire_date'] ?? '-'); ?></td>
                    <td class="py-3 px-4"><?php echo htmlspecialchars($row['district'] ?? '-'); ?></td>
                    <td class="py-3 px-4"><?php echo htmlspecialchars($row['phone'] ?? '-'); ?></td>
                    <td class="py-3 px-4"><?php echo htmlspecialchars($row['trade'] ?? '-'); ?></td>
                    <td class="py-3 px-4"><?php echo htmlspecialchars($row['reference_name'] ?? '-'); ?></td>
                    <td class="py-3 px-4"><?php echo htmlspecialchars($row['medical_status'] ?? '-'); ?></td>
                    <td class="py-3 px-4"><?php echo htmlspecialchars($row['pc_status'] ?? '-'); ?></td>
                    <td class="py-3 px-4"><?php echo htmlspecialchars($row['photo_status'] ?? '-'); ?></td>
                    <td class="py-3 px-4"><?php echo htmlspecialchars($row['apply_date'] ?? '-'); ?></td>
                    <td class="py-3 px-4">
                        <span class="bg-blue-100 text-blue-800 px-2.5 py-1 rounded-full text-xs font-semibold">
                            <?php echo htmlspecialchars($row['application_status'] ?? 'NEW'); ?>
                        </span>
                    </td>
                    <td class="py-3 px-4 flex justify-center gap-2">
                        <button onclick='editCandidate(<?php echo json_encode($row); ?>)' class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs font-bold shadow-sm transition-all">Edit</button>
                        <button onclick="deleteCandidate(<?php echo $row['id']; ?>)" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs font-bold shadow-sm transition-all">Delete</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div id="addModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-5xl p-6 h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center border-b pb-3 mb-4">
            <h3 id="modalTitle" class="text-xl font-bold text-slate-800">Add New Candidate</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-red-500 text-2xl font-bold transition-colors">&times;</button>
        </div>

        <form id="candidateForm">
            <input type="hidden" name="id" id="candidate_id">

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div><label class="block text-xs font-bold text-slate-700">Interview No</label><input type="text" name="interview_no" id="interview_no" class="mt-1 w-full border rounded p-2 text-sm outline-none focus:border-blue-500"></div>
                <div class="md:col-span-2"><label class="block text-xs font-bold text-slate-700">Full Name</label><input type="text" name="full_name" id="full_name" class="mt-1 w-full border rounded p-2 text-sm outline-none focus:border-blue-500" required></div>
                <div><label class="block text-xs font-bold text-slate-700">Passport No</label><input type="text" name="passport_no" id="passport_no" class="mt-1 w-full border rounded p-2 text-sm outline-none focus:border-blue-500" required></div>

                <div><label class="block text-xs font-bold text-slate-700">Date of Birth</label><input type="date" name="dob" id="dob" class="mt-1 w-full border rounded p-2 text-sm outline-none focus:border-blue-500"></div>
                <div><label class="block text-xs font-bold text-slate-700">Age</label><input type="number" name="age" id="age" class="mt-1 w-full border rounded p-2 text-sm outline-none focus:border-blue-500"></div>
                <div><label class="block text-xs font-bold text-slate-700">PP Expire Date</label><input type="date" name="pp_expire_date" id="pp_expire_date" class="mt-1 w-full border rounded p-2 text-sm outline-none focus:border-blue-500"></div>
                <div><label class="block text-xs font-bold text-slate-700">District</label><input type="text" name="district" id="district" class="mt-1 w-full border rounded p-2 text-sm outline-none focus:border-blue-500"></div>

                <div><label class="block text-xs font-bold text-slate-700">Phone</label><input type="text" name="phone" id="phone" class="mt-1 w-full border rounded p-2 text-sm outline-none focus:border-blue-500"></div>
                <div><label class="block text-xs font-bold text-slate-700">Trade (Job)</label><input type="text" name="trade" id="trade" class="mt-1 w-full border rounded p-2 text-sm outline-none focus:border-blue-500"></div>
                <div class="md:col-span-2"><label class="block text-xs font-bold text-slate-700">Reference Name</label><input type="text" name="reference_name" id="reference_name" class="mt-1 w-full border rounded p-2 text-sm outline-none focus:border-blue-500"></div>

                <div><label class="block text-xs font-bold text-slate-700">Medical Status</label><input type="text" name="medical_status" id="medical_status" class="mt-1 w-full border rounded p-2 text-sm outline-none focus:border-blue-500"></div>
                <div><label class="block text-xs font-bold text-slate-700">PC (Police Clearance)</label><input type="text" name="pc_status" id="pc_status" class="mt-1 w-full border rounded p-2 text-sm outline-none focus:border-blue-500"></div>
                <div><label class="block text-xs font-bold text-slate-700">Photo Status</label><input type="text" name="photo_status" id="photo_status" class="mt-1 w-full border rounded p-2 text-sm outline-none focus:border-blue-500"></div>
                <div><label class="block text-xs font-bold text-slate-700">Apply Date</label><input type="date" name="apply_date" id="apply_date" class="mt-1 w-full border rounded p-2 text-sm outline-none focus:border-blue-500"></div>

                <div class="md:col-span-4">
                    <label class="block text-xs font-bold text-slate-700">Application Status</label>
                    <select name="application_status" id="application_status" class="mt-1 w-full border rounded p-2 text-sm outline-none focus:border-blue-500 bg-white">
                        <option value="NEW">NEW</option>
                        <option value="CONFIRM">CONFIRM</option>
                        <option value="SUBMIT">SUBMIT</option>
                        <option value="RETURN">RETURN</option>
                        <option value="REFUSE TO GO">REFUSE TO GO</option>
                    </select>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3 border-t pt-4">
                <button type="button" onclick="closeModal()" class="bg-slate-400 hover:bg-slate-500 text-white py-2 px-4 rounded font-semibold transition-colors">Cancel</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded font-semibold transition-colors">Save Data</button>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        // ১. টেবিলের হেডারে ফিল্টারের জন্য একটি নতুন সারি (Row) তৈরি করা
        $('#candidateTable thead tr').clone(true).appendTo('#candidateTable thead').addClass('filter-row');

        // ২. হেডারের খালি th-গুলোতে ফিল্টারের ID বসানো
        $('#candidateTable thead tr:eq(1) th').each(function(i) {
            if (i === 15) {
                $(this).html('');
                return;
            } // Action Column

            const colMap = {
                0: "filter_int_no",
                1: "filter_name",
                2: "filter_passport",
                4: "filter_age",
                6: "filter_district",
                7: "filter_phone",
                8: "filter_trade",
                9: "filter_reference",
                10: "filter_medical",
                11: "filter_pc",
                12: "filter_photo",
                14: "filter_status"
            };

            if (colMap[i]) {
                $(this).html('<div id="' + colMap[i] + '"></div>');
            } else {
                // নরমাল টেক্সট সার্চ (যেমন: Date)
                $(this).html('<input type="text" placeholder="Search" class="w-full px-2 py-1 text-xs border rounded shadow-sm focus:outline-none focus:border-blue-500" />');

                $('input', this).on('keyup change', function() {
                    if (table.column(i).search() !== this.value) {
                        table.column(i).search(this.value).draw();
                    }
                });
            }
        });

        // ৩. DataTables ইনিশিয়ালাইজ করা (Full Width)
        var table = $('#candidateTable').DataTable({
            orderCellsTop: true,
            fixedHeader: true,
            pageLength: 20,
            autoWidth: false,
            lengthMenu: [10, 20, 50, 100, 500],
            columnDefs: [{
                orderable: false,
                targets: 15
            }] // Action সর্টিং বন্ধ
        });

        // ৪. Yadcf দিয়ে এক্সেল স্টাইল ড্রপডাউন ও সার্চ ফিল্টার সেটআপ করা
        yadcf.init(table, [{
                column_number: 0,
                filter_type: "text",
                filter_container_id: "filter_int_no",
                filter_default_label: "Search Int. No"
            },
            {
                column_number: 1,
                filter_type: "text",
                filter_container_id: "filter_name",
                filter_default_label: "Search Name"
            },
            {
                column_number: 2,
                filter_type: "text",
                filter_container_id: "filter_passport",
                filter_default_label: "Search Passport"
            },
            {
                column_number: 4,
                filter_type: "multi_select",
                select_type: 'select2',
                filter_container_id: "filter_age",
                filter_default_label: "All Ages"
            },
            {
                column_number: 6,
                filter_type: "multi_select",
                select_type: 'select2',
                filter_container_id: "filter_district",
                filter_default_label: "All Districts"
            },
            {
                column_number: 7,
                filter_type: "text",
                filter_container_id: "filter_phone",
                filter_default_label: "Search Phone"
            },
            {
                column_number: 8,
                filter_type: "multi_select",
                select_type: 'select2',
                filter_container_id: "filter_trade",
                filter_default_label: "All Trades"
            },
            {
                column_number: 9,
                filter_type: "multi_select",
                select_type: 'select2',
                filter_container_id: "filter_reference",
                filter_default_label: "All Refs"
            },
            {
                column_number: 10,
                filter_type: "multi_select",
                select_type: 'select2',
                filter_container_id: "filter_medical",
                filter_default_label: "All Meds"
            },
            {
                column_number: 11,
                filter_type: "multi_select",
                select_type: 'select2',
                filter_container_id: "filter_pc",
                filter_default_label: "All PC Status"
            },
            {
                column_number: 12,
                filter_type: "multi_select",
                select_type: 'select2',
                filter_container_id: "filter_photo",
                filter_default_label: "All Photos"
            },
            {
                column_number: 14,
                filter_type: "multi_select",
                select_type: 'select2',
                filter_container_id: "filter_status",
                filter_default_label: "All Status"
            }
        ]);
    });

    // Modal & Form Logic
    function openModal() {
        $('#modalTitle').text('Add New Candidate');
        $('#candidateForm')[0].reset();
        $('#candidate_id').val('');
        $('#addModal').removeClass('hidden');
    }

    function closeModal() {
        $('#addModal').addClass('hidden');
        $('#candidateForm')[0].reset();
    }

    function editCandidate(data) {
        $('#modalTitle').text('Edit Candidate');
        $('#candidate_id').val(data.id);
        $('#interview_no').val(data.interview_no);
        $('#full_name').val(data.full_name);
        $('#passport_no').val(data.passport_no);
        $('#dob').val(data.dob);
        $('#age').val(data.age);
        $('#pp_expire_date').val(data.pp_expire_date);
        $('#district').val(data.district);
        $('#phone').val(data.phone);
        $('#trade').val(data.trade);
        $('#reference_name').val(data.reference_name);
        $('#medical_status').val(data.medical_status);
        $('#pc_status').val(data.pc_status);
        $('#photo_status').val(data.photo_status);
        $('#apply_date').val(data.apply_date);
        $('#application_status').val(data.application_status);

        $('#addModal').removeClass('hidden');
    }

    function deleteCandidate(id) {
        if (confirm("Are you sure you want to delete this record?")) {
            $.ajax({
                url: '../../controllers/DataController.php',
                type: 'POST',
                data: {
                    action: 'delete',
                    id: id
                },
                success: function(response) {
                    alert('Deleted successfully!');
                    location.reload();
                },
                error: function() {
                    alert('Error deleting data.');
                }
            });
        }
    }

    $('#candidateForm').on('submit', function(e) {
        e.preventDefault();
        let formData = $(this).serialize() + '&action=save';

        $.ajax({
            url: '../../controllers/DataController.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                alert('Data saved successfully!');
                closeModal();
                location.reload();
            },
            error: function() {
                alert('Error processing request.');
            }
        });
    });
</script>

<?php require_once '../layouts/footer.php'; ?>