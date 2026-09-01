let yolo_model;
let frameCount = 0;
let lastTime = Date.now();
// let peopleCount = 0;
const stopButton = document.getElementById('stopBtn');
const video = document.getElementById('video');
const canvas = document.getElementById('canvas');
const startButton = document.getElementById('startBtn');
const sourceButton = document.getElementById('sourceBtn');
const resetButton = document.getElementById('resetBtn')
const ctx = canvas.getContext('2d');
let countingZone = { x: 320, y: 240, width: 1200, height: 800 };
let trackedPersons = {};
let nextPersonId = 1;
let totalParticipants = 0;
let currentParticipants = 0;
const maxDisplacement = 400;
const maxTrackAge = 3;
let isProcessingActive = false;
let currentSource = 'video';
const FIVE_MINUTES = 5 * 60;
const currentDateTime = new Date();
const day = currentDateTime.getDate();
const month = currentDateTime.getMonth();
const year = currentDateTime.getFullYear();
const hours = currentDateTime.getHours();
const minutes = currentDateTime.getMinutes();
const seconds = currentDateTime.getSeconds();
const time = day + ":" + month + ":" + year + ":" + hours + ":" + minutes + ":" + seconds;

// Pre-allocate offscreen canvas for 640x640 preprocessing
const offscreenCanvas = document.createElement('canvas');
offscreenCanvas.width = 640;
offscreenCanvas.height = 640;
// 'willReadFrequently: true' optimizes software rendering for getImageData calls
const offscreenCtx = offscreenCanvas.getContext('2d', { willReadFrequently: true });

let personCount = 0;



const TEN_MINUTES_MS = 10 * 60 * 1000;
 
setInterval(() => {
 
    // Reads global current state variables every 10 minutes
 
    sendToBackend(currentParticipants, personCount);
 
}, TEN_MINUTES_MS);

async function checkSession() {
    try {
        const response = await fetch('get_session.php');
        const sessionData = await response.json();

        console.log("Fetched session data:", sessionData);

        if (sessionData.total_participants) {
            console.log("Total:", sessionData.total_participants);
            personCount = sessionData.total_participants;
        }
    } catch (error) {
        console.error("Error fetching session:", error);
    }
}

function getFormattedTime() {
    const now = new Date();

    const day = String(now.getDate()).padStart(2, '0');
    const month = String(now.getMonth() + 1).padStart(2, '0'); // Months are zero-indexed
    const year = now.getFullYear();

    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');

    return `${day}:${month}:${year}:${hours}:${minutes}:${seconds}`;
}

resetButton.addEventListener('click', function () {
    const payload = {
        Command: 'Reset'
    };

    fetch('reset_event_counter.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    })
        .then(response => {
            // 2. Explicitly handle server-side errors (4xx, 5xx)
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                console.log('Success:', data.message);
                // Insert UI update logic here (e.g., changing counter display to 0)
            } else {
                console.warn('Application Warning:', data.message);
            }
        })
        .catch(error => {
            // Catches both network failures AND errors thrown from the response block above
            console.error('Error communicating with PHP:', error.message);
        });
    location.reload();
});


stopButton.addEventListener('click', function () {

    isProcessingActive = false;

    video.pause();

})

sourceButton.addEventListener('click', async () => {

    if (currentSource === 'video') {
        currentSource = 'live_cam';
        sourceButton.textContent = "Switch to Video File";
    } else {
        currentSource = 'video';
        sourceButton.textContent = "Switch to Live Cam";
    }

    try {
        await setupCamera();
        isProcessingActive = false;

    } catch (err) {
        console.error("Failed to switch media source:", err);
    }
});

function stopCurrentStream() {
    if (video.srcObject) {
        video.srcObject.getTracks().forEach(track => track.stop());
        video.srcObject = null;
    }
    video.removeAttribute('src');
    video.load();
}

