var touchFingers;
var touchstartX, touchstartY;
var touchendX, touchendY;
var body = document.body;

function onLeftSwipe() {
  var link = document.getElementById('leftarrow');
  if(link) window.location.href = link.href;
}

function onRightSwipe() {
  var link = document.getElementById('rightarrow');
  if(link) window.location.href = link.href;
}

function onDownSwipe() {
  var link = document.getElementById('backtoalbum');
  if(link) window.location.href = link.href;
}

function handleGesture() {
  var zoom = window.visualViewport.scale;
  var threshold = Math.min(window.screen.width, window.screen.height) * 0.125;
  if(zoom >= 1.02) return;

  if(Math.abs(touchendY - touchstartY) < threshold) {
    if(touchendX - touchstartX < -threshold) {
      onLeftSwipe();
    }
    else if(touchendX - touchstartX > threshold) {
      onRightSwipe();
    }
  }

  if(Math.abs(touchendX - touchstartX) < threshold) {
    if(touchendY - touchstartY > threshold) {
      onDownSwipe();
    }
  }
}

body.addEventListener('touchstart', function (event) {
  touchFingers = event.touches.length;
  touchstartX = event.changedTouches[0].screenX;
  touchstartY = event.changedTouches[0].screenY;
}, false);

body.addEventListener('touchend', function (event) {
  if(touchFingers == 1) {
    touchendX = event.changedTouches[0].screenX;
    touchendY = event.changedTouches[0].screenY;
    handleGesture();
  }
}, false);
