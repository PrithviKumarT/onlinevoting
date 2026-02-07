<?php
include "db.php";

// Get results
$res = $conn->query(
 "SELECT candidate, COUNT(*) as total FROM votes GROUP BY candidate"
);

$resultText = "Voting Results:\n";
while($r = $res->fetch_assoc()){
  $resultText .= "Candidate ".$r['candidate']." : ".$r['total']." votes\n";
}

// Get all emails
$emails = $conn->query(
 "SELECT email FROM votes WHERE email!=''"
);

while($e = $emails->fetch_assoc()){
    $to = $e['email'];
    $subject = "Voting Results";
    $message = $resultText;
    $headers = "From: voting@mechexpo.com";

    mail($to,$subject,$message,$headers);
}

echo "Results published and emailed successfully!";
?>
