<?php
session_start();
include 'components/header.php';
?>

<script defer src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.min.js"></script>

<div>
  <h2 class="font-headline-lg" style="margin-bottom: var(--unit-md);">Live Attendance Scanner</h2>
  
  <div style="display: grid; grid-template-columns: 2fr 1fr; gap: var(--gutter);">
    <div class="glass-card" style="padding: var(--unit-lg);">
      <div style="position: relative; width: 100%; aspect-ratio: 16/9; background-color: var(--surface); border-radius: 8px; overflow: hidden;">
        <video id="video-stream" autoplay muted playsinline style="width: 100%; height: 100%; object-fit: cover;"></video>
        
        <div style="position: absolute; inset: 0; border: 2px solid rgba(225, 29, 72, 0.2); pointer-events: none;">
          <div style="position: absolute; top: 10%; left: 10%; width: 20px; height: 20px; border-top: 2px solid var(--primary); border-left: 2px solid var(--primary);"></div>
          <div style="position: absolute; top: 10%; right: 10%; width: 20px; height: 20px; border-top: 2px solid var(--primary); border-right: 2px solid var(--primary);"></div>
          <div style="position: absolute; bottom: 10%; left: 10%; width: 20px; height: 20px; border-bottom: 2px solid var(--primary); border-left: 2px solid var(--primary);"></div>
          <div style="position: absolute; bottom: 10%; right: 10%; width: 20px; height: 20px; border-bottom: 2px solid var(--primary); border-right: 2px solid var(--primary);"></div>
        </div>

        <div id="loading-overlay" style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; background-color: rgba(0,0,0,0.8);">
          <span id="status-text" class="font-title-md">Initializing system...</span>
        </div>
      </div>
      <div style="margin-top: 16px; text-align: center;">
         <span id="status-badge" class="badge badge-secondary">Waiting...</span>
      </div>
    </div>

    <div class="glass-card" style="padding: var(--unit-lg); display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;">
      <span id="scan-icon" class="material-symbols-outlined" style="font-size: 48px; color: var(--primary); margin-bottom: 16px; opacity: 0.2; transition: opacity 0.3s;">
        how_to_reg
      </span>
      <h3 class="font-title-md" style="margin: 0 0 8px 0;">Identity Verification</h3>
      <p id="recent-scan" class="font-body-base" style="min-height: 48px; color: var(--on-surface-variant); transition: color 0.3s; margin-bottom: 16px;">
        Awaiting face...
      </p>
      
      <div id="action-container" style="display: none; flex-direction: column; gap: 8px; width: 100%;">
          <button id="btn-confirm" class="btn btn-primary" style="width: 100%; justify-content: center;">
             Confirm Attendance
          </button>
          <button id="btn-cancel" class="btn btn-secondary" style="width: 100%; justify-content: center;">
             Cancel (Not Me)
          </button>
      </div>
    </div>
  </div>
</div>

<script>
let faceMatcher = null;
let isScanning = false;
let pendingAssistantId = null;
let pendingAssistantName = null;

