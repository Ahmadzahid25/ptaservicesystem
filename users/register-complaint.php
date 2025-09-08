
<?php
session_start();
error_reporting(E_ALL);
include('includes/config.php');

$successmsg = '';
$errormsg = '';

if (strlen($_SESSION['login']) == 0) { 
    header('location:index.php');
    exit();
}

if (isset($_POST['submit'])) {
    $uid = $_SESSION['id'];
    $category = mysqli_real_escape_string($con, $_POST['category']);
    $subcat = mysqli_real_escape_string($con, $_POST['subcategory']);
    $complaintype = mysqli_real_escape_string($con, $_POST['complaintype']);
    $state = mysqli_real_escape_string($con, $_POST['state']);
    $brandId = mysqli_real_escape_string($con, $_POST['brandname']); // ini ID brand
    $modelNo = mysqli_real_escape_string($con, $_POST['modelNo']);
    $complaintdetails = mysqli_real_escape_string($con, $_POST['complaindetails']);
    $warranty_status = $_POST['complaintype'];

    $warrantyfile = !empty($_FILES["warrantyfile"]["name"]) ? $_FILES["warrantyfile"]["name"] : null;
    $receiptfile = !empty($_FILES["receiptfile"]["name"]) ? $_FILES["receiptfile"]["name"] : null;

    $warrantyfile_dir = "warrantydocs/";
    $receiptfile_dir = "receiptdocs/";

    if (!file_exists($warrantyfile_dir)) {
        mkdir($warrantyfile_dir, 0755, true);
    }
    if (!file_exists($receiptfile_dir)) {
        mkdir($receiptfile_dir, 0755, true);
    }

    $can_proceed = false;

    // Ambil nama brand dari ID brand
    $sql = "SELECT brandname FROM brandname WHERE id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $brandId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $errormsg = "❌ Invalid brand selected.";
        $can_proceed = false;
    } else {
        $row = $result->fetch_assoc();
        $brand_name = $row['brandname']; // nama brand yang akan disimpan
        $can_proceed = true;
    }
    $stmt->close();

    // Validate state
    $sql = "SELECT stateName FROM state WHERE stateName = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $state);
    $stmt->execute();
    if ($stmt->get_result()->num_rows == 0) {
        $errormsg = "❌ Invalid state selected.";
        $can_proceed = false;
    }
    $stmt->close();

    if ($can_proceed) {
        if ($warranty_status == "Over Warranty") {
            $can_proceed = true;
            $warrantyfile = null;
            $receiptfile = null;
        } else {
            $warrantyfile_uploaded = false;
            $receiptfile_uploaded = false;
            $maxFileSize = 5 * 1024 * 1024; // 5MB
            $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];

            if (!empty($warrantyfile) && $_FILES["warrantyfile"]["error"] == 0) {
                if ($_FILES["warrantyfile"]["size"] > $maxFileSize || !in_array($_FILES["warrantyfile"]["type"], $allowedTypes)) {
                    $errormsg = "❌ Invalid warranty file. Only JPEG, PNG, or PDF allowed (max 5MB).";
                    $can_proceed = false;
                } else {
                    $warrantyfile_uploaded = move_uploaded_file($_FILES["warrantyfile"]["tmp_name"], $warrantyfile_dir . basename($warrantyfile));
                }
            }
            if (!empty($receiptfile) && $_FILES["receiptfile"]["error"] == 0) {
                if ($_FILES["receiptfile"]["size"] > $maxFileSize || !in_array($_FILES["receiptfile"]["type"], $allowedTypes)) {
                    $errormsg = "❌ Invalid receipt file. Only JPEG, PNG, or PDF allowed (max 5MB).";
                    $can_proceed = false;
                } else {
                    $receiptfile_uploaded = move_uploaded_file($_FILES["receiptfile"]["tmp_name"], $receiptfile_dir . basename($receiptfile));
                }
            }

            if (!$warrantyfile_uploaded || !$receiptfile_uploaded) {
                $errormsg = "❌ Please upload both purchase receipt and warranty document for Under Warranty option.";
                $can_proceed = false;
            }
        }

        if ($can_proceed) {
            $sql = "INSERT INTO tblcomplaints (userId, category, subcategory, complaintType, state, brandname, modelNo, complaintDetails, warrantyFile, receiptFile) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("iissssssss", $uid, $category, $subcat, $complaintype, $state, $brand_name, $modelNo, $complaintdetails, $warrantyfile, $receiptfile);
            if ($stmt->execute()) {
                $complainno = $con->insert_id;
                $successmsg = "✅ Your complaint has been successfully submitted. Your complaint number is: $complainno, Brand: $brand_name";
            } else {
                $errormsg = "❌ Error occurred while saving the complaint to the system: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PTA Customer Complaint System">
    <meta name="author" content="Dashboard">
    <meta name="keyword" content="Complaint, Customer, PTA">

    <title>PTA | Customer Report Form</title>

    <link rel="icon" href="logopta.png" type="image/png">

    <!-- Bootstrap core CSS -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <!--external css-->
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="assets/js/bootstrap-datepicker/css/datepicker.css" />
    <link rel="stylesheet" type="text/css" href="assets/js/bootstrap-daterangepicker/daterangepicker.css" />
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/style-responsive.css" rel="stylesheet">

    <!-- Custom CSS for improved UI -->
    <style>
        html, body {
        height: auto !important;
        overflow-y: auto !important;
        }
        
        body {
            background-color: #ffffff; /* White background */
        }
        .form-panel {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .form-group label {
            font-weight: 500;
            color: #333;
        }
        .form-control {
            border-radius: 5px;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            border-radius: 5px;
            padding: 10px 20px;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }
        .alert {
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .form-group small {
            color: #6c757d;
            font-size: 0.9em;
        }
    </style>

    <script>
        function getCat(val) {
            $.ajax({
                type: "POST",
                url: "getsubcat.php",
                data: 'catid=' + val,
                success: function(data) {
                    $("#subcategory").html(data);
                }
            });
        }
    </script>
</head>

<body>
    <section id="container">
        <?php include("includes/header.php"); ?>
        <?php include("includes/sidebar.php"); ?>
        <section id="main-content">
            <section class="wrapper">
                <h3><i class="fa fa-angle-right"></i> Customer Report Form</h3>

                <div class="row mt">
                    <div class="col-lg-12">
                        <div class="form-panel">
                            <?php if ($successmsg) { ?>
                                <div class="alert alert-success alert-dismissable">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                    <b>Success!</b> <?php echo htmlentities($successmsg); ?>
                                </div>
                            <?php } ?>

                            <?php if ($errormsg) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                    <b>Error!</b> <?php echo htmlentities($errormsg); ?>
                                </div>
                            <?php } ?>

                            <form class="form-horizontal style-form" method="post" name="complaint" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Category</label>
                                    <div class="col-sm-6">
                                        <select name="category" id="category" class="form-control" onchange="getCat(this.value);" required>
                                            <option value="">Select Category</option>
                                            <?php 
                                            $sql = mysqli_query($con, "SELECT id, categoryName FROM category");
                                            while ($rw = mysqli_fetch_array($sql)) {
                                            ?>
                                                <option value="<?php echo htmlentities($rw['id']); ?>"><?php echo htmlentities($rw['categoryName']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>

                                                            <div class="form-group">
                                <label class="col-sm-2 control-label">Kategori / Subkategori</label>
                                <div class="col-sm-6">
                                    <select name="subcategory" id="subcategory" class="form-control" required>
                                    <option value="">Pilih Subkategori</option>
                                    <option value="VACUUM">VACUUM</option>
                                    <option value="AIR COOLER">AIR COOLER</option>
                                    <option value="SERVICE">SERVICE</option>
                                    <option value="WIRING">WIRING</option>
                                    <option value="JUICER">JUICER</option>
                                    <option value="WATER JET">WATER JET</option>
                                    <option value="AIR FRYER">AIR FRYER</option>
                                    <option value="HAIR DRYER">HAIR DRYER</option>
                                    <option value="BREADMAKER">BREADMAKER</option>
                                    <option value="THERMOPOT">THERMOPOT</option>
                                    <option value="DRYER">DRYER</option>
                                    <option value="WATER DISPENSER">WATER DISPENSER</option>
                                    <option value="WATER PUMP">WATER PUMP</option>
                                    <option value="KETTLE JUG">KETTLE JUG</option>
                                    <option value="STEAMER">STEAMER</option>
                                    <option value="ANDROID BOX">ANDROID BOX</option>
                                    <option value="HAND MIXER">HAND MIXER</option>
                                    <option value="AIR PURIFIER">AIR PURIFIER</option>
                                    <option value="SEALER">SEALER</option>
                                    <option value="SPEAKER">SPEAKER</option>
                                    <option value="JAM">JAM</option>
                                    <option value="HOOD">HOOD</option>
                                    <option value="HOME THEATER">HOME THEATER</option>
                                    <option value="INSECT KILLER">INSECT KILLER</option>
                                    <option value="GRILL PAN">GRILL PAN</option>
                                    <option value="CCTV">CCTV</option>
                                    <option value="LAMPU">LAMPU</option>
                                    <option value="AUTOGATE">AUTOGATE</option>
                                    <option value="CHILLER">CHILLER</option>
                                    <option value="EKZOS FAN">EKZOS FAN</option>
                                    <option value="AIRCOND">AIRCOND</option>
                                    <option value="NETWORK">NETWORK</option>
                                    <option value="TRANSPORT">TRANSPORT</option>
                                    </select>
                                </div>
                                </div>


                                <div class="form-group">
                                <label class="col-sm-2 control-label">Brand Name</label>
                                <div class="col-sm-6">
                                    <select name="brandname" id="brandname" class="form-control" required>
                                    <option value="">Select Brand</option>
                                    <?php 
                                    $stmt = $con->prepare("SELECT id, brandname FROM brandname ORDER BY brandname ASC");
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    while ($rw = $result->fetch_array()) {
                                    ?>
                                        <option value="<?php echo htmlentities($rw['id']); ?>">
                                        <?php echo htmlentities($rw['brandname']); ?>
                                        </option>
                                    <?php } ?>
                                    </select>
                                </div>
</div>

                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Purchase</label>
                                    <div class="col-sm-6">
                                        <select name="state" class="form-control" required>
                                            <option value="">Select Purchase</option>
                                            <?php 
                                            $stmt = $con->prepare("SELECT stateName FROM state");
                                            $stmt->execute();
                                            $result = $stmt->get_result();
                                            while ($rw = $result->fetch_array()) {
                                            ?>
                                                <option value="<?php echo htmlentities($rw['stateName']); ?>"><?php echo htmlentities($rw['stateName']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-2 control-label">No Model</label>
                                    <div class="col-sm-6">
                                        <input type="text" name="modelNo" class="form-control">
                                        <small>Enter model number or product name</small>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Complaint Details</label>
                                    <div class="col-sm-6">
                                        <textarea name="complaindetails" class="form-control" rows="5" maxlength="2000" required></textarea>
                                        <small>Maximum 2000 characters</small>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Warranty Type</label>
                                    <div class="col-sm-6">
                                        <select name="complaintype" id="complaintype" class="form-control" required>
                                            <option value="Under Warranty">Under Warranty (Requires warranty document and receipt)</option>
                                            <option value="Over Warranty">Out of Warranty (No documents required)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group" id="warrantyfile-container">
                                    <label class="col-sm-2 control-label">Warranty Document</label>
                                    <div class="col-sm-6">
                                        <input type="file" name="warrantyfile" id="warrantyfile" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                                        <small>Only JPEG, PNG, or PDF files (max 5MB)</small>
                                    </div>
                                </div>

                                <div class="form-group" id="receiptfile-container">
                                    <label class="col-sm-2 control-label">Purchase Receipt</label>
                                    <div class="col-sm-6">
                                        <input type="file" name="receiptfile" id="receiptfile" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                                        <small>Only JPEG, PNG, or PDF files (max 5MB)</small>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-sm-10 text-center">
                                        <button type="submit" name="submit" class="btn btn-primary">Submit Complaint</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </section>
        <?php include("includes/footer.php"); ?>
    </section>

    <!-- js placed at the end of the document so the pages load faster -->
    <script src="assets/js/jquery.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script class="include" type="text/javascript" src="assets/js/jquery.dcjqaccordion.2.7.js"></script>
    <script src="assets/js/jquery.scrollTo.min.js"></script>
    <script src="assets/js/jquery.nicescroll.js" type="text/javascript"></script>

    <!--common script for all pages-->
    <script src="assets/js/common-scripts.js"></script>

    <!--script for this page-->
    <script src="assets/js/jquery-ui-1.9.2.custom.min.js"></script>
    <script src="assets/js/bootstrap-switch.js"></script>
    <script src="assets/js/jquery.tagsinput.js"></script>
    <script type="text/javascript" src="assets/js/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
    <script type="text/javascript" src="assets/js/bootstrap-daterangepicker/date.js"></script>
    <script type="text/javascript" src="assets/js/bootstrap-daterangepicker/daterangepicker.js"></script>
    <script type="text/javascript" src="assets/js/bootstrap-inputmask/bootstrap-inputmask.min.js"></script>
    <script src="assets/js/form-component.js"></script>

    <script>
        // Custom select box
        $(function() {
            $('select.styled').customSelect();
        });

        // Get elements
        const complaintype = document.getElementById('complaintype');
        const warrantyfileContainer = document.getElementById('warrantyfile-container');
        const receiptfileContainer = document.getElementById('receiptfile-container');
        const warrantyfileInput = document.getElementById('warrantyfile');
        const receiptfileInput = document.getElementById('receiptfile');
        const form = document.querySelector('form[name="complaint"]');

        // Function to toggle file upload requirements
        function toggleFileRequirements() {
            if (complaintype.value === "Under Warranty") {
                warrantyfileInput.setAttribute('required', 'required');
                receiptfileInput.setAttribute('required', 'required');
                warrantyfileContainer.style.display = 'block';
                receiptfileContainer.style.display = 'block';
            } else {
                warrantyfileInput.removeAttribute('required');
                receiptfileInput.removeAttribute('required');
                warrantyfileContainer.style.display = 'none';
                receiptfileContainer.style.display = 'none';
                warrantyfileInput.value = ''; // Clear file input
                receiptfileInput.value = ''; // Clear file input
            }
        }

        // Initial toggle based on the default selection
        toggleFileRequirements();

        // Listen for changes to complaint type selection
        complaintype.addEventListener('change', toggleFileRequirements);

        // Client-side validation
        form.addEventListener('submit', function(e) {
            const complainDetails = document.querySelector('textarea[name="complaindetails"]').value;
            const maxFileSize = 5 * 1024 * 1024; // 5MB
            const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];

            if (complainDetails.length > 2000) {
                e.preventDefault();
                alert('Complaint details exceed the 2000-character limit.');
                return;
            }

            if (complaintype.value === "Under Warranty") {
                if (warrantyfileInput.files.length > 0) {
                    const warrantyFile = warrantyfileInput.files[0];
                    if (warrantyFile.size > maxFileSize || !allowedTypes.includes(warrantyFile.type)) {
                        e.preventDefault();
                        alert('Invalid warranty file. Only JPEG, PNG, or PDF allowed (max 5MB).');
                        return;
                    }
                } else {
                    e.preventDefault();
                    alert('Please upload a warranty file for Under Warranty option.');
                    return;
                }

                if (receiptfileInput.files.length > 0) {
                    const receiptFile = receiptfileInput.files[0];
                    if (receiptFile.size > maxFileSize || !allowedTypes.includes(receiptFile.type)) {
                        e.preventDefault();
                        alert('Invalid receipt file. Only JPEG, PNG, or PDF allowed (max 5MB).');
                        return;
                    }
                } else {
                    e.preventDefault();
                    alert('Please upload a receipt file for Under Warranty option.');
                    return;
                }
            }
        });
    </script>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
  $('#subcategory').select2({
    placeholder: "Pilih Subkategori",
    allowClear: true
  });
});
</script>


<!-- jQuery + Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
  $('#brandname').select2({
    placeholder: "Select Brand",
    allowClear: true
  });
});
</script>
</body>
</html>