<?php
include "db.php";

$res = $conn->query("SELECT candidate, COUNT(*) as total FROM votes GROUP BY candidate");

$data = [1=>0, 2=>0, 3=>0];
while($row = $res->fetch_assoc()){
  $data[$row['candidate']] = $row['total'];
}

echo json_encode($data);
?>