async function setupCamera() {
    stopCurrentStream();

    if (currentSource === 'video') {
        return new Promise((resolve, reject) => {
            video.crossOrigin = 'anonymous';
            video.loop = true;

            video.onloadedmetadata = () => {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
            };

            video.oncanplaythrough = async () => {
                try {

                    resolve(video);
                } catch (err) {
                    reject(new Error("Playback blocked: " + err.message));
                }
            };

            video.onerror = () => {
                reject(new Error("Failed to load video file."));
            };

            video.src = 'video/people_walking.mp4';
        });
    } else {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { width: { ideal: 1920 }, height: { ideal: 1080 } },
                audio: false
            });

            return new Promise((resolve, reject) => {
                video.srcObject = stream;

                video.onloadeddata = async () => {
                    try {
                        canvas.width = video.videoWidth;
                        canvas.height = video.videoHeight;
                        await video.play();
                        resolve(video);
                    } catch (err) {
                        reject(new Error("Playback failed: " + err.message));
                    }
                };

                video.onerror = () => {
                    reject(new Error("Video element error occurred."));
                };
            });
        } catch (error) {
            console.error("Error accessing camera:", error);
            throw error;
        }
    }
}

function setupZoneControls() {
    const inputX = document.getElementById('zoneX');
    const inputY = document.getElementById('zoneY');
    const inputW = document.getElementById('zoneW');
    const inputH = document.getElementById('zoneH');
    const inputAngle = document.getElementById('zoneAngle');

    function updateZone() {
        countingZone.x = parseInt(inputX.value, 10) || 0;
        countingZone.y = parseInt(inputY.value, 10) || 0;
        countingZone.width = parseInt(inputW.value, 10) || 0;
        countingZone.height = parseInt(inputH.value, 10) || 0;
        countingZone.angle = parseInt(inputAngle.value, 10) || 0;

        // Calculate center line coordinates (Vertical split)
        const centerX = countingZone.x + (countingZone.width / 2);

        countingZone.centerLine = {
            x1: centerX,
            y1: countingZone.y,
            x2: centerX,
            y2: countingZone.y + countingZone.height
        };

        // force a redraw so the box moves instantly
        if (!isProcessingActive) {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            drawCountingZone();
        }
    }

    inputX.addEventListener('input', updateZone);
    inputY.addEventListener('input', updateZone);
    inputW.addEventListener('input', updateZone);
    inputH.addEventListener('input', updateZone);
    inputAngle.addEventListener('input', updateZone);
}

function drawCountingZone() {
    const centerX = countingZone.x + (countingZone.width / 2);
    const centerY = countingZone.y + (countingZone.height / 2);
    const angleInRadians = ((countingZone.angle || 0) * Math.PI) / 180;

    ctx.save();

    // Apply rotation around center
    ctx.translate(centerX, centerY);
    ctx.rotate(angleInRadians);
    ctx.translate(-centerX, -centerY);

    // Bounding Box
    ctx.strokeStyle = 'green';
    ctx.lineWidth = 4;
    ctx.strokeRect(countingZone.x, countingZone.y, countingZone.width, countingZone.height);

    ctx.stroke();

    ctx.restore();
}

/* function isInsideCountingZone(x, y) {
    return x > countingZone.x && x < countingZone.x + countingZone.width &&
        y > countingZone.y && y < countingZone.y + countingZone.height;
}*/

async function loadModel() {
    document.getElementById('status').innerText = 'Loading Model...';

    try {
        const modelUrl = 'exp.onnx';

        // Set WASM multi-threading options to boost performance if GPU fails
        ort.env.wasm.numThreads = Math.min(4, navigator.hardwareConcurrency || 2);

        const options = {
            // Priority order for hardware acceleration
            executionProviders: ['wasm'],
            // Enable graph optimization for better performance
            graphOptimizationLevel: 'all'
        };

        yolo_model = await ort.InferenceSession.create(modelUrl, options);

        document.getElementById('status').innerText = 'Ready';
        console.log("YOLOv8 ONNX model loaded successfully.");

    } catch (err) {
        document.getElementById('status').innerText = 'Failed to load model.';
        console.error("Error loading model:", err);
    }
}

