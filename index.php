<?php
include "db.php";
$message = "";
$error = "";

// Handle vote
if (isset($_POST['vote'])) {

    // SAFE READ (this fixes your error)
    $name  = trim($_POST['name']  ?? "");
    $phone = trim($_POST['phone'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $candidate = $_POST['vote'];

    // VALIDATION
    if (!preg_match("/^[A-Za-z ]{3,}$/", $name)) {
        $error = "Invalid name (only letters, min 3 characters)";
    }
    else if (!preg_match("/^[6-9][0-9]{9}$/", $phone)) {
        $error = "Invalid phone number (10 digits, starts with 6-9)";
    }
    else if ($email != "" && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address";
    }
    else {
        // Check if phone already exists
        $check = $conn->prepare("SELECT id FROM votes WHERE phone=?");
        $check->bind_param("s", $phone);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "This phone number has already voted";
        } else {
            // Insert vote
            $stmt = $conn->prepare(
                "INSERT INTO votes(name, phone, email, candidate) VALUES(?,?,?,?)"
            );
            $stmt->bind_param("sssi", $name, $phone, $email, $candidate);
            $stmt->execute();

            $message = "Vote recorded successfully!";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Digital Voting</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Poppins;}
body{
  height:100vh;
  background:linear-gradient(135deg,#0f2027,#203a43,#2c5364);
  display:flex;
  align-items:center;
  justify-content:center;
  color:white;
}
.card{
  background:rgba(255,255,255,0.08);
  backdrop-filter:blur(15px);
  border-radius:20px;
  padding:40px;
  width:420px;
  text-align:center;
  box-shadow:0 20px 40px rgba(0,0,0,0.3);
}
input{
  width:100%;
  padding:10px;
  margin:8px 0;
  border-radius:8px;
  border:none;
  outline:none;
}
.vote-btn{
  display:block;
  width:100%;
  margin:15px 0;
  padding:16px;
  border-radius:12px;
  border:none;
  font-size:16px;
  cursor:pointer;
  color:white;
  transition:0.3s;
}
.btn1{background:linear-gradient(135deg,#ff512f,#dd2476);}
.btn2{background:linear-gradient(135deg,#24c6dc,#514a9d);}
.btn3{background:linear-gradient(135deg,#56ab2f,#a8e063);}
.vote-btn:hover{transform:translateY(-3px);}

/* MODAL */
.modal-bg{
  position:fixed;
  top:0;left:0;
  width:100%;height:100%;
  background:rgba(0,0,0,0.6);
  display:flex;
  align-items:center;
  justify-content:center;
  opacity:0;
  pointer-events:none;
  transition:0.3s;
}
.modal{
  background:rgba(255,255,255,0.12);
  backdrop-filter:blur(20px);
  padding:30px;
  border-radius:20px;
  width:320px;
  text-align:center;
  transform:scale(0.7);
  transition:0.3s;
}
.modal-bg.active{
  opacity:1;
  pointer-events:auto;
}
.modal-bg.active .modal{
  transform:scale(1);
}
.modal button{
  margin-top:15px;
  padding:10px 20px;
  border:none;
  border-radius:10px;
  cursor:pointer;
}
.confirm{background:#00e676;}
.cancel{background:#ff5252;color:white;}

/* SUCCESS & ERROR TOAST */
.toast{
  position:fixed;
  bottom:30px;
  right:30px;
  padding:15px 25px;
  border-radius:12px;
  backdrop-filter:blur(10px);
  animation:slideIn 0.5s ease;
}
.success{background:rgba(0,255,150,0.2);}
.error{background:rgba(255,0,0,0.2);}

@keyframes slideIn{
  from{transform:translateX(100px);opacity:0;}
  to{transform:translateX(0);opacity:1;}
}
</style>
</head>

<body>
<div class="card">
  <h2>Digital Voting</h2>
  <p>Enter details and select your choice</p><br>

  <input id="name" placeholder="Name *" required>
  <input id="phone" placeholder="Phone *" required>
  <input id="email" placeholder="Email (optional)">

  <button class="vote-btn btn1" onclick="openModal(1)">Candidate 1</button>
  <button class="vote-btn btn2" onclick="openModal(2)">Candidate 2</button>
  <button class="vote-btn btn3" onclick="openModal(3)">NOTA</button>
</div>

<!-- Hidden Form -->
<form id="voteForm" method="post" style="display:none;">
  <input type="hidden" name="name" id="f_name">
  <input type="hidden" name="phone" id="f_phone">
  <input type="hidden" name="email" id="f_email">
  <input type="hidden" name="vote" id="voteInput">
</form>

<!-- MODAL -->
<div class="modal-bg" id="modalBg">
  <div class="modal">
    <h3 id="modalText">Confirm?</h3>
    <button class="confirm" onclick="confirmVote()">Confirm</button>
    <button class="cancel" onclick="closeModal()">Cancel</button>
  </div>
</div>

<?php if($message!=""){ ?>
<div class="toast success">
  ✅ <?= $message ?>
</div>
<?php } ?>

<?php if($error!=""){ ?>
<div class="toast error">
  ❌ <?= $error ?>
</div>
<?php } ?>

<script>
let selectedCandidate = null;

function openModal(num){
  const name = document.getElementById("name").value.trim();
  const phone = document.getElementById("phone").value.trim();

  if(name=="" || phone==""){
    alert("Name and Phone are required");
    return;
  }

  selectedCandidate = num;
  document.getElementById("modalText").innerText =
    "Are you sure you want to vote for Candidate " + num + "?";
  document.getElementById("modalBg").classList.add("active");
}

function closeModal(){
  document.getElementById("modalBg").classList.remove("active");
}

function confirmVote(){
  document.getElementById("f_name").value =
    document.getElementById("name").value;
  document.getElementById("f_phone").value =
    document.getElementById("phone").value;
  document.getElementById("f_email").value =
    document.getElementById("email").value;
  document.getElementById("voteInput").value = selectedCandidate;
  document.getElementById("voteForm").submit();
}
</script>
</body>
</html>
