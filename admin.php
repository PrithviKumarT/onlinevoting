<?php
include "db.php";
?>
<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Poppins;}
body{
  min-height:100vh;
  background:linear-gradient(135deg,#0f2027,#203a43,#2c5364);
  color:white;
  padding:40px;
}
.container{max-width:1000px;margin:auto;}
.card{
  background:rgba(255,255,255,0.08);
  backdrop-filter:blur(15px);
  border-radius:20px;
  padding:30px;
  margin-bottom:30px;
  box-shadow:0 20px 40px rgba(0,0,0,0.3);
}
h2{margin-bottom:15px;}
table{width:100%;border-collapse:collapse;}
th,td{padding:12px;text-align:center;}
th{background:rgba(255,255,255,0.1);}
.chart-box{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:30px;
}
.footer{text-align:center;margin-top:20px;opacity:0.6;font-size:12px;}
</style>
</head>

<body>
<div class="container">

  <div class="card">
    <h2>Live Voting Results</h2>
    <table id="resultTable">
      <tr><th>Candidate</th><th>Votes</th></tr>
      <tr><td>Candidate 1</td><td id="c1">0</td></tr>
      <tr><td>Candidate 2</td><td id="c2">0</td></tr>
      <tr><td>NOTA</td><td id="c3">0</td></tr>
    </table>
  </div>

  <div class="chart-box">
    <div class="card">
      <h2>Bar Chart</h2>
      <canvas id="barChart"></canvas>
    </div>
    <div class="card">
      <h2>Pie Chart</h2>
      <canvas id="pieChart"></canvas>
    </div>
  </div>

  <div class="footer">
    Admin Dashboard • Auto Live System
  </div>

</div>

<script>
// Shadow / Gloss plugin
Chart.register({
  id: 'shadow',
  beforeDraw: (chart) => {
    const ctx = chart.ctx;
    ctx.save();
    ctx.shadowColor = "rgba(0,0,0,0.4)";
    ctx.shadowBlur = 15;
    ctx.shadowOffsetX = 5;
    ctx.shadowOffsetY = 5;
  }
});

// Context
const barCtx = document.getElementById("barChart").getContext("2d");
const pieCtx = document.getElementById("pieChart").getContext("2d");

// Gradients (glossy)
const grad1 = barCtx.createLinearGradient(0,0,0,300);
grad1.addColorStop(0,"#ff9a9e");
grad1.addColorStop(1,"#ff3b6f");

const grad2 = barCtx.createLinearGradient(0,0,0,300);
grad2.addColorStop(0,"#6dd5fa");
grad2.addColorStop(1,"#2980b9");

const grad3 = barCtx.createLinearGradient(0,0,0,300);
grad3.addColorStop(0,"#81ecec");
grad3.addColorStop(1,"#00b894");

// Data structure
const chartData = {
  labels: ["Candidate 1","Candidate 2","NOTA"],
  datasets: [{
    label: "Votes",
    data: [0,0,0],
    backgroundColor: [grad1, grad2, grad3],
    borderColor: ["#fff","#fff","#fff"],
    borderWidth: 2,
    hoverOffset: 15
  }]
};

// Bar chart
const barChart = new Chart(barCtx, {
  type: "bar",
  data: chartData,
  options: {
    plugins: {
      legend: { labels:{color:"white"} }
    },
    scales: {
      x:{ ticks:{color:"white"}, grid:{color:"rgba(255,255,255,0.1)"} },
      y:{ ticks:{color:"white"}, grid:{color:"rgba(255,255,255,0.1)"} }
    }
  }
});

// Pie chart
const pieChart = new Chart(pieCtx, {
  type: "pie",
  data: chartData,
  options: {
    plugins: {
      legend: {
        position:"bottom",
        labels:{color:"white"}
      }
    }
  }
});

// AUTO LIVE UPDATE
function updateData(){
  fetch("get_data.php")
    .then(res => res.json())
    .then(data => {
      document.getElementById("c1").innerText = data[1];
      document.getElementById("c2").innerText = data[2];
      document.getElementById("c3").innerText = data[3];

      barChart.data.datasets[0].data = [data[1],data[2],data[3]];
      pieChart.data.datasets[0].data = [data[1],data[2],data[3]];

      barChart.update();
      pieChart.update();
    });
}

// Every 2 seconds
setInterval(updateData,2000);
updateData();
</script>
<form action="publish.php" method="post">
  <button name="publish">Publish Results</button>
</form>

</body>
</html>
