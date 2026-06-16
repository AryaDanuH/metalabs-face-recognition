<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
require_once 'api/db.php';
$stmt = $pdo->query("SELECT id, name FROM divisions ORDER BY name");
$divisions = $stmt->fetchAll();

include 'components/header.php';
?>

<script defer src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.min.js"></script>

<div>
  <h2 class="font-headline-lg" style="margin-bottom: var(--unit-md);">Register New Assistant</h2>
  
  <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--gutter);">
    
    <div class="glass-card" style="padding: var(--unit-lg);">
      <h3 class="font-title-md" style="margin-bottom: var(--unit-md);">Face Capture</h3>
      <div style="position: relative; width: 100%; aspect-ratio: 4/3; background-color: var(--surface); border-radius: 8px; overflow: hidden;">
        <video id="video-stream" autoplay muted playsinline style="width: 100%; height: 100%; object-fit: cover;"></video>
        
        <div id="loading-overlay" style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; background-color: rgba(0,0,0,0.6);">
          <span id="status-text">Loading face recognition models...</span>
        </div>
      </div>
      <div style="margin-top: 16px; display: flex; justify-content: space-between; align-items: center;">
        <span id="status-label" class="font-body-sm text-on-surface-variant">Waiting...</span>
        <button id="btn-capture" class="btn btn-primary" disabled>
          <span class="material-symbols-outlined">camera</span> Capture
        </button>
      </div>
    </div>

    <div class="glass-card" style="padding: var(--unit-lg);">
      <h3 class="font-title-md" style="margin-bottom: var(--unit-md);">Details</h3>
      <form id="reg-form" style="display: flex; flex-direction: column; gap: 16px;">
        <div style="display: flex; flex-direction: column; gap: 8px;">
          <label class="font-label-caps">NIM</label>
          <input type="text" id="input-nim" class="input-field" required />
        </div>
        <div style="display: flex; flex-direction: column; gap: 8px;">
          <label class="font-label-caps">Full Name</label>
          <input type="text" id="input-name" class="input-field" required />
        </div>
        <div style="display: flex; flex-direction: column; gap: 8px;">
          <label class="font-label-caps">Division</label>
          <select id="input-division" class="input-field" required>
            <option value="">-- Select Division --</option>
            <?php foreach($divisions as $div): ?>
              <option value="<?= $div['id'] ?>"><?= htmlspecialchars($div['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div id="message-box" style="display: none; padding: 12px; border-radius: 4px;"></div>
        
        <button type="submit" id="btn-submit" class="btn btn-primary" disabled>
           Register Aslab
        </button>
      </form>
    </div>

  </div>
</div>

<script>
let capturedFaceData = null;

document.addEventListener('DOMContentLoaded', async () => {
  const video = document.getElementById('video-stream');
  const overlay = document.getElementById('loading-overlay');
  const statusText = document.getElementById('status-text');
  const statusLabel = document.getElementById('status-label');
  const btnCapture = document.getElementById('btn-capture');
  const btnSubmit = document.getElementById('btn-submit');
  const form = document.getElementById('reg-form');
  const messageBox = document.getElementById('message-box');

  const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';
  
  try {
    await Promise.all([
      faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
      faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
      faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
    ]);
    
    statusText.innerText = "Starting webcam...";
    const stream = await navigator.mediaDevices.getUserMedia({ video: {} });
    video.srcObject = stream;
    
    statusText.innerText = "Warming up AI (this may take a moment)...";
    // Warmup
    try {
      const tempCanvas = document.createElement('canvas');
      tempCanvas.width = 100;
      tempCanvas.height = 100;
      await faceapi.detectSingleFace(tempCanvas).withFaceLandmarks().withFaceDescriptor();
    } catch (e) {}

    overlay.style.display = 'none';
    statusLabel.innerText = "Ready to capture.";
    btnCapture.disabled = false;
  } catch (err) {
    statusText.innerText = "Error loading models or camera.";
    console.error(err);
  }

  btnCapture.addEventListener('click', async () => {
    btnCapture.disabled = true;
    statusLabel.innerText = "Detecting face...";
    
    const detection = await faceapi.detectSingleFace(video).withFaceLandmarks().withFaceDescriptor();
    
    if (!detection) {
      statusLabel.innerText = "No face detected! Please ensure you are clearly visible.";
      btnCapture.disabled = false;
      return;
    }
    
    capturedFaceData = Array.from(detection.descriptor);
    statusLabel.innerText = "Face captured successfully!";
    btnSubmit.disabled = false;
    btnCapture.disabled = false;
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!capturedFaceData) return;
    
    btnSubmit.disabled = true;
    messageBox.style.display = 'block';
    messageBox.style.backgroundColor = 'rgba(255,255,255,0.1)';
    messageBox.style.color = 'white';
    messageBox.innerText = "Registering...";

    const payload = {
      nim: document.getElementById('input-nim').value,
      name: document.getElementById('input-name').value,
      role: 'assistant',
      division_id: document.getElementById('input-division').value,
      face_data: JSON.stringify(capturedFaceData)
    };

    try {
      const res = await fetch('api/register.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      
      if (res.ok) {
        messageBox.style.display = 'block';
        messageBox.style.backgroundColor = 'rgba(34, 197, 94, 0.2)';
        messageBox.style.color = '#22c55e';
        messageBox.innerText = "Aslab registered successfully!";
        form.reset();
        capturedFaceData = null;
        btnSubmit.disabled = true;
        statusLabel.innerText = "Ready to capture.";
      } else {
        messageBox.style.backgroundColor = 'rgba(239, 68, 68, 0.2)';
        messageBox.style.color = '#ef4444';
        messageBox.innerText = data.error || "Registration failed.";
        btnSubmit.disabled = false;
      }
    } catch (err) {
      messageBox.style.backgroundColor = 'rgba(239, 68, 68, 0.2)';
      messageBox.style.color = '#ef4444';
      messageBox.innerText = "Network error. Make sure API is reachable.";
      btnSubmit.disabled = false;
    }
  });
});
</script>

<?php include 'components/footer.php'; ?>