function sendToBackend(current_participants, personCount) {

    // Fallback: Use current ISO timestamp if timeString is not provided
    const payloadTime = getFormattedTime();

    fetch('actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            Time: payloadTime,
            current_participants: current_participants,
            totalParticipants: personCount
        })
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'error') {
                console.error("PHP Error:", data.message);
            }
        })
        .catch(err => console.error("Backend sync failed:", err));
}

function runNMS(candidates, iouThreshold, maxOutputSize) {
    // 1. Sort by confidence score descending
    candidates.sort((a, b) => b.score - a.score);
 
    const selected = [];
    const active = new Array(candidates.length).fill(true);
 
    for (let i = 0; i < candidates.length; i++) {
        if (!active[i]) continue;
 
        const boxA = candidates[i];
        selected.push(boxA);
 
        if (selected.length >= maxOutputSize) break;
 
        for (let j = i + 1; j < candidates.length; j++) {
            if (!active[j]) continue;
 
            const boxB = candidates[j];
            // Calculate Intersection over Union (IoU)
            const interX1 = Math.max(boxA.x1, boxB.x1);
            const interY1 = Math.max(boxA.y1, boxB.y1);
            const interX2 = Math.min(boxA.x2, boxB.x2);
            const interY2 = Math.min(boxA.y2, boxB.y2);
 
            const interWidth = Math.max(0, interX2 - interX1);
            const interHeight = Math.max(0, interY2 - interY1);
            const interArea = interWidth * interHeight;
 
            if (interArea > 0) {
                const areaA = (boxA.x2 - boxA.x1) * (boxA.y2 - boxA.y1);
                const areaB = (boxB.x2 - boxB.x1) * (boxB.y2 - boxB.y1);
                const unionArea = areaA + areaB - interArea;
                const iou = interArea / unionArea;
 
                // Suppress overlapping candidate box
                if (iou >= iouThreshold) {
                    active[j] = false;
                }
            }
        }
    }
 
    return selected;
}

