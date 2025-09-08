<?php
session_start();
include('../include/config.php');

if(strlen($_SESSION['subalogin'])==0){ 
    header('location:index.php');
    exit();
}

if(isset($_POST['update'])) {
    $complaintnumber = isset($_GET['cid']) ? intval($_GET['cid']) : 0;
    $status = $_POST['status'];
    $notetransport = $_POST['notetransport'];
    $checking = $_POST['checking'];
    $remark = $_POST['remark'];
    $sadminid = $_SESSION['suid'];

    if($complaintnumber){
        // Cek ada record lama
        $check = mysqli_query($con, "SELECT id FROM tblsubadminremark WHERE ComplainNumber='$complaintnumber' AND RemarkBy='$sadminid'");
        if(mysqli_num_rows($check) > 0){
            // Update data lama
            $query = mysqli_query($con, "UPDATE tblsubadminremark 
                SET ComplainStatus='$status', notetransport='$notetransport', checking='$checking', ComplainRemark='$remark', PostingDate=NOW()
                WHERE ComplainNumber='$complaintnumber' AND RemarkBy='$sadminid'");
        } else {
            // Insert data baru
            $query = mysqli_query($con, "INSERT INTO tblsubadminremark (ComplainNumber, ComplainStatus, notetransport, checking, ComplainRemark, RemarkBy, PostingDate)
                VALUES ('$complaintnumber','$status','$notetransport','$checking','$remark','$sadminid',NOW())");
        }

        // Update status di tblcomplaints
        $sql = mysqli_query($con,"UPDATE tblcomplaints SET status='$status' WHERE complaintNumber='$complaintnumber'");

        echo "<script>alert('Complaint details updated successfully');</script>";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Update Complaint</title>
<link href="style.css" rel="stylesheet" type="text/css" />
<link href="anuj.css" rel="stylesheet" type="text/css">
<script>
function f2(){ window.close(); }
function f3(){ window.print(); }
</script>
</head>
<body>

<div style="margin-left:50px;">
<form name="updateticket" id="updatecomplaint" method="post"> 
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr height="50">
<td><b>Repair Number</b></td>
<td><?php echo htmlentities($_GET['cid']); ?></td>
</tr>
<tr height="50">
<td><b>Status</b></td>
<td>
<select name="status" required="required">
<option value="">Select Status</option>
<option value="in process">In Process</option>
<option value="closed">Closed</option>
</select>
</td>
</tr>
<tr>
<td><b>Note transport</b></td>
<td><textarea name="notetransport" cols="50" rows="5" required></textarea></td>
</tr>
<tr>
<td><b>Checking</b></td>
<td><textarea name="checking" cols="50" rows="5" required></textarea></td>
</tr>
<tr>
<td><b>Remark</b></td>
<td><textarea name="remark" cols="50" rows="5" required></textarea></td>
</tr>
<tr height="50">
<td>&nbsp;</td>
<td>
<input type="submit" name="update" value="Submit" class="btn btn-primary">
<input name="Submit2" type="button" value="Close this window" onClick="f2();" class="btn btn-default">
</td>
</tr>
</table>
</form>
</div>

</body>
</html>