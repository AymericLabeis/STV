document.querySelectorAll('.rectDef').forEach(rect => {
    rect.addEventListener('click', (e) => {
      const index = parseInt(e.target.getAttribute('data-index'));
      const videoWidth = document.querySelector('.videoExclu video').offsetWidth;
      const videoExclu = document.querySelector('.videoExclu');
      videoExclu.scrollTo({
        left: index * (videoWidth + 20), // 20 is the margin (10px on each side)
        behavior: 'smooth'
      });
      updateActiveRect(index);
    });
  });
  
  function updateActiveRect(activeIndex) {
    document.querySelectorAll('.rectDef').forEach((rect, index) => {
      if (index === activeIndex) {
        rect.classList.add('active');
      } else {
        rect.classList.remove('active');
      }
    });
  }