async function detectPeople() {
    if (!yolo_model) return;
 
    let inputOrtTensor;
    let outputMap;
 
    try {
        // 1. Draw video frame to 640x640 canvas and extract raw RGBA pixels
        offscreenCtx.drawImage(video, 0, 0, 640, 640);
        const imageData = offscreenCtx.getImageData(0, 0, 640, 640);
        const pixels = imageData.data; // Uint8ClampedArray [R, G, B, A, R, G, B, A, ...]
 
        // 2. Preprocess: Convert BHWC RGBA [640,640,4] to CHW Float32 [1,3,640,640] normalized [0, 1]
        const float32Data = new Float32Array(1 * 3 * 640 * 640);
        const imageArea = 640 * 640;
 
        for (let i = 0; i < imageArea; i++) {
            float32Data[i]                 = pixels[i * 4]     / 255.0; // Red
            float32Data[imageArea + i]     = pixels[i * 4 + 1] / 255.0; // Green
            float32Data[imageArea * 2 + i] = pixels[i * 4 + 2] / 255.0; // Blue
        }
 
        // 3. Create ONNX Tensor and Run Inference
        inputOrtTensor = new ort.Tensor('float32', float32Data, [1, 3, 640, 640]);
        outputMap = await yolo_model.run({ images: inputOrtTensor });
 
        // 4. Extract Output Data (Shape: [1, 5, 8400])
        const outputName = yolo_model.outputNames[0];
        const rawOutput = outputMap[outputName].data; // Float32Array size: 5 * 8400 = 42000
 
        // Clear display canvas for updated rendering
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        drawCountingZone();
 
        // 5. Post-process YOLO output in Vanilla JS (Extract Bounding Boxes & Scores)
        const scoreThreshold = 0.30;
        const candidates = [];
 
        // YOLOv8 output layout: 8400 predictions across 5 attributes (cx, cy, w, h, class_score)
        const numAnchors = 8400;
 
        for (let i = 0; i < numAnchors; i++) {
            const score = rawOutput[4 * numAnchors + i]; // Attribute index 4 = class score
 
            if (score >= scoreThreshold) {
                const cx = rawOutput[0 * numAnchors + i];
                const cy = rawOutput[1 * numAnchors + i];
                const w  = rawOutput[2 * numAnchors + i];
                const h  = rawOutput[3 * numAnchors + i];
 
                // Convert center coordinates (cx, cy, w, h) to corner coordinates (x1, y1, x2, y2)
                const x1 = cx - w / 2;
                const y1 = cy - h / 2;
                const x2 = cx + w / 2;
                const y2 = cy + h / 2;
 
                candidates.push({ x1, y1, x2, y2, score });
            }
        }
 
        // 6. Run Non-Maximum Suppression (NMS)
        const iouThreshold = 0.35;
        const maxOutputSize = 300;
        const selectedDetections = runNMS(candidates, iouThreshold, maxOutputSize);
 
        // 7. Scale coordinates back to canvas dimensions
        const currentDetections = [];
        const scaleX = canvas.width / 640;
        const scaleY = canvas.height / 640;
 
        for (let i = 0; i < selectedDetections.length; i++) {
            const det = selectedDetections[i];
            const x = det.x1 * scaleX;
            const y = det.y1 * scaleY;
            const width = (det.x2 - det.x1) * scaleX;
            const height = (det.y2 - det.y1) * scaleY;
 
            currentDetections.push({
                centerX: x + (width / 2),
                centerY: y + (height / 2),
                box: [x, y, width, height],
                score: det.score
            });
        }
 
        // 8. Zone Check Math Setup
        const zoneCenterX = countingZone.x + (countingZone.width / 2);
        const zoneCenterY = countingZone.y + (countingZone.height / 2);
        const angleRad = ((countingZone.angle || 0) * Math.PI) / 180;
        const cosA = Math.cos(-angleRad);
        const sinA = Math.sin(-angleRad);
 
        function isPointInRotatedRect(px, py) {
            const dx = px - zoneCenterX;
            const dy = py - zoneCenterY;
 
            const localX = zoneCenterX + (dx * cosA - dy * sinA);
            const localY = zoneCenterY + (dx * sinA + dy * cosA);
 
            return (
                localX >= countingZone.x &&
                localX <= countingZone.x + countingZone.width &&
                localY >= countingZone.y &&
                localY <= countingZone.y + countingZone.height
            );
        }
 
        // 9. Distance Matrix Matching Algorithm (Tracking)
        const matches = [];
        const trackedIds = Object.keys(trackedPersons);
 
        trackedIds.forEach(id => {
            const track = trackedPersons[id];
            currentDetections.forEach((det, idx) => {
                const dist = Math.hypot(det.centerX - track.centerX, det.centerY - track.centerY);
                if (dist < maxDisplacement) {
                    matches.push({ id, idx, dist });
                }
            });
        });
 
        matches.sort((a, b) => a.dist - b.dist);
 
        const matchedIds = new Set();
        const matchedDetections = new Set();
        const nextTrackedPersons = {};
 
        matches.forEach(m => {
            if (matchedIds.has(m.id) || matchedDetections.has(m.idx)) return;
 
            matchedIds.add(m.id);
            matchedDetections.add(m.idx);
 
            const det = currentDetections[m.idx];
            const track = trackedPersons[m.id];
            let insideZone = track.insideZone || false;
 
            if (!insideZone && isPointInRotatedRect(det.centerX, det.centerY)) {
                personCount += 1;
                insideZone = true;
            }
 
            nextTrackedPersons[m.id] = {
                centerX: det.centerX,
                centerY: det.centerY,
                box: det.box,
                score: det.score,
                insideZone: insideZone,
                missedFrames: 0
            };
        });
 
        // Retain un-matched tracks up to maxTrackAge
        trackedIds.forEach(id => {
            if (!matchedIds.has(id)) {
                const track = trackedPersons[id];
                const missedFrames = (track.missedFrames || 0) + 1;
                if (missedFrames <= maxTrackAge) {
                    nextTrackedPersons[id] = {
                        ...track,
                        missedFrames: missedFrames
                    };
                }
            }
        });
 
        // Register new tracks
        currentDetections.forEach((det, idx) => {
            if (matchedDetections.has(idx)) return;
 
            let insideZone = isPointInRotatedRect(det.centerX, det.centerY);
            if (insideZone) {
                personCount += 1;
                currentParticipants += 1;
                console.log(`New Person inside zone! Total Count: ${personCount}`);
            }
 
            nextTrackedPersons[nextPersonId++] = {
                centerX: det.centerX,
                centerY: det.centerY,
                box: det.box,
                score: det.score,
                insideZone: insideZone,
                missedFrames: 0
            };
        });
 
        // Sync UI
        document.getElementById('detections').innerText = `Zone Count: ${personCount}`;
        trackedPersons = nextTrackedPersons;
 
        // Render Bounding Boxes
        Object.keys(trackedPersons).forEach(id => {
            const person = trackedPersons[id];
            if (person.missedFrames > 0) return;
 
            const [x, y, width, height] = person.box;
 
            ctx.strokeStyle = person.insideZone ? '#00ff00' : '#ff0000';
            ctx.lineWidth = 3;
            ctx.strokeRect(x, y, width, height);
 
            ctx.fillStyle = ctx.strokeStyle;
            ctx.font = '24px Arial';
            ctx.fillText(`ID: ${id} (${Math.round(person.score * 100)}%)`, x, y > 20 ? y - 10 : 20);
        });
 
    } catch (err) {
        console.error("Inference execution failed:", err);
    } finally {
        if (inputOrtTensor && inputOrtTensor.dispose) {
            inputOrtTensor.dispose();
        }
    }
 
    frameCount++;
    const now = Date.now();
    if (now - lastTime >= 1000) {
        document.getElementById('fps').innerText = frameCount;
        frameCount = 0;
        lastTime = now;
    }
}

