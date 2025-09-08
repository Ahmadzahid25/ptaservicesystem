<?php
session_start();
include('include/config.php');

if(strlen($_SESSION['alogin'])==0){	
    header('location:index.php');
    exit();
}

// Forward/Update complaint by Admin
if(isset($_POST['update'])){
    $complaintnumber = isset($_GET['cid']) ? intval($_GET['cid']) : 0;
    $status = $_POST['status'];
    $notetransport = $_POST['notetransport'];
    $checking = $_POST['checking'];
    $remark = $_POST['remark'];

    if($complaintnumber){
        // Check if admin already has a remark for this complaint
        $check = mysqli_query($con, "SELECT * FROM complaintremark WHERE complaintNumber='$complaintnumber'");
        if(mysqli_num_rows($check) > 0){
            // Update existing record
            $query = mysqli_query($con, "UPDATE complaintremark SET 
                status='$status',
                notetransport='$notetransport',
                checking='$checking',
                remark='$remark'
                WHERE complaintNumber='$complaintnumber'");
        } else {
            // Insert new record
            $query = mysqli_query($con, "INSERT INTO complaintremark(complaintNumber, status, notetransport, checking, remark) 
                VALUES('$complaintnumber','$status','$notetransport','$checking','$remark')");
        }

        // Update complaint status
        $sql = mysqli_query($con, "UPDATE tblcomplaints SET status='$status' WHERE complaintNumber='$complaintnumber'");

        echo "<script>alert('Complaint details updated successfully');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Update Complaint - Admin</title>
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
<table>
<tr>
  <td><b>Repair Number</b></td>
  <td><?php echo htmlentities($_GET['cid']); ?></td>
</tr>
<tr>
  <td><b>Status</b></td>
  <td>
    <select name="status" required>
      <option value="">Select Status</option>
      <option value="in process">In Process</option>
      <option value="closed">Closed</option>
    </select>
  </td>
</tr>
<tr>
  <td><b>Note Transport</b></td>
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
<tr>
  <td>&nbsp;</td>
  <td><input type="submit" name="update" value="Submit"></td>
</tr>
<tr>
  <td></td>
  <td><input type="button" value="Close this window" onclick="f2();" style="cursor:pointer;"></td>
</tr>
</table>
</form>
</div>
</body>
</html>
