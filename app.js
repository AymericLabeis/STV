const API_KEY = 'AIzaSyBPpnKStP6WHOdWekaUYsxHtQSpnBXdAcc';

async function fetchTrendingVideos(pageToken = '') {
  const response = await fetch(`https://www.googleapis.com/youtube/v3/videos?part=snippet&chart=mostPopular&regionCode=FR&maxResults=20&pageToken=${pageToken}&key=${API_KEY}`);
  const data = await response.json();
  return data;
}

async function displayTrendingVideos() {
  let videos = [];
  let pageToken = '';
  
  // Fetch up to 20 videos (adjust the limit if needed)
  while (videos.length < 24) {
    const data = await fetchTrendingVideos(pageToken);
    videos = videos.concat(data.items);
    pageToken = data.nextPageToken;

    // Break the loop if there are no more videos to fetch or if we have enough videos
    if (!pageToken || videos.length >= 24) break;
  }

  // Ensure we only have 20 videos
  videos = videos.slice(0, 24);

  const container = document.getElementById('latest-videos');
  container.innerHTML = '';

  videos.forEach(video => {
    const videoId = video.id;
    const iframe = document.createElement('iframe');
   
    iframe.src = `https://www.youtube.com/embed/${videoId}`;
    iframe.frameBorder = '0';
    iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
    iframe.allowFullscreen = true;
    container.appendChild(iframe);
  });
}

window.onload = displayTrendingVideos;





document.addEventListener('DOMContentLoaded', () => {
  const categories = document.querySelector('.categories');
  let isDragging = false;
  let startX, scrollLeft;

  // Fonction pour démarrer le glissement
  function startDrag(e) {
    isDragging = true;
    startX = e.pageX - categories.offsetLeft;
    scrollLeft = categories.scrollLeft;
    categories.style.cursor = 'grabbing';
  }

  // Fonction pour arrêter le glissement
  function stopDrag() {
    isDragging = false;
    categories.style.cursor = 'grab';
  }

  // Fonction pour gérer le mouvement du glissement
  function dragMove(e) {
    if (!isDragging) return;
    e.preventDefault();
    const x = e.pageX - categories.offsetLeft;
    const walk = (x - startX) * 3; // Ajustez la vitesse du défilement
    categories.scrollLeft = scrollLeft - walk;
  }

  // Ajouter les événements de souris pour glisser-déposer
  categories.addEventListener('mousedown', startDrag);
  categories.addEventListener('mouseup', stopDrag);
  categories.addEventListener('mouseleave', stopDrag);
  categories.addEventListener('mousemove', dragMove);

  // Pour le support mobile
  categories.addEventListener('touchstart', (e) => {
    startX = e.touches[0].pageX - categories.offsetLeft;
    scrollLeft = categories.scrollLeft;
    isDragging = true;
    categories.style.cursor = 'grabbing';
  });
  
  categories.addEventListener('touchend', () => {
    isDragging = false;
    categories.style.cursor = 'grab';
  });

  categories.addEventListener('touchmove', (e) => {
    if (!isDragging) return;
    e.preventDefault();
    const x = e.touches[0].pageX - categories.offsetLeft;
    const walk = (x - startX) * 2; // Ajustez la vitesse du défilement
    categories.scrollLeft = scrollLeft - walk;
  });
});







document.addEventListener('DOMContentLoaded', () => {
  const videoContainer = document.querySelector('.videoExclu');
  const videos = videoContainer.querySelectorAll('video');
  const rects = document.querySelectorAll('.rectDef');

  // Fonction pour mettre à jour les rectangles actifs
  function updateActiveRects() {
    const containerRect = videoContainer.getBoundingClientRect();

    videos.forEach((video, index) => {
      const videoRect = video.getBoundingClientRect();
      if (videoRect.left >= containerRect.left && videoRect.right <= containerRect.right) {
        rects[index].classList.add('active');
      } else {
        rects[index].classList.remove('active');
      }
    });
  }

  // Appeler updateActiveRects au chargement initial
  updateActiveRects();

  // Mettre à jour les rectangles lorsqu'on clique sur un rectangle
  rects.forEach((rect, index) => {
    rect.addEventListener('click', () => {
      const videoWidth = videos[0].offsetWidth;
      videoContainer.scrollTo({
        left: index * (videoWidth + 20),
        behavior: 'smooth'
      });
      updateActiveRects();
    });
  });

  // Mettre à jour les rectangles lorsqu'on clique sur les flèches
  document.querySelectorAll('.flecheG, .flecheD').forEach(button => {
    button.addEventListener('click', (e) => {
      const direction = e.target.getAttribute('data-direction');
      const videoWidth = videos[0].offsetWidth;
      const margin = 20; // 10px de chaque côté
      const scrollAmount = videoWidth + margin;
      const currentScrollPosition = videoContainer.scrollLeft;
      const containerWidth = videoContainer.offsetWidth;
      const totalScrollWidth = videoContainer.scrollWidth;

      let newScrollPosition;

      if (direction === 'prev') {
        newScrollPosition = Math.max(0, currentScrollPosition - scrollAmount);
      } else if (direction === 'next') {
        newScrollPosition = Math.min(totalScrollWidth - containerWidth, currentScrollPosition + scrollAmount);
      }

      videoContainer.scrollTo({
        left: newScrollPosition,
        behavior: 'smooth'
      });

      updateActiveRects();
    });
  });

  // Mettre à jour les rectangles lorsqu'on clique sur les flèches de catégorie
  document.querySelectorAll('.flecheBG, .flecheBD').forEach(button => {
    button.addEventListener('click', (e) => {
      const direction = e.target.getAttribute('data-direction');
      const videoWidth = videos[0].offsetWidth;
      const scrollAmount = videoWidth + 20; // 20 is the margin (10px on each side)

      if (direction === 'prev') {
        videoContainer.scrollBy({
          left: -scrollAmount,
          behavior: 'smooth'
        });
      } else if (direction === 'next') {
        videoContainer.scrollBy({
          left: scrollAmount,
          behavior: 'smooth'
        });
      }

      updateActiveRects();
    });
  });

  // Mettre à jour les rectangles lorsqu'on fait défiler la liste de vidéos
  videoContainer.addEventListener('scroll', updateActiveRects);

  // Mettre à jour les rectangles lorsqu'on redimensionne la fenêtre
  window.addEventListener('resize', updateActiveRects);
});


document.querySelectorAll('.videoExclu video').forEach(video => {
  // Fonction pour entrer en plein écran
  function enterFullscreen() {
    if (video.requestFullscreen) {
      video.requestFullscreen();
    } else if (video.mozRequestFullScreen) { // Firefox
      video.mozRequestFullScreen();
    } else if (video.webkitRequestFullscreen) { // Chrome, Safari, Opera
      video.webkitRequestFullscreen();
    } else if (video.msRequestFullscreen) { // IE/Edge
      video.msRequestFullscreen();
    }
  }

  // Activer le plein écran lorsque la vidéo commence à jouer
  video.addEventListener('play', () => {
    enterFullscreen();
  });

  // Empêcher le plein écran lors du clic direct sur la vidéo
  video.addEventListener('click', (event) => {
    event.stopPropagation();
  });
});