async function predictionLoop() {
    if (!isProcessingActive) return;

    //await fetch_data_sql();
    await detectPeople();
    setTimeout(predictionLoop, 100);
}

function Start() {
    startButton.addEventListener('click', () => {
        if (!isProcessingActive) {
            isProcessingActive = true;

            predictionLoop();
        }
        video.play()
            .then(() => {
                console.log("Video playback started successfully.");
            })
            .catch((error) => {
                console.error("Error attempting to play video:", error);
            });
    });
}

/*async function fetch_data_sql() {
    try {
        const response = await fetch('get_element.php');
        const data = await response.json();
        peopleCount = data.peopleCount;
        document.getElementById('detections').innerText = data.currentParticipants;
    } catch (error) {
        console.error('Error fetching data:', error);
    }
}*/

async function run() {
    try {
        await setupCamera();
        await loadModel();
        await checkSession();
        await setupZoneControls();
        drawCountingZone();
        await Start();

        if (isProcessingActive) {
            predictionLoop();
        }

    } catch (error) {
        if (error.name === 'NotReadableError') {
            document.getElementById('status').innerText = 'Camera blocked by another app/tab.';
        } else if (error.name === 'NotAllowedError') {
            document.getElementById('status').innerText = 'Initialization error.';
        } else {
            document.getElementById('status').innerText = 'Error: ' + error.message;
        }
    }
}
run();