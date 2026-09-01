<?php
session_start();
if (!$_SESSION['user']) {
    header("Location: login.php");
    exit();
}

header("Cross-Origin-Opener-Policy: same-origin"); 
header("Cross-Origin-Embedder-Policy: require-corp");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>People Counter Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@4.20.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/coco-ssd@2.2.2/dist/coco-ssd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/onnxruntime-web/dist/ort.min.js"></script>
    <link rel="stylesheet" href="style\index_style.css">
    <link rel="stylesheet" href="style\start_button.css">
    <link rel="stylesheet" href="style\another_btn.css">
</head>

<body>

    <div class="header">
        <h1>People Counter Dashboard</h1>
    </div>

    <div class="dashboard">
        <div class="card">
            <div class="card-title">Status</div>
            <div class="card-value" id="status">Loading...</div>
        </div>
        <div class="card">
            <div class="card-title">Detections</div>
            <div class="card-value" id="detections">0</div>
        </div>
        <div class="card">
            <div class="card-title">FPS</div>
            <div class="card-value" id="fps">0</div>
        </div>
    </div>

    <div class="video-container">
        <video id="video" muted playsinline></video>
        <canvas id="canvas"></canvas>
    </div>

    <div class="Start-Stop_Button">
        <button id="startBtn" type="button">Start</button>
        <button id="stopBtn" type="button">Stop</button>
        <button id="sourceBtn" type="button">Switch to Live Cam</button>
        <button id="resetBtn" type="button">Reset</button>
    </div>



    <div class="controls">
        <h3>Adjust Detection Zone</h3>
        <label>X Position: <input type="range" id="zoneX" min="0" max="1920" value="320"></label><br>
        <label>Y Position: <input type="range" id="zoneY" min="0" max="1200" value="240"></label><br>
        <label>Width: <input type="range" id="zoneW" min="50" max="1920" value="1200"></label><br>
        <label>Height: <input type="range" id="zoneH" min="50" max="1200" value="800"></label>
        <label>Rotation (deg): <input type="range" id="zoneAngle" min="0" max="360" value="0"></label>
    </div>

    <div class="footer">
        TensorFlow.js
    </div>

    <script src="script12_A.js" defer></script>

</body>

</html>