document.addEventListener('DOMContentLoaded', async () => {
  const video = document.getElementById('video-stream');
  const overlay = document.getElementById('loading-overlay');
  const statusText = document.getElementById('status-text');
  const statusBadge = document.getElementById('status-badge');
  const recentScan = document.getElementById('recent-scan');
  const scanIcon = document.getElementById('scan-icon');
  
  const actionContainer = document.getElementById('action-container');
  const btnConfirm = document.getElementById('btn-confirm');
  const btnCancel = document.getElementById('btn-cancel');

  const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';
  
  try {
    statusText.innerText = "Loading face recognition models...";
    await Promise.all([
      faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
      faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
      faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
    ]);

    statusText.innerText = "Fetching aslab data...";
    const res = await fetch('api/assistants.php');
    const assistants = await res.json();
    
    const labeledDescriptors = assistants
      .filter(a => a.face_data)
      .map(a => {
        const descriptorArray = JSON.parse(a.face_data);
        const descriptor = new Float32Array(descriptorArray);
        return new faceapi.LabeledFaceDescriptors(
          JSON.stringify({ id: a.id, name: a.name }), 
          [descriptor]
        );
      });
      
    if (labeledDescriptors.length > 0) {
      faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.6);
    } else {
      statusText.innerText = "No registered aslabs found. Scanner cannot match faces.";
      return;
    }

    statusText.innerText = "Starting webcam...";
    const stream = await navigator.mediaDevices.getUserMedia({ video: {} });
    video.srcObject = stream;
    
    statusText.innerText = "Warming up AI...";
    try {
      const tempCanvas = document.createElement('canvas');
      tempCanvas.width = 100;
      tempCanvas.height = 100;
      await faceapi.detectSingleFace(tempCanvas).withFaceLandmarks().withFaceDescriptor();
    } catch (e) {}

    overlay.style.display = 'none';
    statusBadge.className = "badge badge-primary";
    statusBadge.innerText = "Scanner active. Please face the camera.";
    
    isScanning = true;
    scanLoop();
  } catch (err) {
    statusText.innerText = "Error: " + (err.message || "Could not access camera. Please ensure you are on localhost or HTTPS.");
    console.error(err);
  }

  btnCancel.addEventListener('click', () => {
      pendingAssistantId = null;
      pendingAssistantName = null;
      actionContainer.style.display = 'none';
      recentScan.innerText = "Awaiting face...";
      recentScan.style.color = 'var(--on-surface-variant)';
      
      // Resume scanning after a brief delay
      setTimeout(() => {
          isScanning = true;
          scanLoop();
      }, 1000);
  });

  btnConfirm.addEventListener('click', async () => {
      if (!pendingAssistantId) return;
      
      btnConfirm.disabled = true;
      btnCancel.disabled = true;
      recentScan.innerText = "Logging attendance...";
      
      try {
          const res = await fetch('api/attendance.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ assistant_id: pendingAssistantId, method: 'face_id', status: 'on_time' })
          });
          
          const data = await res.json();
          
          if (res.ok) {
              recentScan.innerText = `Successfully logged: ${pendingAssistantName}`;
              recentScan.style.color = '#4ade80';
              scanIcon.style.opacity = 1;
          } else {
              recentScan.innerText = data.error || "Error logging attendance!";
              recentScan.style.color = '#ef4444';
          }
      } catch (err) {
          recentScan.innerText = "Network error.";
          recentScan.style.color = '#ef4444';
      }
      
      // Reset UI after 3 seconds
      setTimeout(() => {
          btnConfirm.disabled = false;
          btnCancel.disabled = false;
          actionContainer.style.display = 'none';
          recentScan.innerText = "Awaiting face...";
          recentScan.style.color = 'var(--on-surface-variant)';
          scanIcon.style.opacity = 0.2;
          pendingAssistantId = null;
          pendingAssistantName = null;
          
          isScanning = true;
          scanLoop();
      }, 3000);
  });

  async function scanLoop() {
    if (!isScanning) return;
    
    if (faceMatcher && video.readyState === 4) {
      const detection = await faceapi.detectSingleFace(video).withFaceLandmarks().withFaceDescriptor();
        
      if (detection) {
        const bestMatch = faceMatcher.findBestMatch(detection.descriptor);
        if (bestMatch.label !== 'unknown') {
          const assistantData = JSON.parse(bestMatch.label);
          
          isScanning = false; // Pause scanning to wait for user input
          
          pendingAssistantId = assistantData.id;
          pendingAssistantName = assistantData.name;
          
          recentScan.innerText = `Detected: ${assistantData.name}`;
          recentScan.style.color = '#3b82f6';
          
          // Show buttons
          actionContainer.style.display = 'flex';
          return;
        } else {
          recentScan.innerText = "Unknown face detected";
          recentScan.style.color = '#f59e0b';
        }
      } else {
        if (recentScan.innerText === "Unknown face detected") {
          recentScan.innerText = "Awaiting face...";
          recentScan.style.color = 'var(--on-surface-variant)';
        }
      }
    }
    
    requestAnimationFrame(scanLoop);
  }
});
</script>

<?php include 'components/footer.php'; ?>
